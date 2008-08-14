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
 * Database abstraction layer for skills data
 *
 */
class skills_sql
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
    * @return skills_sql
    */
   function skills_sql(&$db) {
      $this->db =& $db;
   }

   /**
    * Get skill list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_skills($params) {
      $query = "SELECT * FROM skill";
      return $this->db->GetAll($query);
   }

   /**
    * Get category list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_categories($params) {
      $query = "SELECT * FROM skill_category";
      return $this->db->GetAll($query);
   }

   /**
    * Get agent's skill list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_agent_skills($params) {
      $agent_id = $params['agent_id'];
      $sql = "SELECT * FROM skill_to_agent WHERE agent_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $agent_id));
   }

   /**
    * Save agent's skill list
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function save_agent_skills($params) {
      $agent_id = $params['agent_id'];
      $skills_list = $params['skills_list'];
      $sql = "DELETE FROM skill_to_agent WHERE agent_id = '%d'";
      $this->db->Execute(sprintf($sql, $agent_id));
      $sql = "INSERT INTO skill_to_agent (`skill_id`, `agent_id`, `has_skill`) VALUES ('%d', '%d', 1)";
      foreach($skills_list as $skill) {
         $this->db->Execute(sprintf($sql, $skill, $agent_id));
      }
      return TRUE;
   }

   /**
    * Add skill
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function add_skill($params) {
      $name = $params['name'];
      $description = $params['description'];
      $category_id = 0+$params['category_id'];
      $sql = "INSERT INTO skill (`skill_name`, `skill_description`, `skill_category_id`) VALUES (%s, %s, '%d')";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($name), $this->db->qstr($description), $category_id))) {
         return FALSE;
      }
      else {
         return $this->db->Insert_ID();
      }
   }

   /**
    * change skill
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function change_skill($params) {
      $skill_id = 0+$params['skill_id'];
      $name = $params['name'];
      $description = $params['description'];
      $category_id = 0+$params['category_id'];
      $sql = "UPDATE skill SET `skill_name` = %s, `skill_description` = %s, `skill_category_id` = '%d' WHERE skill_id = '%d'";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($name), $this->db->qstr($description), $category_id, $skill_id));
   }

   /**
    * remove skill
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function remove_skill($params) {
      extract($params);
      $sql = "DELETE FROM skill_to_ticket WHERE skill_id = '%d'";
      $this->db->Execute(sprintf($sql, $skill_id));
      $sql = "DELETE FROM skill_to_agent WHERE skill_id = '%d'";
      $this->db->Execute(sprintf($sql, $skill_id));
      $sql = "DELETE FROM skill WHERE skill_id = '%d'";
      return $this->db->Execute(sprintf($sql, $skill_id));
   }

   /**
    * Add category
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function add_category($params) {
      $name = $params['name'];
      $parent_category_id = 0+$params['parent_category_id'];
      $sql = "INSERT INTO skill_category (`category_name`, `category_parent_id`) VALUES (%s, '%d')";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($name), $parent_category_id))) {
         return FALSE;
      }
      else {
         return $this->db->Insert_ID();
      }
   }

   /**
    * change category
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function change_category($params) {
      $category_id = 0+$params['category_id'];
      $name = $params['name'];
      $parent_category_id = 0+$params['parent_category_id'];
      $sql = "UPDATE skill_category SET `category_name` = %s, `category_parent_id` = '%d' WHERE category_id = '%d'";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($name), $parent_category_id, $category_id));
   }

   /**
    * remove category
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function remove_category($params) {
      $category_id = 0+$params['category_id'];
      $sql = "DELETE FROM skill_category WHERE category_id = '%d'";
      return $this->db->Execute(sprintf($sql, $category_id));
   }
   
   /**
    * check how many skills are in a category
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function category_skill_count($params) {
      extract($params);
      $sql = "SELECT COUNT(skill_id) FROM skill WHERE skill_category_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $category_id));
   }
   
   
   /**
    * Get ticket's skill list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_ticket_skills($params) {
      $ticket_id = $params['ticket_id'];
      $sql = "SELECT * FROM skill_to_ticket WHERE ticket_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $ticket_id));
   }

   /**
    * Save ticket's skill list
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function save_ticket_skills($params) {
      $ticket_id = $params['ticket_id'];
      $skills_list = $params['skills_list'];
      $sql = "DELETE FROM skill_to_ticket WHERE ticket_id = '%d'";
      $this->db->Execute(sprintf($sql, $ticket_id));
      $sql = "INSERT INTO skill_to_ticket (`skill_id`, `ticket_id`) VALUES ('%d', '%d')";
      foreach($skills_list as $skill) {
         $this->db->Execute(sprintf($sql, $skill, $ticket_id));
      }
      return TRUE;
   }
   
   /**
    * update count of skills for a ticket
    *
    * @param array $params Associative array of parameters
    * @return bool
    */
   function update_ticket_skill_count($params) {
      extract($params);
      $sql = "UPDATE ticket SET skill_count = '%d' WHERE ticket_id = '%d'";
      return $this->db->Execute(sprintf($sql, $count, $ticket_id));
   }
}