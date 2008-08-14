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
 * This class handles unassigning a member from a team
 *
 */
class unassign_member_handler extends xml_parser
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
    * @return unassign_member_handler
    */
   function unassign_member_handler(&$xml) {
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
      $agents = $this->xml->get_child("members",0);
      if(is_object($agents)) {
         $members_list = $agents->get_child("member");
         if(is_array($members_list)) {
            foreach($members_list as $member) {
               if(is_object($member)) {
                  $members_array[] = $member->get_attribute("id",FALSE);
               }
            }
         }
      }
      
      $obj = new general_teams();   
      
      if($obj->unassign_member($team_id, $members_array) === FALSE) {
         xml_output::error(0, 'Failed to unassign agent(s) from team');
      }
      else {
         xml_output::success();
      }
   }        
}
