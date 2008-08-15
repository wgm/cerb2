<?php
define("REPORT_NAME","Service Level Report - (by response time)");
define("REPORT_SUMMARY","What % of e-mail was responded to by an agent within a given number of hours.");
define("REPORT_TAG","service_level_report");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_ServiceLevelReport();
	$report->generate_report();
	return $report;
}

class cer_ReportServiceLevel
{
	var $queues = array();
	var $sorted_queues = array(); // [JAS]: Pointer array hook
	var $replies = 0;
	var $replies_match = 0;
	var $percent = 0.0;
	
	function sort() {
		foreach($this->queues as $idx => $q) {
			$this->queues[$idx]->sorted_groups = cer_PointerSort::pointerSortCollection($this->queues[$idx]->groups,"group_name");
		}
		$this->sorted_queues = cer_PointerSort::pointerSortCollection($this->queues,"queue_name");
		
		return true;
	}
};

class cer_ReportServiceLevelQueue
{
	var $queue_name = null;
	var $queue_replies = 0;
	var $queue_replies_match = 0;
	var $queue_percent = 0.0;
	var $groups = array();
	var $sorted_groups = array(); // [JAS]: Pointer array hook
};

class cer_ReportServiceLevelGroup
{
	var $group_name = null;
	var $group_replies = 0;
	var $group_replies_match = 0;
	var $group_percent = 0.0;
};

class cer_ServiceLevelReport extends cer_ReportModule
{
	var $within_seconds = 0; // [JAS]: Replied within this many secs.
	var $report_search_text = null;
	var $service_level = null;
	
	function cer_ServiceLevelReport() {
		$this->cer_ReportModule();
		$this->service_level = new cer_ReportServiceLevel();
	}
	
	function generate_report()
	{
		$acl = new cer_admin_list_struct();
		
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_calendar();
		$this->_init_queue_list();
		$this->_init_group_list();
		
		@$report_search_text = $_REQUEST["report_search_text"];
		$this->report_search_text = (($report_search_text) ? $report_search_text : 24); // [JAS]: Default to 24h
		
	  	$this->within_seconds = (int) $this->report_search_text * 60 * 60; // hours->mins->secs
	  	
		$report_queue_id = $this->report_data->queue_data->report_queue_id;
		$report_group_id = $this->report_data->group_data->report_group_id;
		
		// [JAS]: If the 'all' option is set, clear the filter.
		if($report_queue_id == -1) $report_queue_id = 0;
		if($report_group_id == -1) $report_group_id = 0;
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);

		$uids = array(); // All user IDs from the selected group (csv)
			
		// [JAS]: If we're filtering by group, make a list of user IDs from a group
		if (!empty($report_group_id)) {
			$sql = sprintf("SELECT u.user_id ".
						   "FROM (user u, user_access_levels g) ".
						   "WHERE u.user_group_id = g.group_id ".
						   "AND g.group_id = %d",
						$report_group_id
					);
			$g_res = $this->db->query($sql);
			
			if ($this->db->num_rows($g_res)) {
				while($g_row = $this->db->fetch_row($g_res)) {
					$uids[$g_row["user_id"]] = 1;
				}
			}
		}
			
		$sql = sprintf("SELECT log.ticket_id, log.epoch, log.user_id, log.action, u.user_name, u.user_login, t.ticket_queue_id, u.user_group_id, g.group_name, q.queue_name  ".
			"FROM `ticket_audit_log` log ".
			"LEFT JOIN `user` u USING (user_id) ".
			"LEFT JOIN `user_access_levels` g ON (u.user_group_id = g.group_id) ".
			"LEFT JOIN `ticket` t ON (t.ticket_id = log.ticket_id) ".
			"LEFT JOIN `queue` q ON (q.queue_id = t.ticket_queue_id) ".
			"WHERE log.action IN ( %d, %d, %d ) ".
			"%s ".
			"AND log.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					1, // created
					4, // agent reply
					8, // requester reply
					(($report_queue_id) ? "AND t.ticket_queue_id = $report_queue_id " : ""),
					$this->report_dates->from_date,
					$this->report_dates->to_date
					).
			"ORDER BY log.ticket_id, log.timestamp";
		$rt_res = $this->db->query($sql);
		
