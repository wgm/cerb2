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
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/thread_content.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class email_views
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function email_views() {
      $this->db =& database_loader::get_instance();
   }

   function get_latest_tickets($timestamp, $limit) {
      $ticket_data = $this->db->Get("ticket", "get_latest", array("timestamp"=>$timestamp, "limit"=>$limit));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      foreach($ticket_data as $ticket) {
         $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'], "timestamp"=>$ticket['ticket_last_update'])));
      }
   }

   function get_assigned_tickets($timestamp, $user_id) {
      $ticket_data = $this->db->Get("ticket", "get_assigned", array("timestamp"=>$timestamp, "user_id"=>$user_id));
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      foreach($ticket_data as $ticket) {
         $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'], "timestamp"=>$ticket['ticket_last_update'])));
      }
   }

   function _ticket_order_sorter($a, $b) {
      $position_a = array_search($a["ticket_id"], $this->ticket_order_array);
      $position_b = array_search($b["ticket_id"], $this->ticket_order_array);
      if($position_a == $position_b) {
         return 0;
      }
      return ($position_a < $position_b) ? -1 : 1;
   }

   function get_headers($tickets_arr) {
      $this->ticket_order_array = $tickets_arr;
      $tickets = implode("','", $tickets_arr);
      $ticket_list = $this->db->Get("ticket", "get_headers", array("tickets"=>$tickets));
      if($ticket_list === FALSE || !is_array($ticket_list)) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $headers =& $data->add_child("headers", xml_object::create("headers"));
      usort($ticket_list, array(&$this, "_ticket_order_sorter"));
      foreach($ticket_list as $ticket_item) {
         $ticket_item['requester_list'] = $this->db->get("ticket", "requester_list", array("ticket_id"=>$ticket_item['ticket_id']));
         $ticket_item['watcher_list'] = $this->db->Get("ticket", "watcher_list", array("queue_id"=>$ticket_item['ticket_id']));
         $ticket_item['contact_list'] = $this->db->Get("ticket", "get_primary_contact", array("ticket_id"=>$ticket_item['ticket_id']));
         $ticket =& $this->gen_xml($headers, $ticket_item, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE);
      }
      return TRUE;
   }

   function get_listeners($tickets_arr) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $headers =& $data->add_child("headers", xml_object::create("headers"));
      foreach($tickets_arr as $ticket_id) {
         $ticket_item = array();
         $ticket_item['ticket_id'] = $ticket_id;
         $ticket_item['requester_list'] = $this->db->get("ticket", "requester_list", array("ticket_id"=>$ticket_item['ticket_id']));
         $ticket_item['watcher_list'] = $this->db->Get("ticket", "watcher_list", array("queue_id"=>$ticket_item['ticket_id']));
         $this->gen_xml($headers, $ticket_item, FALSE, FALSE, TRUE, TRUE);
      }
      return TRUE;
   }

   function get_contents($tickets_arr) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $contents =& $data->add_child("contents", xml_object::create("contents"));
      $this->thread_list = array();
      $thread_content_handler = new thread_content_handler();
      foreach($tickets_arr as $ticket_id=>$max_thread) {
         $db_max_thread_id = 0;
         $ticket_item['ticket_id'] = $ticket_id;
         $this->thread_list[$ticket_id] = $this->db->get("thread", "get_thread_data_max", array('ticket_id'=>$ticket_id, "max_thread_id"=>$max_thread));
         $thread_content_handler->load_ticket_content($ticket_id, $max_thread);
         $list = $this->thread_list[$ticket_id];
         foreach($list as $key=>$thread_item) {
            $this->thread_list[$ticket_id][$key]['content'] = $thread_content_handler->threads[$thread_item['thread_id']]->content;
            if($db_max_thread_id < $thread_item['thread_id']) $db_max_thread_id = $thread_item['thread_id'];
            $this->thread_list[$ticket_id][$key]['attachments'] = $this->db->Get("thread", "attachment_list", array("thread_id"=>$thread_item['thread_id']));
         }
         $ticket_item['max_thread_id'] = $db_max_thread_id;
         $ticket =& $this->gen_xml($contents, $ticket_item, FALSE, TRUE);
      }
      return TRUE;
   }

   function get_thread_headers($tickets_arr) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $contents =& $data->add_child("contents", xml_object::create("contents"));
      $this->thread_list = array();
      $thread_content_handler = new thread_content_handler();
      foreach($tickets_arr as $ticket_id=>$max_thread) {
         $db_max_thread_id = 0;
         $ticket_item['ticket_id'] = $ticket_id;
         $this->thread_list[$ticket_id] = $this->db->get("thread", "get_thread_data_max", array('ticket_id'=>$ticket_id, "max_thread_id"=>$max_thread));
         $list = $this->thread_list[$ticket_id];
         foreach($list as $key=>$thread_item) {
            if($db_max_thread_id < $thread_item['thread_id']) $db_max_thread_id = $thread_item['thread_id'];
            $this->thread_list[$ticket_id][$key]['attachments'] = $this->db->Get("thread", "attachment_list", array("thread_id"=>$thread_item['thread_id']));
         }
         $ticket_item['max_thread_id'] = $db_max_thread_id;
         $ticket =& $this->gen_xml($contents, $ticket_item, FALSE, TRUE, FALSE, FALSE, FALSE);
      }
      return TRUE;
   }

   function load_view($view_type, $limit, $page, $order_by_field, $order_by_direction, $id) {
      $method = "load_" . $view_type . "_view";
      if(method_exists($this, $method)) {
         return $this->$method($limit, $page, $order_by_field, $order_by_direction, $id);
      }
      else {
         return FALSE;
      }
   }

   function load_basic_view($limit, $page, $order_by_field, $order_by_direction, $id = '') {
      $this->ticket_list = $this->db->Get("ticket", "basic_view", array("limit"=>$limit, "page"=>$page, "order_by_field"=>$order_by_field, "order_by_direction"=>$order_by_direction));
      if(!$this->ticket_list || !is_array($this->ticket_list)) {
         return FALSE;
      }
      else {
         return TRUE;
      }
   }

   function load_basic_threaddata_view($limit, $page, $order_by_field, $order_by_direction, $id = '') {
      $this->ticket_list = $this->db->Get("ticket", "basic_view", array("limit"=>$limit, "page"=>$page, "order_by_field"=>$order_by_field, "order_by_direction"=>$order_by_direction));
      $this->thread_list = array();
      $thread_content_handler = new cer_ThreadContentHandler();
      foreach($this->ticket_list as $ticket_item) {
         $ticket_id = $ticket_item['ticket_id'];
         $this->thread_list[$ticket_id] = $this->db->get("thread", "get_thread_data", array('ticket_id'=>$ticket_id));
         $thread_content_handler->loadTicketContentDB($ticket_id);
         $list = $this->thread_list[$ticket_id];
         foreach($list as $key=>$thread_item) {
            $this->thread_list[$ticket_id][$key]['content'] = $thread_content_handler->threads[$thread_item['thread_id']]->content;
         }
      }
      if(!$this->ticket_list || !$this->thread_list || !is_array($this->ticket_list) || !is_array($this->thread_list)) {
         return FALSE;
      }
      else {
         return TRUE;
      }
   }

   function build_view($view_type) {
      $method = "build_" . $view_type . "_view";
      if(method_exists($this, $method)) {
         return $this->$method();
      }
      else {
         return FALSE;
      }
   }

   function build_basic_view() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $view =& $data->add_child("view", xml_object::create("view"));
      foreach($this->ticket_list as $ticket_item) {
         $ticket =& $this->gen_xml($view, $ticket_item);
      }
      return TRUE;
   }

   function build_basic_threaddata_view() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $view =& $data->add_child("view", xml_object::create("view"));
      foreach($this->ticket_list as $ticket_item) {
         $this->gen_xml($view, $ticket_item, TRUE, TRUE);
      }
      return TRUE;
   }

   function gen_xml(&$view, $ticket_item, $with_ticketdata = TRUE, $with_threaddata = FALSE, $with_watchers = FALSE, $with_requesters = FALSE, $with_threadcontent = TRUE, $with_contacts = FALSE) {
      global $priority_options;

      $ticket =& $view->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_item['ticket_id'])));
      if($with_ticketdata) {
         if(is_null($ticket_item['ticket_mask']) || strlen($ticket_item['ticket_mask']) < 1) {
            $ticket->add_child("mask", xml_object::create("mask", $ticket_item['ticket_id']));
         }
         else {
            $ticket->add_child("mask", xml_object::create("mask", $ticket_item['ticket_mask']));
         }
         $ticket->add_child("max_thread_type", xml_object::create("max_thread_type", substr($ticket_item['max_thread_type'], 0, 1)));
         $ticket->add_child("last_reply_by_agent", xml_object::create("last_reply_by_agent", $ticket_item['last_reply_by_agent']));
         $ticket->add_child("subject", xml_object::create("subject", stripslashes($ticket_item['ticket_subject'])));
         $queue =& $ticket->add_child("queue", xml_object::create("queue", NULL, array("id"=>$ticket_item['queue_id'])));
         $queue->add_child("name", xml_object::create("name", $ticket_item['queue_name']));
         $queue->add_child("address", xml_object::create("address", $ticket_item['queue_address'] . '@' . $ticket_item['queue_domain'], array("id"=>$ticket_item['queue_addresses_id'])));
         $ticket->add_child("status", xml_object::create("status", $ticket_item['ticket_status']));
         $date = new cer_DateTime($ticket_item['ticket_due']);
         $ticket->add_child("due", xml_object::create("due", $date->getDate(XML_DATE_FORMAT2), array("timestamp"=>$date->mktime_datetime)));
         $date = new cer_DateTime($ticket_item['last_update_date']);
         $ticket->add_child("last_activity", xml_object::create("last_activity", $date->getDate(XML_DATE_FORMAT2), array("timestamp"=>$date->mktime_datetime)));
         $ticket->add_child("last_wrote_date", xml_object::create("last_wrote_date", $ticket_item['last_wrote_date']));
         $date = new cer_DateTime($ticket_item['ticket_date']);
         $ticket->add_child("created", xml_object::create("created", $date->getDate(XML_DATE_FORMAT2), array("timestamp"=>$date->mktime_datetime)));
         $ticket->add_child("owner", xml_object::create("owner", $ticket_item['ticket_owner'], array("id"=>$ticket_item['ticket_assigned_to_id'])));
         @$priority_string = !empty($priority_options[$ticket_item['ticket_priority']]) ? $priority_options[$ticket_item['ticket_priority']] : 'Unknown';
         $ticket->add_child("priority", xml_object::create("priority", $priority_string, array("id"=>$ticket_item['ticket_priority'])));
         $ticket->add_child("requester", xml_object::create("requester", $ticket_item['requester_address'], array("id"=>$ticket_item['requester_address_id'])));
         $ticket->add_child("last_wrote", xml_object::create("last_wrote", $ticket_item['address_address'], array("id"=>$ticket_item['thread_address_id'])));
         $ticket->add_child("company", xml_object::create("company", $ticket_item['company_name'], array("id"=>$ticket_item['company_id'])));
         $ticket->add_child("time_worked", xml_object::create("time_worked", $ticket_item['total_time_worked']));
         $ticket->add_child("min_thread_id", xml_object::create("min_thread_id", $ticket_item['min_thread_id']));
         $ticket->add_child("max_thread_id", xml_object::create("max_thread_id", $ticket_item['max_thread_id']));
         $spam =& $ticket->add_child("spam", xml_object::create("spam"));
         $spam->add_child("probability", xml_object::create("probability", $ticket_item['ticket_spam_probability']*1));
         $spam->add_child("trained", xml_object::create("trained", $ticket_item['ticket_spam_trained']));
         $ticket->add_child("skill_count", xml_object::create("skill_count", $ticket_item['skill_count']));
      }
      if($with_requesters) {
         $requesters =& $ticket->add_child("requesters", xml_object::create("requesters"));
         if(is_array($ticket_item['requester_list'])) {
            foreach($ticket_item['requester_list'] as $requester_item) {
               $requesters->add_child("requester", xml_object::create("requester", $requester_item['address_address'], array("id"=>$requester_item['address_id'], "suppress"=>$requester_item['suppress'])));
            }
         }
      }
      if($with_contacts) {
         $contacts =& $ticket->add_child("contacts", xml_object::create("contacts"));
         if(is_array($ticket_item['contact_list'])) {
            foreach($ticket_item['contact_list'] as $contact_item) {
               $contact =& $contacts->add_child("contact", xml_object::create("contact", NULL, array("id"=>$contact_item['id'])));
               $contact->add_child("name", xml_object::create("name", $contact_item["name"]));
            }
         }
      }
      if($with_watchers) {
         $watchers =& $ticket->add_child("watchers", xml_object::create("watchers"));
         if(is_array($ticket_item['watcher_list'])) {
            foreach($ticket_item['watcher_list'] as $watcher_item) {
               $watcher =& $watchers->add_child("watcher", xml_object::create("watcher", NULL, array("user_id"=>$watcher_item['user_id'])));
               $watcher->add_child("email", xml_object::create("email", $watcher_item['user_email']));
               $watcher->add_child("name", xml_object::create("name", $watcher_item['user_name']));
            }
         }
      }
      if($with_threaddata && !$with_ticketdata) {
         $ticket->add_child("max_thread_id", xml_object::create("max_thread_id", $ticket_item['max_thread_id']));
      }
      if($with_threaddata) {
         $threads =& $ticket->add_child("threads", xml_object::create("threads"));
         foreach($this->thread_list[$ticket_item['ticket_id']] as $thread_item) {
            $thread =& $threads->add_child("thread", xml_object::create("thread", NULL, array("id"=>$thread_item['thread_id'])));
            $thread->add_child("type", xml_object::create("type", $thread_item['thread_type']));
            $thread->add_child("address_banned", xml_object::create("address_banned", $thread_item['address_banned']));
            $thread->add_child("address", xml_object::create("address", $thread_item['address_address'], array('id'=>$thread_item['address_id'])));
            $thread->add_child("subject", xml_object::create("subject", stripslashes($thread_item['thread_subject'])));
            $thread->add_child("to", xml_object::create("to", $thread_item['thread_to']));
            $thread->add_child("cc", xml_object::create("cc", $thread_item['thread_cc']));
            $date = new cer_DateTime($thread_item['thread_date']);
            $thread->add_child("date", xml_object::create("date", $date->getDate(XML_DATE_FORMAT2), array("timestamp"=>$thread_item['thread_timestamp'])));
            $thread->add_child("replyto", xml_object::create("replyto", $thread_item['thread_replyto']));
            $thread->add_child("is_agent_message", xml_object::create("is_agent_message", $thread_item['is_agent_message']));
            if($with_threadcontent) {
               $thread->add_child("content", xml_object::create("content", stripslashes($thread_item['content'])));
            }
            $attachments =& $thread->add_child("attachments", xml_object::create("attachments"));
            if(is_array($thread_item['attachments']) && count($thread_item['attachments']) > 0) {
               $thread->add_child("has_attachments", xml_object::create("has_attachments", "TRUE"));
               foreach($thread_item['attachments'] as $attachment_item) {
                  $attachments->add_child("attachment", xml_object::create("attachment", $attachment_item['file_name'], array('size'=>$attachment_item['file_size'], 'id'=>$attachment_item['file_id'])));
               }
            }
            else {
               $thread->add_child("has_attachments", xml_object::create("has_attachments", "FALSE"));
            }
         }
      }
   }
}
