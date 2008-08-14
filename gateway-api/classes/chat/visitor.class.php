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
|		Jeff Standen		(jeff@webgroupmedia.com)	 [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/html_template_parser.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/rooms.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/messages.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/agent.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/notifications.class.php");

class chat_visitor
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function chat_visitor() {
      $this->db =& database_loader::get_instance();
      $this->notify =& new general_notifications();
   }
   
   function heartbeat($heartbeat, $visitor_sid, $location, $referrer) {
      $this->db->Save("visitor", "expire_sessions", array("timeout"=>SESSION_EXPIRE_SECS));
      
      if(FALSE === $visitor_id = $this->db->Get("visitor", "get_visitor_id", array("visitor_sid"=>$visitor_sid, "ip"=>$_SERVER['REMOTE_ADDR']))) {
         $query_info = array("sid"=>$visitor_sid, "browser"=>$_SERVER['HTTP_USER_AGENT'],  "ip"=>$_SERVER['REMOTE_ADDR'], "host"=>gethostbyaddr($_SERVER['REMOTE_ADDR']));
         $visitor_id = $this->db->Save("visitor", "create_visitor", $query_info);
      }
      $_SESSION['visitor_id'] = $visitor_id;
  
      $query_info = array("location"=>$location, "referrer"=>$referrer, "visitor_id"=>$_SESSION['visitor_id'], "referrer_host"=>$this->get_hostname_from_url($referrer));
      if($heartbeat == 0) {
         $this->db->Save("visitor", "add_page_hit", $query_info);
      }
      $this->db->Save("visitor", "save_heartbeat_time", array("visitor_id"=>$_SESSION['visitor_id']));
      return $this->check_invite_request();
   }
   
   function get_invite_msg() {
      $invite = $this->db->Get("visitor", "get_invite", array("visitor_id"=>$_SESSION['visitor_id']));
      $html =& html_output::get_instance();
      $invite_msg = nl2br($invite['invite_message']);
      $agent_name = $invite['chat_display_name'];
      $template_parser = new html_template_parser();
      $template_parser->assign("agent_name", $agent_name);
      $template_parser->assign("invite_message", $invite_msg);
      $content = $template_parser->parse("iframe_invite.html");
      $html->set_content($content);
   }
   
   function send_invite_image($invite) {
      $file = sprintf(FILESYSTEM_PATH . "visitor-api/images/bit_%d.gif",$invite);
      if(file_exists($file)) {
			$fp = fopen($file,"rb");
			$fstat = fstat($fp);
			
			header("Expires: Mon, 26 Nov 1962 00:00:00 GMT\n");
			header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT\n");
			header("Cache-control: private\n");
			header('Pragma: no-cache\n');
			header("Content-Type: image/gif\n");
			header("Content-transfer-encoding: binary\n"); 
			header("Content-Length: " . $fstat["size"] . "\n");
			header("Content-Disposition: inline; filename=\"bit_" . $bit . ".gif\"\n");
			
			fpassthru($fp);
			fclose($fp);
			exit();
      }
   }   
   
   function check_invite_request() {
      $invite_exists = $this->db->Get("visitor", "check_has_invites", array("visitor_id"=>$_SESSION['visitor_id']));
      if($invite_exists > 0) {
         return 2;
      }
      else {
         return 1;
      }
   }
   
   // [JAS]: Return just the hostname from a URL, compensate for different types
	//	of URI protocols.
	function get_hostname_from_url($url) {
		// If we're not formatted like a URL (file:/// http:// https:// ftp://) return false.
		$url = str_replace(":///","://",$url);
		
		$prot_pos = strpos($url,"://");
		if($prot_pos === false) {
			return false;
		}
		
		$prot_pos += 3; // Add the length of :// to the position
		
		$url = substr($url, $prot_pos); // Remove the protocol
		
		$url_parse = split("/", $url); // Split on paths
		
		$host = str_replace("www.","", $url_parse[0]); // strip "www." from hostname
		
		// [JAS]: If someone happened to add a period after the domain name
		// but before the / then strip it off.  Sure it sounds silly, but it's
		// in our referrer log over 30 times from 3-4 days. ;-)
		if(substr($host,-1,1) == ".") {
			$host = substr($host,0,strlen($host)-1);
		}
		
		if(!empty($host)) 
			return $host;
		else
			return false;
	}
	
	function accept_invite() {
	   $agent_id = $this->db->Get("visitor", "get_agent_from_invite", array("visitor_id"=>$_SESSION['visitor_id']));
	   $payload =& $this->notify->make_payload();
	   $payload->add_child("visitor", xml_object::create("visitor", NULL, array("id"=>$_SESSION['visitor_id'])));
	   $this->notify->add_notification($agent_id, EVENT_TYPE_CHAT_INVITE_ACCEPTED, $payload->to_string(), 300);
	   return $this->db->Save("visitor", "remove_invites", array("visitor_id"=>$_SESSION['visitor_id']));
	}
	
	function reject_invite() {
	   $agent_id = $this->db->Get("visitor", "get_agent_from_invite", array("visitor_id"=>$_SESSION['visitor_id']));
	   $payload =& $this->notify->make_payload();
	   $payload->add_child("visitor", xml_object::create("visitor", NULL, array("id"=>$_SESSION['visitor_id'])));
	   $this->notify->add_notification($agent_id, EVENT_TYPE_CHAT_INVITE_ACCEPTED, $payload->to_string(), 300);
	   return $this->db->Save("visitor", "remove_invites", array("visitor_id"=>$_SESSION['visitor_id']));
	}
	
	function get_visitor_list_as_xml() {
	   $visitors_data = $this->db->Get("visitor", "get_list", array("session_expire_secs"=>SESSION_EXPIRE_SECS));
	   $xml =& xml_output::get_instance();
	   $data =& $xml->get_child("data", 0);
	   $visitors_xml =& $data->add_child("visitors", xml_object::create("visitors"));
	   
	   if(is_array($visitors_data))
	   while(NULL !== $visitor = array_shift($visitors_data)) {	      
	      $visitor_id = $visitor['visitor_id'];
	      $current_page = $this->db->Get("visitor", "get_current_page", array("visitor_id"=>$visitor_id));
	      $referrer = $this->db->Get("visitor", "get_referrer", array("visitor_id"=>$visitor_id));
	      $page_count = $this->db->Get("visitor", "get_page_count", array("visitor_id"=>$visitor_id));
	      $status = $this->get_visitor_status($visitor_id);
	      
	      $visitor_name = (empty($visitor['visitor_name'])) ? $visitor['visitor_host'] : $visitor['visitor_name'];
	      
	      $visitor_xml =& $visitors_xml->add_child("visitor", xml_object::create("visitor", NULL, array("id"=>$visitor['visitor_id'])));
	      $visitor_xml->add_child("status", xml_object::create("status", $status));
	      $visitor_xml->add_child("host", xml_object::create("host", $visitor_name));
	      $visitor_xml->add_child("location", xml_object::create("location", $current_page));
	      $visitor_xml->add_child("referrer", xml_object::create("referrer", $referrer));
	      $visitor_xml->add_child("pages", xml_object::create("pages", $page_count));
	      $visitor_xml->add_child("time", xml_object::create("time", $visitor['duration']));
	   }	      	      
	}
	
	function get_visitor_status($visitor_id) {
	   if(0 < $this->db->Get("visitor", "num_chat_requests_visitor", array("visitor_id"=>$visitor_id))) {
	      return VISITOR_STATUS_CHAT_REQUEST;
	   }
	   elseif(0 < $this->db->Get("visitor", "num_chat_rooms_visitor", array("visitor_id"=>$visitor_id))) {
	      return VISITOR_STATUS_CHATTING;
	   }
	   elseif(0 < $this->db->Get("visitor", "num_chat_invites_visitor", array("visitor_id"=>$visitor_id))) {
	      return VISITOR_STATUS_INVITED;
	   }
	   else {
	      return VISITOR_STATUS_BROWSING;
	   }	   
	}   
	
	function get_visitor_info_as_xml($visitor_id) {
	   $xml =& xml_output::get_instance();
	   $data =& $xml->get_child("data", 0);
	   $data->add_child("visitor_id", xml_object::create("visitor_id", $visitor_id));
	   $info_xml =& $data->add_child("visitor_info", xml_object::create("visitor_info"));
	   $info_data =& $this->db->Get("visitor", "get_visitor_info", array("visitor_id"=>$visitor_id));
	   $current_page = $this->db->Get("visitor", "get_current_page", array("visitor_id"=>$visitor_id));
	   $referrer = $this->db->Get("visitor", "get_referrer", array("visitor_id"=>$visitor_id));
	   
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "IP:"));
	     $row->add_child("value", xml_object::create("value", $info_data['visitor_ip']));
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Host:"));
	     $row->add_child("value", xml_object::create("value", $info_data['visitor_host']));
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Visitor Name:"));
	     $row->add_child("value", xml_object::create("value", $info_data['visitor_name']));
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Browser:"));
	     $row->add_child("value", xml_object::create("value", $info_data['visitor_browser']));
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Current Page:"));
	     $row->add_child("value", xml_object::create("value", $current_page));
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Referrer:"));
	     $row->add_child("value", xml_object::create("value", $referrer));	
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Entered Site:"));
	     $row->add_child("value", xml_object::create("value", date(XML_DATE_FORMAT, $info_data['visitor_time_start']), array("timestamp"=>$info_data['visitor_time_start'])));	
	   $row =& $info_xml->add_child("row", xml_object::create("row"));
	     $row->add_child("info", xml_object::create("info", "Last Ping:"));
	     $row->add_child("value", xml_object::create("value", date(XML_DATE_FORMAT, $info_data['visitor_time_latest']), array("timestamp"=>$info_data['visitor_time_latest'])));	   
	     
	   $pages_xml =& $data->add_child("pages", xml_object::create("pages"));
	   $pages_data = $this->db->Get("visitor", "get_pages", array("visitor_id"=>$visitor_id));
	   while(NULL !== $page = array_shift($pages_data)) {
	      $page_xml =& $pages_xml->add_child("page", xml_object::create("page"));
	      $page_xml->add_child("viewed", xml_object::create("viewed", $page['page_name']));
	      $page_xml->add_child("date", xml_object::create("date", date(XML_DATE_FORMAT, $page['page_timestamp']), array("timestamp"=>$page['page_timestamp'])));
	      $page_xml->add_child("referrer", xml_object::create("referrer", $page['page_referrer']));
	   }
	}
	
	function send_invite($agent_id, $visitor_id, $message) {
	   return $this->db->Save("visitor", "save_invite", array("agent_id"=>$agent_id, "visitor_id"=>$visitor_id, "message"=>$message));
	}
	
	function check_chat_requests() {
	   $user_id = general_users::get_user_id();
	   $agent_status = $this->db->Get("agent", "get_status", array("user_id"=>$user_id));
	   if($agent_status == AGENT_STATUS_ONLINE) {
	      return $this->get_chat_requests_xml();
	   }
	   else {
	      $xml =& xml_output::get_instance();
	      $data =& $xml->get_child("data", 0);
	      $requests =& $data->add_child("requests", xml_object::create("requests"));
	      return TRUE;
	   }
	}
	
	function get_chat_requests_xml() {
	   $requests_info = $this->db->Get("visitor", "get_chat_requests", array());
	   $xml =& xml_output::get_instance();
	   $data =& $xml->get_child("data", 0);
	   $requests =& $data->add_child("requests", xml_object::create("requests"));
	   while(NULL !== $request_info = array_shift($requests_info)) {
	      $request =& $requests->add_child("request", xml_object::create("request", NULL, array("id"=>$request_info['chat_request_id'])));
	      $request->add_child("visitor", xml_object::create("visitor", $request_info['visitor_name'], array("id"=>$request_info['visitor_id'], "host"=>$request_info['visitor_host'])));
         $request->add_child("question", xml_object::create("question", $request_info['visitor_question']));
         $request->add_child("date", xml_object::create("date", date(XML_DATE_FORMAT, $request_info['request_time_start']), array("timestamp"=>$request_info['request_time_start'])));
         $request->add_child("wait_time", xml_object::create("wait_time", $request_info['request_time_waiting']));
	   }
	   return TRUE;
	}
	
	// [JAS]: Added visitor + request info to the return packet.
	function accept_chat_request($request_id) {
	   $visitor_id = $this->db->get("visitor", "get_visitor_id_from_request", array("request_id"=>$request_id));
	   $visitor_name = $this->db->Get("visitor", "get_visitor_name", array("visitor_id"=>$visitor_id));

	   $payload =& $this->notify->make_payload();
	   $payload->add_child("agent", xml_object::create("agent", general_users::get_user_name(), array("id"=>general_users::get_user_id(), "login"=>general_users::get_user_login())));
	   $payload->add_child("date", xml_object::create("date", date(XML_DATE_FORMAT), array("timestamp"=>time())));
	   $payload->add_child("request_id", xml_object::create("request_id", $request_id, array()));
	   $payload->add_child("visitor", xml_object::create("visitor", $visitor_name, array("id"=>$visitor_id)));
	   $this->notify->add_notification_online(EVENT_TYPE_CHAT_REQUEST_HANDLED, $payload->to_string(), 300);
	   
	   // [TODO] Alternatively this can loop through any other open rooms by this visitor and leave them
	   // using the API.
	   $this->db->Get("visitor", "clear_other_visitor_requests", array("request_id"=>$request_id,"visitor_id"=>$visitor_id));
	   
	   $room_handler = new chat_rooms();
	   $room_id =& $room_handler->create_room(sprintf("Visitor Chat: %s", $visitor_name), "visitor");
	   if($room_id > 0) {
	      $this->db->Get("visitor", "assign_room_to_request", array("request_id"=>$request_id, "visitor_id"=>$visitor_id, "room_id"=>$room_id));
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $confirmation =& $data->add_child("confirmation", xml_object::create("confirmation"));
         $confirmation->add_child("confirm", xml_object::create("confirm", "Room Created & Visitor Request Updated!"));
         $room =& $data->add_child("room", xml_object::create("room", NULL, array("id"=>$room_id)));
         $room->add_child("room_name", xml_object::create("room_name", sprintf("Visitor Chat: %s", $visitor_name)));
         $room->add_child("room_type", xml_object::create("room_type", "visitor"));
         return TRUE;
      }
	   else {
	      return FALSE;
	   }
	}    

	function reject_chat_request($request_id) {
	   // this immediately sends the user to send an email
	   return $this->db->Save("visitor", "reset_request_start", array("request_id"=>$request_id, "chat_timeout"=>CHAT_REQUEST_TIMEOUT));
	}
	
	function end_visitor_chat($GUID, $room_id) {
      $visitor = $this->db->Get("messages", "get_visitor_time", array("visitor_sid"=>$GUID, "room_id"=>$room_id));

      $room_handler = new chat_rooms();
      $room_handler->visitor_leave_room($visitor['visitor_id'], $visitor['room_id'], $visitor['visitor_name']);
      return TRUE;
	}	      
}