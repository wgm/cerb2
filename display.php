<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: display.php
|
| Purpose: Display tickets, ticket properties and all ticket sub-tab
|		pages.  Handles the updates of any ticket properties.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted	  (ben@webgroupmedia.com)   [BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/*
[JAS]: This whole section really needs ripped up and revamped using the API.  There's a bit
of redundant code here, and some things (aka the way the display ticket object works) that 
just don't make sense anymore.

Consider this area under construction.
*/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");
require_once(FILESYSTEM_PATH . "includes/functions/xsp_master_gui.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramCerby.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_translate = new cer_translate;
$queue_access = new CER_QUEUE_ACCESS();
$audit_log = new CER_AUDIT_LOG();
$cerberus_format = new cer_formatting_obj();
$acl = new cer_admin_list_struct();
$time_entry_defaults = array();

// [JAS]: Set up the local variables from the scope objects
@$qid = $_REQUEST["qid"];
@$ticket = $_REQUEST["ticket"];
@$mode = $_REQUEST["mode"];
@$form_submit = $_REQUEST["form_submit"];
@$ticket_subject = $_REQUEST["ticket_subject"];
@$ticket_status = $_REQUEST["ticket_status"];
@$ticket_owner = $_REQUEST["ticket_owner"];
@$ticket_queue = $_REQUEST["ticket_queue"];
@$ticket_priority = round($_REQUEST["ticket_priority"]);
@$source = $_REQUEST["source"];
@$hp = $_REQUEST["hp"];
@$field_ids = $_REQUEST["field_ids"];
@$address_id = $_REQUEST["address_id"];
@$batch = $_REQUEST["batch"];
@$initial_owner = $_REQUEST["initial_owner"];
@$initial_queue = $_REQUEST["initial_queue"];
@$initial_status = $_REQUEST["initial_status"];
@$initial_priority = $_REQUEST["initial_priority"];
@$initial_ticket_due = $_REQUEST["initial_ticket_due"];
@$ticket_due_date = $_REQUEST["ticket_due_date"];
@$ticket_due_time_h = $_REQUEST["ticket_due_time_h"];
@$ticket_due_time_m = $_REQUEST["ticket_due_time_m"];
@$ticket_due_time_ampm = $_REQUEST["ticket_due_time_ampm"];

// [JAS]: Time Tracking Variables
$thread_time_id = ($_REQUEST["thread_time_id"]) ? $_REQUEST["thread_time_id"] : "";
$thread_time_date = ($_REQUEST["thread_time_date"]) ? $_REQUEST["thread_time_date"] : "";
$thread_time_h = ($_REQUEST["thread_time_h"]) ? $_REQUEST["thread_time_h"] : "";
$thread_time_m = ($_REQUEST["thread_time_m"]) ? $_REQUEST["thread_time_m"] : "";
$thread_time_ampm = ($_REQUEST["thread_time_ampm"]) ? $_REQUEST["thread_time_ampm"] : "";
$thread_time_working_agent_id = ($_REQUEST["thread_time_working_agent_id"]) ? $_REQUEST["thread_time_working_agent_id"] : 0;
$thread_time_hrs_spent = ($_REQUEST["thread_time_hrs_spent"]) ? $_REQUEST["thread_time_hrs_spent"] : 0.0;
$thread_time_hrs_chargeable = ($_REQUEST["thread_time_hrs_chargeable"]) ? $_REQUEST["thread_time_hrs_chargeable"] : "";
$thread_time_hrs_billable = ($_REQUEST["thread_time_hrs_billable"]) ? $_REQUEST["thread_time_hrs_billable"] : "";
$thread_time_hrs_payable = ($_REQUEST["thread_time_hrs_payable"]) ? $_REQUEST["thread_time_hrs_payable"] : "";
$thread_time_summary = ($_REQUEST["thread_time_summary"]) ? $_REQUEST["thread_time_summary"] : "";
$thread_time_date_billed = ($_REQUEST["thread_time_date_billed"]) ? $_REQUEST["thread_time_date_billed"] : 0;
$thread_time_delete = ($_REQUEST["thread_time_delete"]) ? $_REQUEST["thread_time_delete"] : "";
$thread_time_custom_gid = ($_REQUEST["thread_time_custom_gid"]) ? $_REQUEST["thread_time_custom_gid"] : 0;
$thread_time_custom_inst_id = ($_REQUEST["thread_time_custom_inst_id"]) ? $_REQUEST["thread_time_custom_inst_id"] : 0;
@$ticket_spam = $_REQUEST["ticket_spam"];

$cer_ticket = new CER_PARSER_TICKET();
if($ticket = $cer_ticket->find_ticket_id(trim($ticket))) {}

// Customer history variables
@$c_history = $_REQUEST["c_history"];
if(!empty($c_history)) $session->vars["c_history"] = $c_history;
else if (!isset($session->vars["c_history"])) $session->vars["c_history"] = "customer";

// Thread variables
@$te_clear = $_REQUEST["te_clear"];
@$thread_action = $_REQUEST["thread_action"];
@$thread = $_REQUEST["thread"];
@$add_to_req = $_REQUEST["add_to_req"];
@$no_attachments = $_REQUEST["no_attachments"];
@$forward_to = $_REQUEST["forward_to"];

// Requester variables
@$req_ids = $_REQUEST["req_ids"];
@$req_address = $_REQUEST["req_address"];
@$req_suppress_ids = $_REQUEST["req_suppress_ids"];

// SLA variables

// CERBY KB Suggestion variables
@$kb_teaching = $_REQUEST["kb_teaching"];
@$kb_suggestion_id = $_REQUEST["kb_suggestion_id"];
@$kb_suggestion = $_REQUEST["kb_suggestion"];

// View variables
@$view_submit = $_REQUEST["view_submit"];

// Merge Variables
@$merge_to = $_REQUEST["merge_to"];
$merge_error = "";

$errorcode = isset($_REQUEST["errorcode"]) ? strip_tags($_REQUEST["errorcode"]) : "";

// [JAS]: Ticket batch actions
if(isset($batch))
{
	switch($batch)
	{
		case "add":
		{
			$session->vars["login_handler"]->batch->batch_add($ticket);
			break;
		}
		case "remove":
		{
			$session->vars["login_handler"]->batch->batch_remove($ticket);
			break;
		}
	}
}

if(isset($te_clear) && !empty($te_clear))
{
	$sql = "DELETE FROM thread_errors WHERE thread_id = " . $te_clear;
	$cerberus_db->query($sql);
}


// ***************************************************************************************************************************
// [JAS]: Check if this ticket was merged somewhere else (and we're not submitting).  If so, redirect
$cer_parser = new CER_PARSER();

if(!isset($form_submit) && isset
($ticket))
{
	$forward_ticket = $cer_parser->check_if_merged($ticket);
	if($forward_ticket != $ticket) header("Location: " . cer_href("display.php?ticket=" . $forward_ticket));
}
// ***************************************************************************************************************************

if(isset($form_submit))
{
	switch($form_submit)
	{
		case "save_layout":
			$layout_home_header_advanced = (isset($_REQUEST["layout_home_header_advanced"])) ? $_REQUEST["layout_home_header_advanced"] : 0;
			$layout_display_show_log = (isset($_REQUEST["layout_display_show_log"])) ? $_REQUEST["layout_display_show_log"] : 0;
			$layout_display_show_suggestions = (isset($_REQUEST["layout_display_show_suggestions"])) ? $_REQUEST["layout_display_show_suggestions"] : 0;
			$layout_display_show_history = (isset($_REQUEST["layout_display_show_history"])) ? $_REQUEST["layout_display_show_history"] : 0;
			$layout_display_show_contact = (isset($_REQUEST["layout_display_show_contact"])) ? $_REQUEST["layout_display_show_contact"] : 0;
			$layout_display_show_fields = (isset($_REQUEST["layout_display_show_fields"])) ? $_REQUEST["layout_display_show_fields"] : 0;
			$layout_display_show_glance = (isset($_REQUEST["layout_display_show_glance"])) ? $_REQUEST["layout_display_show_glance"] : 0;
			$layout_display_show_vitals = (isset($_REQUEST["layout_display_show_vitals"])) ? $_REQUEST["layout_display_show_vitals"] : 0;
			$layout_view_options_bv = (isset($_REQUEST["layout_view_options_bv"])) ? $_REQUEST["layout_view_options_bv"] : 0;

			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_header_advanced"] = $layout_home_header_advanced;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_log"] = $layout_display_show_log;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_suggestions"] = $layout_display_show_suggestions;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_history"] = $layout_display_show_history;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_contact"] = $layout_display_show_contact;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_fields"] = $layout_display_show_fields;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_glance"] = $layout_display_show_glance;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_vitals"] = $layout_display_show_vitals;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_bv"] = $layout_view_options_bv;
			
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			
			$errorcode = "Page layout saved!";
			
			break;
		
		case "kb_suggestion_submit":
		{
			// go get the KB suggestions
			if(!empty($kb_suggestion)) {
				$cerby = new cer_TrigramCerby();
				foreach($kb_suggestion as $kb_id) {
					switch($kb_teaching) {
						case "good":
						{
							$cerby->goodSuggestion($ticket,$kb_id,0);
							break;
						}
						case "bad":
						{
							$cerby->badSuggestion($ticket,$kb_id,0);
							break;
						}
					}
				}
			}
			
			if(!empty($kb_suggestion_id)) {
				$cerby = new cer_TrigramCerby();
				$cerby->goodSuggestion($ticket, $kb_suggestion_id,0);
			}
			
			break;
		}
		case "merge":
		{
			$merge = new CER_TICKET_MERGE();
			if(!$merge->do_merge(array($merge_to,$ticket))) {
				$merge_error = $merge->merge_error;
			}
			break;
		}
		
		case "strip_html":
		{
			if(empty($thread)) break;
			
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent($thread);

			require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
			$strip = new cer_StripHTML();
			$html_part = $strip->strip_html($thread_handler->threads[$thread]->content);
			
			$thread_handler->writeThreadContent($thread,$html_part);

			break;
		}
		case "block_req":
		case "unblock_req":
		{
			$sql = "SELECT th.thread_address_id FROM thread th ".
				"WHERE th.thread_id = " . $thread;
			$req_res = $cerberus_db->query($sql);
			
			if($req = $cerberus_db->grab_first_row($req_res))
			{
				$addy_id = $req["thread_address_id"];
				if($form_submit == "block_req") {
					$sql = "UPDATE address SET address_banned = 1 WHERE address_id = " . $addy_id;
				} else {
					$sql = "UPDATE address SET address_banned = 0 WHERE address_id = " . $addy_id;
				}
				$cerberus_db->query($sql);
			}
			break;
		}
		case "requesters":
		{
			// [JAS]: Adding requester.
			if(!empty($req_address))
			{
				$cer_ticket = new CER_PARSER_TICKET();
				$req_email = stripslashes($req_address);
				$requester_id = $cer_ticket->get_address_id($req_email);
				if($cer_ticket->save_requester_link($ticket,$requester_id)) {
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$req_email);
				}
			}
			
			$sql = "UPDATE requestor SET suppress = 0 WHERE ticket_id = " . $ticket;
			$cerberus_db->query($sql);
			
			// [JAS]: Batch Suppressing Requesters
			if(is_array($req_suppress_ids) && count($req_suppress_ids))
			{
				$sql = "UPDATE requestor SET suppress = 1 WHERE ticket_id = " . $ticket . " AND " .
					"address_id IN (" . implode(",",$req_suppress_ids) . ")";
				$cerberus_db->query($sql);
			}
			
			// [JAS]: Batch Deleting Requesters
			if(is_array($req_ids) && count($req_ids))
			{
				$sql = "DELETE FROM requestor WHERE ticket_id = $ticket AND ".
					"address_id IN (" . implode(",",$req_ids) . ")";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "add_req":
		{
			if(empty($ticket) || empty($thread)) die("Cerberus [ERROR]: Ticket ID or Thread ID not passed for requester add.");
			
			$sql = "SELECT th.thread_address_id, a.address_address FROM thread th LEFT JOIN address a ON (th.thread_address_id = a.address_id) ".
				"WHERE th.thread_id = " . $thread;
			$add_req = $cerberus_db->query($sql);
			
			if($req = $cerberus_db->grab_first_row($add_req))
			{
				$cer_ticket = new CER_PARSER_TICKET();
				if($cer_ticket->save_requester_link($ticket,$req["thread_address_id"])) {
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$req["address_address"]);
				}
			}
			
			break;
		}
		case "forward":
		{
			if(empty($ticket) || empty($thread) || empty($forward_to)) die("Cerberus [ERROR]: Ticket ID or Thread ID not passed for e-mail forward.");
			
			$errors = false;
			$error_log = array();
			
			$cer_parser = new CER_PARSER();
			$cer_ticket = new CER_PARSER_TICKET();
			$cer_email = new CERB_RAW_EMAIL();
			
			$cer_ticket->load_ticket_data($ticket);
			
			$sql = "SELECT th.thread_date, th.is_agent_message, a.address_address ".
				"FROM thread th ".
				"LEFT JOIN address a ON (th.thread_address_id = a.address_id) ".
				"WHERE th.thread_id = " . $thread;
			$th_res = $cerberus_db->query($sql);
			
			if(!$th_info = $cerberus_db->grab_first_row($th_res)) break;
			
			// [JAS]: We don't want to disclose our staff e-mail addresses
			if(!$th_info["is_agent_message"]) {
				$from_address = $th_info["address_address"];
			}
			else {
				$sql = "SELECT qa.queue_address, qa.queue_domain FROM queue_addresses qa ".
					"WHERE qa.queue_addresses_id = " . $cer_ticket->queue_addresses_id;
				$addy_res = $cerberus_db->query($sql);
				if($addy_row = $cerberus_db->grab_first_row($addy_res))
					$from_address = $addy_row["queue_address"] . "@" . $addy_row["queue_domain"];
				else {
					$from_address = "Staff";
				}
				unset($addy_row); unset($addy_res);
			}
			
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent($thread);
			$thread_content = &$thread_handler->threads[$thread]->content;
			
			$date = new cer_DateTime($th_info["thread_date"]);
			
			$cer_email->body = "[[ This is a forwarded message ]]\r\n\r\n" . 
				"On " . $date->getUserDate() . " " .
				$from_address . " wrote:\r\n\r\n" . $thread_content;
			
			$cer_email->import_thread_attachments($thread,$cerberus_db);
			
			// [JAS]: Does the user want to exclude file attachments in their forward?
			if(isset($no_attachments) && $no_attachments)
				$send_attachments = false;
			else
				$send_attachments = true;
			
			$error_check = $cer_parser->send_email_to_address($forward_to,$cer_email,$cer_ticket,"",$send_attachments);
			if(is_array($error_check) && count($error_check)) {
				$errors = true;
				$error_msg = sprintf("Could not forward e-mail to address (%s). (<b>%s</b>)",$forward_to,implode("; ",$error_check));
				array_push($error_log,$error_msg);
			}

			// [JAS]: If we had errors sending e-mail above, log them.
			if($errors && is_array($error_log) && count($error_log)) {
				$ticket_errors = new CER_TICKET_THREAD_ERRORS();
				$ticket_errors->log_thread_errors($thread,$cer_ticket->ticket_id,$error_log);
			}
			else {
				if(!empty($add_to_req))	{
					$requester_id = $cer_ticket->get_address_id($forward_to);
					if($cer_ticket->save_requester_link($ticket,$requester_id)) {
						$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$forward_to);
					}
				}
				
				$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_THREAD_FORWARD,$forward_to);
			}
			
			break;
		}
		
 		case "bounce":    // [JXD]: 20030812
 		{
 			if(empty($ticket) || empty($thread) || empty($forward_to)) die("Cerberus [ERROR]: Ticket ID or Thread ID not passed for e-mail bounce.");
 			
 			$errors = false;
 			$error_log = array();
 			
 			$cer_parser = new CER_PARSER();
 			$cer_ticket = new CER_PARSER_TICKET();
 			$cer_email = new CERB_RAW_EMAIL();
 			
 			$cer_ticket->load_ticket_data($ticket);
 			
 			$sql = "SELECT th.thread_date, a.address_address ".
 				", th.thread_subject, th.thread_cc, th.thread_replyto " .
 				"FROM thread th ".
 				"LEFT JOIN address a ON (th.thread_address_id = a.address_id) ".
 				"WHERE th.thread_id = " . $thread;
 			$th_res = $cerberus_db->query($sql);
 			
 			if(!$th_info = $cerberus_db->grab_first_row($th_res)) break;
 			
 			$from_address = $th_info["address_address"];
 			
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent($thread);
			$thread_content = &$thread_handler->threads[$thread]->content;
 			
			$date = new cer_DateTime($th_info["thread_date"]);
			
 			$cer_email->body = "[[ This message has been bounced by Cerberus Helpdesk ]]\r\n" . 
 				"Originally sent on " . $date->getUserDate() . " by " .
 				$from_address . ":\r\n\r\n" . $thread_content;
 
 			$cer_email->headers->from = $from_address;
 
 			$o_ticket = new CER_TICKET_DISPLAY();
 			$o_ticket->set_ticket_id($ticket);
 			$o_ticket->build_ticket();
 
 			if (count($o_ticket->threads))
 			{
				$tmp_dir_path = FILESYSTEM_PATH . "tempdir";
				
 				foreach ($o_ticket->threads as $one_thread)
 				{
 					if (($one_thread->type != "comment" && $one_thread->type != "email")
 						|| $one_thread->ptr->thread_id !== $thread)
 							continue;
 					
 					foreach ($one_thread->ptr->file_attachments as $attm)
 					{
 						$attmidx = count($cer_email->attachments);
 						$cer_email->add_attachment();
 						$cer_email->attachments[$attmidx]->filename = $attm->file_name;
 						
 						$sql = "SELECT part_content FROM thread_attachments_parts WHERE file_id = ".$attm->file_id;
 						$file_parts_res = $cerberus_db->query($sql,false);
 						
 						while($file_part = $cerberus_db->fetch_row($file_parts_res))
 						{
							$tmp_name = tempnam($tmp_dir_path, "cerb");
							$tp = fopen($tmp_name,"wb");
							if($tp)
							{
								$file_content = $file_part[0];
								fwrite($tp,$file_content,strlen($file_content));
								fclose($tp);
								array_push($cer_email->attachments[$attmidx]->tmp_files,$tmp_name);
							}
 						}
 					}
 					
 				}
 			}
 									
 
 			$error_check = $cer_parser->send_email_to_address(
 				$forward_to,$cer_email,$cer_ticket,$th_info["thread_cc"],true,true);
 			if(is_array($error_check) && count($error_check)) {
 				$errors = true;
 				$error_msg = sprintf("Could not bounce e-mail to address (%s). (<b>%s</b>)",$forward_to,implode("; ",$error_check));
 				array_push($error_log,$error_msg);
 			}
 
 			// [JAS]: If we had errors sending e-mail above, log them.
 			if($errors && is_array($error_log) && count($error_log)) {
 				$ticket_errors = new CER_TICKET_THREAD_ERRORS();
 				$ticket_errors->log_thread_errors($thread,$cer_ticket->ticket_id,$error_log);
 			}
 			else {
 				if(!empty($add_to_req))	{
 					$requester_id = $cer_ticket->get_address_id($forward_to);
 					if($cer_ticket->save_requester_link($ticket,$requester_id)) {
 						$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$forward_to);
 					}
 				}
 				
 				$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_THREAD_BOUNCE,$forward_to);
 			}
 			
 			break;
 		}    // [JXD]: 20030812

		case "clone":
		{
			global $queue_access;
			
			$u_qids = $queue_access->get_readable_qid_list();
			
			$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_status, t.ticket_date, t.ticket_due, t.ticket_assigned_to_id, t.ticket_queue_id, t.ticket_priority, th.thread_address_id, ad.address_address, t.queue_addresses_id, q.queue_name, t.min_thread_id, t.max_thread_id, t.ticket_reopenings, t.ticket_time_worked, t.last_reply_by_agent " .
			"FROM (ticket t, thread th, address ad, queue q) " .
			"WHERE q.queue_id IN ($u_qids) AND th.ticket_id = t.ticket_id AND t.ticket_queue_id = q.queue_id AND th.thread_address_id = ad.address_id AND t.ticket_id = $ticket GROUP BY th.thread_id LIMIT 0,1";
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result) == 0) { 	
				header("Location: ".cer_href("index.php?errorcode=NOACCESS&errorvalue=" . urlencode($_REQUEST["ticket"])));
				exit; 
 			}
			$ticket_data = $cerberus_db->fetch_row($result);
			
			$o_ticket = new CER_TICKET_DISPLAY();
			$o_ticket->set_ticket_id($ticket_data["ticket_id"]);
			$o_ticket->set_ticket_subject($ticket_data["ticket_subject"]);
			$o_ticket->set_ticket_date($ticket_data["ticket_date"]);
			$o_ticket->set_ticket_due($ticket_data["ticket_due"]);
			$o_ticket->set_ticket_status($ticket_data["ticket_status"]);
			$o_ticket->set_ticket_owner($ticket_data["ticket_assigned_to_id"]);
			$o_ticket->set_ticket_priority($ticket_data["ticket_priority"]);
			$o_ticket->set_ticket_queue($ticket_data["ticket_queue_id"]);
			$o_ticket->set_ticket_queue_address_id($ticket_data["queue_addresses_id"]);
			$o_ticket->set_ticket_queue_name($ticket_data["queue_name"]);
			$o_ticket->set_requestor_address($ticket_data["thread_address_id"],$ticket_data["address_address"]);
			$o_ticket->set_ticket_max_thread($ticket_data["max_thread_id"]);
			$o_ticket->set_ticket_min_thread($ticket_data["min_thread_id"]);
			$o_ticket->set_ticket_reopenings($ticket_data["ticket_reopenings"]);
			$o_ticket->set_ticket_time_worked($ticket_data["ticket_time_worked"]);
			
			// [JAS]: Generate Ticket Mask ID if enabled
			$clone_id_mask = "";
			if($cfg->settings["enable_id_masking"])
			{
				require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
				$parser_ticket = new CER_PARSER_TICKET();
				$clone_id_mask = $parser_ticket->generate_unique_mask();
				unset($parser_ticket);
			}
			
			$sql = "INSERT INTO ticket(ticket_subject,ticket_date,ticket_priority,ticket_status,ticket_assigned_to_id,ticket_queue_id,queue_addresses_id,ticket_reopenings,min_thread_id,max_thread_id,ticket_mask,ticket_time_worked,last_reply_by_agent) ".
			"VALUES('".addslashes($o_ticket->ticket_subject)."','".$o_ticket->ticket_date."',".$o_ticket->ticket_priority.",'".$o_ticket->ticket_status."',".$o_ticket->ticket_assigned_to_id.",".$o_ticket->ticket_queue_id.",".$o_ticket->queue_addresses_id.",".$o_ticket->ticket_reopenings.",".$o_ticket->min_thread_id.",".$o_ticket->max_thread_id.",'".$clone_id_mask . "',".$ticket_data["ticket_time_worked"].",".$ticket_data["last_reply_by_agent"].")";
			$cerberus_db->query($sql);
			$clone_id = $cerberus_db->insert_id();
			
			// **** [ Clone REQUESTERS ]
			$o_ticket->requesters = new CER_TICKET_DISPLAY_REQUESTER($o_ticket);
			foreach($o_ticket->requesters->addresses as $req)
			{
				$sql = "INSERT INTO requestor (ticket_id,address_id,suppress) ".
					sprintf("VALUES(%d,%d,%d)",
						$clone_id,
						$req->address_id,
						$req->suppress
						);
				$cerberus_db->query($sql);
			}
			
			// **** [ Clone THREADS ]
			$sql = "SELECT ticket_id,thread_id,thread_message_id,thread_address_id,thread_type,thread_date,thread_time_worked,is_agent_message FROM thread WHERE ticket_id = " . $o_ticket->ticket_id . " ORDER BY thread_id ASC";
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result) > 0)
			{
				$min_thread=0;
				$max_thread=0;
				
				while($ar = $cerberus_db->fetch_row($result))
				{
					$sql = "INSERT INTO thread(ticket_id,thread_message_id,thread_address_id,thread_type,thread_date,thread_time_worked,is_agent_message) ".
					"VALUES(".$clone_id.",'".$ar["thread_message_id"]."',".$ar["thread_address_id"].",'".$ar["thread_type"]."','".$ar["thread_date"]."',".$ar["thread_time_worked"].",".$ar["is_agent_message"].")";
					$cerberus_db->query($sql);
					$th_id = $cerberus_db->insert_id();
					if($min_thread==0) $min_thread = $th_id;
					$max_thread = $th_id;
					
					$thread_handler = new cer_ThreadContentHandler();
					$thread_handler->loadThreadContent($ar["thread_id"]);
					$thread_content = &$thread_handler->threads[$ar["thread_id"]]->content;
					
					$thread_handler->writeThreadContent($th_id,$thread_content);
					
					// [JAS]: **** File attachment cloning
					$file_ids = array();
					
					$sql = "SELECT thread_id,file_id,file_name,file_size FROM thread_attachments WHERE thread_id = " . $ar["thread_id"];
					$res = $cerberus_db->query($sql);
					
					if($cerberus_db->num_rows($res))
					{
						while($tf = $cerberus_db->fetch_row($res))
						{
							$sql = "INSERT INTO thread_attachments(thread_id,file_name,file_size) ".
							sprintf("VALUES(%d,%s,%d)",
									$th_id,
									$cerberus_db->escape($tf["file_name"]),
									$tf["file_size"]
								);
							$cerberus_db->query($sql);
							
							$file_id_old = $tf["file_id"];
							$file_id_new = $cerberus_db->insert_id();
							
							$sql = sprintf("SELECT part_id,part_content FROM thread_attachments_parts WHERE file_id = %d",
									$file_id_old
								);
							$p_res = $cerberus_db->query($sql);
							
							if($cerberus_db->num_rows($p_res))
							{
								while($pf = $cerberus_db->fetch_row($p_res))
								{
									$sql = "INSERT INTO thread_attachments_parts (file_id,part_content) ".
									sprintf("VALUES(%d,%s)",
											$file_id_new,
											$cerberus_db->escape($pf["part_content"])
										);
									$cerberus_db->query($sql);
								}
							}
						} // end file loop
					}
					
					unset($tf);
					unset($file_ids);
					unset($res);
					
				} // [JAS]: End thread while loop
				
				// **** Clone CUSTOM FIELD INSTANCES (ticket bound)
				$instances = array();
				$new_instances = array();
				
				$sql = sprintf("SELECT efg.group_instance_id, efg.entity_code, efg.entity_index, efg.group_id ".
					"FROM entity_to_field_group efg ".
					"WHERE efg.entity_code = 'T' ".
					"AND efg.entity_index = %d",
						$o_ticket->ticket_id
				);
				$res = $cerberus_db->query($sql);
				
				if($cerberus_db->num_rows($res)) {
					while($row = $cerberus_db->fetch_row($res)) {
						$inst = $row["group_instance_id"];
						$instances[$inst] = $inst;
						
						$sql = sprintf("INSERT INTO entity_to_field_group (entity_code, entity_index, group_id) ".
							"VALUES ('%s',%d,%d)",
								$row["entity_code"],
								$clone_id,
								$row["group_id"]
						);
						$cerberus_db->query($sql);
						
						$new_instances[$inst] = $cerberus_db->insert_id();
					}
					
					if(!empty($instances)) {
						
						$sql = sprintf("SELECT fv.field_id, fv.field_value, fv.group_instance_id, fv.entity_code, ".
							"fv.entity_index, fv.field_group_id ".
							"FROM field_group_values fv ".
							"WHERE fv.entity_code = 'T' ".
							"AND fv.group_instance_id IN (%s)",
								implode(',',$instances)
						);
						$c_res = $cerberus_db->query($sql);
						
						if($cerberus_db->num_rows($c_res)) {
							while($c_row = $cerberus_db->fetch_row($c_res)) {
								$sql = sprintf("INSERT INTO field_group_values (field_id, field_value, group_instance_id, entity_code, entity_index, field_group_id) ".
									"VALUES (%d,%s,%d,'%s',%d,%d)",
										$c_row["field_id"],
										$cerberus_db->escape($c_row["field_value"]),
										$new_instances[$c_row["group_instance_id"]],
										"T",
										$clone_id,
										$c_row["field_group_id"]
								);
								$cerberus_db->query($sql);
							}							
						}
					}
				}
				
				// [JAS]: Reset Min/Max thread pointers after clone
				$sql = "UPDATE ticket SET max_thread_id=$max_thread,min_thread_id=$min_thread WHERE ticket_id = " . $clone_id; // $o_ticket->ticket_id;
				$result = $cerberus_db->query($sql);
			}
			
			$sql = "SELECT al.audit_id,al.ticket_id,al.epoch,al.timestamp,al.user_id,al.action,al.action_value FROM ticket_audit_log al WHERE al.ticket_id = " . $o_ticket->ticket_id;
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result) > 0)
			{
				while($ar = $cerberus_db->fetch_row($result))
				{
					$sql = "INSERT INTO ticket_audit_log(ticket_id,epoch,timestamp,user_id,action,action_value) ".
					"VALUES(".$clone_id.",".$ar["epoch"].",'".$ar["timestamp"]."',".$ar["user_id"].",".$ar["action"].",'".$ar["action_value"]."')";
					$cerberus_db->query($sql);
				}
			}
			
			$sql = "SELECT word_id, in_subject from search_index where ticket_id = $ticket";
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result))
			{
				while($ar = $cerberus_db->fetch_row($result))
				{
					$sql = "INSERT INTO search_index(ticket_id,word_id,in_subject) ".
					"VALUES($clone_id,".$ar["word_id"].",".$ar["in_subject"].")";
					$cerberus_db->query($sql);
				}
			}
			
			$audit_log->log_action($clone_id,$session->vars["login_handler"]->user_id,AUDIT_ACTION_TICKET_CLONED_FROM,$ticket); 
			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_TICKET_CLONED_TO,$clone_id);
			header("Location: ".cer_href("display.php?ticket=$clone_id"));
 
			exit;
			break;
		}
		
		case "edit_custom_fields":
		{
			$handler = new cer_CustomFieldGroupHandler();
			
			@$gi_id = $_REQUEST["instantiate_gid"];
			$for = explode("_",@$_REQUEST["instantiate_for"]);
			
			if($gi_id) {
				$handler->addGroupInstance($for[0],$for[1],$gi_id);
			}
			
			if($queue_access->has_write_access($qid))
			{
				if(@$_REQUEST["group_instances"])
				foreach($_REQUEST["group_instances"] as $gi) {
					
					$field_ids = $_REQUEST["g_{$gi}_field_ids"];
					foreach($field_ids as $id) {
						$value = $_REQUEST["g_{$gi}_field_{$id}"];
						$handler->setFieldInstanceValue($id,$gi,$value);
					}
				}
				
				@$instance_ids = $_REQUEST["instance_ids"];
				if(!empty($instance_ids)) {
					$handler->deleteGroupInstances($instance_ids);
				}
			}
			
			// [JAS]: \todo Need to handle these logging actions
//			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CUSTOM_FIELDS_REQUESTOR,"");
//			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CUSTOM_FIELDS_TICKET,"");
			
			break;
		}
					
		case "properties":
		{
			if($queue_access->has_write_access($qid)) {
				if(!isset($_REQUEST["ticket_priority"])) $ticket_priority=""; else $ticket_priority = $_REQUEST["ticket_priority"];
				$ticket_priority = intval($ticket_priority);
				if($ticket_priority > 100) $ticket_priority = 100;  else if($ticket_priority < 0) $ticket_priority = 0;

				$ticket_due_str = sprintf("%s %d:%02d %s",
						$ticket_due_date,
						$ticket_due_time_h,
						$ticket_due_time_m,
						$ticket_due_time_ampm
					);

				// [JAS]: Make the due date (entered in user time) a timestamp
				$date = new cer_DateTime($ticket_due_str);
				
				// [JAS]: Convert the user timestamp to server time
				$date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);

				// [JAS]: Rebuild the due date string for the database
				$ticket_due_str = $date->getDate("%m/%d/%y %I:%M %p");
				
				// [JAS]: If we've changed the due date
				if(isset($initial_ticket_due) && $initial_ticket_due != $ticket_due_str) {	
					
						$ticket_due_date = new cer_DateTime($ticket_due_str);
						$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id = %d",
								$ticket_due_date->getDate("%Y-%m-%d %H:%M:%S"),
								$ticket
							);
					$cerberus_db->query($sql);
				}
				
				// [JAS]: See if we're assigning new values to this ticket by checking
				//	the existing values in the DB before writing.
				$newstatus = false;
				
				// [JAS]: Perform value change checks and log to the audit log as necessary
				if($initial_status != $ticket_status && !empty($ticket_status)) {
					$newstatus=true;
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_STATUS,$ticket_status);
				}
				
				$owner_check = $ticket_owner;
				if($owner_check == -1) $owner_check = 0;
				
				if($initial_owner != $owner_check) {
					$sql = "SELECT u.user_name FROM user u WHERE user_id = $ticket_owner;";
					$user_record = $cerberus_db->query($sql);
					$user_row = $cerberus_db->fetch_row($user_record);
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_ASSIGN,$user_row["user_name"]);
					
					// [JAS]: Trigger the Assignment Notification
					$notification = new CER_NOTIFICATION($ticket_owner);
					$notification->trigger_event(NOTIFY_EVENT_ASSIGNMENT,$ticket);
				}
				
				
				if($initial_queue != $ticket_queue) {
					$sql = "SELECT q.queue_name FROM queue q WHERE q.queue_id = $ticket_queue;";
					$queue_record = $cerberus_db->query($sql);
					$queue_row = $cerberus_db->fetch_row($queue_record);
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_QUEUE,$queue_row["queue_name"]);
				}
				
				if($initial_priority != $ticket_priority) {
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_PRIORITY,$ticket_priority);
				}
				
				// [JAS]: sloppy, needs redone
				if(isset($_REQUEST["ticket_subject"]) && $_REQUEST["ticket_subject"]=="") {
					$sql = "UPDATE ticket SET ticket_status = '$ticket_status', ticket_assigned_to_id = $ticket_owner, " .
					"ticket_queue_id = $ticket_queue, ticket_priority = $ticket_priority WHERE ticket_id = $ticket";
				}
				else {
					$sql = "UPDATE ticket SET ticket_status = '$ticket_status', ticket_assigned_to_id = $ticket_owner, ticket_queue_id = $ticket_queue, ";
					if(isset($ticket_subject)) $sql .= "ticket_subject = '".addslashes($ticket_subject)."', ";
					$sql .= "ticket_priority = $ticket_priority WHERE ticket_id = $ticket";
				}
				$cerberus_db->query($sql);
				
				$sql = "SELECT q.queue_prefix, q.queue_response_close, q.queue_send_closed, t.queue_addresses_id, t.ticket_subject FROM queue q left join ticket t on t.ticket_queue_id=q.queue_id WHERE t.ticket_id = $ticket LIMIT 0,1";
				$result = $cerberus_db->query($sql);
				$queue_data = $cerberus_db->fetch_row($result);
				
				// [JAS]: Auto response
				if($cfg->settings["sendmail"] && $newstatus && $ticket_status=="resolved" && $queue_data["queue_send_closed"]=="1") {
					$cer_ticket->load_ticket_data($ticket); // this is needed for the autoresponse
					$cer_parser->send_closeresponse($cer_ticket);							
				}
				
				// re-index the subject line
				$cer_search = new cer_SearchIndexEmail();
				$cer_search->indexSingleTicketSubject($ticket);
				
				// un-index trigrams for the email (because of the subject change)
				$cer_trigram = new cer_TrigramEmail();
				$cer_trigram->unindexTicket($ticket);
			}
			break;
		}
		case "take":
		{
			$sql = "SELECT t.ticket_assigned_to_id FROM ticket t WHERE t.ticket_id = $ticket";
			$trez = $cerberus_db->query($sql);
			if($cerberus_db->num_rows($trez) > 0)
			{
				$trow = $cerberus_db->fetch_row($trez);
				if($trow["ticket_assigned_to_id"] == 0)
				{
					$sql = "UPDATE ticket SET ticket_assigned_to_id = " . $session->vars["login_handler"]->user_id . " WHERE ticket_id = $ticket";
					$cerberus_db->query($sql);
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_ASSIGN,$session->vars["login_handler"]->user_name);
				}
				else
				{
					$ticket_preowned = true;
				}
			}
			break;
		}
		case "thread_time_edit":
		{
			if(empty($thread_time_id))
				break;
			
			// [JAS]: ACL check for edit
			if(!$priv->has_priv(ACL_TIME_TRACK_EDIT_OWN,BITGROUP_3)
				&& !($priv->has_priv(ACL_TIME_TRACK_EDIT_ALL,BITGROUP_3) 
					&& ($thread_ptr->ptr->working_agent_id == $session->vars["login_handler"]->user_id))
				)
				break;
				
				
			// [JAS]: Are we deleting the time entry?
			if(!empty($thread_time_delete) && strtoupper($thread_time_delete) == "YES") {
				
				// [JAS]: ACL check for delete
				if($priv->has_priv(ACL_TIME_TRACK_DELETE_OWN,BITGROUP_3)
					&& !($priv->has_priv(ACL_TIME_TRACK_DELETE_ALL,BITGROUP_3) 
						&& ($thread_ptr->ptr->working_agent_id == $session->vars["login_handler"]->user_id))
					)
					{
						$time_handler = new cer_ThreadTimeTrackingHandler();
						$time_handler->deleteTimeEntry($thread_time_id);
						
						$custom_handler = new cer_CustomFieldGroupHandler();
						$custom_handler->load_entity_groups(ENTITY_TIME_ENTRY,$thread_time_id);
						
						// [JAS]: If we had any custom field groups assigned to this thread, time to delete them.
						if(!empty($custom_handler->group_instances))
						foreach($custom_handler->group_instances as $inst) {
							$custom_handler->deleteGroupInstances(array($inst->group_instance_id));	
						}

						break;
					}
			}
			
			// [JAS]: Perform time entry edit.
			$date = new cer_DateTime(sprintf("%s %d:%d%s",$thread_time_date,$thread_time_h,$thread_time_m,$thread_time_ampm));
			$date_billed = new cer_DateTime(sprintf("%s 00:00:00",$thread_time_date_billed));
				
			$time_entry = new cer_ThreadTimeTracking();
				$time_entry->thread_time_id = $thread_time_id;
				$time_entry->ticket_id = $ticket;
				$time_entry->date = $date->getDate("%Y-%m-%d %H:%M:%S");
				$time_entry->working_agent_id = $thread_time_working_agent_id;
				$time_entry->hrs_spent = $thread_time_hrs_spent;
				$time_entry->hrs_chargeable = ($thread_time_hrs_chargeable != "") ? $thread_time_hrs_chargeable : $time_entry->hrs_spent;
				$time_entry->hrs_billable = ($thread_time_hrs_billable != "") ? $thread_time_hrs_billable : $time_entry->hrs_chargeable;
				$time_entry->hrs_payable = ($thread_time_hrs_payable != "") ? $thread_time_hrs_payable : $time_entry->hrs_billable;
				$time_entry->summary = $thread_time_summary;
				$time_entry->date_billed = $date_billed->getDate("%Y-%m-%d %H:%M:%S");
			
			$time_handler = new cer_ThreadTimeTrackingHandler();
			$time_handler->updateTimeEntry($time_entry);
			
			// [JAS]: Do we have custom fields to update?
			// \todo This could probably also be moved into the bindings API for company/contact/time-entry edit
			if($thread_time_id && !empty($thread_time_custom_inst_id)) {
				$custom_handler = &$time_handler->custom_handler;
				$custom_handler->loadGroupTemplates();
				$custom_handler->loadSingleInstance($thread_time_custom_inst_id);
				$gid = $custom_handler->group_instances[$thread_time_custom_inst_id]->group_id;
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$gid]->fields))
				foreach($custom_handler->group_templates[$gid]->fields as $id => $fld) {
					$fld_idx = "thread_time_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					$custom_handler->setFieldInstanceValue($fld->field_id,$thread_time_custom_inst_id,$val);
				}
			}
			
			break;
		}
		
		case "thread_time_add":
		{
			// [JAS]: ACL check for create
			if(!$priv->has_priv(ACL_TIME_TRACK_CREATE,BITGROUP_3))
				break;
			
			$date = new cer_DateTime(sprintf("%s %d:%d%s",$thread_time_date,$thread_time_h,$thread_time_m,$thread_time_ampm));
			$date_billed = new cer_DateTime(sprintf("%s 00:00:00",$thread_time_date_billed));
			
			$time_entry = new cer_ThreadTimeTracking();
				$time_entry->ticket_id = $ticket;
				$time_entry->date = $date->getDate("%Y-%m-%d %H:%M:%S");
				$time_entry->working_agent_id = $thread_time_working_agent_id;
				$time_entry->hrs_spent = $thread_time_hrs_spent;
				$time_entry->hrs_chargeable = ($thread_time_hrs_chargeable != "") ? $thread_time_hrs_chargeable : $time_entry->hrs_spent;
				$time_entry->hrs_billable = ($thread_time_hrs_billable != "") ? $thread_time_hrs_billable : $time_entry->hrs_chargeable;
				$time_entry->hrs_payable = ($thread_time_hrs_payable != "") ? $thread_time_hrs_payable : $time_entry->hrs_billable;
				$time_entry->summary = $thread_time_summary;
				$time_entry->date_billed = $date_billed->getDate("%Y-%m-%d %H:%M:%S");
			
			$time_handler = new cer_ThreadTimeTrackingHandler();
			$time_entry_id = $time_handler->createTimeEntry($time_entry);
			
			// [JAS]: Are we adding a group of custom fields to this entry?
			if($time_entry_id && !empty($thread_time_custom_gid)) {
				$custom_handler = &$time_handler->custom_handler;
				$custom_handler->loadGroupTemplates();
				$inst_id = $custom_handler->addGroupInstance(ENTITY_TIME_ENTRY,$time_entry_id,$thread_time_custom_gid);
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$thread_time_custom_gid]->fields))
				foreach($custom_handler->group_templates[$thread_time_custom_gid]->fields as $id => $fld) {
					$fld_idx = "thread_time_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					if(!empty($val)) {
						$custom_handler->setFieldInstanceValue($fld->field_id,$inst_id,$val);
					}
				}
			}
			
			break;
		}
		
		case "thread_create_time_entry":
		{
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent(array($thread));
			
			if(isset($thread_handler->threads[$thread])) {
				$time_entry_defaults["summary"] = $thread_handler->threads[$thread]->content;
			}
			
			break;
		}
	}
	
	// Send satellite status updates to the master GUI about the
	//	ticket's property changes
	if($cfg->settings["satellite_enabled"])
	{
		$xsp_upd = new xsp_login_manager();
		$xsp_upd->register_callback_acl($acl,"is_admin");
		$xsp_upd->xsp_send_summary($ticket);
	}
}

