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
 * Database abstraction layer for agent data
 *
 */
class agent_sql
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
    * @return agent_sql
    */
   function agent_sql(&$db) {
      $this->db =& $db;
   }
   

   function set_status($params) {
      extract($params);
      $sql = "UPDATE gateway_session SET chat_status = '%d' WHERE user_id = '%d'";
      return $this->db->Execute(sprintf($sql, $status, $user_id));
   }
   
   function check_online_agent_status($params) {
      extract($params);
      $sql = "SELECT COUNT(user_id) FROM gateway_session WHERE chat_status = '%d'";
      return $this->db->GetOne(sprintf($sql, $status));
   } 

   function get_online_agents() {
   	$sql = "SELECT ses.user_id, u.user_name, u.user_login, ses.chat_status, ses.ip_address, ses.requests, ses.login_timestamp, ses.last_timestamp FROM gateway_session ses, user u WHERE ses.user_id = u.user_id ORDER BY u.user_name";
   	return $this->db->GetAll($sql);
   }
   
   function get_status($params) {
      extract($params);
      $sql = "SELECT chat_status FROM gateway_session WHERE user_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $user_id));
   } 
   
   function set_chat_notification_mask_on($params) {
      extract($params);
      $sql = "UPDATE user_extended_info SET notification_event_mask = notification_event_mask + %2\$d WHERE user_id = %1\$d AND notification_event_mask & %2\$d = 0";
      return $this->db->Execute(sprintf($sql, $user_id, $mask));
   }
   
   function set_chat_notification_mask_off($params) {
      extract($params);
      $sql = "UPDATE user_extended_info SET notification_event_mask = notification_event_mask - %2\$d WHERE user_id = %1\$d AND notification_event_mask & %2\$d = %2\$d";
      return $this->db->Execute(sprintf($sql, $user_id, $mask));
   }
   
   function set_offline_agents_status($params) {
      extract($params);
      $sql = "UPDATE gateway_session SET chat_status = '%d' WHERE user_id NOT IN (0%s)";
      return $this->db->Execute(sprintf($sql, $status, $user_list));
   }
}