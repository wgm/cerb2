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
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/messages.class.php");

class chat_transcripts
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function chat_transcripts() {
      $this->db =& database_loader::get_instance();
      $this->transcripts = array();
   }

   function build_transcript_headers($range = 1, $agent_id = NULL, $search = NULL) {
      if(empty($agent_id) && empty($search)) {
         $headers = $this->db->Get("transcripts", "get_headers", array("range"=>$range));
      }
      elseif(empty($agent_id)) {
         $headers = $this->db->Get("transcripts", "get_headers_by_search", array("range"=>$range, "search"=>$search));
      }
      elseif(empty($search)) {
         $headers = $this->db->Get("transcripts", "get_headers_by_agent", array("range"=>$range, "agent_id"=>$agent_id));
      }
      else {
         $headers = $this->db->Get("transcripts", "get_headers_by_search_agent", array("range"=>$range, "search"=>$search, "agent_id"=>$agent_id));
      }
      if(is_array($headers)) {
         while(NULL !== $row = array_shift($headers)) {
            $trans = new chat_transcript_container();
            $trans->transcript_id = $row["transcript_id"];
            $trans->transcript_date = $row["transcript_date"];
            $trans->room_name = $row['room_name'];
            $trans->room_id = $row["room_id"];
            $date_str = date("D (M d Y)",$trans->transcript_date);
            if(!isset($this->transcripts[$date_str])) {
					$this->transcripts[$date_str] = array();
				}
				$this->transcripts[$date_str][$trans->transcript_id] = $trans;
         }
      }
   }
   
   function get_transcript_xml($transcript_id, &$container) {
      $transcript_info = $this->db->Get("transcripts", "get_transcript_info", array("transcript_id"=>$transcript_id));
      $message_handler = new chat_messages();
      
      $transcript =& $container->add_child("transcript", xml_object::create("cer_transcript", NULL, array("id"=>$transcript_info['transcript_id'])));
      $transcript->add_child("room", xml_object::create("room", $transcript_info['room_name'], array("id"=>$transcript_info['room_id'])));
      $transcript->add_child("date", xml_object::create("date", date(XML_DATE_FORMAT, $transcript_info['transcript_date']), array("timestamp"=>$transcript_info['transcript_date'])));
      $messages =& $transcript->add_child("messages", xml_object::create("messages"));
      $message_handler->get_room_messages_xml($transcript_info['room_id'], $messages, 0, FALSE);
      return TRUE;
   }   
   
   function get_transcript_headers($range = 1, $agent_id = NULL, $search = NULL) {
      $this->build_transcript_headers($range, $agent_id, $search);
      return $this->transcripts;
   }
}

class chat_transcript_container
{
   var $transcript_id = null;
   var $transcript_date = null;
   var $room_id = null;
   var $room_name = null;
   var $transcript_messages = array();
}
