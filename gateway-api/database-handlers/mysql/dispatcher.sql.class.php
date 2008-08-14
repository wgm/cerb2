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
 * Database abstraction layer for dispatcher
 *
 */
class dispatcher_sql
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
    * @return dispatcher_sql
    */
   function dispatcher_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Clears something from the dispatcher_delays
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function clear_from_delay_queue($params) {
   	extract($params);
      $sql = "DELETE FROM dispatcher_delays WHERE delay_id = '%d'";
      return $this->db->Execute(sprintf($sql, $delay_id));
   }
   
   /**
    * Clears something from the dispatcher_suggestions
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function clear_from_suggestion_queue($params) {
   	extract($params);
      $sql = "DELETE FROM dispatcher_suggestions WHERE suggestion_id = '%d'";
      return $this->db->Execute(sprintf($sql, $suggestion_id));
   }
   
   /**
    * Gets a list of tickets from the assignment_queue based on date
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_date($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT dd.delay_id, dd.ticket_id FROM dispatcher_delays dd LEFT JOIN ticket t USING (ticket_id) WHERE dd.agent_id = '%d' AND dd.delay_type = '%d' AND dd.expire_timestamp != 1 AND dd.expire_timestamp <= UNIX_TIMESTAMP() AND t.ticket_assigned_to_id = 0 AND t.ticket_queue_id IN (%s) LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $user_id, DISPATCHER_DELAY_DATE, $queue_list, $max_tickets));
   }
   
   /**
    * Gets a list of tickets from the assignment_queue based on customer reply
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_customer_reply($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT dd.delay_id, dd.ticket_id FROM dispatcher_delays dd LEFT JOIN ticket t USING (ticket_id) WHERE dd.agent_id = '%d' AND dd.delay_type = '%d' AND t.last_reply_by_agent = 0 AND t.ticket_assigned_to_id = 0 AND t.ticket_queue_id IN (%s) LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $user_id, DISPATCHER_DELAY_CUSTOMER_REPLY, $queue_list, $max_tickets));
   }
   
   /**
    * Gets a list of tickets with no specific criteria
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_anywhere($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT t.ticket_id FROM ticket t WHERE t.ticket_assigned_to_id = 0 AND t.ticket_status NOT IN ('resolved', 'dead', 'awaiting-reply') AND t.ticket_queue_id IN (%s) ORDER BY t.ticket_date DESC LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $queue_list, $max_tickets));
   }
   
   /**
    * Gets a list of tickets with skills match
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_skills_match($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT stt.ticket_id, COUNT(stt.ticket_id)/t.skill_count AS percentage_match FROM skill_to_agent sta LEFT JOIN skill_to_ticket stt USING (skill_id) LEFT JOIN ticket t USING (ticket_id) WHERE sta.agent_id = '%d' AND sta.has_skill = 1 AND t.ticket_status NOT IN ('resolved','dead','awaiting-reply') AND t.ticket_assigned_to_id = 0 AND t.ticket_queue_id IN (%s) GROUP BY stt.ticket_id LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $user_id, $queue_list, $max_tickets));
   }
   
   /**
    * Gets a list of tickets which are overdue (or close to it)
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_overdue($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT t.ticket_id FROM ticket t WHERE t.ticket_assigned_to_id = 0 AND t.ticket_status NOT IN ('resolved', 'dead') AND t.ticket_due <= NOW() AND t.ticket_queue_id IN (%s) LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $queue_list, $max_tickets));
   }
   
   /**
    * Gets a list of tickets with high priority
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_high_priority($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT t.ticket_id FROM ticket t WHERE t.ticket_assigned_to_id = 0 AND t.ticket_status NOT IN ('resolved', 'dead', 'awaiting-reply') AND t.ticket_id >= '%d' AND t.ticket_queue_id IN (%s) LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $priority, $queue_list, $max_tickets));
   }
   
   /**
    * Gets a list of tickets which others have scheduled to resurrect to them
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_scheduled_others($params) {
   	extract($params);
   	$ticket_list = "'" . implode("','", $ticket_ids) . "'";
      $sql = "SELECT DISTINCT ticket_id FROM dispatcher_delays WHERE ticket_id IN ( %s ) AND agent_id != '%d' AND (expire_timestamp != '1' OR delay_type != %d)";
      return $this->db->GetAll(sprintf($sql, $ticket_list, $user_id, DISPATCHER_DELAY_DATE));
   }
   
   /**
    * Gets a list of tickets which I have ignored or rejected
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_ignored_or_rejected($params) {
   	extract($params);
      $sql = "SELECT DISTINCT ticket_id FROM dispatcher_delays WHERE agent_id = '%d' AND delay_type = '%d'";
      return $this->db->GetAll(sprintf($sql, $user_id, DISPATCHER_DELAY_DATE));
   }

   /**
    * Purges the ignored tickets whose time has expired (leaving permanent rejects in tact)
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function purge_ignored_tickets($params) {
   	extract($params);
      $sql = "DELETE FROM dispatcher_delays WHERE delay_type = '%d' AND expire_timestamp != 1 AND expire_timestamp < UNIX_TIMESTAMP()";
      return $this->db->Execute(sprintf($sql, DISPATCHER_DELAY_DATE));
   }
   
   /**
    * Saves the teams an agent pulled tickets from
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function save_pulled_teams($params) {
   	extract($params);
   	$teams_list = "'" . implode("','", $teams) . "'";
      $sql = "UPDATE team_members SET ticket_pull = 1 WHERE agent_id = '%d' AND team_id IN (%s)";
      return $this->db->Execute(sprintf($sql, $user_id, $teams_list));
   }
   
   /**
    * Clears the pulled ticket's mark on all teams for an agent
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function clear_ticket_pulled_teams($params) {
   	extract($params);
   	$sql = "UPDATE team_members SET ticket_pull = 0 WHERE agent_id = '%d'";
      return $this->db->Execute(sprintf($sql, $user_id, $teams_list));
   }
   
   function recommend_ticket($params) {
      extract($params);
      $sql = "DELETE FROM dispatcher_suggestions WHERE ticket_id = '%d'";
      $this->db->Execute(sprintf($sql, $ticket_id));
      if(is_array($member_ids) && count($member_ids) > 0) {
      	$insert_sql = '';
      	foreach($member_ids as $id) {
      		$insert_sql .= sprintf(" ('%d', '%d', UNIX_TIMESTAMP()),", $ticket_id, $id);
      	}
      	$insert_sql = substr($insert_sql, 0, -1) . ";";
      	$sql = "INSERT INTO dispatcher_suggestions (ticket_id, member_id, timestamp) VALUES %s";
      	return $this->db->Execute(sprintf($sql, $insert_sql));
      }
      else {
      	return TRUE;
      }
   }
   
   /**
    * Gets a list of tickets from the assignment_queue that were recommended to me
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_tickets_recommended_to_me($params) {
   	extract($params);
   	$queue_list = "'" . implode("','", $queues) . "'";
      $sql = "SELECT ds.suggestion_id, ds.ticket_id FROM dispatcher_suggestions ds LEFT JOIN ticket t USING (ticket_id) LEFT JOIN team_members tm ON (ds.member_id = tm.member_id) WHERE tm.agent_id = '%d' AND t.ticket_assigned_to_id = 0 AND t.ticket_queue_id IN (%s) LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $user_id, $queue_list, $max_tickets));
   }
     
   function accept_ticket($params) {
      extract($params);
      $sql = "UPDATE ticket SET ticket_assigned_to_id = '%d', last_update_date = NOW() WHERE ticket_id = '%d' AND ticket_assigned_to_id = 0";
      $this->db->Execute(sprintf($sql, $user_id, $ticket_id));
      if($this->db->Affected_Rows() == 1) {
         return TRUE;
      }
      else {
         return FALSE;
      }
   }
   
   function hide_ticket($params) {
      extract($params);
      $sql = "INSERT INTO dispatcher_delays (ticket_id, agent_id, delay_type, added_timestamp, expire_timestamp, reason) VALUES ('%d', '%d', '%d', UNIX_TIMESTAMP(), 1, %s)";
      return $this->db->Execute(sprintf($sql, $ticket_id, $user_id, DISPATCHER_DELAY_DATE, $this->db->qstr($reason)));
   }
   
   function delay_ticket($params) {
      extract($params);
      $sql = "INSERT INTO dispatcher_delays (ticket_id, agent_id, delay_type, added_timestamp, expire_timestamp, reason) VALUES ('%d', '%d', '%d', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()+%d, %s)";
      return $this->db->Execute(sprintf($sql, $ticket_id, $user_id, DISPATCHER_DELAY_DATE, $mins*60, $this->db->qstr($reason)));
   }
   
   function get_ticket_suggestions($params) {
   	extract($params);
   	$sql = "SELECT ds.member_id, tm.agent_id, ds.timestamp FROM dispatcher_suggestions ds LEFT JOIN team_members tm USING (member_id) WHERE ds.ticket_id = '%d'";
   	return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
}
