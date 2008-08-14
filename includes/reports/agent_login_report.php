<?php
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

define("REPORT_NAME","Agent Login Report");
define("REPORT_SUMMARY","Display Logins per day for users.");
define("REPORT_TAG","agent_login_report");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");

//* \todo Implement sorting for the performance report by pulling output into an object rather than dumping from query to report
//require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_AgentLoginReport();
	$report->generate_report();
	return $report;
}

class cer_AgentLoginReport extends cer_ReportModule
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
			
			$sql = "SELECT l.*, ".
			    "DATE_FORMAT( l.local_time_login,  '%a %b %d %Y'  )  AS LoginDate, ".	
			    "u.user_name, u.user_login ".
			    "FROM user_login_log l, user u ".
				"WHERE l.user_id = u.user_id ".
				sprintf("AND  l.local_time_login BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
						$this->report_dates->from_date,
						$this->report_dates->to_date
						).
				"ORDER  BY l.local_time_login ASC";
			$u_res = $this->db->query($sql);
			
			$col_span = 5;
			$totalLogins = "0";

			if($this->db->num_rows($u_res))
			{
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = $col_span;			
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Report Name
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#FF6600";
				$new_row->cols[0] = new cer_ReportDataCol($report_title);	
				$new_row->cols[0]->col_span = $col_span;
				array_push($this->report_data->rows,$new_row);

				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = $col_span;			
				array_push($this->report_data->rows,$new_row);

				// [JAS]: Column Headings
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#AAAAAA";
				$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
				$new_row->cols[1] = new cer_ReportDataCol("Login");
				$new_row->cols[2] = new cer_ReportDataCol("Logout");
				$new_row->cols[3] = new cer_ReportDataCol("Total Login");
				$new_row->cols[4] = new cer_ReportDataCol("IP Address");
				$new_row->cols[1]->align = "left";
				$new_row->cols[2]->align = "left";
				$new_row->cols[3]->align = "right";
				$new_row->cols[4]->align = "center";
				array_push($this->report_data->rows,$new_row);

				$last_date = null;
				
				
				while($ur = $this->db->fetch_row($u_res))
				{
					// [JAS]: Date Subsection
					if($ur["LoginDate"] != $last_date)
					{
						if($last_date != null)
						{
							// [JAS]: Blank row for space after each day
							$new_row = new cer_ReportDataRow();
							$new_row->bgcolor = "#E5E5E5";
							$new_row->cols[0] = new cer_ReportDataCol(SPACER_5PX);
							$new_row->cols[0]->col_span = $col_span;			
							array_push($this->report_data->rows,$new_row);
						}

						// [JAS]: Black Spacer
						$new_row = new cer_ReportDataRow();
						$new_row->bgcolor = "#000000";
						$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
						$new_row->cols[0]->col_span = $col_span;			
						array_push($this->report_data->rows,$new_row);

						$new_row = new cer_ReportDataRow();
						$new_row->style = "cer_maintable_headingSM";
						$new_row->bgcolor = "#CCCCCC";
						$new_row->cols[0] = new cer_ReportDataCol($ur["LoginDate"]);
						$new_row->cols[0]->col_span = $col_span;			
						array_push($this->report_data->rows,$new_row);

						// [JAS]: Black Spacer
						$new_row = new cer_ReportDataRow();
						$new_row->bgcolor = "#000000";
						$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
						$new_row->cols[0]->col_span = $col_span;			
						array_push($this->report_data->rows,$new_row);
					}

					$nextDayString = "";
					
					//[TAR]: Format login and logout timestamps
					
					$totalLogInTime = new cer_DateTimeFormat();
					$totalLogInTime = cer_DateTimeFormat::secsAsEnglishString($ur["logged_secs"]);
					
					$loginDate = new cer_DateTime($ur["local_time_login"]);
					$loginDateString = $loginDate->getDate("%I:%M:%S %p");
					$loginDateCompare = $loginDate->getDate("%m/%d/%y");
					
					if ($ur["local_time_logout"] != "0000-00-00 00:00:00"){
					  	$logoutDate = new cer_DateTime($ur["local_time_logout"]);	
					 	$logoutDateString = $logoutDate->getDate("%I:%M:%S %p");
					 	$logoutDayCompare = $logoutDate->getDate("%m/%d/%y");
					 	
					 	if($loginDateCompare != $logoutDayCompare){
					 		$nextDayString = " ($logoutDayCompare)";
					 	}
					} 
					else {
 					  	$logoutDateString = "<I>currently logged in</I>";
 					 	$totalLogIn = $this->_diffSeconds($ur["local_time_login"], date("Y-m-d H:i:s"));
 				 		$totalLogInTime = new cer_DateTimeFormat();
						$totalLogInTime = cer_DateTimeFormat::secsAsEnglishString($totalLogIn);
					}
				
					// [JAS]: Data Rows
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#E5E5E5";
					$new_row->cols[0] = new cer_ReportDataCol("<b>".stripslashes($ur["user_name"])."</b>");
					$new_row->cols[1] = new cer_ReportDataCol($loginDateString);
					$new_row->cols[2] = new cer_ReportDataCol($logoutDateString.$nextDayString);
					$new_row->cols[3] = new cer_ReportDataCol($totalLogInTime);
					$new_row->cols[4] = new cer_ReportDataCol($ur["user_ip"]);
					$new_row->cols[1]->align = "left";
					$new_row->cols[2]->align = "left";
					$new_row->cols[3]->align = "right";
					$new_row->cols[4]->align = "center";
					$total_replies += $ur["DateEmailTotal"];
					$total_comments += $ur["DateCommentTotal"];
					array_push($this->report_data->rows,$new_row);

					// [JAS]: White Spacer
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#FFFFFF";
					$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
					$new_row->cols[0]->col_span = $col_span;			
					array_push($this->report_data->rows,$new_row);
					
					$last_date = $ur["LoginDate"];
					$totalLogins += 1;
				}
				
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = $col_span;			
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Totals Heading
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#888888";
				$new_row->cols[0] = new cer_ReportDataCol("Total Logins: " . $totalLogins);
				$new_row->cols[0]->col_span = $col_span;
				array_push($this->report_data->rows,$new_row);
				
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = $col_span;			
				array_push($this->report_data->rows,$new_row);
			}
	}
	
	
	function _diffSeconds($start_date, $end_date)
	{
   		return floor(abs(strtotime($start_date) - strtotime($end_date)));
	}
	
};

?>