// Pre-Ticket Actions ]*******************************************************************************************************
switch($mode)
{
	case "batch_clear":
		foreach($session->vars["login_handler"]->batch->tickets as $tkt)
		{ $session->vars["login_handler"]->batch->batch_remove($tkt); }
		break;
}
// ***************************************************************************************************************************


// [JAS]: Handle dynamic view filter options on form submit ]*****************************************************************
if(!empty($view_submit))
{
	@$filter_responded = $_REQUEST[$view_submit."_filter_responded"];
	if(empty($filter_responded)) $filter_responded = 0;
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_responded"] = $filter_responded;

	@$filter_rows =  $_REQUEST[$view_submit."_filter_rows"];
	if(empty($filter_rows)) $filter_rows = 15;
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_rows"] = $filter_rows;
}


// [JAS]: Load Ticket Display Object ]****************************************************************************************
log_user_who_action(WHO_DISPLAY_TICKET,$ticket);

$u_qids = $queue_access->get_readable_qid_list();

//c.id as company_id
$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_status, t.ticket_date, t.ticket_due, t.ticket_assigned_to_id, t.ticket_queue_id, ".
	"t.ticket_priority, th.thread_address_id, ad.address_address, ".
	"t.queue_addresses_id, q.queue_name, t.min_thread_id, t.max_thread_id, t.ticket_reopenings, t.ticket_time_worked, ".
	"t.ticket_spam_trained, t.ticket_mask, ad.public_user_id " .
