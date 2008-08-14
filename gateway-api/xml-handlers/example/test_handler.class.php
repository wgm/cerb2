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

/**
 * This class is an example test handler
 *
 */
class test_handler extends xml_parser
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
    * @return update_handler
    */
   function test_handler(&$xml) {
      $this->xml =& $xml;
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
      // $var1 = $this->xml->get_child_data("var1", 0);
      // $var2 = $this->xml->get_child_data("var2", 0);

      // do something with the data and if good do a:
      // xml_output::success();
      // otherwise if you fail do a
      // xml_output::error(int error_code, string error_msg);
   }   
}