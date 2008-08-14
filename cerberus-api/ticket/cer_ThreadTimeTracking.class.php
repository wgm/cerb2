<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

class cer_ThreadTimeTracking
{
	var $thread_time_id = null;
	var $ticket_id = 0;
	var $date = null;
	var $hrs_spent = null;
	var $hrs_chargeable = null;
	var $hrs_billable = null;
	var $hrs_payable = null;
	var $working_agent_id = 0;
	var $working_agent_string = null;
	var $summary = null;
	var $date_billed = null;
	var $created_date = null;
	var $created_by_id = null;
	var $created_by_string = null;
	var $custom_fields = null;
	
	// [JAS]: Display variables (not from DB)
	var $date_string = null;
	var $date_mdy = null;
	var $date_hr = null;
	var $date_min = null;
	var $date_ampm = null;

	var $date_billed_string = null;
	
	function format() {
		$date = new cer_DateTime($this->date);
		$this->date_string = $date->getDate();
		$this->date_mdy = $date->getDate("%m/%d/%y");
		$this->date_hr = $date->getDate("%I");
		$this->date_min = (5*round($date->getDate("%M")/5)); // [JAS]: Steps of 5 mins
		$this->date_ampm = strtolower($date->getDate("%p"));
		
		$date = new cer_DateTime($this->created_date);
		$this->created_date_string = $date->getDate();
		
		$date = new cer_DateTime($this->date_billed);
		if($date->getDate("%Y") < 1990) { // [JAS]: Handle blank dates
			$this->date_billed_string = "";
			$this->date_billed_mdy = "";
		}
		else {
			$this->date_billed_string = $date->getDate("%a %b %d %Y");
			$this->date_billed_mdy = $date->getDate("%m/%d/%y");
		}
	}
	
};

class cer_ThreadTimeTrackingHandler
{
	var $db = null;
	var $custom_handler = null;
	var $time_threads = array();
	
	function cer_ThreadTimeTrackingHandler() {
		$this->db = cer_Database::getInstance();
		$this->custom_handler = new cer_CustomFieldGroupHandler();
	}
	
	function loadThreadsByTicketId($ticket) {
		$sql = sprintf("SELECT tt.thread_time_id, tt.ticket_id, tt.thread_time_date, tt.thread_time_hrs_spent, tt.thread_time_hrs_chargeable, tt.thread_time_hrs_billable, tt.thread_time_hrs_payable, ".
						"tt.thread_time_working_agent_id, thread_time_summary, thread_time_date_billed, ".
						"tt.thread_time_created_date, tt.thread_time_created_by_id, u.user_name, u.user_login, cu.user_name as create_name, cu.user_login as create_login ".
						"FROM thread_time_tracking tt ".
						"LEFT JOIN user u ON (tt.thread_time_working_agent_id = u.user_id) ".
						"LEFT JOIN user cu ON (tt.thread_time_created_by_id = cu.user_id) ".
						"WHERE tt.ticket_id = %d ".
						"ORDER BY tt.thread_time_date ASC ",
					$ticket
				);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$time_entry = new cer_ThreadTimeTracking();
					$time_entry->thread_time_id = $row["thread_time_id"];
					$time_entry->ticket_id = $row["ticket_id"];
					$time_entry->date = $row["thread_time_date"];
					$time_entry->hrs_spent = $row["thread_time_hrs_spent"];
					$time_entry->hrs_chargeable = $row["thread_time_hrs_chargeable"];
					$time_entry->hrs_billable = $row["thread_time_hrs_billable"];
					$time_entry->hrs_payable = $row["thread_time_hrs_payable"];
					$time_entry->working_agent_id = $row["thread_time_working_agent_id"];
					$time_entry->working_agent_string = sprintf("%s (%s)",stripslashes($row["user_name"]),stripslashes($row["user_login"]));
					$time_entry->summary = stripslashes($row["thread_time_summary"]);
					$time_entry->date_billed = $row["thread_time_date_billed"];
					$time_entry->created_date = $row["thread_time_created_date"];
					$time_entry->created_by_id = $row["thread_time_created_by_id"];
					$time_entry->created_by_string = sprintf("%s (%s)",stripslashes($row["create_name"]),stripslashes($row["create_login"]));
					$time_entry->format();
				$this->time_threads[] = $time_entry;
			}
			
