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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/views.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting view data
 *
 */
class get_contents_handler extends xml_parser
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
    * @return get_contents_handler
    */
   function get_contents_handler(&$xml) {
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
      
      $views_obj =& new email_views();
          
      $tickets = array();
           
      $tickets_obj =& $this->xml->get_child('tickets', 0);
      $children = $tickets_obj->get_children();
      if(is_array($children['ticket'])) {
         foreach($children['ticket'] as $key=>$ticket_obj) {
            $thread_id = $ticket_obj->get_attribute('max_thread_id', FALSE);
            $tickets[$ticket_obj->get_attribute('id', FALSE)] = ($thread_id > 0) ? $thread_id : 0;
         }
      }
                     
      if($views_obj->get_contents($tickets) === FALSE) {
         xml_output::error(0, 'Get ticket headers failed'); 
      }
      else {
         xml_output::success();
      }
   }        
}