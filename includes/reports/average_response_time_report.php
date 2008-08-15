<?php
define("REPORT_NAME","Average Response Time Report");
define("REPORT_SUMMARY","The average amount of time taken to respond to each requester email over a date range, by queue, agent or group.");
define("REPORT_TAG","average_response_time");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/reports/cer_ReportAverageHandleTime.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_AverageResponseTimeReport();
	$report->generate_report();
	return $report;
}

class cer_ReportUserResponseTime
{
	var $user_name = null;
	var $user_login = null;
	var $average_response_time = 0;
	var $average_forward_time = 0;
	var $r_samples = 0;
	var $f_samples = 0;
	var $r_response_times = array(); // reply times
	var $f_response_times = array(); // forward times
	var $tickets = array();
};

class cer_AverageResponseTimeReport extends cer_ReportModule
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

		if(!empty($report_user_id)) {
			$uids[$report_user_id] = 1;
		}
		
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
		
		$params = array(
			"from_date" => $this->report_dates->from_date,
			"to_date" => $this->report_dates->to_date,
			"queue_id" => $report_queue_id
			);
			
		$userAvgHandleTime = new cer_ReportAverageHandleTime();
		$user_times = $userAvgHandleTime->getUserAverageHandleTimes($uids,$params);
			
		// [JAS]: If we have no user times to display, don't bother 
		//	drawing the report.  It will show "No data for range." 
		//	by default
		if (empty($user_times))
			return;
			
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 5;			
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Report Name
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#FF6600";
		$new_row->cols[0] = new cer_ReportDataCol($report_title);
		$new_row->cols[0]->col_span = 5;
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 5;			
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Column Headings
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#AAAAAA";
		$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
		$new_row->cols[1] = new cer_ReportDataCol("Agent Login");
		$new_row->cols[2] = new cer_ReportDataCol("Replies/Forwards");
		$new_row->cols[3] = new cer_ReportDataCol("Avg. Response Time");
		$new_row->cols[4] = new cer_ReportDataCol("Avg. Forward Time");
		$new_row->cols[2]->align = "center";
		$new_row->cols[3]->align = "center";
		$new_row->cols[4]->align = "center";
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 5;			
		array_push($this->report_data->rows,$new_row);
		
		$total_times = 0;
		$total_r_count = 0;
		$total_f_count = 0;
		$total_samples = 0;
		$total_agents = 0;
		
		$system_r_avg = new cer_WeightedAverage();
		$system_f_avg = new cer_WeightedAverage();
		
		$sorted_user_times = cer_PointerSort::pointerSortCollection($user_times,"user_name");
		
		foreach($sorted_user_times as $idx => $user_time)
		{
			$total_agents++;
			
			// [JAS]: Data Rows
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#E5E5E5";
			$new_row->cols[0] = new cer_ReportDataCol("<b>".$user_time->user_name."</b>");
			$new_row->cols[1] = new cer_ReportDataCol($user_time->user_login);
			$new_row->cols[2] = new cer_ReportDataCol($user_time->r_samples . " / " . $user_time->f_samples);
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_time->average_response_time));
			$new_row->cols[4] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_time->average_forward_time));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			$new_row->cols[4]->align = "center";
			
			if($user_time->average_response_time !== null) {
				$total_r_times += $user_time->average_response_time;
				$total_r_samples += $user_time->r_samples;
				$system_r_avg->addSample($user_time->average_response_time,$user_time->r_samples);
				$total_r_count++;
			}
			
			if($user_time->average_forward_time !== null) {
				$total_f_times += $user_time->average_forward_time;
				$total_f_samples += $user_time->f_samples;
				$system_f_avg->addSample($user_time->average_forward_time,$user_time->f_samples);
				$total_f_count++;
			}
			
			array_push($this->report_data->rows,$new_row);

			// [JAS]: White Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#FFFFFF";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
		}
		
		if($total_r_count || $total_f_count)
		{
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Totals Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol("Agent Average Response/Forward Times");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_agents . " agents");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_r_times/$total_r_count)));
			$new_row->cols[4] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_f_times/$total_f_count)));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			$new_row->cols[4]->align = "center";
			array_push($this->report_data->rows,$new_row);

			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Totals Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol("System Average Response/Forward Times (weighted)");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_r_samples . " / " . $total_f_samples . " samples");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($system_r_avg->getAverage()));
			$new_row->cols[4] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($system_f_avg->getAverage()));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			$new_row->cols[4]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
		}
		
	}
};

?>