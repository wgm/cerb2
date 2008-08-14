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

class general_teams
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_teams() {
      $this->db =& database_loader::get_instance();
   }

   function add_team($team_name, $team_acl1, $team_acl2, $team_acl3, $queues, $department_id) {
      if(FALSE !== $team_id = $this->db->Save("teams", "add_team", array("name"=>$team_name, "acl1"=>$team_acl1, "acl2"=>$team_acl2, "acl3"=>$team_acl3))) {
         $this->save_queues($team_id, $queues);
         $this->db->Save("departments", "assign_team", array("id"=>$department_id, "team_id"=>$team_id));
         return $this->get_team($team_id, $department_id);
      }
      else {
         return FALSE;
      }
   }

   function assign_member($team_id, $agents) {
      $success = FALSE;
      foreach($agents as $agent) {
         if(FALSE !== $this->db->Save("teams", "add_member", array("team_id"=>$team_id, "agent_id"=>$agent['id'], "options"=>$agent['options']))) {
            $this->get_member($team_id, $agent['id']);
            $success = TRUE;
         }
      }
      return $success;
   }

   function get_team($team_id, $department_id = NULL) {
      if(FALSE !== $team_info = $this->db->Get("teams", "get_team_info", array("team_id"=>$team_id))) {
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $team =& $data->add_child("team", xml_object::create("team", NULL, array("id"=>$team_id)));
         if(!is_null($department_id) && is_numeric($department_id)) {
            $team->add_child("department_id", xml_object::create("department_id", $department_id));
         }
         $team->add_child("team_name", xml_object::create("team_name", $team_info[0]['team_name']));
         $team->add_child("team_acl1", xml_object::create("team_acl1", $team_info[0]['team_acl1']));
         $team->add_child("team_acl2", xml_object::create("team_acl2", $team_info[0]['team_acl2']));
         $team->add_child("team_acl3", xml_object::create("team_acl3", $team_info[0]['team_acl3']));
         $queues =& $team->add_child("queues", xml_object::create("queues"));
         foreach($team_info as $row) {
            if($row['queue_id'] > 0) {
               $queues->add_child("queue", xml_object::create("queue", $row['queue_name'], array('id'=>$row['queue_id'], 'access'=>$row['queue_access'])));
            }
         }
         $members_list = $this->db->Get("teams", "get_members", array("team_id"=>$team_id));
         $members =& $team->add_child("members", xml_object::create("members"));
         if(is_array($members_list)) {
            foreach($members_list as $member_item) {
               $member =& $members->add_child("member", xml_object::create("member", NULL, array("id"=>$member_item["member_id"])));
               $member->add_child("team_id", xml_object::create("team_id", $team_id));
               $member->add_child("agent_id", xml_object::create("agent_id", $member_item['agent_id']));
               $member->add_child("agent_name", xml_object::create("agent_name", $member_item['user_name']));
               $member->add_child("agent_login", xml_object::create("agent_login", $member_item['user_login']));
            }
         }
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function get_member($team_id, $agent_id) {
      if(FALSE !== $user_info = $this->db->Get("teams", "get_member_info", array("team_id"=>$team_id, "agent_id"=>$agent_id))) {
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $member =& $data->add_child("member", xml_object::create("member"));
         $member->add_child("team_id", xml_object::create("team_id", $team_id));
         $member->add_child("agent_id", xml_object::create("agent_id", $agent_id));
         $member->add_child("agent_name", xml_object::create("agent_name", $user_info['user_name']));
         $member->add_child("agent_login", xml_object::create("agent_login", $user_info['user_login']));
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function unassign_member($team_id, $agents) {
      $success = FALSE;
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $members =& $data->add_child("members", xml_object::create("members"));
      foreach($agents as $agent_id) {
         if(FALSE !== $this->db->Save("teams", "remove_member", array("team_id"=>$team_id, "member_id"=>$agent_id))) {
            $member =& $members->add_child("member", xml_object::create("member"));
            $member->add_child("team_id", xml_object::create("team_id", $team_id));
            $member->add_child("agent_id", xml_object::create("agent_id", $agent_id));
            $success = TRUE;
         }
      }
      return $success;
   }

   function edit_team($team_id, $team_name, $acl1, $acl2, $acl3, $queues) {
      if(FALSE !== $this->db->Save("teams", "update_team", array("team_id"=>$team_id, "name"=>$team_name, "acl1"=>$acl1, "acl2"=>$acl2, "acl3"=>$acl3))) {
         $this->save_queues($team_id, $queues);
         return $this->get_team($team_id);
      }
      else {
         return FALSE;
      }
   }

   function edit_member($team_id, $agent_id) {
      if(FALSE !== $this->db->Save("teams", "update_member", array("team_id"=>$team_id, "agent_id"=>$agent_id))) {
         return $this->get_member($team_id, $agent_id);
      }
      else {
         return FALSE;
      }
   }

   function delete_team($team_id) {
      if(FALSE !== $this->db->Save("teams", "remove_team", array("team_id"=>$team_id))) {
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $team =& $data->add_child("team", xml_object::create("team", NULL, array("id"=>$team_id)));
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function save_queues($team_id, $queues) {
      $this->db->save("teams", "delete_queues", array("team_id"=>$team_id));
      if(!is_array($queues)) {
         return;
      }
      foreach($queues as $queue_id=>$queue_access) {
         $this->db->Save("teams", "save_queue", array("team_id"=>$team_id, "queue_id"=>$queue_id, "queue_access"=>$queue_access));
      }
   }
}