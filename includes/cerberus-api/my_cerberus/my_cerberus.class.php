<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: my_cerberus.class.php
|
| Purpose: Object to store all the data for My Cerberus functionality.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/calendar.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/hash/core.hash.php");

class CER_MY_CERBERUS_TABS
{
	var $tab_dashboard_bg_css = "boxtitle_gray_glass";
	var $tab_dashboard_css = "cer_navbar_heading";
	var $tab_prefs_bg_css = "boxtitle_gray_glass";
	var $tab_prefs_css = "cer_navbar_heading";
	var $tab_layout_bg_css = "boxtitle_gray_glass";
	var $tab_layout_css = "cer_navbar_heading";
	var $tab_msgs_bg_css = "boxtitle_gray_glass";
	var $tab_msgs_css = "cer_navbar_heading";
	var $tab_assign_bg_css = "boxtitle_gray_glass";
	var $tab_assign_css = "cer_navbar_heading";
	var $tab_notify_bg_css = "boxtitle_gray_glass";
	var $tab_notify_css = "cer_navbar_heading";
	var $tab_tasks_bg_css = "boxtitle_gray_glass";
	var $tab_tasks_css = "cer_navbar_heading";

	function CER_MY_CERBERUS_TABS($mode="")
	{
		$this->set_tab_mode($mode);
	}
	
	function set_tab_mode($mode="")
	{
		switch($mode)
		{
			case "notification":
				$this->tab_notify_bg_css = "boxtitle_green_glass";
				$this->tab_notify_css = "cer_navbar_selected";
			break;
			case "assign":
				$this->tab_assign_bg_css = "boxtitle_green_glass";
				$this->tab_assign_css = "cer_navbar_selected";
			break;
			case "tasks":
				$this->tab_tasks_bg_css = "boxtitle_green_glass";
				$this->tab_tasks_css = "cer_navbar_selected";
			break;
			case "preferences":
				$this->tab_prefs_bg_css = "boxtitle_green_glass";
				$this->tab_prefs_css = "cer_navbar_selected";
			break;
			case "messages":
				$this->tab_msgs_bg_css = "boxtitle_green_glass";
				$this->tab_msgs_css = "cer_navbar_selected";
			break;
			case "layout":
				$this->tab_layout_bg_css = "boxtitle_green_glass";
				$this->tab_layout_css = "cer_navbar_selected";
			break;
			default:
			case "preferences":
				$this->tab_dashboard_bg_css = "boxtitle_green_glass";
				$this->tab_dashboard_css = "cer_navbar_selected";
			break;
		}
	}
};

class CER_MY_CERBERUS_PM_FOLDER
{
	var $folder_id = 0;
	var $folder_name = null;
};

class CER_MY_CERBERUS_PM_CONTAINER
{
	var $db = null;
	var $pm_mode = null;
	var $to_users = array();
	var $msgs = array();
	var $folder = null;
	var $pm_to_id = 0;
	
	function CER_MY_CERBERUS_PM_CONTAINER($mode,$msg_id=0)
	{
		$this->db = cer_Database::getInstance();
		$this->load_users();
		$this->pm_mode = $mode;
		$this->init_folders();
		
		switch($mode)
		{		
			case "list":
				$this->load_msgs();
			break;
			case "read":
			case "reply":
				$this->load_msg($msg_id);
			break;
		}
	}
	
	function init_folders()
	{
		global $session;
		
		switch($session->vars["pm_folder"])
		{
			case "ib":
			default:
			{
				$this->folder = new CER_MY_CERBERUS_PM_FOLDER();
				$this->folder->folder_id = 0;
				$this->folder->folder_name = "Inbox";
				break;
			}
			case "ob":
			{
				$this->folder = new CER_MY_CERBERUS_PM_FOLDER();
				$this->folder->folder_id = -1;
				$this->folder->folder_name = "Sent Messages";
				break;
			}
		}
	}
	
	function load_users()
	{
		global $cer_hash;
		global $session;
		
		foreach($cer_hash->get_user_hash() as $user)
		{
			if($user->user_id != $session->vars["login_handler"]->user_id)
				$this->to_users[$user->user_id] = $user->user_name . " (" . $user->user_login . ")";
		}
	}
	
