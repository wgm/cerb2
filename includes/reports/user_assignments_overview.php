<?php
define("REPORT_NAME","Group Assignment Overview");
define("REPORT_SUMMARY","Overview of who is assigned to what group.");
define("REPORT_TAG","user_assignments_report");
define("REPORT_GROUP","system");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_UserAssignmentsOverview();
	$report->generate_report($cer_tpl);
	return $report;
}

class cer_UserAssignmentsUser
{
	var $user_id = null;
	var $user_name = null;
	var $user_email = null;
	var $user_login = null;
	var $user_last_login = null;
};

class cer_UserAssignmentsGroup
{
	var $group_id = null;
	var $group_name = null;
	var $users = array();
	
	function setGroupName($name="") {
		if(empty($name))
			$name = "Not Assigned";
		
		$this->group_name = $name;
	}
	
};

class cer_UserAssignmentsOverview extends cer_ReportModule
{
	function generate_report(&$cer_tpl)
	{
		$acl = new cer_admin_list_struct();
		
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_group_list();
		
		$report_group_id = $this->report_data->group_data->report_group_id;
		
		if($report_group_id == -1) $report_group_id = 0;  // [JAS]: Unset if on "all"
		
		$report_title = REPORT_NAME;
					
		// [JAS]: Gather staff user address IDs
		$staff_ids = array();
				
		$sql = "SELECT u.user_id, u.user_name, u.user_email, u.user_login, u.user_last_login, ual.group_id, ual.group_name ".
			"FROM `user` u ".
			"LEFT JOIN `user_access_levels` ual ON ( u.user_group_id = ual.group_id ) ".
			sprintf("WHERE 1=1 ".
					"%s ".
					"ORDER BY ual.group_name,u.user_name ",
				((!empty($report_group_id)) ? "AND u.user_group_id='$report_group_id' " : " ")
			);
		$rt_res = $this->db->query($sql);
		
		// [JAS]: If we have data
		$row_count = $this->db->num_rows($rt_res);
		
		if($row_count && $row_count > 0)
		{
			$last_group_id = -1;
			$groups = array();
			
			while($rt = $this->db->fetch_row($rt_res))
			{
				$grp_id = $rt["group_id"];
				$uid = $rt["user_id"];
				
				if(!isset($groups[$grp_id])) {
					$groups[$grp_id] = new cer_UserAssignmentsGroup();
					$groups[$grp_id]->group_id = $grp_id; 
					$groups[$grp_id]->setGroupName(stripslashes($rt["group_name"]));
				}
				
				$user = new cer_UserAssignmentsUser();
					$user->user_id = $rt["user_id"];
					$user->user_name = stripslashes($rt["user_name"]);
					$user->user_email = $rt["user_email"];
					$user->user_login = stripslashes($rt["user_login"]);
					$user->user_last_login = $rt["user_last_login"];
				$groups[$grp_id]->users[$uid] = $user;
				
				$last_group_id = $rt["group_id"];
			}
		
			$colspan = "3";
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = $colspan;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Report Title
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#FF6600";
			$new_row->cols[0] = new cer_ReportDataCol($report_title);
			$new_row->cols[0]->col_span = $colspan;
			array_push($this->report_data->rows,$new_row);

			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = $colspan;			
			array_push($this->report_data->rows,$new_row);

			$total_num_times = 0;
			$last_group_id = "-1";
			
			$sorted_groups = cer_PointerSort::pointerSortCollection($groups,"group_name");
			
			foreach($sorted_groups as $idx => $group)
			{
				
				if ($group->group_id != $last_group_id) {

					// [JAS]: Draw Group Heading				
					$new_row = new cer_ReportDataRow();
					$new_row->style = "cer_maintable_header";
					$new_row->bgcolor = "#AAAAAA";
					$new_row->cols[0] = new cer_ReportDataCol("Group: ". $group->group_name);
					$new_row->cols[0]->col_span = $colspan;			
					array_push($this->report_data->rows,$new_row);

					// [JAS]: Black Spacer
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#000000";
					$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
					$new_row->cols[0]->col_span = $colspan;			
					array_push($this->report_data->rows,$new_row);
					
					// [JAS]: Column Headings
					$new_row = new cer_ReportDataRow();
					$new_row->style = "cer_maintable_headingSM";
					$new_row->bgcolor = "#CCCCCC";
					$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
					$new_row->cols[1] = new cer_ReportDataCol("Email Address");
					$new_row->cols[2] = new cer_ReportDataCol("Agent Login");
					$new_row->cols[0]->width = "150";
					$new_row->cols[1]->width = "200";
					$new_row->cols[0]->align = "left";
					array_push($this->report_data->rows,$new_row);
					
					// [JAS]: Black Spacer
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#000000";
					$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
					$new_row->cols[0]->col_span = $colspan;			
					array_push($this->report_data->rows,$new_row);
				}
				
				$sorted_users = cer_PointerSort::pointerSortCollection($group->users,"user_name");
				
				foreach ($sorted_users as $idx => $user) {
					// [JAS]: Data Rows
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#E5E5E5";
					$new_row->cols[0] = new cer_ReportDataCol("<b>".$user->user_name."</b>");
					$new_row->cols[1] = new cer_ReportDataCol($user->user_email);
					$new_row->cols[2] = new cer_ReportDataCol($user->user_login);
					$new_row->cols[0]->align = "left";
					$new_row->cols[0]->valign = "top";
					$new_row->cols[1]->valign = "top";
					$new_row->cols[2]->valign = "top";
					$new_row->cols[2]->nowrap = "NOWRAP";
					$total_num_times++;
					array_push($this->report_data->rows,$new_row);
		
					// [JAS]: White Spacer
					$new_row = new cer_ReportDataRow();
					$new_row->bgcolor = "#FFFFFF";
					$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
					$new_row->cols[0]->col_span = $colspan;			
					array_push($this->report_data->rows,$new_row);
				}
				
				// [JAS]: Blank row for space after each user
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#E5E5E5";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_5PX);
				$new_row->cols[0]->col_span = $colspan;			
				array_push($this->report_data->rows,$new_row);
			
				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = $colspan;			
				array_push($this->report_data->rows,$new_row);
				
				$last_group_id = $group->group_id;
			}
			
			// [JAS]: Draw Footer
			if($total_num_times)
			{
				// [JAS]: Totals Heading
				$new_row = new cer_ReportDataRow();
				$new_row->style = "cer_maintable_header";
				$new_row->bgcolor = "#888888";
				$new_row->cols[0] = new cer_ReportDataCol("&nbsp;");
				$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
				$new_row->cols[0]->col_span = "2";
				array_push($this->report_data->rows,$new_row);

				// [JAS]: Black Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#000000";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = $colspan;			
				array_push($this->report_data->rows,$new_row);
			}
			
		}
		
	}
		
};


?>