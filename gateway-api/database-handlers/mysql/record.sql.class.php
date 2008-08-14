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
 * Database abstraction layer for data
 *
 */
class record_sql
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
    * @return record_sql
    */
   function record_sql(&$db) {
      $this->db =& $db;
   }
   
   function get_record_instance($params) {
   	extract($params);
   	
   	if(empty($record_id) 
   		|| empty($instance_id) 
   		|| empty($joins) 
   		|| empty($fields)
   		|| empty($pivot_data)
   		) {
   		return array();
   	}
		
   	$sql = sprintf("SELECT %s ".
			"FROM %s ".
			"%s ".
			"WHERE %s.%s = '%d'",
				implode(",", array_values($fields)),
				$pivot_data["name"],
				implode(" ", array_values($joins)),
				$pivot_data["name"],
				$pivot_data["key"],
				$instance_id
		);

		return $this->db->GetRow($sql);
   }
   
   function create_record_instance($params) {
   	extract($params);
   	
   	if(empty($key) || empty($table) || empty($inst_id) || empty($auto_inc))
   		return false;

   	if($auto_inc == 'Y') {  	
	   	$sql = sprintf("INSERT INTO %s VALUES();",
	   		$table
	   	);
	   	$this->db->Execute($sql);
	   	return $this->db->Insert_ID();
   	}
   	else {
   		$sql = sprintf("INSERT INTO %s (%s) VALUES(%s)",
   			$table,
   			$key,
   			$this->db->qstr($inst_id)
   		);
	   	$this->db->Execute($sql);
	   	return $inst_id;
   	}
   	
   }
   
   function check_record_instance($params) {
   	extract($params);
   	
   	if(empty($key) || empty($table) || empty($inst_id) || empty($auto_inc))
   		return false;
   	
   	$sql = sprintf("SELECT %s FROM %s WHERE %s = '%d'",
   		$key,
   		$table,
   		$key,
   		$inst_id
   	);
   	$id = $this->db->GetOne($sql);
   }
   
   function save_record_instance($params) {
   	extract($params);
   	
   	if(empty($record_id)
   		|| empty($instance_id)
   		|| empty($tables)
   		) {
   		return array();
   	}
   	
   	$sql = sprintf("SELECT t.primary_key, t.table_name, t.auto_inc ".
   		"FROM mcrm_record_tables t ".
   		"WHERE t.table_name IN ('%s')",
   			implode("','", array_keys($tables))
   	);
   	$table_result = $this->db->GetAll($sql);
   	$table_keys = array();
   	
   	if(is_array($table_result))
   	foreach($table_result as $tr) {
   		$table_keys[$tr["table_name"]] = array("key"=>$tr["primary_key"],"auto_inc"=>$tr["auto_inc"]);
   	}   	
   	
		foreach($tables as $tbl_name => $tbl) {
			if(empty($tbl))
				continue;

			$updates = array();
			if(is_array($tbl))
			foreach($tbl as $map_id => $fld) {
				$updates[] = sprintf("%s = %s",
					$fld["field"],
					$this->db->qstr($fld["value"])
				);
			}
			
			$table_key = $table_keys[$tbl_name]["key"];
			$auto_inc = $table_keys[$tbl_name]["auto_inc"];
			
			if(!empty($updates) && !empty($table_key)) {
		   	$id = $this->check_record_instance(array("key"=>$table_key,"table"=>$tbl_name,"inst_id"=>$instance_id,"auto_inc"=>$auto_inc));
		   	
		   	if(empty($id)) {
		   		$this->create_record_instance(array("key"=>$table_key,"table"=>$tbl_name,"inst_id"=>$instance_id,"auto_inc"=>$auto_inc));
		   	}
				
				$sql = sprintf(
					"UPDATE %s ".
					"SET %s ".
					"WHERE %s = '%d'",
						$tbl_name,
						implode(",", $updates),
						$table_key,
						$instance_id
				);
				
//				echo $sql . "\r\n";
				$this->db->Execute($sql);
			}
			
		}
   	
   }
}