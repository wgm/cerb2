<?php

require_once (FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

// Modules
define("MODULE_HOME",1);
define("MODULE_KNOWLEDGEBASE",2);
define("MODULE_MY_ACCOUNT",3);
define("MODULE_OPEN_TICKET",4);
define("MODULE_TRACK_TICKETS",5);
define("MODULE_FORGOT_PW",6);
define("MODULE_REGISTER",7);
define("MODULE_ANNOUNCEMENTS",8);
define("MODULE_WELCOME",9);
define("MODULE_CONTACT",10);
define("MODULE_ADD_EMAIL",11);
define("MODULE_COMPANY_TICKETS",12);

class cer_PublicGUISettings
{
	var $db=null;
	var $settings=array();
	var $queues=array();

	function cer_PublicGUISettings($id=0)
	{
		$this->db = cer_Database::getInstance();
		$this->_set_defaults();
		$this->_load_settings($id);
		
		if(!empty($this->settings["pub_queues"])) {
			$this->queues = unserialize($this->settings["pub_queues"]);
			
			if(!empty($this->queues))
			foreach($this->queues as $idx => $q) {
				$this->queues[$idx]->queue_mask = stripslashes($this->queues[$idx]->queue_mask);
			}
			
			$qids = array_keys($this->queues);
			
			// [JAS]: Load up a current queue address for each public queue
			$sql = sprintf("SELECT qa.queue_id, CONCAT(qa.queue_address,'@',qa.queue_domain) As queue_addy ".
					"FROM queue_addresses qa ".
					"WHERE qa.queue_id IN (%s) ".
					"GROUP BY qa.queue_id ".
					"ORDER BY qa.queue_addresses_id ASC ",
						implode(',',$qids)
				);
			$qres = $this->db->query($sql);
			
			if($this->db->num_rows($qres)) {
				while($qrow = $this->db->fetch_row($qres)) {
					$qid = $qrow["queue_id"];
					$this->queues[$qid]->queue_address = $qrow["queue_addy"];
				}
			}
		}
	}
	
	function checkProfileID($id)
	{
		$sql = sprintf("SELECT p.profile_id FROM public_gui_profiles p WHERE p.profile_id = %d",
				$id
			);
		$res = $this->db->query($sql);
		
		if(!$this->db->num_rows($res)) {
			return false;
		}
		
		return true;
	}
	
	function _load_settings($id=0)
	{
		$sql = "SELECT * FROM public_gui_profiles WHERE profile_id = $id";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		{
			$cfg = $this->db->fetch_row($res);
			
			if(!empty($cfg))
			foreach($cfg as $idx=>$val)
			{
				$cfg_fld = $idx;
				$cfg_val = $val;
				if($cfg_val != "")
					$this->settings[$cfg_fld]=stripslashes($cfg_val);
			}
		}
		else {
			return false;
		}
	 }
	
	function _set_defaults()
	{
		$this->settings["pub_company_name"] = "";
		$this->settings["pub_company_email"] = "";
		$this->settings["pub_confirmation_subject"] = "Please confirm your new Support Center account for ##confirm_email##";
		$this->settings["pub_confirmation_body"] = "".
			"Hello!\r\n".
			"\r\n".
			"To activate your ##company_name## Support Center account, please follow the instructions below.\r\n".
			"\r\n".
			"======================================================================\r\n".
			"To automatically confirm your registration:\r\n".
			"======================================================================\r\n".
			"##confirm_url##\r\n".
			"\r\n".
			"\r\n".
			"======================================================================\r\n".
			"To confirm your registration manually:\r\n".
			"======================================================================\r\n".
			"##site_url##\r\n".
			"\r\n".
			"E-mail: ##confirm_email##\r\n".
			"Confirmation Code: ##confirm_code##\r\n".
			"\r\n".
			"\r\n".
			"======================================================================\r\n".
			"We look forward to serving you!\r\n".
			"======================================================================\r\n".
			"\r\n".
			"##company_name##\r\n".
			"##company_email##\r\n";
			
		$this->queues = array();
		
		$this->settings["pub_mod_registration"] = "0";
		$this->settings["pub_mod_registration_mode"] = "pass";
		$this->settings["pub_mod_kb"] = "0";
		$this->settings["pub_mod_my_account"] = "0";
		$this->settings["pub_mod_open_ticket"] = "0";
		$this->settings["pub_mod_open_ticket_locked"] = "0";
		$this->settings["pub_mod_track_tickets"] = "0";
		$this->settings["pub_mod_announcements"] = "0";
		$this->settings["pub_mod_welcome"] = "0";
		$this->settings["pub_mod_welcome_title"] = "Welcome to the Support Center!";
		$this->settings["pub_mod_welcome_text"] = 
			"Welcome to our Support Center!";
		$this->settings["pub_mod_contact"] = "0";
		$this->settings["pub_mod_contact_text"] = 
			"Company Name<br>\r\n".
			"Mailing Address<br>\r\n".
			"City, State, Zip<br>\r\n".
			"Country<br>\r\n".
			"<br>\r\n".
			"Phone<br>\r\n".
			"<br>\r\n".
			"Hours<br>";
		$this->settings["login_plugin_id"] = 0;
	}
	
};

class cer_PublicGUIQueue  // Sync'd with main GUI.
{
	var $queue_id=0;
	var $queue_name=null;
	var $queue_mask=null;
	var $queue_field_group=null;
	var $queue_address=null;
};

class cer_PublicGUIFieldGroups
{
	var $db=null;
	var $groups=array();
	var $active_group=null;
	
	function cer_PublicGUIFieldGroups($fid=0)
	{
		$this->db = cer_Database::getInstance();
		$this->_load_groups($fid);
	}
	
	function _load_groups($fid=0)
	{
		$sql = "SELECT g.group_id, g.group_name, g.group_fields ".
			"FROM public_gui_fields g ".
			(($fid) ? "WHERE g.group_id = $fid " : "").
			"ORDER BY g.group_name";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				$group = new cer_PublicGUIGroup();
				$group->group_id = $row["group_id"];
				$group->group_name = $row["group_name"];
				$group->fields = unserialize(stripslashes($row["group_fields"]));
				
				array_push($this->groups,$group);
				
				if($fid && $group->group_id = $fid)
					$this->active_group = &$this->groups[count($this->groups)-1];
			}
		}
	}
	
	function field_exists(&$ptr,$fid)
	{
		if(!empty($ptr) && is_array($ptr))
		foreach($ptr as $p)
		{
			if($p->field_id == $fid)
				return $p;
		}
		
		return false;
	}
};

class cer_PublicGUIGroup
{
	var $group_id = null;
	var $group_name = null;
	var $fields = null;
	
	function cer_PublicGUIGroup()
	{
		$this->fields = array();
	}
};

class cer_PublicGUIGroupField
{
	var $field_id=null;
	var $field_name=null;
	var $field_type=null;
	var $field_option=null;
	var $field_value=null;
};

?>