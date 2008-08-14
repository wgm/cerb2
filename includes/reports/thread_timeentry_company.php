<?php
define("REPORT_NAME","Agent Billable Time Spent by Client");
define("REPORT_SUMMARY","How much time did agents spend doing billable work for clients during a date range?");
define("REPORT_TAG","thread_timeentry_company");

define("REPORT_COLSPAN",9);

require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_ThreadTimeEntryCompanyReport();
	$report->generate_report();
	return $report;
}

class cer_ThreadTimeEntryCompanyReport extends cer_ReportModule
{
	function cer_ServiceLevelReport() {
		$this->cer_ReportModule();
	}
	
	function generate_report()
	{
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->report_search_text = (isset($_REQUEST["report_search_text"])) ? $_REQUEST["report_search_text"] : "";
		
		$this->_init_calendar();
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);

		// [JAS]: Pull up all time tracking threads
		$sql = sprintf("SELECT st.thread_time_date, st.ticket_id, st.thread_time_hrs_spent,st.thread_time_hrs_chargeable,".
			"st.thread_time_hrs_billable,st.thread_time_hrs_payable, u.user_login, u.user_name, ".
			"st.thread_time_date_billed, a.address_id, a.address_address, c.id AS company_id, c.name AS company_name ".
			"FROM  `thread_time_tracking` st,  `ticket` t,  `thread` th,  `address` a ".
			"LEFT  JOIN  `user` u ON ( st.thread_time_working_agent_id = u.user_id )  ".
			"LEFT  JOIN  `public_gui_users` pu ON ( pu.public_user_id = a.public_user_id )  ".
			"LEFT  JOIN  `company` c ON ( c.id = pu.company_id )  ".
			"WHERE t.ticket_id = st.ticket_id ".
			"AND t.min_thread_id = th.thread_id ".
			"AND a.address_id = th.thread_address_id ".
			"AND st.thread_time_date BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ".
			"ORDER BY c.name, st.thread_time_date ",
				$this->report_dates->from_date,
				$this->report_dates->to_date
			);
		$res = $this->db->query($sql);
			
		if (!$this->db->num_rows($res))
			return;
			
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = REPORT_COLSPAN;			
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Report Name
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#FF6600";
		$new_row->cols[0] = new cer_ReportDataCol($report_title);
		$new_row->cols[0]->col_span = REPORT_COLSPAN;
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = REPORT_COLSPAN;			
		array_push($this->report_data->rows,$new_row);
		
		$companies = array();
		$last_company_id = -1;
		$subtotal_hours = 0.0;
		
