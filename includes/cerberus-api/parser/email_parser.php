<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted	  (ben@webgroupmedia.com)	[BGH]
|       Jeremy Johnstone (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/mail_rules/mail_rules.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/email_templates/cer_email_templates.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_QueueCatchallRules.class.php");

define("CER_PARSER_ADDRESS_BANNED",1);

class CER_PARSER
{
	var $db;

	function CER_PARSER()
	{
		$this->db = cer_Database::getInstance();
	}

	// [JAS]: Returns final merged ID or original ticket ID if not merged
	function check_if_merged($ticket_id)
	{
		$merge_path = true;

		while($merge_path)
		{
			$sql = "SELECT to_ticket FROM merge_forward WHERE from_ticket = " . $ticket_id;
			$res = $this->db->query($sql);
			$merge_path = $this->db->num_rows($res);

			if($merge_path)
			{
				$row = $this->db->fetch_row($res);
				$ticket_id = $row["to_ticket"];
			}
			else
			{	return $ticket_id; }
		}
	}

	function get_ticket_id(&$ticket_obj)
	{
		$cfg = CerConfiguration::getInstance();

		$mask = $ticket_obj->ticket_id_mask;
		if($cfg->settings["enable_id_masking"] && !empty($mask))
		$id = $mask;
		else
		$id = $ticket_obj->ticket_id;

		return $id;
	}

	function send_autoresponse(&$queue_obj,&$ticket_obj)
	{
		$cfg = CerConfiguration::getInstance();

		if(!$cfg->settings["sendmail"]) return array();

		$email_templates = new CER_EMAIL_TEMPLATES();

		// [JAS]: Determine if we're doing ticket masking or not.
		$id = $this->get_ticket_id($ticket_obj);

		$subject = "[" . $queue_obj->queue_prefix . " #" . $id . "]: " . $ticket_obj->ticket_subject;
		$body = $email_templates->parse_template_text($queue_obj->queue_response_open,$ticket_obj->ticket_id);

		$mail = new htmlMimeMail();

		if($queue_obj->queue_email_display_name == "") {
			$mail->setFrom($queue_obj->queue_address);
			$proxy_from = $queue_obj->queue_address;
		}
		else {
			$mail->setFrom("\"" . $queue_obj->queue_email_display_name . "\" <" . $queue_obj->queue_address . ">");
			$proxy_from = "\"" . $queue_obj->queue_email_display_name . "\" <" . $queue_obj->queue_address . ">";
		}

		$mail->setText(stripcslashes($body));
		$mail->setSubject(stripcslashes($subject));
		$mail->setReturnPath($queue_obj->queue_address);
		$mail->setHeader("Reply-To", $proxy_from);
		$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
		// [JSJ]: Added creation of new unique message-id
		$new_message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
		$mail->setHeader("Message-ID", $new_message_id); // [JSJ] fixed to use a new message id instead of invalidly using previous email's id.
		$mail->setHeader("In-Reply-To", $ticket_obj->last_message_id); // [JAS] added in-reply-to header
		$mail->setHeader("References", $ticket_obj->last_message_id); // [JAS] added references header
		$send_to = array($ticket_obj->requester_address);
		$result = @$mail->send($send_to,$cfg->settings["mail_delivery"]);

		if(!empty($mail->errors)) {
			return $mail->errors;
		}
		else {
			return array();
		}

	}

	function send_closeresponse(&$ticket_obj)
	{
		$cfg = CerConfiguration::getInstance();

		if(!$cfg->settings["sendmail"]) return array();

		$email_templates = new CER_EMAIL_TEMPLATES();

		$sql = "SELECT q.queue_send_closed, q.queue_response_close FROM queue q ".
		"WHERE q.queue_id = " . $ticket_obj->ticket_queue_id;
		$q_res = $this->db->query($sql);

		$id = $this->get_ticket_id($ticket_obj);

		if($this->db->num_rows($q_res))
		{
			$q_row = $this->db->fetch_row($q_res);
			if($q_row["queue_send_closed"] == 1)
			{
				$body = $email_templates->parse_template_text($q_row["queue_response_close"],$ticket_obj->ticket_id);
				$email = new CERB_RAW_EMAIL();
				$email->body = $body;
				$this->proxy_email_to_requesters($email,$ticket_obj,"",false);
			}
		}
	}

	function send_gatedresponse(&$ticket_obj,&$email,&$queue_obj)
	{
		$cfg = CerConfiguration::getInstance();

		if(!$cfg->settings["sendmail"]) return array();

		// change this to 'bounce' or 'failure' or something?  "Returned"
		$subject = "Re: " . $email->headers->subject;

		// to, sender, date, body

		$tokens = array("##email_subject##","##email_to##","##email_sender##","##email_date##","##email_body##");
		$values = array($email->headers->subject, $queue_obj->queue_address, $ticket_obj->requester_address, date("r"), $email->body);

		// write the body using the gated template tags from this raw email
		$body = str_replace($tokens,$values,$queue_obj->queue_response_gated);

		$mail = new htmlMimeMail();

		$mail->setReturnPath($queue_obj->queue_address);

		if(!empty($queue_obj->queue_email_display_name))
		$mail->setFrom($queue_obj->queue_address);
		else {
			$mail->setFrom("\"" . $queue_obj->queue_email_display_name . "\" <" . $queue_obj->queue_address . ">");
		}

		$mail->setText(stripcslashes($body));
		$mail->setSubject(stripcslashes($subject));

		$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info

		$send_to = array($ticket_obj->requester_address);

		$result = @$mail->send($send_to,$cfg->settings["mail_delivery"]);

		if(!empty($mail->errors)) {
			return $mail->errors;
		}
		else {
			return array();
		}

	}

	function proxy_email_to_requesters(&$email,&$ticket_obj,$cc="",$send_attachments=false)
	{
		$cfg = CerConfiguration::getInstance();

		if(!$cfg->settings["sendmail"]) return array();

		$send_to = array();

		// [JAS]: Gather requesters for this ticket (who are not suppressed).
		$sql = "SELECT a.address_address FROM address a, requestor r ".
		"WHERE r.suppress = 0 AND r.address_id = a.address_id AND r.ticket_id = " . $ticket_obj->ticket_id;
		$req_res = $this->db->query($sql);

		if($this->db->num_rows($req_res))
		{
			while($req_row = $this->db->fetch_row($req_res)) {
				array_push($send_to,$req_row["address_address"]);
			}

			// [JAS]: Remove any addresses from the CC that are also in the requester list
			$cc_list = array();
			$cc_ary = explode(",",$cc);

			foreach($cc_ary as $cc_addy)
			{
				if(array_search(trim($cc_addy),$send_to) === FALSE)
				array_push($cc_list,trim($cc_addy));
			}

			$cc = implode(",",$cc_list);

			// [JAS]: Pull queue information
			$sql = "SELECT q.queue_prefix, qa.queue_address, q.queue_email_display_name, qa.queue_domain ".
			"FROM queue q, queue_addresses qa, ticket t ".
			"WHERE q.queue_id = qa.queue_id AND t.queue_addresses_id = qa.queue_addresses_id ".
			"AND t.ticket_id = " . $ticket_obj->ticket_id;
			$q_res = $this->db->query($sql);

			if($this->db->num_rows($q_res))
			{
				$id = $this->get_ticket_id($ticket_obj);

				$q_row = $this->db->fetch_row($q_res);
				$subject = "[" . $q_row["queue_prefix"] . " #" . $id . "]: " . $ticket_obj->ticket_subject;

				$body = $email->body;

				if($q_row["queue_email_display_name"] == "")
				$proxy_from = $q_row["queue_address"] . "@" . $q_row["queue_domain"];
				else
				$proxy_from = "\"" . $q_row["queue_email_display_name"] . "\" <" . $q_row["queue_address"] . "@" . $q_row["queue_domain"] . ">";

				$mail = new htmlMimeMail();
				$mail->setText($body);
				$mail->setFrom($proxy_from);
				if(!empty($cc)) $mail->setCc($cc);
				$mail->setSubject(stripcslashes($subject));
				$mail->setReturnPath($proxy_from);
				$mail->setHeader("Reply-To", $proxy_from);
				$mail->setHeader("Message-ID", $email->headers->message_id); // [BGH] so the message ID will be the same as the message sent to the requesters
				$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
				$mail->setHeader("In-Reply-To", $ticket_obj->last_message_id); // [BGH] added in-reply-to header
				$mail->setHeader("References", $ticket_obj->last_message_id); // [BGH] added references header

				if($send_attachments !== false && count($email->attachments))
				{
					foreach($email->attachments as $file)
					{
						if($file->filename != "message_source.xml" && $file->filename != "html_mime_part.html") {
							//$attachment = implode("",$file->content);
							$attachment = null;
							foreach($file->tmp_files as $f)
							{
								$attachment .= $mail->getFile($f);
							}
							if(!empty($attachment)) $mail->addAttachment($attachment, $file->filename);
						}
					}
				}

				$result = @$mail->send($send_to,$cfg->settings["mail_delivery"]);

				if(isset($mail->errors) && is_array($mail->errors) && count($mail->errors)) {
					return $mail->errors;
				}
				else { return array(); }
			}
			else {
				// [JAS]: [TODO] Log that we couldn't find a queue address to proxy from for this ticket.
			}
		}
	}

	// [JSJ]: This function sends the new mail to all watchers for the ticket's queue
	function send_email_to_watchers(&$email,&$ticket_obj,$cc="",$thread_type="email",$send_attachments=false)
	{
		$cfg = CerConfiguration::getInstance();

		if(!$cfg->settings["sendmail"]) return array();

		$send_to = array();

		// [JSJ]: Gather watchers for this ticket
		$sql = "select u.user_email FROM queue_access qa, user u ".
		"WHERE qa.user_id = u.user_id AND qa.queue_watch = 1 ".
		"AND u.user_email != '' AND qa.queue_id = " . $ticket_obj->ticket_queue_id;

		if($cfg->settings["watcher_assigned_tech"]) {
			$sql = "select u.user_email FROM user u WHERE u.user_email != '' AND u.user_id = " . $ticket_obj->ticket_assigned_to_id;
			$result = $this->db->query($sql);
			if($this->db->num_rows($result) > 0)
			{
				$assigned_user_row = $this->db->fetch_row($result);
			}
		}

		$result = $this->db->query($sql);
		if($this->db->num_rows($result) > 0)
		{
			// [JAS]: Filter watchers based on global settings and store in an array
			while($watrow = $this->db->fetch_row($result)) {
				if(!$cfg->settings["watcher_assigned_tech"]
				|| $assigned_user_row["user_email"] == $watrow["user_email"]
				|| $ticket_obj->is_new)
				{
					if(!$cfg->settings["not_to_self"]
					|| $email->headers->from != $watrow["user_email"])
					{
						array_push($send_to,$watrow["user_email"]);
					}
				}
			}
		}
		else
		{
			return;
		}

		// [JAS]: Try to use the queue address the ticket was opened from
		$sql = "SELECT q.queue_prefix, qa.queue_address, qa.queue_domain ".
		"FROM queue q, queue_addresses qa ".
		"WHERE q.queue_id = qa.queue_id AND qa.queue_addresses_id = " . $ticket_obj->queue_addresses_id;
		$q_res = $this->db->query($sql);

		// [JAS]: If the original queue e-mail address was removed, use any queue addy
		if(!$this->db->num_rows($q_res)) {
			$sql = "SELECT q.queue_prefix, qa.queue_address, qa.queue_domain ".
			"FROM queue q, queue_addresses qa ".
			"WHERE q.queue_id = qa.queue_id AND qa.queue_id = " . $ticket_obj->ticket_queue_id;
			$q_res = $this->db->query($sql);
		}

		if($this->db->num_rows($q_res)) {

			$q_row = $this->db->fetch_row($q_res);
			$id = $this->get_ticket_id($ticket_obj);

			$subject = "[" . $q_row["queue_prefix"] . " #" . $id . "]: " . $ticket_obj->ticket_subject;
			$body = $email->body;

			$proxy_from = $q_row["queue_address"] . "@" . $q_row["queue_domain"];

			$mail = new htmlMimeMail();
			$mail->setText($body);

			if($cfg->settings["watcher_from_user"] && $email->headers->from != "") // [JSJ]: Fixed to not set empty from
			{
				$mail->setFrom($email->headers->from);
				$mail->setHeader("Sender", $email->headers->from);
			}
			else
			{
				$mail->setFrom($proxy_from);
				$mail->setHeader("Sender", $proxy_from);
			}

			$mail->setHeader("Reply-To", $proxy_from);
			$mail->setHeader("Message-ID", $email->headers->message_id); // [BGH] proxy the message-id also

			if(!empty($ticket_obj->last_message_id)) {
				$mail->setHeader("In-Reply-To", $ticket_obj->last_message_id); // [BGH] added in-reply-to header
			}
			else if(!empty($email->headers->in_reply_to)) {
				$mail->setHeader("In-Reply-To", $email->headers->in_reply_to); // [BGH] added in-reply-to header
			}

			if(!empty($ticket_obj->last_message_id)) {
				$mail->setHeader("References", $ticket_obj->last_message_id); // [BGH] added references header
			}
			else if(!empty($email->headers->references)) {
				$mail->setHeader("References", $email->headers->references); // [BGH] added references header
			}

			if(!empty($cc)) $mail->setCc($cc);

			// [JAS]: We want to denote in the email client whether a message was an email or a comment.
			if($thread_type=="comment") $subject = "[comment]" . $subject;

			$mail->setSubject(stripcslashes($subject));
			$mail->setReturnPath($proxy_from);
			$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info

			// [JAS]: Add attachments to the watcher email, and use JSJ's system attachment filter if enabled
			if($send_attachments !== false && count($email->attachments)) {
				foreach($email->attachments as $file) {
					if(!($cfg->settings["watcher_no_system_attach"] && ($file->filename == "message_source.xml" || $file->filename == "html_mime_part.html")))
					{
						$attachment = null;
						foreach($file->tmp_files as $f)
						{
							$attachment .= $mail->getFile($f);
						}
						$mail->addAttachment($attachment, $file->filename);
					}
				}
			}

			if($cfg->settings["bcc_watchers"]) {
				// [JAS]: Send out each BCC watcher email separately.
				foreach($send_to as $addy)
				$result = @$mail->send(array($addy),$cfg->settings["mail_delivery"]);
			}
			elseif(is_array($send_to) && count($send_to) > 0) {
				$result = @$mail->send($send_to,$cfg->settings["mail_delivery"]);
			}

			if(isset($mail->errors) && is_array($mail->errors) && count($mail->errors)) {
				return $mail->errors;
			}
			else {
				return array();
			}
		}
		else {
			// [JAS]: [TODO] Log that we couldn't find a queue address to proxy from for this ticket.
		}
	}

	// [JXD]: Added bounce arg  11/11/03
	function send_email_to_address($address="",&$email,&$ticket_obj,$cc="",$send_attachments=false,$do_bounce=false,$bcc="")
	{
		$cfg = CerConfiguration::getInstance();

		if(!$cfg->settings["sendmail"]) return array();
		if(empty($address)) return array("No address provided to forward to.");

		$send_to = array();

		if(is_array($address)) {
			$send_to = array_merge($send_to, $address);
		}
		else {
			array_push($send_to, $address);
		}

		$sql = "SELECT q.queue_prefix, q.queue_email_display_name, qa.queue_address, qa.queue_domain ".
		"FROM queue q, queue_addresses qa, ticket t ".
		"WHERE q.queue_id = qa.queue_id AND t.queue_addresses_id = qa.queue_addresses_id ".
		"AND t.ticket_id = " . $ticket_obj->ticket_id;
		$q_res = $this->db->query($sql);

		if($this->db->num_rows($q_res))
		{
			$q_row = $this->db->fetch_row($q_res);
			$id = $this->get_ticket_id($ticket_obj);

			$subject = $ticket_obj->ticket_subject;
			if (!$do_bounce) $subject = "[" . $q_row["queue_prefix"] . " #" . $id . "]: " . $subject;	// [jxdemel]
			$body = $email->body;

			if($q_row["queue_email_display_name"] == "")
				$proxy_from = $q_row["queue_address"] . "@" . $q_row["queue_domain"];
			else
				$proxy_from = "\"" . $q_row["queue_email_display_name"] . "\" <" . $q_row["queue_address"] . "@" . $q_row["queue_domain"] . ">";

			$mail = new htmlMimeMail();
			$mail->setText(stripcslashes($body));

			if ($do_bounce) {	// [jxdemel]  Feature bounce
			$mail->setFrom($email->headers->from);
			$mail->setReturnPath($email->headers->from);
			}
			else {
				$mail->setFrom($proxy_from);
			}

			if(is_array($cc)) $mail->setCc(implode(", ", $cc));
			elseif(!empty($cc)) $mail->setCc($cc);
			if(is_array($bcc)) $mail->setBcc(implode(", ", $bcc));
			elseif(!empty($bcc)) $mail->setBcc($bcc);
			$mail->setSubject(stripcslashes($subject));
			if (!$do_bounce) $mail->setReturnPath($proxy_from);		// [jxdemel]
			if (!$do_bounce) $mail->setHeader("Reply-To", $proxy_from);	// [jxdemel]
			$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
			$mail->setHeader("Message-ID", $email->headers->message_id); // [JSJ] Adding sending of Message-ID header
			$mail->setHeader("In-Reply-To", $ticket_obj->last_message_id); // [BGH] added in-reply-to header
			$mail->setHeader("References", $ticket_obj->last_message_id); // [BGH] added references header

			// [JAS]: Add attachments to the watcher email, and use JSJ's system attachment filter if enabled
			if($send_attachments !== false && count($email->attachments)) {
				foreach($email->attachments as $file) {
					if(!($file->filename == "message_source.xml" || $file->filename == "html_mime_part.html"))
					{
						$attachment = null;
						foreach($file->tmp_files as $f)
						{
							$attachment .= $mail->getFile($f);
						}
						$mail->addAttachment($attachment, $file->filename);
					}
				}
			}

			$result = @$mail->send($send_to,$cfg->settings["mail_delivery"]);

			if(@is_array($mail->errors) && @count($mail->errors)) {
				return $mail->errors;
			}
			else { return array(); }

		}
		else {
			// [JAS]: [TODO] Log that we couldn't find a queue address to proxy from for this ticket.
		}
	}

	function process_mail_rules($type,&$email,&$ticket_obj,&$audit_log)
	{
		$codes = array();

		// [JAS]: Load up all parser mail rules
		$mail_rule_handler = new CER_MAIL_RULE_HANDLER();

		$ignore_email = false;
		$ignore_rules = false;

		// [JAS]: Filter what set of rules we're using
		switch($type) {
			case RULE_TYPE_POST:
			$mail_rules = &$mail_rule_handler->post_rules;
			break;
			case RULE_TYPE_PRE:
			$mail_rules = &$mail_rule_handler->pre_rules;
			break;
			default:
			case RULE_TYPE_ANY:
			$mail_rules = &$mail_rule_handler->mail_rules;
			break;
		}

		// [JAS]: Loop through all mail rules one by one
		foreach($mail_rules as $rule)
		{
			$passed_criteria = true;

			// [JAS]: Loop through all this mail rule's field/oper/value criteria sets
			foreach($rule->fovs as $fov)
			{
				switch($fov->fov_field)
				{
					case RULE_FIELD_SENDER:
					{
						if(!$fov->execute_proc($email->headers->from,$fov->fov_value)) $passed_criteria = false;
						break;
					}
					case RULE_FIELD_SUBJECT:
					{
						if(!$fov->execute_proc($email->headers->subject,$fov->fov_value)) $passed_criteria = false;
						break;
					}
					case RULE_FIELD_QUEUE:
					{
						if(!$fov->execute_proc($ticket_obj->ticket_queue_id,$fov->fov_value)) $passed_criteria = false;
						break;
					}
					case RULE_FIELD_NEW_TICKET:
					{
						if(!$fov->execute_proc($ticket_obj->is_new,$fov->fov_value)) $passed_criteria = false;
						break;
					}
					case RULE_FIELD_REOPENED_TICKET:
					{
						if(!$fov->execute_proc($ticket_obj->is_reopened,$fov->fov_value)) $passed_criteria = false;
						break;
					}
					case RULE_FIELD_ATTACHMENT_NAME:
					{
						$matched_attachment = 0;

						foreach($email->attachments as $email_attachments)
						{ if($fov->execute_proc($email_attachments->filename,$fov->fov_value)) $matched_attachment = 1; }

						if($matched_attachment != 1) $passed_criteria = false;
						break;
					}
					case RULE_FIELD_SPAM_PROBABILITY:
					{
						if(!$fov->execute_proc(100 * $ticket_obj->ticket_spam_probability,$fov->fov_value)) $passed_criteria = false;
						break;
					}
				}
			}

			// [JAS]: All the criteria have been met, run the rule's actions
			if($passed_criteria == true && $ignore_rules == false)
			{
				foreach($rule->actions as $action)
				{
					$args = array();
					$args["ticket_obj"] = &$ticket_obj;
					$args["email"] = &$email;
					$args["action_value"] = $action->action_value;
					$args["audit_log"] = &$audit_log;
					$action->execute_proc($args);

					if($action->action_type == RULE_ACTION_STOP_PROCESSING) {
						$ignore_rules = true;
					}
					if($type != RULE_TYPE_POST && $action->action_type == RULE_ACTION_PRE_IGNORE) {
						$codes["pre_ignore"] = 1;
					}
					if($type != RULE_TYPE_POST && $action->action_type == RULE_ACTION_PRE_NO_NOTIFICATION) {
						$codes["pre_no_notification"] = 1;
					}
					if($type != RULE_TYPE_POST && $action->action_type == RULE_ACTION_PRE_NO_AUTOREPLY) {
						$codes["pre_no_autoreply"] = 1;
					}
				}
			}
		}

		return $codes;
	}

	//	function ticket_reopen(&$ticket_obj)
	//	{
	//		$sql = "UPDATE ticket SET ticket_status = 'reopened' WHERE ticket_id = " . $ticket_obj->ticket_id;
	//		$this->db->query($sql);
	//
	//		return true;
	//	}

	function mark_ticket_customer_replied(&$ticket_obj)
	{
		$sql = "UPDATE ticket SET ticket_status = 'customer-reply' WHERE ticket_status IN ('new','awaiting-reply','bounced','resolved') AND ticket_id = " . $ticket_obj->ticket_id;
		$this->db->query($sql);

		return true;
	}

	function mark_ticket_bounced(&$ticket_obj)
	{
		$sql = "UPDATE ticket SET ticket_status = 'bounced' WHERE ticket_id = " . $ticket_obj->ticket_id;
		$this->db->query($sql);

		return true;
	}

	function find_ticketid_in_subject($subject)
	{
		preg_match_all("/\[.*? #(.*?)\]\:/",$subject,$ticketid_matches);
		unset($ticketid_matches[0]);
		if(count($ticketid_matches[1])) {
			$num_matches = count($ticketid_matches[1])-1; // [JAS]: Compensate for 0-based array, grab right-most subject #
			$ticket_find = $ticketid_matches[1][$num_matches];
			if(!$ticket_id = $this->_validate_ticket_id($ticket_find))
			return false;
			else
			return $ticket_id;
		}
		else {
			return false;
		}
	}

	function find_ticketid_from_message_id($in_reply_to, $references)
	{   // [JSJ]: Search for a ticket_id based on a given message_id

	if(is_null($in_reply_to) && is_null($references)) {
		return false;
	}
	if(!is_null($in_reply_to)) {   // [JSJ]: Check for a match in the in_reply_to header first
	$sql = sprintf("SELECT ticket_id FROM thread WHERE thread_message_id = %s LIMIT 1",
	$this->db->escape($in_reply_to)
	);
	$res = $this->db->query($sql);

	if($row = $this->db->grab_first_row($res)) {
		return $row["ticket_id"];
	}
	}
	if(!is_null($references)) {  // [JSJ]: Check for a match in the references header
	$message_ids = preg_split("/[\s]+/", $references); // [JSJ]: There can be multiple references so split on whitespace
	foreach($message_ids as $message_id) {
		$sql = sprintf("SELECT ticket_id FROM thread WHERE thread_message_id = %s LIMIT 1",
		$this->db->escape($message_id)
		);
		$res = $this->db->query($sql);

		if($row = $this->db->grab_first_row($res)) {
			return $row["ticket_id"];
		}
	}
	}

	return false; // [JSJ]: Nothing found which matches
	}

	function find_ticketid_in_body(&$raw_email)
	{       // [JSJ]:  Search for ticket id in the email body instead of the subject. Used for mail delivery failure notices
	preg_match_all("/\[.*? #(.*?)\]\:/",$raw_email->body,$ticketid_matches);
	unset($ticketid_matches[0]);
	if(count($ticketid_matches[1])) {
		$num_matches = count($ticketid_matches[1])-1; // [JAS]: Compensate for 0-based array, grab right-most subject #
		$ticket_find = $ticketid_matches[1][$num_matches];
		if(!$ticket_id = $this->_validate_ticket_id($ticket_find))
		return false;
		else
		return $ticket_id;
	}
	else {
		return false;
	}
	}

	// TODO: identical to find_ticket_id below
	function _validate_ticket_id($ticket_id=0)
	{
		$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.ticket_mask = %s LIMIT 1",
		$this->db->escape($ticket_id)
		);
		$res = $this->db->query($sql);

		if($row = $this->db->grab_first_row($res)) {
			return $row["ticket_id"];
		}

		$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.ticket_id = %d LIMIT 1",
		$ticket_id
		);
		$res = $this->db->query($sql);

		if($row = $this->db->grab_first_row($res))
		return $row["ticket_id"];

		return false;
	}

	function is_banned_address($address)
	{
		$sql = "SELECT a.address_banned FROM address a WHERE a.address_address = '".$address."'";
		$addy_res = $this->db->query($sql);

		if($addy = $this->db->grab_first_row($addy_res))
		{
			if($addy["address_banned"] == CER_PARSER_ADDRESS_BANNED) return true;
			else return false;
		}
	}

	// [JAS]: Perform flood protection / auto responder war check
	function perform_war_check(&$email_obj, &$ticket_obj)
	{
		$cfg = CerConfiguration::getInstance();

		$warcheck_delay = $cfg->settings["warcheck_secs"];

		$subject_md5 = md5($email_obj->headers->subject);

		// [JAS]: If the e-mail is not addressed to us, automatically pass.  It will officially fail in the parser logic.
		if(!$dest_queue = $ticket_obj->get_dest_queue_data($email_obj)) return true;

		$sql = "DELETE FROM war_check WHERE ".
		sprintf("timestamp < DATE_SUB(NOW(),INTERVAL \"%d\" SECOND)",
		$warcheck_delay
		);
		$this->db->query($sql);

		// [JAS]: See if the same address, subject, queue combo has existed within the last [configurable] seconds.
		$sql = "SELECT wc.warcheck_id FROM war_check wc ".
		sprintf("WHERE wc.address_id = %d AND wc.subject_md5 = %s AND wc.queue_id = %d ",
		$ticket_obj->requester_id,
		$this->db->escape($subject_md5),
		$dest_queue->queue_id
		);
		$war_res = $this->db->query($sql);

		//		$date_handler = new cer_DateTime();
		//		$war_time = $date_handler->rfcDateAsDbDate(@$email_obj->headers->all["date"]);

		if($war_row = $this->db->grab_first_row($war_res)) // Match -- failed warcheck
		{
			//			$sql = "UPDATE war_check wc SET wc.`timestamp` = '$war_time' WHERE wc.warcheck_id = " . $war_row["warcheck_id"];
			$sql = "UPDATE war_check wc SET wc.`timestamp` = NOW() WHERE wc.warcheck_id = " . $war_row["warcheck_id"];
			$this->db->query($sql);
			return false;
		}
		else // [JAS]: No Match -- passed warcheck
		{
			$sql = "INSERT INTO war_check (address_id, subject_md5, queue_id, `timestamp`) " .
			sprintf("VALUES (%d,%s,%d,NOW())",
			$ticket_obj->requester_id,
			$this->db->escape($subject_md5),
			$dest_queue->queue_id
			//				$war_time // removed 04/11/04
			);
			$this->db->query($sql);
			return true;
		}
	}

};

