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

if(!defined('VALID_INCLUDE')) exit();

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

/**
 * xml_parser class - Parses XML data in a variety of forms (designed to be called statically or to be extended by another class)
 *
 */
class xml_parser
{
   /**
   * @return string
   * @param string $xml_data  xml data to parse
   * @param string $token xml element to find
   * @param bool $fail to abort if not found
   * @param bool $cdata_strip to strip cdata tags from returned data
   * @param bool $return_attributes whether to return tag attributes or not
   * @param bool $attributes_as_str FALSE = return as array (default), TRUE = return as unparsed string
   * @desc This function parses the given XML looking for the specified token and returns the first instance
   */
   function parse($xml_data, $token, $fail = TRUE, $cdata_strip = TRUE, $return_attributes = FALSE, $attributes_as_str = FALSE) {
      list($xml_data, $replacements) = xml_parser::encode_cdatas($xml_data);
      if($return_attributes) {
         $regexp = sprintf('/<%1$s([^>]*)((>(.*?)<\/%1$s>)|\s?\/>)/is', $token);
      }
      else {
         $regexp = sprintf('/<%1$s((>(.*?)<\/%1$s>)|\s?\/>)/is', $token);
      }
      if(preg_match($regexp, $xml_data, $matches) === 0) {
         if($fail) {
            xml_output::error("2", "Bad XML Packet - Token not found: " . $token);
            exit();
         }
         else {
            return FALSE;
         }
      }
      if($return_attributes) {
         $string = trim($matches[4]);
         $attributes = $matches[1];
      }
      else {
         $string = trim($matches[3]);
      }
      $string = xml_parser::decode_cdatas($string, $replacements);
      if($cdata_strip && substr($string, 0, 9) == "<![CDATA[") {
         $string = substr(substr($string, 0, -3), 9);
      }
      if($return_attributes) {
         if(!$attributes_as_str) {
            $attributes = xml_parser::parse_attributes($attributes);
         }
         return array("data"=>trim($string), "attributes"=>$attributes);
      }
      else {
         return trim($string);
      }
   } // End function parse();

   /**
   * @return array
   * @param string $xml_data  xml data to parse
   * @param string $token  xml element to find
   * @param bool $fail to abort if not found
   * @param bool $cdata_strip to strip cdata tags from returned data
   * @param bool $return_attributes whether to return tag attributes or not
   * @param bool $attributes_as_str FALSE = return as array (default), TRUE = return as unparsed string
   * @desc This function parses the given XML looking for the specified token and returns all instances as an array
   */
   function parse_multiple($xml_data, $token, $fail = TRUE, $cdata_strip = TRUE, $return_attributes = FALSE, $attributes_as_str = FALSE) {
      list($xml_data, $replacements) = xml_parser::encode_cdatas($xml_data);
      if($return_attributes) {
         $regexp = sprintf('/<%1$s([^>]*)((>(.*?)<\/%1$s>)|\s?\/>)/is', $token);
      }
      else {
         $regexp = sprintf('/<%1$s((>(.*?)<\/%1$s>)|\s?\/>)/is', $token);
      }
      if(preg_match_all($regexp, $xml_data, $matches, PREG_PATTERN_ORDER) == 0) {
         if($fail) {
            xml_output::error("2", "Bad XML Packet - Token(s) not found: " . $token);
            exit();
         }
         else {
            return FALSE;
         }
      }
      if($return_attributes) {
         $tmp_data = $matches[4];
         $attr = $matches[1];
      }
      else {
         $tmp_data = $matches[3];
      }
      $data = array();
      $attributes = array();
      foreach($tmp_data as $key=>$string) {
         $string = xml_parser::decode_cdatas($string, $replacements);
         if($cdata_strip && substr($string, 0, 9) == "<![CDATA[") {
            $string = substr(substr($string, 0, -3), 9);
         }
         $data[] = trim($string);
         if(!$attributes_as_str && $return_attributes) {
            $attributes[$key] = xml_parser::parse_attributes($attr[$key]);
         }
         elseif($attributes_as_str && $return_attributes) {
            $attributes[$key] = $attr[$key];
         }
      }
      if($return_attributes) {
         return array("data"=>$data, "attributes"=>$attributes);
      }
      else {
         return $data;
      }
   } // End function parse_multiple();

