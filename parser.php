<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| File: parser.php
|
| Purpose: E-mail parsing / XML classes
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted   (ben@webgroupmedia.com)   [BGH]
|
| Contributors:
|       Jeremy Johnstone (jeremy@scriptd.net)   [JSJ]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_SESSION",true); // [JAS]: Leave this true

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/xsp_master_gui.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/gui_parser_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/stats/cer_SystemStats.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_handlers.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");

// [JAS]: Pull in and clean the MTA parser's XML POST data
@$post_xml = $_REQUEST["xml"];
@$user = $_REQUEST["user"];
@$password = $_REQUEST["password"];

$xml_data = stripslashes(rawurldecode($post_xml));
unset($post_xml);

$errors = false;
$error_log = array();

if(!isset($cerberus_db)) {
	$cerberus_db = cer_Database::getInstance();
}

$o_raw_email = new CERB_RAW_EMAIL();
$xml_handler = new CERB_XML_EMAIL_HANDLER($o_raw_email);
$cer_parser = new CER_PARSER();
$cer_ticket = new CER_PARSER_TICKET();
$cer_audit_log = new CER_AUDIT_LOG();
$cer_log = new CER_GUI_LOG();
$cer_trigram = new cer_TrigramEmail();
$cer_search = new cer_SearchIndexEmail();
$cer_bayes = new cer_BayesianAntiSpam();
$cfg = CerConfiguration::getInstance();
$cer_ticket_errors = new CER_TICKET_THREAD_ERRORS();
$cer_stats = new cer_SystemStats();

$cer_warcheck = false; // [JAS]: Reset the warcheck

// [JAS]: If we're running the parser in secure mode, we must match the login and password from GUI:Configuration:Global Settings
if($cfg->settings["parser_secure_enabled"])
{
	if($cfg->settings["parser_secure_user"] == $user &&
	$cfg->settings["parser_secure_password"] == $password)
	{} // pass
	else // fail
	{
		$error_msg = sprintf("CERBERUS PARSER [ERROR]: Parser in Secure Mode did not match login/password during XML packet post.  Aborting without parsing.");
		$cer_log->log($error_msg);
		die($error_msg);
	}
}

$xml_handler->parser->read_xml_string($xml_data);
$o_raw_email->build_message(); // [JAS]: Populate attachments from their POST variables, RFC822 addresses, etc.

// [JAS]: Are we adding CC'd addresses to the requester list automatically?
$auto_cc_req = (($cfg->settings["auto_add_cc_reqs"]) ? true : false);

// [JAS]: Get/Create the Requester Address ID
if(!empty($o_raw_email->headers->reply_to) && $o_raw_email->headers->reply_to != '@') { //[BGH] changed return_path to reply_to
	$cer_ticket->requester_id = $cer_ticket->get_address_id($o_raw_email->headers->reply_to); //[BGH] changed return_path to reply_to
	$cer_ticket->requester_address = $o_raw_email->headers->reply_to; //[BGH] changed return_path to reply_to
}
else {
	$cer_ticket->requester_id = $cer_ticket->get_address_id($o_raw_email->headers->from);
	$cer_ticket->requester_address = $o_raw_email->headers->from;
}

// [JAS]: Make sure our 'from' address validated
if($cer_ticket->requester_address == '@' || empty($cer_ticket->requester_address))
{
	$error_msg = sprintf("CERBERUS PARSER [ERROR]: Incoming message didn't have a valid from address (%s).",$cer_ticket->requester_address);
	$cer_log->log($error_msg);
	die($error_msg);
}

// [JAS]: Check and see if the current sender is banned.
if($cer_parser->is_banned_address($cer_ticket->requester_address))
{
	$error_msg = sprintf("CERBERUS PARSER [ERROR]: Incoming message is from banned sender (%s).",$cer_ticket->requester_address);
	$cer_log->log($error_msg);
	die($error_msg);
}

// [JAS]: Perform autoresponder war check / flood protection, returns pass/fail (true/false)
if(!$cer_parser->perform_war_check($o_raw_email,$cer_ticket))
{
	$error_msg = sprintf("CERBERUS PARSER [WARNING]: Incoming message failed flood protection (%s).",$cer_ticket->requester_address);
	$cer_log->log($error_msg);
	$cer_warcheck = true;
}

// [JAS]: Determine if this message is a comment or an e-mail thread.
$thread_type = $cer_ticket->is_comment_or_email($o_raw_email->headers->subject);

// [BGH]: Update the message_id on the ticket object
if(!empty($o_raw_email->headers->message_id)) {
	$cer_ticket->last_message_id = $o_raw_email->headers->message_id;
}

// [JAS]: Try to determine our destination queue in advance so it's available to pre-parse mail rules.
$dest_queue = $cer_ticket->get_dest_queue_data($o_raw_email);

