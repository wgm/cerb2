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

class chat_agent
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function chat_agent() {
      $this->db =& database_loader::get_instance();
   }

   function set_status($agent_id, $status) {
      if($status == AGENT_STATUS_ONLINE) {
         $notification_mask = EVENT_TYPE_CHAT_REQUEST;
         $this->db->Save("agent", "set_chat_notification_mask_off", array("user_id"=>$agent_id, "mask"=>$notification_mask));
      }
      else {
         $notification_mask = EVENT_TYPE_CHAT_REQUEST;
         $this->db->Save("agent", "set_chat_notification_mask_on", array("user_id"=>$agent_id, "mask"=>$notification_mask));
      }
      return $this->db->Save("agent", "set_status", array("user_id"=>$agent_id, "status"=>$status));
   }

   function get_agents_status() {
      if(0 < $this->db->Get("agent", "check_online_agent_status", array("status"=>AGENT_STATUS_ONLINE))) {
         return AGENT_STATUS_ONLINE;
      }
      elseif(0 < $this->db->Get("agent", "check_online_agent_status", array("status"=>AGENT_STATUS_AWAY))) {
         return AGENT_STATUS_AWAY;
      }
      else {
         return AGENT_STATUS_OFFLINE;
      }
   }

   function get_status_image($GUID) {
      $this->timeout_agent_statuses();
      switch($this->get_agents_status()) {
         case AGENT_STATUS_AWAY: {
            $file = FILESYSTEM_PATH . "visitor-api/images/status_away.jpg";
            $image_name = "status_away.jpg";
            break;
         }
         case AGENT_STATUS_ONLINE: {
            $file = FILESYSTEM_PATH . "visitor-api/images/status_online.jpg";
            $image_name = "status_online.jpg";
            break;
         }
         default:
         case AGENT_STATUS_OFFLINE: {
            $file = FILESYSTEM_PATH . "visitor-api/images/status_offline.jpg";
            $image_name = "status_offline.jpg";
            break;
         }
      }
      if(file_exists($file)) {
         header("Expires: Mon, 26 Nov 1962 00:00:00 GMT\n");
         header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT\n");
         header("Cache-control: private\n");
         header('Pragma: no-cache\n');
         header("Content-Type: image/jpg\n");
         header("Content-transfer-encoding: binary\n");
         header("Content-Length: " . filesize($file) . "\n");
         header("Content-Disposition: inline; filename=\"" . $image_name . "\"\n");
         readfile($file);
         exit();
      }
   }
   
   function timeout_agent_statuses() {
      $agents_list = '';
      $online_list = $this->db->Get("user", "get_online_users_list", array());
      if(is_array($online_list)) {
         foreach($online_list as $online_item) {
            $agents_online[] = $online_item['user_id'];
         }
         if(is_array($agents_online)) {
            $agents_list = "," . implode(",", $agents_online);
         } 
      }
      $this->db->Save("agent", "set_offline_agents_status", array("status"=>AGENT_STATUS_OFFLINE, "user_list"=>$agents_list));
      return TRUE;
   }
      
}
