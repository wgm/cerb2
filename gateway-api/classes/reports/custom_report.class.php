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

class reports_custom
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	var $db;
   
	var $model;
	
	var $col_info;
   
	var $report_data;
	var $report_status_message;
   
   //the result set transformed into a hierarchical array with a dimension for each column being grouped by
	var $grouped_results; 
   
	var $listed_results;
	var $timing;
   
	function reports_custom(&$model) {

		$this->timing = false;
		if($this->timing) $this->showTimeStamp("start custom report ");//echo "start custom report ". date("m/d/Y H:i:s")."-".microtime()." <br>\n";

		$this->report_status_message = "OK";
		$this->db =& database_loader::get_instance();
		
		$this->model =& $model;
		
		$this->validate_input();
		if($this->report_status_message != "OK")
			return;

		$this->report_data =& $this->get_result_set();
		if($this->report_status_message != "OK") 
			return;

		$this->create_grouped_array();
		
		$this->create_report_xml();
	}
   
	function get_status_message() {
		return $this->report_status_message;
	}
	
	function validate_input() {
		for($i=0; $i < sizeof($this->model->filters); $i++) {
			$col_info = custom_report_mappings::get_col_info($this->model->filters[$i]['column_token']);
			if($col_info['type'] == "unixtimestamp") {
				$time = strtotime($this->model->filters[$i]['value']);
				if($time != -1 && $time !== FALSE) {
					//echo "$time + ".$this->model->UTC_time_offset."=".($time + $this->model->UTC_time_offset."<br>"); 
					$this->model->filters[$i]['value'] = $time + $this->model->UTC_time_offset;
				}
				else {
					$this->report_status_message = "Invalid date string: '".$this->model->filters[$i]['value']."'";
				}
			}
		}
	}
	
	
	function get_result_set() {
		$result_data =& $this->db->get("reports", "get_report_data", array("dataset"=>$this->model->dataset, "column_tokens"=>$this->model->display_column_tokens, "groupings"=>$this->model->groupings, "filters"=>$this->model->filters, "first_day_of_week"=>$this->model->first_day_of_week, "ordering"=>$this->model->ordering));
		if(!$result_data || !is_array($result_data)) {
			$this->report_status_message = "Unable to obtain result set";
		}
		else {
		
		//echo sizeof($result_data);
		//print_r($result_data);
		if($this->timing) $this->showTimeStamp("results done (".sizeof($result_data)." records)");

		//if($this->timing) echo "pointless loop done (".sizeof($result_data)." records)".date("m/d/Y H:i:s")."-".microtime()." <br>\n";

		$this->presort_results($result_data);

		//$result_data =& $this->groupquicksort($result_data);
		if($this->timing) $this->showTimeStamp("results sorted ");
		// print_r($result_data);
		// exit();
		
		}
		return $result_data;
	}
	
	function presort_results(&$result_data) {
		usort($result_data, array($this, "groupcompare"));
	}
   
		//returns true if the node $a is less than or equal to $b
	function groupcompare($a, $b) {
		//echo "comparing ".$a['OPP_NAME']. " and ". $b['OPP_NAME']." <br>\n";
		for($i=0; $i < sizeof($this->model->groupings); $i++) {
			$column_token = $this->model->groupings[$i]['column_token'];
			//echo "&nbsp;&nbsp;&nbsp;comparing $column_token <br>\n";  
    		$col_info = custom_report_mappings::get_col_info($column_token);
			$val1 =& $a[$column_token];
			$val2 =& $b[$column_token];
			$sort_descending = ($this->model->groupings[$i]['sort'] == "DESC");
    		switch($col_info['type']) {
    			case "varchar":
    			case "char":
    			case "enum":
					$tmp = strcasecmp($val1, $val2);
					//echo "tmp = $tmp <br>\n so...";
					if($tmp != 0) { 
						//echo "not equal, returning (tmp < 0)=". ($tmp < 0) . " <br>\n"; 
						//return $tmp;
						return ($tmp < 0) ? 
								(($sort_descending) ? 1 : -1 ) 
								: 
								(($sort_descending) ? -1 : 1 );						
						
					}
    			break;
    			case "int":
    			case "decimal":
    			case "bigint":
					if($val1 != $val2) {
						//echo "not equal, returning val1 < val2=". ($val1 < $val2) . " <br>\n";
						 					
						//return ($val1 <= $val2) ? -1 : 1;
						
						return ($val1 <= $val2) ? 
								(($sort_descending) ? 1 : -1 ) 
								: 
								(($sort_descending) ? -1 : 1 );						
						
					}
				break;
    			case "unixtimestamp":
					$tmp = $this->compare_grouping_value($val1, $val2, $this->model->groupings[$i]['grouping_type']);
					//echo "TIMESTAMP tmp = $tmp <br>\n so...";
					if($tmp != 0) {
						//echo "not equal, returning (tmp < 0)=". ($tmp < 0) . " <br>\n"; 					
						//return $tmp;
						return ($tmp < 0) ? 
								(($sort_descending) ? 1 : -1 ) 
								: 
								(($sort_descending) ? -1 : 1 );						
					}
    				break;
    		}
						
		}
		//if we get here than all grouping fields are equal
  		return 0;
	}	

	
	function compare_grouping_value($a, $b, $groupingType) {
		$val1 = $this->get_grouping_value($groupingType, $a);
		$val2 = $this->get_grouping_value($groupingType, $b);
		
		if($val1 == $val2)
			//grouping  values are equal
			return 0;
		else {
			switch($groupingType) {
				case "DATE_MONTH_IN_YEAR":
				case "DATE_DAY_IN_MONTH":
					//for month-in-year or day-in-month, just compare the month/day number
					return ($val1 <= $val2) ? -1 : 1;
				break;
				default:
					//all other grouping types, just compare the timestamps
					return ($a <= $b) ? -1 : 1;
				break;
			}
		}
	}
	
	
	function compare($a, $b) {
		for($i=0; $i < sizeof($this->model->ordering); $i++) {
			$current_col_token = $this->model->ordering[$i]['column_token'];
			$col_info = custom_report_mappings::get_col_info($current_col_token);
			$sort_descending = ($this->model->ordering[$i]['sort'] == "DESC");
			//echo $col_info['type'] ;		
			switch($col_info['type']) {
				case "varchar":
				case "char":
				case "enum":
					$tmp = strcasecmp($a[$current_col_token], $b[$current_col_token]);
					if($tmp != 0) {
						return ($tmp < 0) ? 
								(($sort_descending) ? 1 : -1 ) 
								: 
								(($sort_descending) ? -1 : 1 );		
					}  
					break;
				case "int":
				case "decimal":
				case "unixtimestamp":
				case "bigint":
					if($a[$current_col_token] != $b[$current_col_token]) {
						return ($a[$current_col_token] < $b[$current_col_token]) ?
								(($sort_descending) ? 1 : -1 ) 
								: 
								(($sort_descending) ? -1 : 1 );									
						
					}
				break;
			}
		}
		
		//a and b are the same
		return 0;
	}	
   
	function create_grouped_array() {
		$report_data =& $this->report_data;

		for($i=0; $i <  sizeof($this->model->groupings); $i++) {
			$groupIdxs[] = 0;
			$currVals[] = "";  
		}
		//set this -1 because the first row will always have a different group col value than the previous(which is always blank), 
		//so it is always incremented.
		//This prevents the array from starting at index 1
		$groupIdxs[0] = -1; 

		$result=array();
		$last_completed_subgroup_node = array();
		
		for($i=0; $i < sizeof($report_data); $i++) {
			//if($i % 50 == 0) echo '*'.$i.'*';
			//loop through each column that we are grouping by in order of the grouping so we can detect when they are changed
			//(this works because the query result we are looping through does "order by" on the grouping columns)
	      	for($j=0; $j <  sizeof($this->model->groupings); $j++) {
				$isDifferentGroup = false;
				$groupingType = $this->model->groupings[$j]['grouping_type'];
				$groupColToken = $this->model->groupings[$j]['column_token'];

				$value = $report_data[$i][$groupColToken];
				$grouping_value = $this->get_grouping_value($groupingType, $value);
				if($grouping_value != $currVals[$j]) {
					//the current looped grouped value is different in this record than the last one
					$isDifferentGroup = true;
					$groupIdxs[$j]++;
            
					//set any currentVals further down the tree to the values of this row, and reset such indexes to zero
					for($n=$j+1; $n < sizeof($currVals); $n++) {
						$tmpGroupColName = $this->model->groupings[$n]['column_token'];
						$currVals[$n] = $report_data[$i][$tmpGroupColName];
						$groupIdxs[$n] = 0;
					}                  
            
					unset($tmp);
					$tmp =& $result;

					//set any grouping values further down the tree to the values
					for($k=0; $k < sizeof($groupIdxs); $k++)	
					{
						$tmp =& $tmp['subgroups'][$groupIdxs[$k]];
						if($k >= $j) {
							$groupingType = $this->model->groupings[$k]['grouping_type'];
							$groupColToken = $this->model->groupings[$k]['column_token'];
							$value = $report_data[$i][$groupColToken];							
							$tmp['grouping_value'] = $this->get_grouping_value_display($groupingType, $value);
						}
					}
				}
				//add the new result if we detected a different value in the current grouping column, 
				//or if we've looped through every grouping column and not found any different value
				if($isDifferentGroup || $j == sizeof($this->model->groupings)-1) {
					//use $tmp to obtain a handle to the result node we need to add to (the indexes are stored in their own array: $groupIdxs)
					unset($tmp);
					$tmp =& $result;
					for($k=0; $k < sizeof($groupIdxs); $k++)	
					{
						$tmp =& $tmp['subgroups'][$groupIdxs[$k]];
					}
					$tmp['subgroups'][] =& $report_data[$i];

					if(sizeof($this->model->ordering) > 0) {
						if($isDifferentGroup){
							//$last_completed_subgroup_node = $this->quicksort($last_completed_subgroup_node);
							usort($last_completed_subgroup_node, array($this, "compare"));
						}
						$last_completed_subgroup_node =& $tmp['subgroups'];
					}

					$this->set_summaries($result, $groupIdxs, $report_data[$i]);

					//found a new value for this group type so change the current stored one
					$currVals[$j] = $grouping_value;

					break;//since we've added a node for this report_data row, no need to loop through any additional group columns
				}
			}
		}
		
		//This sorts the last subgroup by the ordering criteria (all previous subgroups are sorted during the above loop)
		if(sizeof($this->model->ordering) > 0) {
			usort($last_completed_subgroup_node, array($this, "compare"));
		}		
		
		if($this->timing) $this->showTimeStamp("grouped array built ");
		$this->grouped_results =& $result;
		//print_r($result);
		//exit();
   }

