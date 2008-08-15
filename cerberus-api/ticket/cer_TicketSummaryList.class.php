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
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketViewProcs.func.php");

class cer_TicketSummaryList {
	var $db = null;
	var $summary_title = null;
	var $tickets = array();
	
	function cer_TicketSummaryList() {
		$this->db = cer_Database::getInstance();
	}
	
	function loadCompanyTickets($c_id, $only_active=1) {
		$addy_ids = array();
		
		$sql = sprintf("SELECT a.address_id ".
				"FROM (address a, public_gui_users pu) ".
				"WHERE a.public_user_id = pu.public_user_id ".
				"AND pu.company_id = %d",
					$c_id
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$addy_ids[] = $row["address_id"];
			}
		}

		$this->_loadTicketsByAddressIDs($addy_ids, $only_active);
	}
	
	function loadUsersTickets($u_ids=array(), $only_active=1) {
		if(empty($u_ids)) return;
		
		$sql = sprintf("SELECT a.address_id ".
				"FROM (address a, public_gui_users pu) ".
				"WHERE a.public_user_id = pu.public_user_id ".
				"AND pu.public_user_id IN (%s) ",
					implode(",", $u_ids)
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$addy_ids[] = $row["address_id"];
			}
		}

		$this->_loadTicketsByAddressIDs($addy_ids, $only_active);
	}
	
	function _loadTicketsByAddressIDs($addy_ids = array(), $only_active=1) {
		
		if(empty($addy_ids)) return;
			
		$sql = sprintf("SELECT t.ticket_id, t.ticket_mask, t.ticket_subject, t.ticket_status, t.ticket_due, ".
					"t.ticket_date, thr.thread_date, a.address_address as last_wrote_address, q.queue_id, q.queue_name ".
				"FROM (ticket t, thread th, thread thr, address a, queue q) ".
				"WHERE th.thread_id = t.min_thread_id ".
				"AND t.ticket_queue_id = q.queue_id ".
				"AND thr.thread_id = t.max_thread_id ".
				"AND thr.thread_address_id = a.address_id ".
				"AND th.thread_address_id IN (%s) ".
				(($only_active) ? "AND t.ticket_status IN ('new','awaiting-reply','customer-reply','bounced') " : "").
				"ORDER BY ticket_due ASC",
					implode(",", $addy_ids)
			);
		$res = $this->db->query($sql);
		
		$this->_loadTicketsFromDbRes($res);
	}
	
	function _loadTicketsFromDbRes($res) {
		global $cerberus_translate;
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$ticket = new cer_TicketSummary();
				
				$ticket->ticket_id = $row["ticket_id"];
				$ticket->ticket_mask = (!empty($row["ticket_mask"])) ? stripslashes($row["ticket_mask"]) : $row["ticket_id"];
				$ticket->ticket_subject = stripslashes($cerberus_translate->translate_status($row["ticket_subject"]));
				$ticket->ticket_status = $row["ticket_status"];
				
				$date = new cer_DateTime($row["ticket_due"]);
				$ticket->ticket_due = $date->getUserDate("%Y-%m-%d %H:%M:%S");
				
				$date = new cer_DateTime($row["thread_date"]);
				$ticket->ticket_age = $date->getUserDate("%Y-%m-%d %H:%M:%S");
				
				$proc_args = new cer_TicketViewsProc($this);
				$proc_args->ticket_status = $ticket->ticket_status;
				$proc_args->ticket_due = $ticket->ticket_due;
				
				$ticket->ticket_due = view_proc_due_to_age($ticket->ticket_due,$proc_args);
				$ticket->ticket_age = view_proc_date_to_age($ticket->ticket_age,$proc_args);
				
				$ticket->queue_id = $row["queue_id"];
				$ticket->queue_name = stripslashes($row["queue_name"]);
				$ticket->queue_url = cer_href("ticket_list.php?queue_view=1&qid=" . $ticket->queue_id);
				
				$ticket->ticket_last_wrote_address = stripslashes($row["last_wrote_address"]);
				$ticket->ticket_url = cer_href("display.php?ticket=" . $ticket->ticket_id);
				
				$this->tickets[$ticket->ticket_id] = $ticket;
			}
		}
	}
	
};

class cer_TicketSummary {
	var $ticket_id = null;
	var $ticket_mask = null;
	var $ticket_subject = null;
	var $ticket_status = null;
	var $queue_id = null;
	var $queue_name = null;
	var $queue_url = null;
	var $ticket_due = null;
	var $ticket_age = null;
	var $ticket_last_wrote_address = null;
	var $ticket_url = null;
};

?>