		// [JAS]: If we have data for factoring initial response time
		$row_count = $this->db->num_rows($rt_res);
		if($row_count && $row_count > 1)
		{
			$last_ticket_id = -1;
			
			while($rt = $this->db->fetch_row($rt_res))
			{
				// [JAS]: If we're analyzing a new ticket, reset our pair search
				if($rt["ticket_id"] != $last_ticket_id)
				{
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
				}
				
				// [JAS]: Isolate a reply from requester and a staff member in that order.  Ignore anything
				//		that breaks this pattern.  Use earliest instances of both (i.e. a customer writing back
				//		3 times before a reply should have response time factored from message 1 alone.
				
				switch($rt["action"])
				{
					case 1: // Ticket open
					case 8: // Requester reply
						if(empty($reply_pair[0])) {
							$reply_pair[0] = -1;
							$epoch_pair[0] = $rt["epoch"];
						}
					break;
					
					case 4: // Agent reply
						if(!empty($reply_pair[0])) { // [JAS]: only add if we have a staff reply first
							$reply_pair[1] = $rt["user_id"];
							$epoch_pair[1] = $rt["epoch"];
						}
					break;
				}
				
				// [JAS]: We have a pair.
				if(!empty($reply_pair[0]) && !empty($reply_pair[1]))
				{
					$uid = $reply_pair[1];
					$qid = $rt["ticket_queue_id"];
					$gid = $rt["user_group_id"];
							
					// [JAS]: If the selected group doesn't contain this user, ignore
					if (!empty($report_group_id) && !isset($uids[$uid]))
						continue;
					
					if(!empty($rt["user_login"])) // [JAS]: If user wasn't deleted
					{
						$time_diff = ($epoch_pair[1] - $epoch_pair[0]);
						$this->service_level->replies++;

						// [JAS]: Queue
						if(!isset($this->service_level->queues[$qid])) {
							$this->service_level->queues[$qid] = new cer_ReportServiceLevelQueue();
							$this->service_level->queues[$qid]->queue_name = stripslashes($rt["queue_name"]);
						}
						
						$this->service_level->queues[$qid]->queue_replies++;
						
						// [JAS]: Queue->Group
						if(!isset($this->service_level->queues[$qid]->groups[$gid])) {
							$this->service_level->queues[$qid]->groups[$gid] = new cer_ReportServiceLevelGroup();
							$group_name = (($rt["group_name"]) ? stripslashes($rt["group_name"]) : "Unassigned");
							$this->service_level->queues[$qid]->groups[$gid]->group_name = $group_name;
						}

						$this->service_level->queues[$qid]->groups[$gid]->group_replies++;
						
						// [JAS]: If this reply was within the given time limit, increment
						if($time_diff <= $this->within_seconds) {
							$this->service_level->replies_match++;
							$this->service_level->queues[$qid]->queue_replies_match++;
							$this->service_level->queues[$qid]->groups[$gid]->group_replies_match++;
						}
						
					}
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
				}
				
				$last_ticket_id = $rt["ticket_id"];
			}
			
			// [JAS]: If we have no queues to display, don't bother 
			//	drawing the report.  It will show "No data for range." 
			//	by default
			if (empty($this->service_level->queues))
				return;
			
			if($this->service_level->replies_match) {	
				$this->service_level->percent = 
					number_format(($this->service_level->replies_match / $this->service_level->replies) * 100, 2);
			}
				
			foreach($this->service_level->queues as $qid => $q) {
				$q_ptr = &$this->service_level->queues[$qid];
				if($q_ptr->queue_replies_match)
					$q_ptr->queue_percent = 
						number_format(($q_ptr->queue_replies_match / $q_ptr->queue_replies) * 100, 2);
					
				foreach($q_ptr->groups as $gid => $g) {
					$g_ptr = &$this->service_level->queues[$qid]->groups[$gid];
					if($g_ptr->group_replies_match) {
						$g_ptr->group_percent = 
							number_format(($g_ptr->group_replies_match / $g_ptr->group_replies) * 100, 2);
					}
				}
			}
				
//			echo "<pre>"; print_r($this->service_level); echo "</pre>";
					
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 3;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Report Name
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#FF6600";
			$new_row->cols[0] = new cer_ReportDataCol($report_title);
			$new_row->cols[0]->col_span = 3;
			array_push($this->report_data->rows,$new_row);

			$this->service_level->sort();
			
			foreach($this->service_level->sorted_queues as $q)
			{
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 3;			
				array_push($this->report_data->rows,$new_row);
			
				// [JAS]: Queue Name
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#AAAAAA";
				$new_row->cols[0] = new cer_ReportDataCol("Queue: " . $q->queue_name);
				$new_row->cols[0]->col_span = 3;
				array_push($this->report_data->rows,$new_row);
	
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 3;			
				array_push($this->report_data->rows,$new_row);
			
				// [JAS]: Column Headings
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_headingSM";
				$new_row->bgcolor = "#CCCCCC";
				$new_row->cols[0] = new cer_ReportDataCol("Group");
				$new_row->cols[1] = new cer_ReportDataCol("Email Handled");
				$new_row->cols[2] = new cer_ReportDataCol("Resolved within " . $this->report_search_text . " hour(s)");
				$new_row->cols[1]->align = "center";
				$new_row->cols[2]->align = "center";
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 3;			
				array_push($this->report_data->rows,$new_row);
				
				foreach($q->sorted_groups as $g) {
					// [JAS]: Data Rows
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#E5E5E5";
					$new_row->cols[0] = new cer_ReportDataCol("<b>".$g->group_name."</b>");
					$new_row->cols[1] = new cer_ReportDataCol($g->group_replies);
					$new_row->cols[2] = new cer_ReportDataCol($g->group_percent . "%");
					$new_row->cols[1]->align = "center";
					$new_row->cols[2]->align = "center";
					array_push($this->report_data->rows,$new_row);
					
					// [JAS]: White Line
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#FFFFFF";
					$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
					$new_row->cols[0]->col_span = 3;			
					array_push($this->report_data->rows,$new_row);
				}
				
				// [JAS]: Subtotal Heading
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_headingSM";
				$new_row->bgcolor = "#D0D0D0";
				$new_row->cols[0] = new cer_ReportDataCol();
				$new_row->cols[1] = new cer_ReportDataCol($q->queue_replies);
				$new_row->cols[2] = new cer_ReportDataCol($q->queue_percent . "%");
				$new_row->cols[1]->align = "center";
				$new_row->cols[2]->align = "center";
				array_push($this->report_data->rows,$new_row);
			}
			
			if(count($this->service_level->queues))
			{
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 3;			
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Totals Heading
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#888888";
				$new_row->cols[0] = new cer_ReportDataCol("Grand Total:");
				$new_row->cols[1] = new cer_ReportDataCol($this->service_level->replies);
				$new_row->cols[2] = new cer_ReportDataCol($this->service_level->percent . "%");
				$new_row->cols[1]->align = "center";
				$new_row->cols[2]->align = "center";
				array_push($this->report_data->rows,$new_row);

				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 3;			
				array_push($this->report_data->rows,$new_row);
			}
			
		}
	}
	
};

?>