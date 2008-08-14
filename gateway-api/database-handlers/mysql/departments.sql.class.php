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
 * Database abstraction layer for departments data
 *
 */
class departments_sql
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
    * @return departments_sql
    */
   function departments_sql(&$db) {
      $this->db =& $db;
   }
   
   function add_department($params) {
      extract($params);
      $sql = "INSERT INTO department (department_name, department_usage) VALUES (%s, '%d')";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($name), $usage))) {
         return FALSE;
      }
      else {
         return $this->db->Insert_ID();
      }
   }
   
   function assign_team($params) {
      extract($params);
      $sql = "INSERT INTO department_teams (department_id, team_id) VALUES ('%d', '%d')";
      return $this->db->Execute(sprintf($sql, $id, $team_id));
   }
   
   function unassign_team($params) {
      extract($params);
      $sql = "DELETE FROM department_teams WHERE department_id = '%d' AND team_id = '%d'";
      return $this->db->Execute(sprintf($sql, $id, $team_id));
   }
   
   function remove_department($params) {
      extract($params);
      $sql = "DELETE FROM department_teams WHERE department_id = '%d'";
      $this->db->Execute(sprintf($sql, $id));
      $sql = "DELETE FROM department WHERE department_id = '%d'";
      return $this->db->Execute(sprintf($sql, $id));
   }
   
   function save_department($params) {
      extract($params);
      $sql = "UPDATE department SET department_name = %s, department_usage = '%d', department_offline_address = %s WHERE department_id = '%d'";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($name), $usage, $this->db->qstr($offline_address), $id));
   }
   
   function get_info($params) {
      extract($params);
      $sql = "SELECT * FROM department WHERE department_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $id));
   }
   
   function get_department_team_count($params) {
   	extract($params);
   	$sql = "SELECT COUNT(team_id) FROM department_teams WHERE department_id = '%d'";
   	return $this->db->GetOne(sprintf($sql, $department_id));
   }
   
   function get_teams_list($params) {
      extract($params);
      $sql = "SELECT dt.team_id, t.team_name FROM department_teams dt LEFT JOIN team t USING (team_id) WHERE dt.department_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $id));
   }
   
   function get_departments_list($params) {
      extract($params);
      $query = "SELECT * FROM department";
      return $this->db->GetAll($query);
   }
   
   function get_departments_chat_status_list($params) {
      extract($params);
      $query = "SELECT d.department_id, d.department_name, d.department_usage, max( ses.chat_status ) AS department_status
			FROM department d
			LEFT JOIN department_teams dt USING ( department_id )
			LEFT JOIN team_members tm USING ( team_id )
			LEFT JOIN gateway_session ses ON ( tm.agent_id = ses.user_id )
			WHERE d.department_usage & 1 = 1
			GROUP BY (d.department_id)";
      return $this->db->GetAll($query);
   }
   
   function get_department_chat_status($params) {
   	extract($params);
   	$sql = "SELECT d.department_id, d.department_name, d.department_usage, max( ses.chat_status ) AS department_status
			FROM department d
			LEFT JOIN department_teams dt USING ( department_id )
			LEFT JOIN team_members tm USING ( team_id )
			LEFT JOIN gateway_session ses ON ( tm.agent_id = ses.user_id )
			WHERE d.department_usage & 1 = 1 AND d.department_id = '%d'
			GROUP BY (d.department_id)";
   	return $this->db->GetRow(sprintf($sql, $dept_id));
   }
   
   function get_department_active_agents($params) {
   	extract($params);
		$sql = "SELECT DISTINCT u.user_id, u.user_name, uei.chat_display_name, ses.chat_status 
			FROM gateway_session ses, user u, user_extended_info uei, department_teams dt, team_members tm 
			WHERE dt.team_id = tm.team_id 
			AND tm.agent_id = u.user_id 
			AND ses.user_id = u.user_id 
			AND uei.user_id = u.user_id 
			AND ses.chat_status = 2 
			AND dt.department_id = %d";
   	return $this->db->GetAll(sprintf($sql, $dept_id));
   }
   
   function get_department_team_list_for_agent($params) {
      extract($params);
      $sql = "SELECT tm.ticket_pull, t.team_name, d.department_name, t.team_id, d.department_id
               FROM team_members tm
               LEFT JOIN team t USING ( team_id )
               LEFT JOIN department_teams dt USING ( team_id )
               LEFT JOIN department d
               USING ( department_id )
               WHERE tm.agent_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $user_id));
   }
   
   function get_departments_from_teams_list($params) {
      extract($params);
      $team_list = "'" . implode("','", $teams) . "'";
      $sql = "SELECT DISTINCT department_id FROM department_teams WHERE team_id IN ( %s )";
      return $this->db->GetAll(sprintf($sql, $team_list));
   }
}
