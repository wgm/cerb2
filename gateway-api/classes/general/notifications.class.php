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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

class general_notifications
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_notifications() {
      $this->db =& database_loader::get_instance();
   }
   
   /**
    * Adds a notification for a specific user
    *
    * @param int $user_id user id to send to
    * @param int $type event type
    * @param string $payload XML payload packet
    * @param int $expiration number of seconds from now to expire or 0 for never
    * @return bool
    */
   function add_notification($user_id, $type, $payload, $expiration = 0) {
      return $this->db->Save("user", "add_user_notification_event", array("user_id"=>$user_id, "event_type"=>$type, "payload"=>$payload, "expiration"=>$expiration));
   }
   
   /**
    * Adds a notification for all users who aren't ignoring this event_type
    *
    * @param int $type event type
    * @param string $payload XML payload packet
    * @param int $expiration number of seconds from now to expire or 0 for never
    * @param bool $not_to_self Don't send notification to yourself (usually always true)
    * @return bool
    */
   function add_notification_all($type, $payload, $expiration = 0, $not_to_self = TRUE) {
      $user_list = $this->db->Get("user", "get_users_watching_event", array("event_type"=>$type));
      if(is_array($user_list)) {
         foreach($user_list as $user_row) {
            if($not_to_self && $user_row['user_id'] == general_users::get_user_id()) {
               continue;
            }
            $this->add_notification($user_row['user_id'], $type, $payload, $expiration);
         }
      }
      return TRUE;        
   }
   
   /**
    * Adds a notification for all online users who aren't ignoring this event_type
    *
    * @param int $type event type
    * @param string $payload XML payload packet
    * @param int $expiration number of seconds from now to expire or 0 for never
    * @param bool $not_to_self Don't send notification to yourself (usually always true)
    * @return bool
    */
   function add_notification_online($type, $payload, $expiration = 0, $not_to_self = TRUE) {
      $user_list = $this->db->Get("user", "get_online_users_watching_event", array("event_type"=>$type));
      if(is_array($user_list)) {
         foreach($user_list as $user_row) {
            if($not_to_self && $user_row['user_id'] == general_users::get_user_id()) {
               continue;
            }
            $this->add_notification($user_row['user_id'], $type, $payload, $expiration);
         }
      }
      return TRUE;
   }
   
   function &get_notifications() {
      $this->db->Get("user", "expire_user_heartbeat_events", array("user_id"=>general_users::get_user_id()));
      $events =& xml_object::create("events");
      $event_list = $this->db->Get("user", "get_user_heartbeat_events", array("user_id"=>general_users::get_user_id()));
      if(is_array($event_list)) {
         foreach($event_list as $event_item) {
            $event =& $events->add_child("event", xml_object::create("event", $event_item['payload'], array("id"=>$event_item['event_id'])));
            $event->force_non_cdata = TRUE;
            $event->add_child("event_type", xml_object::create("event_type", $event_item['event_type']));
            $purge_ids[] = $event_item['event_id'];
         }
      } 
      if(is_array($purge_ids)) {
         $this->db->Get("user", "clear_user_heartbeat_events", array("event_array"=>$purge_ids));
      }
      return $events;           
   }
   
   function notifications_xml() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $data->add_child("events", $this->get_notifications());
      return TRUE;
   }
   
   function &make_payload() {
      return xml_object::create("event_payload");
   }
}