	// [JAS]: Load a single message
	function load_msg($msg_id=0)
	{
		global $session;
		
		$user_id = $session->vars["login_handler"]->user_id;
		
		$sql = sprintf("UPDATE private_messages SET pm_marked_read = 1 WHERE pm_to_user_id = %d AND pm_id = %d",
			$user_id,$msg_id);
		$this->db->query($sql);
		
		$sql = sprintf("SELECT pm.pm_id, pm.pm_marked_read, pm.pm_from_user_id, pm.pm_subject, pm.pm_date, pm.pm_message, u.user_login as from_login, u2.user_login as to_login ".
			"FROM private_messages pm LEFT JOIN user u ON (pm.pm_from_user_id = u.user_id) LEFT JOIN user u2 ON (pm.pm_to_user_id = u2.user_id) ".
			"WHERE (pm.pm_to_user_id = %d OR pm.pm_from_user_id = %d) AND pm.pm_id = %d",
				$user_id,$user_id,$msg_id);
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result))
		{
			while($row = $this->db->fetch_row($result))
			{
				$pm = new CER_MY_CERBERUS_PM();
				$pm->pm_id = $row["pm_id"];
				$pm->to_id = $user_id;
				$pm->to = $row["to_login"];
				$pm->from_id = $row["pm_from_user_id"];
				$pm->from = $row["from_login"];
				$pm->subject = stripslashes($row["pm_subject"]);
				$pm->date = $row["pm_date"];
				$pm->message = stripslashes($row["pm_message"]);
				$pm->marked_read = $row["pm_marked_read"];

				$pm->urls['reply'] = cer_href("my_cerberus.php?mode=messages&pm_action=reply&pm_id=".$row["pm_id"]);
				$pm->urls['inbox'] = cer_href("my_cerberus.php?mode=messages");
				$pm->urls['delete'] = cer_href("my_cerberus.php?mode=messages&pm_action=list&form_submit=pm_delete&pm_id=".$row["pm_id"]);
				
				array_push($this->msgs,$pm);
			}
		}
	}

	function load_msgs()
	{
		global $session;
		
		// [JAS]: Pull mail from either inbox or sent items depending on the folder.  Flip TO/FROM
		$sql = sprintf("SELECT pm.pm_id, pm.pm_marked_read, pm.pm_from_user_id, pm.pm_subject, pm.pm_date, u.user_login as from_login, u2.user_login as to_login ".
			"FROM private_messages pm LEFT JOIN user u ON (pm.pm_from_user_id = u.user_id) LEFT JOIN user u2 ON (pm.pm_to_user_id = u2.user_id) ".
			" WHERE 1 ".
			" AND " . (($this->folder->folder_id==0)?"pm.pm_to_user_id":"pm.pm_from_user_id") . 
			" = %d ORDER BY pm.pm_date DESC",
				$session->vars["login_handler"]->user_id);
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result))
		{
			while($row = $this->db->fetch_row($result))
			{
				$pm = new CER_MY_CERBERUS_PM();
				$pm->pm_id = $row["pm_id"];
				$pm->to_id = $session->vars["login_handler"]->user_id;
				$pm->to = $row["to_login"];
				$pm->pm_url = cer_href("my_cerberus.php?mode=messages&pm_action=read&pm_id=" . $row["pm_id"]);
				$pm->from_id = $row["pm_from_user_id"];
				$pm->from = $row["from_login"];
				$pm->subject = stripslashes($row["pm_subject"]);
				$pm->date = $row["pm_date"];
				$pm->marked_read = $row["pm_marked_read"];
				
				array_push($this->msgs,$pm);
			}
		}
	}
	
};

class CER_MY_CERBERUS_PM
{
	var $pm_id = 0;
	var $to_id = 0;
	var $to = null;
	var $pm_url = null;
	var $from_id = 0;
	var $from = null;
	var $subject = null;
	var $date = null;
	var $marked_read = 0;
	var $message = null;
	var $urls = array();
};


class CER_MY_CERBERUS_DASHBOARD
{
	var $db; 											// [JAS]: database reference pointer
	var $urls = array();
	var $last_actions_title = null;
	var $last_actions = array();
	var $stats = array();
	var $snapshot = array();
	var $cal = null;
	var $tasks = null;
	
	function CER_MY_CERBERUS_DASHBOARD($pid=0,$tid=0,$prefs=array())
	{
		global $session;
		global $mo_offset;
		
		$this->db = cer_Database::getInstance();
		
		$this->compute_stats();
		
		// [JAS]: Set up the calendar ==========================
		$this->cal = new CER_CALENDAR($mo_offset);
		$this->cal->register_callback_day_links("calendar_draw_day_links",$this);
		$this->cal->register_callback_month_links("calendar_draw_month_links",$this);
		$this->cal->populate_calendar_matrix();
		// ======================================================

		$this->load_audit_log();
		
		$this->urls['my_tickets'] = cer_href(sprintf("ticket_list.php?search_submit=1&qid=&search_status=-1&search_subject=&search_content&search_company=&search_owner=%d",
			$session->vars["login_handler"]->user_id));
			
		$this->tasks = new CER_MY_CERBERUS_TASK_HANDLER($pid,$tid,$prefs);
	}
	
