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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/visitor.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles update heartbeats
 *
 */
class update_heartbeat_handler
{  
   /**
    * Class constructor
    *
    * @return update_heartbeat_handler
    */
   function update_heartbeat_handler() {
      // Nothing needed right now
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $class_obj = new chat_visitor();
      
      $chat_sid = get_var('chatVisitor', FALSE, get_var('visitor', FALSE, NULL));
      $location = get_var('location', TRUE);
      $referrer = get_var('referrer', TRUE);
      $first = get_var('first', FALSE, 1);
                     
      if(FALSE === $invite = $class_obj->heartbeat($first, $chat_sid, $location, $referrer)) {
         html_output::error(0, 'Failed to get javascript!'); 
      }
      else {
         $class_obj->send_invite_image($invite);
		}
   }        
}