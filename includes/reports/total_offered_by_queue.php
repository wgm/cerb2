<?php
define("REPORT_NAME","Total Tickets Offered per Queue");
define("REPORT_SUMMARY","Total tickets offered per queue over a date range with the ability to filter by sender.");
define("REPORT_TAG","total_offered_by_queue");

function init_report(&$cer_tpl)
{
	$report = new cer_TotalOfferedByQueue();
	$report->generate_report();
	return $report;
}

class cer_UserAddresses
{
	var $address_id = null;
	var $address_address = null;
	var $num_tickets = 0;
};

class cer_TicketQueue
{
	var $queue_id = null;
	var $queue_name = null;
	var $total_tickets = 0;
	var $addresses = array();
};


class cer_TotalOfferedByQueue extends cer_ReportModule
{
	var $queues = null;
	
	function generate_report()
	{
		
		// [TAR]: Text box from template to search by Requestor
		@$report_search_text = $_REQUEST["report_search_text"];
	  	$this->report_search_text = $report_search_text;
		
		$acl = new cer_admin_list_struct();
		
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_calendar();
		$this->_init_queue_list();
		
		$report_queue_id = $this->report_data->queue_data->report_queue_id;
		
		$report_title = sprintf("%s for %s",
			REPORT_NAME,
			$this->report_dates->date_range_str			
		);
			
		// [JAS]: Gather staff user address IDs
		$staff_ids = array();
		global $sid;
		
		$sql = "SELECT a.address_id, a.address_address, count( a.address_id ) AS tickets, th.thread_id,  t.ticket_queue_id, t.ticket_id, q.queue_name  ".
			"FROM address a, thread th, ticket t, queue q  ".
			"WHERE t.min_thread_id = th.thread_id ".
			"AND th.thread_address_id = a.address_id  ".
			"AND a.address_address != '' ".
			"AND q.queue_id =  t.ticket_queue_id ".
			(($report_search_text  && $report_search_text != "")?"AND a.address_address LIKE '%$report_search_text%' ":" ").
			(($report_queue_id  && $report_queue_id != "-1")?"AND t.ticket_queue_id='$report_queue_id' ":" ").
			sprintf("AND t.ticket_date BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					$this->report_dates->from_date,
					$this->report_dates->to_date
					).
			"GROUP  BY a.address_id, t.ticket_queue_id ".
			"ORDER  BY q.queue_name, a.address_address ";
			$rt_res = $this->db->query($sql);
				
		// [JAS]: If we have data for factoring response time
		$row_count = $this->db->num_rows($rt_res);
		
		if (!$row_count){
			return;
		}
		
		$total_tickets = 0;
		
		while($rt = $this->db->fetch_row($rt_res))
		{
			$q_id = $rt["ticket_queue_id"];
			$aid = $rt["address_id"];
			
			if(!isset($this->queues[$q_id])) {
				$this->queues[$q_id] = new cer_TicketQueue();
				$this->queues[$q_id]->queue_id = $q_id; 
				$this->queues[$q_id]->queue_name = $rt["queue_name"];
			}
			
			$addy = new cer_UserAddresses();
				$addy->address_id = $rt["address_id"];
				$addy->address_address = $rt["address_address"];
				$addy->num_tickets = $rt["tickets"];
				$this->queues[$q_id]->total_tickets += $addy->num_tickets;
				$total_tickets += $addy->num_tickets;
				
			if (!empty($report_search_text)){
				$this->queues[$q_id]->addresses[$aid] = $addy;
			}
			
		}
		
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Report Title
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#FF6600";
		$new_row->cols[0] = new cer_ReportDataCol($report_title);
		$new_row->cols[0]->col_span = 2;
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
		
		$total_queues = count($this->queues);
		
		if (!empty($report_search_text)){
			foreach($this->queues as $idx => $queue) {
				$this->_DrawRequesterHeader($idx);
				$this->_DrawRequesterLine($idx);
			}
		}
		else {
			$this->_DrawQueueHeader();
			$this->_DrawQueueLine();
		}
			
		// [JAS]: Totals Heading
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#888888";
		$new_row->cols[0] = new cer_ReportDataCol("Total Queues: ".$total_queues);
		$new_row->cols[1] = new cer_ReportDataCol("Total Tickets: ".$total_tickets);
		$new_row->cols[1]->align = "center";
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
	}
	
	function _DrawRequesterHeader($idx) {
		// [JAS]: Draw Group Heading				
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#AAAAAA";
		$new_row->cols[0] = new cer_ReportDataCol("Queue: ". $this->queues[$idx]->queue_name);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Column Headings
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_headingSM";
		$new_row->bgcolor = "#CCCCCC";
		$new_row->cols[0] = new cer_ReportDataCol("Requester");
		$new_row->cols[1] = new cer_ReportDataCol("Tickets");
		$new_row->cols[0]->width = "20%";
		$new_row->cols[1]->width = "80%";
		$new_row->cols[1]->align = "center";
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
	}
				
	function _DrawRequesterLine($idx) {
		
		foreach ($this->queues[$idx]->addresses as $addy) {
			// [JAS]: Data Rows
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#E5E5E5";
			$new_row->cols[0] = new cer_ReportDataCol("<b>".$addy->address_address."</b>");
			$new_row->cols[1] = new cer_ReportDataCol($addy->num_tickets);
			$new_row->cols[0]->align = "left";
			$new_row->cols[0]->valign = "top";
			$new_row->cols[1]->valign = "top";
			$new_row->cols[1]->align = "center";
			array_push($this->report_data->rows,$new_row);

			// [JAS]: White Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#FFFFFF";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 2;			
			array_push($this->report_data->rows,$new_row);
		}
		
		// [JAS]: Blank row for space after each user
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#E5E5E5";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_5PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
	
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
		
	}
	
	function _DrawQueueHeader() {
		// [JAS]: Column Headings
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_headingSM";
		$new_row->bgcolor = "#CCCCCC";
		$new_row->cols[0] = new cer_ReportDataCol("Queue");
		$new_row->cols[1] = new cer_ReportDataCol("Tickets");
		$new_row->cols[0]->width = "20%";
		$new_row->cols[1]->width = "80%";
		$new_row->cols[1]->align = "center";
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
	}
	
	function _DrawQueueLine() {
		foreach ($this->queues as $queue) {
			// [JAS]: Data Rows
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#E5E5E5";
			$new_row->cols[0] = new cer_ReportDataCol("<b>".$queue->queue_name."</b>");
			$new_row->cols[1] = new cer_ReportDataCol($queue->total_tickets);
			$new_row->cols[0]->align = "left";
			$new_row->cols[0]->valign = "top";
			$new_row->cols[1]->valign = "top";
			$new_row->cols[1]->align = "center";
			array_push($this->report_data->rows,$new_row);

			// [JAS]: White Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#FFFFFF";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 2;			
			array_push($this->report_data->rows,$new_row);
		}
		
		// [JAS]: Blank row for space after each user
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#E5E5E5";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_5PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
	
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 2;			
		array_push($this->report_data->rows,$new_row);
	}
	
};

?>