"FROM (ticket t, thread th, address ad, queue q) ".
//"LEFT JOIN company c ON (c.id = ad.company_id) " .
"WHERE t.ticket_queue_id IN ($u_qids) ".
"AND th.ticket_id = t.ticket_id ".
"AND t.ticket_queue_id = q.queue_id ".
"AND th.thread_address_id = ad.address_id ".
"AND t.ticket_id = $ticket ".
"GROUP BY th.thread_id ".
"LIMIT 0,1";

$result = $cerberus_db->query($sql);

if($cerberus_db->num_rows($result) == 0) {
	$sql = "SELECT to_ticket FROM merge_forward WHERE from_ticket = '%d'";
        $result = $cerberus_db->query(sprintf($sql, $ticket));
        if($cerberus_db->num_rows($result) == 0) {
		header("Location: ".cer_href("index.php?errorcode=NOACCESS&errorvalue=" . urlencode($_REQUEST["ticket"])));
		exit;
	}
	else {
		// Merged Ticket found. Goto Merged ticket (pkolmann)
		$ticket_data = $cerberus_db->fetch_row($result);
                header("Location: ".cer_href("display.php?ticket=" . urlencode($ticket_data["to_ticket"])));
                exit;
        } 
}

$ticket_data = $cerberus_db->fetch_row($result);

