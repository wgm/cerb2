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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/notifications.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/agent.class.php");

class general_users
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_users() {
      $this->db =& database_loader::get_instance();
      $this->notify =& new general_notifications();
   }

   function login($username, $password) {
      $user_data = $this->db->Get("user", "get_user_info", array("username"=>$username, "password"=>$password));
      if(!$user_data || !is_array($user_data) || count($user_data) < 1) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $user =& $data->add_child("user", xml_object::create("user", NULL, array("id"=>$user_data['user_id'])));
      $user->add_child("user_email", xml_object::create("user_email", $user_data['user_email']));
      $user->add_child("user_last_login", xml_object::create("user_last_login", $user_data['user_last_login']));
      $user->add_child("user_display_name", xml_object::create("user_display_name", $user_data['chat_display_name']));
      $user->add_child("user_name", xml_object::create("user_name", $user_data['user_name']));
      $_SESSION["user_data"] = $user_data;
      session_write_close();
      $session_handler =& gateway_session::get_instance();
      $session_handler->update_login($user_data['user_id']);
      $payload =& $this->notify->make_payload();
      $user =& $payload->add_child("user", xml_object::create("user", NULL, array("id"=>$user_data['user_id'])));
      $user->add_child("user_email", xml_object::create("user_email", $user_data['user_email']));
      $user->add_child("user_name", xml_object::create("user_name", $user_data['user_name']));
      $this->notify->add_notification_online(EVENT_TYPE_USER_LOGIN, $payload->to_string(), 300);
      return $this->db->Save("user", "update_last_login", array("username"=>$username, "password"=>$password));
   }

   function logout() {
      $user_data = $_SESSION["user_data"];
      $user_id = $user_data['user_id'];
      $payload =& $this->notify->make_payload();
      $user =& $payload->add_child("user", xml_object::create("user", NULL, array("id"=>$user_id)));
      $user->add_child("user_email", xml_object::create("user_email", $user_data['user_email']));
      $user->add_child("user_name", xml_object::create("user_name", $user_data['user_name']));
      $this->notify->add_notification_online(EVENT_TYPE_USER_LOGOFF, $payload->to_string(), 300);
      session_unset();
      
      // [JAS]: Force the extended info to log out.
      $chat_agent =& new chat_agent();
      $chat_agent->set_status($user_id, AGENT_STATUS_OFFLINE);
      
      $_SESSION = array();
      if (isset($_COOKIE[session_name()])) {
         setcookie(session_name(), '', time()-86400, '/');
      }
      return session_destroy();
   }
   
   function check_login() {      
      if(!isset($_SESSION['user_data']['user_id']) || $_SESSION['user_data']['user_id'] < 1) {
         return FALSE;
      }
      if(!isset($_SESSION['user_data']['user_last_login_timestamp']) || $_SESSION['user_data']['user_last_login_timestamp'] < (time() - 86400)) {
         return FALSE;
      }
      
      // Hack to support older Cerberus code
      $GLOBALS['session'] = new generic_class();
      $GLOBALS['session']->vars["login_handler"] = new generic_class();
      $GLOBALS['session']->vars["login_handler"]->user_access = new generic_class();

      $GLOBALS['session']->vars["login_handler"]->user_id = $_SESSION['user_data']['user_id'];
		$GLOBALS['session']->vars["login_handler"]->user_superuser = $_SESSION['user_data']['user_superuser'];
		$GLOBALS['session']->vars["login_handler"]->user_access->group_id = $_SESSION['user_data']['user_group_id'];
      return TRUE;
   }
   
   function get_user_id() {
      return $_SESSION['user_data']['user_id'];
   }
   
   function get_user_name() {
      return $_SESSION['user_data']['user_name'];
   }
   
   function get_user_login() {
      return $_SESSION['user_data']['user_login'];
   }

}

if(!class_exists('generic_class')) {
   class generic_class
   {
      // empty generic class
   }
}
