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
 * Database abstraction layer for chat rooms
 *
 */
class rooms_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;
   
   function rooms_sql(&$db) {
      $this->db =& $db;
   }
   
   function agent_in_room($params) {
      $agent_id = $params['agent_id'];
      $room_id = $params['room_id'];
      $sql = "SELECT ar.agent_id, ar.room_id, ar.join_flags FROM chat_agents_to_rooms ar WHERE ar.agent_id = '%d' AND ar.room_id = '%d'";
      $agent_data = $this->db->GetRow(sprintf($sql, $agent_id, $room_id));
      if(is_array($agent_data) && count($agent_data)) {
         return $agent_data;
      }
      else {
         return FALSE;
      }
   }
   
   function agent_join_room($params) {
      $agent_id = $params['agent_id'];
      $room_id = $params['room_id'];
      $line_id = $params['line_id'];
      $join_flags = $params['join_flags'];			
		$sql = "INSERT INTO chat_agents_to_rooms (agent_id, room_id, line_id, join_flags) VALUES ('%d','%d','%d','%d') ";
		return $this->db->Execute(sprintf($sql,$agent_id,$room_id,$line_id,$join_flags));
   }
   
   function get_history_line_id($params) {
      $room_id = $params['room_id'];
      $history_hrs = $params['history_hrs'];
      $sql = "SELECT message_id FROM chat_messages WHERE room_id = '%d' AND message_date >= DATE_SUB(NOW(),INTERVAL %d HOUR) ORDER BY message_id ASC LIMIT 0,1";
      $line_id = $this->db->GetOne(sprintf($sql, $room_id, $history_hrs));
      if(!$line_id) {
         $sql = "SELECT max(message_id) AS max_id FROM messages WHERE room_id = '%d'";
         $line_id = $this->db->GetOne(sprintf($sql, $room_id));
         if(!$line_id) {
            $line_id = 0;
         }
      }
      return $line_id;         
   }
   
   function agent_leave_room($params) {
      $agent_id = $params['agent_id'];
      $room_id = $params['room_id'];
      $sql = "DELETE FROM chat_agents_to_rooms WHERE agent_id = '%d' AND room_id = '%d'";
      $this->db->Execute(sprintf($sql, $agent_id, $room_id));
      return TRUE;
   }
   
   function create_room($params) {
      extract($params);
      $sql = "INSERT INTO chat_rooms (room_name, room_type, room_created) VALUES (%s, %s, UNIX_TIMESTAMP())";
      $this->db->Execute(sprintf($sql, $this->db->qstr($room_name), $this->db->qstr($room_type)));
      return $this->db->Insert_ID();
   }
   
   function remove_room($params) {
      extract($params);
      $sql = "DELETE FROM chat_rooms WHERE room_id = '%d'";
      return $this->db->Execute(sprintf($sql, $room_id));
   }
   
   function agents_in_room($params) {
      extract($params);
      $sql = "SELECT agent_id, join_flags, chat_display_name FROM chat_agents_to_rooms LEFT JOIN user_extended_info ON ( agent_id = user_id ) WHERE room_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $room_id));
   }
   
   function visitors_in_room($params) {
      extract($params);
      $sql = "SELECT * FROM chat_visitors_to_rooms LEFT JOIN chat_visitors USING ( visitor_id ) WHERE room_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $room_id));
   }
   
   function get_rooms_list($params) {
      extract($params);
      $query = "SELECT * FROM chat_rooms";
      return $this->db->GetAll($query);
   }
   
   function people_count($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM chat_visitors_to_rooms WHERE room_id = '%d'";
      $visitor_count = $this->db->GetOne(sprintf($sql, $room_id));
      $sql = "SELECT COUNT(*) FROM chat_agents_to_rooms WHERE room_id = '%d'";
      $agent_count = $this->db->GetOne(sprintf($sql, $room_id));
      return $visitor_count + $agent_count;
   }
   
   function unread_count($params) {
      extract($params);
      $sql = "SELECT line_id FROM chat_agents_to_rooms WHERE room_id = '%d' AND agent_id = '%d'";
      $line_id = 0+$this->db->GetOne(sprintf($sql, $room_id, $agent_id));
      $sql = "SELECT COUNT(*) FROM chat_messages WHERE room_id = '%d' AND message_id > '%d'";
      return $this->db->GetOne(sprintf($sql, $room_id, $line_id));      
   }
   
   function room_exists($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM chat_rooms WHERE room_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $room_id));
   }
   
   function visitor_join_room($params) {
      extract($params);
      $sql = "INSERT INTO chat_visitors_to_rooms (visitor_id, room_id, last_update) VALUES ('%d', '%d', 0)";
      return $this->db->Execute(sprintf($sql, $visitor_id, $room_id));
   }
   
   function visitor_leave_room($params) {
      extract($params);
      $sql = "DELETE FROM chat_visitors_to_rooms WHERE visitor_id = '%d' AND room_id = '%d'";
      return $this->db->Execute(sprintf($sql, $visitor_id, $room_id));
   }
   
   function get_stale_rooms($params) {
      extract($params);
      $query = "SELECT cr.room_id FROM chat_rooms cr LEFT JOIN chat_agents_to_rooms catr USING ( room_id ) LEFT JOIN chat_visitors_to_rooms cvtr ON ( cr.room_id = cvtr.room_id ) WHERE cvtr.visitor_id IS NULL AND catr.agent_id IS NULL";
      return $this->db->GetAll($query);
   }
   
   function any_agent_in_room($params) {
      $room_id = $params['room_id'];
      $sql = "SELECT COUNT(*) FROM chat_agents_to_rooms ar WHERE ar.room_id = '%d'";
      $count = $this->db->GetOne(sprintf($sql, $room_id));
      if($count > 0) {
         return TRUE;
      }
      else {
         return FALSE;
      }
   }
}