class CER_PARSER_QUEUE
{
	var $queue_id=0;
	var $queue_name=null;
	var $queue_email_display_name=null;
	var $queue_address_id=0;
	var $queue_address=null;
	var $queue_prefix=null;
	var $queue_response_open=null;
	var $queue_response_close=null;
	var $queue_response_gated=null;
	var $queue_send_open=0;
	var $queue_send_closed=0;

	function CER_PARSER_QUEUE()
	{
	}

	function has_enabled_autoresponse()
	{
		if($this->queue_send_open && strlen($this->queue_response_open))
		return true;
		else
		return false;
	}

};

class CER_PARSER_TICKET
{
	var $ticket_id;
	var $ticket_id_mask;
	var $ticket_subject;
	var $ticket_date;
	var $ticket_due;
	var $ticket_priority;
	var $ticket_status;
	var $last_message_id;
	var $ticket_assigned_to_id;
	var $ticket_queue_id;
	var $queue_addresses_id;
	var $min_thread_id;
	var $max_thread_id;
	var $is_new;
	var $is_reopened;
	var $requester_id;
	var $requester_address;
	var $requesters;
	var $ticket_spam_probability = 0.0;
	var $db = null;
	var $thread_handler = null;

	function CER_PARSER_TICKET()
	{
		$this->db = cer_Database::getInstance();
		$this->thread_handler = new cer_ThreadContentHandler();
	}

