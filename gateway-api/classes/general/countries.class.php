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

class general_countries
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_countries() {
      $this->db =& database_loader::get_instance();
   }

   function get_country_list() {
   	$country_list = $this->db->Get("countries","get_countries_list");   	
   	
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

		$countries =& $data->add_child("countries", xml_object::create("countries", NULL));

		if(is_array($country_list)) {
			foreach($country_list as $country_item) {
				$country =& $countries->add_child("country", xml_object::create("country", NULL, array("id"=>$country_item["country_id"])));
            $country->add_child("name", xml_object::create("name", stripslashes($country_item["country_name"])));
			}
		}
		
		return TRUE;   	
   }
   
}