	function calendar_draw_day_links(&$o_day,$month,$year)
	{
		global $mo_offset; // clean
		
		if($o_day == null) return true;
		
		$o_day->day_url = cer_href(sprintf("my_cerberus.php?mode=dashboard&mo_offset=%d&mo_d=%d&mo_m=%d&mo_y=%d",
			$mo_offset,$o_day->day,$month,$year));
			
		return($o_day);
	}

	function calendar_draw_month_links($mo_offset=0,$prev_mo=-1,$next_mo=1)
	{
		$o_links = array();
		
		$o_links["prev_mo"] = cer_href($_SERVER["PHP_SELF"] . "?mode=dashboard&mo_offset=$prev_mo");
		$o_links["next_mo"] = cer_href($_SERVER["PHP_SELF"] . "?mode=dashboard&mo_offset=$next_mo");
		
		return($o_links);
	}
	
	function compute_stats()
	{
		global $session;
		
		$cerberus_format = new cer_formatting_obj();
		
		$this->stats['active_tickets'] = 0;
		$this->stats['active_tickets_assigned'] = 0;
		$this->stats['my_percentage'] = 0.0;
		$this->stats['latest_ticket_id'] = 0;
		$this->stats['latest_ticket_subject'] = null;
		$this->stats['latest_ticket_age'] = null;
		// [JSJ]: Added last resolved ticket to dashboard
        $this->stats['last_resolved_ticket_id'] = 0;    
        $this->stats['last_resolved_ticket_subject'] = null;
        $this->stats['last_resolved_time_since'] = null;
		
		// [JAS]: Total Tickets Assigned to User and Active
		$sql = sprintf("SELECT count(*) FROM ticket t WHERE t.ticket_assigned_to_id = %d AND t.ticket_status IN ('new', 'customer-reply','bounced') ",
			$session->vars["login_handler"]->user_id);
		$result = $this->db->query($sql,false);
		
		if($row = $this->db->grab_first_row($result))
		{ $this->stats['active_tickets_assigned'] = $row[0]; }

		// [JAS]: Total Tickets Active
		$sql = sprintf("SELECT count(*) FROM ticket t WHERE t.ticket_status IN ('new', 'customer-reply','bounced')",
			$session->vars["login_handler"]->user_id);
		$result = $this->db->query($sql,false);
		
		if($row = $this->db->grab_first_row($result))
		{ $this->stats['active_tickets'] = $row[0]; }
		
		// [JAS]: Compute Percentage
		if($this->stats['active_tickets'] && $this->stats['active_tickets_assigned'])
		{ $this->stats['my_percentage'] = sprintf("%0.1f",($this->stats['active_tickets_assigned'] / $this->stats['active_tickets'])*100); }
		
		// [JAS]: Find oldest ticket
		$sql = sprintf("SELECT t.ticket_id, t.ticket_subject, t.ticket_date FROM ticket t ".
			" WHERE t.ticket_assigned_to_id = %d AND t.ticket_status IN ('new', 'customer-reply','bounced') ".
			" ORDER BY t.ticket_date LIMIT 0,1",
				$session->vars["login_handler"]->user_id);
		$result = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($result))
		{
			$this->stats['latest_ticket_id'] = $row["ticket_id"];
			$this->stats['latest_ticket_subject'] = stripslashes($row["ticket_subject"]);
			$time_ticket = $cerberus_format->db_date_to_epoch(@$row["ticket_date"]);
			$ticket_secs = $cerberus_format->date_diff_epoch(mktime(),$time_ticket);
			$ticket_age = $cerberus_format->format_seconds($ticket_secs);
			$this->stats['latest_ticket_age'] = $ticket_age;
			$this->urls['latest_ticket'] = cer_href("display.php?ticket=" . $row["ticket_id"]);
		}
		
        // [JSJ]: Added last resolved ticket to dashboard
        // [JAS]: Redo this using the audit log.  Commenting out last_update_user_id
        /*
        	code cut in refactoring
		*/

		// [JAS]: Last 7 days threads by tech
		$sql = sprintf("SELECT a.address_id FROM address a WHERE a.address_address = '%s'",
			$session->vars["login_handler"]->user_email);
		$result = $this->db->query($sql);