	function load_ticket_data($ticket_id=0)
	{
		if($ticket_id == 0) return false;

		$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_date, t.ticket_due, t.ticket_priority, t.ticket_status, " .
		"t.ticket_assigned_to_id, t.ticket_queue_id, t.queue_addresses_id, t.min_thread_id, t.max_thread_id, " .
		"a.address_address, a.address_id, th_max.thread_message_id,t.ticket_mask " .
		"FROM ticket t ".
		"LEFT JOIN requestor r ON (r.ticket_id = t.ticket_id) ".
		"LEFT JOIN address a ON (r.address_id = a.address_id) ".
		"LEFT JOIN thread th_max ON (t.max_thread_id = th_max.thread_id) " .
		"WHERE t.ticket_id = " . $ticket_id;
		$ticket_res = $this->db->query($sql);

		if($this->db->num_rows($ticket_res))
		{
			$ticket_data = $this->db->fetch_row($ticket_res);

			$this->ticket_id = $ticket_data["ticket_id"];
			$this->ticket_id_mask = $ticket_data["ticket_mask"];
			$this->ticket_subject = $ticket_data["ticket_subject"];
			$this->ticket_date = $ticket_data["ticket_date"];
			$this->ticket_due = $ticket_data["ticket_due"];
			$this->ticket_priority = $ticket_data["ticket_priority"];
			$this->ticket_status = $ticket_data["ticket_status"];
			$this->last_message_id = $ticket_data["thread_message_id"];
			$this->ticket_assigned_to_id = $ticket_data["ticket_assigned_to_id"];
			$this->ticket_queue_id = $ticket_data["ticket_queue_id"];
			$this->queue_addresses_id = $ticket_data["queue_addresses_id"];
			$this->min_thread_id = $ticket_data["min_thread_id"];
			$this->max_thread_id = $ticket_data["max_thread_id"];
			$this->is_new = false;
			$this->is_reopened = (($ticket_data["ticket_status"]=="resolved" && !$this->is_admin_address($this->requester_address)) ? true : false);
		}

		$this->cache_ticket_requesters();

		unset($ticket_res);
		unset($ticket_data);
	}

