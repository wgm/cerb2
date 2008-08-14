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

class general_opportunity
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_opportunity() {
      $this->db =& database_loader::get_instance();
   }

   function get_opportunity_list() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
		$opportunities_xml =& $data->add_child("opportunities", xml_object::create("opportunities"));
		
      $opportunity_list = $this->db->Get("opportunity", "get_opportunity_list", array());
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
   
   function get_opportunity_info($opportunity_id) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
		
      $opportunity_info = $this->db->Get("opportunity", "get_opportunity_info", array("opportunity_id"=>$opportunity_id));
      if(is_array($opportunity_info)) {
     		$opportunity_xml =& $data->add_child("opportunity", xml_object::create("opportunity", NULL, array("id"=>$opportunity_info["opportunity_id"])));
     		$opportunity_xml->add_child("close_date", xml_object::create("close_date", $opportunity_info["close_date"]));
     		$opportunity_xml->add_child("name", xml_object::create("name", $opportunity_info["opportunity_name"]));
     		$opportunity_xml->add_child("stage", xml_object::create("stage", $opportunity_info["stage"]));
     		$opportunity_xml->add_child("probability", xml_object::create("probability", $opportunity_info["probability"]));
     		$opportunity_xml->add_child("amount", xml_object::create("amount", $opportunity_info["amount"]));
     		$opportunity_xml->add_child("amount_currency", xml_object::create("amount_currency", $opportunity_info["amount_currency"]));
     		$opportunity_xml->add_child("agent", xml_object::create("agent", $opportunity_info["owner_name"], array("id"=>$opportunity_info["owner_id"])));
     		$opportunity_xml->add_child("team", xml_object::create("team", NULL, array("id"=>$opportunity_info["team_id"])));
     		$opportunity_xml->add_child("company", xml_object::create("company", $opportunity_info["company_name"], array("id"=>$opportunity_info["company_id"])));
     		return TRUE;
      }
      else {
      	return FALSE;
      }	   	
   }

   function save_opportunity($opp_id, $changes) {
   	if(0 == $opp_id) {
			$opp_id = $this->db->Get("opportunity", "create_opportunity", array($opp_id));
   	}

   	if(0 != $opp_id) {
			$dbSet = $this->db->Get("opportunity", "save_opportunity", array("opp_id"=>$opp_id, "changes"=>$changes));
	
	      $xml =& xml_output::get_instance();
	      $data =& $xml->get_child("data", 0);
	
	      $opp_id_xml = $data->add_child("opportunity", xml_object::create("opportunity", NULL, array("id"=>$opp_id)));
	      
	      return TRUE;
   	}
   	
   	return FALSE;
   }
   
   
}