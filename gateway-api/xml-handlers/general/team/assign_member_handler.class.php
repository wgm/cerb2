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
 * This class handles adding a member to a team
 *
 */
class assign_member_handler extends xml_parser
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
    * @return assign_member_handler
    */
   function assign_member_handler(&$xml) {
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
      
      $team_id = $this->xml->get_child_data("team_id", 0);
      if(empty($team_id) || $team_id < 1) {
         xml_output::error(0, 'Team ID not provided or invalid');
      }
      
      $agents_array = array();
      $agents = $this->xml->get_child("agents",0);
      if(is_object($agents)) {
         $agents_list = $agents->get_child("agent");
         if(is_array($agents_list)) {
            foreach($agents_list as $agent) {
               if(is_object($agent)) {
                  $agents_array[] = array("id"=>$agent->get_attribute("id",FALSE), "options"=>$agent->get_attribute("options",FALSE));
               }
            }
         }
      }
      
      if(empty($agents_array)) {
         xml_output::error(0, "Agent(s) not provided or invalid");
      }
           
      $obj = new general_teams();   
      
      if($obj->assign_member($team_id, $agents_array) === FALSE) {
         xml_output::error(0, 'Failed to assign agent(s) to team');
      }
      else {
         xml_output::success();
      }
   }        
}