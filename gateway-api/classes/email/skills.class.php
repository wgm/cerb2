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

class email_skills
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function email_skills() {
      $this->db =& database_loader::get_instance();
   }

   function get_skills_list() {
      if($this->build_skills() === FALSE || $this->build_categories() === FALSE) {
         return FALSE;
      }
      else {
         return TRUE;
      }
   }

   function build_skills() {
      $skills_array = $this->db->get("skills", "get_skills");
      if(!is_array($skills_array)) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $skills =& $data->add_child("skills", xml_object::create("skills"));
      foreach($skills_array as $item) {
         $skill =& $skills->add_child("skill", xml_object::create("skill", $item['skill_name'], array('id'=>$item['skill_id'], 'category'=>$item['skill_category_id'])));
         $skill->add_child("description", xml_object::create("description", $item['skill_description']));
      }
      return TRUE;
   }

   function build_categories() {
      $cats_array = $this->db->get("skills", "get_categories");
      if(!is_array($cats_array)) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $cats =& $data->add_child("skill_categories", xml_object::create("skill_categories"));
      foreach($cats_array as $item) {
         $cats->add_child("category", xml_object::create("category", $item['category_name'], array('id'=>$item['category_id'], 'parent'=>$item['category_parent_id'])));
      }
      return TRUE;
   }
   
   function get_agent_skills($agent_id) {
      $skills_list = $this->db->Get("skills", "get_agent_skills", array("agent_id"=>$agent_id));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $skills =& $data->add_child("skills", xml_object::create("skills"));
      if(!is_array($skills_list)) {
         return TRUE;
      }
      foreach($skills_list as $skill) {
         $skills->add_child("skill", xml_object::create("skill", NULL, array("id"=>$skill['skill_id'], "has_skill"=>$skill['has_skill'])));
      }
      return TRUE;
   }
   
   function save_agent_skills($agent_id, $skills_list) {
      $this->db->Save("skills", "save_agent_skills", array("agent_id"=>$agent_id, "skills_list"=>$skills_list));
      return TRUE;
   }
   
   function get_ticket_skills($ticket_id) {
      $skills_list = $this->db->Get("skills", "get_ticket_skills", array("ticket_id"=>$ticket_id));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $skills =& $data->add_child("skills", xml_object::create("skills"));
      if(!is_array($skills_list)) {
         return TRUE;
      }
      foreach($skills_list as $skill) {
         $skills->add_child("skill", xml_object::create("skill", NULL, array("id"=>$skill['skill_id'])));
      }
      return TRUE;
   }
   
   function save_ticket_skills($ticket_id, $skills_list) {
      $this->db->Save("skills", "save_ticket_skills", array("ticket_id"=>$ticket_id, "skills_list"=>$skills_list));
      $this->db->Save("skills", "update_ticket_skill_count", array("ticket_id"=>$ticket_id, "count"=>count($skills_list)));
      return TRUE;
   }
   
   function add_skill($name, $description, $category_id) {
      if(strlen($description) > 255) {
         xml_output::error("0", "Skill description to long");
      }
      if(strlen($name) < 1) {
         xml_output::error("0", "Skill name to short. Minimum one character");
      }
      if(!is_numeric($category_id) || $category_id < 1) {
         xml_output::error("0", "Skill category id is invalid");
      }
      $skill_id = $this->db->Save("skills", "add_skill", array("name"=>$name, "description"=>$description, "category_id"=>$category_id));
      if($skill_id === FALSE) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $skill =& $data->add_child("skill", xml_object::create("skill", $name, array('id'=>$skill_id, 'category'=>$category_id)));
      $skill->add_child("description", xml_object::create("description", $description));
      return TRUE;
   }
   
   function change_skill($skill_id, $name, $description, $category_id) {
      if(strlen($description) > 255) {
         xml_output::error("0", "Skill description to long");
      }
      if(strlen($name) < 1) {
         xml_output::error("0", "Skill name to short. Minimum one character");
      }
      if(!is_numeric($category_id) || $category_id < 1) {
         xml_output::error("0", "Skill category id is invalid");
      }
      if(!is_numeric($skill_id) || $skill_id < 1) {
         xml_output::error("0", "Skill id is invalid");
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $skill =& $data->add_child("skill", xml_object::create("skill", $name, array('id'=>$skill_id, 'category'=>$category_id)));
      $skill->add_child("description", xml_object::create("description", $description));
      return $this->db->Save("skills", "change_skill", array("skill_id"=>$skill_id, "name"=>$name, "description"=>$description, "category_id"=>$category_id));
   }
   
   function remove_skill($skill_id) {
      if(!is_numeric($skill_id) || $skill_id < 1) {
         xml_output::error("0", "Skill id is invalid");
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $skill =& $data->add_child("skill", xml_object::create("skill", NULL, array("id"=>$skill_id)));
      return $this->db->Save("skills", "remove_skill", array("skill_id"=>$skill_id));
   }
   
   function add_category($name, $parent_category_id) {
      if(strlen($name) < 1) {
         xml_output::error("0", "Category name to short. Minimum one character");
      }
      if(!is_numeric($parent_category_id) || $parent_category_id < 0) {
         xml_output::error("0", "Parent category id is invalid");
      }
      $category_id = $this->db->Save("skills", "add_category", array("name"=>$name, "parent_category_id"=>$parent_category_id));
      if($category_id === FALSE) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $data->add_child("category", xml_object::create("category", $name, array('id'=>$category_id, 'parent'=>$parent_category_id)));
      return TRUE;
   }
   
   function change_category($category_id, $name, $parent_category_id) {
      if(strlen($name) < 1) {
         xml_output::error("0", "Category name to short. Minimum one character");
      }
      if(!is_numeric($category_id) || $category_id < 1) {
         xml_output::error("0", "Category id is invalid");
      }
      if(!is_numeric($parent_category_id) || $parent_category_id < 0) {
         xml_output::error("0", "Parent category id is invalid");
      }     
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $data->add_child("category", xml_object::create("category", $name, array('id'=>$category_id, 'parent'=>$parent_category_id)));
      return $this->db->Save("skills", "change_category", array("category_id"=>$category_id, "name"=>$name, "parent_category_id"=>$parent_category_id));
   }
   
   function remove_category($category_id) {
      if(!is_numeric($category_id) || $category_id < 1) {
         xml_output::error("0", "Category ID is invalid");
      }
      if($this->db->Get("skills", "category_skill_count", array("category_id"=>$category_id)) > 0) {
         xml_output::error("0", "Category has skills assigned, can't remove");
      } 
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $data->add_child("category", xml_object::create("category", NULL, array('id'=>$category_id)));
      return $this->db->Save("skills", "remove_category", array("category_id"=>$category_id));
   }
}