		if($row = $this->db->grab_first_row($result))
		{
			$addy_id = $row["address_id"];
			
			$sql = sprintf("SELECT COUNT(  ".
			"IF ( th.thread_type =  'comment', 1,  NULL)  ) AS DateCommentTotal, COUNT(  ".
			"IF ( th.thread_type =  'email', 1,  NULL)  ) AS DateEmailTotal, DATE_FORMAT( th.thread_date,  '%%a %%b %%d'  )  AS TicketDate, SUM(th.thread_time_worked) as ThreadTimeWorked  ".
			"FROM thread th, ticket t ".
			"WHERE th.ticket_id = t.ticket_id AND th.thread_date > DATE_SUB( NOW(  ) ,  ".
			"INTERVAL  \"7\" ".
			"DAY  )  AND t.ticket_status NOT  ".
			"IN (  'dead' ) AND th.thread_address_id = %d ".
			"GROUP  BY TicketDate ".
			"ORDER  BY th.thread_date DESC  ".
			"LIMIT 0 , 7",
				$addy_id);

			/*
			$sql = sprintf("SELECT count(th.thread_id) As DateTotal, DATE_FORMAT(th.thread_date,'%%a %%b %%d') As TicketDate ".
				" FROM thread th, ticket t WHERE th.ticket_id = t.ticket_id AND th.thread_date > DATE_SUB(NOW(),INTERVAL \"7\" DAY) ".
				" AND t.ticket_status NOT IN ('dead') AND th.thread_address_id = %d GROUP BY TicketDate order by th.thread_date desc LIMIT 0,7",
					$addy_id);
			*/
			$result = $this->db->query($sql);
			
			if($this->db->num_rows($result))
			{
				while($row = $this->db->fetch_row($result))
				{
					$snapshot = new CER_MY_CERBERUS_DAY_SNAPSHOT();
					$snapshot->day_str = $row["TicketDate"];
					$snapshot->day_email_count = $row["DateEmailTotal"];
					$snapshot->day_comment_count = $row["DateCommentTotal"];
					
					array_push($this->snapshot,$snapshot);
				}
			}
		}
	}
	
	function load_audit_log()
	{
		global $session; // clean up
		global $audit_log; // clean
		global $mo_d, $mo_m, $mo_y; // clean
		
		$limit = 1000;
		
		if(!isset($mo_d) || !isset($mo_m) || !isset($mo_y))
		{ 
			$mo_d = $this->cal->cur_day;
			$mo_m = $this->cal->cur_month;
			$mo_y = $this->cal->cur_year;
		}
		
		$first_day = date("Y-m-d",mktime(0,0,0,$mo_m,$mo_d,$mo_y));
		$next_day = date("Y-m-d",mktime(0,0,0,$mo_m,$mo_d+1,$mo_y));
		$this->last_actions_title = "Tickets Worked History for " . date("l, F d Y",mktime(0,0,0,$mo_m,$mo_d,$mo_y));
		
		$sql = sprintf("SELECT l.ticket_id,t.ticket_subject,t.ticket_status ".
			" FROM `ticket_audit_log` l, `ticket` t " .
			" WHERE t.ticket_id = l.ticket_id AND l.user_id = %d ".
			" AND l.timestamp BETWEEN '$first_day 00:00:01' AND '$next_day 00:00:00' " .
			" GROUP BY l.ticket_id ORDER BY l.timestamp DESC LIMIT 0,$limit",
				$session->vars["login_handler"]->user_id);
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result))
		{
			while($row = $this->db->fetch_row($result))
			{
				$log_entry = new CER_MY_CERBERUS_TICKET_ENTRY();
				$log_entry->ticket_id = $row["ticket_id"]; 
				$log_entry->ticket_subject = stripslashes($row["ticket_subject"]);
				$log_entry->ticket_status = $row["ticket_status"];
				$log_entry->ticket_url = cer_href("display.php?ticket=" . $row["ticket_id"]);
				
				array_push($this->last_actions,$log_entry);
			}
		}
	}
	
};

class CER_MY_CERBERUS_DAY_SNAPSHOT
{
	var $day_str = null;
	var $day_email_count = 0;
	var $day_comment_count = 0;
};

class CER_MY_CERBERUS_TICKET_ENTRY
{
	var $ticket_id;
	var $ticket_subject;
	var $ticket_status;
	var $ticket_url;
};

class CER_MY_CERBERUS_WATCHER
{
	var $queues=array();
	
	function CER_MY_CERBERUS_WATCHER()
	{
		global $cer_hash;
		
		foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
		{
			array_push($this->queues,$queue);	
		}
	}
};

