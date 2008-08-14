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

function print_remove_text($tagname, $instance) {
   echo "&nbsp;&nbsp;&nbsp;<a href=\"javascript: remove_text_value('" . $tagname . "','" . $instance . "');\">Remove Text Value</a>";
}

function print_add_text($tagname, $instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_text_value('" . $tagname . "','" . $instance . "');\">Add Text Value</a>";
}

function print_remove_node($tagname, $instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: remove_node('" . $tagname . "','" . $instance . "');\">Remove Node</a>";
}

function print_add_child($tagname, $instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_child('" . $tagname . "','" . $instance . "');\">Add Child</a>";
}

function print_text_textarea($tagname, $instance, $data) {
   echo "\n" . "<textarea name='child[" . $tagname . "][" . $instance . "][value]' rows=2 cols=50>";
   echo $data . "</textarea>";
}

function print_left_padder() {
   echo "\n" . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}

function print_add_attribute($tagname, $instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_attribute('" . $tagname . "','" . $instance . "');\">Add/Edit Attribute</a>";
}

function print_attribute($attribute_name, $attribute_value, $tagname, $instance) {
   echo " <a title='Remove Attribute' alt='Remove Attribute' class='remove_attribute' href=\"javascript: remove_attribute('" . $attribute_name . "','" . $tagname . "','" . $instance . "');\">" . $attribute_name . "</a>";
   if(strstr($attribute_value, "'") === FALSE) {
      echo "='" . $attribute_value . "'";
   }
   else {
      echo '="' . $attribute_value . '"';
   }
}

function print_remove_text2($tagname, $instance, $parent_tag, $parent_instance) {
   echo "&nbsp;&nbsp;&nbsp;<a href=\"javascript: remove_text_value2('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "');\">Remove Text Value</a>";
}

function print_add_text2($tagname, $instance, $parent_tag, $parent_instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_text_value2('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "');\">Add Text Value</a>";
}

function print_remove_node2($tagname, $instance, $parent_tag, $parent_instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: remove_node2('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "');\">Remove Node</a>";
}

function print_add_child2($tagname, $instance, $parent_tag, $parent_instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_child2('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "');\">Add Child</a>";
}

function print_text_textarea2($tagname, $instance, $data, $parent_tag, $parent_instance) {
   echo "\n" . "<textarea name='child2[" . $parent_tag . "][" . $parent_instance . "][" . $tagname . "][" . $instance . "][value]' rows=2 cols=50>";
   echo $data . "</textarea>";
}

function print_left_padder2() {
   echo "\n" . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}

function print_add_attribute2($tagname, $instance, $parent_tag, $parent_instance) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_attribute2('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "');\">Add/Edit Attribute</a>";
}

function print_attribute2($attribute_name, $attribute_value, $tagname, $instance, $parent_tag, $parent_instance) {
   echo " <a title='Remove Attribute' alt='Remove Attribute' class='remove_attribute' href=\"javascript: remove_attribute2('" . $attribute_name . "','" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "');\">" . $attribute_name . "</a>";
   if(strstr($attribute_value, "'") === FALSE) {
      echo "='" . $attribute_value . "'";
   }
   else {
      echo '="' . $attribute_value . '"';
   }
}

function print_remove_text3($tagname, $instance, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo "&nbsp;&nbsp;&nbsp;<a href=\"javascript: remove_text_value3('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "','" . $parent_tag2 . "','" . $parent_instance2 . "');\">Remove Text Value</a>";
}

function print_add_text3($tagname, $instance, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_text_value3('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "','" . $parent_tag2 . "','" . $parent_instance2 . "');\">Add Text Value</a>";
}

function print_remove_node3($tagname, $instance, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: remove_node3('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "','" . $parent_tag2 . "','" . $parent_instance2 . "');\">Remove Node</a>";
}

function print_add_child3($tagname, $instance, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: alert('Maybe in the future!'); \">Add Child</a>";
}

function print_text_textarea3($tagname, $instance, $data, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo "\n" . "<textarea name='child3[" . $parent_tag2 . "][" . $parent_instance2 . "][" . $parent_tag . "][" . $parent_instance . "][" . $tagname . "][" . $instance . "][value]' rows=2 cols=50>";
   echo $data . "</textarea>";
}

function print_left_padder3() {
   echo "\n" . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}

function print_add_attribute3($tagname, $instance, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo "\n" . "&nbsp;&nbsp;&nbsp;<a href=\"javascript: add_attribute3('" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "','" . $parent_tag2 . "','" . $parent_instance2 . "');\">Add/Edit Attribute</a>";
}

