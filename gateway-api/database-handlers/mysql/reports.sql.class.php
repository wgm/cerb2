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

require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/mysql/reports_matrix_query_builder.class.php");

/**
 * Database abstraction layer for chat reports data
 *
 */
class reports_sql
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
    * @return reports_sql
    */
   function reports_sql(&$db) {
      $this->db =& $db;
   }
	   
	function get_saved_reports($params) {
		extract($params);

		$sql = "SELECT report_id, report_name, report_category, report_data FROM saved_reports ";
		
		return $this->db->GetAll($sql);
	}
	
	function get_saved_report_by_id($params) {
		extract($params);
		//$id
		$sql = "SELECT report_id FROM saved_reports WHERE report_id = %d ";
		return $this->db->GetAll(sprintf($sql, $this->db->qstr($id)));
	}
   
	function create_saved_report($params) {
		extract($params);
		//$name, $category, $data, $id

		$sql = "INSERT INTO saved_reports () VALUES ()";
		
		$this->db->Execute($sql);
		
		return mysql_insert_id();
	}

	function update_saved_report($params) {
		extract($params);
		//$id, $name, $category, $data

		$sql = "UPDATE saved_reports SET report_name = %s, report_category = '%d', report_data = %s WHERE report_id = '%d' ";
		$sql = sprintf($sql, $this->db->qstr($name), $category, $this->db->qstr($data), $id);
		
		$this->db->Execute($sql);
	}	
	
	function get_report_data($params) {
		extract($params);
		//$dataset, $column_tokens, $groupings, $filters, $first_day_of_week, $ordering
		$sql_select = "SELECT ";
	    $prefix = "";
		
		//build most of the "SELECT" part of the query, including fields that will be needed only as hyperlink ids 
		if(is_array($column_tokens))
    	foreach($column_tokens as $token) {
			$token_arr = custom_report_mappings::get_col_info($token);
	    	$sql_select .= $prefix . $token_arr['table_name'] . "." . $token_arr['column_name'] . " " . $token;
			$prefix = ", ";
      
	  		//select extra field id for this token if a mapping is defined for it
			$link_token = custom_report_mappings::get_link_col_token($token);
			if($link_token != "") {
				$link_token_arr = custom_report_mappings::get_col_info($link_token);
				$sql_select .= $prefix . $link_token_arr['table_name'] . "." . $link_token_arr['column_name'] . " " . $link_token;
			}
      
		}
    	
		//builds the query 'FROM' clause 
		$sql_from = custom_report_mappings::get_join_sql_from_dataset_token($dataset);
		
		//add a WHERE clause for each filter
		$sql_where = " WHERE 1=1 ";
		if(is_array($filters))
		foreach($filters as $filter) {
			$sql_where .= custom_report_mappings::get_condition_mapping($filter['column_token'], $filter['operator'], $filter['value']);
    	}

		if(sizeof($groupings) > 0) {
			//Build the "ORDER BY" clause by looping through any groupings
			$sql_order = " ORDER BY  " . 
			$prefix = "";
			for($i=0; $i < sizeof($groupings); $i++) {
				$token_arr = custom_report_mappings::get_col_info($groupings[$i]['column_token']);
				$sql_order .= $prefix . $token_arr['table_name'] . '.' . $token_arr['column_name'] . ' ' . $groupings[$i]['sort'] ;
				
				$prefix = ", ";
			}
		}
		else {
			//this is a list report, Build the "ORDER BY" clause by looping through any orderings 
			if(sizeof($ordering) > 0) {
				$sql_order = 'ORDER BY ';
				$prefix="";
				for($i=0; $i < sizeof($ordering); $i++) {
					$sql_order .= $prefix . ' ' . $ordering[$i]['column_token'] . ' ' . $ordering[$i]['sort'];
					$prefix=",";
				}
			}
		}
		//$sql_where .= " AND (opportunity.opportunity_id in ('5956','5959','5960','5961')) ";
		//$sql_where .= " AND opportunity.amount = 499.00 ";
		//print_r($column_tokens);
		$sql = $sql_select . $sql_from . $sql_where . $sql_order;
		//$sql .= " LIMIT 1000 "; 
		//echo "^^^". $sql . "^^^";
    	//exit();
		return $this->db->GetAll(sprintf($sql));
	}
	
	function get_matrix_report_data($params) {
		extract($params);
		//$dataset, $display_columns , $groupings, $global_filters, $first_day_of_week

		$results=array();
		
		for($i=0; $i < sizeof($display_columns); $i++) {
			$query_builder = new reports_matrix_query_builder($dataset, $display_columns[$i], $groupings, $global_filters, $first_day_of_week);
			$results[$i] =& $this->db->GetAll(sprintf($query_builder->get_query()));
			//echo "got results " . sizeof($results[$i]) ."\n";
		}
		return $results;

	}

}