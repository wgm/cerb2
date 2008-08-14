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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/records.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * 
 *
 */
class save_record_instance_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;
   
   /**
    * Class constructor
    *
    * @param object $xml
    * @return get_record_instance_handler
    */
   function save_record_instance_handler(&$xml) {
      $this->xml =& $xml;
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $users_obj =& new general_users();
      if($users_obj->check_login() === FALSE) {
         xml_output::error(0, 'Not logged in. Please login before proceeding!');
      }
      
      $record_id = $this->xml->get_child_data("record_id", 0);
      $instance_id = $this->xml->get_child_data("instance_id", 0);
      
      $changes_xml =& $this->xml->get_child("changes", 0);
		if(is_object($changes_xml)) {
			$changes_xml_children =& $changes_xml->get_children();
		}
		$changes = array();

		if(is_array($changes_xml_children)) {
			foreach($changes_xml_children as $change_xml_instance) {
				foreach($change_xml_instance as $change_xml) {
					$token = strtolower($change_xml->get_token());
					$changes[$token] = $change_xml->get_data_trim();
				}
			}
		}
      
      $obj = new general_records();
      
      if($obj->save_record_instance($record_id,$instance_id,$changes) === FALSE) {
         xml_output::error(0, 'Failed to get record instance');
      }
      else {
         xml_output::success();
      }
   }        
}