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
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/html_template_parser.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/messages.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/rooms.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/agent.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/notifications.class.php");

class chat_window
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   /**
    * Template abstraction layer handle
    *
    * @var object
    */
   var $tpl;

   /**
    * HTML output abstraction layer handle
    *
    * @var object
    */
   var $html;

   function chat_window() {
      $this->db =& database_loader::get_instance();
      $this->tpl = new html_template_parser();
      $this->html =& html_output::get_instance();
      $this->notify =& new general_notifications();
   }

   function set_default_tokens($GUID) {
      $this->tpl->assign("guid", $GUID);
      $this->tpl->assign("chat_server_url", CHAT_SERVER_URL);
      $this->tpl->assign("chat_server_base_url", CHAT_SERVER_BASE_URL);
      $this->tpl->assign("remote_addr", $_SERVER['REMOTE_ADDR']);
      $this->tpl->assign("cache_kill", mktime());
   }

   function getwindow_prechat($GUID) {
      $this->set_default_tokens($GUID);
      $content = $this->tpl->parse("window_prechat.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_prechat_submit($GUID, $visitor_name, $visitor_question, $dept_id) {
      $this->set_default_tokens($GUID);
      if($this->add_chat_request($GUID, $visitor_name, $visitor_question, $dept_id)) {
         $this->tpl->assign("waiting_string", "0 sec");
         $this->tpl->assign("dept_id", $dept_id);
         $content = $this->tpl->parse("frame_pendingchat.html");
      }
      else {
         $content = $this->tpl->parse("frame_fatal_error.html");
      }
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_postchat($GUID) {
      $this->set_default_tokens($GUID);
      $content = $this->tpl->parse("frame_postchat.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_pendingchat($GUID, $dept_id) {
      $visitor_id = $this->db->Get("visitor", "get_visitor_id", array("visitor_sid"=>$GUID, "ip"=>$_SERVER['REMOTE_ADDR']));

      if($visitor_id === FALSE) {
         $this->tpl->assign("fatal_error_message", "Failed to get visitor id");
         $content = $this->tpl->parse("frame_fatal_error.html");
         $this->html->set_content($content);
         return TRUE;
      }

      $this->db->Save("visitor", "update_request_time", array("visitor_id"=>$visitor_id));
      $this->set_default_tokens($GUID);

      $request_id = $this->db->Get("visitor", "get_latest_request_from_visitor", array("visitor_id"=>$visitor_id));
      $room_id = $this->db->Get("visitor", "get_request_room_id", array("request_id"=>$request_id));

      if($room_id > 0) {
         // we got a room! let's head to it!
         $rooms_obj = new chat_rooms();
         if($rooms_obj->visitor_join_room($visitor_id, $room_id)) {

            // [JAS]: Are we sure we want to expire this already?
            $this->db->save("visitor", "expire_visitor_request", array("visitor_id"=>$visitor_id));

            // [JAS]: We should add the room/request id to the template here.
            $this->tpl->assign("room_id", $room_id);
            $this->tpl->assign("dept_id", $dept_id);
            $content = $this->tpl->parse("frame_pendingchat_redirect.html");
         }
         else {
            $this->db->save("visitor", "expire_visitor_request", array("visitor_id"=>$visitor_id));
            $this->tpl->assign("fatal_error_message", "Failed to join chat!");
            $content = $this->tpl->parse("frame_fatal_error.html");
         }
      }
      else {
         if(CHAT_REQUEST_TIMEOUT < $wait_time = $this->db->Get("visitor", "request_pending_time", array("visitor_id"=>$visitor_id))) {
            $this->db->save("visitor", "expire_visitor_request", array("visitor_id"=>$visitor_id));
            $this->tpl->assign("dept_id", $dept_id);
            $content = $this->tpl->parse("frame_pendingchat_expired.html");
         }
         else {
            $wait_hourglass = $wait_time;
            $wait_mins = floor($wait_hourglass / 60);
            $wait_hourglass -= (60 * $wait_mins);
            $wait_secs = $wait_hourglass;
            $waiting_string = sprintf("%s %s", ($wait_mins) ? $wait_mins . " min " : "", ($wait_secs) ? $wait_secs . " sec " : "");

            $this->tpl->assign("waiting_string", $waiting_string);
            $this->tpl->assign("dept_id", $dept_id);
            $content = $this->tpl->parse("frame_pendingchat.html");
         }
      }
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_choose_dept($GUID) {
      $this->set_default_tokens($GUID);

      $depts = $this->get_department_statuses_as_html();

      if(is_array($depts)) {
         $department_html = implode("", $depts);
      }
      else {
         $department_html = '';
      }
      $this->tpl->assign("department_html",$department_html);
      $content = $this->tpl->parse("frame_choose_dept.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_prechat_form($GUID, $dept_id) {
      $this->set_default_tokens($GUID);

      $dept_info = $this->db->Get("departments", "get_department_chat_status", array("dept_id" => $dept_id));
      $this->tpl->assign("dept_id", $dept_id);
      $this->tpl->assign("dept_name", $dept_info["department_name"]);

      // [JAS]: If this department is available for chats, accept one.
      //  Otherwise go to the department's e-mail form.
      if(AGENT_STATUS_ONLINE == $dept_info["department_status"]) {
         $content = $this->tpl->parse("frame_prechat_form.html");
      }
      else {
         // [JAS]: [TODO] This should add a template variable of the department ID so we can
         //	route to the appropriate e-mail address in the helpdesk, etc.
         $content = $this->tpl->parse("frame_email.html");
      }

      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_email($GUID, $dept_id) {
      $this->set_default_tokens($GUID);

      $dept_info = $this->db->Get("departments", "get_department_chat_status", array("dept_id" => $dept_id));
      $this->tpl->assign("dept_id", $dept_id);
      $this->tpl->assign("dept_name", $dept_info["department_name"]);

      $content = $this->tpl->parse("frame_email.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_blank($GUID) {
      $this->set_default_tokens($GUID);
      $content = $this->tpl->parse("frame_blank.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_branding($GUID) {
      $this->set_default_tokens($GUID);
      $content = $this->tpl->parse("frame_branding.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_sendchat($GUID, $submit_action, $message_text, $room_id) {
      $this->set_default_tokens($GUID);
      if($submit_action == "send_message") {
         $this->save_message($GUID, $message_text, $room_id);
         $this->tpl->assign("chat_content", sprintf('"<span class=\"customer_prefix\">%s:</span> %s<br>"', "Me", str_replace('"', '\"', str_replace('\\', '\\\\', $message_text))));
      }
      else {
         $this->tpl->assign("chat_content", '""');
      }
      $content = $this->tpl->parse("frame_sendchat.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_chat($GUID, $room_id) {
      $this->set_default_tokens($GUID);
      $this->tpl->assign("room_id", $room_id);
      $content = $this->tpl->parse("frame_chat.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getiframe_chatcontents($GUID) {
      $this->set_default_tokens($GUID);
      $content = $this->tpl->parse("iframe_chatcontents.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getiframe_heartbeat($GUID, $room_id) {
      // [JAS]: Update the user heartbeat time while they're chatting with us.
      // [TODO] This could be optimized by removing the requirement for the visitor_id to save the HB and use GUID.
      $visitor_id = $this->db->Get("visitor", "get_visitor_id", array("visitor_sid"=>$GUID, "ip"=>$_SERVER['REMOTE_ADDR']));
      $this->db->Save("visitor", "save_heartbeat_time", array("visitor_id"=>$visitor_id));

      $this->set_default_tokens($GUID);
      $this->tpl->assign("agent_typing_bool", 'false');
      $this->tpl->assign("visitor_pull_interval", '10000');
      $this->tpl->assign("embedded_sound", '');
      $this->tpl->assign("chat_lines", $this->get_room_messages($GUID, $room_id));
      $this->tpl->assign("room_id", $room_id);
      $content = $this->tpl->parse("iframe_heartbeat.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_line($GUID) {
      $this->set_default_tokens($GUID);
      $content = $this->tpl->parse("frame_line.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function getframe_text($GUID,$room_id) {
      $this->set_default_tokens($GUID);
      $this->tpl->assign("room_id", $room_id);
      $content = $this->tpl->parse("frame_text.html");
      $this->html->set_content($content);
      return TRUE;
   }

   function get_room_messages($GUID, $room_id) {
      $chat_lines = '';
      $msg_handler = new chat_messages();

      $messages = $msg_handler->get_visitor_room_messages($GUID, $room_id);

      foreach($messages as $message) {
         switch($message->message_code) {
            case MESSAGE_CODE_VISITOR: {
               $message_code_css = "customer_prefix";
               break;
            }
            case MESSAGE_CODE_AGENT: {
               $message_code_css = "agent_prefix";
               break;
            }
            default:
            case MESSAGE_CODE_BROADCAST: {
               $message_code_css = "broadcast_prefix";
               break;
            }
         }
         $chat_lines .= sprintf("<span class=\"%s\">%s:</span> %s<br>",
         $message_code_css,
         $message->message_prefix,
         str_replace("\n","<br>",(str_replace("\n\n","\n",str_replace("\r","\n",$message->message))))
         );
      }
      return $chat_lines;
   }

   function save_message($GUID, $message, $room_id) {
      $msg_handler = new chat_messages();
      $msg_handler->save_visitor_message($GUID, $message, $room_id);
   }

   function add_chat_request($GUID, $visitor_name, $visitor_question, $dept_id) {
      $visitor_id = $this->db->Get("visitor", "get_visitor_id", array("visitor_sid"=>$GUID, "ip"=>$_SERVER['REMOTE_ADDR']));

      if($visitor_id) {
         if(!$this->db->Save("visitor", "save_name_question", array("visitor_id"=>$visitor_id, "visitor_name"=>$visitor_name, "visitor_question"=>$visitor_question))) {
            $this->tpl->assign("fatal_error_message", "Failed to save name and question");
            return FALSE;
         }

         if(FALSE === $request_id = $this->db->Save("visitor", "save_chat_request", array("visitor_id"=>$visitor_id))) {
            $this->tpl->assign("fatal_error_message", "Failed to save chat request");
            return FALSE;
         }

         $payload =& $this->notify->make_payload();
         $request =& $payload->add_child("request", xml_object::create("request", NULL, array("id"=>$request_id)));
         $visitor =& $request->add_child("visitor", xml_object::create("visitor", NULL, array("id"=>$_SESSION['visitor_id'])));
         $visitor->add_child("visitor_name", xml_object::create("visitor_name", $visitor_name));
         $visitor->add_child("visitor_question", xml_object::create("visitor_question", $visitor_question));

         // [JAS]: Send the chat request to active members of the user's chosen department.
         $dept_users = $this->db->Get("departments","get_department_active_agents",array("dept_id"=>$dept_id));
         if(is_array($dept_users)) {
            foreach($dept_users as $user) {
               $user_id = $user["user_id"];
               $this->notify->add_notification($user_id, EVENT_TYPE_CHAT_REQUEST, $payload->to_string(), 300);
            }
         }

         return TRUE;
      }
      else {
         $this->tpl->assign("fatal_error_message", "Failed to get visitor id");
         return FALSE;
      }
   }

   function get_department_statuses_as_html() {
      // [JAS]: Grab public departments.
      if(!$depts = $this->db->Get("departments", "get_departments_chat_status_list", array())) {
         $this->tpl->assign("fatal_error_message", "Failed to load department information");
         return FALSE;
      }

      $dept_html = array();

      foreach($depts as $array_id => $dept) {
         $dept_id = $dept["department_id"];
         $dept_status = max($dept["department_status"],0);

         $dept_html[] = sprintf("<input type=\"radio\" name=\"dept\" value=\"%d\" %s>%s (<span class=\"%s\">%s</span>)<br>\r\n",
         $dept_id,
         (($array_id==0) ? "checked" : ""),
         $dept["department_name"],
         (($dept_status == 2) ? "status_online" : "status_offline"),
         (($dept_status == 2) ? "Online" : "Offline")
         );
      }

      return $dept_html;
   }

}