			$this->_loadEntryCustomFields();
		}
	}
	
	function _loadEntryCustomFields() {
		$entity_idx_ptrs = array();
		$bind_gid = 0;
		
		if(empty($this->time_threads))
			return;

		$this->custom_handler->loadGroupTemplates();
		
		$field_binding = new cer_CustomFieldBindingHandler();
		$bind_gid = $field_binding->getEntityBinding(ENTITY_TIME_ENTRY);
		
		foreach($this->time_threads as $idx => $entry) {
			
			// [JAS]: If a custom field group instance wasn't created for this entity earlier,
			//	instantiate it now.
			$result = $this->custom_handler->load_entity_groups(ENTITY_TIME_ENTRY,$entry->thread_time_id);
			
			if(!$result && $bind_gid) {
				$inst_id = $this->custom_handler->addGroupInstance(ENTITY_TIME_ENTRY,$entry->thread_time_id,$bind_gid);
				$this->custom_handler->load_entity_groups(ENTITY_TIME_ENTRY,$entry->thread_time_id); // reload
			}
				
			$entity_idx_ptrs[$entry->thread_time_id] = &$this->time_threads[$idx];
		}
		
		foreach($this->custom_handler->group_instances as $idx => $inst) {
			$entity_idx_ptrs[$inst->entity_index]->custom_fields = $this->custom_handler->group_instances[$idx];
		}
	}
	
	function updateTimeEntry($time_entry) {
		
		if(strtolower(get_class($time_entry)) != "cer_threadtimetracking")
			return false;
		
		$sql = sprintf("UPDATE thread_time_tracking ".
					   "SET thread_time_date = '%s', thread_time_hrs_spent = %0.2f, thread_time_hrs_chargeable = %0.2f, thread_time_hrs_billable = %0.2f, thread_time_hrs_payable = %0.2f, thread_time_working_agent_id = %d, thread_time_summary = %s, thread_time_date_billed = '%s' ".
					   "WHERE thread_time_id = %d",
					$time_entry->date,
					$time_entry->hrs_spent,
					$time_entry->hrs_chargeable,
					$time_entry->hrs_billable,
					$time_entry->hrs_payable,
					$time_entry->working_agent_id,
					$this->db->escape($time_entry->summary),
					$time_entry->date_billed,
					$time_entry->thread_time_id
				);
		$this->db->query($sql);
		
		return true;
	}
	
	function deleteTimeEntry($time_id) {
		if(empty($time_id))
			return false;
			
		$sql = sprintf("DELETE FROM thread_time_tracking WHERE thread_time_id = %d",
				$time_id
			);
		$this->db->query($sql);
		
		return true;
	}
	
	function createTimeEntry($time_entry) {
		global $session;
		
		if(strtolower(get_class($time_entry)) != "cer_threadtimetracking")
			return false;
		
		// [JAS]: \todo Add created_by and created_date fields to insert
		
		$sql = sprintf("INSERT INTO thread_time_tracking (ticket_id, thread_time_date, thread_time_hrs_spent, thread_time_hrs_chargeable, thread_time_hrs_billable, thread_time_hrs_payable, thread_time_working_agent_id, thread_time_summary, thread_time_date_billed, thread_time_created_date, thread_time_created_by_id) ".
					   "VALUES (%d,'%s',%0.2f,%0.2f,%0.2f,%0.2f,%d,%s,'%s',NOW(),%d)",
					$time_entry->ticket_id,
					$time_entry->date,
					$time_entry->hrs_spent,
					$time_entry->hrs_chargeable,
					$time_entry->hrs_billable,
					$time_entry->hrs_payable,
					$time_entry->working_agent_id,
					$this->db->escape($time_entry->summary),
					$time_entry->date_billed,
					$session->vars["login_handler"]->user_id
				);
		$this->db->query($sql);
		
		$time_entry_id = $this->db->insert_id();
		
		if($time_entry_id)
			return $time_entry_id;
		else
			return false;
	}
};

?>