class CER_MY_CERBERUS_ASSIGN_QUEUES
{
	var $max;
	var $queues=array();
	
	function CER_MY_CERBERUS_ASSIGN_QUEUES()
	{
		global $session;
		global $cer_hash;
		
		$qs = $session->vars["login_handler"]->user_prefs->assign_queues;
		$this->max = $session->vars["login_handler"]->user_prefs->assign_queues->max;
		
		foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
		{
			array_push($this->queues,(($qs->queues[$queue->queue_id]) ? 1 : 0));	
		}
		
	}
};

class CER_MY_CERBERUS_TASK_HANDLER
{
	var $db = null;	 // [JAS]: Database reference pointer
	var $active_project = null;   // [JAS]: Active project pointer
	var $projects = array();
	var $user_list = array();
	var $project_prefs = array();
	var $progress_options = array(0 => "0% (Not Started)",
								 10 => "10% (In Progress)",
								 20 => "20%",
								 30 => "30%",
								 40 => "40%",
								 50 => "50%",
								 60 => "60%",
								 70 => "70%",
								 80 => "80%",
								 90 => "90%",
								100 => "100% (Completed)"
								);
	var $priority_options = array(0 => "Undefined",
								 25 => "Trivial/Low",
								 50 => "Moderate",
								 75 => "High",
								100 => "Urgent/Critical"
								 );
								
								
	function CER_MY_CERBERUS_TASK_HANDLER($pid=0,$tid=0,$prefs=array())
	{
		$this->db = cer_Database::getInstance();
		
		$this->project_prefs = $prefs;
		
		$this->_load_project_list();
		$this->set_active_project($pid);
		$this->_load_task_list();
		$this->set_active_task($tid);
		$this->_load_task_notes($tid);
		$this->_load_user_list();
		$this->_read_project_acls();
		$this->apply_filters($this->project_prefs["filter_hide_completed"],
							$this->project_prefs["filter_only_my_tasks"],
							$this->project_prefs["filter_category"],
							$this->project_prefs["filter_hide_completed_projects"]
							);
	}
		
