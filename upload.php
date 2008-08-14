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
| File: upload.php
|
| Purpose: Handles uploading of file attachments.
|
| Contributors:
|       Jeremy Johnstone (jeremy@scriptd.net)   [JSJ]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/fileuploads/fileuploads.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();

$cerberus_translate = new cer_translate;
$queue_access = new CER_QUEUE_ACCESS();
$audit_log = new CER_AUDIT_LOG();
$cerberus_format = new cer_formatting_obj();
$acl = new cer_admin_list_struct();
$file_upload = new CER_FILE_UPLOADER();

// [JSJ]: Set up the local variables from the scope objects
@$form_submit = $_REQUEST["form_submit"];
@$reply_attachment = $_FILES["reply_attachment"];
@$ticket = $_REQUEST["ticket"];
@$ticket_id = $_REQUEST["ticket_id"];
@$filelist = $_REQUEST["filelist"];
if(empty($ticket_id)) $ticket_id = $ticket;
if(!is_array($session->vars["uploaded_file_array"])) $session->vars["uploaded_file_array"] = array();

// [JSJ]: Initialize variables
$total_attachment_size = 0;
$file_name_array = array();
$file_name_list_array = array();
$file_name_array["0"] = " - Message Attachments - ";
	
if(isset($_REQUEST["form_upload"]) && $_REQUEST["form_upload"] == 1)
{
	if(!empty($reply_attachment["name"]) && $reply_attachment["size"] != 0) {
		$file_object = $file_upload->add_file($reply_attachment, $ticket_id, $session->vars["login_handler"]->user_id);
		array_push($session->vars["uploaded_file_array"], $file_object);	
	}
}
elseif(isset($_REQUEST["form_delete"]) && $_REQUEST["form_delete"] == 1)
{
	if(!empty($filelist))
	foreach($filelist as $delete)
	{ 
		$file_upload->delete_file($delete); 
		foreach($session->vars["uploaded_file_array"] as $idx=>$file_object)
		{ if($file_object->file_id == $delete) unset($session->vars["uploaded_file_array"][$idx]); }	
	}	
}
elseif(isset($_REQUEST["form_submit"]) && $_REQUEST["form_submit"] == 1)
{
     $cer_tpl->assign("update_and_close", "1");	
}

if(is_array($session->vars["uploaded_file_array"])) 
{
	if(!empty($session->vars["uploaded_file_array"]))
	foreach($session->vars["uploaded_file_array"] as $file)
	{ 
		$file_name_array["$file->file_id"] = $file->file_name . " (" . round($file->size/1024,1) . "k)"; 
		$total_attachment_size += round($file->size/1024,1);
	}

	// [JAS]: Get a select list ready for the create/display page list box, 
	//		without -message attachments- header
	if(!empty($file_name_array))
	foreach($file_name_array as $idx => $val)
	{
		if($idx != 0) array_push($file_name_list_array,$val);
	}
}


$cer_tpl->assign_by_ref('file_name_array',$file_name_array);
$cer_tpl->assign_by_ref('file_name_list_array',$file_name_list_array); // [JAS]: For bottom layer select
$cer_tpl->assign('total_attachment_size',$total_attachment_size."k");
$cer_tpl->assign('ticket_id',$ticket_id);
$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->display("fileupload.tpl.php");


//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************

?>
