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

echo "\n" . '<html><head>';
echo "\n" . '<title>XML Test - Step #1 - Create XML packet</title>';
print_style_header();
echo "\n" . '</head><body><script language="javascript" src="xml_test_md5.js"></script><h1>XML Test - Step #1 - Create XML packet (NEW)</h1><br />';
echo "\n" . '<form method="post" action="builder.php" name="main">';
echo "\n" . '<input type="hidden" name="form_action" value="submit"><input type="hidden" name="command" value="">';
echo "\n" . '<input type="hidden" name="displaynext" value="viewer"><input type="hidden" name="tagname" value="">';
echo "\n" . '<input type="hidden" name="taginstance" value=""><input type="hidden" name="new_tag_name" value="">';
echo "\n" . '<input type="hidden" name="new_attribute_name" value=""><input type="hidden" name="new_attribute_value" value="">';
echo "\n" . '<input type="hidden" name="attribute_name" value=""><input type="hidden" name="childtagname" value="">';
echo "\n" . '<input type="hidden" name="childtaginstance" value=""><input type="hidden" name="tagname2" value="">';
echo "\n" . '<input type="hidden" name="taginstance2" value="">';
echo "\n" . '<table border="0">';
echo "\n" . '<tr><td><strong>Channel:</strong></td><td><input type=text name="xml_channel" value="' . $xml->get_child_data('channel', 0) . '"></td><td>&nbsp;</td></tr>';
echo "\n" . '<tr><td><strong>Module:</strong></td><td><input type=text name="xml_module" value="' . $xml->get_child_data('module', 0) . '"></td><td>&nbsp;</td></tr>';
echo "\n" . '<tr><td><strong>Command:</strong></td><td><input type=text name="xml_command" value="' . $xml->get_child_data('command', 0) . '"></td><td><input type="submit" name="add_top_child" onClick="javascript: document.main.command.value = \'save_packet_header\'; return true;" value="Save packet header changes"></td></tr>';
echo "\n" . '</table>';
print_javascript();
echo "\n" . '<br /><strong>Data:</strong><br /><table cellpadding="3" cellspacing="1" bgcolor="#A3A3A3" width="100%">';

