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
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/visitor.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class sending an invite to a user
 *
 */
class send_invite_handler extends xml_parser
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
    * @return send_invite_handler
    */
   function send_invite_handler(&$xml) {
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
      
      $visitors =& new chat_visitor(); 
      
      $visitor_id = $this->xml->get_child_data("visitor_id", 0);
      $message = $this->xml->get_child_data("message", 0);
            
      if($visitors->send_invite(general_users::get_user_id(), $visitor_id, $message) === FALSE) {
         xml_output::error(0, 'Failed to send invite');
      }
      else {
         xml_output::success();
      }
   }
}