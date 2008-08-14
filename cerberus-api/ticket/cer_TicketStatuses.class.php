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
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");

class cer_TicketStatuses {
	
	function getInstance() {
		static $instance = NULL;
		
		if($instance == NULL) {
			$instance = new cer_TicketStatuses();
		}
		
		return $instance;
	}
	
	function cer_TicketStatuses() {
		$cerberus_db = cer_Database::getInstance();
		$this->statuses = array();
		$this->permanent_statuses = array(
				"new"=>"new",
				"awaiting-reply"=>"awaiting-reply",
				"customer-reply"=>"customer-reply",
				"bounced"=>"bounced",
				"resolved"=>"resolved",
				"dead"=>"dead"
			);
		$matches = array();
		
		$sql = "DESCRIBE `ticket` `ticket_status`";
		$res = $cerberus_db->query($sql);
		
		if(!$row = $cerberus_db->grab_first_row($res))
			$this->failStatuses();
			
		$status_raw = $row["Type"];
		
		if(empty($status_raw))
			$this->failStatuses();
		
		preg_match("/enum\(\'(.*?)\'\)/i",$status_raw,$matches);
		
		if(empty($matches[1]))
			return $this->failStatuses();
		
		$statuses = explode("','",$matches[1]);
		
		if(empty($statuses))
			$this->failStatuses();
			
		foreach($statuses as $st) {
			$this->statuses[$st] = $st;
		}
	}
	
	function reload() {
		$this->cer_TicketStatuses();
	}
	
	function computeTicketStatusCounts() {
		global $queue_access;

		// [JAS]: [TODO] This really should be a singleton.
		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();
		
		$cerberus_db = cer_Database::getInstance();
		$this->status_counts = array();
		
		$u_qids = $queue_access->get_readable_qid_list();
		
		$sql = sprintf("SELECT count(t.ticket_id) as status_count, t.ticket_status ".
				"FROM `ticket` t ".
				"WHERE t.`ticket_queue_id` IN (%s) ".
				"GROUP BY t.ticket_status",
					$u_qids					
			);
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res)) {
			while($row = $cerberus_db->fetch_row($res)) {
				$this->status_counts[stripslashes($row["ticket_status"])] = $row["status_count"];
			}
		}
	}
	
	function getTicketStatuses() {
		if(!empty($this->statuses))
			return $this->statuses;
		else
			return array();
	}
	
	function getTicketStatusCounts() {
		if(!empty($this->status_counts))
			return $this->status_counts;
		else
			return array();
	}
	
	function failStatuses() {
		die("Cerberus [ERROR]: Cannot read or parse `ticket`.`ticket_status`");
	}
	
};

?>