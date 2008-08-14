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

class general_accounts
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_accounts() {
      $this->db =& database_loader::get_instance();
   }

   function get_accounts_by_filter($filters) {
   	$account_list = $this->db->Get("accounts","get_accounts_list",array("filters"=>$filters));   	
   	
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

		$accounts =& $data->add_child("accounts", xml_object::create("accounts", NULL));

		if(is_array($account_list)) {
			foreach($account_list as $account_item) {
				$account =& $accounts->add_child("account", xml_object::create("account", NULL, array("id"=>$account_item["id"])));
            $account->add_child("company_name", xml_object::create("company_name", stripslashes($account_item["name"])));
            $account->add_child("company_phone", xml_object::create("company_phone", $account_item["company_phone"]));
            $account->add_child("sla", xml_object::create("sla", $account_item["sla_name"], array("id"=>$account_item["sla_id"])));
            $account->add_child("sla_expire_date", xml_object::create("sla_expire_date", $account_item["sla_expire_date"]));
            $account->add_child("num_contacts", xml_object::create("num_contacts", $account_item["num_contacts"]));
			}
		}
		
		return TRUE;   	
   }

   function get_account_by_id($account_id) {
   	$account_info = $this->db->Get("accounts","get_account_by_id",array("account_id"=>$account_id));

   	if(empty($account_info))
      	return TRUE;

   	$xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

		$account =& $data->add_child("account", xml_object::create("account", NULL, array("id"=>$account_info["id"])));
      $account->add_child("company_name", xml_object::create("company_name", stripslashes($account_info["name"])));
      $account->add_child("company_account_number", xml_object::create("company_account_number", $account_info["company_account_number"]));
      $account->add_child("company_mailing_address", xml_object::create("company_mailing_address", $account_info["company_mailing_address"]));
      $account->add_child("company_mailing_city", xml_object::create("company_mailing_city", $account_info["company_mailing_city"]));
      $account->add_child("company_mailing_state", xml_object::create("company_mailing_state", $account_info["company_mailing_state"]));
      $account->add_child("company_mailing_zip", xml_object::create("company_mailing_zip", $account_info["company_mailing_zip"]));
      $account->add_child("company_mailing_country", xml_object::create("company_mailing_country", $account_info["company_mailing_country_name"], array("id"=>$account_info["company_mailing_country_id"])));
      $account->add_child("company_phone", xml_object::create("company_phone", $account_info["company_phone"]));
      $account->add_child("company_fax", xml_object::create("company_fax", $account_info["company_fax"]));
      $account->add_child("company_website", xml_object::create("company_website", $account_info["company_website"]));
      $account->add_child("company_email", xml_object::create("company_email", $account_info["company_email"]));
      $account->add_child("sla", xml_object::create("sla", $account_info["sla_name"], array("id"=>$account_info["sla_id"])));
      $account->add_child("sla_expire_date", xml_object::create("sla_expire_date", $account_info["sla_expire_date"]));
      
      $contact_handler = new cer_PublicUserHandler();
      $contact_handler->loadUsersByCompany($account_id);
      
      $contacts =& $account->add_child("contacts", xml_object::create("contacts", NULL));
      
      if(!empty($contact_handler->users)) {
      	      	
      	foreach($contact_handler->users as $user_item) {
      		$contact =& $contacts->add_child("contact", xml_object::create("contact", NULL, array("id"=>$user_item->public_user_id)));
      		$contact->add_child("contact_name_salutation", xml_object::create("contact_name_salutation", $user_item->account_name_salutation));
      		$contact->add_child("contact_name_first", xml_object::create("contact_name_first", $user_item->account_name_first));
      		$contact->add_child("contact_name_last", xml_object::create("contact_name_last", $user_item->account_name_last));
      		$contact->add_child("contact_phone", xml_object::create("contact_phone", $user_item->account_phone_work));
      		$contact->add_child("contact_mobile", xml_object::create("contact_mobile", $user_item->account_phone_mobile));
      		$contact->add_child("contact_access", xml_object::create("contact_access", $user_item->account_access_level));
      		
      		$addresses =& $contact->add_child("addresses", xml_object::create("addresses", NULL)); 
      		
      		if(!empty($user_item->email_addresses)) {
      			foreach($user_item->email_addresses as $email_id => $email_item) {
      				$address =& $addresses->add_child("address", xml_object::create("address", $email_item, array("id"=>$email_id)));
      			}
      		}
      		
      	}
      }

      // [JAS]: Add opportunities to the account info packet
      $opportunity_list = $this->db->Get("opportunity", "get_opportunity_list_by_account", array("account_id"=>$account_info["id"]));
		$opportunities_xml =& $account->add_child("opportunities", xml_object::create("opportunities"));
      if(is_array($opportunity_list)) {
      	foreach($opportunity_list as $opportunity_row) {
      		$opportunity_xml =& $opportunities_xml->add_child("opportunity", xml_object::create("opportunity", NULL, array("id"=>$opportunity_row["opportunity_id"])));
      		$opportunity_xml->add_child("close_date", xml_object::create("close_date", $opportunity_row["close_date"]));
      		$opportunity_xml->add_child("name", xml_object::create("name", $opportunity_row["opportunity_name"]));
      		$opportunity_xml->add_child("stage", xml_object::create("stage", $opportunity_row["stage"]));
      		$opportunity_xml->add_child("probability", xml_object::create("probability", $opportunity_row["probability"]));
      		$opportunity_xml->add_child("amount", xml_object::create("amount", $opportunity_row["amount"]));
      	}
      }

      return TRUE;
   }
   
   function save_account($account_id, $changes) {
   	if(0 == $account_id) {
			$account_id = $this->db->Get("accounts", "create_account", array($account_id));
   	}

   	if(0 != $account_id) {
			$dbSet = $this->db->Get("accounts", "save_account", array("account_id"=>$account_id, "changes"=>$changes));
	
	      $xml =& xml_output::get_instance();
	      $data =& $xml->get_child("data", 0);
	
	      $account_id_xml = $data->add_child("account", xml_object::create("account", NULL, array("id"=>$account_id)));
	      
	      return TRUE;
   	}
   	
   	return FALSE;
   }
   
}