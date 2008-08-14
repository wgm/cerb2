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

class general_industries
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_industries() {
      $this->db =& database_loader::get_instance();
   }

   function get_industry_list() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

		$sectors_xml =& $data->add_child("sectors", xml_object::create("sectors", NULL));

		$sectors = array();
		$sector_list = $this->get_sector_list_from_db();
		if(is_array($sector_list)) {
			foreach($sector_list as $sector) {
				$sectors[$sector] = array();
			}
		}

   	$industry_list = $this->db->Get("industries","get_industry_list");   	
   	if(is_array($industry_list)) {
			foreach($industry_list as $idx => $industry_item) {
				$sectors[$industry_item["industry_sector"]][] = &$industry_list[$idx];
			}
		}
		
		if(!empty($sectors)) {
			foreach($sectors as $sector => $industries) {
				$sector_xml =& $sectors_xml->add_child("sector", xml_object::create("sector", null));
				$sector_xml->add_child("name", xml_object::create("name", $sector));
				$industries_xml =& $sector_xml->add_child("industries", xml_object::create("industries", null));
				
				if(is_array($industries))
				foreach($industries as $industry_item) {
					$industry_xml =& $industries_xml->add_child("industry", xml_object::create("industry", NULL, array("id"=>$industry_item["industry_id"])));
					$industry_xml->add_child("name", xml_object::create("name", stripslashes($industry_item["industry_name"])));
				}
			}
		}
		

		return TRUE;   	
   }
   
   function get_sector_list_from_db() {
   	$fld_industry_sector = $this->db->Get("industries","get_sector_enum");
   	
   	if(is_array($fld_industry_sector))
  			$sector_list = $fld_industry_sector["Type"];

  			if(empty($sector_list))
  				return array();
  			
			preg_match("/enum\(\'(.*?)\'\)/i",$sector_list,$matches);
	
			if(empty($matches[1]))
				return array();
			
			$sectors = explode("','",$matches[1]);
			
			if(empty($sectors))
				return array();
				
			return $sectors;
   	}
}