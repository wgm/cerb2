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

require_once(FILESYSTEM_PATH . "gateway-api/functions/external.inc.php");

/**
 * xml_output class - Handles generation of XML from dynamically created arrays
 *
 */
class xml_output
{
   /**
    * This method takes a specifically formatted array and prints it out as XML
    *
    */
   function display() {
      session_write_close();
      $pretty_xml_output = get_var("pretty_output", FALSE, FALSE);
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      $object =& xml_output::get_instance();
      $xml .= $object->to_string($pretty_xml_output);      
      if(!headers_sent()) {
	   	header("HTTP/1.0 200 OK");
	   	header("Status: 200");
         header("Content-Type: text/xml");
         header("Content-Length: " . strlen(str_replace(chr(0)," ",$xml)));
      }
      print($xml);
   }

   /**
    * Sets the status flag on the output XML to error, forces output of the XML, and exits operation.
    *
    * @param int $error_code The error code of the corresponding error
    * @param string $error_msg The error message which corresponds to the error which occurred
    */
   function error($error_code = 0, $error_msg = NULL) {
      unset($GLOBALS['xml_output_object_singleton']);
      $xml =& xml_output::get_instance();
     
      $status =& $xml->get_child("status", 0);
      $status->set_data("error");
      
      $data =& $xml->get_child("data", 0);
      $data->add_child("error_msg", xml_object::create("error_msg", $error_msg));
      $data->add_child("error_code", xml_object::create("error_code", $error_code));
      
      xml_output::display();
      exit();
   }

   /**
    * Sets the status flag on the output XML to success
    *
    */
   function success($data = NULL) {
      $xml =& xml_output::get_instance();
      
      $status =& $xml->get_child("status", 0);
      $status->set_data("success");
   }

   /**
    * Loops of an array and builds an array ready for the XML display method.
    *
    * @param object $xml_object The XML object node to append the children onto
    * @param array $loop The array of data to loop over
    * @param string $ptag The parent tag of the XML block to generate
    * @param string $p_id_f The key of the element in the loop which holds the DB id number for the parent tag
    * @param array $children This is an array of arrays where the array holds three values. 1.) ctag = name of the child tag 2.) c_rev_f = the key of the element in the loop which holds the ID number for this child tag 3.) value_f = the key of the element in the loop which holds the value for this child.
    */  
   function build_xml_from_loop(&$xml_object, &$loop, $ptag, $p_id_f, $children = array(), $with_sync_attrib = TRUE) {
      if(!is_array($loop)) {
         return;
      }
      foreach($loop as $loop_item) {
         if($with_sync_attrib && isset($loop_item["sync"]) && !empty($loop_item["sync"])) {
            $attribs = array("id"=>$loop_item[$p_id_f], "sync"=>$loop_item["sync"]);
         }
         else {
            $attribs = array("id"=>$loop_item[$p_id_f]);
         }
         $xml =& $xml_object->add_child($ptag, xml_object::create($ptag, NULL, $attribs));
         foreach($children as $child) {
            //$data = str_replace("<br />", "<br>", nl2br($loop_item[$child["value_f"]]));
            $object =& xml_object::create($child["ctag"], $loop_item[$child["value_f"]]);
            if($child["ctag"] == "description") {
               $object->force_cdata = TRUE;
            }
            $xml->add_child($child["ctag"], $object);
            unset($object);
         }
      }
   }
   
   function &get_instance() {
      if(isset($GLOBALS['xml_output_object_singleton']) &&
         is_object($GLOBALS['xml_output_object_singleton']) && 
         is_a($GLOBALS['xml_output_object_singleton'], "xml_object") && 
         $GLOBALS['xml_output_object_singleton']->get_token() == "cerberus_xml") {
            return $GLOBALS['xml_output_object_singleton'];
      }
      else {
         $GLOBALS['xml_output_object_singleton'] =& new xml_object("cerberus_xml");
         $GLOBALS['xml_output_object_singleton']->add_child("status", xml_object::create("status"));
         $GLOBALS['xml_output_object_singleton']->add_child("version", xml_object::create("version", XML_COMM_VERSION_STRING));
         $GLOBALS['xml_output_object_singleton']->add_child("data", xml_object::create("data"));
         return $GLOBALS['xml_output_object_singleton'];
      }
   }
         
         
}
