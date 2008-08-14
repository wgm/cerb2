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
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");

class email_tickets
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function email_tickets() {
      $this->db =& database_loader::get_instance();
      $this->audit_log = new CER_AUDIT_LOG();
   }

   function mark_as_resolved($ticket_id) {
      $this->mark_as_unassigned($ticket_id);
      if($this->db->Save("ticket", "mark_as_resolved", array("ticket_id"=>$ticket_id))) {
         $this->audit_log->log_action($ticket_id, general_users::get_user_id(), AUDIT_ACTION_CHANGED_STATUS, 'resolved');
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $data->add_child("ticket_id", xml_object::create("ticket_id", $ticket_id));
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function mark_as_junk($ticket_id) {
      $this->mark_as_unassigned($ticket_id);
      $this->train_spam($ticket_id);
      if($this->db->Save("ticket", "mark_as_junk", array("ticket_id"=>$ticket_id))) {
         $this->audit_log->log_action($ticket_id, general_users::get_user_id(), AUDIT_ACTION_CHANGED_STATUS, 'dead');
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $data->add_child("ticket_id", xml_object::create("ticket_id", $ticket_id));
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function assign_for_later($ticket_id, $type, $reason, $date) {
      $this->mark_as_unassigned($ticket_id);
      $query_params = array("ticket_id"=>$ticket_id, "type"=>$type, "reason"=>$reason, "agent_id"=>general_users::get_user_id());
      if($type == DISPATCHER_DELAY_DATE) {
         $query_params["date"] = $date;
      }
      else {
         $query_params["date"] = 0;
      }
      if($this->db->Save("ticket", "assign_for_later", $query_params)) {
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $data->add_child("ticket_id", xml_object::create("ticket_id", $ticket_id));
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function train_spam($ticket_id) {
      $bayes = new cer_BayesianAntiSpam();
      $bayes->mark_message_as_spam($ticket_id);
   }

   function unassign($ticket_id) {
      if($this->mark_as_unassigned($ticket_id) === FALSE) {
         return FALSE;
      }
      else {
         $xml =& xml_output::get_instance();
         $data =& $xml->get_child("data", 0);
         $data->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id)));
         return TRUE;
      }
   }

   function mark_as_unassigned($ticket_id) {
      if($this->db->Save("ticket", "mark_as_unassigned", array("ticket_id"=>$ticket_id))) {
         $this->audit_log->log_action($ticket_id, general_users::get_user_id(), AUDIT_ACTION_CHANGED_ASSIGN, 'Nobody');
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function reassign_to_user($ticket_id, $user_id) {
      $username = $this->db->Get("user", "get_userlogin", array("user_id"=>$user_id));
      if(!$this->db->Save("ticket", "reassign_to_user", array("ticket_id"=>$ticket_id, "user_id"=>$user_id))) {
         return FALSE;
      }
      $this->audit_log->log_action($ticket_id, general_users::get_user_id(), AUDIT_ACTION_CHANGED_ASSIGN, $username);
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $data->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id)));
      $data->add_child("user", xml_object::create("user", NULL, array("id"=>$user_id)));
      return TRUE;
   }

   function mass_actions($tickets, $actions) {
   	if(array_key_exists("spam", $actions) && $actions["spam"] == 1) {
   		foreach($tickets as $ticket_id) {
   			$this->train_spam($ticket_id);
   		}
   	}
   	return $this->db->Save("ticket", "mass_actions", array("tickets"=>$tickets, "actions"=>$actions));  	
   }
}