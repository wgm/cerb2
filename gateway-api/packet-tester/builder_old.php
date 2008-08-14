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

define("FILESYSTEM_PATH", dirname(__FILE__) . "/../../");
define("VALID_INCLUDE", 1);

// Remove any PHP notices so they don't break output
error_reporting(E_ALL & ~E_NOTICE);

// This is actually not very efficient (potentially does a lot of loops over $_REQUEST)
// But it works and we shouldn't really be building that big of XML test packets anyway.
// Even with 20 top level data nodes each with 10 children, there was no noticeable delay
// in page load, so I doubt the inefficiency will ever be an issue.

$channel = isset($_REQUEST['channel']) ? $_REQUEST['channel'] : '';
$module = isset($_REQUEST["module"]) ? $_REQUEST["module"] : '';
$command = isset($_REQUEST["command"]) ? $_REQUEST["command"] : '';

$top_children = isset($_REQUEST["top_children"]) ? $_REQUEST["top_children"] : '0';

$child = array();

foreach($_REQUEST as $request_key=>$request_value) {
   $matches = array();
   if(preg_match("/^child(\d+)_name$/", $request_key, $matches)) {
      $i = $matches[1];
      $delete_child = isset($_REQUEST["child" . $i . "_delete"]) ? $_REQUEST["child" . $i . "_delete"] : '';
      if($delete_child == "Delete") {
         $top_children--;
         continue;
      }
      $child[$i]["name"] = isset($_REQUEST["child" . $i . "_name"]) ? $_REQUEST["child" . $i . "_name"] : '';
      $child[$i]["value"] = isset($_REQUEST["child" . $i . "_value"]) ? $_REQUEST["child" . $i . "_value"] : '';
      $child[$i]["children"] = isset($_REQUEST["child" . $i . "_children"]) ? $_REQUEST["child" . $i . "_children"] : '0';
      foreach($_REQUEST as $request_key2=>$request_value2) {
         $matches = array();
         if(preg_match("/^child" . $i . "_child(\d+)_name$/", $request_key2, $matches)) {
            $j = $matches[1];
            $delete_child = isset($_REQUEST["child" . $i . "_child" . $j . "_delete"]) ? $_REQUEST["child" . $i . "_child" . $j . "_delete"] : '';
            if($delete_child == "Delete") {
               $child[$i]["children"]--;
               continue;
            }
            $child[$i]["child"][$j]["name"] = isset($_REQUEST["child" . $i . "_child" . $j . "_name"]) ? $_REQUEST["child" . $i . "_child" . $j . "_name"] : '';
            $child[$i]["child"][$j]["value"] = isset($_REQUEST["child" . $i . "_child" . $j . "_value"]) ? $_REQUEST["child" . $i . "_child" . $j . "_value"] : '';
            $child[$i]["child"][$j]["children"] = isset($_REQUEST["child" . $i . "_child" . $j . "_children"]) ? $_REQUEST["child" . $i . "_child" . $j . "_children"] : '0';
            foreach($_REQUEST as $request_key3=>$request_value3) {
               $matches = array();
               if(preg_match("/^child" . $i . "_child" . $j . "_child(\d+)_name$/", $request_key3, $matches)) {
                  $k = $matches[1];
                  $delete_child = isset($_REQUEST["child" . $i . "_child" . $j . "_child" . $k . "_delete"]) ? $_REQUEST["child" . $i . "_child" . $j . "_child" . $k . "_delete"] : '';
                  if($delete_child == "Delete") {
                     $child[$i]["child"][$j]["children"]--;
                     continue;
                  }
                  $child[$i]["child"][$j]["child"][$k]["name"] = isset($_REQUEST["child" . $i . "_child" . $k . "_child" . $j . "_name"]) ? $_REQUEST["child" . $i . "_child" . $j . "_child" . $k . "_name"] : '';
                  $child[$i]["child"][$j]["child"][$k]["value"] = isset($_REQUEST["child" . $i . "_child" . $k . "_child" . $j . "_value"]) ? $_REQUEST["child" . $i . "_child" . $j . "_child" . $k . "_value"] : '';
               }
            }
            $add_child = isset($_REQUEST["child" . $i . "_child" . $j . "_add_child"]) ? $_REQUEST["child" . $i . "_child" . $j . "_add_child"] : '';
            if($add_child == "Add Sub Child") {
               $child[$i]["child"][$j]["children"]++;
               $child[$i]["child"][$j]["child"][] = array("name"=>'', "value"=>'');
            } 
         }
      }
      $add_child = isset($_REQUEST["child" . $i . "_add_child"]) ? $_REQUEST["child" . $i . "_add_child"] : '';
      if($add_child == "Add Sub Child") {
         $child[$i]["children"]++;
         $child[$i]["child"][] = array("name"=>'', "value"=>'');
      }
   }
}

$add_top_child = isset($_REQUEST["add_top_child"]) ? $_REQUEST["add_top_child"] : '';
if($add_top_child == "Add top child") {
   $top_children++;
   $child[] = array("value"=>'', "children"=>0);
}

