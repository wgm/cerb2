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
| File: update.php
|
| Purpose: Handle comments and replies on tickets.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted	  (ben@webgroupmedia.com)   [BGH]
|
| Contributors:
|       Jeremy Johnstone (jeremy@scriptd.net)   [JSJ]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/update_ticket.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

require_once(FILESYSTEM_PATH . "includes/functions/general.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php"); // for translate_status
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/xsp_master_gui.php");

$cerberus_disp = new cer_display_obj;
$queue_access = new CER_QUEUE_ACCESS();
$audit_log = new CER_AUDIT_LOG();
$cerberus_translate = new cer_translate;
$cerberus_format = new cer_formatting_obj();

$cer_tpl = new CER_TEMPLATE_HANDLER();

@$qid = $_REQUEST["qid"];
@$ticket = $_REQUEST["ticket"];
@$ticket_id = $_REQUEST["ticket_id"];
if(empty($ticket_id)) $ticket_id = $ticket;
@$type = $_REQUEST["type"];
@$thread = $_REQUEST["thread"];
@$use_queue_address = $_REQUEST["use_queue_address"];
@$ticket_cc = $_REQUEST["ticket_cc"];
@$ticket_forward = $_REQUEST["ticket_forward"];
@$ticket_status = $_REQUEST["ticket_status"];
@$ticket_owner = $_REQUEST["ticket_owner"];
@$ticket_queue = $_REQUEST["ticket_queue"];
@$ticket_priority = $_REQUEST["ticket_priority"];
@$ticket_time_worked = $_REQUEST["ticket_time_worked"];
@$thread_type = $_REQUEST["thread_type"];
@$ticket_response = $_REQUEST["ticket_response"];
@$reply_attachment = $_FILES["reply_attachment"];
@$initial_owner = $_REQUEST["initial_owner"];
@$initial_status = $_REQUEST["initial_status"];
@$initial_queue = $_REQUEST["initial_queue"];
@$initial_priority = $_REQUEST["initial_priority"];
@$quote = $_REQUEST["quote"];
@$ticket_cc_add_reqs = $_REQUEST["ticket_cc_add_reqs"];
@$next_action = $_REQUEST["next_action"];

// Batch Action Variables
@$mode = $_REQUEST["mode"];
@$bids = $_REQUEST["bids"];
@$batch_action = $_REQUEST["batch_action"];
@$form_submitx = $_REQUEST["form_submitx"];

if($type=="reply" || $type=="forward" || $batch_action=="reply") { 
	log_user_who_action(WHO_REPLY_TICKET,$ticket);
}
else {
	log_user_who_action(WHO_COMMENT_TICKET,$ticket);
}
		
function is_batch()
{
	Global $mode;
	if(@$mode == "batch") return true;
	else return false;
}
		
// [JAS]: Set up scope for batch and non-batch uses of this page
if(is_batch())
$type = $batch_action;

if(isset($_REQUEST["form_submit"]) && strlen($batch_action != ""))
{ $bids = explode(",",$bids); }

function parse_quote_blocks($str)
{
	$offset = 0;
	$o_qp = strpos($str,"[quote]",$offset);
	$parsed = "";
	while($o_qp !== false) // 7
	{
		$c_qp = strpos($str,"[/quote]",$o_qp); // 8
		
		if($c_qp < $o_qp) // [JAS]: if tags were entered backwards, swap
		{	$t_qp = $c_qp; $c_qp = $o_qp; $o_qp = $t_qp; $ol=8; $cl=7;  }
		else
		{ $ol=7; $cl=8; }
		
		if($o_qp > $offset)
		$parsed .= substr($str,$offset,$o_qp-$offset);
		
		$quote_chunk = substr($str,$o_qp+$ol,$c_qp-$o_qp-$ol);
		$quote_chunk = wordwrap($quote_chunk,78,"\r\n");
		$quote_chunk = "> " . str_replace("\r\n","\r\n> ",$quote_chunk);
		$parsed .= $quote_chunk;
		$offset = $c_qp+$cl;
		$o_qp = strpos($str,"[quote]",$offset);
	}
	$parsed .= substr($str,$offset);
	
	return $parsed;
}

