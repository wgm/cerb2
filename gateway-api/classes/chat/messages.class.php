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
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/rooms.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class chat_messages
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function chat_messages() {
      $this->db =& database_loader::get_instance();
   }

   function write_chat_message($msg) {
      if(!is_a($msg, "chat_message_container")) {
         return FALSE;
      }

      $msg_id = $this->db->Save("messages", "write_message", array("room_id"=>$msg->room_id, "owner_id"=>$msg->owner_id,
                  "message_code"=>$msg->message_code, "message_date"=>$msg->message_date, "message_prefix"=>$msg->message_prefix));
      if($msg_id === FALSE) {
         return FALSE;
      }
      $chunks = cer_String::strSplit($msg->message, 128);
      foreach($chunks as $msg_chunk) {
         $this->db->Save("messages", "write_message_chunks", array("message_id"=>$msg_id, "message_chunk"=>$msg_chunk));
      }
      return TRUE;
   }
   
   function send_message($agent_id, $room_id, $message) {
      $room_handler = new chat_rooms();
      
      // [JAS]: Make sure the agent is in the room to speak.
		if(FALSE !== ($join_data = $room_handler->agent_in_room($agent_id, $room_id))) {
				$msg = new chat_message_container();
					$msg->room_id = $room_id;
					$msg->owner_id = $agent_id;
					$msg->message_code = MESSAGE_CODE_AGENT;
					$msg->message_date = time();
					$msg->message_prefix = $room_handler->get_agent_name_by_id($agent_id);
					$msg->message = $message;
				return $this->write_chat_message($msg);
		}      
		else {
		   xml_output::error(0, "Agent must be in room to speak!");
		}
   }

   function _build_agent_messages($agent_id, $max) {
      $msg_ids = array();
      $line_ids = array();

      $this->_flush_messages();

      $messages = $this->db->Get("messages", "get_agent_messages", array("agent_id"=>$agent_id, "max"=>$max));

      while(NULL !== $message = array_shift($messages)) {
         $new_msg = new chat_message_container();
            $new_msg->message_id = $message['message_id'];
            $new_msg->message_prefix = $message['message_prefix'];
            $new_msg->room_id = $message['room_id'];
            $new_msg->owner_id = $message['owner_id'];
            $new_msg->message_code = $message['message_code'];
         $this->messages[$message['message_id']] = $new_msg;
         $line_id[$new_msg->room_id] = $new_msg->message_id;
         $msg_ids[] = $new_msg->message_id;
      }

      if(!empty($line_ids)) {
         foreach($line_ids as $room_id => $line_id) {
            $this->db->Save("rooms", "update_agent_room_line_id", array("line_id"=>$line_id, "agent_id"=>$agent_id, "room_id"=>$room_id));
         }
      }

      $this->_build_message_parts($msg_ids);

      return true;
   }

