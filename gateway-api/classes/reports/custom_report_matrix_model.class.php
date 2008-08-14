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

class reports_custom_matrix_model extends reports_custom_model 
{
	var $groupings;
	var $x_groupings;
	var $y_groupings;
	
	
	function reports_custom_matris_model($xml_input) {
     	parent::reports_custom_model($xml_input);
		
		$this->format_type = "matrix"; //the default report type  //all define their own
	}	

	function set_display_column_data(&$disp_cols_tag) {
		$display_cols = $disp_cols_tag->get_child("display_col");//->get_child_data($token, $instance = 0, $start = 0, $end = NULL)
		if(is_array($display_cols)) {
			foreach($display_cols as $display_col) {

				$display_column = array();
				$display_column['friendly_name'] = $display_col->get_child_data("friendly_name");
				$display_column['function_type'] = $display_col->get_child_data("function_type");
				$display_column['column_token'] = $display_col->get_attribute("name", FALSE);

				$display_column['filters'] = array();
				$filter_columns_tag = $display_col->get_child("filter_columns", 0);

				if(!empty($filter_columns_tag)) {
					
					$filter_column_tags =& $filter_columns_tag->get_child("filter_col");
					
					if(!empty($filter_column_tags))
					foreach($filter_column_tags AS $filter_tag) {
						$filter = array();
						$filter['column_token'] = $filter_tag->get_attribute("name", FALSE);
						$filter['operator'] = $filter_tag->get_child_data("operator");
						$filter['value'] = $filter_tag->get_child_data("value");
						$filter['value_original'] = $filter_tag->get_child_data("value");
						$display_column['filters'][]= $filter;
					}
				}				
				$this->display_columns[] = $display_column;
			}
			//print_r($this->display_columns);
			//print_r($this);
		}
	}
	
	function set_grouping_data($xml_input) {
		$grid_grouping_elm = $xml_input->get_child("grid_groupings", 0);
		if($grid_grouping_elm != NULL) {
			$this->set_grouping_grid_data($grid_grouping_elm);
		}		
	}


	function set_grouping_grid_data(&$groupings_elm) {

		$ygroups_tag =& $groupings_elm->get_child("ygroups", 0);
		$ygroups =& $ygroups_tag->get_child("group");
		foreach ($ygroups AS $group_elm) {
			$order_attribute = $group_elm->get_attribute("order", FALSE);
			$this->y_groupings[$order_attribute] =& $this->process_group($group_elm);			
		}

		$xgroups_tag =& $groupings_elm->get_child("xgroups", 0);
		$xgroups =& $xgroups_tag->get_child("group");
		foreach ($xgroups AS $group_elm) {
			$order_attribute = $group_elm->get_attribute("order", FALSE);
			$this->x_groupings[$order_attribute] =& $this->process_group($group_elm);			
		}

		$this->groupings = array_merge($this->y_groupings, $this->x_groupings);
		
	}		
	
}
