<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
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
| File: system_status.class.php
|
| Purpose: Object to drive the system status box on Home
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/stats/cer_SystemStats.class.php");

class CER_SYSTEM_STATUS_QUEUE
{
	var $queue_id = 0;
	var $queue_name = null;
	var $queue_url = null;
	var $queue_bar_width = 0;
	var $queue_active_tickets = 0;
	
	function CER_SYSTEM_STATUS_QUEUE($q_id=0,$q_name="",$q_url="",$q_bar=0)
	{
		$this->queue_id = $q_id;
		$this->queue_name = $q_name;
		$this->queue_url = $q_url;
		$this->queue_bar_width = $q_bar;
	}
};

class CER_SYSTEM_TICKET_STATUS_ITEM
{
	var $status = null;
	var $status_url = null;
	var $count = 0;
};

class CER_SYSTEM_STATUS_DAY_ITEM
{
	var $mysql_timestamp;
	var $day_total = 0;
	
	function CER_SYSTEM_STATUS_DAY_ITEM($d_mysql_timestamp=0,$d_total=0)
	{
		$cerberus_translate = new cer_translate;	
		$this->mysql_timestamp = $d_mysql_timestamp;
		$this->day_total = $d_total;
	}
	
};

class CER_SYSTEM_STATUS
{
	var $db = null;
	var $queue_grand_totals = 0;
	var $queue_list = array();
	var $queue_tree = array();
	var $queue_totals = array();
	var $day_totals = array();
	var $total_tickets = 0;
	var $total_threads = 0;
	var $total_articles = 0;
	var $total_addresses = 0;

	function CER_SYSTEM_STATUS()
	{
		$this->db = cer_Database::getInstance();
		
		$this->load_day_totals();
		$this->load_statistics();
		$this->load_statuses();
	}

	function load_queue_data_by_uid($user_id=0)
	{
		global $cer_hash; // fix
		global $queue_access;
		
		if($user_id==0) return false;
		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();
		
		$u_qids = $queue_access->get_readable_qid_list();

		// [BGH]: get the Queue lists so we can build an object and won't 
		// have to left join in the next SQL query
		$sql = sprintf("SELECT `q`.`queue_id`,`q`.`queue_name`,`q`.`queue_addresses_inherit_qid` ".
						"FROM `queue` q ".
						"WHERE `q`.`queue_id` IN (%s) ".
						"ORDER BY queue_addresses_inherit_qid ASC, `queue_name` ASC ",
							$u_qids
						);
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result))
		{
			while(@$qr = $this->db->fetch_row($result))
			{
				$queue_id = $qr["queue_id"];
				$parent_qid = $qr["queue_addresses_inherit_qid"];
				$this->queue_totals[$queue_id] = 0;
				
				// [JAS]: Store our top-level queues
				if(0 == $parent_qid || !$queue_access->has_read_access($parent_qid)) {
					if(!isset($queue_raw_tree[$queue_id]))
						$queue_raw_tree[$queue_id] = array();
				} else {
					if(!isset($queue_raw_tree[$parent_qid]))
						$queue_raw_tree[$parent_qid] = array();
					
					// [JAS]: Add the child queue
					$queue_raw_tree[$parent_qid][$queue_id] = $queue_id;
				}
				
			}
		}

		$sql = "SELECT COUNT(*) as total,t.ticket_queue_id ".
			"FROM ticket t ".
			"WHERE t.ticket_queue_id IN ($u_qids) ".
			"AND t.ticket_status NOT IN ('awaiting-reply', 'resolved', 'dead') ". // makes custom statuses active
			"GROUP BY t.ticket_queue_id ";

		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result))
		{
			// [JSJ] : No point in looping over data twice, just do it in one pass.
			while(@$qr = $this->db->fetch_row($result))
			{ 
				$this->queue_grand_totals += $qr["total"]; 
				$queue_id = $qr["ticket_queue_id"];
				$this->queue_totals[$queue_id] = $qr["total"];
			}

		}

		foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $idx => $queue)
		{
			$queue_item = new CER_SYSTEM_STATUS_QUEUE($queue->queue_id,$queue->queue_name);
			$queue_item->queue_url = cer_href(sprintf("ticket_list.php?queue_view=1&qid=%d",$queue_item->queue_id));
			$queue_item->queue_active_tickets = sprintf("%d",@$this->queue_totals[$queue_item->queue_id]);
			
			if($this->queue_grand_totals)
				$queue_item->queue_bar_width = round((($queue_item->queue_active_tickets / $this->queue_grand_totals) * 100)/2);

			$this->queue_list[$queue->queue_id] = $queue_item;
			
			// [JAS]: Piggyback on realphabetizing the tree top level nodes. If subqueues are visible and their parents
			//	are not, we've moved them to the top level (which disturbs the SQL alphabetizing);
			if(isset($queue_raw_tree[$queue->queue_id])) {
				$this->queue_tree[$queue->queue_id] = $queue_raw_tree[$queue->queue_id];
			}
		}
		
	}
	
	function load_day_totals()
	{
		global $session;
		
		$day_count = 0;
		$cer_stats = new cer_SystemStats();
		$datetime = new cer_DateTime(date("Y-m-d H:i:s"));
		$server_time = $datetime->mktime_datetime;
		$datetime->changeGMTOffset($session->vars["login_handler"]->user_prefs->gmt_offset);
		
		// break this down into 7 queries for systems with large numbers of tickets
		for($day_offset=0; $day_offset<7; $day_offset++) {
			$stats = null;
			$date = $datetime->mktime_datetime;
			$date-=$day_offset*86400; // subtract day offset

			$stats = $cer_stats->getTicketCount($date);
//			list($dayname,$month,$day) = sscanf(strftime("%a %b %d", $date),"%s %s %d");
			$day_item = new CER_SYSTEM_STATUS_DAY_ITEM($date,$stats[0]);
			array_push($this->day_totals,$day_item);
		} // for
	}
	
	function load_statistics()
	{
		$sql = "select count(*) from ticket";
		$result = $this->db->query($sql,false);
		$t_row = $this->db->fetch_row($result);
		$sql = "select count(*) from thread";
		$result = $this->db->query($sql,false);
		$th_row = $this->db->fetch_row($result);
		$sql = "select count(*) from address";
		$result = $this->db->query($sql,false);
		$a_row = $this->db->fetch_row($result);
		$sql = "select count(*) from knowledgebase";
		$result = $this->db->query($sql,false);
		$k_row = $this->db->fetch_row($result);
		
		$this->total_addresses = $a_row[0];
		$this->total_articles = $k_row[0];
		$this->total_threads = $th_row[0];
		$this->total_tickets = $t_row[0];
	}
	
	function load_statuses() {
		$this->statuses = array();
		
		require_once(FILESYSTEM_PATH ."cerberus-api/ticket/cer_TicketStatuses.class.php");
		$ticket_status_handler = cer_TicketStatuses::getInstance();
		$ticket_status_handler->computeTicketStatusCounts();
		
		// [JAS]: Populate rows (and zero out empty statuses)
		foreach($ticket_status_handler->getTicketStatuses() as $st) {
			$this->statuses[$st] = new CER_SYSTEM_TICKET_STATUS_ITEM();
			$this->statuses[$st]->status = $st;
			$this->statuses[$st]->status_url = cer_href(sprintf("ticket_list.php?qid=&queue_view=1&search_status=%s",$st));
		}
		
		// [JAS]: Load counts
		foreach($ticket_status_handler->getTicketStatusCounts() as $st => $ct) {
			$this->statuses[$st]->count = $ct;
		}
	}
};

?>