function process_outgoing_email($ticket,$email_message,$send_autoclose=false)
{
	$cfg = CerConfiguration::getInstance();
	$cerberus_db = cer_Database::getInstance();
	global $session;
	
	// post
	global $reply_attachment;
	global $thread_type;
	global $audit_log;
	global $thread;
	global $ticket_cc;
	global $ticket_cc_add_reqs;
 	global $ticket_forward;
	global $ticket_time_worked;
	global $use_queue_address;
	
	if(!(-1<$ticket_time_worked)) {	$ticket_time_worked=0; }

	$errors = false;
	$error_log = array();
	
	//$message = str_replace("\\","\\\\",$email_message);
	$message = $email_message;
	$message = stripcslashes($message);

	$cer_parser = new CER_PARSER();
	$cer_ticket = new CER_PARSER_TICKET();
	$cer_email = new CERB_RAW_EMAIL();
	$cer_search = new cer_SearchIndexEmail();
	
	$cer_ticket->load_ticket_data($ticket);
	
	// [JAS]: If the queue address was changed on reply.
	if(!empty($use_queue_address)) {
		$cer_ticket->set_queue_address_id($use_queue_address);
	}
	
	$message_from = $session->vars["login_handler"]->user_email;
	$cer_ticket->set_requester($message_from);

//	if(!empty($thread) && $thread_type = "")
//		$cer_email->import_thread_attachments($thread,$cerberus_db);
	
	// [JSJ]: Changed code to allow multiple attachments.
	$attachment_id = count($cer_email->attachments);
    if(is_array($session->vars["uploaded_file_array"])) 
    {
    	$file_uploader = new CER_FILE_UPLOADER();
    	
		foreach($session->vars["uploaded_file_array"] as $file)
		{ 
			$cer_email->add_attachment();
			$cer_email->attachments[$attachment_id]->filename = $file->file_name;
			$cer_email->attachments[$attachment_id]->filesize = $file->size;
			$cer_email->attachments[$attachment_id]->content_type = $file->browser_mimetype;
			
			if($fp = fopen($file->temp_name,"rb"))
			{
				while($file_content = fread($fp,512000)) // [JAS]: ~1MB chunks
				{ 
					$tmp_dir_path = FILESYSTEM_PATH . "tempdir"; // [BGH]: set the temp dir path
					$tmp_name = tempnam($tmp_dir_path, "cerb");
					$tp = fopen($tmp_name,"wb");
					if($tp)
					{
						$chunk_len = strlen(str_replace(chr(0)," ",$file_content)); // [JAS]: Don't stop counting on a NULL
						fwrite($tp,$file_content);
						array_push($cer_email->attachments[$attachment_id]->tmp_files,$tmp_name);
					}
				}
			}
			
			@fclose($fp);
			$file_uploader->delete_file($file->file_id);
			$attachment_id++;
		}
	}

	//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
	if(isset($session) && method_exists($session,"save_session"))
	{ $session->save_session(); }
	//*********************************
	
	$cer_email->body = $message;
	$cer_email->headers->from = $message_from;
	$cer_email->headers->subject = $cer_ticket->ticket_subject;

	// [JAS]: Did we have addresses to CC to as well?
	$cc_add_reqs = false;
	
	if(!empty($ticket_cc))
	{
		$cc_addys = split(",",$ticket_cc);
		foreach($cc_addys as $cc_addy) {
			$req_email = trim($cc_addy);
			array_push($cer_email->headers->cc,$req_email);
			if($ticket_cc_add_reqs) $audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$req_email);			
		}
		
		// [JAS]: If we're adding requesters from CC, remove the override CC list -- they'll get a copy of the reply during proxy
		if($ticket_cc_add_reqs) 
		{ 
			$cc_add_reqs = true; 
			$ticket_cc = "";
		}
	}
	
	// [BGH] add a message-id for local host. this will be added to the thread.thread_message_id field
	// code somewhat taken from htmlMimeMail.php
	$cer_email->headers->message_id = $cer_email->generate_message_id();
	
	$thread_id = $cer_ticket->add_ticket_thread($cer_email,$thread_type,false,$cc_add_reqs);
	
	$cer_ticket->save_thread_time_worked($thread_id,$ticket_time_worked);
	
	// [JAS]: If this was an email and not a comment, send the message to all the requesters
	if($thread_type == "email") {
		$error_check = $cer_parser->proxy_email_to_requesters($cer_email,$cer_ticket,$ticket_cc,true);
		if(is_array($error_check) && count($error_check)) {
			$errors = true;
			$error_msg = sprintf("Could not send e-mail to requester list. (<b>%s</b>)",implode("; ",$error_check));
			array_push($error_log,$error_msg);
		}
 	}
 
 	if($thread_type == "forward") {
 		$error_check = $cer_parser->send_email_to_address($ticket_forward,$cer_email,$cer_ticket,"",true);
 		if(is_array($error_check) && count($error_check)) {
 			$errors = true;
 			$error_msg = sprintf("Could not forward e-mail to address %s (<b>%s</b>)",$ticket_forward,implode("; ",$error_check));
 			array_push($error_log,$error_msg);
 		}
	}

	if($send_autoclose) {
		$cer_parser->send_closeresponse($cer_ticket);
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
	}

	$cer_parser->process_mail_rules(RULE_TYPE_POST,$cer_email,$cer_ticket,$audit_log);
	$cer_search->indexSingleTicket($cer_ticket->ticket_id);
}

