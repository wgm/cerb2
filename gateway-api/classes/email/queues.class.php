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
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");

class email_queues
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function email_queues() {
      $this->db =& database_loader::get_instance();
   }

   function get_full_list() {
      return FALSE;
   }
   
   function get_primary_list() {
      $queues_array = array();
      $queue_list = $this->db->get("queues", "primary_list", array());
      if(!is_array($queue_list)) {
         return FALSE;
      }
      foreach($queue_list as $item) {
         $queues_array[$item['queue_id']]['name'] = $item['queue_name'];
         $queues_array[$item['queue_id']]['prefix'] = $item['queue_prefix'];
         $queues_array[$item['queue_id']]['display_name'] = $item['queue_email_display_name'];
         $queues_array[$item['queue_id']]['mode'] = $item['queue_mode'];
         $queues_array[$item['queue_id']]['addresses'][] = array('address_id'=>$item['queue_addresses_id'], 'address'=>$item['queue_address'].'@'.$item['queue_domain']);
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $queues =& $data->add_child("queues", xml_object::create("queues"));
      foreach($queues_array as $queue_id=>$info) {
         $queue =& $queues->add_child("queue", xml_object::create("queue", NULL, array("id"=>$queue_id, "mode"=>$info['mode'])));
         $queue->add_child("name", xml_object::create("name", $info['name']));
         $queue->add_child("display_name", xml_object::create("display_name", $info['display_name']));
         $addresses =& $queue->add_child("addresses", xml_object::create("addresses"));
         foreach($info['addresses'] as $addr_info) {
            $addresses->add_child("address", xml_object::create("address", $addr_info['address'], array('id'=>$addr_info['address_id'])));
         }
      }
      return TRUE;         
   }
   
   function get_permissions($user_id) {
      $queue_access = new CER_QUEUE_ACCESS();
      $queue_perms = $queue_access->queues;
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $queues =& $data->add_child("queues", xml_object::create("queues"));
      foreach($queue_perms as $perm) {
          $queues->add_child("queue", xml_object::create("queue", $perm->queue_name, array("id"=>$perm->queue_id, "access"=>$perm->queue_access, "watcher"=>$perm->queue_watcher)));
      }
   }
}
