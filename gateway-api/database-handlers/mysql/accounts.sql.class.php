<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer example
 *
 */
class accounts_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var db
    */
   var $db;
   
   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return accounts_sql
    */
   function accounts_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Gets the accounts list using specified filters
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_accounts_list($params) {
   	extract($params);
   	
		$sql = "SELECT c.id, c.name, c.sla_id, sla.name as sla_name, unix_timestamp(c.sla_expire_date) as sla_expire_date, c.company_phone, COUNT(pu.public_user_id) as num_contacts ".
      	"FROM `company` c ".
      	"LEFT JOIN `sla` ON (c.sla_id = sla.id) ".
      	"LEFT JOIN `public_gui_users` pu ON (pu.company_id = c.id) ".
      	((isset($filters["keyword"]) && !empty($filters["keyword"])) ? "WHERE c.name LIKE '%%".addslashes($filters["keyword"])."%%' " : "").
      	"GROUP BY c.id ".
      	"ORDER BY c.name ASC ";
      
      return $this->db->GetAll(sprintf($sql));
   }

   function get_account_by_id($params) {
   	extract($params);
      $sql = "SELECT c.id, c.name, c.company_account_number, c.company_mailing_address, c.company_mailing_city, c.company_mailing_state, c.company_mailing_zip, c.company_mailing_country_id, cou.country_name as company_mailing_country_name, c.sla_id, sla.name as sla_name, unix_timestamp(c.sla_expire_date) as sla_expire_date, c.company_phone, c.company_fax, c.company_website, c.company_email FROM `company` c LEFT JOIN `sla` ON (c.sla_id = sla.id) LEFT JOIN `country` cou ON (c.company_mailing_country_id = cou.country_id) WHERE c.id = '%d' ORDER BY c.name ASC";
      return $this->db->GetRow(sprintf($sql, $account_id));
   }

   function create_account($params) {
   	extract($params);
   	
   	$sql = "INSERT INTO company(created_date) VALUES(UNIX_TIMESTAMP(NOW()));";
   	$this->db->Execute($sql);
   	
   	return $this->db->Insert_ID();
   }
   
   function save_account($params) {
   	extract($params);
   	
   	$company_table_array = array();
   	
      foreach($changes as $key=>$value) {
         switch($key) {
            case 'account_name':
            	$company_table_array[] = sprintf("name = %s", $this->db->qstr($value));
            	break;
            case 'account_no':
            	$company_table_array[] = sprintf("company_account_number = %s", $this->db->qstr($value));
            	break;
            case 'account_street':
            	$company_table_array[] = sprintf("company_mailing_address = %s", $this->db->qstr($value));
            	break;
            case 'account_city':
            	$company_table_array[] = sprintf("company_mailing_city = %s", $this->db->qstr($value));
            	break;
            case 'account_state':
            	$company_table_array[] = sprintf("company_mailing_state = %s", $this->db->qstr($value));
            	break;
            case 'account_zip':
            	$company_table_array[] = sprintf("company_mailing_zip = %s", $this->db->qstr($value));
            	break;
            case 'account_country':
            	$company_table_array[] = sprintf("company_mailing_country_id = '%d'", $value);
            	break;
            case 'account_industry':
            	// [JAS]: [TODO] We need a linking table for acct<->industry
            	break;
            case 'account_phone':
            	$company_table_array[] = sprintf("company_phone = %s", $this->db->qstr($value));
            	break;
            case 'account_fax':
            	$company_table_array[] = sprintf("company_fax = %s", $this->db->qstr($value));
            	break;
            case 'account_email':
            	$company_table_array[] = sprintf("company_email = %s", $this->db->qstr($value));
            	break;
            case 'account_website':
            	$company_table_array[] = sprintf("company_website = %s", $this->db->qstr($value));
            	break;
            case 'account_slaplan':
            	$company_table_array[] = sprintf("sla_id = '%d'", $value);
            	break;
            case 'account_slaexpire':
            	$company_table_array[] = sprintf("sla_expire_date = '%s'", gmdate("Y-m-d H:i:s", $value));
            	break;
            case 'account_acctmgr':
            	// [JAS]: [TODO] We need a field for account manager
            	break;
         }
      } // foreach
   	
      $company_table_sql = "";
      if(!empty($account_id) && is_array($company_table_array) && !empty($company_table_array)) {
      	$company_table_sql = implode(", ", $company_table_array);
      	
	      $sql = sprintf("UPDATE company SET %s WHERE id = %d",
	      		$company_table_sql,
	      		$account_id
	     		);
	     	$this->db->Execute($sql);
//	     	print_r($sql);
      }
      
      
   }
   
}