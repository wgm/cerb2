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
 * This class handles getting a contact's info by ID
 *
 */
class get_contact_info_handler extends xml_parser
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
    * @return get_contact_info_handler
    */
   function get_contact_info_handler(&$xml) {
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

      $contact_xml = $this->xml->get_child("contact", 0);
      if(is_object($contact_xml)) {
      	$contact_id = $contact_xml->get_attribute("id", FALSE);
      }
      if(!is_numeric($contact_id) || $contact_id < 1) {
      	xml_output::error(0, "Missing or invalid contact ID provided");
      }
      
      $obj = new general_contacts();   
      
      if($obj->get_contact_info($contact_id) === FALSE) {
         xml_output::error(0, 'Failed to get contact info for contact');
      }
      else {
         xml_output::success();
      }
   }        
}