// [JAS]: Single Reply/Comment
if(isset($_REQUEST["form_submit"]) && !is_array($bids))
{
	// [JAS]: Parse all [quote] and [/quote] blocks.
	$ticket_response = parse_quote_blocks($ticket_response);
	
	// [BGH]: Make sure we have write queue access.
	if($queue_access->has_write_access($qid)) {
		
		// [JAS]: Audit log helpdesk user reply/comment
		$extra = "";
		
		if($thread_type=="comment")
			$do_action = AUDIT_ACTION_COMMENTED;
		elseif ($thread_type=="email")
			$do_action = AUDIT_ACTION_REPLIED;
		elseif ($thread_type=="forward") {
			$do_action = AUDIT_ACTION_THREAD_FORWARD;
			$extra = $ticket_forward;
		}
		$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,$do_action,$extra);
		
		// [JAS]: Audit log status change
		$newstatus = false;
		if($initial_status != $ticket_status) {
			$newstatus=true;
			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_STATUS,$ticket_status);
		}
		
		// [JAS]: Audit log owner change
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
				
		// [JAS]: If any ticket properties were changed on reply, save
		$sql = "UPDATE ticket SET ticket_queue_id = $ticket_queue, ticket_priority = $ticket_priority, ticket_status = '$ticket_status', ticket_assigned_to_id = $ticket_owner WHERE ticket_id = $ticket_id";
		$cerberus_db->query($sql);
		
		if($newstatus && $ticket_status=="resolved") $autoclose = true; else $autoclose = false;
		process_outgoing_email($ticket,$ticket_response,$autoclose);
	}

	// Send satellite status updates to the master GUI about the
	//	ticket's property changes
	if($cfg->settings["satellite_enabled"])
	{
		$acl = new cer_admin_list_struct();
		$xsp_upd = new xsp_login_manager();
		$xsp_upd->register_callback_acl($acl,"is_admin");
		$xsp_upd->xsp_send_summary($ticket_id);
	}
	
	switch($next_action)
	{
		case "queue":
			header("Location: " . cer_href("ticket_list.php?queue_view=1&qid=$qid"));
		break;
		case "search":
			header("Location: " . cer_href("ticket_list.php"));
		break;
		case "home":
			header("Location: " . cer_href("index.php"));
		break;
		case "batch":
			header("Location: " . cer_href("display.php?mode=batch&ticket=$ticket_id"));
		break;
		default:
		case "details":
			header("Location: " . cer_href("display.php?ticket=$ticket_id"));
		break;
	}
	exit();
}

// [JAS]: Batch Reply/Comment
else if(isset($_REQUEST["form_submit"]) && count($bids))
{
	// [JAS]: Get batch ticket id + original status pairs
	$sql = "SELECT t.ticket_id,t.ticket_status, t.ticket_assigned_to_id,t.ticket_queue_id FROM ticket t ".
		"WHERE t.ticket_id IN (". implode(",",$bids) .")";
	$result = $cerberus_db->query($sql);

	// [JAS]: Update ticket properties based on reply screen values
	$sql = "UPDATE ticket SET ticket_queue_id = '$ticket_queue', ticket_priority = '$ticket_priority', ticket_status = '$ticket_status', ticket_assigned_to_id = $ticket_owner WHERE ticket_id IN (" . implode(",",$bids) . ")";
	$cerberus_db->query($sql);
	
	while($batch_data = $cerberus_db->fetch_row($result))
	{
		if($batch_data["ticket_status"] != $ticket_status) 
		{ $newstatus = true; } else { $newstatus = false; }

		if($newstatus && $ticket_status=="resolved") $autoclose = true; else $autoclose = false;
		process_outgoing_email($batch_data["ticket_id"],$ticket_response,$autoclose);
	}

	header("Location: " . cer_href("display.php?ticket=$ticket_id&mode=batch"));
	exit();
}


// [JAS]: Load Ticket Information ]*******************************************************************************************
$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_status, t.ticket_assigned_to_id, t.ticket_queue_id, t.ticket_priority, ".
	"t.ticket_date, t.queue_addresses_id, ad.address_id, ad.address_address, q.queue_name, t.ticket_mask " .
