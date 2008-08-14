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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

class reports_custom_model
{
	var $format_type;
	
	var $title;
	var $display_column_tokens;
	var $ordering;
	var $filters;
	var $dataset;

	var $first_day_of_week;
	var $UTC_time_offset;
   
	function reports_custom_model($xml_input) {

		$this->filters = array();
		
		$this->dataset =& $xml_input->get_child_data("dataset");
   		$this->title =& $xml_input->get_child_data("title");
		$this->set_localization_data($xml_input->get_child("localization", 0));
		//$this->summaries = array(); //only in grouped
		
		$this->set_display_column_data($xml_input->get_child("display_columns", 0));  //matrix defines own
		
		//$this->format_type = "list"; //the default report type  //all define their own

		$this->set_grouping_data($xml_input);
		
		
		/*
		$grouping_elm = $xml_input->get_child("groupings", 0); //matrix/ group define own, list needs to opt out
		
		if($grouping_elm != NULL) {
			$this->format_type = "grouping";
			$this->set_grouping_data($grouping_elm);
		}

		$grid_grouping_elm = $xml_input->get_child("grid_groupings", 0);
		if($grid_grouping_elm != NULL) {
			$this->format_type = "grid_grouping";
			$this->set_grouping_grid_data($grid_grouping_elm);
		}
		*/
		$this->set_filter_column_data($xml_input->get_child("filter_columns", 0));
		
	}
	
	function set_localization_data(&$local_elm) {
		if($local_elm) {
			$this->UTC_time_offset = $local_elm->get_child_data("time_offset");
			$this->first_day_of_week = $local_elm->get_child_data("first_day_of_week");
		}
		else {
			$this->UTC_time_offset = 0; //default to UTC time
			$this->first_day_of_week = 1; //default to Sunday first day of week
		}		
	}
	
	function set_display_column_data(&$disp_cols) {
		$display_cols_children =& $disp_cols->get_children();		
		
		if(is_array($display_cols_children)) {
			foreach($display_cols_children as $tag_name=>$display_cols) {
				if(is_array($display_cols))
				foreach($display_cols as $display_col) {
					//$fields[$field_xml->get_token()] = $field_xml->get_data_trim();
					$disp_col_name = $display_col->get_attribute("name", FALSE);
					$this->display_column_tokens[] = $disp_col_name;
					
					//$orderIndex = $display_col->get_attribute("order", FALSE);
					//if($orderIndex != "") {
					//	$this->ordering[$orderIndex] =  $disp_col_name;
					//}
					
					$disp_col_children =& $display_col->get_children();
					$this->process_display_column_children($disp_col_children, $disp_col_name);

				}
			}
		}
	}
	
	function process_display_column_children(&$disp_col_children, $disp_col_name) {
		if(is_array($disp_col_children)) {
	
			foreach($disp_col_children as $tag_name2=>$disp_child_tag) {

				if($tag_name2 == "summaries") {
	
					$this->process_summaries($disp_child_tag, $disp_col_name);
					
				}
				elseif($tag_name2 == "order") {
	
					$this->process_order($disp_child_tag, $disp_col_name);
				
				}
			}
		}
	}
	
	
	function set_grouping_data(&$groupings_elm) {
		
		//$groupings_children =& $groupings_elm->get_children();
		
		$groupings_children =& $groupings_elm->get_child("group");
		
		if(is_array($groupings_children))
			foreach($groupings_children as $group_elm) {
				//print_r($group_elm);
				$order_attribute = $group_elm->get_attribute("order", FALSE);
				$this->groupings[$order_attribute] = $this->process_group($group_elm);
			}		
		
	}	
	
	function process_group(&$group_elm) {				
		$group = array();
		$group['column_token'] = $group_elm->get_attribute("col", FALSE);
		$group['sort'] = $group_elm->get_attribute("sort", FALSE);
		$group['grouping_type'] = $group_elm->get_attribute("range", FALSE);

		return $group;
	}
	
	function process_summaries(&$disp_child_tag, $disp_col_name) {

	}
	
	function process_order(&$disp_child_tag, $disp_col_name) {
		if(is_array($disp_child_tag))
		foreach($disp_child_tag as $order_elm) {
			$order = array();
			$order['column_token'] = $disp_col_name;
			$order['sort'] = $order_elm->get_attribute("sort", FALSE);

			$order_attribute = $order_elm->get_attribute("order", FALSE);
			$this->ordering[$order_attribute] = $order;
		}							
	}
	
	function set_filter_column_data(&$filter_elm) {
		$filter_container_children =& $filter_elm->get_children();
      
		if(is_array($filter_container_children)) {
			foreach($filter_container_children as $tag_name=>$filter_cols) {
				if(is_array($filter_cols))
				foreach($filter_cols as $filter_col) {
					$filter = array();
					$filter['column_token'] = $filter_col->get_attribute("name", FALSE);
					$filter['operator'] = $filter_col->get_child_data("operator");
					$filter['value'] = $filter_col->get_child_data("value");
					$filter['value_original'] = $filter_col->get_child_data("value");
					//$filter['value2'] = $filter_col->get_attribute("value2", FALSE);  

					$this->filters[]=$filter;
				}
			}
		}
	}

}