$o_ticket = new CER_TICKET_DISPLAY();
$o_ticket->set_ticket_id($ticket_data["ticket_id"]);
$o_ticket->set_ticket_mask($ticket_data["ticket_mask"]);
$o_ticket->set_ticket_subject($ticket_data["ticket_subject"]);
$o_ticket->set_ticket_date($ticket_data["ticket_date"]);
$o_ticket->set_ticket_due($ticket_data["ticket_due"]);
$o_ticket->set_ticket_status($ticket_data["ticket_status"]);
$o_ticket->set_ticket_time_worked($ticket_data["ticket_time_worked"]);
$o_ticket->set_ticket_owner($ticket_data["ticket_assigned_to_id"]);
$o_ticket->set_ticket_priority($ticket_data["ticket_priority"]);
$o_ticket->set_ticket_queue($ticket_data["ticket_queue_id"]);
$o_ticket->set_ticket_queue_address_id($ticket_data["queue_addresses_id"]);
$o_ticket->set_ticket_queue_name($ticket_data["queue_name"]);
$o_ticket->set_requestor_address($ticket_data["thread_address_id"],$ticket_data["address_address"]);
$o_ticket->set_ticket_max_thread($ticket_data["max_thread_id"]);
$o_ticket->set_ticket_min_thread($ticket_data["min_thread_id"]);
$o_ticket->set_ticket_reopenings($ticket_data["ticket_reopenings"]);
$o_ticket->set_public_gui_user_id($ticket_data["public_user_id"]);
$o_ticket->set_spam_trained($ticket_data["ticket_spam_trained"]);
$o_ticket->build_ticket();

