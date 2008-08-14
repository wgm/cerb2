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

require_once(FILESYSTEM_PATH . "/gateway-api/classes/general/users.class.php");

/**
 * Database abstraction layer for search data
 *
 */
class search_sql
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
    * @return search_sql
    */
   function search_sql(&$db) {
      $this->db =& $db;
      $this->database_loader =& database_loader::get_instance();
   }

   /**
    * Search on keywords function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function keyword_search($params) {
      $keywords = $params['keywords'];
      $sql = "SELECT DISTINCT ticket_id FROM search_words LEFT JOIN search_index USING ( word_id ) WHERE word IN (%s)";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $keywords)), $params['filters']);
   }

   /**
    * Get full words matching partial function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_words_from_partial($params) {
      $word = $params['word'];
      $sql = "SELECT word FROM search_words WHERE word LIKE %s";
      return $this->db->GetAll(sprintf($sql, $this->db->qstr($word)));
   }

   /**
    * Search on requester function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function requester_search($params) {
      $requester = $params['requester'];
      $sql = "SELECT DISTINCT ticket_id FROM address LEFT JOIN requestor USING ( address_id ) WHERE address_address LIKE %s";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $this->db->qstr("%".$requester."%"))), $params['filters']);
   }

   /**
    * Search on assigned to id function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function assigned_search($params) {
      $assigned_id = $params['assigned_id'];
      $sql = "SELECT ticket_id FROM ticket WHERE ticket_assigned_to_id = '%d'";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $assigned_id)), $params['filters']);
   }
   
   /**
    * Search on part of a ticket id function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function partial_ticket_id_search($params) {
      extract($params);
      $sql = "SELECT ticket_id FROM ticket WHERE ticket_id LIKE %s OR ticket_mask LIKE %s";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $this->db->qstr("%".$ticket_search."%"), $this->db->qstr("%".$ticket_search."%"))), $filters);
   }

   function parse_filters($ticket_array, $filters, $full_search = FALSE) {
      if(is_array($ticket_array)) {
         $prefilter_count = count($ticket_array);
      }
      else {
         $prefilter_count = 0;
      }
      if(!is_array($filters) || count($filters) < 1) {
         return array("results"=>$ticket_array, "page"=>1, "total_pages"=>1, "prefilter_count"=>$prefilter_count, "postfilter_count"=>$prefilter_count);
      }

      if($full_search) {
         $sql = "SELECT %s FROM ticket t %s WHERE %s %s %s %s";
         $ticket_list = '1';
      }
      else {
         if(!is_array($ticket_array)) return FALSE;
         if($prefilter_count < 1) return array("results"=>$ticket_array, "page"=>0, "total_pages"=>0, "prefilter_count"=>$prefilter_count, "postfilter_count"=>$prefilter_count);
         $sql = "SELECT %s FROM ticket t %s WHERE t.ticket_id IN (%s) %s %s %s";
         $ticket_list = "''";
         foreach($ticket_array as $ticket_item) {
            $ticket_list .= ",'" . $ticket_item['ticket_id'] . "'";
         }
      }

      $join_tables = array();
      $where = '';
      $order_by = '';
      $limit = '';
      $has_limit = FALSE;
      $has_orderby = FALSE;
      $show_resolved_dead = FALSE;
      $has_contacts = FALSE;
      $limit_page = 0;
      $override_team_permissions = FALSE;
      $contacts_list = array();
      foreach($filters as $key=>$value) {
         switch($key) {
            case 'limit': {
               $has_limit = TRUE;
               if(!is_numeric($value) || $value < 1) {
                  $limit_count = 10;
               }
               else {
                  $limit_count = $value;
               }
               break;
            }
            case 'page': {
               if(!is_numeric($value) || $value < 0) {
                  $limit_page = 0;
               }
               else {
                  $limit_page = $value;
               }
               break;
            }
            case 'order_by': {
               $has_orderby = TRUE;
               $orderby_field = $value;
               break;
            }
            case 'order_by_dir': {
               $orderby_dir = $value;
               break;
            }
            case 'has_skills': {
               if($value) {
                  $where .= " AND t.skill_count > 0 ";
               }
               else {
                  $where .= " AND t.skill_count = 0 ";
               }
               break;
            }
            case 'queues': {
               if(is_array($value)) {
                  $queue_list = "'" . implode("','", $value) . "'";
                  $where .= sprintf(" AND t.ticket_queue_id IN ( %s ) ", $queue_list);
               }
               break;
            }
            case 'show_closed': {
               if($value) {
                  $show_resolved_dead = TRUE;
               }
               break;
            }
            case 'override_team_permissions': {
               if($value) {
                  $override_team_permissions = TRUE;
               }
               break;
            }
            case 'owners': {
               if(is_array($value)) {
                  $owner_list = "'" . implode("','", $value) . "'";
                  $where .= sprintf(" AND t.ticket_assigned_to_id IN ( %s ) ", $owner_list);
               }
               break;
            }
            case 'statuses': {
               if(is_array($value)) {
                  $status_list = "'" . implode("','", $value) . "'";
                  $where .= sprintf(" AND t.ticket_status IN ( %s ) ", $status_list);
               }
               break;
            }
            case 'companies': {
               if(is_array($value)) {
                  $companies_list = "'" . implode("','", $value) . "'";
                  $contacts_db = $this->db->GetAll(sprintf("SELECT public_user_id FROM public_gui_users WHERE company_id IN ( %s )", $companies_list));
                  if(is_array($contacts_db)) {
                     foreach($contacts_db as $contacts_row) {
                        $contacts_list[$contacts_row['public_user_id']] = $contacts_row['public_user_id'];
                     }
                  }
                  $has_contacts = TRUE;
               }
               break;
            }
            case 'contacts': {
               if(is_array($value)) {
                  foreach($value as $contact_id) {
                     $contacts_list[$contact_id] = $contact_id;
                  }
                  $has_contacts = TRUE;
               }
               break;
            }
            case 'created_between': {
            	if(is_array($value)) {
            		$where .= sprintf(" AND t.ticket_date >= FROM_UNIXTIME(%d) AND t.ticket_date <= FROM_UNIXTIME(%d) ", $value["from"], $value["to"]);
            	}
            	break;	
            }
            case 'last_wrote_between': {
            	if(is_array($value)) {
            		$where .= sprintf(" AND th_max.thread_date >= FROM_UNIXTIME(%d) AND th_max.thread_date <= FROM_UNIXTIME(%d) ", $value["from"], $value["to"]);
            		$join_tables[] = " LEFT JOIN thread th_max ON (t.max_thread_id = th_max.thread_id) ";
            	}
            	break;	
            }
            case 'file_name': {
            	$where .= sprintf(" AND attach.file_name LIKE %s ", $this->db->qstr("%" . $value . "%"));
            	$join_tables[] = " INNER JOIN thread th_all ON (t.ticket_id = th_all.ticket_id) ";
            	$join_tables[] = " INNER JOIN thread_attachments attach ON (th_all.thread_id = attach.thread_id) ";
            	break;
            }
            case 'addresses': {
            	if(is_array($value)) {
            		$where .= sprintf(" AND req.address_id IN ( '%s' ) ", implode("','", $value));
            		$join_tables[] = " LEFT JOIN requestor req ON (req.ticket_id = t.ticket_id) ";
            	}
            	else {
            		$where .= sprintf(" AND req.address_id = %s ", $this->db->qstr($value));
            		$join_tables[] = " LEFT JOIN requestor req ON (req.ticket_id = t.ticket_id) ";
            	}
            	break;            		
            }
         }
      }
      if($has_limit) {
         $limit = " LIMIT " . ($limit_page * $limit_count) . "," . $limit_count;
      }
      if($has_orderby) {
         $order_by = " ORDER BY " . $orderby_field . " " . $orderby_dir;
      }
      if($has_contacts) {
         if(count($contacts_list) > 0) {
            $contact_list = "'" . implode("','", array_values($contacts_list)) . "'";
            $addresses_db = $this->db->GetAll(sprintf("SELECT address_id FROM address WHERE public_user_id IN ( %s )", $contact_list));
            if(is_array($addresses_db)) {
               $address_array = array();
               foreach($addresses_db as $address_row) {
                  $address_array[$address_row["address_id"]] = $address_row["address_id"];
               }
               $address_list = "'" . implode("','", array_values($address_array)) . "'";
            }
         }
         else {
            $address_list = "'-1'";
         }
         $join_tables[] = " LEFT JOIN requestor req ON (req.ticket_id = t.ticket_id) ";
         $where .= sprintf(" AND req.address_id IN ( %s ) ", $address_list);
      }
      if(!$show_resolved_dead) {
         $status_array = $this->build_ticket_status_list(array("block_list"=>array("resolved", "dead")));
         $status_list = "'" . implode("','", array_values($status_array)) . "'";
         $where .= sprintf(" AND t.ticket_status IN ( %s ) ", $status_list);
      }
      if(!$override_team_permissions) {
         $queue_array = $this->database_loader->Get("teams", "get_queues_from_user_id_read_writable", array("user_id"=>general_users::get_user_id()));
         $queues_list = "'";
         if(is_array($queue_array)) {
            foreach($queue_array as $queue_row) {
               $queues_list .= "','" . $queue_row["queue_id"];
            }
         }
         $queues_list .= "'";
         $where .= sprintf(" AND t.ticket_queue_id IN ( %s ) ", $queues_list);
      }
      $join_tables_sql = implode(' ', array_unique($join_tables));
      $postfilter_count = $this->db->GetOne(sprintf($sql, 'COUNT(t.ticket_id)', $join_tables_sql, $ticket_list, $where, '', ''));
      if($has_limit) {
         $total_pages = ceil($postfilter_count / $limit_count);
      }
      else {
         $total_pages = 1;
      }
      return array("page"=>$limit_page,
      "total_pages"=>$total_pages,
      "prefilter_count"=>$prefilter_count,
      "postfilter_count"=>$postfilter_count,
      "results"=>$this->db->GetAll(sprintf($sql, 't.ticket_id', $join_tables_sql, $ticket_list, $where, $order_by, $limit)));
   }

   function filter_search($params) {
      return $this->parse_filters(array(), $params['filters'], TRUE);
   }

   /**
    * Get's an array of all the possible ticket statuses for a ticket.
    *
    * @param array $params Associative array of input values
    * @return array ticket_statuses keyed by status name w/ value same
    */
   function build_ticket_status_list($params) {
      extract($params);
      if(!isset($this->ticket_statuses) || !is_array($this->ticket_statuses) || count($this->ticket_statuses) < 0) {
         $this->ticket_statuses = array();
         $query = "DESCRIBE `ticket` `ticket_status`";
         $row = $this->db->GetRow($query);
         $status_raw = $row["Type"];
         preg_match("/enum\(\'(.*?)\'\)/i",$status_raw,$matches);

         if(is_array($matches) && isset($matches[1])) {
            $statuses = explode("','",$matches[1]);
            if(is_array($statuses)) {
               foreach($statuses as $st) {
                  $this->ticket_statuses[$st] = $st;
               }
            }
         }
      }
      $return = $this->ticket_statuses;
      if(is_array($block_list)) {
         foreach($block_list as $status) {
            if(isset($return[$status])) unset($return[$status]);
         }
      }
      return $return;
   }
}