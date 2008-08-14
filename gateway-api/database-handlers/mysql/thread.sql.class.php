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
 * Database abstraction layer for thread data
 *
 */
class thread_sql
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
    * @return thread_sql
    */
   function thread_sql(&$db) {
      $this->db =& $db;
   }

   /**
    * Get thread view function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_thread_data($params) {
      $ticket_id = 0+$params['ticket_id'];
      $order = $params['thread_order'];
      $sql = "SELECT th.thread_id, th.thread_type, th.thread_date, th.thread_time_worked, ad.address_banned,ad.address_id, ad.address_address,
               th.thread_subject, th.thread_to, th.thread_cc, th.thread_replyto, th.is_agent_message FROM thread th, ticket tk, address ad WHERE 
               th.ticket_id = tk.ticket_id AND th.thread_address_id = ad.address_id AND tk.ticket_id = %d ORDER BY th.thread_id %s";
      return $this->db->GetAll(sprintf($sql, $ticket_id, $order));
   }

   /**
    * Get thread view function for threads greater than max thread
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_thread_data_max($params) {
      $ticket_id = 0+$params['ticket_id'];
      $max_thread_id = 0+$params['max_thread_id'];
      $order = $params['thread_order'];
      $sql = "SELECT th.thread_id, th.thread_type, th.thread_date, th.thread_time_worked, ad.address_banned,ad.address_id, ad.address_address,
               th.thread_subject, th.thread_to, th.thread_cc, th.thread_replyto, th.is_agent_message, UNIX_TIMESTAMP(th.thread_date) AS thread_timestamp
               FROM thread th, ticket tk, address ad WHERE th.ticket_id = tk.ticket_id AND th.thread_address_id = ad.address_id AND tk.ticket_id = %d 
               AND th.thread_id > %d ORDER BY th.thread_id %s";
      return $this->db->GetAll(sprintf($sql, $ticket_id, $max_thread_id, $order));
   }

   /**
    * Get attachment list function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function attachment_list($params) {
      $thread_id = 0+$params['thread_id'];
      $sql = "SELECT file_id, file_name, file_size FROM thread_attachments WHERE thread_id = '%d'";
      return $this->db->GetAll(sprintf($sql, $thread_id));
   }
}