//   function get_agent_messages($agent_id, $max) {
//      $this->_build_agent_messages($agent_id, $max);
//      $line_id = 0;
//      $xml =& xml_output::get_instance();
//      $data =& $xml->get_child("data", 0);
//      $this->xml_container =& $data->add_child("cerberuschat", xml_object::create("cerberuschat"));
//      $this->generate_messages_xml($cerberuschat);
//      return TRUE;
//   }

   function _build_message_parts($msg_ids) {
      if(!empty($msg_ids)) {
         $messages = $this->db->Get("messages", "get_message_parts", array("message_ids"=>$msg_ids));
         while(NULL !== $message_part = array_shift($messages)) {
            $part = $message_part['message_part'];
            if(strlen($part) < 128) $part .= ' '; // fix space issue
            $this->messages[$message_part['message_id']]->message .= $part;
         }
      }
   }

   function _build_room_messages($room_id, $line_id=0, $hide_self = TRUE) {
      $msg_ids = array();
      $this->_flush_messages();

      $messages = $this->db->Get("messages", "get_room_messages", array("room_id"=>$room_id,"line_id"=>$line_id));

      while(NULL !== $message = array_shift($messages)) {
         if($hide_self && $message['message_code'] == MESSAGE_CODE_AGENT && $message['owner_id'] == general_users::get_user_id()) {
            // we don't want our own messages, so skip them
            continue;
         }
         $new_msg = new chat_message_container();
            $new_msg->message_id = $message['message_id'];
            $new_msg->message_prefix = $message['message_prefix'];
            $new_msg->room_id = $message['room_id'];
            $new_msg->message_code = $message['message_code'];
            $new_msg->owner_id = $message['owner_id'];
         $this->messages[$message['message_id']] = $new_msg;
//         $line_id[$new_msg->room_id] = $new_msg->message_id;
         $msg_ids[] = $new_msg->message_id;
      }
      $this->_build_message_parts($msg_ids);
   }

   function get_room_messages_xml($room_id, &$container,$line_id, $hide_self = TRUE) {
      $this->xml_container =& $container;
      $this->_build_room_messages($room_id,$line_id, $hide_self);
      return $this->generate_messages_xml($line_id);
   }

   function generate_messages_xml($line_id) {
      if(is_array($this->messages) && !empty($this->messages)) {
         foreach($this->messages as $msg) {
            $line =& $this->xml_container->add_child("line", xml_object::create("line", $msg->message, array("messageId"=>$msg->message_id,
                                                               "messageCode"=>$msg->message_code, "roomId"=>$msg->room_id)));
            $line->add_child("prefix", xml_object::create("prefix", $msg->message_prefix));
            if($line_id < $msg->message_id) $line_id = $msg->message_id;
         }
      }
      return $line_id;
   }

   function _flush_messages() {
      $this->messages = array();
      return true;
   }
   
   function get_visitor_room_messages($GUID, $room_id) {
      $msg_ids = array();
      $last_update = 0;
      $this->_flush_messages();
      
      $visitor_room_info = $this->db->Get("messages", "get_visitor_time", array("visitor_sid"=>$GUID, "room_id"=>$room_id));
      
      $messages = $this->db->Get("messages", "get_visitor_messages", array("visitor_id"=>$visitor_room_info['visitor_id'], "room_id"=>$room_id, "last_update"=>$visitor_room_info['last_update']));

      while(NULL !== $message = array_shift($messages)) {
         if($message['owner_id'] == $visitor_room_info['visitor_id'] && $message['message_code'] == MESSAGE_CODE_VISITOR) {
            // We don't want our own messages as they are already displayed elsewhere
            continue;
         }
         $new_msg = new chat_message_container();
            $new_msg->message_id = $message['message_id'];
            $new_msg->message_prefix = $message['message_prefix'];
            $new_msg->room_id = $message['room_id'];
            $new_msg->message_date = $message['message_date'];
            $new_msg->message_code = $message['message_code'];
            $new_msg->owner_id = $message['owner_id'];
         $this->messages[$message['message_id']] = $new_msg;
         $line_id[$new_msg->room_id] = $new_msg->message_id;
         $msg_ids[] = $new_msg->message_id;
         if($last_update < $message['message_date']) $last_update = $message['message_date'];
      }
      $this->_build_message_parts($msg_ids);
      
      if($last_update != 0 && $last_update > $visitor_room_info['last_update']) {
         $this->db->Save("messages", "update_visitor_time", array("visitor_id"=>$visitor_room_info['visitor_id'], "room_id"=>$room_id, "last_update"=>$last_update));
      }
            
      return $this->messages;
   }
   
   function save_visitor_message($visitor_sid, $message, $room_id) {
      $visitor = $this->db->Get("messages", "get_visitor_time", array("visitor_sid"=>$visitor_sid, "room_id"=>$room_id));
      
      $msg = new chat_message_container();
		$msg->room_id = $room_id;
		$msg->owner_id = $visitor['visitor_id'];
		$msg->message_code = MESSAGE_CODE_VISITOR;
		$msg->message_date = time();
		$msg->message_prefix = empty($visitor['visitor_name']) ? 'Visitor' : $visitor['visitor_name'];
		$msg->message = $message;
		return $this->write_chat_message($msg);
   }

}

class chat_message_container {
   var $message_id = null;
   var $room_id = null;
   var $owner_id = null;
   var $message_code = null;
   var $message_date = null;
   var $message_prefix = null;
   var $message = null;
};