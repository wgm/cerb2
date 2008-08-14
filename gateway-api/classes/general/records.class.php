<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class general_records
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_records() {
      $this->db =& database_loader::get_instance();
      $xml =& xml_output::get_instance();
      $this->data =& $xml->get_child("data", 0);
   }

   function get_record_instance($record_id, $instance_id) {
   	$tables = array();
   	$fields = array();

   	$field_list = $this->db->Get("schema", "get_record_schema", array("record_id"=>$record_id));
   	
		foreach($field_list as $field_item) {
			$table_name = $field_item["table_name"];
			$tables[$table_name] = $field_item["table_id"];
			$fields[$field_item["map_id"]] = sprintf("%s.%s as 'f_%d'", $table_name, $field_item["field_name"], $field_item["map_id"]);
		}

		$pivot_data = $this->db->Get("schema", "get_record_pivot_data", 
			array(
				"record_id"=>$record_id
		));
      $joins = $this->db->Get("schema", "get_record_joins", 
      	array(
      		"record_id"=>$record_id,
      		"tables"=>$tables,
      		"pivot_data"=>$pivot_data
      ));
      $record_inst = $this->db->Get("record", "get_record_instance", 
      	array(
      		"record_id"=>$record_id, 
      		"instance_id"=>$instance_id,
      		"pivot_data"=>$pivot_data,
      		"joins"=>$joins,
      		"fields"=>$fields
      ));
      
   	$record_xml =& $this->data->add_child("record", xml_object::create("record", NULL, array("record_id"=>$record_id,"instance_id"=>$instance_id)));
      $fields_xml =& $record_xml->add_child("fields", xml_object::create("fields", NULL));
   	
      if(is_array($record_inst)) {
       	foreach($record_inst as $key => $val) {
	   		if(substr($key,0,2) == "f_") { // [JAS]: Get rid of pesky iterative indexes (keep assoc)
	   			$keystr = substr($key,2);
	   			$fields_xml->add_child("field", xml_object::create("field", $val, array("map_id"=>$keystr)));
	   		}
      	}
      }
      
      return TRUE;
   }
   
   function save_record_instance($record_id, $instance_id, $changes) {
   	$tables = array();
   	$fields = array();

   	$field_list = $this->db->Get("schema", "get_record_schema", array("record_id"=>$record_id));
   	
		foreach($field_list as $field_item) {
			$table_name = $field_item["table_name"];
			
			if(!isset($tables[$table_name]))
				$tables[$table_name] = array();
			
			$map_id = $field_item["map_id"];
			$value = $changes["field" . $map_id];
			if(!empty($value)) {
				$tables[$table_name][$map_id] = array("field"=>$field_item["field_name"], "value"=>$value);
			}
		}

		$this->db->Save("record", "save_record_instance", array("record_id"=>$record_id,"instance_id"=>$instance_id,"tables"=>$tables));
		
      $this->data->add_child("record_id", xml_object::create("record_id", $record_id, array()));
      $this->data->add_child("instance_id", xml_object::create("instance_id", $instance_id, array()));
		
//		$pivot_data = $this->db->Get("schema", "get_record_pivot_data", 
//			array(
//				"record_id"=>$record_id
//		));
//      $joins = $this->db->Get("schema", "get_record_joins", 
//      	array(
//      		"record_id"=>$record_id,
//      		"tables"=>$tables,
//      		"pivot_data"=>$pivot_data
//      ));
//      $record_inst = $this->db->Get("record", "get_record_instance", 
//      	array(
//      		"record_id"=>$record_id, 
//      		"instance_id"=>$instance_id,
//      		"pivot_data"=>$pivot_data,
//      		"joins"=>$joins,
//      		"fields"=>$fields
//      ));
//      
//   	$record_xml =& $this->data->add_child("record", xml_object::create("record", NULL, array("record_id"=>$record_id,"instance_id"=>$instance_id)));
//      $fields_xml =& $record_xml->add_child("fields", xml_object::create("fields", NULL));
//   	
//      if(is_array($record_inst)) {
//       	foreach($record_inst as $key => $val) {
//	   		if(substr($key,0,2) == "f_") { // [JAS]: Get rid of pesky iterative indexes (keep assoc)
//	   			$keystr = substr($key,2);
//	   			$fields_xml->add_child("field", xml_object::create("field", $val, array("map_id"=>$keystr)));
//	   		}
//      	}
//      }
      
      return TRUE;
   }

}