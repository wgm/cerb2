<?php
define("REPORT_NAME","Agent Performance Report");
define("REPORT_SUMMARY","Email replies and comments listed by agent over a given date range.");
define("REPORT_TAG","user_performance");

//* \todo Implement sorting for the performance report by pulling output into an object rather than dumping from query to report
//require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_UserPerformanceReport();
	$report->generate_report();
	return $report;
}

class cer_UserPerformanceReport extends cer_ReportModule
{
	function generate_report()
	{
		global $mo_offset;
		
		$total_replies = 0;
		$total_comments = 0;
		
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;

		$this->_init_calendar();
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);
			
			$sql = "SELECT COUNT(  IF ( l.action = 5, 1,  NULL  ) )  AS DateCommentTotal, ".
				"COUNT(  IF ( l.action = 4, 1,  NULL  ) )  AS DateEmailTotal, ".
				"u.user_name, u.user_login, ".
				"DATE_FORMAT( l.timestamp,  '%a %b %d %Y'  )  AS TicketDate ".
				"FROM ticket_audit_log l ".
				"LEFT JOIN user u ON ( l.user_id = u.user_id ) ".
				"LEFT JOIN ticket t ON ( t.ticket_id = l.ticket_id ) ".
				"WHERE u.user_login !=  '' AND t.ticket_status NOT IN ('dead') ".
				"AND l.action IN ( 4, 5 ) ".
				sprintf("AND l.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
						$this->report_dates->from_date,
						$this->report_dates->to_date
						).
				"GROUP BY TicketDate, l.user_id ".
				"ORDER  BY l.timestamp ASC";
			$u_res = $this->db->query($sql);

			if($this->db->num_rows($u_res))
			{
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
				$new_row->cols[2] = new cer_ReportDataCol("Replies");
				$new_row->cols[3] = new cer_ReportDataCol("Comments");
				$new_row->cols[2]->align = "center";
				$new_row->cols[3]->align = "center";
				array_push($this->report_data->rows,$new_row);

				$last_date = null;
				
				while($ur = $this->db->fetch_row($u_res))
				{
					// [JAS]: Date Subsection
					if($ur["TicketDate"] != $last_date)
					{
						if($last_date != null)
						{
							// [JAS]: Blank row for space after each day
							$new_row = new cer_ReportDataRow();
							$new_row->bgcolor = "#E5E5E5";
							$new_row->cols[0] = new cer_ReportDataCol(SPACER_5PX);
							$new_row->cols[0]->col_span = 4;			
							array_push($this->report_data->rows,$new_row);
						}

						// [JAS]: Black Spacer
						$new_row = new cer_ReportDataRow();
						$new_row->bgcolor = "#000000";
						$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
						$new_row->cols[0]->col_span = 4;			
						array_push($this->report_data->rows,$new_row);

						$new_row = new cer_ReportDataRow();
						$new_row->style = "cer_maintable_headingSM";
						$new_row->bgcolor = "#CCCCCC";
						$new_row->cols[0] = new cer_ReportDataCol($ur["TicketDate"]);
						$new_row->cols[0]->col_span = 4;			
						array_push($this->report_data->rows,$new_row);

						// [JAS]: Black Spacer
						$new_row = new cer_ReportDataRow();
						$new_row->bgcolor = "#000000";
						$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
						$new_row->cols[0]->col_span = 4;			
						array_push($this->report_data->rows,$new_row);
					}

					// [JAS]: Data Rows
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#E5E5E5";
					$new_row->cols[0] = new cer_ReportDataCol("<b>".stripslashes($ur["user_name"])."</b>");
					$new_row->cols[1] = new cer_ReportDataCol($ur["user_login"]);
					$new_row->cols[2] = new cer_ReportDataCol($ur["DateEmailTotal"]);
					$new_row->cols[3] = new cer_ReportDataCol($ur["DateCommentTotal"]);
					$new_row->cols[2]->align = "center";
					$new_row->cols[3]->align = "center";
					$total_replies += $ur["DateEmailTotal"];
					$total_comments += $ur["DateCommentTotal"];
					array_push($this->report_data->rows,$new_row);

					// [JAS]: White Spacer
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#FFFFFF";
					$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
					$new_row->cols[0]->col_span = 4;			
					array_push($this->report_data->rows,$new_row);
					
					$last_date = $ur["TicketDate"];
				}
				
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
				$new_row->cols[2] = new cer_ReportDataCol($total_replies);
				$new_row->cols[3] = new cer_ReportDataCol($total_comments);
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