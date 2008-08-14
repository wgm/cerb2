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

/**
 * Database abstraction layer for ticket data
 *
 */
class schema_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;
   
   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return ticket_sql
    */
   function schema_sql(&$db) {
      $this->db =& $db;
   }
   
   function get_records($params) {
   	extract($params);
   	
   	$sql = "SELECT r.record_id, r.record_name, r.record_code, r.custom ".
			"FROM `mcrm_records` r ".
			"ORDER BY r.record_name ASC";
		return $this->db->GetAll(sprintf($sql));
   }
   
   function load_table_schemas($record_id) {
   	$tables = array();

   	$sql = "SELECT DISTINCT t.table_name ".
   		"FROM `mcrm_cfield_mapping` m ".
   		"INNER JOIN `mcrm_record_tables` t ON (m.table_id = t.table_id) ".
   		"WHERE m.record_id = %d";
   	$tables_list = $this->db->GetAll(sprintf($sql, $record_id));
   	
   	if(is_array($tables_list))
   	foreach($tables_list as $table) {
   		$table_name = $table["table_name"];
   		$sql = "DESCRIBE %s";
   		$table_desc = $this->db->GetAll(sprintf($sql, $table_name));
   		if(is_array($table_desc)) {
   			foreach($table_desc as $desc_item) {
   				if(!isset($tables[$table_name])) {
   					$tables[$table_name] = array();
   				}
   				$tables[$table_name][$desc_item["Field"]] = $desc_item;
				}
   		}
   	}

   	return $tables;
   }
   
   function get_record_pivot_data($params) {
   	extract($params);
   	
   	if(empty($record_id))
   		return array();
   		
   	$sql = "SELECT m.table_id as 'id', t.table_name as 'name', m.field_name as 'key' ".
			"FROM mcrm_cfield_mapping m ".
			"INNER JOIN mcrm_record_tables t ON (m.table_id = t.table_id) ".
			"WHERE m.record_id = '%d' ".
			"AND m.input_datatype = 'PK' ";
   	$pivot_data = $this->db->GetRow(sprintf($sql,$record_id));
   	
   	if(empty($pivot_data))
   		return array();
   	
   	return $pivot_data;
   }
   
   function get_record_joins($params) {
   	extract($params);
   	
   	if(empty($record_id) || empty($pivot_data))
   		return array();

   	$joins = array(
   		$pivot_data["id"] => "FROM " . $pivot_data["name"]
   	);
   		
   	$sql = sprintf("SELECT tp.table_id as primary_table_id, tp.table_name as primary_table_name, mp.field_name as primary_field_name, ".
   		"tf.table_id as foreign_table_id, tf.table_name as foreign_table_name, mf.field_name as foreign_field_name, l.join_type ".
   		"FROM mcrm_record_linking l ".
   		"INNER JOIN mcrm_cfield_mapping mp ON (mp.map_id = l.primary_key_field) ".
   		"INNER JOIN mcrm_cfield_mapping mf ON (mf.map_id = l.foreign_key_field) ".
   		"INNER JOIN mcrm_record_tables tp ON (tp.table_id = mp.table_id) ".
   		"INNER JOIN mcrm_record_tables tf ON (tf.table_id = mf.table_id) ".
   		"WHERE l.record_id = '%d' ",
   			$record_id
   	);
   	$linking = $this->db->GetAll($sql);

   	$changes = -1;
   	if(is_array($linking))
   	while(!empty($linking) && $changes != 0) {
	   	foreach($linking as $idx => $link) {
	   		$primary_id = $link["primary_table_id"];
	   		$foreign_id = $link["foreign_table_id"];
	   		
	   		if(array_key_exists($primary_id,$joins)) {
	   			$joins[$primary_id] = sprintf(
	   				"%s JOIN %s ON (%s.%s = %s.%s)",
	   					$link["join_type"],
	   					$link["foreign_table_name"],
	   					$link["primary_table_name"],
	   					$link["primary_field_name"],
	   					$link["foreign_table_name"],
	   					$link["foreign_field_name"]
	   			);
	   			unset($linking[$idx]);
	   		}
	   	}
   	}
   	
   	return $joins;
   }
   
   function get_record_schema($params) {
   	extract($params);
   	
   	if(empty($record_id))
   		return false;
   	
   	$tables = $this->load_table_schemas($record_id);
   		
   	$sql = "SELECT m.map_id, t.table_id, t.table_name, m.field_name, ".
   		"m.display_name, g.group_name, g.group_id, m.input_datatype, m.report_datatype ".
	   	"FROM `mcrm_cfield_mapping` m ".
	   	"INNER JOIN `mcrm_cfield_groups` g ON (m.group_id = g.group_id) ".
	   	"INNER JOIN `mcrm_record_tables` t ON (t.table_id = m.table_id) ".
	   	"WHERE m.record_id = %d ".
	   	"AND m.is_visible = 'Y' ".
   		"ORDER BY g.group_order, m.field_order, m.map_id";
	   $mappings = $this->db->GetAll(sprintf($sql, $record_id));
	   
	   $output = array();
	   
	   if(is_array($mappings))
	   foreach($mappings as $map_inst) {
	   	$table_name = $map_inst["table_name"];
	   	$field_name = $map_inst["field_name"];
	   	$field_type = $tables[$table_name][$field_name]["Type"];
	   	
			$field_options = array();
			preg_match("/enum\(\'(.*?)\'\)/i",$field_type,$matches);
			if(!empty($matches[1])) {
				$field_options = explode("','",$matches[1]);
			}
	   	
	   	$output[] = array(
	   		"map_id" => $map_inst["map_id"],
	   		"table_id" => $map_inst["table_id"],
	   		"table_name" => $table_name,
	   		"field_name" => $map_inst["field_name"],
	   		"input_datatype" => $map_inst["input_datatype"],
	   		"report_datatype" => $map_inst["report_datatype"],
	   		"display_name" => $map_inst["display_name"],
	   		"group_id" => $map_inst["group_id"],
	   		"group_name" => $map_inst["group_name"],
	   		"field_options" => $field_options
	   	); 
	   }
	   
	   return $output;
   }
}