   /**
   * @return string
   * @param string $xml_data  xml data to parse
   * @param string $token xml element to find
   * @param bool $fail to abort if not found
   * @param bool $cdata_strip to strip cdata tags from returned data
   * @desc This function parses the given XML looking for an unknown token and returns the first instance
   */
   function parse_unknown($xml_data, $fail = TRUE, $cdata_strip = TRUE) {
      list($xml_data, $replacements) = xml_parser::encode_cdatas($xml_data);
      $regexp = '/<([\w|-|\.|:]+)([^\/>]*)((>(.*?)<\/\1>)|\s?\/>)/is';
      if(preg_match($regexp, $xml_data, $matches) === 0) {
         if($fail) {
            xml_output::error("3", "Bad XML Packet - Token not found");
         }
         else {
            return FALSE;
         }
      }
      $string = trim($matches[5]);
      $attributes = $matches[2];
      $token = $matches[1];
      $string = xml_parser::decode_cdatas($string, $replacements);
      if($cdata_strip && substr($string, 0, 9) == "<![CDATA[") {
         $string = substr(substr($string, 0, -3), 9);
      }
      return array("token"=>$token, "data"=>trim($string), "attributes"=>$attributes);
   } // End function parse_unknown();

   /**
   * @return array
   * @param string $xml_data  xml data to parse
   * @param string $token  xml element to find
   * @param bool $fail to abort if not found
   * @param bool $cdata_strip to strip cdata tags from returned data
   * @desc This function parses the given XML looking for an unknown token and returns all instances as an array
   */
   function parse_unknown_multiple($xml_data, $fail = TRUE, $cdata_strip = TRUE) {
      list($xml_data, $replacements) = xml_parser::encode_cdatas($xml_data);
      $regexp = '/<([\w|-|\.|:]+)([^\/>]*)((>(.*?)<\/\1>)|\s?\/>)/is';
      if(preg_match_all($regexp, $xml_data, $matches, PREG_PATTERN_ORDER) == 0) {
         if($fail) {
            xml_output::error("3", "Bad XML Packet - Token(s) not found");
            exit();
         }
         else {
            return FALSE;
         }
      }
      $tokens = $matches[1];
      $attributes = $matches[2];
      $tmp_data = $matches[5];
      foreach($tmp_data as $string) {
         $string = xml_parser::decode_cdatas($string, $replacements);
         if($cdata_strip && substr($string, 0, 9) == "<![CDATA[") {
            $string = substr(substr($string, 0, -3), 9);
         }
         $data[] = trim($string);
      }
      return array("tokens"=>$tokens, "data"=>$data, "attributes"=>$attributes);
   } // End function parse_unknown_multiple();


   /**
    * Parses an XML packet into an object.
    *
    * @param string $xml_data XML input string
    * @return object XML data object
    */
   function parse_as_object($xml_data) {
      $first_pass = xml_parser::parse_unknown_multiple($xml_data, FALSE, FALSE);
      if($first_pass === FALSE) {
         return NULL;
      }
      $tokens = $first_pass["tokens"];
      foreach($tokens as $key=>$token) {
         $data = xml_parser::parse_unknown_multiple($first_pass["data"][$key], FALSE, FALSE);
         $attributes = xml_parser::parse_attributes($first_pass["attributes"][$key]);
         $string = trim($first_pass["data"][$key]);
         if($data === FALSE || substr($string, 0, 9) == "<![CDATA[") {
            if(substr($string, 0, 9) == "<![CDATA[") {
               $string = substr(substr($string, 0, -3), 9);
            }
            $ret[$token][] =& new xml_object($token, $string, $attributes);
         }
         else {
            $tmp =& new xml_object($token);
            $tmp->attributes = $attributes;
            $tmp->children = xml_parser::parse_as_object($first_pass["data"][$key]);
            $ret[$token][] =& $tmp;
         }
      }
      return $ret;
   } // End function parse_as_object();

