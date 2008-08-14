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
class viewtest_handler extends xml_parser
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
    * @return viewtest_handler
    */
   function viewtest_handler(&$xml) {
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

      $view_type = $this->xml->get_child_data("type", 0);
      if(empty($view_type) || is_null($view_type)) $view_type = 'basic';

      $limit = $this->xml->get_child_data("limit", 0);
      if(!is_numeric($limit) || $limit < 0) {
         $limit = 50;
      }

      $page = $this->xml->get_child_data("page", 0);
      if(!is_numeric($page) || $page < 0) {
         $page = 0;
      }

      $order_by_field = $this->xml->get_child_data("order_by_field", 0);
      if(strlen($order_by_field) < 1) {
         $order_by_field = 'ticket_id';
      }

      $order_by_direction = $this->xml->get_child_data("order_by_direction", 0);
      if($order_by_direction != 'ASC' && $order_by_direction != 'DESC') {
         $order_by_direction = 'DESC';
      }

      switch($view_type) {
         case 'search': {
            $id = $this->xml->get_child_data("search_id", 0);
            break;
         }
         case 'queue': {
            $id = $this->xml->get_child_data("queue_id", 0);
            break;
         }
         default: {
            $id = 0;
         }
      }

      if($views_obj->load_view($view_type, $limit, $page, $order_by_field, $order_by_direction, $id) === FALSE) {
         xml_output::error(0, 'Get view data from DB failed');
      }
      elseif($views_obj->build_view($view_type) === FALSE) {
         xml_output::error(0, 'Building of view as XML failed');
      }
      else {
         xml_output::success();
      }
   }
}