	function _get_ticket_spam_probability()
	{
		static $bayes;
		if(empty($bayes)) $bayes = new cer_BayesianAntiSpam();

		$sql = "SELECT th.thread_subject FROM thread th ".
		"WHERE th.thread_id = " . $this->min_thread_id;
		$res = $this->db->query($sql);

		$this->thread_handler->loadThreadContent($this->min_thread_id);
		$thread_content = &$this->thread_handler->threads[$this->min_thread_id]->content;

		if($row = $this->db->grab_first_row($res))
		{
			$text = stripslashes($row["thread_subject"]) . "\r\n" . $thread_content;
			$prob = $bayes->calculate_spam_probability($this->ticket_id);
			return $prob;
		}

		return false;
	}

	function generate_unique_mask()
	{
		$letters = range(65,90); // [JAS]: A-Z

		do {
			$prefix = "";
			$ptr = array_rand($letters,3);
			foreach($ptr as $idx => $p) {
				$prefix .= chr($letters[$p]);
			}
			$suffix1 = rand(10000,99999);
			$suffix2 = rand(100,999);
			$mask = sprintf("%s-%d-%d",$prefix,$suffix1,$suffix2);
		} while (!$this->mask_is_unique($mask));

		return $mask;
	}

	function mask_is_unique($mask="")
	{
		if(empty($mask)) return false;

		$sql = sprintf("SELECT ticket_id FROM ticket WHERE ticket_mask = '%s'",$mask);
		$res = $this->db->query($sql);

		if(!$this->db->num_rows($res)) return true; // unique!
		else return false; // used
	}

