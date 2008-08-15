<?php
/************************************************************************
| Modifications to Cerberus Helpdesk(tm) developed by ScriptDevelopers 
|------------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: printdisplay.php
|
| Purpose: Page which handles printing of tickets and threads.
|
| Developers involved with this file: 
|	Jeremy Johnstone  (jsjohnst@scriptdevelopers.net)  [JSJ]
|
| _______________________________________________________________________
|	http://www.scriptdevelopers.net/  http://www.cerberusweb.com
************************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_translate = new cer_translate;
$queue_access = new CER_QUEUE_ACCESS();
$cerberus_format = new cer_formatting_obj();

@$ticket = $_REQUEST["ticket"];
@$thread = $_REQUEST["thread"];
@$level = $_REQUEST["level"];

$u_qids = $queue_access->get_readable_qid_list();

$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_status, t.ticket_date, t.ticket_assigned_to_id, t.ticket_queue_id, t.ticket_priority, th.thread_address_id, ad.address_address, t.queue_addresses_id, q.queue_name, t.min_thread_id, t.max_thread_id, t.ticket_reopenings, t.ticket_mask " .
"FROM (ticket t, thread th, address ad, queue q) ".
"WHERE t.ticket_queue_id IN ($u_qids) AND th.ticket_id = t.ticket_id AND t.ticket_queue_id = q.queue_id AND th.thread_address_id = ad.address_id AND t.ticket_id = $ticket GROUP BY th.thread_id LIMIT 0,1";

$result = $cerberus_db->query($sql);
if($cerberus_db->num_rows($result) == 0) { 
	header("Location: ".cer_href("index.php?errorcode=NOACCESS&errorvalue=" . urlencode($_REQUEST["ticket"])));
	exit; 
}
$ticket_data = $cerberus_db->fetch_row($result);

$o_ticket = new CER_TICKET_DISPLAY();
$o_ticket->set_ticket_id($ticket_data["ticket_id"]);
$o_ticket->set_ticket_mask($ticket_data["ticket_mask"]);
$o_ticket->set_ticket_subject($ticket_data["ticket_subject"]);
$o_ticket->set_ticket_date($ticket_data["ticket_date"]);
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
$o_ticket->build_ticket();

$cer_tpl->assign_by_ref("o_ticket",$o_ticket);

$cer_tpl->assign('thread',$thread);
$cer_tpl->assign('printlevel',$level);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign('qid',$qid);

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);
$cer_tpl->assign_by_ref('cerberus_disp',$cerberus_disp);

$cer_tpl->assign('ticket',$ticket);
$cer_tpl->assign_by_ref('urls',$urls);
$cer_tpl->display("print/printdisplay.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
