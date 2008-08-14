<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/update_ticket.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");

class email_mail
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;

	function email_mail() {
		$this->db =& database_loader::get_instance();
	}

	function send($from, $to, $cc = "", $subject, $body, $agent_id = 0, $as_html = FALSE, $auto_assign_on_reply = FALSE, $attachments = array(), $bcc = "") {
		$cfg = CerConfiguration::getInstance();

		$mail = new htmlMimeMail();
		$mail->setFrom($from);
		$mail->setSubject($subject);
		if(!empty($cc)) {
			if(is_array($cc)) {
				$mail->setCc(implode(", ", $cc));
			}
			else {
				$mail->setCc($cc);
			}
		}
		if(!empty($bcc)) {
			if(is_array($bcc)) {
				$mail->setBcc(implode(", ", $bcc));
			}
			else {
				$mail->setBcc($bcc);
			}
		}
		
		if(is_array($attachments) && count($attachments) > 0) {
			foreach($attachments as $attachment) {
				$mail->addAttachment($mail->getFile($attachment["temp_name"]), $attachment["original_name"]);
			}
		}

		$message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
		$mail->setHeader("Message-ID", $new_message_id);
		$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");

		if($as_html) {
			$mail->setHtml($body, strip_tags($body));
		}
		else {
			$mail->setText($body);
		}

		if(!is_array($to)) {
			$to = array($to);
		}
		
		$result = @$mail->send($to,$cfg->settings["mail_delivery"]);

		if(isset($mail->errors) && is_array($mail->errors) && count($mail->errors)) {
			xml_output::error(0, "Mail Delivery Failure:\n" . implode("\n", $mail->errors));
			return FALSE;
		}

		return $result;
	}

	function new_outbound_email($from, $recipients, $subject, $body, $agent_id = 0, $as_html = FALSE, $files) {
		$email_attachments = array();
		if(is_array($files) && count($files) > 0) {
			foreach($files as $file_num=>$file_data) {
				if(!is_array($file_data["chunks"])) {
					continue;
				}
				
				$email_attachments[$file_num]["original_name"] = $file_data["name"];
				$tmp_filename = tempnam(FILESYSTEM_PATH . "/tempdir", "mcrm_ne_attach");
				$fp = fopen($tmp_filename, "wb");
				
				foreach($file_data["chunks"] as $chunk_num=>$chunk_data) {
					$chunk_name = $chunk_data["name"];
					$chunk_size = $chunk_data["size"];
					if(!is_array($_FILES) || !array_key_exists($chunk_name, $_FILES) || !is_uploaded_file($_FILES[$chunk_name]["tmp_name"])) {
						xml_output::error(0, "Couldn't find attachment chunk data! Error in upload");
					}
					$chunk_content = file_get_contents($_FILES[$chunk_name]["tmp_name"]);
					if(strlen(str_replace(chr(0)," ",$chunk_content)) != $chunk_size) {
						xml_output::error(0, "Chunk size received doesn't match the size specified in packet");
					}
					fwrite($fp, $chunk_content, $chunk_size);
				}
				fclose($fp);
				$email_attachments[$file_num]["temp_name"] = $tmp_filename;
			}
		}
		return $this->send($from, $recipients["to"], $recipients["cc"], $subject, $body, $agent_id, $as_html, FALSE, $email_attachments, $recipients["bcc"]);
	}

	function new_thread($user_id, $ticket_id, $thread_type, $from, $recipients, $subject, $body, $queue_id, $files) {
		$cer_parser = new CER_PARSER();
		$cer_ticket = new CER_PARSER_TICKET();
		$cer_email = new CERB_RAW_EMAIL();
		$cer_search = new cer_SearchIndexEmail();
		$queue_access = new CER_QUEUE_ACCESS();
		$audit_log = new CER_AUDIT_LOG();
		$error_log = array();

		$cer_ticket->load_ticket_data($ticket_id);

		if(!$queue_access->has_write_access($cer_ticket->ticket_queue_id)) {
			xml_output::error("0", "No write access to queue");
		}

		if(is_numeric($queue_id) && $queue_id > 0 && $cer_ticket->ticket_queue_id != $queue_id) {
			$cerberus_db =& cer_Database::getInstance();
			$sql = "SELECT q.queue_name FROM queue q WHERE q.queue_id = '%d';";
			$queue_record = $cerberus_db->query(sprintf($sql, $queue_id));
			$queue_row = $cerberus_db->fetch_row($queue_record);
			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_QUEUE,$queue_row["queue_name"]);
			$sql = "UPDATE ticket SET ticket_queue_id = '%d' WHERE ticket_id = '%d'";
			$cerberus_db->query(sprintf($sql, $queue_id, $ticket_id));
			$cer_ticket->ticket_queue_id = $queue_id;
		}

		if($thread_type=="comment") {
			$do_action = AUDIT_ACTION_COMMENTED;
			$extra = '';
		}
		elseif ($thread_type=="email") {
			$do_action = AUDIT_ACTION_REPLIED;
			$extra = '';
		}
		elseif ($thread_type=="forward") {
			$do_action = AUDIT_ACTION_THREAD_FORWARD;
			$extra = $ticket_forward;
		}
		else {
			xml_output::error("0", "Unknown thread type");
		}

		$audit_log->log_action($ticket_id,$user_id,$do_action,$extra);

		// [JAS]: If the queue address was changed on reply.
		$cer_ticket->set_queue_address_id($cer_ticket->queue_addresses_id);

		$cer_ticket->set_requester($from);

		$attachment_id = count($cer_email->attachments);
		if(is_array($files) && count($files) > 0) {
			foreach($files as $file_num=>$file_data) {
				if(!is_array($file_data["chunks"])) {
					continue;
				}
				
				$cer_email->add_attachment();
				$cer_email->attachments[$attachment_id]->filename = $file_data["name"];
				$cer_email->attachments[$attachment_id]->filesize = $file_data["size"];
				$cer_email->attachments[$attachment_id]->content_type = "application/octet-stream";

				foreach($file_data["chunks"] as $chunk_num=>$chunk_data) {
					$chunk_name = $chunk_data["name"];
					$chunk_size = $chunk_data["size"];
					if(!is_array($_FILES) || !array_key_exists($chunk_name, $_FILES) || !is_uploaded_file($_FILES[$chunk_name]["tmp_name"])) {
						xml_output::error(0, "Couldn't find attachment chunk data! Error in upload");
					}
					$chunk_content = file_get_contents($_FILES[$chunk_name]["tmp_name"]);
					if(strlen(str_replace(chr(0)," ",$chunk_content)) != $chunk_size) {
						xml_output::error(0, "Chunk size received doesn't match the size specified in packet");
					}
					array_push($cer_email->attachments[$attachment_id]->tmp_files,$_FILES[$chunk_name]["tmp_name"]);
					$attachment_id++;
				}
			}
		}


		$cer_email->body = $body;
		$cer_email->headers->from = $from;
		$cer_email->headers->subject = strlen($subject > 0) ? $subject : $cer_ticket->ticket_subject;

		// [JAS]: Did we have addresses to CC to as well?
		$cc_add_reqs = false;
		$ticket_cc = '';

		$cer_email->headers->message_id = $cer_email->generate_message_id();

		$thread_id = $cer_ticket->add_ticket_thread($cer_email,$thread_type,false,$cc_add_reqs);

		// [JAS]: If this was an email and not a comment, send the message to all the requesters
		if($thread_type == "email" || $thread_type == "forward") {
			$error_check = $cer_parser->send_email_to_address($recipients["to"],$cer_email,$cer_ticket,implode(" ", $recipients["cc"]),true,false,implode(" ", $recipients["bcc"]));
			if(is_array($error_check) && count($error_check)) {
				$errors = true;
				$error_msg = sprintf("Could not send e-mail to requester list. (<b>%s</b>)",implode("; ",$error_check));
				array_push($error_log,$error_msg);
			}
		}

		// [JSJ]: Send mail to all watchers for the queue.
		$error_check = $cer_parser->send_email_to_watchers($cer_email,$cer_ticket,"", $thread_type, true);
		if(is_array($error_check) && count($error_check)) {
			$errors = true;
			$error_msg = sprintf("Could not send e-mail to watchers. (<b>%s</b>)",implode("; ",$error_check));
			array_push($error_log,$error_msg);
		}

		// [JAS]: If we had errors sending e-mail above, log them.
		if($errors && is_array($error_log) && count($error_log))
		{
			$ticket_errors = new CER_TICKET_THREAD_ERRORS();
			$ticket_errors->log_thread_errors($thread_id,$cer_ticket->ticket_id,$error_log);
			xml_output::error("0", "Error updating ticket:\n" . implode("\n", $error_log));
		}

		$cer_parser->process_mail_rules(RULE_TYPE_POST,$cer_email,$cer_ticket,$audit_log);
		$cer_search->indexSingleTicket($cer_ticket->ticket_id);
		return TRUE;
	}
}
