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
 * Database abstraction layer for user data
 *
 */
class user_sql
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
    * @return user_sql
    */
   function user_sql(&$db) {
      $this->db =& $db;
   }

   /**
    * Get user info from login
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_user_info($params) {
      $username = $params['username'];
      $password = $params['password'];
      $sql = "SELECT u.*, uei.chat_display_name, UNIX_TIMESTAMP(u.user_last_login) AS user_last_login_timestamp ".
      	"FROM (user u, user_extended_info uei) ".
      	"WHERE u.user_id = uei.user_id ".
      	"AND u.user_login = %s ".
      	"AND u.user_password = %s ";
      return $this->db->GetRow(sprintf($sql, $this->db->qstr($username), $this->db->qstr(md5($password))));
   }

   /**
    * Get user's last login
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function update_last_login($params) {
      $username = $params['username'];
      $password = $params['password'];
      $sql = "UPDATE user SET user_last_login = NOW() WHERE user_login = %s AND user_password = %s";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($username), $this->db->qstr(md5($password))));
   }
   
   /**
    * Get user's name from ID
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_username($params) {
      $user_id = $params['user_id'];
      $sql = "SELECT user_name FROM user WHERE user_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $user_id));
   }
   
   /**
    * Get user's login from ID
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_userlogin($params) {
      $user_id = $params['user_id'];
      $sql = "SELECT user_login FROM user WHERE user_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $user_id));
   }
   
   /**
    * Get user info from user_id
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_user_info_by_id($params) {
      $user_id = $params['user_id'];
      $sql = "SELECT *, UNIX_TIMESTAMP(user_last_login) AS user_last_login_timestamp FROM user WHERE user_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $user_id));
   }
   
   /**
    * Get extended user info from user_id
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_extended_user_info_by_id($params) {
      $user_id = $params['user_id'];
      $sql = "SELECT *, UNIX_TIMESTAMP(user_last_login) AS user_last_login_timestamp FROM user LEFT JOIN user_extended_info USING (user_id) WHERE user.user_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $user_id));
   }
   
   function get_user_list($params) {
      extract($params);
      $query = "SELECT u.*, uei.chat_display_name FROM user u LEFT JOIN user_extended_info uei USING (user_id) ORDER BY u.user_name";
      return $this->db->GetAll($query);
   }
   
   function get_users_watching_event($params) {
      extract($params);
      $sql = "SELECT u.user_id FROM user u LEFT JOIN user_extended_info uei USING (user_id) WHERE uei.notification_event_mask IS NULL OR uei.notification_event_mask = 0 OR uei.notification_event_mask & %1\$d = %1\$d";
      return $this->db->GetAll(sprintf($sql, $event_type));
   }
   
   function get_online_users_watching_event($params) {
      extract($params);
      // [JAS]: IIRC, we're "opting out" of events.  Therefore to send the mask bit should NOT match, fixed query below from mask & 2 == 2, to mask & 2 == 0;
      $sql = "SELECT DISTINCT gs.user_id FROM gateway_session gs LEFT JOIN user_extended_info uei USING (user_id) WHERE uei.notification_event_mask IS NULL OR uei.notification_event_mask = 0 OR uei.notification_event_mask & %1\$d = 0";
      return $this->db->GetAll(sprintf($sql, $event_type));
   }
   
   function get_user_heartbeat_events($params) {
      extract($params);
      $sql = "SELECT heq.*, hep.payload FROM heartbeat_event_queue heq LEFT JOIN heartbeat_event_payload hep USING (event_id) WHERE user_id = '%d' ORDER BY heq.event_id";
      return $this->db->GetAll(sprintf($sql, $user_id));
   }
   
   function clear_user_heartbeat_events($params) {
      extract($params);
      $event_list = implode(",", $event_array);
      $sql = "DELETE FROM heartbeat_event_queue WHERE event_id IN (%s)";
      $this->db->Execute(sprintf($sql, $event_list));
      $sql = "DELETE FROM heartbeat_event_payload WHERE event_id IN (%s)";
      $this->db->Execute(sprintf($sql, $event_list));
      return TRUE;
   }
   
   function expire_user_heartbeat_events($params) {
      extract($params);
      $sql = "SELECT event_id FROM heartbeat_event_queue WHERE expiration != 0 AND expiration < UNIX_TIMESTAMP()";
      $event_list = $this->db->GetAll(sprintf($sql, $user_id));
      if(!is_array($event_list)) {
         return FALSE;
      }
      foreach($event_list as $event_item) {
         $events[] = $event_item['event_id'];
      }
      if(is_array($events)) {
         $event_list = implode(",", $events);
         $sql = "DELETE FROM heartbeat_event_queue WHERE event_id IN (%s)";
         $this->db->Execute(sprintf($sql, $event_list));
         $sql = "DELETE FROM heartbeat_event_payload WHERE event_id IN (%s)";
         $this->db->Execute(sprintf($sql, $event_list));
      }
      return TRUE;
   }
   
   function add_user_notification_event($params) {
      extract($params);
      if($expiration == 0) {
         $sql = "INSERT INTO heartbeat_event_queue (user_id, event_type, expiration) VALUES ('%d', '%d', '%d')";
      }
      else {
         $sql = "INSERT INTO heartbeat_event_queue (user_id, event_type, expiration) VALUES ('%d', '%d', UNIX_TIMESTAMP()+%d)";
      }
      if(!$this->db->Execute(sprintf($sql, $user_id, $event_type, $expiration))) {
         return FALSE;
      }
      else {
         $event_id = $this->db->Insert_ID();
         $sql = "INSERT INTO heartbeat_event_payload (event_id, payload) VALUES ('%d', %s)";
         return $this->db->Execute(sprintf($sql, $event_id, $this->db->qstr($payload)));
      }
   }
   
   function get_online_users_list($params) {
      extract($params);
      $query = "SELECT DISTINCT user_id FROM gateway_session WHERE user_id != 0";
      return $this->db->GetAll($query);
   }
   
   function get_assigned_ticket_count($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM ticket WHERE ticket_assigned_to_id = '%d' AND ticket_status NOT IN ('resolved', 'dead')";
      return $this->db->GetOne(sprintf($sql, $user_id));
   }
      
}