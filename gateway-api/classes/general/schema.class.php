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

class general_schema
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_schema() {
      $this->db =& database_loader::get_instance();
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $this->schema =& $data->add_child("schema", xml_object::create("schema"));
   }

   function get_schema() {
      $schema_map = $this->db->Get("schema", "get_schema_mapping", array());
      $records = $this->db->Get("schema", "get_records", array());

      $records_xml =& $this->schema->add_child("records", xml_object::create("records"));
      
      if(is_array($records))
      foreach($records as $record) {
      	$record_xml =& $records_xml->add_child("record", xml_object::create("record", NULL, array("id"=>$record["record_id"])));
      	$record_xml->add_child("name", xml_object::create("name", $record["record_name"]));
      	$record_xml->add_child("code", xml_object::create("code", $record["record_code"]));
      	$record_xml->add_child("custom", xml_object::create("custom", $record["custom"]));

			$field_list = $this->db->Get("schema", "get_record_schema", array("record_id"=>$record["record_id"]));
	      $groups_xml =& $record_xml->add_child("groups", xml_object::create("groups"));
			$last_group = "";
			
			if(is_array($field_list))
			foreach($field_list as $field_item) {
				if($field_item["group_name"] != $last_group) {
					$group_xml =& $groups_xml->add_child("group", xml_object::create("group", NULL, array()));
					$group_xml->add_child("name", xml_object::create("name", $field_item["group_name"]));
			      $fields_xml =& $group_xml->add_child("fields", xml_object::create("fields"));
				}
				
				$field_xml =& $fields_xml->add_child("field", xml_object::create("field", NULL, array("map_id"=>$field_item["map_id"])));
				$field_xml->add_child("table", xml_object::create("table", $field_item["table_name"], array("id"=>$field_item["table_id"])));
				$field_xml->add_child("column", xml_object::create("column", $field_item["field_name"]));
				$field_xml->add_child("display_name", xml_object::create("display_name", $field_item["display_name"]));
				$field_xml->add_child("input_datatype", xml_object::create("input_datatype", $field_item["input_datatype"]));
				$field_xml->add_child("report_datatype", xml_object::create("report_datatype", $field_item["report_datatype"]));
				
				$field_options = $field_item["field_options"];
				if(is_array($field_options)) {
					$options_xml =& $field_xml->add_child("options", xml_object::create("options", NULL, array()));
					foreach($field_options as $option) {
						$options_xml->add_child("option", xml_object::create("option", $option));
					}
				}
				
				$last_group = $field_item["group_name"];
			}

      }
      
      return TRUE;
   }

}