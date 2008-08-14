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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/teams.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles adding teams
 *
 */
class add_team_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;

   /**
    * Class constructor
    *
    * @param object $xml
    * @return add_team_handler
    */
   function add_team_handler(&$xml) {
      $this->xml =& $xml;
   }

   /**
    * main() function for this class. 
    *
    */
   function process() {
      $users_obj =& new general_users();
      if($users_obj->check_login() === FALSE) {
         xml_output::error(0, 'Not logged in. Please login before proceeding!');
      }

      $team_name = $this->xml->get_child_data("team_name", 0);
      if(strlen($team_name) < 2) {
         xml_output::error(0, 'Team name should be atleast 2 characters long');
      }

      $department_id = $this->xml->get_child_data("department_id", 0);
      if(empty($department_id) || !is_numeric($department_id) || $department_id < 1) {
         xml_output::error(0, 'Invalid or missing department ID');
      }

      $team_acl1 = $this->xml->get_child_data("team_acl1", 0);
      $team_acl2 = $this->xml->get_child_data("team_acl2", 0);
      $team_acl3 = $this->xml->get_child_data("team_acl3", 0);

      $queues_array = array();
      $queues = $this->xml->get_child("queues",0);
      if(is_object($queues)) {
         $queues_list = $queues->get_child("queue");
         if(is_array($queues_list)) {
            foreach($queues_list as $queue) {
               if(is_object($queue)) {
                  $queues_array[$queue->get_attribute("id", FALSE)] = $queue->get_attribute("access", FALSE);
               }
            }
         }
      }

      $obj = new general_teams();

      if($obj->add_team($team_name, $team_acl1, $team_acl2, $team_acl3, $queues_array, $department_id) === FALSE) {
         xml_output::error(0, 'Failed to add team');
      }
      else {
         xml_output::success();
      }
   }
}