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
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/transcripts.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting report data for chat transcripts list
 *
 */
class get_list_handler extends xml_parser
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
    * @return get_list_handler
    */
   function get_list_handler(&$xml) {
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
      $range = $this->xml->get_child_data("range", 0);
      if(empty($range) || !is_numeric($range) || $range < 1) {
         $range = 1;
      }
      $search = $this->xml->get_child_data("search", 0);

      $reports_obj =& new reports_transcripts();

      if($reports_obj->get_transcripts_list($range, $agent_id, $search) === FALSE) {
         xml_output::error(0, 'Transcripts list report failed');
      }
      else {
         xml_output::success();
      }
   }
}