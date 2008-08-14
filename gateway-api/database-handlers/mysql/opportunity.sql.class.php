<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "/gateway-api/classes/general/users.class.php");

/**
 * Database abstraction layer for opportunity data
 *
 */
class opportunity_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;

   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return opportunity_sql
    */
   function opportunity_sql(&$db) {
      $this->db =& $db;
      $this->database_loader =& database_loader::get_instance();
   }

   /**
    * 
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_opportunity_list($params) {
      extract($params);
      $query = "SELECT opportunity_id, opportunity_name, stage, amount, probability, close_date FROM opportunity";
      return $this->db->GetAll($query);
   }
   
//   function get_opportunity_list_by_contact($params) {
//      extract($params);
//      $query = "SELECT opportunity_id, opportunity_name, stage, amount, probability, close_date FROM opportunity WHERE contact_id = '%d'";
//      return $this->db->GetAll(sprintf($query, $contact_id));
//   }

   function get_opportunity_list_by_account($params) {
      extract($params);
      $query = "SELECT opportunity_id, opportunity_name, stage, amount, probability, close_date FROM opportunity WHERE company_id = '%d'";
      return $this->db->GetAll(sprintf($query, $account_id));
   }

   /**
    * 
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_opportunity_info($params) {
      extract($params);
      $sql = "SELECT o.*, u.user_name as owner_name, c.name AS company_name FROM opportunity o LEFT JOIN company c ON (o.company_id = c.id) LEFT JOIN user u ON (o.owner_id = u.user_id) WHERE o.opportunity_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $opportunity_id));
   }
   
   function create_opportunity($params) {
   	extract($params);
   	
   	$sql = "INSERT INTO opportunity(created_date) VALUES(UNIX_TIMESTAMP(NOW()));";
   	$this->db->Execute($sql);
   	
   	return $this->db->Insert_ID();
   }
   
   function save_opportunity($params) {
		extract($params);
		
		$opp_table_array = array();
		
	   foreach($changes as $key=>$value) {
	      switch($key) {
	         case 'opp_name':
	         	$opp_table_array[] = sprintf("opportunity_name = %s", $this->db->qstr($value));
	         	break;
	         case 'opp_stage':
	         	$opp_table_array[] = sprintf("stage = %s", $this->db->qstr($value));
	         	break;
	         case 'opp_company':
	         	$opp_table_array[] = sprintf("company_id = '%d'", $value);
	         	break;
	         case 'opp_owner':
	         	$opp_table_array[] = sprintf("owner_id = '%d'", $value);
	         	break;
	         case 'opp_amount':
	         	$opp_table_array[] = sprintf("amount = '%0.2f'", $value);
	         	break;
	         case 'opp_probability':
	         	$opp_table_array[] = sprintf("probability = '%d'", $value);
	         	break;
	         case 'opp_closedate':
	         	$opp_table_array[] = sprintf("close_date = '%s'", $value);
	         	break;
	         case 'opp_source':
	         	$opp_table_array[] = sprintf("source = %s", $this->db->qstr($value));
	         	break;
	      }
	   } // foreach
		
	   $opp_table_sql = "";
	   if(!empty($opp_id) && is_array($opp_table_array) && !empty($opp_table_array)) {
	   	$opp_table_sql = implode(", ", $opp_table_array);
	   	
	      $sql = sprintf("UPDATE opportunity SET %s WHERE opportunity_id = %d",
	      		$opp_table_sql,
	      		$opp_id
	     		);
	     	$this->db->Execute($sql);
//		     	print_r($sql);
	   }
	   
   }

}