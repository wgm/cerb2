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
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/window.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the iframe HTML for heartbeat
 *
 */
class getiframe_heartbeat_handler
{  
   /**
    * Class constructor
    *
    * @return getiframe_heartbeat_handler
    */
   function getiframe_heartbeat_handler() {
      // Nothing needed right now
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $class_obj = new chat_window();
      
      $GUID = get_var('chatVisitor', FALSE, get_var('visitor', FALSE, NULL));
      $room_id = get_var('r', true);
                     
      if($class_obj->getiframe_heartbeat($GUID, $room_id) === FALSE) {
         html_output::error(0, 'Failed to get iframe for heartbeat!'); 
      }
      else {
         html_output::success();
      }
   }        
}