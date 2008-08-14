<?php
/***********************************************************************
| MajorCRM (tm) developed by WebGroup Media, LLC.
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

$command = get_var("command");
$tagname = get_var("tagname");
$taginstance = intval(get_var("taginstance"));
$tagname2 = get_var("tagname2");
$taginstance2 = intval(get_var("taginstance2"));
$childtagname = get_var("childtagname");
$childtaginstance = intval(get_var("childtaginstance"));
$new_tag_name = get_var("new_tag_name");
$new_attribute_name = get_var("new_attribute_name");
$new_attribute_value = get_var("new_attribute_value");
$attribute_name = get_var("attribute_name");

save_text_value_changes();

if($command == "save_packet_header") {
   $xml_command = get_var("xml_command", FALSE, FALSE);
   if($xml_command !== FALSE && !empty($xml_command)) {
      $obj =& $xml->get_child("command", 0);
      $obj->set_data($xml_command);
   }

   $xml_module = get_var("xml_module", FALSE, FALSE);
   if($xml_module !== FALSE && !empty($xml_module)) {
      $obj =& $xml->get_child("module", 0);
      $obj->set_data($xml_module);
   }

   $xml_channel = get_var("xml_channel", FALSE, FALSE);
   if($xml_channel !== FALSE && !empty($xml_channel)) {
      $obj =& $xml->get_child("channel", 0);
      $obj->set_data($xml_channel);
   }
}
elseif($command == "add_top_child") {
   if(strlen($new_tag_name) > 0) {
      $xml_data->add_child($new_tag_name, xml_object::create($new_tag_name));
   }
}
elseif($command == "add_child") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   if(strlen($new_tag_name) > 0) {
      $obj->add_child($new_tag_name, xml_object::create($new_tag_name));
   }
}
elseif($command == "add_child2") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($childtagname, $childtaginstance);
   if(strlen($new_tag_name) > 0) {
      $obj2->add_child($new_tag_name, xml_object::create($new_tag_name));
   }
}
elseif($command == "add_attribute") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   if(strlen($new_attribute_name) > 0) {
      $obj->add_attribute($new_attribute_name, $new_attribute_value);
   }
}
elseif($command == "add_attribute2") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($childtagname, $childtaginstance);
   if(strlen($new_attribute_name) > 0) {
      $obj2->add_attribute($new_attribute_name, $new_attribute_value);
   }
}
elseif($command == "add_attribute3") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($tagname2, $taginstance2);
   $obj3 =& $obj2->get_child($childtagname, $childtaginstance);
   if(strlen($new_attribute_name) > 0) {
      $obj3->add_attribute($new_attribute_name, $new_attribute_value);
   }
}
elseif($command == "remove_attribute") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   if(strlen($attribute_name) > 0) {
      $obj->remove_attribute($attribute_name);
   }
}
elseif($command == "remove_attribute2") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($childtagname, $childtaginstance);
   if(strlen($attribute_name) > 0) {
      $obj2->remove_attribute($attribute_name);
   }
}
elseif($command == "remove_attribute3") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($tagname2, $taginstance2);
   $obj3 =& $obj2->get_child($childtagname, $childtaginstance);
   if(strlen($attribute_name) > 0) {
      $obj3->remove_attribute($attribute_name);
   }
}
elseif($command == "add_text_value") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj->set_data(' ');
}
elseif($command == "add_text_value2") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($childtagname, $childtaginstance);
   $obj2->set_data(' ');
}
elseif($command == "add_text_value3") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($tagname2, $taginstance2);
   $obj3 =& $obj2->get_child($childtagname, $childtaginstance);
   $obj3->set_data(' ');
}
elseif($command == "remove_text_value") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj->set_data();
}
elseif($command == "remove_text_value2") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($childtagname, $childtaginstance);
   $obj2->set_data();
}
elseif($command == "remove_text_value3") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($tagname2, $taginstance2);
   $obj3 =& $obj2->get_child($childtagname, $childtaginstance);
   $obj3->set_data();
}
elseif($command == "remove_node") {
   $xml_data->remove_child($tagname, $taginstance);
}
elseif($command == "remove_node2") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj->remove_child($childtagname, $childtaginstance);
}
elseif($command == "remove_node3") {
   $obj =& $xml_data->get_child($tagname, $taginstance);
   $obj2 =& $obj->get_child($tagname2, $taginstance2);
   $obj2->remove_child($childtagname, $childtaginstance);
}

