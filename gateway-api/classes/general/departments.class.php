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
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class general_departments
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_departments() {
      $this->db =& database_loader::get_instance();
   }

   function add_department($name, $usage) {
      $department_id = $this->db->Save("departments", "add_department", array("name"=>$name, "usage"=>$usage));
      if($department_id === FALSE) {
         return FALSE;
      }
      return $this->get_department($department_id);
   }

   function edit_department($department_id, $name, $usage, $offline_address) {
      if(!$this->db->Save("departments", "save_department", array("id"=>$department_id, "name"=>$name, "usage"=>$usage, "offline_address"=>$offline_address))) {
         return FALSE;
      }
      return $this->get_department($department_id);
   }

   function remove_department($department_id) {
   	if($this->db->Get("departments", "get_department_team_count", array("department_id"=>$department_id)) > 0) {
   		xml_output::error(0, "Teams still associated with this department");
   	}
      if(!$this->db->Save("departments", "remove_department", array("id"=>$department_id))) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $department =& $data->add_child("department", xml_object::create("department", NULL, array("id"=>$department_id)));
      return TRUE;
   }

   function get_department($department_id) {
      $department_data = $this->db->Get("departments", "get_info", array("id"=>$department_id));
      $teams_list = $this->db->Get("departments", "get_teams_list", array("id"=>$department_id));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $department =& $data->add_child("department", xml_object::create("department", NULL, array("id"=>$department_id)));
      $department->add_child("name", xml_object::create("name", $department_data['department_name']));
      $department->add_child("usage", xml_object::create("usage", $department_data['department_usage']));
      $department->add_child("offline_address", xml_object::create("offline_address", $department_data['department_offline_address']));
      $teams =& $department->add_child("teams", xml_object::create("teams"));
      if(is_array($teams_list)) {
         foreach($teams_list as $team_item) {
            $teams->add_child("team", xml_object::create("team", $team_item['team_name'], array("id"=>$team_item['team_id'])));
         }
      }
      return TRUE;
   }

   function assign_team($department_id, $team_id) {
      if(!$this->db->Save("departments", "assign_team", array("id"=>$department_id, "team_id"=>$team_id))) {
         return FALSE;
      }
      return $this->get_department($department_id);
   }

   function unassign_team($department_id, $team_id) {
      if(!$this->db->Save("departments", "unassign_team", array("id"=>$department_id, "team_id"=>$team_id))) {
         return FALSE;
      }
      return $this->get_department($department_id);
   }

   function get_departments_list() {
      $departments_list = $this->db->Get("departments", "get_departments_list", array());
      if(!is_array($departments_list)) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $departments =& $data->add_child("departments", xml_object::create("departments"));
      foreach($departments_list as $department_item) {
         $department =& $departments->add_child("department", xml_object::create("department", NULL, array("id"=>$department_item['department_id'])));
         $department->add_child("name", xml_object::create("name", $department_item['department_name']));
         $department->add_child("usage", xml_object::create("usage", $department_item['department_usage']));
         $teams_list = $this->db->Get("departments", "get_teams_list", array("id"=>$department_item['department_id']));
         $teams =& $department->add_child("teams", xml_object::create("teams"));
         foreach($teams_list as $team_item) {
            $team =& $teams->add_child("team", xml_object::create("team", NULL, array("id"=>$team_item['team_id'])));
            $team->add_child("name", xml_object::create("name", $team_item['team_name']));
            $members_list = $this->db->Get("teams", "get_members", array("team_id"=>$team_item['team_id']));
            $members =& $team->add_child("members", xml_object::create("members"));
            if(is_array($members_list)) {
               foreach($members_list as $member_item) {
                  $member =& $members->add_child("member", xml_object::create("member", NULL, array("id"=>$member_item['member_id'], "agent_id"=>$member_item['agent_id'], "login"=>$member_item['user_login'])));
                  $member->add_child("name", xml_object::create("name", $member_item['user_name']));
               }
            }
         }
      }
      return TRUE;
   }

   function get_agents_departments_and_teams($user_id) {
      $list = $this->db->Get("departments", "get_department_team_list_for_agent", array("user_id"=>$user_id));
      if(!is_array($list)) {
         return FALSE;
      }
      $department_names = array();
      $team_names = array();
      $mapping = array();
      foreach($list as $row) {
         $team_names[$row['team_id']] = $row['team_name'];
         $department_names[$row['department_id']] = $row['department_name'];
         $mapping[$row['department_id']][] = $row['team_id'];
         $ticket_pull[$row['team_id']] = $row['ticket_pull'];
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $departments =& $data->add_child("departments", xml_object::create("departments"));
      if(is_array($mapping)) {
         foreach($mapping as $department_id=>$team_list) {
            $department =& $departments->add_child("department", xml_object::create("department", NULL, array("id"=>$department_id)));
            $department->add_child("department_name", xml_object::create("department_name", $department_names[$department_id]));
            $teams =& $department->add_child("teams", xml_object::create("teams"));
            if(is_array($team_list)) {
               foreach($team_list as $team_id) {
                  $teams->add_child("team", xml_object::create("team", $team_names[$team_id], array("id"=>$team_id, "ticket_pull"=>$ticket_pull[$team_id])));
               }
            }
         }
      }
      return TRUE;
   }
}