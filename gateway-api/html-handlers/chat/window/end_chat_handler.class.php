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
 * This class handles ending a chat
 *
 */
class end_chat_handler
{  
   /**
    * Class constructor
    *
    * @return end_chat_handler
    */
   function end_chat_handler() {
      // Nothing needed right now
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $class_obj = new chat_visitor();
      
      $GUID = get_var('chatVisitor', FALSE, get_var('visitor', FALSE, NULL));
      $room_id = get_var('r', TRUE);
                     
      if($class_obj->end_visitor_chat($GUID, $room_id) === FALSE) {
         html_output::error(0, 'Failed to end chat!'); 
      }
      else {
         html_output::success();
      }
   }        
}