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

require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_TicketSummaryList.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

class cer_CompanyHandler {
	var $db = null;
	var $companies = array();
	var $sla_handler = null;
	var $custom_handler = null;
	
	var $set_from = 0;
	var $set_to = 0;
	var $set_of = 0;  // number of companies in the subset
	var $set_url_prev = null;
	var $set_url_next = null;
	
	function cer_CompanyHandler() {
		$this->db = cer_Database::getInstance();
		$this->sla_handler = new cer_SLA();
		$this->custom_handler = new cer_CustomFieldGroupHandler();
	}
	
	function _loadCompaniesByIds($ids=array(),$load_custom_fields=true) {
		if(!is_array($ids)) return false;
		
		$sql = sprintf("SELECT c.id, c.name, c.sla_id, c.sla_expire_date, c.company_account_number, c.company_mailing_address, ".
				"c.company_mailing_city, c.company_mailing_state, c.company_mailing_zip, c.company_mailing_country_old, c.company_mailing_country_id, cou.country_name as company_mailing_country_name, c.company_phone, ".
				"c.company_fax, c.company_website, c.company_email, count(pu.company_id) as num_users ".
				"FROM company c ".
				"LEFT JOIN public_gui_users pu ON (pu.company_id = c.id) ".
				"LEFT JOIN country cou ON (cou.country_id = c.company_mailing_country_id) ".
				"%s ".
				"GROUP BY c.id ".
				"ORDER BY c.name ",
					((!empty($ids)) ? "WHERE c.id IN (" . implode(',',$ids) . ")"  : "" )
			);
		$res = $this->db->query($sql);
		
		$this->_loadCompaniesFromDbRes($res,$load_custom_fields);
	}
	
	function _loadCompaniesByPage($p=0,$limit=10) {
		$sql = sprintf("SELECT c.id, c.name, c.sla_id, c.sla_expire_date, c.company_account_number, c.company_mailing_address, ".
				"c.company_mailing_city, c.company_mailing_state, c.company_mailing_zip, c.company_mailing_country_old, c.company_mailing_country_id, cou.country_name as company_mailing_country_name, c.company_phone, ".
				"c.company_fax, c.company_website, c.company_email, count(pu.company_id) as num_users ".
				"FROM company c ".
				"LEFT JOIN public_gui_users pu ON (pu.company_id = c.id) ".
				"LEFT JOIN country cou ON (cou.country_id = c.company_mailing_country_id) ".
				"%s ". // search params
				"GROUP BY c.id ".
				"ORDER BY c.name ".
				"LIMIT %d, %d ",
					((!empty($ids)) ? "WHERE c.id IN (" . $ids . ")"  : "" ),
					$p * $limit,
					$limit
			);
		$res = $this->db->query($sql);
		
		$this->_loadCompaniesFromDbRes($res);
		
		// Override totals for paging since it's potentially a subset of a larger set
		$sql = "SELECT count(c.id) as total_companies FROM company c";
		$res = $this->db->query($sql);
		if($row = $this->db->grab_first_row($res)) {
			$this->set_of = $row["total_companies"];
		}
		
		$this->set_from = $p * $limit + 1;
		$this->set_to = max($this->set_from + count($this->companies) - 1,0);
	
		if(function_exists("cer_href") && $this->set_of > $this->set_to) {
			$this->set_url_next = cer_href(sprintf("clients.php?cp=%d",
						$p + 1
					),"companies");
		}
					
		if($p && function_exists("cer_href")) {
			$this->set_url_prev = cer_href(sprintf("clients.php?cp=%d",
						$p - 1
					),"companies");
		}
	
	}
	
	function _loadCompaniesBySearch($query,$p=0,$limit=10) {
		
		$find = '%' . stripslashes($query) . '%';

		$sql = sprintf("SELECT c.id, c.name, c.sla_id, c.sla_expire_date, c.sla_expire_date, c.company_account_number, c.company_mailing_address, ".
				"c.company_mailing_city, c.company_mailing_state, c.company_mailing_zip, c.company_mailing_country_old, c.company_mailing_country_id, cou.country_name as company_mailing_country_name, c.company_phone, ".
				"c.company_fax, c.company_website, c.company_email, count(pu.company_id) as num_users ".
				"FROM company c ".
				"LEFT JOIN public_gui_users pu ON (pu.company_id = c.id) ".
				"LEFT JOIN country cou ON (cou.country_id = c.company_mailing_country_id) ".
				"WHERE c.name LIKE %s ". // search params
				"GROUP BY c.id ".
				"ORDER BY c.name ",
					$this->db->escape($find)
			);
		$res = $this->db->query($sql);
		
		$this->_loadCompaniesFromDbRes($res);
	}
	