/*   
		this->model->summaries['OPP_AMOUNT'][0]['type'] = "SUM"
   this->model->summaries['OPP_AMOUNT'][0]['value']
   this->model->summaries['OPP_AMOUNT'][1]['type'] = "AVG"
   this->model->summaries['OPP_AMOUNT'][1]['value']
   this->model->summaries['OPP_AMOUNT'][2]['type'] = "MAX"
   this->model->summaries['OPP_AMOUNT'][2]['value']
   this->model->summaries['OPP_CLOSE_DATE'][0]['type']
   this->model->summaries['OPP_CLOSE_DATE'][0]['value']
  */ 
  
	function set_summaries(&$grouped_array, &$groupIdxs, &$data_node) {
		$tmp =& $grouped_array;
		for($k=0; $k < sizeof($groupIdxs); $k++) {
			$this->set_summary_fields($tmp, $data_node);
			$tmp =& $tmp['subgroups'][$groupIdxs[$k]];
		}
		$this->set_summary_fields($tmp, $data_node);      
	}
      
	function set_summary_fields(&$group_node, &$data_node) {
		if(is_array($this->model->summaries)) {
			foreach($this->model->summaries AS $col_token => $value) {
				for($m=0; $m < sizeof($value); $m++) {
					$tmp =& $group_node['summary'][$col_token][$value[$m]['type']];
					$last_index = sizeof($group_node['subgroups']) - 1;
					//$col_value = $group_node['subgroups'][$last_index][$col_token];
					$col_value = $data_node[$col_token];
					switch(strtoupper($value[$m]['type'])) {
						case "SUM":
							if($tmp == "") $tmp = 0;
							$tmp += $col_value;
							break;
						case "AVG":
							break;
						case "MAX":
							if($tmp == "" || $col_value > $tmp) {
								$tmp = $col_value;
							}
							break;
						case "MIN":
							if($tmp == "" || $col_value < $tmp) {
								$tmp = $col_value;
							}            
							break;
					}
				}
			}
		}
	}
   
   function get_grouping_value($groupingType, $value) {
   	switch($groupingType) {
    	case "DATE_DAY":
			$value = $value + $this->model->UTC_time_offset;
    		$result = gmdate("Ymd", $value);
			break;
    	case "DATE_WEEK":
			$value = $value + $this->model->UTC_time_offset;
			/*
			$week = str_pad(((date("z", $value) - (date("z", $value)%7))/7 + 1), 2, "0", STR_PAD_LEFT);
    		$result = gmdate("Y", $value) . "" . $week;
			*/
			//the first 
			if($this->model->first_day_of_week == 1) {
				//[mdf] format date with sunday as the first day of the week. 
				// this will consider the first sunday as the start of the first week of the year.
				// for example: if January 1st is the date used and it falls on any other day than sunday, 
				//that week is considered week zero for the year
				$result = gmstrftime("%Y%U",$value);
			}
			else {
				//format a date with monday as the first day of the week.
				//see notes above for more info
				$result = gmstrftime("%Y%W",$value);
			}
			break;    
    	case "DATE_MONTH":
			$value = $value + $this->model->UTC_time_offset;		
    		$result = gmdate("Ym", $value);
			break;    
    	case "DATE_QUARTER":
			$value = $value + $this->model->UTC_time_offset;
			$month = gmdate("m", $value);
			$year = gmdate("Y", $value);
			$quarter = ceil(($month * 4) / 12);
			$result = $year . $quarter;
			break;
    	case "DATE_YEAR":
			$value = $value + $this->model->UTC_time_offset;
			$result = gmdate("Y", $value);
			break;
		case "DATE_MONTH_IN_YEAR":
			$value = $value + $this->model->UTC_time_offset;		
			$result = gmdate("m", $value);
			break;
		case "DATE_DAY_IN_MONTH":
			$value = $value + $this->model->UTC_time_offset;		
			$result = gmdate("d", $value);
			break;
		default:
			$result = $value;
			break;
    }
	
    return $result;
   }
   
	function get_grouping_value_display($groupingType, $value) {
		
		
   	switch($groupingType) {
    	case "DATE_DAY":
			$value = intval($value) + intval($this->model->UTC_time_offset);
    		$result = gmdate("m/d/Y", $value);
			break;
    	case "DATE_WEEK":
			$value = intval($value) + intval($this->model->UTC_time_offset);
			//$week = str_pad(((date("z", $value) - (date("z", $value)%7))/7 + 1), 2, "0", STR_PAD_LEFT);
			
			//store the current day of week
			$curr_day = gmdate("w", $value);

			//get the start and end dates of week assuming Sunday is first day of week
			$start_of_week = $value-($curr_day*86400);
			$end_of_week = $value + (6-$curr_day)*86400;
			
			
			//if start of week param is Monday then add one day to the start and end unless current day is sunday
			if($this->model->first_day_of_week == 2) {
				if($curr_day != 0) {
					//the current date is a Sunday, so add one day to the start and end dates
					$start_of_week = $start_of_week+86400;
					$end_of_week = $end_of_week+86400;
				}
				else {
					//the current date is non-Sunday so subtract 6 days from previous start calculation
					$start_of_week = $value - 6*86400;
					$end_of_week = $value;
				}
			}			

			//get the week number
			if($this->model->first_day_of_week == 1) {
				//week number if First Day of week = SUN
				$result = gmstrftime("%U",$value);
			}
			else {
				//week number if First Day of week = MON			
				$result = gmstrftime("%W",$value);
			}
			
			//if result=zero than this is the incomplete first week of the year
			//further calculations needed to determine start and end dates of the week
			//(since it's less than 7 days)
			if(intval($result) == 0) {
				if($this->model->first_day_of_week == 1) {
					//First Day of week =  SUN
					//$start_of_week = gmmktime(0, 0, 0, 1, 1, gmdate("Y",$value)) + $this->model->UTC_time_offset;
					$start_of_week = strtotime("01/01/" . gmdate("Y",$value)) ;
					$start_day_of_week = gmstrftime("%w", $start_of_week);//0-6 sun-sat
					$end_of_week = $start_of_week + (6-$start_day_of_week)*86400;

				}
				else {
					//First Day of week = MON
					//$start_of_week = gmmktime(0, 0, 0, 1, 1, gmdate("Y",$value)) + $this->model->UTC_time_offset;
					$start_of_week = strtotime("01/01/" . gmdate("Y",$value)) ;
					$start_day_of_week = gmstrftime("%w", $start_of_week);//0-6 sun-sat
					$end_of_week = ($start_day_of_week == 0) ? $start_of_week : $start_of_week + (7-$start_day_of_week)*86400;
				}
			}
			else {
				//This isn't the incomplete first week of the year, 
				//so check if it's the incomplete last week of the year (if so adjust end of week to Dec 31)
				$curr_month = gmdate("m", $value);
				$curr_date_in_month = gmdate("d", $value);
				$curr_year = gmdate("Y", $value);
				if($curr_month == 12 && $curr_date_in_month > 25) {
					$last_day_of_year = gmmktime(0, 0, 0, 12, 31, date("Y",$value));
					$end_of_week = $last_day_of_year;
				}  
			}
			
    		$result = gmdate("m/d/Y", $start_of_week) . " - " . gmdate("m/d/Y", $end_of_week);
			
			break;    
    	case "DATE_MONTH":
			$value = intval($value) + intval($this->model->UTC_time_offset);		
    		$result = gmdate("F Y", $value);
			break;    
    	case "DATE_QUARTER":
			$value = intval($value) + intval($this->model->UTC_time_offset);
			$month = gmdate("m", $value);
			$year = gmdate("Y", $value);
			$quarter = ceil(($month * 4) / 12);
			$result = 'Q'.$quarter . ' ' . $year;
			break;
    	case "DATE_YEAR":
			$value = intval($value) + intval($this->model->UTC_time_offset);
			$result = gmdate("Y", $value);
			break;
		case "DATE_MONTH_IN_YEAR":
			$value = intval($value) + intval($this->model->UTC_time_offset);		
			$result = gmdate("F", $value);
			break;
		case "DATE_DAY_IN_MONTH":
			$value = intval($value) + intval($this->model->UTC_time_offset);		
			$result = gmdate("d", $value);
			break;
		default:
			$result = $value;
			break;
    }
	
    return $result;
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


		$groups =& $report->add_child("groups", xml_object::create("groups", NULL, array()));
		$total_summary_row =& $report->add_child("summary_row", xml_object::create("summary_row"));

		for($k=0; $k < sizeof($this->model->display_column_tokens); $k++) {
			$sum_col =& $total_summary_row->add_child("col", xml_object::create("col"));
			//$summary_type_array =& $groupArr['subgroups'][$i]['summary'][$this->model->display_column_tokens[$k]];        
			$summary_type_array =& $this->grouped_results['summary'][$this->model->display_column_tokens[$k]];

			if(is_array($summary_type_array)) {
				foreach($summary_type_array AS $summary_type => $summary_val) {
					$sum_col->add_child("summary", xml_object::create("summary", $summary_val, array("type"=>$summary_type)));
				}
			}
		}
		$this->recurse_groups($this->grouped_results, $groups, 0);
		
	}   
   

	function recurse_groups(&$groupArr, &$groupsElm, $level) {
		//print_r($groupArr);
		//echo "Recursing<br>\n";
		$clToken = $this->model->groupings[$level]['column_token']; 
		$groupsElm->add_child("field", xml_object::create("field", $this->col_info[$clToken]['friendly_name']));
		//$groupsElm->add_attribute("level", $level);
		for($i=0; $i < sizeof($groupArr['subgroups']); $i++) {
			//if(!($i==0 && $level==0))
			//echo "LEVEL ".$level." ON ". ($i+1) . " / ". sizeof($groupArr['subgroups']). "<br>\n";
			$group =& $groupsElm->add_child("group", xml_object::create("group"));  
			//echo "@@@@";print_r($groupArr['subgroup'][$i]);
			$value =& $group->add_child("value", xml_object::create("value", $groupArr['subgroups'][$i]['grouping_value']));
			$total_records =& $group->add_child("total_records", xml_object::create("total_records"));
			$summary_row =& $group->add_child("summary_row", xml_object::create("summary_row"));
			for($k=0; $k < sizeof($this->model->display_column_tokens); $k++) {
				$sum_col =& $summary_row->add_child("col", xml_object::create("col"));
        
				//echo "****".$this->model->display_column_tokens[$k]."****";
				//$hi =$this->model->display_column_tokens[$k];
				//echo $hi . "<br>";
				//print_r($groupArr['subgroups'][$i]['summary']);
				$summary_type_array =& $groupArr['subgroups'][$i]['summary'][$this->model->display_column_tokens[$k]];
				//$summary_type_array =& $groupArr['summary'][$this->model->display_column_tokens[$k]];
				if(is_array($summary_type_array)) {
					foreach($summary_type_array AS $summary_type => $summary_val) {
						$sum_col->add_child("summary", xml_object::create("summary", $summary_val, array("type"=>$summary_type)));
					}
				}
			}

			//if(is_array($this->grouped_results[$i]) {
			//echo "level < sizeof(this->model->groupings) ? " . $level . " < ". sizeof($this->model->groupings) . "<br>\n";
			if($level < sizeof($this->model->groupings)-1) { 
				//echo "TRUE<br>\n";
				$childGroups =& $group->add_child("groups", xml_object::create("groups", NULL, array()));
				$this->recurse_groups($groupArr['subgroups'][$i], $childGroups, $level+1);
			}
			else {
				unset($leafArray);
				//echo "setting leafArray to groupArr[subgroups][".$i."]...".$groupArr[$i]."<br>\n";
				$leafArray =& $groupArr['subgroups'][$i]; //the groupArr we will work with is the one we would have recursed if this past if condition would have been true
				//$groupArr =& $groupArr[$i];
				//echo "FALSE<br>\n";
				//echo "leafArray size=".sizeof($leafArray['subgroups'])."<br>\n";      
				//no more group lists within this element, so instead of a groups elm it will contain data_rows
				//print_r($leafArray);
				$data_rows =& $group->add_child("data_rows", xml_object::create("data_rows"));
				unset($row);
				$row = array();
				for($j=0; $j < sizeof($leafArray['subgroups']); $j++) {
					$row[$j] =& $data_rows->add_child("row", xml_object::create("row"));
					//echo "ADDING ROW  ";
					for($k=0; $k < sizeof($this->model->display_column_tokens); $k++) {
						//echo $leafArray['subgroups'][$j][$k] . "-";
						//put if 
						$col_token = $this->model->display_column_tokens[$k];
						$col =& $row[$j]->add_child("col", xml_object::create("col", $leafArray['subgroups'][$j][$col_token], array()));
						if($this->col_info[$col_token]['type'] == "unixtimestamp") {
							$col->add_attribute("type", "date");
							//$str = gmdate("m/d/Y H:i",$leafArray['subgroups'][$j][$col_token] + $this->model->UTC_time_offset);
							$str = gmdate("m/d/Y",$leafArray['subgroups'][$j][$col_token] + $this->model->UTC_time_offset);
							$col->set_data($str);
						}

						$link = $this->get_link_attribute($leafArray['subgroups'][$j], $col_token);
						if($link != "") {
							$col->add_attribute("href", $link);
						}
					}
					//echo "<br>\n\n";
				}
				//break;
			}
		}
	}   
  
	function get_link_attribute(&$row, $col_token) {
		$link = "";
		switch($col_token) {
			case "COM_NAME":
				$link = "a". $row['COM_ID'];
			break;
			case "OPP_NAME":
				$link = "o".$row['OPP_ID'];
			break;
			case "PGU_NAME_LAST":
			case "PGU_NAME_FIRST":
				$link = "c".$row['PGU_ID'];
			break;
			
		}
		return $link;
	}
	

	function showTimeStamp($str) {
		//$mtime = array_sum(explode(" ",microtime()));
		$mtime = preg_replace('/^0?(\S+) (\S+)$/X', '$2$1', microtime());
		echo $mtime . "-". /* date("m/d/Y H:i:s"). */ "-".$str . " <br>\n";
	}
	
}