	function _load_task_list()
	{
		if(isset($this->project_prefs["sort_by"]))
			$sort_by = $this->project_prefs["sort_by"];
		else
			$sort_by = "t.task_priority";
			
		if(!empty($this->project_prefs["sort_asc"]))
			$sort_asc = "ASC";
		else
			$sort_asc = "DESC";
			
		$sql = "SELECT t.task_id, t.task_summary, t.task_progress, t.task_assigned_uid, t.task_priority, t.task_parent_id, t.task_project_id, ".
			"t.task_project_category_id, tc.category_name, t.task_due_date, t.task_updated_date, t.task_reminder_date, t.task_created_uid, t.task_classification, u.user_name, u.user_login ".
			"FROM tasks t LEFT JOIN user u ON (t.task_assigned_uid = u.user_id) LEFT JOIN tasks_projects_categories tc ON (t.task_project_id = tc.project_id AND t.task_project_category_id = tc.category_id) ".
			"WHERE 1 ".
			"ORDER BY $sort_by $sort_asc";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				$task = new CER_MY_CERBERUS_TASK();
				$task->task_id=$row["task_id"];
				$task->task_summary=stripslashes($row["task_summary"]);
				$task->task_progress=$row["task_progress"];
				$task->task_created_uid=$row["task_created_uid"];
				$task->task_assigned_uid=$row["task_assigned_uid"];
				$task->task_assigned_user=$row["user_name"];
				$task->task_assigned_login=$row["user_login"];
				$task->task_priority=$row["task_priority"];
				$task->task_parent_id=$row["task_parent_id"];
				$task->task_project_id=$row["task_project_id"];
				$task->task_project_category_id=$row["task_project_category_id"];
				$task->task_project_category_name=stripslashes($row["category_name"]);
				$task->task_classification=$row["task_classification"];
				
				$task->task_priority_string = $this->priority_options[$task->task_priority];

				list($due_y,$due_m,$due_d) = sscanf($row["task_due_date"],"%d-%d-%d 00:00:00");
				// use 1990 because it's before the 'Dawn of Cerberus'
				// This is a nasty hack because of the inconsistencies from DB servers and 
				// web servers that return the dates
	   			if((empty($due_d) && empty($due_m) && empty($due_y)) || 1990>$due_y)
   					$tstamp = 0;
   				else
 					$tstamp = mktime(0,0,0,$due_m,$due_d,$due_y);

				if($tstamp) {
					$task->task_past_due = true;
					$task->task_due_date=date("D, M d Y",$tstamp);
					$task->task_due_date_mdy=date("m/d/y",$tstamp);
				}
				
				list($due_y,$due_m,$due_d) = sscanf($row["task_updated_date"],"%d-%d-%d 00:00:00");
				if((empty($due_d) && empty($due_m) && empty($due_y)) || 1990>$due_y)
   					$tstamp = 0;
   				else
					$tstamp = mktime(0,0,0,$due_m,$due_d,$due_y);
					
				if($tstamp) {
					$task->task_updated_date=date("m/d/y",$tstamp);
				}
			
				list($due_y,$due_m,$due_d) = sscanf($row["task_reminder_date"],"%d-%d-%d 00:00:00");
	   			if((empty($due_d) && empty($due_m) && empty($due_y)) || 1990>$due_y)
   					$tstamp = 0;
   				else
 					$tstamp = mktime(0,0,0,$due_m,$due_d,$due_y);

 				if($tstamp) {
					$task->task_reminder_date=date("D, M d Y",$tstamp);
					$task->task_reminder_date_mdy=date("m/d/y",$tstamp);
 				}
				
				$task->task_url = cer_href(sprintf("my_cerberus.php?mode=tasks&pid=%d&tid=%d",
					$this->active_project->project_id,
					$task->task_id
					));
				
				$this->_add_task_to_project($task,$task->task_project_id);
			}
		}
		
	}
	
	function _add_task_to_project($task,$pid)
	{
		foreach($this->projects as $idx => $p)
		{
			if($p->project_id == $pid) {
				
				if($this->active_project->project_id == $p->project_id) {
					array_push($this->active_project->tasks,$task);
				}
				
				return true;
			}
		}
		
		return false;
	}

	function apply_filters($hide_completed=0,$only_mine=0,$category=0,$hide_completed_projects=0)
	{
		global $session;
		
		if($hide_completed_projects && is_array($this->projects))
		foreach($this->projects as $idx => $project) {
			if($project->tasks_incomplete == 0)
				unset($this->projects[$idx]);
		}
		
		if(is_array($this->active_project->tasks))
		foreach($this->active_project->tasks as $idx => $t) {
			
			if($hide_completed && $t->task_progress == 100) {
				unset($this->active_project->tasks[$idx]);
			}
			
			if($only_mine && $t->task_assigned_uid != $session->vars["login_handler"]->user_id) {
				unset($this->active_project->tasks[$idx]);
			}
			
			if($category && $t->task_project_category_id != $category) {
				unset($this->active_project->tasks[$idx]);
			}
		}
		
	}
	
	function _load_project_list()
	{
		global $session; // clean
		
		// [JAS]: Load projects from database
		$sql = ''.
		"SELECT f.project_id, f.project_name, f.project_manager_uid, f.project_acl, u.user_name, u.user_login, count( ".
		"IF (".
		"t.task_progress = 100, 1, NULL ".
		") ) AS tasks_complete, count( ".
		"IF (".
		"t.task_progress < 100, 1, NULL ".
		") ) AS tasks_incomplete ".
		"FROM tasks_projects f ".
		"LEFT JOIN tasks t ON ( t.task_project_id = f.project_id ) ".
		"LEFT JOIN user u ON ( f.project_manager_uid = u.user_id ) ".
		"GROUP BY f.project_id ".
		"ORDER BY f.project_name ASC ";
		
		$f_res = $this->db->query($sql);
		
		if($this->db->num_rows($f_res))
		{
			while($row = $this->db->fetch_row($f_res))
			{
				$new_project = new CER_MY_CERBERUS_TASK_PROJECT();
				$new_project->project_id = $row["project_id"];
				$new_project->project_name = stripslashes($row["project_name"]);
				$new_project->project_manager_uid = $row["project_manager_uid"];
				$new_project->project_manager_name = $row["user_name"];
				$new_project->project_manager_login = $row["user_login"];
				$new_project->project_acl = $row["project_acl"];
				$new_project->project_url = cer_href("my_cerberus.php?mode=tasks&pid=".$row["project_id"]);
				$new_project->tasks_complete = $row["tasks_complete"];
				$new_project->tasks_incomplete = $row["tasks_incomplete"];
				$new_project->task_count = $row["tasks_complete"] + $row["tasks_incomplete"];
				
				if($new_project->project_manager_uid == $session->vars["login_handler"]->user_id
					|| $session->vars["login_handler"]->user_superuser
					)
					$new_project->writable = true;
				
				array_push($this->projects,$new_project);
			}
		}
	}
	
	function _generate_heading_url($field="")
	{
		global $_SERVER;
		
		return cer_href(sprintf("%s?mode=tasks&pid=%d&form_submit=tasks_filter&sort_by=%s&sort_asc=%d",
				$_SERVER["PHP_SELF"],
				$this->active_project->project_id,
				$field,
				(($this->project_prefs["sort_by"] == $field && !empty($this->project_prefs["sort_asc"]))?0:1)
			),"task_list");
	}
	
	function set_heading_urls()
	{
		$this->active_project->heading_urls["manager_panel"] = cer_href(sprintf("%s?mode=tasks&pid=%d&pm_brief=%d&form_submit=tasks_filter",
				$_SERVER["PHP_SELF"],
				$this->active_project->project_id,
				(!empty($this->project_prefs["pm_brief"]) ? 0 : 1)
			));
		
		$this->active_project->heading_urls["task_summary"] = $this->_generate_heading_url("t.task_summary");
		$this->active_project->heading_urls["task_category"] = $this->_generate_heading_url("tc.category_name");
		$this->active_project->heading_urls["task_assigned"] = $this->_generate_heading_url("u.user_name");
		$this->active_project->heading_urls["task_priority"] = $this->_generate_heading_url("t.task_priority");
		$this->active_project->heading_urls["task_updated"] = $this->_generate_heading_url("t.task_updated_date");
		$this->active_project->heading_urls["task_due"] = $this->_generate_heading_url("t.task_due_date");
		$this->active_project->heading_urls["task_progress"] = $this->_generate_heading_url("t.task_progress");
	}	
	
	function set_active_project($pid=0)
	{
		if(is_array($this->projects))
		foreach($this->projects as $p_idx => $p) {
			if($p->project_id == $pid) { 
				$ptr = &$this->projects[$p_idx];
				$this->active_project = $ptr;
				
				$sql = sprintf("SELECT tc.category_id, tc.category_name FROM tasks_projects_categories tc WHERE tc.project_id = %d ".
					"ORDER BY tc.category_name",
						$this->active_project->project_id
					);
				$res = $this->db->query($sql);
				
				if($this->db->num_rows($res))
				{
					while($catr = $this->db->fetch_row($res))
					{
						$cat = new CER_MY_CERBERUS_TASK_PROJECT_CATEGORY();
						$cat->category_id = $catr["category_id"];
						$cat->category_name = stripslashes($catr["category_name"]);
						array_push($this->active_project->categories,$cat);
					}
				}
				
				$this->set_heading_urls();
				
				return; 
			}
		}
		
		return(false);		
	}
	
	function set_active_task($tid=0)
	{
		global $session; // [JAS]: Clean
		
		if(is_array($this->active_project->tasks))
		foreach($this->active_project->tasks as $t_idx => $t) {
			if($t->task_id == $tid) { 
				$ptr = &$this->active_project->tasks[$t_idx];
				$this->active_project->active_task = $ptr;
				
				$sql = "SELECT t.task_description FROM tasks t WHERE t.task_id = " . $t->task_id;
				$res = $this->db->query($sql);
				
				if($row = $this->db->grab_first_row($res))
				{ $this->active_project->active_task->task_description = stripslashes($row["task_description"]); }
				
				// [JAS]: See if the current user is allowed to edit this task				
				$uid = $session->vars["login_handler"]->user_id;
				if($t->task_assigned_uid == $uid
					|| $this->active_project->project_manager_uid == $uid
					|| $session->vars["login_handler"]->user_superuser
					)
					$this->active_project->active_task->writable = true;
								
				return; 
			}
		}
		
		return(false);		
	}
	
	function _load_task_notes($tid=0)
	{
		$sql = sprintf("SELECT n.note_id, n.task_id, n.note_poster_uid, u.user_login, n.note_timestamp, n.note_text ".
			"FROM tasks_notes n LEFT JOIN user u ON (n.note_poster_uid = u.user_id) LEFT JOIN tasks t ON (t.task_id = n.task_id) ".
			"WHERE t.task_project_id = %d ORDER BY n.note_timestamp ASC",
			$this->active_project->project_id		
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		while($row = $this->db->fetch_row($res))
		{
			$new_note = new CER_MY_CERBERUS_TASK_NOTE();
			$new_note->note_id = $row["note_id"];
			$new_note->task_id = $row["task_id"];
			$new_note->note_poster_uid = $row["note_poster_uid"];
			$new_note->note_poster_login = $row["user_login"];
			$new_note->note_timestamp = $row["note_timestamp"];
			$new_note->note_text = stripslashes($row["note_text"]);
			
			$this->_add_note_to_task($new_note,$new_note->task_id);
			
			$this->active_project->tasks->task_note_count++;
		}
	}
	
	function _add_note_to_task($note,$tid=0)
	{
		foreach($this->active_project->tasks as $idx => $t)
		{
			if($t->task_id == $tid) {
				if($this->active_project->active_task->task_id == $t->task_id) {
					array_push($this->active_project->active_task->task_notes,$note);
					$this->active_project->active_task->task_note_count++;
				}
			$this->active_project->tasks[$idx]->task_note_count++;
			return true;
			}
		}
		return false;
	}
	
	function _load_user_list()
	{
		global $cer_hash;
		
		foreach($cer_hash->get_user_hash() as $user)
			array_push($this->user_list,$user);
	}
	
	function _read_project_acls()
	{
		global $session; //clean
		
		foreach($this->projects as $p_idx => $p)
		{
			$users = $this->user_list; // copy the users list
			$user_in_acl = false;
			$acl_list = array();
			
			if(strlen($p->project_acl))
				$acl_list = explode(",",$p->project_acl);
			
			// [JAS]: Loop through this projects members
			foreach($acl_list as $acl_item)
			{
				if($acl_item == $session->vars["login_handler"]->user_id)
					$user_in_acl = true;
					
				// [JAS]: Loop through system users to set the project user pointers
				foreach($this->user_list as $u_idx => $u)
				{
					if($u->user_id == $acl_item)
					{
						unset($users[$u_idx]);
						$user_ptr = &$this->user_list[$u_idx];
						array_push($this->projects[$p_idx]->project_members,$user_ptr);
					}
				}
			}
			
			// [JAS]: Assign the rest to the available users pointer array
			foreach($users as $u_idx => $usr) {
				$user_ptr = &$this->user_list[$u_idx];
				array_push($this->projects[$p_idx]->available_users,$user_ptr);
			}
			
			// [JAS]: If this is the active project, update the cached info for the templates
			if($p->project_id == $this->active_project->project_id) {
				$this->active_project->project_members = $this->projects[$p_idx]->project_members;
				$this->active_project->available_users = $this->projects[$p_idx]->available_users;
			}

			// [JAS]: Hide Projects the current user isn't entitled to see
			if(!$session->vars["login_handler"]->user_superuser
				&& !$user_in_acl
				)
				{
					if($this->active_project->project_id == $p->project_id)
						$this->active_project= null;
						
					unset($this->projects[$p_idx]);
				}
		}
	}
	
};


