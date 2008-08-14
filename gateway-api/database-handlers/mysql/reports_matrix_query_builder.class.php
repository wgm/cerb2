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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/


/*
	Assembles the query to be run for one specific column of a matrix report
*/
class reports_matrix_query_builder {
	var $dataset;
	var $display_column;
	var $groupings;
	var $global_filters;
	var $first_day_of_week;
	
	//$dataset, $display_columns , $groupings, $global_filters, $first_day_of_week
	function reports_matrix_query_builder($dataset, $display_column, $groupings, $global_filters, $first_day_of_week) {
			$this->dataset = $dataset;
			$this->display_column = $display_column;
			$this->groupings = $groupings;
			$this->global_filters = $global_filters;
			$this->first_day_of_week = $first_day_of_week;
	}
		
	function get_query() {

		$group_expression = $this->get_group_statement();
		$global_conditions = $this->get_global_conditions();
		$select_groups = $this->get_grouping_select_list();
		$select_data = $this->get_select_data();
		$from_statement = $this->get_from_statement();
		$col_specific_conditions = $this->get_col_specific_conditions();
		
		$sql = 'SELECT ';
		$sql .= $select_groups . ', ' . $select_data;
		$sql .= $from_statement;
		$sql .= 'WHERE 1 = 1 ';
		$sql .= $global_conditions;
		$sql .= $col_specific_conditions;
		$sql .= ' GROUP BY ';	
		$sql .= $group_expression;
		
		$sql .= " LIMIT 100 ";
		//echo $sql;
		//exit();
		return $sql;
	}
	
	function get_grouping_select_list() {
		$groups =& $this->groupings;
		$prefix = '';
		for($j=0; $j < sizeof($groups); $j++) {
			
			$token = $groups[$j]['column_token'];
			$range = $groups[$j]['grouping_type'];
			$sort = $groups[$j]['sort'];
			
			$col_info = custom_report_mappings::get_col_info($token);
			
			if($range != '') {
				$select_expression = custom_report_mappings::get_range_grouping_expression($range, $token, $this->model->first_day_of_week);
			}
			else {
				$select_expression = $col_info['table_name'] . '.' . $col_info['column_name'];
			}
			$select_expression .= ' group__' . $j;
			
			$select_groups .= $prefix . $select_expression . ' ';
			$prefix = ', ';
		}
		return $select_groups;
	}
	
	function get_select_data() {
		$col_info = custom_report_mappings::get_col_info($this->display_column['column_token']);
		$select_data = $col_info['table_name'] . '.' . $col_info['column_name'];
		$select_data = $this->display_column['function_type'] . '(' . $select_data . ') data_column ';
		return $select_data;
	}
	
	function get_group_statement() {
		$group_expression = '';
		$prefix='';		
		for($j=0; $j < sizeof($this->groupings); $j++) {
			$sort_direction = ($this->groupings[$j]['sort'] == "DESC") ? "DESC" : "ASC";
			$group_expression .= $prefix . ' group__' . $j . ' ' . $sort_direction;
			$prefix = ', ';
		}
		return $group_expression;
	}
	
	function get_from_statement() {
		$col_info = custom_report_mappings::get_col_info($this->display_column['column_token']);
		
		//create a dataset object which knows how all the tables in the dataset are joined
		$dataset_obj = custom_report_mappings::get_dataset($this->dataset);
		
		//get arrays of table names for all tables that will be used in the query
		//(in other words, all tables needed for grouping, filters, and the data column)
		$table_list_grouping = $this->get_tables_array($this->groupings);
		$table_list_filters_col_specific = $this->get_tables_array($this->display_column['filters']);
		$table_list_filters_global = $this->get_tables_array($this->global_filters);

		//merge together, remove duplicates, and reindex all of the possible tables from our arrays
		$table_list = array_merge($table_list_grouping, $table_list_filters_col_specific, $table_list_filters_global, array($col_info['table_name']));
		$table_list = array_values(array_unique($table_list));

		return $dataset_obj->get_query($table_list);
		
	}
	
	function get_global_conditions() {
		$global_conditions = '';
		foreach($this->global_filters as $filter) {
			$global_conditions .= custom_report_mappings::get_condition_mapping($filter['column_token'], $filter['operator'], $filter['value']);
		}
		return $global_conditions;
	}
	
	function get_col_specific_conditions() {
		$col_specific_conditions = '';
		foreach($this->display_column['filters'] AS $filter) {
			$col_specific_conditions .= custom_report_mappings::get_condition_mapping($filter['column_token'], $filter['operator'], $filter['value']);
		}
		return $col_specific_conditions;
	}
	
	function get_tables_array($set) {
		$result = array();
		for($i=0; $i < sizeof($set); $i++) {
			$token = $set[$i]['column_token'];
			$col_info = custom_report_mappings::get_col_info($token);
			$result[] = $col_info['table_name'];
		}
		return $result;
	}
	
	function get_global_filter_tables_array() {
		$result = array();
		for($i=0; $i < sizeof($this->global_filters); $i++) {
			$token = $this->global_filters[$i]['column_token'];
			$col_info = custom_report_mappings::get_col_info($token);
			$result[] = $col_info['table_name'];
		}
		return $result;
	}	
	
}


