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
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/company/cer_Company.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_TicketSummaryList.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

class cer_PublicUserHandler {
	var $db = null;
	var $users = array();
	var $company_handler = null;
	var $users_by_email = array();
	var $set_to = 0;
	var $set_from = 0;
	var $set_of = 0;
	var $set_title = "Registered Contacts";
	var $set_url_prev = null;
	var $set_url_next = null;
	var $custom_handler = null;
	
	function cer_PublicUserHandler() {
		$blank_ary = array();
		$this->db = cer_Database::getInstance();
		$this->company_handler = new cer_CompanyHandler();
		$this->company_handler->_loadCompaniesByIds($blank_ary,false);
		$this->custom_handler = new cer_CustomFieldGroupHandler();
	}
	
	function loadUsersByPage($p=0,$limit=10) {
		$this->set_title = "Registered Contacts";
		
		$sql = sprintf("SELECT pu.public_user_id, pu.name_salutation, pu.name_first, pu.name_last, pu.`public_access_level`, pu.company_id, a.address_id, a.address_address ".
				"FROM public_gui_users pu, address a ".
				"WHERE pu.public_user_id = a.public_user_id ".
				"ORDER BY a.address_address ".
				"LIMIT %d, %d ",
					$p * $limit,
					$limit
			);
		$res = $this->db->query($sql);
		
		$this->_loadUsersFromDbRes($res);
		
		// [JAS]: Override the count, since we're paging through a larger list of addresses
		// [BGH]: useing a join instead of a Not Equals for speed
		$sql = "SELECT count(*) as total_addresses FROM address a, public_gui_users pgu WHERE a.public_user_id=pgu.public_user_id";
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			$this->set_of = $row["total_addresses"];
		}
		
		$this->set_from = $p * $limit + 1;
		$this->set_to = max($this->set_from + count($this->users_by_email) - 1,0);
		
		if(function_exists("cer_href") && $this->set_of > $this->set_to) {
			$this->set_url_next = cer_href(sprintf("clients.php?up=%d",
						$p + 1
					),"users");
		}
					