class CER_MY_CERBERUS_TASK_PROJECT
{
	var $project_id = 0;
	var $project_name = null;
	var $project_manager_uid = 0;
	var $project_manager_name = null;
	var $project_manager_login = null;
	var $project_acl = null;
	var $project_members = array(); // array of pointers
	var $available_users = array(); // array of pointers
	var $task_count = 0;
	var $tasks_complete = 0;
	var $tasks_incomplete = 0;
	var $active_task=null;		// active task pointer
	var $heading_urls=array();
	var $categories=array();
	var $tasks = array();
	var $project_url="";
	var $writable=false;
};

class CER_MY_CERBERUS_TASK_PROJECT_CATEGORY
{
	var $category_id=0;
	var $category_name=null;
};

class CER_MY_CERBERUS_TASK
{
	var $task_id=0;
	var $task_summary=null;
	var $task_description=null;
	var $task_progress=0;
	var $task_created_uid=0;
	var $task_assigned_uid=0;
	var $task_assigned_user=null;
	var $task_assigned_login=null;
	var $task_priority=0;
	var $task_priority_string="Undefined";
	var $task_parent_id=0;
	var $task_project_id=0;
	var $task_project_category_id=0;
	var $task_project_category_name="";
	var $task_classification=0;
	var $task_created_date="00/00/00";
	var $task_updated_date="00/00/00";
	var $task_due_date="";
	var $task_due_date_mdy="00/00/00";
	var $task_past_due=false;
	var $task_reminder_date="";
	var $task_reminder_date_mdy="00/00/00";
	var $task_reminder_sent=0;
	var $task_notes=array();
	var $task_note_count=0;
	var $task_url;
	var $writable=false;
};

class CER_MY_CERBERUS_TASK_NOTE
{
	var $note_id=0;
	var $task_id=0;
	var $note_poster_uid=0;
	var $note_poster_login=null;
	var $note_timestamp=null;
	var $note_text=null;
};

?>