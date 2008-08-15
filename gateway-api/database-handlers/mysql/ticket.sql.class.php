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
 * Database abstraction layer for ticket data
 *
 */
class ticket_sql
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
    * @return ticket_sql
    */
   function ticket_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Get ticket view function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function basic_view($params) {
      $limit = 0+$params["limit"];
      $page = 0+$params["page"]*$limit;
      $field = $params["order_by_field"];
      $direction = $params["order_by_direction"];
      $sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_priority, t.ticket_spam_trained, t.last_reply_by_agent, t.ticket_spam_probability, 
               th.thread_date, thr.thread_received, t.ticket_status, t.ticket_due, th.thread_address_id, t.min_thread_id, a.address_address, 
               t.ticket_mask, t.ticket_time_worked AS total_time_worked, ad.address_address AS requester_address, ad.address_banned, 
               q.queue_id, q.queue_name, c.name AS company_name, u.user_name AS ticket_owner, c.id AS company_id, t.ticket_date, qa.queue_address, 
               UNIX_TIMESTAMP(t.last_update_date) AS last_update_date, thr.thread_address_id AS requester_address_id, qa.queue_domain, qa.queue_addresses_id,
               t.min_thread_id, t.max_thread_id, UNIX_TIMESTAMP(th.thread_date) AS last_wrote_date
               FROM (ticket t, thread th, thread thr, address a, address ad)
               LEFT JOIN queue_addresses qa ON (t.queue_addresses_id = qa.queue_addresses_id)
               LEFT JOIN queue q ON ( q.queue_id = t.ticket_queue_id )
               LEFT JOIN public_gui_users pu ON ( ad.public_user_id = pu.public_user_id )
               LEFT JOIN company c ON ( pu.company_id = c.id )
               LEFT JOIN user u ON ( u.user_id = t.ticket_assigned_to_id )
               WHERE t.max_thread_id = th.thread_id
               AND t.min_thread_id = thr.thread_id
               AND a.address_id = th.thread_address_id
               AND ad.address_id = thr.thread_address_id
               ORDER BY `%s` %s
               LIMIT %d, %d";
      return $this->db->GetAll(sprintf($sql, $field, $direction, $page, $limit));
   }  
   
   /**
    * Get latest tickets function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_latest($params) {  
      $timestamp = 0+$params['timestamp'];
      $limit = 0+$params['limit'];
      $sql = "SELECT t.ticket_id, UNIX_TIMESTAMP(t.last_update_date) AS ticket_last_update FROM ticket t WHERE 
               UNIX_TIMESTAMP(t.last_update_date) > '%d' ORDER BY t.last_update_date DESC LIMIT %d";
      return $this->db->GetAll(sprintf($sql, $timestamp, $limit));
   }
   
   /**
    * Get assigned tickets function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_assigned($params) {  
      $user_id = 0+$params['user_id'];
      $timestamp = 0+$params['timestamp'];
      $sql = "SELECT t.ticket_id, UNIX_TIMESTAMP(t.last_update_date) AS ticket_last_update FROM ticket t ".
      			"WHERE t.ticket_assigned_to_id = '%d' ".
      			"AND UNIX_TIMESTAMP(t.last_update_date) > '%d' ".
                "AND t.ticket_status NOT IN ('resolved', 'dead', 'awaiting-reply') ".
			   "ORDER BY t.last_update_date DESC";
      return $this->db->GetAll(sprintf($sql, $user_id, $timestamp));
   }
   
   /**
    * Get headers function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_headers($params) {  
      $tickets = $params['tickets'];
      $sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_priority, t.ticket_spam_trained, t.last_reply_by_agent, t.ticket_spam_probability, 
               th.thread_date, thr.thread_received, t.ticket_status, t.ticket_due, th.thread_address_id, t.min_thread_id, a.address_address, 
               t.ticket_mask, t.ticket_time_worked AS total_time_worked, ad.address_address AS requester_address, ad.address_banned, 
               q.queue_id, q.queue_name, c.name AS company_name, u.user_name AS ticket_owner, c.id AS company_id, t.ticket_date, qa.queue_address, 
               UNIX_TIMESTAMP(t.last_update_date) AS last_update_date, thr.thread_address_id AS requester_address_id, qa.queue_domain, qa.queue_addresses_id,
               t.min_thread_id, t.max_thread_id, th.thread_type AS max_thread_type, t.skill_count, UNIX_TIMESTAMP(th.thread_date) AS last_wrote_date
               FROM (ticket t, thread th, thread thr, address a, address ad)
               LEFT JOIN queue_addresses qa ON (t.queue_addresses_id = qa.queue_addresses_id)
               LEFT JOIN queue q ON ( q.queue_id = t.ticket_queue_id )
               LEFT JOIN public_gui_users pu ON ( ad.public_user_id = pu.public_user_id )
               LEFT JOIN company c ON ( pu.company_id = c.id )
               LEFT JOIN user u ON ( u.user_id = t.ticket_assigned_to_id )
               WHERE t.max_thread_id = th.thread_id
               AND t.min_thread_id = thr.thread_id
               AND a.address_id = th.thread_address_id
               AND ad.address_id = thr.thread_address_id
               AND t.ticket_id IN ('%s') ";
      return $this->db->GetAll(sprintf($sql, $tickets));
   }

   /**
    * Get requesters for a ticket
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function requester_list($params) {  
      $ticket_id = 0+$params['ticket_id'];
      $sql = "SELECT r.suppress, a.address_id, a.address_address FROM requestor r LEFT JOIN address a USING (address_id) 
               WHERE ticket_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
   
   /**
    * Get watchers for a ticket
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function watcher_list($params) {  
      $ticket_id = 0+$params['ticket_id'];
      $sql = "SELECT u.user_id, u.user_name, u.user_email FROM ticket t LEFT JOIN queue_access qa ON (t.ticket_queue_id = qa.queue_id) LEFT JOIN user u USING ( user_id ) WHERE queue_watch = 1 AND t.ticket_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
   
   /**
    * Get tickets and age assigned to user (report)
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function agent_ticket_age($params) {  
      $query = "SELECT ticket_id, ticket_assigned_to_id, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_update_date) AS ticket_age 
                  FROM ticket WHERE ticket_assigned_to_id > 0 AND ticket_status NOT IN ('resolved', 'dead') ORDER BY ticket_assigned_to_id";
      return $this->db->GetAll($query);
   }
   
   function mark_as_unassigned($params) {
      extract($params);
      $sql = "UPDATE ticket SET ticket_assigned_to_id = 0, last_update_date = NOW() WHERE ticket_id = '%d'";
      return $this->db->Execute(sprintf($sql, $ticket_id));
   }
   
   function mark_as_resolved($params) {
      extract($params);
      $sql = "UPDATE ticket SET ticket_status = 'resolved', last_update_date = NOW() WHERE ticket_id = '%d'";
      return $this->db->Execute(sprintf($sql, $ticket_id));
   }
   
   function mark_as_dead($params) {
      extract($params);
      $sql = "UPDATE ticket SET ticket_status = 'dead', last_update_date = NOW() WHERE ticket_id = '%d'";
      return $this->db->Execute(sprintf($sql, $ticket_id));
   }
   
   function assign_for_later($params) {
      extract($params);
      $sql = "INSERT INTO dispatcher_delays (ticket_id, agent_id, delay_type, added_timestamp, expire_timestamp, reason) VALUES ('%d', '%d', '%d', UNIX_TIMESTAMP(), '%d', %s)";
      return $this->db->Execute(sprintf($sql, $ticket_id, $entity_id, $type, $date, $this->db->qstr($reason)));
   }
   
   function mark_as_junk($params) {
      extract($params);
      $sql = "UPDATE ticket SET ticket_status = 'dead', ticket_spam_trained = 2, last_update_date = NOW() WHERE ticket_id = '%d'";
      return $this->db->Execute(sprintf($sql, $ticket_id));
   }
   
   function reassign_to_user($params) {
      extract($params);
      $sql = "UPDATE ticket SET ticket_assigned_to_id = '%d', last_update_date = NOW() WHERE ticket_id = '%d'";
      return $this->db->Execute(sprintf($sql, $user_id, $ticket_id));
   }
   
   function add_task($params) {
      extract($params);
      $sql = "INSERT INTO ticket_tasks (ticket_id, estimate, date_added, completed, title) VALUES ('%d', '%d', UNIX_TIMESTAMP(), 0, %s)";
      return $this->db->Execute(sprintf($sql, $ticket_id, $estimate, $this->db->qstr($title)));
   }
   
   /**
    * Get requesters for a ticket
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_primary_contact($params) {  
      $ticket_id = 0+$params['ticket_id'];
      $sql = "SELECT CONCAT(pgu.name_first, ' ', pgu.name_last) AS name, pgu.public_user_id AS id FROM ticket t LEFT JOIN thread th ON (t.min_thread_id = th.thread_id)
      			LEFT JOIN address a ON (th.thread_address_id = a.address_id) INNER JOIN public_gui_users pgu USING (public_user_id) WHERE t.ticket_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $ticket_id));
   }
   
   function mass_actions($params) {
   	extract($params);
   	$ticket_list = "'" . implode("','", $tickets) . "'";
   	$action_sql = " last_update_date = NOW() ";
   	foreach($actions as $action=>$value) {
   		switch($action) {
   			case 'status': {
   				$action_sql .= sprintf(", ticket_status = %s ", $this->db->qstr($value));
   				break;
   			}
   			case 'queue': {
   				$action_sql .= sprintf(", ticket_queue_id = '%d' ", $value);
   				break;
   			}
   			case 'priority': {
   				$action_sql .= sprintf(", ticket_priority = '%d' ", $value);
   				break;
   			}
   			case 'owner': {
   				$action_sql .= sprintf(", ticket_assigned_to_id = '%d' ", $value);
   				break;
   			}
   			case 'due_date': {
   				$action_sql .= sprintf(", ticket_due = FROM_UNIXTIME('%d') ", $value);
   				break;
   			}
   		}
   	}
   	$sql = "UPDATE ticket SET %s WHERE ticket_id IN ( %s )";
   	return $this->db->Execute(sprintf($sql, $action_sql, $ticket_list));
   }
}