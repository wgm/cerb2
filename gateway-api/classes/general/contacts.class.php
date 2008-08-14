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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicUser.class.php");

class general_contacts
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_contacts() {
      $this->db =& database_loader::get_instance();
   }

   function get_contacts_by_filter($filters) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

		$contacts =& $data->add_child("contacts", xml_object::create("contacts", NULL));

      $contact_list = $this->db->Get("contacts", "get_full_contact_list", array("filters"=>$filters));
		
      if(is_array($contact_list)) {
	   	foreach($contact_list as $contact_item) {
				$contact =& $contacts->add_child("contact", xml_object::create("contact", NULL, array("id"=>$contact_item["public_user_id"])));
      		$contact->add_child("salutation", xml_object::create("salutation", $contact_item["name_salutation"]));
      		$contact->add_child("first", xml_object::create("first", $contact_item["name_first"]));
      		$contact->add_child("last", xml_object::create("last", $contact_item["name_last"]));
      		$contact->add_child("phone", xml_object::create("phone", $contact_item["phone_work"]));
      		$contact->add_child("mobile", xml_object::create("mobile", $contact_item["phone_mobile"]));
      		$contact->add_child("access", xml_object::create("access", $contact_item["access_level"]));
      		$contact->add_child("company", xml_object::create("company", $contact_item["company_name"], array("id"=>$contact_item["company_id"])));
			}
		}
		
		return TRUE;   	
   }
   
   function get_address_list($filters) {
   	$xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
		
		$address_list = $this->db->Get("contacts", "get_address_list", array("filters"=>$filters));
		
		$addresses =& $data->add_child("addresses", xml_object::create("addresses", NULL, array("count"=>count($address_list))));
		
		if(is_array($address_list)) {
			foreach($address_list as $address_row) {
				$address =& $addresses->add_child("address", xml_object::create("address", NULL, array("id"=>$address_row["address_id"])));
				$address->add_child("contact_name_first", xml_object::create("contact_name_first", $address_row["name_first"]));
      		$address->add_child("contact_name_last", xml_object::create("contact_name_last", $address_row["name_last"]));
      		$address->add_child("company_name", xml_object::create("company_name", $address_row["company_name"]));
      		$address->add_child("email", xml_object::create("email", $address_row["address_address"]));
			}
		}
		
		return TRUE;
   }
   
//   function get_accounts_by_keyword($filter_keyword) {
//   	$account_list = $this->db->Get("accounts","get_accounts_by_keyword", array("filter_keyword"=>$keyword));   	
//   	
//      $xml =& xml_output::get_instance();
//      $data =& $xml->get_child("data", 0);
//
//		$accounts =& $data->add_child("accounts", xml_object::create("accounts", NULL));
//
//		if(is_array($account_list)) {
//			foreach($account_list as $account_item) {
//				$account =& $accounts->add_child("account", xml_object::create("account", NULL, array("id"=>$account_item["id"])));
//            $account->add_child("company_name", xml_object::create("company_name", stripslashes($account_item["name"])));
//            $account->add_child("company_phone", xml_object::create("company_phone", $account_item["company_phone"]));
//            $account->add_child("sla", xml_object::create("sla", $account_item["sla_name"], array("id"=>$account_item["sla_id"])));
//            $account->add_child("sla_expire_date", xml_object::create("sla_expire_date", $account_item["sla_expire_date"]));
//            $account->add_child("num_contacts", xml_object::create("num_contacts", $account_item["num_contacts"]));
//			}
//		}
//		
//		return TRUE;   	
//   }
   
