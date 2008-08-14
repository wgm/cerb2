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
 * Database abstraction layer for queues data
 *
 */
class queues_sql
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
    * @return queues_sql
    */
   function queues_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Get list of primary queues
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function primary_list($params) {
      $query = "SELECT q.queue_id, q.queue_name, q.queue_prefix, q.queue_email_display_name, qa.queue_addresses_id, 
               qa.queue_address, qa.queue_domain, q.queue_mode FROM queue_addresses qa 
               LEFT JOIN queue q USING (queue_id) ORDER BY qa.queue_id";
      return $this->db->GetAll($query);
   }  
   
   function get_full_queue_list_with_access($params) {
      extract($params);
      $sql = "SELECT q.queue_id, q.queue_name, q.queue_prefix, q.queue_email_display_name, qa.queue_addresses_id, qa.queue_address, qa.queue_domain, q.queue_mode, IF(tm.agent_id IS NULL, 0, MAX(tq.queue_access)) AS queue_access FROM queue_addresses qa LEFT JOIN queue q USING ( queue_id ) LEFT JOIN team_queues tq USING ( queue_id ) LEFT JOIN team_members tm USING ( team_id ) WHERE tm.agent_id IS NULL or tm.agent_id = '%d' GROUP BY qa.queue_addresses_id ORDER BY qa.queue_id";
      return $this->db->GetAll(sprintf($sql, $user_id));
   }
}