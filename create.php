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
| File: create.php
|
| Purpose: Create a ticket from the GUI interface and have it sent
|		through the appropriate queue address to the parser.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted   (ben@webgroupmedia.com)	[BGH]
|
| Contributors:
|		J. X Demel		Forums					[JXD]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/stats/cer_SystemStats.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/user/user_prefs.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/xsp_master_gui.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

if(!$priv->has_priv(ACL_CREATE_TICKET,BITGROUP_3))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

log_user_who_action(WHO_CREATE);

// [JXD]: Are we adding CC'd addresses to the requester list automatically?
$auto_cc_req = (($cfg->settings["auto_add_cc_reqs"]) ? true : false);

// [JAS]: Set up the local variable scope from the scope objects
@$qid = $_REQUEST["qid"];
@$nt_to = $_REQUEST["nt_to"];
@$nt_requestor = $_REQUEST["nt_requestor"];
@$nt_cc = $_REQUEST["nt_cc"];
@$nt_subject = $_REQUEST["nt_subject"];
@$ticket_response = $_REQUEST["ticket_response"];
@$form_submit = $_REQUEST["form_submit"];
@$nt_suppress_autoreply = $_REQUEST["nt_suppress_autoreply"];
@$nt_suppress_email = $_REQUEST["nt_suppress_email"];

// [JSJ]: Set up the local variable for the file attachment
@$create_attachment = $_FILES["create_attachment"];  

if(!isset($qid)) { echo LANG_CONFIG_QUEUE_EDIT_NOID; exit(); }

$cer_tpl = new CER_TEMPLATE_HANDLER();

if(isset($form_submit))
{
	$errors = false;
	$error_log = array();
	
	// [JAS]: If we weren't given a "to" address for this queue, fail
	$mail_to = @$nt_to;
	if(strlen($mail_to) == 0) die("Cerberus [ERROR]: No destination queue address selected.");
	
	if($nt_subject == "" || !isset($nt_subject)) $nt_subject = "No Subject";
	
	$cer_parser = new CER_PARSER();
	$cer_ticket = new CER_PARSER_TICKET();
	$cer_email = new CERB_RAW_EMAIL();
	$cer_trigram = new cer_TrigramEmail();
	$cer_search = new cer_SearchIndexEmail();
	$cer_stats = new cer_SystemStats();
	$cer_audit_log = new CER_AUDIT_LOG();
	
	// [JAS]: Simulate an inbound e-mail
	$cer_email->headers->to = $mail_to;
	$cer_email->headers->from = $nt_requestor;
	$cer_email->headers->subject = $nt_subject;
	
	// [JSJ]: Message-ID Fix (thanks to westr for diagnosing this)
	  // Create a new Message-ID Header and add it to the message!
	  $cer_email->headers->message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
	// End Fix

 	// [JXD]:
	if($nt_cc)
 	{
		$cer_email->headers->raw_cc = $nt_cc;
		if(!empty($nt_cc))
		{
//			$RFC822 = new Mail_RFC822(); 
//			$address = $RFC822->parseAddressList(trim($nt_cc), 'localhost', TRUE);
//			foreach($address as $addy)
//			{ array_push($cer_email->headers->cc, $addy->mailbox . "@" . $addy->host); }

			$cc_addys = split(",",$nt_cc);
			foreach($cc_addys as $cc_addy)
			{
				array_push($cer_email->headers->cc,trim($cc_addy));
			}

		}
 	} 

	$cer_email->body = stripcslashes($ticket_response);
	
	// [JSJ]: Add attachment (if exists) to the new email
	$attachment_id = 0;
    if(is_array($session->vars["uploaded_file_array"])) 
    {
    	$file_uploader = new CER_FILE_UPLOADER();
    	
		foreach($session->vars["uploaded_file_array"] as $file)
		{ 
			$cer_email->add_attachment();
			$cer_email->attachments[$attachment_id]->filename = $file->file_name;
			$cer_email->attachments[$attachment_id]->filesize = $file->size;
			$cer_email->attachments[$attachment_id]->content_type = $file->browser_mimetype;
			
			$fp = fopen($file->temp_name,"rb");
			if($fp)
				while($file_content = fread($fp,512000)) // [JAS]: ~1MB chunks
				{ 
					$tmp_dir_path = FILESYSTEM_PATH . "tempdir"; // [BGH]: set the temp dir path
					$tmp_name = tempnam($tmp_dir_path, "cerb");
					$tp = fopen($tmp_name,"wb");
					if($tp)
					{
						fwrite($tp,$file_content,strlen($file_content));
						fclose($tp);
						array_push($cer_email->attachments[$attachment_id]->tmp_files,$tmp_name);
					}
				}
			@fclose($fp);
			
			$file_uploader->delete_file($file->file_id);
			$attachment_id++;
		}
	}

	// [JAS]: Get/Create the Requester Address ID
	$cer_ticket->requester_id = $cer_ticket->get_address_id($cer_email->headers->from);
	$cer_ticket->requester_address = $cer_email->headers->from;

	// [JAS]: Create the new ticket
	if(!$dest_queue = $cer_ticket->get_dest_queue_data($cer_email)) die("Cerberus [ERROR]: Didn't match a queue in TO/CC.");
	$cer_ticket->create_new_ticket($nt_subject,$dest_queue);
	$cer_ticket->save_requester_link($cer_ticket->ticket_id,$cer_ticket->requester_id);
	$cer_audit_log->log_action($cer_ticket->ticket_id,0,AUDIT_ACTION_OPENED,$dest_queue->queue_id);
 	$thread_id = $cer_ticket->add_ticket_thread($cer_email,"email",true,$auto_cc_req);   // [JXD]
	
	// [JAS]: Send out the auto response, if set & active.
	if($dest_queue->has_enabled_autoresponse() && empty($nt_suppress_autoreply)) { 
		// [JAS]: If we failed sending the mail, this ticket shouldn't be added to database
		$error_check = $cer_parser->send_autoresponse($dest_queue,$cer_ticket);
		if(is_array($error_check) && count($error_check)) { 
			$errors = true;
			$error_msg = sprintf("Could not send autoresponse e-mail. (%s)",implode("; ",$error_check));
			array_push($error_log,$error_msg);
		}
	}
	
	// [JSJ]: Send mail to all watchers for the queue.
	$error_check = $cer_parser->send_email_to_watchers($cer_email,$cer_ticket,"", $thread_type, true);
	if(is_array($error_check) && count($error_check))
	{
		$errors = true;
		$error_msg = sprintf("Could not send e-mail to watchers. (<b>%s</b>)",implode("; ",$error_check));
		array_push($error_log,$error_msg);
	}

	// [JAS]: Send a copy of the new ticket message straight to the recipient
	if(empty($nt_suppress_email)) {
		$error_check = $cer_parser->proxy_email_to_requesters($cer_email,$cer_ticket,$nt_cc,true);
		if(is_array($error_check) && count($error_check)) { 
			$errors = true;
			$error_msg = sprintf("Could not send e-mail to requester list. (<b>%s</b>)",implode("; ",$error_check));
			array_push($error_log,$error_msg);
		}
	}

	// [JAS]: If we had errors sending e-mail above, log them.
	if($errors && is_array($error_log) && count($error_log))
	{
		$ticket_errors = new CER_TICKET_THREAD_ERRORS();
		$ticket_errors->log_thread_errors($thread_id,$cer_ticket->ticket_id,$error_log);
	}
	
	// [JAS]: Process parser mail rules for this e-mail
	$cer_parser->process_mail_rules(RULE_TYPE_POST,$cer_email,$cer_ticket,$cer_audit_log);

	// [JAS]: Trigger the New Ticket Notification
	$notification = new CER_NOTIFICATION();
	$notification->trigger_event(NOTIFY_EVENT_NEW_TICKET,$cer_ticket->ticket_id);

	// [BGH]: Search index the email
	$cer_search->indexSingleTicketSubject($cer_ticket->ticket_id);
	$cer_search->indexSingleTicket($cer_ticket->ticket_id);

	// [BGH]: Update the daily stats
	$cer_stats->incrementTicket($dest_queue->queue_id);
	
	// Send satellite status updates to the master GUI about the
	//	ticket's property changes
	if($cfg->settings["satellite_enabled"])
	{
		$acl = new cer_admin_list_struct();
		$xsp_upd = new xsp_login_manager();
		$xsp_upd->register_callback_acl($acl,"is_admin");
		$xsp_upd->xsp_send_summary($cer_ticket->ticket_id);
	}
	
	header("Location: ".cer_href("display.php?ticket=".$cer_ticket->ticket_id)); // go to ticket list
}