function print_attribute3($attribute_name, $attribute_value, $tagname, $instance, $parent_tag, $parent_instance, $parent_tag2, $parent_instance2) {
   echo " <a title='Remove Attribute' alt='Remove Attribute' class='remove_attribute' href=\"javascript: remove_attribute3('" . $attribute_name . "','" . $tagname . "','" . $instance . "','" . $parent_tag . "','" . $parent_instance . "','" . $parent_tag2 . "','" . $parent_instance2 . "');\">" . $attribute_name . "</a>";
   if(strstr($attribute_value, "'") === FALSE) {
      echo "='" . $attribute_value . "'";
   }
   else {
      echo '="' . $attribute_value . '"';
   }
}

function print_javascript() {
   echo "\n" . '<script language="javascript">
<!--
function add_child_root() {
  document.main.displaynext.value = "build";
  document.main.new_tag_name.value = prompt("What is the tag name for the new node?", "");
  document.main.command.value = "add_top_child";
  document.main.submit();
}
function add_text_value(tag, instance) {
  document.main.displaynext.value = "build";
  document.main.command.value = "add_text_value";
  document.main.tagname.value = tag;
  document.main.taginstance.value = instance;
  document.main.submit();
}
function remove_text_value(tag, instance) {
  document.main.displaynext.value = "build";
  document.main.command.value = "remove_text_value";
  document.main.tagname.value = tag;
  document.main.taginstance.value = instance;
  document.main.submit();
}
function remove_node(tag, instance) {
  document.main.displaynext.value = "build";
  document.main.command.value = "remove_node";
  document.main.tagname.value = tag;
  document.main.taginstance.value = instance;
  document.main.submit();
}
function add_child(tag, instance) {
  document.main.displaynext.value = "build";
  document.main.new_tag_name.value = prompt("What is the tag name for the new child node?", "");
  document.main.command.value = "add_child";
  document.main.tagname.value = tag;
  document.main.taginstance.value = instance;
  document.main.submit();
}

function add_attribute(tag, instance) {
  document.main.displaynext.value = "build";
  document.main.new_attribute_name.value = prompt("What is the name for the attribute add/edit?", "");
  document.main.new_attribute_value.value = prompt("What is the value you want to set for the attribute?", "");
  document.main.command.value = "add_attribute";
  document.main.tagname.value = tag;
  document.main.taginstance.value = instance;
  document.main.submit();
}

function remove_attribute(attribute, tag, instance) {
  document.main.displaynext.value = "build";
  document.main.attribute_name.value = attribute;
  document.main.command.value = "remove_attribute";
  document.main.tagname.value = tag;
  document.main.taginstance.value = instance;
  document.main.submit();
}
function add_text_value2(tag, instance, parent, pinstance) {
  document.main.displaynext.value = "build";
  document.main.command.value = "add_text_value2";
  document.main.tagname.value = parent;
  document.main.taginstance.value = pinstance;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
function remove_text_value2(tag, instance, parent, pinstance) {
  document.main.displaynext.value = "build";
  document.main.command.value = "remove_text_value2";
  document.main.tagname.value = parent;
  document.main.taginstance.value = pinstance;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
function remove_node2(tag, instance, parent, pinstance) {
  document.main.displaynext.value = "build";
  document.main.command.value = "remove_node2";
  document.main.tagname.value = parent;
  document.main.taginstance.value = pinstance;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
function add_child2(tag, instance, parent, pinstance) {
  document.main.displaynext.value = "build";
  document.main.new_tag_name.value = prompt("What is the tag name for the new child node?", "");
  document.main.command.value = "add_child2";
  document.main.tagname.value = parent;
  document.main.taginstance.value = pinstance;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}

function add_attribute2(tag, instance, parent, pinstance) {
  document.main.displaynext.value = "build";
  document.main.new_attribute_name.value = prompt("What is the name for the attribute add/edit?", "");
  document.main.new_attribute_value.value = prompt("What is the value you want to set for the attribute?", "");
  document.main.command.value = "add_attribute2";
  document.main.tagname.value = parent;
  document.main.taginstance.value = pinstance;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}

function remove_attribute2(attribute, tag, instance, parent, pinstance) {
  document.main.displaynext.value = "build";
  document.main.attribute_name.value = attribute;
  document.main.command.value = "remove_attribute2";
  document.main.tagname.value = parent;
  document.main.taginstance.value = pinstance;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
// Level 3
function add_text_value3(tag, instance, parent1, pinstance1, parent2, pinstance2) {
  document.main.displaynext.value = "build";
  document.main.command.value = "add_text_value3";
  document.main.tagname.value = parent2;
  document.main.taginstance.value = pinstance2;
  document.main.tagname2.value = parent1;
  document.main.taginstance2.value = pinstance1;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
function remove_text_value3(tag, instance, parent1, pinstance1, parent2, pinstance2) {
  document.main.displaynext.value = "build";
  document.main.command.value = "remove_text_value3";
  document.main.tagname.value = parent2;
  document.main.taginstance.value = pinstance2;
  document.main.tagname2.value = parent1;
  document.main.taginstance2.value = pinstance1;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
function remove_node3(tag, instance, parent1, pinstance1, parent2, pinstance2) {
  document.main.displaynext.value = "build";
  document.main.command.value = "remove_node3";
  document.main.tagname.value = parent2;
  document.main.taginstance.value = pinstance2;
  document.main.tagname2.value = parent1;
  document.main.taginstance2.value = pinstance1;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}
function add_child3(tag, instance, parent1, pinstance1, parent2, pinstance2) {
  document.main.displaynext.value = "build";
  document.main.new_tag_name.value = prompt("What is the tag name for the new child node?", "");
  document.main.command.value = "add_child3";
  document.main.tagname.value = parent2;
  document.main.taginstance.value = pinstance2;
  document.main.tagname2.value = parent1;
  document.main.taginstance2.value = pinstance1;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}

function add_attribute3(tag, instance, parent1, pinstance1, parent2, pinstance2) {
  document.main.displaynext.value = "build";
  document.main.new_attribute_name.value = prompt("What is the name for the attribute add/edit?", "");
  document.main.new_attribute_value.value = prompt("What is the value you want to set for the attribute?", "");
  document.main.command.value = "add_attribute3";
  document.main.tagname.value = parent2;
  document.main.taginstance.value = pinstance2;
  document.main.tagname2.value = parent1;
  document.main.taginstance2.value = pinstance1;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}

function remove_attribute3(attribute, tag, instance, parent1, pinstance1, parent2, pinstance2) {
  document.main.displaynext.value = "build";
  document.main.attribute_name.value = attribute;
  document.main.command.value = "remove_attribute3";
  document.main.tagname.value = parent2;
  document.main.taginstance.value = pinstance2;
  document.main.tagname2.value = parent1;
  document.main.taginstance2.value = pinstance1;
  document.main.childtagname.value = tag;
  document.main.childtaginstance.value = instance;
  document.main.submit();
}

function submit_save_changes() {
  document.main.command.value = "";
  document.main.displaynext.value = "build";
  document.main.submit();
}
function submit_create_xml() {
  document.main.command.value = "";
  document.main.displaynext.value = "createxml";
  document.main.submit();
}

// -->
</script>';
}

function print_style_header() {
   echo "<style>";
   echo 'a { text-decoration: none; }';
   echo 'a.remove_attribute { color: red; }';
   echo "</style>";
}

function save_text_value_changes() {
   global $xml_data;
   @$child = $_REQUEST["child"];
   if(is_array($child)) {
      foreach($child as $tag=>$array) {
         if(is_array($array)) {
            foreach($array as $instance=>$text_data) {
               $obj =& $xml_data->get_child($tag, $instance);
               $obj->set_data($text_data['value']);
            }
         }
      }
   }

   @$child2 = $_REQUEST["child2"];
   if(is_array($child2)) {
      foreach($child2 as $ptag=>$parray) {
         if(is_array($parray)) {
            foreach($parray as $pinstance=>$carray) {
               if(is_array($carray)) {
                  foreach($carray as $ctag=>$array) {
                     if(is_array($array)) {
                        foreach($array as $cinstance=>$text_data) {
                           $obj =& $xml_data->get_child($ptag, $pinstance);
                           $obj2 =& $obj->get_child($ctag, $cinstance);
                           $obj2->set_data($text_data['value']);
                        }
                     }
                  }
               }
            }
         }
      }
   }

   @$child3 = $_REQUEST["child3"];
   if(is_array($child3)) {
      foreach($child3 as $ptag=>$parray) {
         if(is_array($parray)) {
            foreach($parray as $pinstance=>$c1array) {
               if(is_array($c1array)) {
                  foreach($c1array as $c1tag=>$c1array) {
                     if(is_array($c1array)) {
                        foreach($c1array as $c1instance=>$c2array) {
                           if(is_array($c2array)) {
                              foreach($c2array as $c2tag=>$array) {
                                 if(is_array($array)) {
                                    foreach($array as $c2instance=>$text_data) {
                                       $obj =& $xml_data->get_child($ptag, $pinstance);
                                       $obj2 =& $obj->get_child($c1tag, $c1instance);
                                       $obj3 =& $obj2->get_child($c2tag, $c2instance);
                                       $obj3->set_data($text_data['value']);
                                    }
                                 }
                              }
                           }
                        }
                     }
                  }
               }
            }
         }
      }
   }
}