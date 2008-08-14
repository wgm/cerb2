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
 * Database abstraction layer for team data
 *
 */
class teams_sql
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
    * @return teams_sql
    */
   function teams_sql(&$db) {
      $this->db =& $db;
   }
   
   function add_team($params) {
      extract($params);
      $sql = "INSERT INTO team (team_name, team_acl1, team_acl2, team_acl3) VALUES (%s, '%d', '%d', '%d')";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($name), $acl1, $acl2, $acl3))) {
         return FALSE;
      }
      return $this->db->Insert_ID();
   }
   
   function add_member($params) {
      extract($params);
      $sql = "INSERT INTO team_members (team_id, agent_id) VALUES ('%d', '%d')";
      return $this->db->Execute(sprintf($sql, $team_id, $agent_id));
   }
   
   function get_team_info($params) {
      extract($params);
      $sql = "SELECT t.*, q.*, tq.queue_access FROM team t LEFT JOIN team_queues tq USING (team_id) LEFT JOIN queue q USING (queue_id) WHERE t.team_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $team_id));
   }
   
   function get_member_info($params) {
      extract($params);
      $sql = "SELECT tm.*, u.user_name, u.user_login FROM team_members tm LEFT JOIN user u ON (tm.agent_id = u.user_id) WHERE tm.team_id = '%d' AND tm.agent_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $team_id, $agent_id));
   }
   
   function remove_member($params) {
      extract($params);
      $sql = "DELETE FROM team_members WHERE team_id = '%d' AND member_id = '%d'";
      return $this->db->Execute(sprintf($sql, $team_id, $member_id));
   }
   
   function remove_team($params) {
      extract($params);
      $sql = "DELETE FROM department_teams WHERE team_id = '%d'";
      $this->db->Execute(sprintf($sql, $team_id));
      $sql = "DELETE FROM team WHERE team_id = '%d'";
      return $this->db->Execute(sprintf($sql, $team_id));
   }
   
   function update_team($params) {
      extract($params);
      $sql = "UPDATE team SET team_name = %s, team_acl1 = '%d', team_acl2 = '%d', team_acl3 = '%d' WHERE team_id = '%d'";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($name), $acl1, $acl2, $acl3, $team_id));
   }
   
//   function update_member($params) {
//      extract($params);
//      $sql = "UPDATE team_members SET agent_options = '%d' WHERE team_id = '%d' AND agent_id = '%d'";
//      return $this->db->Execute(sprintf($sql, $options, $team_id, $agent_id));
//   }
   
   function delete_queues($params) {
      extract($params);
      $sql = "DELETE FROM team_queues WHERE team_id = '%d'";
      return $this->db->Execute(sprintf($sql, $team_id));
   }
   
   function save_queue($params) {
      extract($params);
      $sql = "REPLACE INTO team_queues (team_id, queue_id, queue_access) VALUES ('%d', '%d', '%d')";
      return $this->db->Execute(sprintf($sql, $team_id, $queue_id, $queue_access));
   }
   
   function get_queues_from_teams_get_workable($params) {
      extract($params);
      $team_list = "'" . implode("','", $teams) . "'";
      $sql = "SELECT queue_id FROM team_queues WHERE queue_access = 2 AND team_id IN (%s)";
      return $this->db->GetAll(sprintf($sql, $team_list));
   }
   
   function get_queues_from_teams_read_writable($params) {
      extract($params);
      $team_list = "'" . implode("','", $teams) . "'";
      $sql = "SELECT queue_id FROM team_queues WHERE queue_access > 0 AND team_id IN (%s)";
      return $this->db->GetAll(sprintf($sql, $team_list));
   }
   
   function get_queues_from_user_id_read_writable($params) {
      extract($params);
      $sql = "SELECT queue_id FROM `team_members` LEFT JOIN team_queues USING ( team_id ) WHERE agent_id = '%d' AND queue_access > 0";
      return $this->db->GetAll(sprintf($sql, $user_id));
   }
   
   function get_members($params) {
      extract($params);
      $sql = "SELECT tm.*, u.user_name, u.user_login FROM team_members tm LEFT JOIN user u ON (tm.agent_id = u.user_id) WHERE tm.team_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $team_id));
   }          
}