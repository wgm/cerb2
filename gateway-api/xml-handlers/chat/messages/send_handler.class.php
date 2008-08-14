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
 * This class sending a message to a room
 *
 */
class send_handler extends xml_parser
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
    * @return send_handler
    */
   function send_handler(&$xml) {
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
      
      $msg_obj =& new chat_messages();
      
      $room_id = $this->xml->get_child_data("room_id", 0);
      if(is_null($room_id) || $room_id < 1) {
         xml_output::error(0, 'Room ID is required');
      }
      
      $chat_message = $this->xml->get_child_data("message", 0);
      if(strlen($chat_message) < 1) {
         xml_output::error(0, 'Message must contain atleast one character!');
      }
      
      
      if($msg_obj->send_message(general_users::get_user_id(), $room_id, $chat_message) === FALSE) {
         xml_output::error(0, 'Failed to send message to room!');
      }
      else {
         xml_output::success();
      }
   }
}