"FROM (ticket t, thread th, address ad, queue q) " .
"WHERE th.ticket_id = t.ticket_id AND t.ticket_queue_id = q.queue_id AND th.thread_address_id = ad.address_id AND t.ticket_id = $ticket";
$result = $cerberus_db->query($sql);
$ticket_data = $cerberus_db->fetch_row($result);

// [JAS] If the status is 'new' set to 'awaiting-reply' automatically
if($type=="reply" && "new"==$ticket_data["ticket_status"]) {
	$ticket_data["ticket_status"] = "awaiting-reply";
}

$o_ticket = new CER_TICKET_UPDATE();
$o_ticket->set_ticket_id($ticket_data["ticket_id"]);
$o_ticket->set_ticket_mask($ticket_data["ticket_mask"]);
$o_ticket->set_ticket_subject($ticket_data["ticket_subject"]);
$o_ticket->set_ticket_status($ticket_data["ticket_status"]);
$o_ticket->set_ticket_owner($ticket_data["ticket_assigned_to_id"]);
$o_ticket->set_requestor_address($ticket_data["address_id"],$ticket_data["address_address"]);
$o_ticket->set_ticket_queue($ticket_data["ticket_queue_id"]);
$o_ticket->set_ticket_queue_name($ticket_data["queue_name"]);
$o_ticket->set_ticket_queue_address_id($ticket_data["ticket_addresses_id"]);
$o_ticket->set_ticket_priority($ticket_data["ticket_priority"]);
$o_ticket->set_ticket_date($ticket_data["ticket_date"]);
$o_ticket->set_ticket_queue_address_id($ticket_data["queue_addresses_id"]);
$o_ticket->build_update();
$o_ticket->quote_thread($thread,$quote);
if(isset($thread) && $type == "forward") $o_ticket->quote_thread_attachments($thread);

$cer_tpl->assign_by_ref('o_ticket',$o_ticket);
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
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign('qid',$qid);

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
			  'tab_display' => cer_href("display.php?ticket=".$o_ticket->ticket_id),
			  'show_templates' => cer_href("update_show_templates.php?ticket_id=".$o_ticket->ticket_id),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'iframe_threads' => cer_href("display_ticket_thread.php?ticket=".$ticket."&type=".$type)
			  );
			  
$page = "update.php";
$cer_tpl->assign("page",$page);
		  
// ***************************************************************************************************************************


// [JAS]: Determine what tabs we're allowing the user to see ]****************************************************************
if($o_ticket->writeable)
 	$urls['tab_props'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties");
 	
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
	{   // [JXD]: correct handling for non-english case
		if(!$priv->has_priv(ACL_TICKET_KILL,BITGROUP_2) // [JAS]: Restrict the 'dead' status
			&& $status == LANG_STATUS_DEAD 
			&& $o_ticket->ticket_status != LANG_STATUS_DEAD)
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

// [BGH]: added version check for pspell because we need the regexp offset in 4.3.0 or greater
$pspell_loaded = false;
if(-1!=version_compare( phpversion(), "4.3.0")) {
	if(extension_loaded("pspell")) {
		$pspell_loaded = true;
	}
}

// [JSJ]: Added code to generate list of attached filenames
$file_name_array = array();
if(is_array($session->vars["uploaded_file_array"])) 
{
	foreach($session->vars["uploaded_file_array"] as $file)
	{ 
		array_push($file_name_array, $file->file_name . " (" . round($file->size/1024,1) . "k)"); 
		$total_attachment_size += round($file->size/1024,1);
	}
}

$cer_tpl->assign_by_ref('file_name_array', $file_name_array);

$tabs = new CER_TICKET_DISPLAY_TABS("");

$cer_tpl->assign_by_ref('tabs',$tabs);
$cer_tpl->assign('mode',$mode);
$cer_tpl->assign('type',$type);
$cer_tpl->assign('batch_action',$batch_action);
$cer_tpl->assign('thread',$thread);
$cer_tpl->assign('pspell_loaded',$pspell_loaded);
$cer_tpl->assign('next_ticket_details', sprintf(LANG_UPDATE_NEXT_DETAILS, $o_ticket->ticket_mask_id));
$cer_tpl->assign("next_ticket_queue", sprintf(LANG_UPDATE_NEXT_QUEUE, $o_ticket->ticket_queue_name));
$cer_tpl->assign_by_ref('urls',$urls);

$cer_tpl->display("update.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************

?>