// [BGH]: go get the KB suggestions
$cerby = new cer_TrigramCerby();
$o_ticket->suggestions = $cerby->getSuggestion($ticket_data["ticket_id"], 5);

$num_fnr_trigram_matches = count($o_ticket->suggestions);

// [JAS]: Append F&R keyword matches to suggestions here to pad initial untrained F&R results
// \todo Move this into the API somewhere.

if($num_fnr_trigram_matches < 5) {
	$fnr_suggested_kb_ids = array();
	
	if(!empty($o_ticket->suggestions))
	foreach($o_ticket->suggestions as $sug) {
		$fnr_suggested_kb_ids[$sug->kb_id] = $sug->kb_id;
	}
	
	// [JAS]: Add the subject to the body text (weighted slightly for importance) and index it.
	$text = " " . str_repeat(strtolower($o_ticket->ticket_subject), 2);
	
	// [JAS]: Add every non-agent e-mail message together
	foreach($o_ticket->threads as $ti => $tp) {
		$t_id = $tp->ptr->thread_id;
//		if($tp->ptr->thread_type == "email" && ($t_id == $o_ticket->min_thread_id || !$tp->ptr->is_agent_message)) {

		if($tp->ptr->thread_type == "email" && $t_id == $o_ticket->min_thread_id) { // [JSJ]: indexing only the first email as that's all F&R uses
			$maximum_thread_size_to_index = 50 * 1024; // [JSJ]: Adding a check to only keyword density check the first 50k of the email.
			$tmp = split("\n", strtolower(substr($o_ticket->thread_content_handler->threads[$t_id]->content, 0, $maximum_thread_size_to_index)));
			
			// [JAS]: Try to exclude commented lines from messing up keyword density.
			foreach($tmp as $line) {
				if(substr(trim(substr($line, 0, 5)),0,1) != ">") {
					$text .= " " . $line;
				}
			}
		}
	}
	
	$search = new cer_searchIndex();

	$search->indexWords($text);
	$search->removeExcludedKeywords();
	$search->loadWordIDs(1);

	$keyword_matches = array();
	
	// [JAS]: Only pull up indexed keywords from this text that exist in KB articles
	$sql = sprintf("SELECT si.word_id, w.word, count(si.word_id)  AS instances ".
			"FROM (`search_index_kb` si, `search_words` w) ".
			"WHERE si.word_id = w.word_id AND si.word_id IN ( %s )  ".
			"GROUP BY si.word_id ".
			"ORDER BY instances DESC ",
				implode(",", array_values($search->wordarray))
		);
	$res = $cerberus_db->query($sql);

	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$keyword_matches[$row["word_id"]] = $row["instances"];
		}
	}
	
	// [JAS]: Determine our highest density keywords from the original text
	$keyword_density = array();
	
	foreach($search->wordarray as $w => $wi) {
		
		// [JAS]: Only do statistics on words we found in KB articles.
		if(!isset($keyword_matches[$wi]))
			continue;
			
		$offset = 0;
		
		// [JSJ]: Replaced ('s and )'s in the word so things like domain(s) would not cause the regexp to fail
		$pattern = "/(^| |\/|\n|\r)". str_replace("/","\\/",str_replace(array('(', ')'), '', $w)) ."($| |\/|s|\.|\?|\,|\!|es|\n|\r)/i";
		
		// [JSJ]: Added the @ to suppress error's incase there was any other character in that would cause the regexp to fail
		@preg_match_all($pattern, $text, $dev_null);

		$matches = count($dev_null[0]);

		$keyword_density[$wi] = $matches;
	}
	
	$keyword_importance = array();
	
	$weighted = new cer_WeightedAverage();
	foreach($keyword_density as $ki => $kv) {
		$km = $keyword_matches[$ki];
		$keyword_importance[$ki] = $kv * $km;
		$weighted->addSample($ki, $kv * $km);
	}
	
	if(!empty($keyword_importance))
	{
		$importance_lowest = min(array_values($keyword_importance));
		$importance_highest = max(array_values($keyword_importance));
		
		$no_mans_land_floor = max(($importance_lowest + ($weighted->getAverage() - $importance_lowest)) * 0.75, 1);
		$no_mans_land_ceiling = $importance_highest - (($importance_highest - $weighted->getAverage()) * 0.75);
	
		// [JAS]: Keep the most popular and most unique words by importance weight
		foreach($keyword_importance as $i => $v) {
			if($v > $no_mans_land_floor && $v < $no_mans_land_ceiling)
				unset($keyword_importance[$i]);
		}
		
		// [JAS]: Use the above density analysis to get KB matches from the keywords
		$sql = sprintf("SELECT k.kb_id, k.kb_category_id, kp.kb_problem_summary, count( si.kb_article_id )  AS matches ".
				"FROM  (`search_index_kb` si, `knowledgebase` k, `knowledgebase_problem` kp) ".
				"WHERE k.kb_id = kp.kb_id AND k.kb_id = si.kb_article_id ".
				"AND si.word_id IN ( %s )  ".
				"%s ".
				"GROUP BY si.kb_article_id ".
				"ORDER BY matches DESC ".
				"LIMIT 0,%d ",
					implode(',', array_keys($keyword_importance)),
					!empty($fnr_suggested_kb_ids) 
						? sprintf("AND k.kb_id NOT IN ( %s )", implode(',', $fnr_suggested_kb_ids))
						: "",
					(5 - $num_fnr_trigram_matches)
			); 
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res)) {
			$null = null;
			while($row = $cerberus_db->fetch_row($res)) {
				$sug = new cer_KbSuggestion($row["kb_id"], $null, stripslashes($row["kb_problem_summary"]), "keyword");
				$sug->trained = 1;
				
				$percent = ($row["matches"] / count($keyword_importance)) * 100;
				
				$sug->score = sprintf("%0.2f",$percent);
				$sug->url = cer_href("knowledgebase.php?mode=view_entry&kbid=".$sug->kb_id);
				
				$o_ticket->suggestions[$sug->kb_id] = $sug;
			}
		}
	}
} // end KB keyword padding