$top_children = $xml_data->get_children();
$top_children_count = 0;
if(is_array($top_children)) {
   foreach($top_children as $xml_tag_name=>$instance_array) {
      foreach($instance_array as $xml_instance=>$xml_item) {
         echo "\n" . "<tr bgcolor='#FFFFFF'><td colspan='3'>&lt;" . $xml_item->get_token();
         if(count($xml_item->get_attributes())) {
            foreach($xml_item->get_attributes() as $attribute_name=>$attribute_value) {
               print_attribute($attribute_name, $attribute_value, $xml_item->get_token(), $xml_instance);
            }
         }
         if($xml_item->get_data() === NULL && $xml_item->get_children() === FALSE) {
            echo " /&gt;";
         }
         else {
            echo "&gt;";
         }
         if($xml_item->get_data() !== NULL) {
            print_remove_text($xml_item->get_token(), $xml_instance);
         }
         else {
            print_add_text($xml_item->get_token(), $xml_instance);
         }
         print_add_attribute($xml_item->get_token(), $xml_instance);
         print_remove_node($xml_item->get_token(), $xml_instance);
         print_add_child($xml_item->get_token(), $xml_instance);
         if($xml_item->get_data() !== NULL) {
            echo "<br />";
            print_left_padder();
            print_text_textarea($xml_item->get_token(), $xml_instance, $xml_item->get_data());
         }
         echo "</td></tr>";
         if(count($xml_item->get_children())) {
            $children = $xml_item->get_children();
            if(is_array($children)) {
               foreach($children as $child_tagname => $child_instance_array) {
                  foreach($child_instance_array as $child_instance=>$child_item) {
                     echo "<tr bgcolor='#FFFFFF'><td bgcolor='#EEEEEE'>";
                     print_left_padder2();
                     echo "</td><td colspan='2'>&lt;" . $child_item->get_token();
                     if(count($child_item->get_attributes())) {
                        foreach($child_item->get_attributes() as $attribute_name=>$attribute_value) {
                           print_attribute2($attribute_name, $attribute_value, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                        }
                     }
                     if($child_item->get_data() === NULL && $child_item->get_children() === FALSE) {
                        echo " /&gt;";
                     }
                     else {
                        echo "&gt;";
                     }
                     if($child_item->get_data() !== NULL) {
                        print_remove_text2($child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                     }
                     else {
                        print_add_text2($child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                     }
                     print_add_attribute2($child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                     print_remove_node2($child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                     print_add_child2($child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                     if($child_item->get_data() !== NULL) {
                        echo "<br />";
                        print_left_padder2();
                        print_text_textarea2($child_item->get_token(), $child_instance, $child_item->get_data(), $xml_item->get_token(), $xml_instance);
                     }
                     if($child_item->get_data() !== NULL && $child_item->get_children() === FALSE) {
                        echo "<br />";
                        echo "&lt;/" . $child_item->get_token() . "&gt;";
                     }
                     echo "</td></tr>";
                     if(count($child_item->get_children())) {
                        $children2 = $child_item->get_children();
                        if(is_array($children2)) {
                           foreach($children2 as $child2_tagname => $child2_instance_array) {
                              foreach($child2_instance_array as $child2_instance=>$child2_item) {
                                 echo "<tr bgcolor='#FFFFFF'><td bgcolor='#EEEEEE'>";
                                 print_left_padder2();
                                 echo "</td><td bgcolor='#EEEEEE'>";
                                 print_left_padder2();
                                 echo "</td><td>&lt;" . $child2_item->get_token();
                                 if(count($child2_item->get_attributes())) {
                                    foreach($child2_item->get_attributes() as $attribute_name=>$attribute_value) {
                                       print_attribute3($attribute_name, $attribute_value, $child2_item->get_token(), $child2_instance, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                    }
                                 }
                                 if($child2_item->get_data() === NULL) {
                                    echo " /&gt;";
                                 }
                                 else {
                                    echo "&gt;";
                                 }
                                 if($child2_item->get_data() !== NULL) {
                                    print_remove_text3($child2_item->get_token(), $child2_instance, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                 }
                                 else {
                                    print_add_text3($child2_item->get_token(), $child2_instance, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                 }
                                 print_add_attribute3($child2_item->get_token(), $child2_instance, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                 print_remove_node3($child2_item->get_token(), $child2_instance, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                 print_add_child3($child2_item->get_token(), $child2_instance, $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                 if($child2_item->get_data() !== NULL) {
                                    echo "<br />";
                                    print_left_padder3();
                                    print_text_textarea3($child2_item->get_token(), $child2_instance, $child2_item->get_data(), $child_item->get_token(), $child_instance, $xml_item->get_token(), $xml_instance);
                                 }
                                 if($child2_item->get_data() !== NULL) {
                                    echo "<br />";
                                    echo "&lt;/" . $child2_item->get_token() . "&gt;";
                                 }
                                 echo "</td></tr>";
                              }
                           }
                        }
                     }
                     if($child_item->get_children() !== FALSE) {
                        echo "<tr bgcolor='#FFFFFF'><td bgcolor='#EEEEEE'>";
                        print_left_padder2();
                        echo "<td colspan='2'>&lt;/" . $child_item->get_token() . "&gt;</td></tr>";
                     }
                  }
               }
            }
            if($xml_item->get_data() !== NULL || $xml_item->get_children() !== FALSE) {
               echo "<tr bgcolor='#FFFFFF'><td colspan='3'>&lt;/" . $xml_item->get_token() . "&gt;</td></tr>";
            }
         }
      }
   }
}

echo "\n" . '</table><br /><br />';
echo "\n" . '<input type="hidden" name="cached_xml" value="' . base64_encode($xml->to_string()) . '">';
echo "\n" . '<input type="button" onClick="add_child_root();" value="Add top child">&nbsp;&nbsp;';
echo "\n" . '<input type="button" onClick="submit_save_changes();" value="Save Changes">&nbsp;&nbsp;';
echo "\n" . '<input type="button" onClick="submit_create_xml();" value="Create XML">';
echo "\n" . '</form>';
//echo htmlentities($xml->to_string());
echo "\n" . '</body></html>';
