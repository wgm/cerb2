<?php
define("REPORT_NAME","Average Initial Handle Time Report");
define("REPORT_SUMMARY","Average of amount of time elapsed between ticket opening and first response by an agent.");
define("REPORT_TAG","average_handle_time");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_AverageHandleTimeReport();
	$report->generate_report();
	return $report;
}

class cer_ReportUserResponseTime
{
	var $user_name = null;
	var $user_login = null;
	var $average_response_time = 0;
	var $samples = 0;
	var $response_times = array();
	var $tickets = array();
};

class cer_AverageHandleTimeReport extends cer_ReportModule
{
	
	function generate_report()
	{
		$acl = new cer_admin_list_struct();
		
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_calendar();
		$this->_init_user_list();
		$this->_init_queue_list();
		$this->_init_group_list();
		
		$report_user_id = $this->report_data->user_data->report_user_id;
		$report_queue_id = $this->report_data->queue_data->report_queue_id;
		$report_group_id = $this->report_data->group_data->report_group_id;
		
		// [JAS]: If the 'all' option is set, clear the filter.
		if($report_user_id == -1) $report_user_id = 0;
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
						   "FROM user u, user_access_levels g ".
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
			
		$sql = sprintf("SELECT log.ticket_id, log.epoch, log.user_id, log.action, u.user_name, u.user_login  ".
			"FROM `ticket_audit_log` log ".
			"LEFT JOIN `user` u USING (user_id) ".
			"LEFT JOIN `ticket` t ON (t.ticket_id = log.ticket_id) ".
			"WHERE log.action IN ( %d, %d ) ".
			"%s ".
			"AND log.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					1, // created
					4, // agent reply
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
			$user_times = array();
			
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
						if(empty($reply_pair[0])) {
							$reply_pair[0] = -1;
							$epoch_pair[0] = $rt["epoch"];
						}
					break;
					
					case 4: // Agent reply
						if(!empty($reply_pair[0])) { // [JAS]: only add if we have an open
							$reply_pair[1] = $rt["user_id"];
							$epoch_pair[1] = $rt["epoch"];
						}
					break;
				}
				
				// [JAS]: We have a pair.
				if(!empty($reply_pair[0]) && !empty($reply_pair[1]))
				{
					$uid = $reply_pair[1];
							
					// [JAS]: If the selected user doesn't match, ignore
					if (!empty($report_user_id) && $uid != $report_user_id)
						continue;
						
					// [JAS]: If the selected group doesn't contain this user, ignore
					if (!empty($report_group_id) && !isset($uids[$uid]))
						continue;
					
					if(!empty($rt["user_login"]))
					{
						$time_diff = ($epoch_pair[1] - $epoch_pair[0]);
						if(!isset($user_times[$reply_pair[1]])) {
							$user_times[$reply_pair[1]] = new cer_ReportUserResponseTime();
							$user_times[$reply_pair[1]]->user_name = $rt["user_name"];
							$user_times[$reply_pair[1]]->user_login = $rt["user_login"];
						}
						array_push($user_times[$reply_pair[1]]->response_times,$time_diff);
						array_push($user_times[$reply_pair[1]]->tickets,$rt["ticket_id"]);
					}
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
				}
				
				$last_ticket_id = $rt["ticket_id"];
			}
			
			foreach($user_times as $idx => $ut)
			{
				$avg_reply_time = 0;
				foreach($ut->response_times as $r_time)
					$avg_reply_time += $r_time;
				
				$user_times[$idx]->samples = count($user_times[$idx]->response_times);
				$user_times[$idx]->average_response_time = round($avg_reply_time / $user_times[$idx]->samples);
				unset($user_times[$idx]->response_times);
			}
			
//			echo "<pre>"; print_r($user_times); echo "</pre>";
			
			// [JAS]: If we have no user times to display, don't bother 
			//	drawing the report.  It will show "No data for range." 
			//	by default
			if (empty($user_times))
				return;
				
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Report Name
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#FF6600";
			$new_row->cols[0] = new cer_ReportDataCol($report_title);
			$new_row->cols[0]->col_span = 4;
			array_push($this->report_data->rows,$new_row);

			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);

			// [JAS]: Column Headings
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#AAAAAA";
			$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
			$new_row->cols[1] = new cer_ReportDataCol("Agent Login");
			$new_row->cols[2] = new cer_ReportDataCol("Samples");
			$new_row->cols[3] = new cer_ReportDataCol("Avg. Initial Response Time");
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
			
			$total_times = 0;
			$total_num_times = 0;
			$total_samples = 0;
			
			$system_avg = new cer_WeightedAverage();
			
			$sorted_user_times = cer_PointerSort::pointerSortCollection($user_times,"user_name");
			
			foreach($sorted_user_times as $idx => $user_time)
			{
				// [JAS]: Data Rows
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#E5E5E5";
				$new_row->cols[0] = new cer_ReportDataCol("<b>".$user_time->user_name."</b>");
				$new_row->cols[1] = new cer_ReportDataCol($user_time->user_login);
				$new_row->cols[2] = new cer_ReportDataCol($user_time->samples);
				$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_time->average_response_time));
				$new_row->cols[2]->align = "center";
				$new_row->cols[3]->align = "center";
				$total_times += $user_time->average_response_time;
				$total_num_times++;
				$total_samples += $user_time->samples;
				$system_avg->addSample($user_time->average_response_time,$user_time->samples);
				array_push($this->report_data->rows,$new_row);
	
				// [JAS]: White Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#FFFFFF";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 4;			
				array_push($this->report_data->rows,$new_row);
			}
			
			if($total_num_times)
			{
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 4;			
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Totals Heading
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#888888";
				$new_row->cols[0] = new cer_ReportDataCol("Agent Average Initial Handle Time");
				$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
				$new_row->cols[2] = new cer_ReportDataCol($total_num_times . " agents");
				$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_times/$total_num_times)));
				$new_row->cols[2]->align = "center";
				$new_row->cols[3]->align = "center";
				array_push($this->report_data->rows,$new_row);

				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 4;			
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Totals Heading
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#888888";
				$new_row->cols[0] = new cer_ReportDataCol("System Average Initial Handle Time (weighted)");
				$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
				$new_row->cols[2] = new cer_ReportDataCol($total_samples . " samples");
				$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($system_avg->getAverage()));
				$new_row->cols[2]->align = "center";
				$new_row->cols[3]->align = "center";
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 4;			
				array_push($this->report_data->rows,$new_row);
			}
			
		}
	}
	
};

?>