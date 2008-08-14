<?php
define("REPORT_NAME","Queue Summary Report");
define("REPORT_SUMMARY","Date range breakdown by queue for each agent showing average handle time and number of e-mails handled.");
define("REPORT_TAG","queue_summary_report");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/reports/cer_ReportAverageHandleTime.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_QueueSummaryReport();
	$report->generate_report();
	return $report;
}

class cer_QueueSummaryReport extends cer_ReportModule
{
	function generate_report()
	{
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_calendar();
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);

		$uids = array(); // All user IDs from the selected group (csv)

		$params = array(
				"from_date" => $this->report_dates->from_date,
				"to_date" => $this->report_dates->to_date,
				"queue_id" => $report_queue_id
				);
				
		$AvgHandleTime = new cer_ReportAverageHandleTime();
		$queue_times = $AvgHandleTime->getQueueUserAverageHandleTimes($uids,$params);
		
		// [JAS]: If we have no user times to display, don't bother 
		//	drawing the report.  It will show "No data for range." 
		//	by default
		if (empty($queue_times))
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

		$total_times = 0;
		$total_num_times = 0;
		$total_samples = 0;
		
		$total_queue_times = 0;
		$total_queues = 0;
		
		$system_avg = new cer_WeightedAverage();
		$sorted_queue_times = cer_PointerSort::pointerSortCollection($queue_times,"queue_name");
		
		foreach($sorted_queue_times as $queue_time)
		{
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
		
			// [JAS]: Queue Name
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#AAAAAA";
			$new_row->cols[0] = new cer_ReportDataCol("Queue: " . $queue_time->queue_name);
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
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#CCCCCC";
			$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
			$new_row->cols[1] = new cer_ReportDataCol("Agent Login");
			$new_row->cols[2] = new cer_ReportDataCol("Email Handled");
			$new_row->cols[3] = new cer_ReportDataCol("Avg. Response Time");
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);

			$sorted_users = cer_PointerSort::pointerSortCollection($queue_time->user_times,"user_name");
			
			foreach($sorted_users as $user_time) {
				// [JAS]: Data Rows
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#E5E5E5";
				$new_row->cols[0] = new cer_ReportDataCol("<b>".$user_time->user_name."</b>");
				$new_row->cols[1] = new cer_ReportDataCol($user_time->user_login);
				$new_row->cols[2] = new cer_ReportDataCol($user_time->r_samples);
				$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_time->average_response_time));
				$new_row->cols[2]->align = "center";
				$new_row->cols[3]->align = "center";
				array_push($this->report_data->rows,$new_row);
				
				$total_times += $user_time->average_response_time;
				$total_num_times++;
				$total_samples += $user_time->r_samples;
				$system_avg->addSample($user_time->average_response_time,$user_time->r_samples);
	
				// [JAS]: White Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#FFFFFF";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 4;			
				array_push($this->report_data->rows,$new_row);
			}
			
			// [JAS]: Subtotal Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#D0D0D0";
			$new_row->cols[0] = new cer_ReportDataCol();
			$new_row->cols[1] = new cer_ReportDataCol();
			$new_row->cols[2] = new cer_ReportDataCol($queue_time->r_samples);
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($queue_time->average_response_time));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			$total_queue_times += $queue_time->average_response_time;
			$total_queues++;
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
			$new_row->cols[0] = new cer_ReportDataCol("Queue Averages");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_queues . " queues");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_queue_times/$total_queues)));
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
			$new_row->cols[0] = new cer_ReportDataCol("System Averages (weighted)");
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
};

?>