$send_xml = isset($_REQUEST["send_xml"]) ? $_REQUEST["send_xml"] : '';
if($send_xml == "Create XML") {
   require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
   echo "<html><head><title>XML Test - Step #2 - Review XML packet</title></head><body><h1>XML Test - Step #2 - Review XML packet</h1><br />";
   echo "<form action='../../gateway.php' method='post' target='result'>";
   echo "<textarea name='xml' cols=80 rows=10>";
   $xml_output_object_singleton =& xml_object::create("cerberus_xml");
   $xml_output_object_singleton->add_child("channel", xml_object::create("channel", $channel));
   $xml_output_object_singleton->add_child("module", xml_object::create("module", $module));
   $xml_output_object_singleton->add_child("command", xml_object::create("command", $command));
   $data =& $xml_output_object_singleton->add_child("data", xml_object::create("data"));
   foreach($child as $xml_node) {
      if($xml_node['name'] != '') {
         if($xml_node["children"] == 0) {
            $data->add_child($xml_node['name'], xml_object::create($xml_node['name'], $xml_node['value']));
         }
         elseif(is_array($xml_node['child'])) {
            foreach($xml_node['child'] as $xml_sub) {
               if($xml_sub['name'] != '') {
                  if($xml_sub["children"] == 0) {
                     $data->add_child($xml_sub['name'], xml_object::create($xml_sub['name'], $xml_sub['value']));
                  }
                  elseif(is_array($xml_sub['child'])) {
                     foreach($xml_sub['child'] as $xml_sub2) {
                        $obj =& $data->get_child($xml_sub['name'], 0);
                        if($obj === FALSE) {
                           unset($obj);
                           $obj =& $data->add_child($xml_sub['name'], xml_object::create($xml_sub['name']));
                        }
                        $obj->add_child($xml_sub2['name'], xml_object::create($xml_sub2['name'], $xml_sub2['value']));
                        unset($obj);
                     }
                  }
               }
            }
         }
      }
   }
   xml_output::display();
   echo "</textarea><br /><br />";
   echo "<input type=submit value='Submit to Gateway'>&nbsp;&nbsp;&nbsp;";
   echo "<input type=button value='Go back' onclick=\"javascript: window.location.href='builder_old.php';\">";
   echo "</form></body></html>";
   exit();
}
            
?>
<html>
<head>
<title>XML Test - Step #1 - Create XML packet</title>
</head>
<body>
<script language="javascript" src="xml_test_md5.js"></script>
<h1>XML Test - Step #1 - Create XML packet</h1><br />
<form method="post" action="builder_old.php" name='xml_packet'>
<strong>Channel:</strong>&nbsp;&nbsp;<input type=text name="channel" value="<?=$channel;?>"><br />
<strong>Module:</strong>&nbsp;&nbsp;<input type=text name="module" value="<?=$module;?>"><br />
<strong>Command:</strong>&nbsp;&nbsp;<input type=text name="command" value="<?=$command;?>"><br />
<input type="hidden" name="top_children" value="<?=$top_children;?>"><br />
<strong>Data:</strong><br />
<?php

foreach($child as $key=>$value) {
   echo "<strong>Child #" . $key . ":</strong>&nbsp;&nbsp;\n";
   echo "Node Name:&nbsp;";
   echo "<input name='child" . $key . "_name' value='" . $value["name"] . "'>\n";
   if($value["children"] == 0) {
      echo "&nbsp;&nbsp;Node Value:&nbsp;";
      echo "<input name='child" . $key . "_value' value='" . $value["value"] . "'>\n";
      echo "<input type='button' value='MD5 the Value' onclick='javascript: md5hash(document.xml_packet.child" . $key . "_value); return false;'>";
   }
   echo "<input type='submit' name='child" . $key . "_add_child' value='Add Sub Child'>\n";
   echo "<input type='submit' name='child" . $key . "_delete' value='Delete'><br />\n";
   echo "<input type='hidden' name='child" . $key . "_children' value='" . $value["children"] . "'>\n";
   if(is_array($value["child"])) {
      foreach($value["child"] as $subkey=>$subvalue) {
         echo "&nbsp;&nbsp;&nbsp;<strong>Child #" . $key . " - SubChild #" . $subkey . "</strong>&nbsp;&nbsp;\n";
         echo "Node Name:&nbsp;";
         echo "<input name='child" . $key . "_child" . $subkey . "_name' value='" . $subvalue["name"] . "'>\n";
         if($subvalue["children"] == 0) {
            echo "&nbsp;&nbsp;Node Value:&nbsp;";
            echo "<input name='child" . $key . "_child" . $subkey . "_value' value='" . $subvalue["value"] . "'>\n";
            echo "<input type='button' value='MD5 the Value' onclick='javascript: md5hash(document.xml_packet.child" . $key . "_child" . $subkey . "_value); return false;'>";
         }
         echo "<input type='submit' name='child" . $key . "_child" . $subkey . "_add_child' value='Add Sub Child'>\n";
         echo "<input type='submit' name='child" . $key . "_child" . $subkey . "_delete' value='Delete'><br />\n";
         echo "<input type='hidden' name='child" . $key . "_child" . $subkey . "_children' value='" . $subvalue["children"] . "'>\n";
         if(is_array($subvalue["child"])) {
            foreach($subvalue["child"] as $subkey2=>$subvalue2) {
               echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Child #" . $key . " - SubChild #" . $subkey . " - SubChild #" . $subkey2 . "</strong>&nbsp;&nbsp;\n";
               echo "Node Name:&nbsp;";
               echo "<input name='child" . $key . "_child" . $subkey . "_child" . $subkey2 . "_name' value='" . $subvalue2["name"] . "'>\n";
               echo "&nbsp;&nbsp;Node Value:&nbsp;";
               echo "<input name='child" . $key . "_child" . $subkey . "_child" . $subkey2 . "_value' value='" . $subvalue2["value"] . "'>\n";
               echo "<input type='button' value='MD5 the Value' onclick='javascript: md5hash(document.xml_packet.child" . $key . "_child" . $subkey . "_child" . $subkey2 . "_value); return false;'>";
               echo "<input type='submit' name='child" . $key . "_child" . $subkey . "_child" . $subkey2 . "_delete' value='Delete'><br />\n";      
            }
         }      
      }
   }
}

?>
<br />
<br />
<input type="submit" name="add_top_child" value="Add top child">
<input type="submit" name="send_xml" value="Create XML">
</form>
</body>
</html>
