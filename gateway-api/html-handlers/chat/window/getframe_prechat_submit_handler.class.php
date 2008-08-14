<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/chat/window.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the pre-chat submit HTML
 *
 */
class getframe_prechat_submit_handler
{  
   /**
    * Class constructor
    *
    * @return getframe_prechat_submit_handler
    */
   function getframe_prechat_submit_handler() {
      // Nothing needed right now
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $class_obj = new chat_window();
      
      $GUID = get_var('visitor', TRUE);
      $dept_id = get_var('dept_id', TRUE);
      $visitor_name = get_var('visitor_name', TRUE);
      $visitor_question = get_var('visitor_question', TRUE);
                     
      if($class_obj->getframe_prechat_submit($GUID, $visitor_name, $visitor_question, $dept_id) === FALSE) {
         html_output::error(0, 'Failed to get pre-chat submit frame!'); 
      }
      else {
         html_output::success();
      }
   }        
}