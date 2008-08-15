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

/**
 * Database abstraction layer for chat messages
 *
 */
class messages_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;
   
   function messages_sql(&$db) {
      $this->db =& $db;
   }
   
   function write_message($params) {
      $room_id = $params['room_id'];
      $owner_id = $params['owner_id'];
      $message_code = $params['message_code'];
      $message_date = $params['message_date'];
      $message_prefix = $params['message_prefix'];
      $sql = "INSERT INTO `chat_messages` (room_id, owner_id, message_code, message_date, message_prefix) ".
					"VALUES ('%d', '%d', '%d', UNIX_TIMESTAMP(), %s)";
		if($this->db->Execute(sprintf($sql, $room_id, $owner_id, $message_code, $this->db->qstr($message_prefix)))) {
		   return $this->db->Insert_ID();
		}
		else {
		   return FALSE;
		}
   }	
   
   function write_message_chunks($params) {
      $msg_id = $params['message_id'];
      $msg_chunk = $params['message_chunk'];
      $sql = "INSERT INTO `chat_message_parts` (`message_id`, `message_part`) VALUES ('%d', %s)";
      return $this->db->Execute(sprintf($sql, $msg_id, $this->db->qstr($msg_chunk)));
   }   
   
   function get_agent_messages($params) {
      $agent_id = $params['agent_id'];
      $max = $params['max'];
      $sql = "SELECT m.message_id , m.message_prefix , m.message_code , m.owner_id, m.room_id FROM chat_agents_to_rooms ar
               LEFT JOIN chat_messages m USING ( room_id ) WHERE ar.agent_id = '%d' AND m.message_id > ar.line_id
               AND m.owner_id != '%d' ORDER BY m.message_id ASC LIMIT 0 , %d";
      return $this->db->GetAll(sprintf($sql, $agent_id, $agent_id, $max));
   }
   
   function get_room_messages($params) {
      extract($params);
      $sql = "SELECT message_id , message_prefix , message_code , owner_id, room_id FROM `chat_messages` WHERE `message_id` > %d 
               AND `room_id` = %d ORDER BY `room_id`, `message_id` ASC ";
      return $this->db->GetAll(sprintf($sql, $line_id, $room_id));
   }
   
   function update_agent_room_line_id($params) {
      $line_id = $params['line_id'];
      $room_id = $params['room_id'];
      $agent_id = $params['agent_id'];
      $sql = "UPDATE chat_agents_to_rooms SET line_id = '%d' WHERE room_id = '%d' AND agent_id = '%d'";
      return $this->db->Execute(sprintf($sql, $line_id, $room_id, $agent_id));
   }
   
   function get_message_parts($params) {
      $msg_ids = $params['message_ids'];
      $inlist = implode(",", $msg_ids);
      $sql = "SELECT message_id, message_part FROM chat_message_parts WHERE message_id IN (%s) ORDER BY message_id ASC";
      return $this->db->GetAll(sprintf($sql, $inlist));
   }
   
   // [JAS]: Removed the LEFT JOIN here and it gave us -much- speedier lookups.
   function get_visitor_time($params) {
      extract($params);
      $sql = "SELECT cv.visitor_id, cv.visitor_name, cvr.room_id, cvr.last_update ".
      		"FROM (chat_visitors cv, chat_visitors_to_rooms cvr) ".
      		"WHERE cv.visitor_id = cvr.visitor_id AND cv.visitor_sid = %s AND cvr.room_id = %d";
      return $this->db->GetRow(sprintf($sql, $this->db->qstr($visitor_sid), $room_id));
   }
   
   function get_visitor_messages($params) {
      extract($params);
      $sql = "SELECT message_id , message_prefix , message_code , owner_id, room_id, message_date FROM `chat_messages` WHERE `message_date` > %d 
               AND `room_id` = %d ORDER BY `message_id` ASC ";
      return $this->db->GetAll(sprintf($sql, $last_update, $room_id));
   }
   
   function update_visitor_time($params) {
      extract($params);
      $sql = "UPDATE chat_visitors_to_rooms SET last_update = '%d' WHERE visitor_id = '%d' AND room_id = '%d'";
      $this->db->Execute(sprintf($sql, $last_update, $visitor_id, $room_id));
   }
}