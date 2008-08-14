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
 * Generic class container for XML data
 *
 */
class xml_object
{
   /**
    * The attributes from the XML tag
    *
    * @var array attributes
    */
   var $attributes = NULL;
   /**
    * Array of the children below this XML tag, keyed by name, then occurance.
    *
    * @var array children
    */
   var $children = NULL;
   /**
    * The data from within the tags.
    *
    * @var string data
    */
   var $data = NULL;
   /**
    * The XML token name
    *
    * @var string token
    */
   var $token = NULL;
   /**
    * Force data to be in a CDATA
    *
    * @var string force_cdata
    */
   var $force_cdata = FALSE;
   /**
    * Force data to not be in a CDATA
    *
    * @var string force_cdata
    */
   var $force_non_cdata = FALSE;
   /**
    * Object reference to the parent
    *
    * @var object parent
    */
   var $parent = FALSE;

   /**
    * Object Constructor. Presets the token, data, and attributes if provided.
    *
    * @param string $token The XML token
    * @param string $data The data from within the tokens
    * @param string $attributes The attributes from the start tag.
    * @return xml_object
    */
   function xml_object($token, $data = NULL, $attributes = NULL) {
      $this->token = $token;
      $this->data = $data;
      $this->attributes = $attributes;
   }

   function &get_attribute($attribute_name, $as_array = TRUE) {
      if(is_array($this->attributes) && array_key_exists($attribute_name, $this->attributes)) {
         if($as_array) {
            return array($attribute_name=>$this->attributes[$attribute_name]);
         }
         else {
            return $this->attributes[$attribute_name];
         }
      }
      else {
         return FALSE;
      }
   }

   function &get_attributes() {
      return $this->attributes;
   }

   function add_attribute($attribute_name, $value, $overwrite = TRUE) {
      if(!$overwrite && array_key_exists($attribute_name, $this->attributes)) {
      		//if($attribute_name=="href") echo "case1";
         return FALSE;
      }
      if(!is_array($this->attributes)) {
            //if($attribute_name=="href") echo "case2";
         return FALSE;
      }
            //if($attribute_name=="href") echo "case3";
      $this->attributes[$attribute_name] = $value;
      return TRUE;
   }

   function remove_attribute($attribute_name) {
      if(is_array($this->attributes) && array_key_exists($attribute_name, $this->attributes)) {
         unset($this->attributes[$attribute_name]);
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function &get_child($token, $instance = NULL) {
      if(is_array($this->children) && array_key_exists($token, $this->children)) {
         if(is_null($instance)) {
            return $this->children[$token];
         }
         elseif(is_integer($instance) && array_key_exists($instance, $this->children[$token])) {
            return $this->children[$token][$instance];
         }
         else {
            return FALSE;
         }
      }
      else {
         return FALSE;
      }
   }
   
   function &get_child_data($token, $instance = 0, $start = 0, $end = NULL) {
      if(is_array($this->children) && array_key_exists($token, $this->children)) {
         if(is_integer($instance) && array_key_exists($instance, $this->children[$token])) {
            return $this->children[$token][$instance]->get_data($start, $end);
         }
         else {
            return FALSE;
         }
      }
      else {
         return FALSE;
      }
   }

   function &get_children() {
      if(is_array($this->children) && count($this->children) > 0) {
         return $this->children;
      }
      else {
         return FALSE;
      }
   }

   function &add_child($token, &$object) {
      $object->set_parent($this);
      $this->children[$token][] =& $object;
      return $object;
   }
   
   function remove_child($token, $instance) {
      unset($this->children[$token][$instance]);
   }
   
   function set_parent(&$parent) {
      $this->parent =& $parent;
   }
   
   function &get_parent() {
      return $this->parent;
   }

   function &create($token, $data = NULL, $attributes = NULL) {
      return new xml_object($token, $data, $attributes);
   }

   function get_token() {
      return $this->token;
   }
   
   function has_children() {
      return (is_array($this->children) && count($this->children) > 0);
   }

   function to_string($pretty = FALSE, $position = 0) {
      if($pretty) {
         $padding = str_repeat(" ", $position*3);
         $ret = "\n";
      }
      else {
         $padding = "";
         $ret = "";
      }
      $ret .= $padding . "<" . $this->token;
      if(is_array($this->attributes) && count($this->attributes) > 0) {
         foreach($this->attributes as $attribute=>$value) {
            if(strstr($value, "'") === FALSE) {
               $ret .= " " . $attribute . "='" . $value . "'";
            }
            else {
               $ret .= " " . $attribute . '="' . $value . '"';
            }
         }
      }
      if((is_array($this->children) && count($this->children) > 0) || $this->data !== NULL) {
         $ret .= ">";
         if(is_array($this->children) && count($this->children) > 0) {
            foreach($this->children as $token=>$instances) {
               foreach($instances as $instance=>$child) {
                  if(is_object($child)) {
                     $ret .= $child->to_string($pretty, $position+1);
                  }
               }
            }
            if($pretty) {
               $ret .= "\n";
            }
         }
         if($this->data !== NULL) {
            if($this->force_cdata && strstr($this->data, "]]>") === FALSE) {
            	$ret .= "<![CDATA[" . $this->data . "]]>";
            }
            elseif($this->force_non_cdata) {
            	$ret .= $this->data;
            }
            else {
               $ret .= str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("&", "&amp;", $this->data)));
            }
         }
         else {
            if($pretty) {
               $ret .= $padding;
            }
         }
         $ret .= "</" . $this->token . ">";
      }
      else {
         $ret .= " />";
      }
      return $ret;
   }

   function set_data($data = NULL, $force_non_cdata = FALSE) {
      $this->data = $data;
      if($force_non_cdata) {
         $this->force_non_cdata = TRUE;
      }
   }
   
   function append_data($data = NULL) {
      $this->data .= $data;
   }
   
   function get_data($start = 0, $end = NULL) {
      if($this->data === NULL || strlen($this->data) == 0) {
         return $this->data;
      }
      if(is_null($end)) {
         $end = strlen($this->data);
      }
      return substr($this->data, $start, $end);
   }
   
   function get_data_trim($start = 0, $end = NULL) {
      return trim($this->get_data($start, $end));
   }
}
