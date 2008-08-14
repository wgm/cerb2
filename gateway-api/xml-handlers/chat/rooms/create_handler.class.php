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
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/rooms.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class creates a chat room
 *
 */
class create_handler extends xml_parser
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
    * @return create_handler
    */
   function create_handler(&$xml) {
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
      
      $rooms_obj =& new chat_rooms();
      
      $room_name = $this->xml->get_child_data("room_name", 0);
      if(strlen($room_name) < 1) {
         xml_output::error(0, 'Room Name is required');
      }
      
      $room_name = $this->xml->get_child_data("room_type", 0);
      if($room_type != "visitor" && $room_type != "im" && $room_type != "meeting") {
         xml_output::error(0, 'Room Type is required (visitor, im, or meeting)');
      }
      
      if($rooms_obj->create_room_xml($room_name, $room_type) === FALSE) {
         xml_output::error(0, 'Failed to create room!');
      }
      else {
         xml_output::success();
      }
   }
}