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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/dispatcher.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting work data
 *
 */
class get_work_handler extends xml_parser
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
    * @return get_work_handler
    */
   function get_work_handler(&$xml) {
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

      $obj =& new email_dispatcher();
      
      
//      $ticket_limit = $this->xml->get_child_data("ticket_limit", 0);
//      if(!is_numeric($ticket_limit) || $ticket_limit < 0) {
//         $ticket_limit = DEFAULT_NUM_OWNED_TICKETS;
//      }
      
      $teams_pulled = array();
      $teams_not_pulled = array();
      $teams_xml =& $this->xml->get_child("teams", 0);
      if(is_object($teams_xml)) {
         $team_array = $teams_xml->get_child("team");
         if(is_array($team_array)) {
            foreach($team_array as $team_item) {
               $team_id = $team_item->get_attribute("id", FALSE);
               $ticket_pull = $team_item->get_attribute("ticket_pull", FALSE);
               if($ticket_pull == 1) {
                  $teams_pulled[] = $team_id;
               }
            }
         }
      }

//      if($obj->assign_work($ticket_limit, general_users::get_user_id(), $teams_pulled) === FALSE) {
      if($obj->assign_work(general_users::get_user_id(), $teams_pulled) === FALSE) {
         xml_output::error(0, 'Error in assigning work to user');
      }
      else {
         xml_output::success();
      }
   }
}