$bayes = new cer_BayesianAntiSpam();
$text = $o_ticket->ptr_first_thread->thread_subject . "\r\n" . $o_ticket->ptr_first_thread->thread_content;

// [JAS]: Are we doing Bayesian Training?
if(!empty($ticket_spam))
{
	switch($ticket_spam)
	{
		case "spam":
			$bayes->mark_message_as_spam($o_ticket->ticket_id);
			if($cfg->settings["auto_delete_spam"]) $o_ticket->ticket_status = 'dead'; // [JAS]: Update the ticket cache (lang system will translate status)
			$t = 2;
			break;
		case "notspam":
			$bayes->mark_message_as_nonspam($o_ticket->ticket_id);
			$t = 1;
			break;
	}
	
	$sql = "UPDATE ticket SET ticket_spam_trained = $t WHERE ticket_id = " . $o_ticket->ticket_id;
	$cerberus_db->query($sql);
	
	$o_ticket->ticket_spam_trained = $t;
}

$o_ticket->ticket_spam_rating = 100 * $bayes->calculate_spam_probability($ticket_data["ticket_id"],0);

$cer_tpl->assign_by_ref("o_ticket",$o_ticket);

$cer_tpl->assign('thread',$thread);
$cer_tpl->assign('thread_action',$thread_action);

