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

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles logins
 *
 */
class logout_handler extends xml_parser
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
    * @return logout_handler
    */
   function logout_handler(&$xml) {
      $this->xml =& $xml;
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      $users_obj = new general_users();
                     
      if($users_obj->logout() === FALSE) {
         xml_output::error(0, 'Unable to logout!'); 
      }
      else {
         xml_output::success();
      }
   }        
}