		if($p && function_exists("cer_href")) {
			$this->set_url_prev = cer_href(sprintf("clients.php?up=%d",
						$p - 1
					),"users");
		}
	}	
	
	function loadUsersBySearch($query) {

		$this->set_title = sprintf("Registered Contacts matching *%s*",
				stripslashes($query)
			);
		
		$find = '%' . stripslashes($query) . '%';
		
		$sql = sprintf("SELECT pu.public_user_id, pu.name_salutation, pu.name_first, pu.name_last, pu.`public_access_level`, pu.company_id, a.address_id, a.address_address ".
				"FROM public_gui_users pu, address a ".
				"LEFT JOIN company c ON (pu.company_id = c.id) ".
				"WHERE pu.public_user_id = a.public_user_id ".
				"AND ( ". 						// search params
				"pu.name_first LIKE %s ". 
				"OR pu.name_last LIKE %s ". 
				"OR a.address_address LIKE %s ".
				"OR c.name LIKE %s ".
				") ".							 // end search params
				"ORDER BY a.address_address ",
					$this->db->escape($find),
					$this->db->escape($find),
					$this->db->escape($find),
					$this->db->escape($find)
			);
		$res = $this->db->query($sql);
		
		$this->_loadUsersFromDbRes($res);
	}
	
	function loadUsersByCompany($id) {
		$this->set_title = "Registered Contacts by E-mail Address from " . $this->company_handler->companies[$id]->company_name;
		
		$sql = sprintf("SELECT p.`public_user_id` , p.`name_salutation`, p.`name_first`, p.`name_last`, p.`public_access_level` , p.`mailing_address`, p.`mailing_city`, ".
			"p.`mailing_state` , p.`mailing_zip` , p.`mailing_country_old` , p.`phone_work` , p.`phone_home` , p.`phone_mobile`, ".
			"p.`phone_fax` , p.`password`, p.`company_id`, a.address_id, a.address_address ".
				"FROM `public_gui_users` p ".
				"LEFT JOIN address a ON (p.public_user_id = a.public_user_id) ".
				"WHERE p.`company_id` = %d ".
				"ORDER BY a.address_address ",
					$id
			);
		$res = $this->db->query($sql);
		
		$this->_loadUsersFromDbRes($res);
	}
	
	function loadUsersByIds($ids = array()) {
		if(!is_array($ids)) return false;
		
		$this->set_title = "Registered Contact";
		
		$sql = sprintf("SELECT p.`public_user_id` , p.`name_salutation`, p.`name_first`, p.`name_last`, p.`public_access_level` , p.`mailing_address`, p.`mailing_city`, ".
			"p.`mailing_state` , p.`mailing_zip` , p.`mailing_country_old` , p.`phone_work` , p.`phone_home` , p.`phone_mobile`, ".
			"p.`phone_fax` , p.`password`, p.`company_id`, a.address_id, a.address_address ".
				"FROM `public_gui_users` p, address a ".
				"WHERE p.public_user_id = a.public_user_id ".
				"%s ",
					((!empty($ids)) ? "AND p.`public_user_id` IN (" . implode(',',$ids) . ")" : "")
			);
		$res = $this->db->query($sql);
		
		$this->_loadUsersFromDbRes($res);
	}
	
	function _loadUsersFromDbRes($res) {
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				
				$pub_id = $row["public_user_id"];
				
				if(!isset($this->users[$pub_id])) {
					$new_user = new cer_PublicUser();
						$new_user->public_user_id = $row["public_user_id"];
						$new_user->account_access_level = $row["public_access_level"];
						$new_user->account_company_id = $row["company_id"];
						$new_user->account_address = stripslashes($row["mailing_address"]);
						$new_user->account_city = stripslashes($row["mailing_city"]);
						$new_user->account_state = stripslashes($row["mailing_state"]);
						$new_user->account_zip = stripslashes($row["mailing_zip"]);
						$new_user->account_country = stripslashes($row["mailing_country_old"]);
						$new_user->account_phone_work = stripslashes($row["phone_work"]);
						$new_user->account_phone_home = stripslashes($row["phone_home"]);
						$new_user->account_phone_mobile = stripslashes($row["phone_mobile"]);
						$new_user->account_phone_fax = stripslashes($row["phone_fax"]);
						$new_user->account_password = stripslashes($row["password"]);
						
						if(function_exists("cer_href")) {
							$new_user->url_view = cer_href(sprintf("clients.php?mode=u_view&id=%d",$new_user->public_user_id));
						}
						
						if(!empty($row["name_first"])) {
							$new_user->account_name_first = stripslashes($row["name_first"]);
						}
						
						if(!empty($row["name_last"])) {
							$new_user->account_name_last = stripslashes($row["name_last"]);
						}
						
						if($row["company_id"]) {
							$new_user->company_ptr = &$this->company_handler->companies[$row["company_id"]];
						}
	
					$this->users[$new_user->public_user_id] = $new_user;
					
				} // new user
				
				$this->users[$pub_id]->email_addresses[$row["address_id"]] = stripslashes($row["address_address"]);
				$this->users[$pub_id]->total_addresses++;
				$this->users_by_email[$row["address_address"]] = &$this->users[$pub_id];
			}

			// Generate paging totals			
			$this->set_from = (($this->users_by_email) ? 1 : 0);
			$this->set_to = max($this->set_from + count($this->users_by_email) - 1, 0);
			$this->set_of = count($this->users_by_email);
			
			$this->_loadCustomFields();
		}	
	}
	
	function _loadCustomFields() {
		$entity_idx_ptrs = array();
		$bind_gid = 0;
		
		if(empty($this->users))
			return;

		$this->custom_handler->loadGroupTemplates();
		
		$field_binding = new cer_CustomFieldBindingHandler();
		$bind_gid = $field_binding->getEntityBinding(ENTITY_CONTACT);
		
		foreach($this->users as $idx => $user) {
			
			// [JAS]: If a custom field group instance wasn't created for this entity earlier,
			//	instantiate it now.
			// \todo This function in each custom binding handler so far (company/contact/time-entry) could 
			//		become part of the custom fields/binding API -- just pass ENTITY_*, entity_index + array ptr
			$result = $this->custom_handler->load_entity_groups(ENTITY_CONTACT,$user->public_user_id);
			
			if(!$result && $bind_gid) {
				$inst_id = $this->custom_handler->addGroupInstance(ENTITY_CONTACT,$user->public_user_id,$bind_gid);
				$this->custom_handler->load_entity_groups(ENTITY_CONTACT,$user->public_user_id); // reload
			}
				
			$entity_idx_ptrs[$user->public_user_id] = &$this->users[$idx];
		}
		
		foreach($this->custom_handler->group_instances as $idx => $inst) {
			$entity_idx_ptrs[$inst->entity_index]->custom_fields = $this->custom_handler->group_instances[$idx];
		}
	}
	
}

class cer_PublicUser {
	var $db = null;
	var $public_user_id = null;
	var $company_ptr = null;
	
	var $account_name_salutation = null;
	var $account_name_first = null;
	var $account_name_last = null;
	var $account_access_level = null;
	var $account_company_id = null;
	var $account_password = null;
	var $account_address = null;
	var $account_city = null;
	var $account_state = null;
	var $account_zip = null;
	var $account_country = null;
	var $account_phone_work = null;
	var $account_phone_home = null;
	var $account_phone_mobile = null;
	var $account_phone_fax = null;
	
	var $account_register_email = null;
	var $account_register_code = null;
	
	var $total_addresses = 0;
	var $email_addresses = array();
	
	var $open_tickets = null;
	
	var $custom_fields = null;
	
	function cer_PublicUser() {
		$this->db = cer_Database::getInstance();
	}
	
	function loadOpenTickets() {
		$this->open_tickets = new cer_TicketSummaryList();
		$this->open_tickets->summary_title = "Open Tickets from " . ((!empty($this->account_name_first) && !empty($this->account_name_last)) ? $this->account_name_first . " " . $this->account_name_last . " of " . $this->company_ptr->company_name : "this Contact");
		$this->open_tickets->loadUsersTickets(array($this->public_user_id));
	}
};

?>