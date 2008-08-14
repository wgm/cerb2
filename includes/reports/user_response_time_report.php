<?php
define("_REPORT_NAME","User Average Response Time Report");
define("_REPORT_SUMMARY","Average ticket response times listed by user.");
define("_REPORT_TAG","user_response_time");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_UserResponseTimeReport();
	$report->generate_report();
	return $report;
}

class cer_ReportUserResponseTime
{
	var $user_name = null;
	var $user_login = null;
	var $avg_response_time = 0;
	var $samples = 0;
	var $response_times = array();
};

class cer_UserResponseTimeReport extends cer_ReportModule
{
	
	function generate_report()
	{
		$acl = new cer_admin_list_struct();
		
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_calendar();
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);
			
		$sql = sprintf("SELECT log.ticket_id, log.epoch, log.user_id, log.action, u.user_name, u.user_login  ".
			"FROM `ticket_audit_log` log ".
			"LEFT JOIN `user` u USING (user_id) ".
			sprintf("WHERE log.action IN ( %d,%d,%d ) ".
					"AND log.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					1, // created
					4, // agent reply
					8, // requester reply
					$this->report_dates->from_date,
					$this->report_dates->to_date
					).
			"ORDER BY log.ticket_id, log.timestamp");
		$rt_res = $this->db->query($sql);

		// [JAS]: If we have data for factoring response time
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
					case 1:
					case 8: // Requester message
						if(empty($reply_pair[0])) {
							$reply_pair[0] = -1;
							$epoch_pair[0] = $rt["epoch"];
						}
					break;
					
					case 4: // Staff reply
						if(!empty($reply_pair[0])) { // [JAS]: only add if we have a req
							$reply_pair[1] = $rt["user_id"];
							$epoch_pair[1] = $rt["epoch"];
						}
					break;
				}
				
				// [JAS]: We have a pair.
				if(!empty($reply_pair[0]) && !empty($reply_pair[1]))
				{
					if(!empty($rt["user_login"]))
					{
						$time_diff = ($epoch_pair[1] - $epoch_pair[0]);
						if(!isset($user_times[$reply_pair[1]])) {
							$user_times[$reply_pair[1]] = new cer_ReportUserResponseTime();
							$user_times[$reply_pair[1]]->user_name = $rt["user_name"];
							$user_times[$reply_pair[1]]->user_login = $rt["user_login"];
						}
						array_push($user_times[$reply_pair[1]]->response_times,$time_diff);
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
			$new_row->cols[0] = new cer_ReportDataCol("User Name");
			$new_row->cols[1] = new cer_ReportDataCol("User Login");
			$new_row->cols[2] = new cer_ReportDataCol("Samples");
			$new_row->cols[3] = new cer_ReportDataCol("Average Response Time");
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
			foreach($user_times as $idx => $user_time)
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
				$new_row->cols[0] = new cer_ReportDataCol("Grand Totals");
				$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
				$new_row->cols[2] = new cer_ReportDataCol($total_samples);
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
			}
			
		}
	}
	
};

?>