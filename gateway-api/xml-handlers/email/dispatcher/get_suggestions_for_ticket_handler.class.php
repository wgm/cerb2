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
 * This class handles getting the people suggested for a ticket
 *
 */
class get_suggestions_for_ticket_handler extends xml_parser
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
    * @return get_suggestions_for_ticket_handler
    */
   function get_suggestions_for_ticket_handler(&$xml) {
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
      
      $ticket_xml = $this->xml->get_child("ticket", 0);
      if(is_object($ticket_xml)) {
      	$ticket_id = $ticket_xml->get_attribute("id", FALSE);
      }
      
      if(empty($ticket_id) || $ticket_id < 0) {
      	xml_output::error(0, "Ticket ID is required");
      }

      if($obj->get_suggestions_for_ticket($ticket_id) === FALSE) {
         xml_output::error(0, 'Error in getting suggestions for a ticket');
      }
      else {
         xml_output::success();
      }
   }
}