// [JAS]: Determine if this is a new or reply ticket by checking for a Cerberus ticket ID in the subject
if(($ticket_id = $cer_parser->find_ticketid_in_subject($o_raw_email->headers->subject))
		|| ($ticket_id = $cer_parser->find_ticketid_from_message_id($o_raw_email->headers->in_reply_to, $o_raw_email->headers->references)))
{
	// [JAS]: Existing Ticket
	$ticket_id = $cer_parser->check_if_merged($ticket_id); // [JAS]: If merged, find the latest ticket ID
	$cer_ticket->is_new = false;
	$cer_ticket->load_ticket_data($ticket_id);
} else {
	// [JAS]: New Ticket
	$ticket_id = false;
	$cer_ticket->is_new = true;
	$cer_ticket->is_reopened = false;
	
	//[mdf]: new tickets (not existing) must get the queue_id based on the addresses in the email header
	if($dest_queue) {
		$cer_ticket->ticket_queue_id = $dest_queue->queue_id;
	}
}

/*
 * [JAS]: Calculate the spam probability from the actual e-mail message and not the ticket 
 * 	so we can use it in pre rules.
 */
$cer_ticket->ticket_spam_probability = $cer_bayes->calculate_spam_probability_from_plaintext($o_raw_email->headers->subject . " " . $o_raw_email->body);

// [JAS]: Process pre-parser mail rules for this e-mail
$pre_rule_codes = $cer_parser->process_mail_rules(RULE_TYPE_PRE,$o_raw_email,$cer_ticket,$cer_audit_log);

// [JAS]: If we're being told by a mail rule to ignore this e-mail, then bail out of parsing.
if(isset($pre_rule_codes["pre_ignore"])) {
	$error_msg = sprintf("CERBERUS PARSER [LOG]: Incoming message blocked by a mail rule (sender: <b>%s</b>; subject: <i>%s</i>).",
			$o_raw_email->headers->from,
			$o_raw_email->headers->subject
		);
	$cer_log->log($error_msg);
	exit();
}

// [JAS]: EXISTING TICKET
if($ticket_id)
{
	$thread_id = $cer_ticket->add_ticket_thread($o_raw_email,$thread_type,false,$auto_cc_req);
	
	if($cer_ticket->is_reopened) {
		$cer_ticket->reset_due_date(); // since it's reopened, set the SLA due date like it's new
		$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_TICKET_REOPENED,"");
//		$cer_parser->ticket_reopen($cer_ticket);
	}
	
	// [JAS]: Add an audit log entry if this is a reply from a customer & not staff
	//  And trigger notification
	if($thread_type == "email" && !$cer_ticket->is_admin_address($o_raw_email->headers->from))
	{
		$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_REQUESTOR_RESPONSE,"");
		
		// [JAS]: Trigger the Client Reply Notification
		if(!isset($pre_rule_codes["pre_no_notification"])) {
			$notification = new CER_NOTIFICATION($cer_ticket->ticket_assigned_to_id);
			$notification->trigger_event(NOTIFY_EVENT_CLIENT_REPLY,$cer_ticket->ticket_id);
		}
		
		$cer_parser->mark_ticket_customer_replied($cer_ticket);
	}
	elseif($thread_type == "email" && $uid = $cer_ticket->is_admin_address($o_raw_email->headers->from))
	{
		$error_check = $cer_parser->proxy_email_to_requesters($o_raw_email,$cer_ticket,"",true);
		
		if(is_array($error_check) && count($error_check)) {
			$errors = true;
			$error_msg = sprintf("Could not send e-mail to requester list. (<b>%s</b>)",implode("; ",$error_check));
			array_push($error_log,$error_msg);
		}
		
		$cer_audit_log->log_action($ticket_id,$uid,AUDIT_ACTION_REPLIED,"");
	}
	
	$cer_search->indexSingleTicket($ticket_id);
}

elseif(stristr($o_raw_email->headers->from,"mailer-daemon") !== false || stristr($o_raw_email->headers->from,"postmaster") !== false)  // Ticket is from a mail server
{    // [JSJ]: If the email is from a mail server itself then try to add to existing ticket

	if($ticket_id = $cer_parser->find_ticketid_in_body($o_raw_email)) // [JSJ]: existing ticket
	{
		$ticket_id = $cer_parser->check_if_merged($ticket_id); // [JAS]: If merged, find the latest ticket ID
		
		$cer_ticket->load_ticket_data($ticket_id);
		
		$thread_id = $cer_ticket->add_ticket_thread($o_raw_email,$thread_type,false,$auto_cc_req);
		if($cer_ticket->is_reopened) {
			$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_TICKET_REOPENED,"");
		}
		
		$cer_parser->mark_ticket_bounced($cer_ticket);
		$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_DELIVERY_FAILURE,"");
	}
	else // [JSJ]: Could not match bounce to existing ticket so create new one
	{
		// [JAS]: Merge the TO/CC/BCC addresses and attempt to find the destination queue
		if(!$dest_queue)
		{
			$error_msg = sprintf("CERBERUS PARSER [ERROR]: Incoming message didn't match a queue in TO/CC/BCC (<b>%s</b>).",
					implode(", ",@$o_raw_email->headers->to)
				);
			$cer_log->log($error_msg);
			die($error_msg);
		}
		
		$cer_ticket->create_new_ticket($o_raw_email->headers->subject,$dest_queue);
		$thread_id = $cer_ticket->add_ticket_thread($o_raw_email,$thread_type,true,$auto_cc_req);
		$cer_ticket->save_requester_link($cer_ticket->ticket_id,$cer_ticket->requester_id);
		$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_OPENED,$dest_queue->queue_id);
	}

}