		while($row = $this->db->fetch_row($res)) {
			$pass = true;
			
			if($row["company_id"] != $last_company_id) {
				$company = new cer_ThreadTimeEntryCompany();
				$company->company_name = (!empty($row["company_name"])) ? stripslashes($row["company_name"]) : "Not Assigned to Company"; 
				$companies[$row["company_id"]] = $company;
				$company_ptr = &$companies[$row["company_id"]];
			}

			$time_entry = new cer_ThreadTimeEntryRow();
				$date = new cer_DateTime($row["thread_time_date"]);
				$time_entry->work_date = ($date->getDate("%Y") < 1990) ? "" : $date->getDate("%m/%d/%y");
				$time_entry->ticket_id = $row["ticket_id"];
				$time_entry->ticket_url = cer_href(sprintf("display.php?ticket=%d",
						$row["ticket_id"]
					));
				$time_entry->requester_address = stripslashes($row["address_address"]);
				$time_entry->agent_name = stripslashes($row["user_name"]);
				$date = new cer_DateTime($row["thread_time_date_billed"]);
				$time_entry->bill_date = ($date->getDate("%Y") < 1990) ? "" : $date->getDate("%m/%d/%y");
				$time_entry->hrs_spent = sprintf("%0.2f",$row["thread_time_hrs_spent"]);
				$time_entry->hrs_chargeable = sprintf("%0.2f",$row["thread_time_hrs_chargeable"]);
				$time_entry->hrs_billable = sprintf("%0.2f",$row["thread_time_hrs_billable"]);
				$time_entry->hrs_payable = sprintf("%0.2f",$row["thread_time_hrs_payable"]);
				
			if($this->report_search_text && !empty($time_entry->bill_date))
				$pass = false;
			
			// [JAS]: If we're not ignoring this row, count it.
			if($pass) {
				$company_ptr->entries[] = $time_entry;
				$company_ptr->worked_subtotal += $row["thread_time_hrs_spent"];				
				$company_ptr->chargeable_subtotal += $row["thread_time_hrs_chargeable"];				
				$company_ptr->billable_subtotal += $row["thread_time_hrs_billable"];				
				$company_ptr->payable_subtotal += $row["thread_time_hrs_payable"];				
			}
			
			$last_company_id = $row["company_id"];
			
		} // end while
		
		
		if(!empty($companies))
		foreach($companies as $company) {
			
			// [JAS]: Company Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol($company->company_name);
			$new_row->cols[0]->col_span = REPORT_COLSPAN;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = REPORT_COLSPAN;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Sub Headings
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#D0D0D0";
			$new_row->cols[0] = new cer_ReportDataCol("Work Date");
			$new_row->cols[1] = new cer_ReportDataCol("Bill Date");
			$new_row->cols[2] = new cer_ReportDataCol("Ticket ID");
			$new_row->cols[3] = new cer_ReportDataCol("Requester");
			$new_row->cols[4] = new cer_ReportDataCol("Working Agent");
			$new_row->cols[5] = new cer_ReportDataCol("Worked");
			$new_row->cols[6] = new cer_ReportDataCol("Chargeable");
			$new_row->cols[7] = new cer_ReportDataCol("Billable");
			$new_row->cols[8] = new cer_ReportDataCol("Payable");
			$new_row->cols[5]->align = "center";
			$new_row->cols[6]->align = "center";
			$new_row->cols[7]->align = "center";
			$new_row->cols[8]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = REPORT_COLSPAN;			
			array_push($this->report_data->rows,$new_row);
			
			foreach($company->entries as $entry) {
				
				// [JAS]: Timesheet Entry Row
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_text";
				$new_row->bgcolor = "#E0E0E0";
				$new_row->cols[0] = new cer_ReportDataCol($entry->work_date);
				$new_row->cols[1] = new cer_ReportDataCol($entry->bill_date);
				$new_row->cols[2] = new cer_ReportDataCol(sprintf("<a href='%s' class='cer_maintable_text'>%s</a>",
						$entry->ticket_url,
						$entry->ticket_id
					));
				$new_row->cols[3] = new cer_ReportDataCol($entry->requester_address);
				$new_row->cols[4] = new cer_ReportDataCol($entry->agent_name);
				$new_row->cols[5] = new cer_ReportDataCol($entry->hrs_spent);
				$new_row->cols[6] = new cer_ReportDataCol($entry->hrs_chargeable);
				$new_row->cols[7] = new cer_ReportDataCol($entry->hrs_billable);
				$new_row->cols[8] = new cer_ReportDataCol($entry->hrs_payable);
				
				$new_row->cols[3]->style = "cer_footer_text";
				$new_row->cols[5]->align = "center";
				$new_row->cols[6]->align = "center";
				$new_row->cols[7]->align = "center";
				$new_row->cols[8]->align = "center";
				
				array_push($this->report_data->rows,$new_row);
					
				// [JAS]: White Spacer between rows
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#FFFFFF";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = REPORT_COLSPAN;			
				array_push($this->report_data->rows,$new_row);
				
			}
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = REPORT_COLSPAN;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Timesheet Entry Row
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#BBBBBB";
			$new_row->cols[0] = new cer_ReportDataCol("");
			$new_row->cols[1] = new cer_ReportDataCol("");
			$new_row->cols[2] = new cer_ReportDataCol("");
			$new_row->cols[3] = new cer_ReportDataCol("");
			$new_row->cols[4] = new cer_ReportDataCol("");
			$new_row->cols[5] = new cer_ReportDataCol(sprintf("%0.2f",$company->worked_subtotal));
			$new_row->cols[6] = new cer_ReportDataCol(sprintf("%0.2f",$company->chargeable_subtotal));
			$new_row->cols[7] = new cer_ReportDataCol(sprintf("%0.2f",$company->billable_subtotal));
			$new_row->cols[8] = new cer_ReportDataCol(sprintf("%0.2f",$company->payable_subtotal));
			$new_row->cols[5]->align = "center";
			$new_row->cols[6]->align = "center";
			$new_row->cols[7]->align = "center";
			$new_row->cols[8]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = REPORT_COLSPAN;			
			array_push($this->report_data->rows,$new_row);
			
		} // end foreach company
		
	} // end generate
};

class cer_ThreadTimeEntryCompany {
	var $company_name = null;
	var $worked_subtotal = 0.00;
	var $chargeable_subtotal = 0.00;
	var $billable_subtotal = 0.00;
	var $payable_subtotal = 0.00;
	var $entries = array();
};

class cer_ThreadTimeEntryRow {
	var $work_date = null;
	var $ticket_id = null;
	var $ticket_url = null;
	var $requester_address = null;
	var $agent_name = null;
	var $hrs_spent = 0.00;
};


?>