// Determine queue addresses for dropdown ]***********************************************************************************
$queue_handler = new cer_QueueHandler(array($qid));
$queue =& $queue_handler->queues[$qid];

if(!empty($queue->queue_addresses)) {
	foreach($queue->queue_addresses as $qa_id => $qa) {
		$queue_addresses[$qa] = $qa;
	}
}


$cer_tpl->assign_by_ref('queue_addresses',$queue_addresses);
// ***************************************************************************************************************************

// ***************************************************************************************************************************
// [JAS]: Header Functionality
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);
// ***************************************************************************************************************************

$uid = $session->vars["login_handler"]->user_id;
$user_prefs = new CER_USER_PREFS($uid);

$cer_tpl->assign_by_ref('user_prefs',$user_prefs);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);
$cer_tpl->assign('qid',((isset($qid))?$qid:0));

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);

// [JSJ]: Added code to generate list of attached filenames
$file_name_array = array();
if(is_array($session->vars["uploaded_file_array"])) 
{
	foreach($session->vars["uploaded_file_array"] as $file)
	{ 
		array_push($file_name_array, $file->file_name . "&nbsp; &nbsp;(" . round($file->size/1024,1) . "k)"); 
		$total_attachment_size += round($file->size/1024,1);
	}
}

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$cer_tpl->assign_by_ref('file_name_array', $file_name_array);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'clients' => cer_href("clients.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'reports' => cer_href("reports.php"),
			  'show_templates' => cer_href("update_show_templates.php?ticket_id=0")
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "create.php";
$cer_tpl->assign("page",$page);

$cer_tpl->display('create.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