   /**
    * Parses the attributes from a string into an array
    *
    * @param string $attr_string the attributes from the XML tag
    * @return array the parsed array of attributes
    */
   function parse_attributes($attr_string) {
      if(empty($attr_string)) {
         return NULL;
      }
      $regexp = '/(\s*[\w]*)(=?)("[^<&"]*")?(\'[^<&\']*\')?/is';
      if(preg_match_all($regexp, $attr_string, $matches, PREG_PATTERN_ORDER) == 0) {
         return NULL;
      }
      else {
         $ret = array();
         foreach($matches[1] as $key=>$name) {
            $name = trim($name);
            if(!empty($name)) {
               $value = (!empty($matches[3][$key])) ? trim($matches[3][$key]) : trim($matches[4][$key]);
               $ret[$name] = ($matches[2][$key] == "=") ? substr($value, 1, -1) : '';
            }
         }
         return $ret;
      }
   } // End function parse_attributes();

   /**
     * Base64 encodes the CDATA's so they won't trigger false hits in the parser
     *
     * @param string $xml
     * @return array encoded XML and the array of replacements
     */
   function encode_cdatas($xml) {
      $regexp = '/<!\[CDATA\[(.*?)]]>/is';
      if(preg_match_all($regexp, $xml, $matches) == 0) {
         return array($xml, NULL);
      }
      foreach($matches[1] as $cdata) {
         $xml = str_replace($cdata, base64_encode($cdata), $xml);
         $replacements[] = $cdata;
      }
      return array($xml, $replacements);
   }

   /**
     * Decodes the base64 encoded CDATA's using the replacements array.
     *
     * @param string $xml XML w/ base64 encoded CDATA's
     * @param array $replacements array of CDATA's to replace
     * @return string XML w/ CDATA's as normal
     */
   function decode_cdatas($xml, $replacements) {
      if(!is_array($replacements) || count($replacements) == 0) {
         return $xml;
      }
      foreach($replacements as $cdata) {
         $xml = str_replace(base64_encode($cdata), $cdata, $xml);
      }
      return $xml;
   }

   /**
     * Recurses through a parsed XML object looking for tags w/ an attribute of "action"
     *
     * @param object $xml_object Parsed XML object
     * @param string $parent_token Name of the parent token
     * @param string $parent_attribs Attributes of the parent token
     * @return array Array of objects w/ action attribute
     */
   function find_actions($xml_object, $parent_token = NULL, $parent_attribs = array()) {
      $ret = array();
      if(is_array($xml_object->attributes) && array_key_exists("sync", $xml_object->attributes)) {
         $ret[] = array("action"=>$xml_object->attributes["sync"], "parent_token"=>$parent_token, "parent_attribs"=>$parent_attribs, "xml_object"=>$xml_object);
      }
      else {
         if(is_array($xml_object->children)) {
            foreach($xml_object->children as $child_token=>$child_array) {
               foreach($child_array as $occurance=>$child_object) {
                  $ret = array_merge(xml_parser::find_actions($child_object, $xml_object->token, $xml_object->attributes), $ret);
               }
            }
         }
      }
      return $ret;
   }

   function &expat_parse($data) {
      $this->parser = xml_parser_create();
      xml_set_object($this->parser, $this);
      xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, FALSE);
      xml_set_element_handler($this->parser, "expat_tag_open", "expat_tag_close");
      xml_set_character_data_handler($this->parser, "expat_cdata");
      $this->xml_object =& xml_object::create("root");
      $this->current =& $this->xml_object;
      xml_parse($this->parser, $data);
      return $this->xml_object;
   }

   function expat_tag_open($parser, $tag, $attributes) {
      $this->current =& $this->current->add_child($tag, xml_object::create($tag, NULL, $attributes));
   }

   function expat_cdata($parser, $cdata) {
      $this->current->append_data($cdata);
   }

   function expat_tag_close($parser, $tag) {
      $this->current =& $this->current->get_parent();
   }
}