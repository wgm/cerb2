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

/**
 * html_output class - Handles generation of html
 *
 */
class html_output
{
   /**
    * This method takes the html object and prints it out
    *
    */
   function display() {
      $html =& html_output::get_instance();
      print($html->to_string());
   }

   /**
    * Sets the HTML content to the error message, forces output of the html, and exits operation.
    *
    * @param int $error_code The error code of the corresponding error
    * @param string $error_msg The error message which corresponds to the error which occurred
    */
   function error($error_code = 0, $error_msg = NULL) {
      $html =& html_output::get_instance();
      $html->set_content(sprintf("<br /><br /><b>Internal Error %d:</b> %s", $error_code, $error_msg));
      $html->finalize_output();
      html_output::display();
      exit();
   }

   /**
    * Sets the status flag on the output html to success
    *
    */
   function success($data = NULL) {
      $html =& html_output::get_instance();
      $html->finalize_output();
   }
  
   function &get_instance() {
      if(isset($GLOBALS['html_output_object_singleton']) &&
         is_object($GLOBALS['html_output_object_singleton']) && 
         is_a($GLOBALS['html_output_object_singleton'], "html_object")) {
            return $GLOBALS['html_output_object_singleton'];
      }
      else {
         $GLOBALS['html_output_object_singleton'] =& new html_object();
         return $GLOBALS['html_output_object_singleton'];
      }
   }
         
         
}
