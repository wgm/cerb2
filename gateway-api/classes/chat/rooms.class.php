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
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/messages.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class chat_rooms
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function chat_rooms() {
      $this->db =& database_loader::get_instance();
   }
   
   function get_room_messages($room_id, $line_id) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $messages =& $data->add_child("messages", xml_object::create("messages"));
      $chat_handler = new chat_messages();
      $new_line_id = $chat_handler->get_room_messages_xml($room_id, $messages, $line_id, TRUE);
      $data->add_child("new_line_id", xml_object::create("new_line_id", $new_line_id));
      return TRUE;
   }
      

   // [JAS]: Agent is joining a chat room
   function agent_join_room($agent_id, $room_id, $join_flags) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $confirmation =& $data->add_child("confirmation", xml_object::create("confirmation"));
      $confirmation->add_child("confirm", xml_object::create("confirm", "Joined chat!"));
      $room_history =& $confirmation->add_child("messages", xml_object::create("messages"));
      $chat_handler = new chat_messages();
      $line_id = $chat_handler->get_room_messages_xml($room_id, $room_history, $this->get_history_line_id($room_id), FALSE);
      $confirmation->add_child("new_line_id", xml_object::create("new_line_id", $line_id));
      if($this->agent_in_room($agent_id, $room_id) !== FALSE) {
         return true;
      }
      if(empty($line_id)) {
         $line_id = $this->get_history_line_id($room_id);
      }
      $this->db->Save("rooms", "agent_join_room", array("agent_id"=>$agent_id, "room_id"=>$room_id, "join_flags"=>$join_flags, "line_id"=>$line_id));
      if(!($join_flags & JOIN_FLAG_SILENT)) {
         $this->write_room_broadcast($room_id, $this->get_agent_name_by_id($agent_id) . " has joined the conversation.");
      }
      return true;
   }

   function get_history_line_id($room_id) {
      return $this->db->Get("rooms", "get_history_line_id", array("room_id"=>$room_id, "history_hrs"=>CHAT_JOIN_HISTORY_HRS));
   }

   // [JAS]: Agent is leaving a chat room
   function agent_leave_room($agent_id, $room_id, $is_timeout=0) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      if(FALSE === ($join_data = $this->agent_in_room($agent_id, $room_id))) {
         $confirmation =& $data->add_child("confirmation", xml_object::create("confirmation"));
         $confirmation->add_child("confirm", xml_object::create("confirm", "Agent not in room! Agent ID: " . $agent_id . " Room ID: " . $room_id));
         return TRUE;
      }
      $this->db->Save("rooms", "agent_leave_room", array("agent_id"=>$agent_id, "room_id"=>$room_id));
      if(!($join_data["join_flags"] & JOIN_FLAG_SILENT)) {
         $action = ($is_timeout) ? " has timed out." : " has left the conversation.";
         $this->write_room_broadcast($room_id, $this->get_agent_name_by_id($agent_id) . $action);
      }
      $confirmation =& $data->add_child("confirmation", xml_object::create("confirmation"));
      $confirmation->add_child("confirm", xml_object::create("confirm", "Left chat!"));
      return true;
   }

   function agent_in_room($agent_id, $room_id) {
      return $this->db->Get("rooms", "agent_in_room", array("agent_id"=>$agent_id,"room_id"=>$room_id));
   }

   function get_agent_name_by_id($agent_id) {
      $user_info = $this->db->Get("user", "get_extended_user_info_by_id", array("user_id"=>$agent_id));
      if(strlen($user_info['chat_display_name']) > 0) {
         return $user_info['chat_display_name'];
      }
      else {
         return $user_info['user_login'];
      }
   }

   function create_room($room_name, $room_type) {
      $room_id = $this->db->Save("rooms", "create_room", array("room_name"=>$room_name, "room_type"=>$room_type));
      $this->db->Save("transcripts", "create_transcript", array("room_name"=>$room_name, "room_id"=>$room_id));
      return $room_id;
   }
   
   function create_room_xml($room_name, $room_type) {
      $room_id = $this->create_room($room_name, $room_type);
      if($room_id > 0) {
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $confirmation =& $data->add_child("confirmation", xml_object::create("confirmation"));
         $confirmation->add_child("confirm", xml_object::create("confirm", "Room Created!"));
         $room =& $data->add_child("room", xml_object::create("room", NULL, array("id"=>$room_id)));
         $room->add_child("room_name", xml_object::create("room_name", $room_name));
         $room->add_child("room_type", xml_object::create("room_type", $room_type));
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function remove_room($room_id) {
      $this->db->Save("rooms", "remove_room", array("room_id"=>$room_id));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $confirmation =& $data->add_child("confirmation", xml_object::create("confirmation"));
      $confirmation->add_child("confirm", xml_object::create("confirm", "Room Removed!"));
      $data->add_child("room_id", xml_object::create("room_id", $room_id));
   }
   
   function expire_stale_visitors() {
      $inactivity_list = $this->db->Get("rooms", "get_inactive_visitors_list", array("timeout"=>NO_AGENT_IN_ROOM_VISITOR_INACTIVITY_TIMEOUT));
      if(is_array($inactivity_list)) {
         foreach($inactivity_list as $inactivity_item) {
            $agent_in_room = $this->db->Get("rooms", "any_agent_in_room", array("room_id"=>$inactivity_item['room_id']));
            if(!$agent_in_room) {
               $this->visitor_leave_room($inactivity_item['visitor_id'], $inactivity_item['room_id']);
            }
         }
      }
   }
   
   function remove_stale_rooms() {
      $this->expire_stale_visitors();
      $room_list = $this->db->Get("rooms", "get_stale_rooms", array());
      if(is_array($room_list)) {
         foreach($room_list as $room_item) {
            $this->db->Save("rooms", "remove_room", array("room_id"=>$room_item['room_id']));
         }
      }
      return TRUE;
   }
   
   function list_room_occupants($room_id) {
      $agent_list = $this->db->Get("rooms", "agents_in_room", array("room_id"=>$room_id));
      $visitor_list = $this->db->Get("rooms", "visitors_in_room", array("room_id"=>$room_id));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $occupants =& $data->add_child("occupants", xml_object::create("occupants"));
      while(NULL !== $agent_info = array_shift($agent_list)) {
         $occupant =& $occupants->add_child("occupant", xml_object::create("occupant"));
         $occupant->add_child("type", xml_object::create("type", "agent"));
         $occupant->add_child("join_flags", xml_object::create("join_flags", $agent_info['join_flags']));
         $occupant->add_child("name", xml_object::create("name", $agent_info['chat_display_name']));
         $occupant->add_child("agent_id", xml_object::create("agent_id", $agent_info['agent_id']));
      }
      while(NULL !== $visitor_info = array_shift($visitor_list)) {
         $occupant =& $occupants->add_child("occupant", xml_object::create("occupant"));
         $occupant->add_child("type", xml_object::create("type", "visitor"));
         $occupant->add_child("name", xml_object::create("name", $visitor_info['visitor_name']));
         $occupant->add_child("visitor_id", xml_object::create("visitor_id", $visitor_info['visitor_id']));
      }
   }

   function write_room_broadcast($room_id, $broadcast_msg) {
      if(empty($room_id) || empty($broadcast_msg)) {
         return false;
      }

      $msg_handler = new chat_messages();

      $msg = new chat_message_container();
      $msg->room_id = $room_id;
      $msg->owner_id = 0;
      $msg->message_code = MESSAGE_CODE_BROADCAST;
      $msg->message_date = time();
      $msg->message_prefix = "Broadcast";
      $msg->message = $broadcast_msg;

      $msg_handler->write_chat_message($msg);
      return TRUE;
   }
   
   function get_rooms_list() {
      $this->remove_stale_rooms();
      $rooms_list = $this->db->Get("rooms", "get_rooms_list", array());
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $rooms =& $data->add_child("rooms", xml_object::create("rooms"));
      while(NULL !== $room_info = array_shift($rooms_list)) {
         $people_count = $this->db->Get("rooms", "people_count", array("room_id"=>$room_info['room_id']));
         $unread_messages = $this->db->Get("rooms", "unread_count", array("room_id"=>$room_info['room_id'], "agent_id"=>general_users::get_user_id()));
         
         $room =& $rooms->add_child("room", xml_object::create("room", NULL, array("id"=>$room_info['room_id'])));
         $room->add_child("room_name", xml_object::create("room_name", $room_info['room_name']));
         $room->add_child("room_type", xml_object::create("room_type", $room_info['room_type']));
         $room->add_child("occupants", xml_object::create("occupants", $room_info['occupants']));
         $room->add_child("new_messages", xml_object::create("new_messages", $unread_messages));
         $room->add_child("created", xml_object::create("created", date(XML_DATE_FORMAT, $room_info['room_created']), array("timestamp"=>$room_info['room_created'])));
      }
   }
   
   function visitor_join_room($visitor_id, $room_id) {
      if(1 != $this->db->Get("rooms", "room_exists", array("room_id"=>$room_id))) {
         return FALSE;
      }
      if(!$this->db->Save("rooms", "visitor_join_room", array("visitor_id"=>$visitor_id, "room_id"=>$room_id))) {
         return FALSE;
      }
      $visitor_info = $this->db->Get("visitor", "get_visitor_info", array("visitor_id"=>$visitor_id));
      $visitor_name = empty($visitor_info['visitor_name']) ? 'Visitor' : $visitor_info['visitor_name'];
      $visitor_question = $visitor_info['visitor_question'];
      $this->write_room_broadcast($room_id, sprintf("%s has joined the conversation.", $visitor_name));
      $this->write_room_broadcast($room_id, sprintf('%s asked "%s"', $visitor_name, $visitor_question));
      return TRUE;
   }
   
   function visitor_leave_room($visitor_id, $room_id, $visitor_name = '') {
      if(1 != $this->db->Get("rooms", "room_exists", array("room_id"=>$room_id))) {
         return FALSE;
      }
      if(!$this->db->Save("rooms", "visitor_leave_room", array("visitor_id"=>$visitor_id, "room_id"=>$room_id))) {
         return FALSE;
      }
      if(empty($visitor_name)) {
         $visitor_info = $this->db->Get("visitor", "get_visitor_info", array("visitor_id"=>$visitor_id));
         $visitor_name = empty($visitor_info['visitor_name']) ? 'Visitor' : $visitor_info['visitor_name'];
      }
      $this->write_room_broadcast($room_id, sprintf("%s has left the conversation.", $visitor_name));
      return TRUE;
   }
}