$session->vars["login_handler"]->ticket_id = $o_ticket->ticket_id;
$session->vars["login_handler"]->ticket_mask = $o_ticket->ticket_mask;
$session->vars["login_handler"]->ticket_subject = $o_ticket->ticket_subject;
$session->vars["login_handler"]->ticket_url = cer_href("display.php?ticket=" . $o_ticket->ticket_id);
// ***************************************************************************************************************************


// [JAS]: Header Functionality ]**********************************************************************************************
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);
// ***************************************************************************************************************************


$qid = ((isset($qid))?$qid:$o_ticket->ticket_queue_id);

// [JAS]: Default Template Vars ]*********************************************************************************************

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('form_submit',$form_submit);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);
$cer_tpl->assign_by_ref('cerberus_disp',$cerberus_disp);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  'take_ticket' => cer_href("display.php?ticket=$ticket&qid=$qid&form_submit=take"),
			  'tab_display' => cer_href("display.php?ticket=".$o_ticket->ticket_id),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'save_layout' => "javascript:savePageLayout();",
 			  'print_ticket' => cer_href("printdisplay.php?level=ticket&ticket=".$o_ticket->ticket_id),
			  'contact_add' => cer_href("clients.php?mode=u_add" . (($o_ticket->requestor_address->address) ? "&add_email=" . $o_ticket->requestor_address->address : "") )
			  );
			  
