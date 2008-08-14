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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/contacts.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting a list of accounts by filter
 *
 */
class get_contact_list_handler extends xml_parser
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
    * @return get_contact_list_handler
    */
   function get_contact_list_handler(&$xml) {
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

      // [JAS]: Grab the passed filters from XML into an array
      $filters_xml =& $this->xml->get_child("filters", 0);
      if(is_object($filters_xml)) {
         $filters_xml_children =& $filters_xml->get_children();
      }
      $filters = array();

      if(is_array($filters_xml_children)) {
         foreach($filters_xml_children as $filters_xml_instance) {
            foreach($filters_xml_instance as $filter_xml) {
               $filters[$filter_xml->get_token()] = $filter_xml->get_data();
            }
         }
      }
      
      $obj = new general_contacts();   
      
      if($obj->get_contacts_by_filter($filters) === FALSE) {
         xml_output::error(0, 'Failed to get contact listing by filters');
      }
      else {
         xml_output::success();
      }
   }        
}