else // New Ticket
{
	// [JAS]: Merge the TO/CC/BCC addresses and attempt to find the destination queue
	if(!$dest_queue)
	{
		$error_msg = sprintf("CERBERUS PARSER [ERROR]: Incoming message didn't match a queue in TO/CC/BCC (<b>%s</b>).",
		implode(", ",@$o_raw_email->headers->to));
		$cer_log->log($error_msg);
		die($error_msg);
	}
	
	//================================================[ SLA Check ]============
	$cer_SLA = new cer_SLA();
	
	// [JAS]: Check to see if the destination queue is gated.
	if($cer_SLA->queueIsGated($dest_queue->queue_id)) {
		
		// [JAS]: It is, do we have a key?  If not:
		if(!$cer_SLA->requesterIdHasKeytoGatedQueue($cer_ticket->requester_id,$dest_queue->queue_id)) {
			if(!$cer_warcheck) { // if this MTA isn't at war with Cerberus
				$cer_log->log(sprintf("E-mail message from %s hit gated queue without an SLA. Sent gated autoresponse.",
						$cer_ticket->requester_address
					));
				if(!isset($pre_rule_codes["pre_no_autoreply"])) {
					$cer_parser->send_gatedresponse($cer_ticket,$o_raw_email,$dest_queue);
				}
			}
			exit;
		}
	}
	
	$due_date_mktime = $cer_SLA->getDueDateForRequesterOnQueue($cer_ticket->requester_id,$dest_queue->queue_id);
//	echo "Ticket should be due on " . date("Y-m-d H:i:s",$due_date_mktime);
	//=========================================================================
	
	$cer_ticket->create_new_ticket($o_raw_email->headers->subject,$dest_queue,$due_date_mktime);
	$thread_id = $cer_ticket->add_ticket_thread($o_raw_email,$thread_type,true,$auto_cc_req);
	$cer_ticket->save_requester_link($cer_ticket->ticket_id,$cer_ticket->requester_id);
	$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_OPENED,$dest_queue->queue_id);
	
	// [JAS]: If autoresponses are enabled for this queue *AND* we haven't failed the warcheck 
	//  and a pre-rule hasn't surpressed autoreplies, send an autoresponse
	if($dest_queue->has_enabled_autoresponse() && $cer_warcheck === false && !isset($pre_rule_codes["pre_no_autoreply"])) {
		$error_check = $cer_parser->send_autoresponse($dest_queue,$cer_ticket);
		if(is_array($error_check) && count($error_check)) {
			$errors = true;
			$error_msg = sprintf("Could not send autoresponse e-mail. (%s)",implode("; ",$error_check));
			array_push($error_log,$error_msg);
		}
	}
	
	// [JAS]: Trigger the New Ticket Notification
	if(!isset($pre_rule_codes["pre_no_notification"])) {
		$notification = new CER_NOTIFICATION();
		$notification->trigger_event(NOTIFY_EVENT_NEW_TICKET,$cer_ticket->ticket_id);
	}
	
	// [BGH]: Search index the email
	$cer_search->indexSingleTicketSubject($cer_ticket->ticket_id);
	$cer_search->indexSingleTicket($cer_ticket->ticket_id);
	
	// [BGH]: increment the daily ticket stats
	$cer_stats->incrementTicket($dest_queue->queue_id);
}

// [JSJ]: Send mail to all watchers for the queue.
$error_check = $cer_parser->send_email_to_watchers($o_raw_email,$cer_ticket,"",$thread_type,true);
if(is_array($error_check) && count($error_check)) {
	$errors = true;
	$error_msg = sprintf("Could not send e-mail to watchers. (<b>%s</b>)",implode("; ",$error_check));
	array_push($error_log,$error_msg);
}

// [JAS]: If we had errors sending e-mail above, log them.
if($errors && is_array($error_log) && count($error_log))
{
	$cer_ticket_errors = new CER_TICKET_THREAD_ERRORS();
	$cer_ticket_errors->log_thread_errors($thread_id,$cer_ticket->ticket_id,$error_log);
}

// [JAS]: Post rules use the ticket's first thread as a spam probability.
$cer_ticket->ticket_spam_probability = $cer_ticket->_get_ticket_spam_probability();

// [JAS]: Process parser mail rules for this e-mail
$post_rule_codes = $cer_parser->process_mail_rules(RULE_TYPE_POST,$o_raw_email,$cer_ticket,$cer_audit_log);

// Send satellite status updates to the master GUI about the
//	ticket's property changes
if($cfg->settings["satellite_enabled"])
{
	$xsp_upd = new xsp_login_manager();
	$xsp_upd->register_callback_acl($cer_ticket,"is_admin_address");
	$xsp_upd->xsp_send_summary($cer_ticket->ticket_id);
}

$xml_handler->parser->free();
?>
