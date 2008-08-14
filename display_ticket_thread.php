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
| File: display_ticket_thread.php
|
| Purpose: Used to review a ticket's thread list on the reply screen.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();

if(!isset($ticket))
	@$ticket = $_REQUEST["ticket"];
if(!isset($iframe))
	@$iframe = $_REQUEST["iframe"];  

@$type = $_REQUEST["type"];

if(!isset($ticket) || empty($ticket))
	{ exit(); }

if($type=="reply") {
	log_user_who_action(WHO_REPLY_TICKET,$ticket); }
else {
	log_user_who_action(WHO_COMMENT_TICKET,$ticket); }

$u_qids = $queue_access->get_readable_qid_list();
	
$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_status, t.ticket_date, t.ticket_assigned_to_id, t.ticket_queue_id, t.ticket_priority, th.thread_address_id, ad.address_address, t.queue_addresses_id, q.queue_name " .
	"FROM ticket t, thread th, address ad, queue q " . 
	"WHERE t.ticket_queue_id IN ($u_qids) AND th.ticket_id = t.ticket_id AND t.ticket_queue_id = q.queue_id AND th.thread_address_id = ad.address_id AND t.ticket_id = " . $ticket . " GROUP BY th.thread_id LIMIT 0,1";
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
  $o_ticket->set_ticket_status($ticket_data["ticket_status"]);
  $o_ticket->set_ticket_owner($ticket_data["ticket_assigned_to_id"]);
  $o_ticket->set_ticket_priority($ticket_data["ticket_priority"]);
  $o_ticket->set_ticket_queue($ticket_data["ticket_queue_id"]);
  $o_ticket->set_ticket_queue_address_id($ticket_data["queue_addresses_id"]);
  $o_ticket->set_ticket_queue_name($ticket_data["queue_name"]);
  $o_ticket->set_requestor_address($ticket_data["thread_address_id"],$ticket_data["address_address"]);
  $o_ticket->build_ticket("DESC");

  $cer_tpl->assign_by_ref('iframe',$iframe);
  $cer_tpl->assign_by_ref('o_ticket',$o_ticket);

  $cer_tpl->display('display/iframe/display_ticket_threads_iframe.tpl.php');
?>