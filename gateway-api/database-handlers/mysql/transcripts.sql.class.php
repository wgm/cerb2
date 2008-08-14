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
 * Database abstraction layer for chat transcripts data
 *
 */
class transcripts_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;

   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return reports_sql
    */
   function transcripts_sql(&$db) {
      $this->db =& $db;
   }
   
   function get_headers($params) {
      extract($params);
      $sql = "SELECT t.transcript_id, t.transcript_date, t.room_id, t.room_name FROM chat_transcripts t WHERE t.transcript_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL %d DAY)) ORDER BY t.transcript_date DESC, t.transcript_id ASC";
      return $this->db->GetAll(sprintf($sql, $range));
   }
   
   function get_headers_by_agent($params) {
      extract($params);
      $sql = "SELECT t.transcript_id, t.transcript_date, t.room_id, t.room_name FROM chat_transcripts t LEFT JOIN chat_messages m USING (room_id) WHERE m.owner_id = '%d' AND t.transcript_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL %d DAY)) GROUP BY t.transcript_id ORDER BY t.transcript_date DESC, t.transcript_id ASC";
      return $this->db->GetAll(sprintf($sql, $agent_id, $range));
   }
   
   function get_headers_by_search($params) {
      extract($params);
      $sql = "SELECT t.transcript_id, t.transcript_date, t.room_id, t.room_name FROM chat_transcripts t LEFT JOIN chat_messages m USING (room_id) LEFT JOIN chat_message_parts mp USING (message_id) WHERE mp.message_part LIKE %s AND t.transcript_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL %d DAY)) GROUP BY t.transcript_id ORDER BY t.transcript_date DESC, t.transcript_id ASC";
      return $this->db->GetAll(sprintf($sql, $this->db->qstr('%'.$search.'%'), $range));
   }
   
   function get_headers_by_search_agent($params) {
      extract($params);
      $sql = "SELECT t.transcript_id, t.transcript_date, t.room_id, t.room_name FROM chat_transcripts t LEFT JOIN chat_messages m USING (room_id) LEFT JOIN chat_message_parts mp USING (message_id) WHERE mp.message_part LIKE %s AND m.owner_id = '%d' AND t.transcript_date >= UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL %d DAY)) GROUP BY t.transcript_id ORDER BY t.transcript_date DESC, t.transcript_id ASC";
      return $this->db->GetAll(sprintf($sql, $this->db->qstr('%'.$search.'%'), $agent_id, $range));
   }
   
   function create_transcript($params) {
      extract($params);
      $sql = "INSERT INTO chat_transcripts (transcript_date, room_id, room_name) VALUES (UNIX_TIMESTAMP(), '%d', %s)";
      return $this->db->Execute(sprintf($sql, $room_id, $this->db->qstr($room_name)));
   }
   
   function get_transcript_info($params) {
      extract($params);
      $sql = "SELECT * FROM chat_transcripts WHERE transcript_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $transcript_id));
   }
   

}