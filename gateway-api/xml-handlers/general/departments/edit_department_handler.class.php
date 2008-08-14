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
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/departments.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles editing departments
 *
 */
class edit_department_handler extends xml_parser
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
    * @return edit_department_handler
    */
   function edit_department_handler(&$xml) {
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

      $name = $this->xml->get_child_data("name", 0);
      if(strlen($name) < 2) {
         xml_output::error(0, 'Department name should be atleast 2 characters long');
      }
      
      $id = $this->xml->get_child_data("department_id", 0);
      if(empty($id) || $id < 1) {
         xml_output::error(0, 'Invalid or missing department ID');
      }     

      $usage = $this->xml->get_child_data("usage", 0);
      $offline_address = $this->xml->get_child_data("offline_address", 0);

      $obj = new general_departments();

      if($obj->edit_department($id, $name, $usage, $offline_address) === FALSE) {
         xml_output::error(0, 'Failed to edit department');
      }
      else {
         xml_output::success();
      }
   }
}