$page = "display.php";
$cer_tpl->assign("page",$page);

$cer_tpl->assign_by_ref('errorcode',$errorcode);

// ***************************************************************************************************************************


// Display Ticket Nav Logic ]*************************************************************************************************
if($o_ticket->batch_id !== false && $priv->has_priv(ACL_TICKET_BATCH,BITGROUP_2))
{
	if($o_ticket->batch_id > 0)
		$urls['batch_prev'] = cer_href("display.php?ticket=" . $session->vars["login_handler"]->batch->tickets[$o_ticket->batch_id-1]);
	
	if($o_ticket->batch_id + 1 < count($session->vars["login_handler"]->batch->get_tickets()))
		$urls['batch_next'] = cer_href("display.php?ticket=" . $session->vars["login_handler"]->batch->tickets[$o_ticket->batch_id+1]);

	if($o_ticket->batch_id !== false && $priv->has_priv(ACL_TICKET_BATCH,BITGROUP_2))
		$urls['batch_remove'] = cer_href("display.php?ticket=$ticket&batch=remove");
}	

if($o_ticket->batch_id === false && $priv->has_priv(ACL_TICKET_BATCH,BITGROUP_2))
 	$urls['batch_add'] = cer_href("display.php?ticket=$ticket&batch=add");
// ***************************************************************************************************************************
 	

// [JAS]: Determine what tabs we're allowing the user to see ]****************************************************************
if($o_ticket->writeable)
{
 	$urls['tab_props'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties");
 	$urls['tab_merge'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties","merge");
}
 	
// [JAS]: This is manage requesters now
if($o_ticket->writeable && $priv->has_priv(ACL_ADD_REQUESTER,BITGROUP_2))
	$urls['tab_edit'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties");
	
if($o_ticket->writeable)
	$urls['tab_antispam'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=anti_spam");
	
if($priv->has_priv(ACL_TICKET_BATCH,BITGROUP_2))
	$urls['tab_batch'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=batch");
	
if($cfg->settings["enable_audit_log"] && $priv->has_priv(ACL_AUDIT_LOG,BITGROUP_2))
	$urls['tab_log'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=log");
// ***************************************************************************************************************************
		  


// [JAS]: [DISPLAY] Ticket at a Glance Functionality ]************************************************************************
if($o_ticket->writeable && $priv->has_priv(ACL_TICKET_CHSTATUS,BITGROUP_1)) {
	$ticket_glance_status_options = array();
	foreach($cer_hash->get_status_hash() as $st => $status)
		{ 
			if(!$priv->has_priv(ACL_TICKET_KILL,BITGROUP_2) // [JAS]: Restrict the 'dead' status
 				&& $status == LANG_STATUS_DEAD        		 // [JXD]: for non-english case
 				&& $o_ticket->ticket_status != LANG_STATUS_DEAD) // [JXD]: for non-english case
				{  }
			else
				{ $ticket_glance_status_options[$st] = $status; }
		}
	$cer_tpl->assign_by_ref('ticket_glance_status_options',$ticket_glance_status_options);
}

if($o_ticket->writeable && $priv->has_priv(ACL_TICKET_CHOWNER,BITGROUP_1)) {
	$ticket_glance_owner_options = array(0 => LANG_WORD_NOBODY);
	foreach($cer_hash->get_user_hash() as $idx => $owner) // [JAS]: Hash call needs to limit to only users who can see this queue
		{ $ticket_glance_owner_options[$owner->user_id] = $owner->user_login; }
	$cer_tpl->assign_by_ref('ticket_glance_owner_options',$ticket_glance_owner_options);
}

if($o_ticket->writeable && $priv->has_priv(ACL_TICKET_CHQUEUE,BITGROUP_1)) {
	$ticket_glance_queue_options = array();
	
	if($cfg->settings["user_only_assign_own_queues"])
		$hash_set = HASH_Q_READWRITE;
	else
		$hash_set = HASH_Q_ALL;
	
	foreach($cer_hash->get_queue_hash($hash_set) as $idx => $queue)
		{ $ticket_glance_queue_options[$queue->queue_id] = $queue->queue_name; }
	$cer_tpl->assign_by_ref('ticket_glance_queue_options',$ticket_glance_queue_options);
}

// [JSJ]: Added code to generate the priority dropdown list.
if($o_ticket->writeable && $priv->has_priv(ACL_TICKET_CHPRIORITY,BITGROUP_1)) { 
      $ticket_glance_priority_options = array();                             
      $cer_tpl->assign_by_ref('ticket_glance_priority_options', $cer_hash->get_priority_hash());
}
else {
	$o_ticket->ticket_priority_string = "Unassigned";
	foreach($cer_hash->get_priority_hash() as $idx => $pri)
		if($idx == $o_ticket->ticket_priority)
			$o_ticket->ticket_priority_string = $pri;
}

// ***************************************************************************************************************************

// Post Ticket Actions ]******************************************************************************************************
switch($mode)
{
	case "batch":
		
	// [JAS]: Create a view for batched tickets
		@$bv = $_REQUEST["bv"]; // batch view
		@$bv_sort_by = $_REQUEST["bv_sort_by"];
		@$bv_asc = $_REQUEST["bv_asc"];
		@$bv_p = $_REQUEST["bv_p"];
		
		if(isset($bv)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["bv"] = $bv; $bv_p = 0; }
		if(isset($bv_sort_by)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["bv_sort_by"] = $bv_sort_by; }
		if(isset($bv_asc)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["bv_asc"] = $bv_asc; }
		if(isset($bv_p)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["bv_p"] = $bv_p; }
		
		$b_view = new cer_TicketViewBatch($session->vars["login_handler"]->user_prefs->view_prefs->vars["bv"],array("batch_ids" => $o_ticket->batch->batch_ids));
		
		$cer_tpl->assign_by_ref('b_view',$b_view);
		break;
		
	case "anti_spam":
		break;
}
// ***************************************************************************************************************************

$cer_tpl->assign('mode',$mode);

$tabs = new CER_TICKET_DISPLAY_TABS($mode);
$cer_tpl->assign_by_ref('tabs',$tabs);

$time_entry_defaults["mdy"] = strftime("%m/%d/%y");
$time_entry_defaults["h"] = strftime("%I");
$time_entry_defaults["m"] = 5*(round(strftime("%M")/5));
$time_entry_defaults["ampm"] = strtolower(strftime("%p"));

	// [JAS]: See if we need to attach a set of custom fields to this entity
	$field_binding = new cer_CustomFieldBindingHandler();
	$custom_handler = new cer_CustomFieldGroupHandler();
	$bind_gid = $field_binding->getEntityBinding(ENTITY_TIME_ENTRY);
	
	// [JAS]: If we do have custom fields, store the custom field group template + ID
	if(!empty($bind_gid)) {
		$custom_handler->loadGroupTemplates();
		$time_entry_defaults["custom_gid"] = $bind_gid;
		$time_entry_defaults["custom_fields"] = $custom_handler->group_templates[$bind_gid];
	}

if(!empty($time_entry_defaults)) $cer_tpl->assign_by_ref('time_entry_defaults',$time_entry_defaults);

if(!empty($merge_error)) $cer_tpl->assign('merge_error',$merge_error);

$user_layout = &$session->vars["login_handler"]->user_prefs->layout_prefs;
$cer_tpl->assign_by_ref('user_layout',$user_layout);

$cer_tpl->assign('ticket',$ticket);
$cer_tpl->assign_by_ref('urls',$urls);
$cer_tpl->display("display.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
