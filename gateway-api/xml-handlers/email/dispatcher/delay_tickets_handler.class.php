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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/dispatcher.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles accepting a ticket suggestion
 *
 */
class delay_tickets_handler extends xml_parser
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
    * @return delay_tickets_handler
    */
   function delay_tickets_handler(&$xml) {
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

      $obj =& new email_dispatcher();
      
      $tickets = array();
      $tickets_xml =& $this->xml->get_child("tickets", 0);
      if(is_object($tickets_xml)) {
         $ticket_array = $tickets_xml->get_child("ticket");
         if(is_array($ticket_array)) {
            foreach($ticket_array as $ticket_xml) {
               $ticket_id = $ticket_xml->get_attribute("id", FALSE);
               $tickets[$ticket_id]["reason"] = $ticket_xml->get_child_data("reason", 0);
               $tickets[$ticket_id]["mins"] = $ticket_xml->get_child_data("mins", 0);
            }
         }
      }

      if($obj->delay_tickets($tickets, general_users::get_user_id()) === FALSE) {
         xml_output::error(0, 'Error in accepting tickets');
      }
      else {
         xml_output::success();
      }
   }
}