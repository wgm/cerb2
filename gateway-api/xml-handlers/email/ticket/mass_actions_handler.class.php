<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles applying changes to a batch of tickets at once
 *
 */
class mass_actions_handler extends xml_parser
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
    * @return mass_actions_handler
    */
   function mass_actions_handler(&$xml) {
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

      $obj =& new email_tickets();
      
      $tickets = array();
      $tickets_xml =& $this->xml->get_child("tickets", 0);
      if(is_object($tickets_xml)) {
         $ticket_array = $tickets_xml->get_child("ticket");
         if(is_array($ticket_array)) {
            foreach($ticket_array as $ticket_xml) {
               $tickets[] = $ticket_xml->get_attribute("id", FALSE);
            }
         }
      }
      
      $actions = array();
      $actions_xml =& $this->xml->get_child("actions", 0);
      if(is_object($actions_xml)) {
         $children_array = $actions_xml->get_children();
         if(is_array($children_array)) {
            foreach($children_array as $instance_array) {
            	foreach($instance_array as $child_xml) {
            		$tagname = $child_xml->get_token();
            		$data = $child_xml->get_data_trim();
            		if(strlen($data) > 0) {
            			$actions[$tagname] = $data;
            		}
            	}
            }
         }
      }

      if($obj->mass_actions($tickets, $actions) === FALSE) {
         xml_output::error(0, 'Error in saving changes to tickets');
      }
      else {
         xml_output::success();
      }
   }
}