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
| File: ticket_search.class.php
|
| Purpose: Ticket search box functionality
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

// [JAS]: Set up the global scope for search vars
@$psearch_sender = $session->vars["psearch_sender"];
@$psearch_status = $session->vars["psearch_status"];
@$psearch_subject = $session->vars["psearch_subject"];
@$psearch_content = $session->vars["psearch_content"];
@$psearch_company = $session->vars["psearch_company"];
@$psearch_date = $session->vars["psearch_date"];
@$psearch_fdate = $session->vars["psearch_fdate"];
@$psearch_tdate = $session->vars["psearch_tdate"];
@$advsearch = $_REQUEST["advsearch"];

if(!isset($p_qid)) $p_qid = "";
if(!isset($psearch_sender)) $psearch_sender="";
if(!isset($psearch_owner)) $psearch_owner="";
if(!isset($psearch_subject)) $psearch_subject="";
if(!isset($psearch_content)) $psearch_content="";
if(!isset($psearch_company)) $psearch_company="";
if(!isset($psearch_date)) $psearch_date="";
if(!isset($psearch_fdate)) $psearch_fdate="";
if(!isset($psearch_tdate)) $psearch_tdate="";
if(!isset($p_qid)) $p_qid="";


// [JAS]: Advanced/Basic Search Mode Toggle Code
if(isset($advsearch))
{
	if($advsearch) 
		$session->vars["psearch"]->params["advsearch"] = 1;
	else 
		$session->vars["psearch"]->params["advsearch"] = 0;
}
else if (!isset($session->vars["psearch"]->params["advsearch"])) { 
	$session->vars["psearch"]->params["advsearch"] = 0;
}

class CER_TICKET_SEARCH_BOX
{
	var $db;
	
	var $search_toggle_url = null;
	var $search_toggle_text = null;
	var $search_toggle_mode = null;
	
	var $search_status_options = array();
	var $search_queue_options = array();
	var $search_owner_options = array();
	var $search_company_options = array();
	
	var $field_groups = array();
	var $field_groups_field_ids = null;
	var $field_values = array();
	
	function CER_TICKET_SEARCH_BOX()
	{
		global $session; // [JAS]: Clean this up
		global $status_options; // clean
		global $cerberus_translate; // clean
		global $cer_hash; // clean
		
		// Our database handler reference
		$this->db = cer_Database::getInstance();
		
		// The search options for ticket status
		$this->search_status_options = array('' => ' - ' . LANG_SEARCH_ANY_STATUS . ' - ',
											 '-1' => ' - ' . LANG_SEARCH_ANY_ACTIVE . ' - '
											 );
		foreach($status_options as $value) {
			$this->search_status_options[$value] = $cerberus_translate->translate_status($value);
		}
		
		// The search options for ticket queue
		$this->search_queue_options = array('' => ' - ' . LANG_SEARCH_ANY_QUEUE . ' - '); 
		foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $idx => $queue)
		{
			$this->search_queue_options[$queue->queue_id] = $queue->queue_name;
		}
		
		// The search options for ticket owner
		$this->search_owner_options = array('-1' => ' - ' . LANG_SEARCH_ANY_OWNER . ' - ',
											'0' => 'Nobody'
											);
		foreach($cer_hash->get_user_hash(HASH_USER_ANY_Q) as $idx => $user)
		{
			$this->search_owner_options[$user->user_id] = $user->user_login;
		}
		
		// The search options for company list
		$this->search_company_options = array('' => ' - ' . LANG_SEARCH_ANY_COMPANY . ' - ',
											  );
		foreach($cer_hash->get_company_hash(HASH_COMPANY_ALL) as $idx => $company)
		{
			$this->search_company_options[$company->company_id] = $company->company_name;
		}
		
		// The quick/advanced search toggle link
		if($session->vars["psearch"]->params["advsearch"] != 1) { 
			$this->search_toggle_url = cer_href($_SERVER['PHP_SELF'] . "?advsearch=1","search_box");
			$this->search_toggle_text = LANG_SEARCH_ADVANCED . " &gt;&gt;";
			$this->search_toggle_mode = "quick";
		} else { 
			$this->search_toggle_url = cer_href($_SERVER['PHP_SELF'] . "?advsearch=0","search_box");
			$this->search_toggle_text = "&lt;&lt; " . LANG_SEARCH_QUICK;
			$this->search_toggle_mode = "advanced";
		}
		 
		// [JAS]: If we're in advanced search mode, draw the custom field groups.
		if($session->vars["psearch"]->params["advsearch"]) {
			$this->field_groups = new cer_CustomFieldGroupHandler();
			$this->field_groups->loadGroupTemplates();
			
			// [JAS]: A list of all the custom field IDs we're drawing, so the search
			//	page knows what values to check for on the $_REQUEST side.
			$ids = array();
			
			foreach($this->field_groups->group_templates as $g) {
				foreach($g->fields as $f) {
					$id = $f->field_id;
					$ids[] = $id;
					$this->field_values[$id] = @$session->vars["psearch"]->params["search_field_" . $id];
				}
			}
			
			if(count($ids)) {
				$this->field_groups_field_ids = implode(',',$ids);
			}
		}		
	}

};


class CER_TICKET_SEARCH_BOX_FIELD
{
	var $field_id = 0;
	var $field_name = null;
	var $field_type = null;
	var $field_options = array();
	var $pvalue = null;
};

?>