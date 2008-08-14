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

class reports_custom_list extends reports_custom 
{

	function create_grouped_array() {
	}
	
	/*
	 * Override parent function because no special sorting is needed for this report type
	*/
	function presort_results(&$result_data) {
	}
   
	function create_report_xml() {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$report =& $data->add_child("report", xml_object::create("report"));

		$report->add_child("title", xml_object::create("title", $this->model->title));
		$display_columns =& $report->add_child("display_columns", xml_object::create("display_columns"));
		//print_r($this->model->display_column_tokens);
		if(is_array($this->model->display_column_tokens))
		foreach($this->model->display_column_tokens AS $key=>$val) {
			$this->col_info[$val] = custom_report_mappings::get_col_info($val);
			$column =& $display_columns->add_child("col", xml_object::create("col", NULL, array('name'=>$this->col_info[$val]['friendly_name'])));
		}      


		if(is_array($this->model->filters)) {
			$filters =& $report->add_child("filters", xml_object::create("filters", NULL, array()));
			foreach($this->model->filters AS $key=>$val) {
				$friendly_operator = custom_report_mappings::get_operator_friendly_name($val['operator']);
				$col_inf = custom_report_mappings::get_col_info($val['column_token']);
				$filter =& $filters->add_child("filter", xml_object::create("filter", NULL, array("name"=>$col_inf['friendly_name'])));
				$filter->add_child("operator", xml_object::create("operator", $friendly_operator, array()));
				$filter->add_child("value", xml_object::create("value", $val['value_original'], array()));
			}      	
		}

		$list =& $report->add_child("list", xml_object::create("list"));
		$data_rows =& $list->add_child("data_rows", xml_object::create("data_rows"));
		for($i=0; $i < sizeof($this->report_data); $i++) {
			$data_row =& $data_rows->add_child("row", xml_object::create("row"));

			if(is_array($this->model->display_column_tokens))
			foreach($this->model->display_column_tokens AS $val) {
				$col =& $data_row->add_child("col", xml_object::create("col", $this->report_data[$i][$val], array()));
				if($this->col_info[$val]['type'] == "unixtimestamp") {
					$col->add_attribute("type", "date");
					//$str = gmdate("m/d/Y H:i",$this->report_data[$i][$val] + $this->model->UTC_time_offset);
					$str = gmdate("m/d/Y",$this->report_data[$i][$val] + $this->model->UTC_time_offset);
					$col->set_data($str);						
				}

				$link = $this->get_link_attribute($this->report_data[$i], $val);
				if($link != "") {
					$col->add_attribute("href", $link);
				}
			}
		}
		
	}   

}