//   function get_account_by_id($account_id) {
//   	$account_info = $this->db->Get("accounts","get_account_by_id",array("account_id"=>$account_id));
////   	$contact_list = $this->db->Get("accounts","get_contacts_by_account",array("account_id"=>$account_id));
//
//   	if(empty($account_info))
//      	return TRUE;
//
//   	$xml =& xml_output::get_instance();
//      $data =& $xml->get_child("data", 0);
//
//		$account =& $data->add_child("account", xml_object::create("account", NULL, array("id"=>$account_info["id"])));
//      $account->add_child("company_name", xml_object::create("company_name", stripslashes($account_info["name"])));
//      $account->add_child("company_account_number", xml_object::create("company_account_number", $account_info["company_account_number"]));
//      $account->add_child("company_mailing_address", xml_object::create("company_mailing_address", $account_info["company_mailing_address"]));
//      $account->add_child("company_mailing_city", xml_object::create("company_mailing_city", $account_info["company_mailing_city"]));
//      $account->add_child("company_mailing_state", xml_object::create("company_mailing_state", $account_info["company_mailing_state"]));
//      $account->add_child("company_mailing_zip", xml_object::create("company_mailing_zip", $account_info["company_mailing_zip"]));
//      $account->add_child("company_mailing_country", xml_object::create("company_mailing_country", $account_info["company_mailing_country_name"], array("id"=>$account_info["company_mailing_country_id"])));
//      $account->add_child("company_phone", xml_object::create("company_phone", $account_info["company_phone"]));
//      $account->add_child("company_fax", xml_object::create("company_fax", $account_info["company_fax"]));
//      $account->add_child("company_website", xml_object::create("company_website", $account_info["company_website"]));
//      $account->add_child("company_email", xml_object::create("company_email", $account_info["company_email"]));
//      $account->add_child("sla", xml_object::create("sla", $account_info["sla_name"], array("id"=>$account_info["sla_id"])));
//      $account->add_child("sla_expire_date", xml_object::create("sla_expire_date", $account_info["sla_expire_date"]));
//      
//      $contact_handler = new cer_PublicUserHandler();
//      $contact_handler->loadUsersByCompany($account_id);
//      
//      $contacts =& $account->add_child("contacts", xml_object::create("contacts", NULL));
//      
//      if(!empty($contact_handler->users)) {
//      	      	
//      	foreach($contact_handler->users as $user_item) {
//      		$contact =& $contacts->add_child("contact", xml_object::create("contact", NULL, array("id"=>$user_item->public_user_id)));
//      		$contact->add_child("contact_name_salutation", xml_object::create("contact_name_salutation", $user_item->account_name_salutation));
//      		$contact->add_child("contact_name_first", xml_object::create("contact_name_first", $user_item->account_name_first));
//      		$contact->add_child("contact_name_last", xml_object::create("contact_name_last", $user_item->account_name_last));
//      		$contact->add_child("contact_phone", xml_object::create("contact_phone", $user_item->account_phone_work));
//      		$contact->add_child("contact_mobile", xml_object::create("contact_mobile", $user_item->account_phone_mobile));
//      		$contact->add_child("contact_access", xml_object::create("contact_access", $user_item->account_access_level));
//      		
//      		$addresses =& $contact->add_child("addresses", xml_object::create("addresses", NULL)); 
//      		
//      		if(!empty($user_item->email_addresses)) {
//      			foreach($user_item->email_addresses as $email_id => $email_item) {
//      				$address =& $addresses->add_child("address", xml_object::create("address", $email_item, array("id"=>$email_id)));
//      			}
//      		}
//      	}
//      }
//      
//      return TRUE;
//   }

	function get_contact_info($contact_id) {
		$contact_info = $this->db->Get("contacts", "get_contact_info", array("contact_id"=>$contact_id));
   	if(!$contact_info) {
   		return FALSE;
   	}
   	$xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $contact =& $data->add_child("contact", xml_object::create("contact", NULL, array("id"=>$contact_info["contact_id"])));
      
      $contact->add_child("name_salutation", xml_object::create("name_salutation", $contact_info["name_salutation"]));
      $contact->add_child("name_first", xml_object::create("name_first", $contact_info["name_first"]));
      $contact->add_child("name_last", xml_object::create("name_last", $contact_info["name_last"]));
      $contact->add_child("mailing_address", xml_object::create("mailing_address", $contact_info["mailing_address"]));
      $contact->add_child("mailing_city", xml_object::create("mailing_city", $contact_info["mailing_city"]));
      $contact->add_child("mailing_state", xml_object::create("mailing_state", $contact_info["mailing_state"]));
      $contact->add_child("mailing_zip", xml_object::create("mailing_zip", $contact_info["mailing_zip"]));
      $contact->add_child("mailing_country", xml_object::create("mailing_country", $contact_info["mailing_country_name"], array("id"=>$contact_info["mailing_country_id"])));
      $contact->add_child("phone_work", xml_object::create("phone_work", $contact_info["phone_work"]));
      $contact->add_child("phone_home", xml_object::create("phone_home", $contact_info["phone_home"]));
      $contact->add_child("phone_mobile", xml_object::create("phone_mobile", $contact_info["phone_mobile"]));
      $contact->add_child("phone_fax", xml_object::create("phone_fax", $contact_info["phone_fax"]));
      $contact->add_child("password", xml_object::create("password", $contact_info["password"]));
      $contact->add_child("public_access_level", xml_object::create("public_access_level", $contact_info["public_access_level"]));
      $contact->add_child("company", xml_object::create("company", $contact_info["company_name"], array("id"=>$contact_info["company_id"])));       
      
      $addresses_list = $this->db->Get("contacts", "get_contact_address_list", array("contact_id"=>$contact_id));
      $addresses_xml =& $contact->add_child("addresses", xml_object::create("addresses"));
      if(is_array($addresses_list)) {
      	foreach($addresses_list as $address_row) {
      		$addresses_xml->add_child("address", xml_object::create("address", $address_row["address_address"], array("id"=>$address_row["address_id"])));
      	}
      }
      
      // [JAS]: Add opportunities to the contact info packet
      // [JAS]: [TODO] Need to add contact id to the opportunity table.
//      $opportunity_list = $this->db->Get("opportunity", "get_opportunity_list_by_contact", array("contact_id"=>$contact_info["contact_id"]));
//		$opportunities_xml =& $contact->add_child("opportunities", xml_object::create("opportunities"));
//      if(is_array($opportunity_list)) {
//      	foreach($opportunity_list as $opportunity_row) {
//      		$opportunity_xml =& $opportunities_xml->add_child("opportunity", xml_object::create("opportunity", NULL, array("id"=>$opportunity_row["opportunity_id"])));
//      		$opportunity_xml->add_child("close_date", xml_object::create("close_date", $opportunity_row["close_date"]));
//      		$opportunity_xml->add_child("name", xml_object::create("name", $opportunity_row["opportunity_name"]));
//      		$opportunity_xml->add_child("stage", xml_object::create("stage", $opportunity_row["stage"]));
//      		$opportunity_xml->add_child("amount", xml_object::create("amount", $opportunity_row["amount"]));
//      	}
//      }

      return TRUE;
	}
	
	function assign_address($contact_id, $address) {
		if(0 == $contact_id || empty($address))
			return FALSE;

		$address = trim($address);
			
		$address_id = $this->db->Get("addresses", "get_address_id", array("address"=>$address));
			
		$addy_info = $this->db->Get("contacts", "get_address_info", array("contact_id"=>$contact_id,"address_id"=>$address_id));

      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
		
      $assigned_code = "";
      $assigned_msg = "";
      $assigned_contact_id = $addy_info['public_user_id'];
      
		// this address isn't assigned, all clear
		if($assigned_contact_id == 0 
			&& !empty($addy_info['address_address'])
			) 
		{
			$this->db->Get("contacts", "assign_address", array("contact_id"=>$contact_id,"address_id"=>$address_id));
			$assigned_code = "success";
			$assigned_msg = sprintf("Success! %s assigned to contact.", $address);
			$assigned_contact_id = $contact_id;
		}
		else { // this address is already assigned
			$contact_name = sprintf("%s %s",
					(!empty($addy_info['name_first']) ? $addy_info['name_first'] : ""),
					(!empty($addy_info['name_last']) ? $addy_info['name_last'] : "")
				);
			$assigned_code = "error";
			$assigned_msg = sprintf("Error! %s is assigned to '%s'.", $address, trim($contact_name));
		}

      $assigned_xml =& $data->add_child("assigned", xml_object::create("assigned", $assigned_msg, array("code"=>$assigned_code,"contact_id"=>$assigned_contact_id)));
		
		return TRUE;
	}
	
   function save_contact($contact_id, $changes) {
   	if(0 == $contact_id) {
			$contact_id = $this->db->Get("contacts", "create_contact", array("contact_id"=>$contact_id));
   	}

   	if(0 != $contact_id) {
			$dbSet = $this->db->Get("contacts", "save_contact", array("contact_id"=>$contact_id, "changes"=>$changes));
	
	      $xml =& xml_output::get_instance();
	      $data =& $xml->get_child("data", 0);
	
	      $contact_id_xml = $data->add_child("contact", xml_object::create("contact", NULL, array("id"=>$contact_id)));
	      
	      return TRUE;
   	}
   	
   	return FALSE;
   }

}
