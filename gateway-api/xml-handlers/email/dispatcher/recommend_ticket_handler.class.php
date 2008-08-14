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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/dispatcher.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles assigning a ticket to someone else
 *
 */
class recommend_ticket_handler extends xml_parser
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
    * @return recommend_ticket_handler
    */
   function recommend_ticket_handler(&$xml) {
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

      $ticket_xml = $this->xml->get_child("ticket", 0);
      if(is_object($ticket_xml)) {
      	$ticket_id = $ticket_xml->get_attribute("id", FALSE);
      }
      if(empty($ticket_id) || $ticket_id < 1) {
         xml_output::error(0, "No Ticket ID provided!");
      }

      $ids = array();
      
      $members_xml =& $this->xml->get_child("members", 0);     
      if(is_object($members_xml)) {
      	$member_array = $members_xml->get_child("member");
      	if(is_array($member_array)) {
      		foreach($member_array as $member_xml) {
      			$ids[] = $member_xml->get_attribute("id", FALSE);
      		}
      	}
      }

//      if(count($ids) < 1) {
//         xml_output::error(0, 'You must suggest at least one member for the ticket');
//      }

      $obj = new email_dispatcher();

      if($obj->recommend_ticket($ticket_id, $ids) === FALSE) {
         xml_output::error(0, 'Failed to assign ticket to user');
      }
      else {
         xml_output::success();
      }
   }
}