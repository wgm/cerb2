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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/transcripts.class.php");

class general_system
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_system() {
      $this->db =& database_loader::get_instance();
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $this->system =& $data->add_child("system", xml_object::create("system"));
   }

   function get_agent_list() {
      $users_list = $this->db->Get("user", "get_user_list", array());
      if(!is_array($users_list)) {
         return FALSE;
      }
      $agents =& $this->system->add_child("agents", xml_object::create("agents"));
      $agent =& $agents->add_child("agent", xml_object::create("agent", NULL, array("id"=>0)));
      $agent->add_child("login", xml_object::create("login", 'Any Agent Login'));
      $agent->add_child("name", xml_object::create("name", 'Any Agent'));
      $agent->add_child("email", xml_object::create("email", 'Any Agent Email'));
      $agent->add_child("chat_display_name", xml_object::create("chat_display_name", 'Any Agent'));
      foreach($users_list as $user_row) {
         $agent =& $agents->add_child("agent", xml_object::create("agent", NULL, array("id"=>$user_row['user_id'])));
         $agent->add_child("login", xml_object::create("login", $user_row['user_login']));
         $agent->add_child("name", xml_object::create("name", $user_row['user_name']));
         $agent->add_child("email", xml_object::create("email", $user_row['user_email']));
         $agent->add_child("chat_display_name", xml_object::create("chat_display_name", $user_row['chat_display_name']));
      }
      return TRUE;
   }

   function get_whos_online_list() {
      $users_list = $this->db->Get("agent", "get_online_agents", array());
      if(!is_array($users_list)) {
         return FALSE;
      }

      $agents =& $this->system->add_child("agents", xml_object::create("agents"));

      foreach($users_list as $user_row) {
         $agent =& $agents->add_child("agent", xml_object::create("agent", NULL, array("id"=>$user_row['user_id'])));
         $agent->add_child("login", xml_object::create("login", $user_row['user_login']));
         $agent->add_child("name", xml_object::create("name", $user_row['user_name']));
         $agent->add_child("chat_status", xml_object::create("chat_status", $user_row['chat_status']));
         $agent->add_child("ip_address", xml_object::create("ip_address", long2ip($user_row['ip_address'])));
         $agent->add_child("login_timestamp", xml_object::create("login_timestamp", $user_row['login_timestamp']));
         $agent->add_child("last_timestamp", xml_object::create("last_timestamp", $user_row['last_timestamp']));
         $agent->add_child("requests", xml_object::create("requests", $user_row['requests']));
      }
      return TRUE;
   }

   function get_ticket_status_list() {
      $ticket_statuses = $this->db->Get("search", "build_ticket_status_list", array());
      $statuses =& $this->system->add_child("statuses", xml_object::create("statuses"));
      if(is_array($ticket_statuses)) {
         foreach($ticket_statuses as $status_name) {
            $statuses->add_child("status", xml_object::create("status", $status_name));
         }
      }
      return TRUE;
   }

//   function get_queue_list() {
//      $queues_db = $this->db->Get("queues", "get_accessable_queues", array("user_id"=>general_users::get_user_id()));
//      $queues =& $this->system->add_child("queues", xml_object::create("queues"));
//      if(is_array($queues_db)) {
//         foreach($queues_db as $queues_row) {
//            $queues->add_child("queue", xml_object::create("queue", $queues_row["queue_name"], array("id"=>$queues_row["queue_id"])));
//         }
//      }
//      return TRUE;
//   }
   
   function get_queue_list() {
      $queues_array = array();
      $queue_list = $this->db->get("queues", "get_full_queue_list_with_access", array("user_id"=>general_users::get_user_id()));
      if(!is_array($queue_list)) {
         return FALSE;
      }
      foreach($queue_list as $item) {
         $queues_array[$item['queue_id']]['name'] = $item['queue_name'];
         $queues_array[$item['queue_id']]['prefix'] = $item['queue_prefix'];
         $queues_array[$item['queue_id']]['display_name'] = $item['queue_email_display_name'];
         $queues_array[$item['queue_id']]['mode'] = $item['queue_mode'];
         $queues_array[$item['queue_id']]['access'] = $item['queue_access'];
         $queues_array[$item['queue_id']]['addresses'][] = array('address_id'=>$item['queue_addresses_id'], 'address'=>$item['queue_address'].'@'.$item['queue_domain']);
      }
      $queues =& $this->system->add_child("queues", xml_object::create("queues"));
      foreach($queues_array as $queue_id=>$info) {
         $queue =& $queues->add_child("queue", xml_object::create("queue", NULL, array("id"=>$queue_id, "mode"=>$info['mode'], "access"=>$info['access'])));
         $queue->add_child("name", xml_object::create("name", $info['name']));
         $queue->add_child("display_name", xml_object::create("display_name", $info['display_name']));
         $addresses =& $queue->add_child("addresses", xml_object::create("addresses"));
         foreach($info['addresses'] as $addr_info) {
            $addresses->add_child("address", xml_object::create("address", $addr_info['address'], array('id'=>$addr_info['address_id'])));
         }
      }
      return TRUE;         
   }

}