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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/tickets.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class unassigns a ticket from a user and marks it for reassignment at a later time/event
 *
 */
class assign_for_later_handler extends xml_parser
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
    * @return assign_for_later_handler
    */
   function assign_for_later_handler(&$xml) {
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
      
      $ticket_id = $this->xml->get_child_data("ticket_id", 0);
      if(empty($ticket_id) || $ticket_id < 1) {
         xml_output::error(0, "No Ticket ID provided!");
      }   
      
      $type = $this->xml->get_child_data("type", 0);
      if(empty($type) || $type < 1) {
         xml_output::error(0, "No type provided!");
      }
      
      $reason = $this->xml->get_child_data("reason", 0);
      
      $date = $this->xml->get_child_data("date", 0);
       
      $obj = new email_tickets();
      
      if($obj->assign_for_later($ticket_id, $type, $reason, $date) === FALSE) {
         xml_output::error(0, 'Failed to schedule ticket for reassignment');
      }
      else {
         xml_output::success();
      }
   }
}