	function set_requester($from) {
		$this->requester_id = $this->get_address_id($from);
		$this->requester_address = $mfrom;
	}

	function set_queue_address_id($qaid) {
		if(!empty($qaid) && $this->queue_addresses_id != $qaid) {
			$this->queue_addresses_id = $qaid;
			$sql = sprintf("UPDATE ticket SET queue_addresses_id = %d WHERE ticket_id = %d",
				$qaid,
				$this->ticket_id
			);
			$this->db->query($sql);
			$this->queue_addresses_id = $qaid;
		}
	}

	// TODO: IDENTICAL TO _validate_ticket_id() above
	function find_ticket_id($tkt=0)
	{
		$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.ticket_mask = %s LIMIT 1",
		$this->db->escape($tkt)
		);
		$res = $this->db->query($sql);

		if($row = $this->db->grab_first_row($res))
		return $row["ticket_id"];

		$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.ticket_id = %d LIMIT 1",
		$tkt
		);
		$res = $this->db->query($sql);

		if($row = $this->db->grab_first_row($res))
		return $row["ticket_id"];

		return false;
	}

	function reset_due_date() {
		$cer_SLA = new cer_SLA();
		$due_date_mktime = $cer_SLA->getDueDateForRequesterOnQueue($this->requester_id,$this->ticket_queue_id);
		$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id = %d",
		date("Y-m-d H:i:s",$due_date_mktime),
		$this->ticket_id
		);
		$this->db->query($sql);
	}

	function create_new_ticket($subject="",$dest_queue,$due_date_mktime=null)
	{
		$cfg = CerConfiguration::getInstance();

		$this->ticket_id = "";
		$this->ticket_id_mask = $this->generate_unique_mask();
		$this->ticket_subject = $subject;
		$this->ticket_date = ""; // determine on creation
		$this->ticket_due = null;
		$this->ticket_priority = "0";
		$this->ticket_status = "new";
		$this->ticket_assigned_to_id = "0";
		$this->ticket_queue_id = $dest_queue->queue_id;
		$this->queue_addresses_id = $dest_queue->queue_address_id;
		$this->min_thread_id = "0"; // determine after creation
		$this->max_thread_id = "0"; // determine after creation
		$this->is_new = true;
		$this->is_reopened = false;
		$this->requesters = array();

		// [JAS]: Figure out when this should be due (according to SLA + schedules),
		// 	allowing an override to save time if the parser already figued this out.
		if(!$due_date_mktime) {
			$cer_SLA = new cer_SLA();
			$due_date_mktime = $cer_SLA->getDueDateForRequesterOnQueue($this->requester_id,$dest_queue->queue_id);
		}

		$this->ticket_due = date("Y-m-d H:i:s",$due_date_mktime);

		$ticket_mask = "";
		if($cfg->settings["enable_id_masking"]) {
			$ticket_mask = $this->ticket_id_mask;
		}

		// [JAS]: Create the basics of the ticket in the database
		$sql = "INSERT INTO ticket (ticket_subject, ticket_date, ticket_due, ticket_priority, ticket_status, " .
		"ticket_queue_id, queue_addresses_id, ticket_mask) " .
		sprintf("VALUES (%s,NOW(),'%s',%d,'%s',%d,%d,'%s')",
			$this->db->escape($this->ticket_subject),
			$this->ticket_due,
			$this->ticket_priority,
			$this->ticket_status,
			$this->ticket_queue_id,
			$this->queue_addresses_id,
			$ticket_mask
		);
		$this->db->query($sql);
		$this->ticket_id = $this->db->insert_id();

		return $this->ticket_id;
	}

	function cache_ticket_requesters()
	{
		// [JAS]: Cache a list of ticket requesters to use instead of database calls
		$sql = "SELECT r.address_id, a.address_address ".
		"FROM requestor r LEFT JOIN address a USING (address_id) ".
		"WHERE r.ticket_id = " . $this->ticket_id;
		$req_res = $this->db->query($sql);
		if($this->db->num_rows($req_res)) {
			while($req = $this->db->fetch_row($req_res))
			{ $this->requesters[$req["address_id"]] = $req["address_address"]; }
		}

		unset($req_res); unset($req);
	}

	function save_requester_link($ticket_id,$requester_id)
	{
		static $queue_addys; // [JAS]: We only need to run this query once per page load

		if(empty($queue_addys))
		{
			$queue_addys = array();

			// [JAS]: Pull up all queue addresses and toss in the requester ID address so we don't have to later
			$sql = "SELECT CONCAT( qa.queue_address, '@', qa.queue_domain ) AS queue_email ".
			"FROM `queue_addresses` qa ";
			$res = $this->db->query($sql);

			if($this->db->num_rows($res)) {
				while($rr = $this->db->fetch_row($res)) {
					$queue_addys[$rr["queue_email"]] = $rr["queue_email"];
				}
			}
		}

		$sql = sprintf("SELECT a.address_address as requester_address ".
		"FROM address a ".
		"WHERE a.address_id = %d",
		$requester_id
		);
		$check_res = $this->db->query($sql);

		// [JAS]: If we have queue addresses to compare, do so.
		//	If the requester we're adding is a queue, don't add link.
		if(!empty($queue_addys) && $rr = $this->db->grab_first_row($check_res)) {
			if(isset($queue_addys[$rr["requester_address"]]))
			return;
			$this->db->data_seek($check_res,0);
		}

		$sql = "SELECT ticket_id FROM requestor WHERE ticket_id = " . $ticket_id . " AND address_id = " . $requester_id;
		$check_res = $this->db->query($sql);

		if(!$this->db->num_rows($check_res))
		{
			$sql = "INSERT IGNORE INTO requestor (ticket_id, address_id) " .
			sprintf("VALUES (%d,%d)",
			$ticket_id,$requester_id);
			$this->db->query($sql);

			$this->cache_ticket_requesters();

			return true;
		}
		else // [JAS]: link already exists
		{
			return false;
		}
	}

	function is_ticket_requester_address($requester_address="")
	{
		if(empty($requester_address)) return false;

		$find_req = array_search($requester_address,$this->requesters);
		if($find_req !== FALSE && $find_req != NULL) return true; // [JAS]: PHP <=> 4.2.0 compliant. (pre 4.2.0 = NULL, post = FALSE)
		else return false;
	}

	function is_ticket_requester_id($requester_id=0)
	{
		if(!$requester_id) return false;

		if(isset($this->requesters[$requester_id])) return true;
		else return false;
	}

	function is_comment_or_email($raw_subject)
	{
		if(stristr($raw_subject,"[comment]"))
		return "comment";
		else
		return "email";
	}

	function save_thread_time_worked($thread_id="",$mins=0)
	{
		if(empty($thread_id) || empty($mins)) return false;

		$sql = sprintf("UPDATE thread SET thread_time_worked = %d WHERE thread_id = %d",
		$mins,
		$thread_id
		);
		$this->db->query($sql);

		// [JAS]: Update the ticket's cumulative time.
		if($this->ticket_id) {
			$sql = sprintf("UPDATE ticket SET ticket_time_worked = ticket_time_worked + %d WHERE ticket_id = %d",
			$mins,
			$this->ticket_id
			);
			$this->db->query($sql);
		}

	}

	function add_ticket_thread(&$email,$thread_type,$is_new=false,$auto_cc_requester=true)
	{
		$to_list = "''";
		$cc_list = "''";

		if(!empty($email->headers->to_raw)) {
			$to_list = $this->db->escape($email->headers->to_raw);
			if(strlen($to_list) > 255) $to_list = substr($to_list,0,252) . "..."; // [JAS]: If more than 255 chars, truncate
			if(substr($to_list,-1) != "'") $to_list .= "'";
		}

		// [JAS]: Also auto add new TO addresses [CERB-41]
		if($auto_cc_requester && is_array($email->headers->to) && !empty($email->headers->to)) {
			foreach($email->headers->to as $to) {
				$requester_id = $this->get_address_id($to);
				$this->save_requester_link($this->ticket_id,$requester_id);
			}
		}
		
		// [JAS]: If we had CC'd addresses, add them to the thread table for tracking
		if(count($email->headers->cc))
		{
			$cc_list = $this->db->escape(implode(", ",$email->headers->cc));
			if(strlen($cc_list) > 255) $cc_list = substr($cc_list,0,252) . "..."; // [JAS]: If more than 255 chars, truncate
			if(substr($cc_list,-1) != "'") $cc_list .= "'";

			// [JAS]: Save CC'd addresses as additional requesters
			if($auto_cc_requester)
			{
				if(!empty($email->headers->cc))
				foreach($email->headers->cc as $cc)
				{
					$requester_id = $this->get_address_id($cc);
					$this->save_requester_link($this->ticket_id,$requester_id);
				}
			}
		}
		
		// [JAS]: Get date from e-mail header
		$date = new cer_DateTime();
		$thread_date = $date->rfcDateAsDbDate(@$email->headers->all["date"]);

		$sql = "INSERT INTO thread (ticket_id, thread_message_id, thread_address_id, thread_type, thread_date, thread_received, thread_subject, thread_to, thread_cc, thread_replyto, is_agent_message) ".
		sprintf("VALUES (%d,%s,%d,'%s','%s',NOW(),%s,%s,%s,%s,%d)",
		$this->ticket_id,
		$this->db->escape($email->headers->message_id),
		$this->requester_id,
		$thread_type,
		$thread_date,
		$this->db->escape($email->headers->subject),
		$to_list,
		$cc_list,
		$this->db->escape($email->headers->from),
		(($this->is_admin_address($email->headers->from)) ? 1 : 0)
		);

		$this->db->query($sql);
		$thread_id = $this->db->insert_id();

		$this->thread_handler->writeThreadContent($thread_id,$email->body);

		$is_agent_reply = (($this->is_admin_address($email->headers->from) && $thread_type == "email") ? 1 : 0);

		if($is_new) {
			$sql = sprintf("UPDATE ticket SET min_thread_id = %d, max_thread_id = %d, last_reply_by_agent = %d ".
			"WHERE ticket_id = %d",
			$thread_id,
			$thread_id,
			$is_agent_reply,
			$this->ticket_id
			);
			$this->min_thread_id = $thread_id;
			$this->max_thread_id = $thread_id;
		}
		else {
			$sql = sprintf("UPDATE ticket SET max_thread_id = %d, last_reply_by_agent = %d ".
			"WHERE ticket_id = %d",
			$thread_id,
			$is_agent_reply,
			$this->ticket_id
			);
			$this->max_thread_id = $thread_id;
		}
		$this->db->query($sql);

		$this->_save_thread_attachments($thread_id,$email);

		return $thread_id;
	}

	function _save_thread_attachments($thread_id,&$email)
	{
		if(count($email->attachments) == 0) return true;

		foreach($email->attachments as $idx => $file)
		{
			if(!count($file->tmp_files)) unset($email->attachments[$idx]);
			else
			{
				$sql = "INSERT INTO thread_attachments (thread_id, file_name, file_size) ".
				sprintf("VALUES (%d,%s,%d)",
				$thread_id,
				$this->db->escape($file->filename),
				$file->filesize
				);
				$this->db->query($sql);

				$file_id = $this->db->insert_id();

				foreach($file->tmp_files as $tmp)
				{
					$fp = @fopen($tmp,"rb");

					$fstat = fstat($fp);

					$size = $fstat["size"];
					if(0<$size) {
						if(@$fp) $file_content = fread($fp,$size);
					}

					$sql = "INSERT INTO thread_attachments_parts (file_id, part_content) ".
					sprintf("VALUES (%d,%s)",
					$file_id,
					$this->db->escape($file_content)
					);
					$this->db->query($sql);

					fclose($fp);
				}
			}
		}
		return true;
	}

	function get_dest_queue_data(&$email)
	{
		$addys = array();

		if($recv_to = $this->_find_to_addy_in_received($email->headers->received)) array_push($addys,$recv_to);

		if(!is_array($email->headers->to))
		$email->headers->to = array($email->headers->to);

		if(!is_array($email->headers->cc))
		$email->headers->cc = array($email->headers->cc);

		$addys = array_merge($addys,$email->headers->to,$email->headers->cc);

		if(!empty($email->headers->delivered_to)) array_push($addys,$email->headers->delivered_to);
		if(!empty($email->headers->envelope_to)) array_push($addys,$email->headers->envelope_to);

		foreach($addys as $addy) {
			if($queue = $this->is_queue_address($addy))
			{ return $queue; break;	}
		}

		// [JAS]: Check catchalls before failing
		foreach($addys as $addy) {
			if($queue = $this->_find_address_catchall_queue($addy))
			{ return $queue; break;}
		}

		return false; // [JAS]: We didn't find a valid matching queue.
	}

	function _find_address_catchall_queue($addy) {
		// [JAS]: \todo need a function to get all a queue's addresses, including inherited ones (containers)
		//   We then use one to match our target queue and hijack: $this->is_queue_address($addy)
		$catchall_handler = new cer_QueueCatchallRuleHandler();

		// [JAS]: Find the proper catch all queue for this destination address, if any.
		if(!$qid = $catchall_handler->findAddressCatchallQueue($addy))
		return false;

		// [JAS]: \todo This handle really should be moved to the parser object
		//	so the entire class can use a single DB interface.
		$queue_handle = new cer_QueueHandler(array($qid));
		$queue = $queue_handle->queues[$qid];
		unset($queue_handle);

		// [JAS]: Hijack the get_queue_data function we're already using to
		//  return queue details to the parser.
		if(!empty($queue->queue_addresses)) {
			$one_addy = array_slice($queue->queue_addresses,0,1);
			return $this->is_queue_address($one_addy[0]);
		}
		else {
			return false;
		}
	}

	function _find_to_addy_in_received($received)
	{
		if(strlen($received) == 0) return false;

		preg_match("/for (.*?)\;/",$received,$to_address);

		unset($to_address[0]);
		if(isset($to_address[1]))
		{
			return $to_address[1];
		}
		else { return false; }
	}

	// [JAS]: \todo This whole method should be using cer_Queue
	function is_queue_address($address)
	{
		$addy = split("@",$address);

		if(count($addy) != 2) return false; // [JAS]: if this doesn't parse into two parts, it's not an email address

		$mailbox = $addy[0];
		$domain = $addy[1];

		$sql = sprintf("SELECT q.queue_name, q.queue_mode, q.queue_email_display_name, ".
		"qa.queue_addresses_id, qa.queue_id, qa.queue_address, ".
		"qa.queue_domain, q.queue_prefix, q.queue_response_open, ".
		"q.queue_send_open, q.queue_response_gated ".
		"FROM queue_addresses qa ".
		"LEFT JOIN queue q USING (queue_id) ".
		"WHERE LOWER(qa.queue_address) = '%s' ".
		"AND LOWER(qa.queue_domain) = '%s'",
		strtolower($mailbox),
		strtolower($domain)
		);
		$addy_res = $this->db->query($sql);

		if($this->db->num_rows($addy_res)) {
			$addy_data = $this->db->fetch_row($addy_res);
			$queue = new CER_PARSER_QUEUE();
			$queue->queue_id = $addy_data["queue_id"];
			$queue->queue_name = $addy_data["queue_name"];
			$queue->queue_mode = $addy_data["queue_mode"];
			$queue->queue_email_display_name = $addy_data["queue_email_display_name"];
			$queue->queue_address_id = $addy_data["queue_addresses_id"];
			$queue->queue_address = $addy_data["queue_address"] . "@" . $addy_data["queue_domain"];
			$queue->queue_prefix = $addy_data["queue_prefix"];
			$queue->queue_response_open = $addy_data["queue_response_open"];
			$queue->queue_response_gated = $addy_data["queue_response_gated"];
			$queue->queue_send_open = $addy_data["queue_send_open"];
			unset($addy_data);
			return $queue;
		}
		else
		return false;
	}

	function is_admin_address($address)
	{
		$sql = "SELECT u.user_id FROM user u WHERE u.user_email != '' AND u.user_email = '$address'";
		$addy_res = $this->db->query($sql);

		if($this->db->num_rows($addy_res))
		{
			if($row = $this->db->fetch_row($addy_res))
			return $row["user_id"];
		}

		return false;
	}

	function get_address_id($address="")
	{
		$sql = sprintf("SELECT a.address_id FROM address a WHERE a.address_address = %s",
		$this->db->escape($address)
		);
		$addy_res = $this->db->query($sql);

		if($this->db->num_rows($addy_res))
		{
			$addy_data = $this->db->fetch_row($addy_res);
			return $addy_data["address_id"];
		}
		else
		{
			$sql = sprintf("INSERT IGNORE INTO address (address_address) VALUES (%s)",
			$this->db->escape($address)
			);

			$this->db->query($sql);
			return $this->db->insert_id();
		}

		unset($addy_res);
		unset($addy_data);
	}

};

?>
