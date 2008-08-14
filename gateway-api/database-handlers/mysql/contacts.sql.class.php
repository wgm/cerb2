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
class contacts_sql
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
    * @return contacts_sql
    */
	function contacts_sql(&$db) {
		$this->db =& $db;
	}

	function get_address_info($params) {
		extract($params);
		$query = "SELECT a.address_id, a.address_address, a.public_user_id, pu.name_first, pu.name_last FROM `address` a LEFT JOIN `public_gui_users` pu ON (a.public_user_id = pu.public_user_id) WHERE a.address_id = '%d'";
		return $this->db->GetRow(sprintf($query, $address_id));
	}
	
	function assign_address($params) {
		extract($params);
		$query = "UPDATE address SET public_user_id = '%d' WHERE address_id = '%d'";
		return $this->db->Execute(sprintf($query, $contact_id, $address_id));
	}
	
	function get_address_list($params) {
		extract($params);
		$query = "SELECT a.address_id, a.address_address, c.name AS company_name, pgu.name_first, pgu.name_last FROM public_gui_users pgu LEFT JOIN address a USING ( public_user_id ) LEFT JOIN company c ON ( pgu.company_id = c.id ) ".
		((isset($filters["keyword"]) && !empty($filters["keyword"])) ? "WHERE (c.name LIKE '%%".addslashes($filters["keyword"])."%%' OR pgu.name_first LIKE '%%".addslashes($filters["keyword"])."%%' OR pgu.name_last LIKE '%%".addslashes($filters["keyword"])."%%' OR a.address_address LIKE '%%".addslashes($filters["keyword"])."%%') " : "").
		"ORDER BY a.address_address";
		return $this->db->GetAll($query);
	}
	
	function get_contact_info($params) {
		extract($params);
		$sql = "SELECT pgu.public_user_id AS contact_id, pgu.*, c.name AS company_name, country.country_name AS mailing_country_name FROM public_gui_users pgu LEFT JOIN company c ON (pgu.company_id = c.id) LEFT JOIN country ON (pgu.mailing_country_id = country.country_id) WHERE public_user_id = '%d'";
		return $this->db->GetRow(sprintf($sql, $contact_id));
	}
	
	function get_contact_address_list($params) {
		extract($params);
		$sql = "SELECT address_id, address_address FROM address where public_user_id = '%d'";
		return $this->db->GetAll(sprintf($sql, $contact_id));
	}
	
	function get_full_contact_list($params) {
		extract($params);
		$query = "SELECT pgu.public_user_id, pgu.name_salutation, pgu.name_first, pgu.name_last, pgu.phone_work, pgu.phone_mobile, pgu.public_access_level, pgu.company_id, c.name AS company_name FROM public_gui_users pgu LEFT JOIN company c ON (pgu.company_id = c.id) ".
		((isset($filters["keyword"]) && !empty($filters["keyword"])) ? "WHERE (c.name LIKE '%%".addslashes($filters["keyword"])."%%' OR pgu.name_first LIKE '%%".addslashes($filters["keyword"])."%%' OR pgu.name_last LIKE '%%".addslashes($filters["keyword"])."%%') " : "").
		"ORDER BY pgu.name_last, pgu.name_first";
		return $this->db->GetAll($query);
	}
	
   function create_contact($params) {
   	extract($params);
   	
   	$sql = "INSERT INTO public_gui_users(created_date) VALUES(UNIX_TIMESTAMP(NOW()));";
   	$this->db->Execute($sql);
   	
   	return $this->db->Insert_ID();
   }

	
   function save_contact($params) {
		extract($params);
		
		$contact_table_array = array();
		
	   foreach($changes as $key=>$value) {
	      switch($key) {
	         case 'contact_sal':
	         	$contact_table_array[] = sprintf("name_salutation = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_firstname':
	         	$contact_table_array[] = sprintf("name_first = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_lastname':
	         	$contact_table_array[] = sprintf("name_last = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_street':
	         	$contact_table_array[] = sprintf("mailing_address = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_city':
	         	$contact_table_array[] = sprintf("mailing_city = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_state':
	         	$contact_table_array[] = sprintf("mailing_state = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_zip':
	         	$contact_table_array[] = sprintf("mailing_zip = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_country':
	         	$contact_table_array[] = sprintf("mailing_country_id = '%d'", $value);
	         	break;
	         case 'contact_company':
	         	$contact_table_array[] = sprintf("company_id = '%d'", $value);
	         	break;
	         case 'contact_phonework':
	         	$contact_table_array[] = sprintf("phone_work = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_phonehome':
	         	$contact_table_array[] = sprintf("phone_home = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_phonemobile':
	         	$contact_table_array[] = sprintf("phone_mobile = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_phonefax':
	         	$contact_table_array[] = sprintf("phone_fax = %s", $this->db->qstr($value));
	         	break;
	         case 'contact_selfhelp':
	         	$contact_table_array[] = sprintf("public_access_level = '%d'", $value);
	         	break;
	         case 'contact_password':
	         	$contact_table_array[] = sprintf("password = %s", $this->db->qstr($value));
	         	break;
	      }
	   } // foreach
		
	   $contact_table_sql = "";
	   if(!empty($contact_id) && is_array($contact_table_array) && !empty($contact_table_array)) {
	   	$contact_table_sql = implode(", ", $contact_table_array);
	   	
	      $sql = sprintf("UPDATE public_gui_users SET %s WHERE public_user_id = %d",
	      		$contact_table_sql,
	      		$contact_id
	     		);
	     	$this->db->Execute($sql);
//		     	print_r($sql);
	   }
	   
   }

}
