<?php
define("REPORT_NAME","Agent Ticket Assignment Report");
define("REPORT_SUMMARY","Breakdown of assigned tickets per queue grouped by agent.");
define("REPORT_TAG","user_open_ticket_report");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_UserOpenTickets();
	$report->generate_report();
	return $report;
}

class cer_TicketQueue
{
	var $queue_id = null;
	var $queue_name = null;
	var $total_tickets = 0;
};

class cer_UserWithTickets
{
	var $user_id = null;
	var $user_name = null;
	var $queues = array();	
};	

class cer_UserOpenTickets extends cer_ReportModule
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
		$this->_init_user_list();
		
		$report_user_id = $this->report_data->user_data->report_user_id;
		
		$report_title = sprintf("%s",
			REPORT_NAME
		);
			
		// [JAS]: Gather staff user address IDs
		$staff_ids = array();
		global $sid;
			
		$sql = "SELECT count(t.ticket_id) AS ticket_count, t.ticket_id, t.ticket_queue_id, q.queue_name, u.user_id, u.user_name ".
			"FROM ticket t, user u, queue q ".
			"WHERE t.ticket_assigned_to_id = u.user_id ".
			"AND t.ticket_queue_id=q.queue_id AND t.ticket_status IN ('new', 'awaiting-reply', 'customer-reply','bounced') ".
			(($report_user_id  && $report_user_id != "-1")?"AND u.user_id='$report_user_id' ":" ").
			"GROUP BY t.ticket_assigned_to_id,q.queue_name ".
			"ORDER BY q.queue_name,u.user_name";
			
			$rt_res = $this->db->query($sql);
				
		// [JAS]: If we have data for factoring response time
		$row_count = $this->db->num_rows($rt_res);
		
		if (!$row_count){
			return;
		}
		
		$total_tickets = 0;
		
		while($rt = $this->db->fetch_row($rt_res))
		{
			$u_id = $rt["user_id"];
						
			if(!isset($this->user[$u_id])) {
				$this->user[$u_id] = new cer_UserWithTickets();
				$this->user[$u_id]->user_id = $u_id; 
				$this->user[$u_id]->user_name = $rt["user_name"];
			}
			
			$tickets = new cer_TicketQueue();
				$tickets->queue_id = $rt["ticket_queue_id"];
				$tickets->queue_name = $rt["queue_name"];
				$tickets->total_tickets = $rt["ticket_count"];
				$total_tickets += $tickets->total_tickets;
				$this->user[$u_id]->queues[$rt["ticket_id"]] = $tickets;
				
			
		}
		
		$colspan='2';
		$total_users = count($this->user);
		
		
		// [JAS]: Black Spacer
		$this->_DrawSpacerRow("#000000",$colspan);
		
		// [JAS]: Report Title
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#FF6600";
		$new_row->cols[0] = new cer_ReportDataCol($report_title);
		$new_row->cols[0]->col_span = 2;
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$this->_DrawSpacerRow("#000000",$colspan);
		
		$sorted_users = cer_PointerSort::pointerSortCollection($this->user,"user_name");
		
		foreach ($sorted_users as $user) {
			// [JAS]: Data Rows
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#AAAAAA";
			$new_row->cols[0] = new cer_ReportDataCol("<b>Agent: ".$user->user_name."</b>");
			$new_row->cols[0]->align = "left";
			$new_row->cols[0]->valign = "top";
			$new_row->cols[0]->col_span = $colspan;
			array_push($this->report_data->rows,$new_row);
	
			// [JAS]: Black Spacer
			$this->_DrawSpacerRow("#000000",$colspan);
			
			// [JAS]: White Spacer
			$this->_DrawSpacerRow("#FFFFFF",$colspan);
			
			// [JAS]: Data Rows
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#CCCCCC";
			$new_row->cols[0] = new cer_ReportDataCol("<b>Queue</b>");
			$new_row->cols[1] = new cer_ReportDataCol("<b>Number of Assigned Tickets</b>");
			$new_row->cols[0]->align = "left";
			$new_row->cols[0]->valign = "top";
			$new_row->cols[1]->valign = "top";
			$new_row->cols[1]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: White Spacer
			$this->_DrawSpacerRow("#FFFFFF",$colspan);
			
			
			foreach ($user->queues as $queue) {
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
				$this->_DrawSpacerRow("#FFFFFF",$colspan);
			}
		}
		
		// [JAS]: Totals Heading
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#888888";
		$new_row->cols[0] = new cer_ReportDataCol("&nbsp;");
		$new_row->cols[1] = new cer_ReportDataCol("Total Assigned Tickets: ".$total_tickets);
		$new_row->cols[1]->align = "center";
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$this->_DrawSpacerRow("#000000",$colspan);
	}
	
	
	function _DrawSpacerRow($bgcolor,$colspan) {
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = $bgcolor;
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = $colspan;			
		array_push($this->report_data->rows,$new_row);
	}
	
	
};

?>