	function _loadCompaniesFromDbRes($res,$load_custom_fields=true) {
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$new_company = new cer_Company();
					$new_company->company_id = $row["id"];
					$new_company->company_account_number = stripslashes($row["company_account_number"]);
					$new_company->company_mailing_address = stripslashes($row["company_mailing_address"]);
					$new_company->company_mailing_city = stripslashes($row["company_mailing_city"]);
					$new_company->company_mailing_state = stripslashes($row["company_mailing_state"]);
					$new_company->company_mailing_zip = stripslashes($row["company_mailing_zip"]);
					$new_company->company_mailing_country_old = $row["company_mailing_country_old"];
					$new_company->company_mailing_country_id = $row["company_mailing_country_id"];
					$new_company->company_mailing_country_name = stripslashes($row["company_mailing_country_name"]);
					$new_company->company_phone = stripslashes($row["company_phone"]);
					$new_company->company_fax = stripslashes($row["company_fax"]);
					$new_company->company_website = stripslashes($row["company_website"]);
					$new_company->company_email = stripslashes($row["company_email"]);
					
					$new_company->num_public_users = $row["num_users"];
					$new_company->sla_id = $row["sla_id"];
					
					if($row["sla_expire_date"] != "0000-00-00 00:00:00") {
						$new_company->sla_expire_date = new cer_DateTime($row["sla_expire_date"]);
					}
					
					if(function_exists("cer_href")) {
						$new_company->url_view = cer_href(sprintf("clients.php?mode=c_view&id=%d",$new_company->company_id));
					}
					
					if(!empty($row["name"])) {
						$new_company->company_name = stripslashes($row["name"]);
					}
					
					if($row["sla_id"]) {
						$new_company->sla_ptr = &$this->sla_handler->plans[$new_company->sla_id];
					}
					
				$this->companies[$new_company->company_id] = $new_company;
				
				$this->set_of++;
				$this->set_to++;
			}

			$this->set_from = 1;
			
			// [JAS]: Make sure we're not wasting load time when the handler object doesn't need the custom fields
			if($load_custom_fields) 
				$this->_loadCustomFields();
		}
	}
	
	function _loadCustomFields() {
		$entity_idx_ptrs = array();
		$bind_gid = 0;
		
		if(empty($this->companies))
			return;

		$this->custom_handler->loadGroupTemplates();
		
		$field_binding = new cer_CustomFieldBindingHandler();
		$bind_gid = $field_binding->getEntityBinding(ENTITY_COMPANY);
		
		foreach($this->companies as $idx => $company) {
			
			// [JAS]: If a custom field group instance wasn't created for this entity earlier,
			//	instantiate it now.
			// \todo This function in each custom binding handler so far (company/contact/time-entry) could 
			//		become part of the custom fields/binding API -- just pass ENTITY_*, entity_index + array ptr
			$result = $this->custom_handler->load_entity_groups(ENTITY_COMPANY,$company->company_id);
			
			if(!$result && $bind_gid) {
				$inst_id = $this->custom_handler->addGroupInstance(ENTITY_COMPANY,$company->company_id,$bind_gid);
				$this->custom_handler->load_entity_groups(ENTITY_COMPANY,$company->company_id); // reload
			}
				
			$entity_idx_ptrs[$company->company_id] = &$this->companies[$idx];
		}
		
		foreach($this->custom_handler->group_instances as $idx => $inst) {
			$entity_idx_ptrs[$inst->entity_index]->custom_fields = $this->custom_handler->group_instances[$idx];
		}
	}
	
};

class cer_Company {
	var $db;
	
	var $company_id = null;
	var $company_name = "(not set)";
	var $company_account_number = null;
	var $company_mailing_address = null;
	var $company_mailing_city = null;
	var $company_mailing_state = null;
	var $company_mailing_zip = null;
	var $company_mailing_country_id = null;
	var $company_phone = null;
	var $company_fax = null;
	var $company_website = null;
	var $company_email = null;
	
	var $sla_id = null;
	var $sla_expire_date = null;
	var $sla_ptr = null;
	
	var $num_public_users = 0;
	
	var $url_view = null;
	
	var $open_tickets = null;
	
	var $custom_fields = null;
	
	function cer_Company() {
		$this->db = cer_Database::getInstance();
	}
	
	function loadOpenTickets() {
		$this->open_tickets = new cer_TicketSummaryList();
		$this->open_tickets->summary_title = "Open Tickets from " . ((!empty($this->company_name)) ? $this->company_name : "this Company");
		$this->open_tickets->loadCompanyTickets($this->company_id);
	}
};

?>