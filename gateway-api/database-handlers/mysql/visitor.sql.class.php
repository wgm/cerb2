<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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

/**
 * Database abstraction layer for visitor data
 *
 */
class visitor_sql
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
    * @return visitor_sql
    */
   function visitor_sql(&$db) {
      $this->db =& $db;
   }
   
   function create_visitor($params) {
      extract($params);
      
      $browser_id = $this->get_browser_id($browser);
      $host_id = $this->get_host_id($host);
      
      $sql = "INSERT INTO chat_visitors (visitor_sid, visitor_ip, visitor_host_id, visitor_browser_id, visitor_time_start, visitor_time_latest) 
               VALUES (%s, %s, %d, %d, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
      $this->db->Execute(sprintf($sql, $this->db->qstr($sid), $this->db->qstr($ip), $host_id, $browser_id));
      return $this->db->Insert_ID();
   }
   
   function get_host_id($host) {
      $sql = "SELECT host_id FROM stat_hosts WHERE host = %s";
      $host_id = $this->db->GetOne(sprintf($sql, $this->db->qstr($host)));
      
      if(empty($host_id)) {
      	$sql = "INSERT IGNORE INTO stat_hosts(host) VALUES(%s)";
      	$this->db->Execute(sprintf($sql,$this->db->qstr($host)));
      	$host_id = $this->db->Insert_ID();
      }
      
      return $host_id;
   }

   function get_browser_id($browser) {
      $sql = "SELECT browser_id FROM stat_browsers WHERE browser = %s";
      $browser_id = $this->db->GetOne(sprintf($sql, $this->db->qstr($browser)));
      
      if(empty($browser_id)) {
      	$sql = "INSERT IGNORE INTO stat_browsers(browser) VALUES(%s)";
      	$this->db->Execute(sprintf($sql,$this->db->qstr($browser)));
      	$browser_id = $this->db->Insert_ID();
      }
      
      return $browser_id;
   }
   
   function get_url_id($url) {
      $sql = "SELECT url_id FROM stat_urls WHERE url = %s";
      $url_id = $this->db->GetOne(sprintf($sql, $this->db->qstr($url)));
      
      if(empty($url_id)) {
      	$sql = "INSERT IGNORE INTO stat_urls(url) VALUES(%s)";
      	$this->db->Execute(sprintf($sql,$this->db->qstr($url)));
      	$url_id = $this->db->Insert_ID();
      }
      
      return $url_id;
   }
   
   function add_page_hit($params) {
      extract($params);
      $url_id = $this->get_url_id($location);
      $referrer_id = $this->get_url_id($referrer);
      $referrer_host_id = $this->get_host_id($referrer_host);
      
      $sql = "INSERT INTO chat_visitor_pages (page_url_id, visitor_id, page_timestamp, page_referrer_url_id, page_referrer_host_id) ".
      	"VALUES (%d, '%d', UNIX_TIMESTAMP(), %d, %d)";
      return $this->db->Execute(sprintf($sql, $url_id, $visitor_id, $referrer_id, $referrer_host_id));
   }
   
   function save_heartbeat_time($params) {
      extract($params);
      $sql = "UPDATE chat_visitors SET visitor_time_latest = UNIX_TIMESTAMP() WHERE visitor_id = '%d'";
      return $this->db->Execute(sprintf($sql, $visitor_id));
   }
   
   function check_has_invites($params) {
      extract($params);
      $interval = /* number of minutes */ 30 * 60 /* number of seconds in a minute */;
      $sql = "SELECT COUNT(*) FROM chat_visitors_to_invites WHERE visitor_id = '%d' AND invite_date >= (UNIX_TIMESTAMP() - %d)";
      return $this->db->GetOne(sprintf($sql, $visitor_id, $interval));
   }
   
   function get_invite($params) {
      extract($params);
      $interval = /* number of minutes */ 30 * 60 /* number of seconds in a minute */;
      $sql = "SELECT i.invite_message, uei.chat_display_name FROM chat_visitors_to_invites i LEFT JOIN user_extended_info uei ON (i.agent_id = uei.user_id) WHERE visitor_id = '%d' AND invite_date >= (UNIX_TIMESTAMP() - %d)";
      return $this->db->GetRow(sprintf($sql, $visitor_id, $interval));
   }   
   
   function remove_invites($params) {
      extract($params);
      $sql = "DELETE FROM chat_visitors_to_invites WHERE visitor_id = '%d'";
      return $this->db->Execute(sprintf($sql, $visitor_id));
   }
   
   function get_list($params) {
      extract($params);
      $sql = "SELECT cv.*, sh.host as visitor_host, sb.browser as visitor_browser, ".
      	"UNIX_TIMESTAMP() - visitor_time_start AS duration ".
      	"FROM chat_visitors cv, stat_hosts sh, stat_browsers sb ".
      	"WHERE sh.host_id = cv.visitor_host_id ".
      	"AND sb.browser_id = cv.visitor_browser_id ".
      	"AND visitor_time_latest > (UNIX_TIMESTAMP() - %d)";
      return $this->db->GetAll(sprintf($sql, $session_expire_secs));
   }
   
   function get_pages($params) {
      extract($params);
      $sql = "SELECT su.url as page_name, su2.url as page_referrer, cp.page_timestamp ".
      	"FROM chat_visitor_pages cp, stat_urls su, stat_urls su2 ".
      	"WHERE su.url_id = cp.page_url_id AND su2.url_id = cp.page_referrer_url_id AND visitor_id = '%d' ".
      	"ORDER BY cp.page_timestamp ASC";
      return $this->db->GetAll(sprintf($sql, $visitor_id));
   }
   
   function save_invite($params) {
      extract($params);
      $sql = "INSERT INTO chat_visitors_to_invites (invite_date, visitor_id, agent_id, invite_message) VALUES (UNIX_TIMESTAMP(), '%d', '%d', %s)";
      return $this->db->Execute(sprintf($sql, $visitor_id, $agent_id, $this->db->qstr($message)));
   }
   
   function get_referrer($params) {
      extract($params);
      $sql = "SELECT MIN(page_id) FROM chat_visitor_pages WHERE visitor_id = '%d'";
      $min_page_id = $this->db->GetOne(sprintf($sql, $visitor_id));
      $sql = "SELECT su.url as page_referrer FROM chat_visitor_pages cp, stat_urls su ".
      	"WHERE su.url_id = cp.page_referrer_url_id AND visitor_id = '%d' AND page_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id, $min_page_id));
   }
   
   function get_current_page($params) {
      extract($params);
      $sql = "SELECT MAX(page_id) FROM chat_visitor_pages WHERE visitor_id = '%d'";
      $max_page_id = $this->db->GetOne(sprintf($sql, $visitor_id));
      $sql = "SELECT u.url as page_name FROM stat_urls u, chat_visitor_pages cp WHERE cp.page_url_id = u.url_id AND cp.visitor_id = '%d' AND cp.page_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id, $max_page_id));
   }
   
   function get_visitor_info($params) {
      extract($params);
      $sql = "SELECT cv.*, sh.host as visitor_host, sb.browser as visitor_browser ".
      	"FROM chat_visitors cv, stat_hosts sh, stat_browsers sb ".
      	"WHERE sh.host_id = cv.visitor_host_id ".
      	"AND sb.browser_id = cv.visitor_browser_id ".
      	"AND visitor_id = '%d'";
      return $this->db->GetRow(sprintf($sql, $visitor_id));
   }
   
   function get_page_count($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM chat_visitor_pages WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
   
   function num_chat_requests_visitor($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM chat_visitor_chat_requests WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
   
   function num_chat_rooms_visitor($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM chat_visitors_to_rooms WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
   
   function num_chat_invites_visitor($params) {
      extract($params);
      $sql = "SELECT COUNT(*) FROM chat_visitors_to_invites WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
   
   function get_visitor_id($params) {
      extract($params);
      $sql = "SELECT visitor_id FROM chat_visitors WHERE visitor_sid = %s AND visitor_ip = %s";
      $visitor_id = $this->db->GetOne(sprintf($sql, $this->db->qstr($visitor_sid), $this->db->qstr($ip)));
      if(is_numeric($visitor_id) && $visitor_id > 1) {
         return $visitor_id;
      }
      else {
         return FALSE;
      }
   }
   
   function save_name_question($params) {
      extract($params);
      $sql = "UPDATE chat_visitors SET visitor_name = %s, visitor_question = %s WHERE visitor_id = '%d'";
      return $this->db->Execute(sprintf($sql, $this->db->qstr($visitor_name), $this->db->qstr($visitor_question), $visitor_id));
   }
   
   function save_chat_request($params) {
      extract($params);
      $sql = "REPLACE INTO chat_visitor_chat_requests (visitor_id, request_time_start, request_time_heartbeat) VALUES (%d, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
      if(!$this->db->Execute(sprintf($sql, $visitor_id))) {
         return FALSE;
      }
      else {
         return $this->db->Insert_ID();
      }
   }
   
   // [JAS]: [TODO] This is a temporary hack until Jeremy can fix his code to properly allow visitors to
   //	chat in multiple rooms.
   function clear_other_visitor_requests($params) {
      extract($params);
      
      $sql = "DELETE FROM chat_visitor_chat_requests WHERE visitor_id = '%d' AND chat_request_id != '%d'";
      $this->db->Execute(sprintf($sql, $visitor_id, $request_id));
      
      $sql = "DELETE FROM chat_visitors_to_rooms WHERE visitor_id = '%d'";
      $this->db->Execute(sprintf($sql, $visitor_id));

      return true;
   }
   
   function expire_visitor_request($params) {
      extract($params);
      $sql = "DELETE FROM chat_visitor_chat_requests WHERE visitor_id = '%d'";
      return $this->db->Execute(sprintf($sql, $visitor_id));
   }
   
   function update_request_time($params) {
      extract($params);
      $sql = "UPDATE chat_visitor_chat_requests SET request_time_heartbeat = UNIX_TIMESTAMP() WHERE visitor_id = '%d'";
      return $this->db->Execute(sprintf($sql, $visitor_id));
   }
   
   function request_pending_time($params) {
      extract($params);
      $sql = "SELECT request_time_heartbeat - request_time_start FROM chat_visitor_chat_requests WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
   
   function get_request_room_id($params) {
      extract($params);
      $sql = "SELECT room_id FROM chat_visitor_chat_requests WHERE chat_request_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $request_id));
   }
   
   function get_visitor_name($params) {
      extract($params);
      $sql = "SELECT visitor_name FROM chat_visitors WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }

   function get_latest_request_from_visitor($params) {
      extract($params);
      $sql = "SELECT chat_request_id FROM chat_visitor_chat_requests WHERE visitor_id = '%d' ORDER BY chat_request_id DESC";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
   
   function get_visitor_id_from_request($params) {
      extract($params);
      $sql = "SELECT visitor_id FROM chat_visitor_chat_requests WHERE chat_request_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $request_id));
   } 
   
   function assign_room_to_request($params) {
      extract($params);
      $sql = "UPDATE chat_visitor_chat_requests SET room_id = '%d' WHERE visitor_id = '%d' AND chat_request_id = '%d'";
      return $this->db->Execute(sprintf($sql, $room_id, $visitor_id, $request_id));
   }
   
   function reset_request_start($params) {
      extract($params);
      $sql = "UPDATE chat_visitor_chat_requests SET request_time_start = request_time_start - %d WHERE chat_request_id = '%d'";
      return $this->db->Execute(sprintf($sql, $chat_timeout, $request_id));
   }
   
   function get_chat_requests($params) {
      extract($params);
      $query = "SELECT cr.chat_request_id, cr.visitor_id, cr.request_time_start, ".
               " UNIX_TIMESTAMP() - cr.request_time_start AS request_time_waiting, ".
               " v.visitor_name, sh.host as visitor_host, v.visitor_question ".
               " FROM chat_visitor_chat_requests cr ".
               " LEFT JOIN chat_visitors v USING (visitor_id) ".
               " INNER JOIN stat_hosts sh ON v.visitor_host_id = sh.host_id ".
               " WHERE room_id = 0";
      return $this->db->GetAll($query);
   }
   
   function expire_sessions($params) {
      extract($params);
      $sql = "UPDATE chat_visitors SET visitor_sid = '' WHERE visitor_time_latest < (UNIX_TIMESTAMP()-%d)";
      return $this->db->Execute(sprintf($sql, $timeout));
   }
   
   function get_agent_from_invite($params) {
      extract($params);
      $sql = "SELECT agent_id FROM chat_visitors_to_invites WHERE visitor_id = '%d'";
      return $this->db->GetOne(sprintf($sql, $visitor_id));
   }
}