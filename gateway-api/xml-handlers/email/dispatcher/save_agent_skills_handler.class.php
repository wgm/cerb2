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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/skills.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets the skills list
 *
 */
class save_agent_skills_handler extends xml_parser
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
    * @return save_agent_skills_handler
    */
   function save_agent_skills_handler(&$xml) {
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

      $agent_id = $this->xml->get_child_data("agent_id", 0);
      if(!is_numeric($agent_id) || $agent_id < 1) {
         $agent_id = $_SESSION['user_data']['user_id'];
      }

      $skills_list = array();

      $skills =& $this->xml->get_child("skills", 0);
      $skills_children = $skills->get_child("skill");
      if(is_array($skills_children)) {
         foreach($skills_children as $skills_child) {
            $skills_list[] = $skills_child->get_attribute("id", FALSE);
         }
      }

      $skills = new email_skills();
      if($skills->save_agent_skills($agent_id, $skills_list) === FALSE) {
         xml_output::error(0, 'Failed to get skills list');
      }
      else {
         xml_output::success();
      }
   }
}