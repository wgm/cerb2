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

/*
	Creates xml necessary for "matrix" custom reports.
	These are custom reports where grouping criteria are allowed to be specified
	on the x and y axis.
*/
class reports_custom_matrix extends reports_custom
{
	var $matrices;
	var $unique_grouped_values;
   	var $unique_grouped_values_map;
	
	function reports_custom_matrix(&$model) {

		$this->timing = false;
		if($this->timing) $this->showTimeStamp("start custom report ");

		$this->report_status_message = "OK";
		$this->db =& database_loader::get_instance();
		
		$this->model =& $model;
		//print_r($this->model);
		
		$this->validate_input();
		if($this->report_status_message != "OK")
			return;		
		
		if($this->timing) $this->showTimeStamp("input validated");
		
		//get the DB result sets
		$this->report_data =& $this->get_result_set();
		if($this->report_status_message != "OK") 
			return;

		if($this->timing) $this->showTimeStamp("result sets obtained");			
			
			//build the array so we know all possible grouping values that show up in the result sets
		$this->set_unique_grouped_values();	
		
		if($this->timing) $this->showTimeStamp("unique_groupedvals set");
		
		//build a convenience version of the above array, 
		//flipped so we can access it by grouping value name as the key
		for($i=0; $i < sizeof($this->unique_grouped_values); $i++) {
			$this->unique_grouped_values_map[$i] = array_flip($this->unique_grouped_values[$i]);
		}

		if($this->timing) $this->showTimeStamp("unique group map set");
		
		//build the hierarchical array structure
		$this->get_matrix_arrays();

		if($this->timing) $this->showTimeStamp("matrix array structure built");
		
		//fill in the missing grouping values
		$this->fill_in_matrix_arrays();
		
		if($this->timing) $this->showTimeStamp("array holes filled");
		
		

		//sort the multi dimensional array by keys, so the newly created formerly "missing" values are sorted
		//in the same order as every other branch of the arrays
		$this->sort_matrices_keys();
		if($this->timing) $this->showTimeStamp("sorted by keys");
			
		
		//create the xml output
		$this->create_report_xml();
		
		//exit();
	}
	
	
	function validate_input() {
		//translate all global filter timestamp values to unixtimestamps
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
		
		//translate all column specific filter timestamp values to unixtimestamps
		for($i=0; $i < sizeof($this->model->display_columns); $i++) {
			for($j=0; $j < sizeof($this->model->display_columns[$i]['filters']); $j++) {
				$filter =& $this->model->display_columns[$i]['filters'][$j];
				$col_info = custom_report_mappings::get_col_info($filter['column_token']);
				if($col_info['type'] == "unixtimestamp") {
					$time = strtotime($filter['value']);
					if($time != -1 && $time !== FALSE) {
						//echo "$time + ".$this->model->UTC_time_offset."=".($time + $this->model->UTC_time_offset."<br>"); 
						$filter['value'] = $time + $this->model->UTC_time_offset;
					}
					else {
						$this->report_status_message = "Invalid date string: '".$filter['value']."'";
					}
				}				
			}
		}		
		
	}

	/*
		get the result set for this matrix report model criteria
	*/
	function get_result_set() {
		//result_data gets an array of result sets, one for each display column of this report
		$result_data =& $this->db->get("reports", "get_matrix_report_data", array("dataset"=>$this->model->dataset, "display_columns"=>$this->model->display_columns, "groupings"=>$this->model->groupings, "global_filters"=>$this->model->filters, "first_day_of_week"=>$this->model->first_day_of_week));
		if(!$result_data || !is_array($result_data)) {
			$this->report_status_message = "Unable to obtain result set";
		}
		else {
			for($i=0; $i < sizeof($result_data); $i++) {
				if(!$result_data[$i] || !is_array($result_data[$i])) {
					$this->report_status_message = "Unable to obtain result set";
				}
			}
		}
		return $result_data;
	}
	
	/*
		iterates through the result set to determine all of the unique grouping values
		for every grouping (x & y) specified in the report
		
		the result is stored as an array (one for each group) of arrays of grouping values
		
		This data is useful later when we want to fill in missing grouping values in the
		hierarchical array structure
	*/
	function set_unique_grouped_values() {
		$report_data =& $this->report_data;
		for($k=0; $k < sizeof($this->model->groupings); $k++) {
			$unique_values[$k] = array();
		}
		
		for($i=0; $i < sizeof($report_data); $i++) {
			for($j=0; $j < sizeof($report_data[$i]); $j++) {
				for($k=0; $k < sizeof($this->model->groupings); $k++) {
					$new_value = $report_data[$i][$j]['group__'.$k];
					if(!in_array($new_value, $unique_values[$k])) {
						$unique_values[$k][] = $new_value;
					} 
				}
				
			}
			
		}
		//print_r($unique_values);
		//exit();
		$this->unique_grouped_values =& $unique_values;
	}
	
	/*
		creates a matrix array for each query result set (ie. for each user chosen data column for reporting)
	*/
	function get_matrix_arrays() {
		for($i=0; $i < sizeof($this->report_data); $i++) {
			$this->matrices[$i] = $this->create_matrix_array($this->report_data[$i]);
		}
	}

	/*
		allows us to easily iterate through grouping values by keeping
		an array of indexes (one for each grouping value).
		We increase the lowest level down, and if it is on it's last value,
		then increment the next highest, and reset all lower level indexes
	*/
	function increment_unique_indexes($unique_indexes) {
		//echo "incremented index<br>\n";
		$unique_values =& $this->unique_grouped_values;
		$last_index  = sizeof($unique_indexes) - 1;
		
		for($i = $last_index; $i >= 0; $i--) {
			$last_unique_value_index = sizeof($unique_values[$i]) - 1;
			if($unique_indexes[$i] != $last_unique_value_index) {
				$unique_indexes[$i]++;
				//echo "resetting at index: ".($i+1). "\n";
				$unique_indexes = $this->reset_unique_indexes($unique_indexes, $i+1);
				return $unique_indexes;
			}
		}
		return NULL;
	}
	
	/*
		accomplishes the same thing as increment_unique_indexes(),
		except it takes the grouping val names as the indexes
		and merely converts them to numeric indexes and calls increment_unique_indexes.
		It returns the index array in the same non-numeric key based format as the input parameter
	*/
	function increment_unique_indexes_map($unique_map_indexes) {
		$numeric_indexes = $this->get_numeric_indexes_from_map($unique_map_indexes);
		$incremented = $this->increment_unique_indexes($numeric_indexes);
		if($incremented == NULL)
			return NULL;
		else
			return $this->get_map_indexes_from_numeric($incremented);
	}	
	
	/*
		gets an array of key values that correspond with $this->unique_grouped_values_map
	*/
	function get_map_indexes_from_numeric($unique_indexes) {
		$result = array();
		$unique_values =& $this->unique_grouped_values;
		for($i=0; $i < sizeof($unique_values); $i++) {
			//echo "unique_values[" . $i . "][unique_indexes[".$i."]]<br>\n";
			$result[$i] = $unique_values[$i][$unique_indexes[$i]];
		}
		
		return $result;
	}
	
	/*
		gets an array of key values that correspond with $this->unique_grouped_values_map
	*/
	function get_numeric_indexes_from_map($unique_indexes) {
		$result = array();
		$unique_values =& $this->unique_grouped_values_map;
		
		for($i=0; $i < sizeof($unique_values); $i++) {
			$result[$i] = $unique_values[$i][$unique_indexes[$i]];
		}
		return $result;
	}	
	
	//takes an index array and resets the values to zero starting at the index given by $start
	//this is a helper for function increment_unique_indexes so when it increments any group value index,
	//all further indexes need to be reset to zero
	function reset_unique_indexes($unique_indexes, $start) {
		for($i=$start; $i < sizeof($unique_indexes); $i++) {
			$unique_indexes[$i] = 0;
		}
		return $unique_indexes;
	}	
	
	/*
		create the main hierarchical array structure with a dimension for each grouping (x & y)
		This is created from the SQL query results
	*/
	function create_matrix_array(&$report_data) {
		$result = array();
		$group_values = array();
		
		$unique_values =& $this->unique_grouped_values;
		$unique_values_map =& $this->unique_grouped_values_map;
		
		$group_count = sizeof($this->model->groupings);

		for($i=0; $i < $group_count; $i++) {
			$currVals[$i] = '#^(';
			$groupIdxs[$i] = 0;
		}
		$groupIdxs[0] = -1;
		
		for($i=0; $i < sizeof($report_data); $i++) {
			//$result[$i]['subgroups'] = array();

			for($j=0; $j < $group_count; $j++) {
				$isDifferentGroup = false;				
				$grouping_value = $report_data[$i]['group__'.$j];
				if($grouping_value != $currVals[$j]) {
					
					//the current looped grouped value is different in this record than the last one
					$isDifferentGroup = true;
					$groupIdxs[$j]++;
            
					//set any currentVals further down the tree to the values of this row, and reset such indexes to zero
					for($n=$j+1; $n < sizeof($currVals); $n++) {
						$currVals[$n] = $report_data[$i]['group__'.$n];
						$groupIdxs[$n] = 0;
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
						$subgroup_key = $report_data[$i]['group__'.$k];
						//$tmp =& $tmp[$subgroup_key][$groupIdxs[$k]];
						$tmp =& $tmp[$subgroup_key];
					}
					//$tmp['subgroups'][] =& $report_data[$i];
					$tmp['value'] = $report_data[$i]['data_column'];

					//found a new value for this group type so change the current stored one
					$currVals[$j] = $grouping_value;

					break;//since we've added a node for this report_data row, no need to loop through any additional group columns
				}				
				
			}
			
		}
		//print_r($result);
		//exit();
		return $result;
	}
	
	/*
		Fills in holes in the matrix arrays where no values were found for grouping criteria
	
		If the SQL query didn't find any criteria that met any of the grouping criteria 
		(ie. any groupings value didn't exist combined with any other grouping criteria),
		then we need to fill in these missing totals with a zero value, so the array
		and subarray sizes are predictable, which will make formatting easier later on
	*/
	function fill_in_matrix_arrays() {
		$matrices =& $this->matrices;
		$unique_map =& $this->unique_grouped_values_map;

		for($i=0; $i < sizeof($matrices); $i++) {
			$matrix =& $matrices[$i];
			for($j=0; $j < sizeof($this->model->groupings); $j++) {
				$curr_unique_indexes[$j] = 0; 
			}
			//print_r($curr_unique_indexes);
			$curr_map_indexes = $this->get_map_indexes_from_numeric($curr_unique_indexes);
			//print_r($curr_map_indexes);echo "<br>\n";
			
			while((!empty($curr_map_indexes))) {
				//print_r($curr_map_indexes);echo "<br>\n";
				
				unset($tmp);
				$tmp =& $matrix;
				
				for($j=0; $j < sizeof($curr_map_indexes); $j++) {
					if(!isset($tmp[$curr_map_indexes[$j]])) {
						//echo "CREATING ". $curr_map_indexes[$j] . "<br>\n";
						$tmp[$curr_map_indexes[$j]] = array();
						
						//if this is at the lowest level create a default 0 value
						if($j == (sizeof($curr_map_indexes) -1 )) {
							$tmp[$curr_map_indexes[$j]]['value'] = 0;
						}
					}
					$tmp =& $tmp[$curr_map_indexes[$j]];
				}
				
				
				$curr_map_indexes = $this->increment_unique_indexes_map($curr_map_indexes);
			}
			
			
			//print_r($matrix);
			//echo "<br>\n********************************************************************************<br>\n";
		}
		
	}
	
	/*
		run the recursive sort function on each data column matrix
		(this is done so it is so the data keys exist in a consistent order throughout the array)		
	*/
	function sort_matrices_keys() {
		for($i=0; $i < sizeof($this->matrices); $i++) {
			$this->matrices[$i] = $this->recursive_sort_keys($this->matrices[$i],0);
		}
	}
	
	/*
		recursively sort the keys in array by our user defined comparison function
	*/
	function recursive_sort_keys(&$arr, $level) {
		if(!isset($arr['value']))
			uksort($arr, array($this, "matrix_key_compare"));

		foreach($arr AS $key=>$val) {
			if(is_array($val)) {
				$arr[$key] =& $this->recursive_sort_keys($val, $level+1);
			}
		}
		return $arr;
	}
	
	/*
		compares keys based on what order they are stored in $this->unique_grouped_values_map
	*/
	function matrix_key_compare($a, $b) {
		if($a==$b) return 0;
		
		for($i=0; $i < sizeof($this->unique_grouped_values_map); $i++) {
			$val_branch =& $this->unique_grouped_values_map[$i];
			
			if(isset($val_branch[$a])) {
				return ($val_branch[$a] > $val_branch[$b]) ? 1 : -1;
			}
		}
		
		return 0;
	}
	
	function create_report_xml() {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$report =& $data->add_child("report", xml_object::create("report"));

		$x_group_start_index = sizeof($this->model->y_groupings);
		
		$report->add_child("title", xml_object::create("title", $this->model->title));
		
		$display_columns =& $report->add_child("display_columns", xml_object::create("display_columns"));
		if(is_array($this->model->display_columns))
		for($i=0; $i < sizeof($this->model->display_columns); $i++) {
			$display_col =& $display_columns->add_child("col", xml_object::create("col", $this->model->display_columns[$i]['friendly_name']));
		}
		
		$this->create_header_xml($report);
		
		$this->create_data_items($report);
		
	}

	/*
		draws the header xml which are the rows that show up above the data along the x-axis.
		This includes the columns chosen to be reported on, and all of the x-grouping values
	*/
	function create_header_xml(&$report) {
		
		$header = $report->add_child("header", xml_object::create("header"));
		
		$x_group_start = sizeof($this->model->y_groupings);
		$x_group_end = sizeof($this->unique_grouped_values);
		
		//calculates the number of columns to colspan for the data columns about to be written
		$columns_per_field = 1;
		for($i=$x_group_start; $i < $x_group_end; $i++) {
			$columns_per_field *= sizeof($this->unique_grouped_values[$i]);
		}
		
		$field_row =& $header->add_child("tr", xml_object::create("tr"));
		
		//display some leading blank boxes to the left of the column friendly names at the absolute top-left of the report
		for($j=0; $j < sizeof($this->model->y_groupings); $j++) {
			$field_row->add_child("td", xml_object::create("td", " "));
		}		
		
		//at the top row of the report header we display the friendly name of the columns the user chose to report on
		if(is_array($this->model->display_columns))
		for($i=0; $i < sizeof($this->model->display_columns); $i++) {
			$field_row->add_child("td", xml_object::create("td", $this->model->display_columns[$i]['friendly_name'], array("colspan"=>$columns_per_field, "type"=>"column")));
		}	
		
		
		//loop through each x-grouping so the grouping header information can be displayed in the report
		for($i=$x_group_start, $ii=0; $i < $x_group_end; $i++, $ii++) {
			$col_count = sizeof($this->model->display_columns);

			$colspan = 1;

			//calculate the number of columns that will be needed for this row, and how much each column should span
			for($j=$x_group_start, $k=0; $j < $x_group_end; $j++, $k++) {
				if($k <= $ii) {
					$col_count *= sizeof($this->unique_grouped_values[$j]);
				}
				else {
					$colspan *= sizeof($this->unique_grouped_values[$j]);
				}
			}

			
			$group_row =& $header->add_child("tr", xml_object::create("tr"));
			
			//loop for adding leading header columns that show up left of the actual x-groups
			//the number of these are dependent on the number of y groupings being performed for the report
			for($j=0; $j < sizeof($this->model->y_groupings); $j++) {
				
				if($j == sizeof($this->model->y_groupings)-1) {
					//this is the last filler space before real x grouping headers, so put the grouping column name in this space
					$mapping = custom_report_mappings::get_col_info($this->model->x_groupings[$ii]['column_token']);
					$grp_name = $mapping['friendly_name'];
					$attribs = array("level"=>$ii, "type"=>"group_name", "axis"=>"x");
				}
				else {
					//this will be the value for an empty area in the top left corner of the report
					$grp_name="";
					$attribs = array();
				}
				$group_row->add_child("td", xml_object::create("td", $grp_name, $attribs));
			}
			
			//output a table data for every grouping value (* for every parent etc ) for the currently looped x-group
			for($j=0; $j < $col_count; $j++) {
				$group_value_index = $j % sizeof($this->unique_grouped_values[$i]);
				$group_value = $this->unique_grouped_values[$i][$group_value_index];
				$group_row->add_child("td", xml_object::create("td", $group_value, array("colspan"=>$colspan, "level"=>$ii, "type"=>"group_value", "axis"=>"x")));
			}
			
		}

		//create the row that has the names of the groupings chosen, (sits below the rest of the header rows, but above the data)
		$y_group_names_row =& $header->add_child("tr", xml_object::create("tr"));
		for($i=0; $i < sizeof($this->model->y_groupings); $i++) {
				$mapping = custom_report_mappings::get_col_info($this->model->y_groupings[$i]['column_token']);
				$grp_name = $mapping['friendly_name'];
				$y_group_names_row->add_child("td", xml_object::create("td", $grp_name, array("level"=>$i, "type"=>"group_name", "axis"=>"y")));
		}
		//the rest of the row is just one long horizontal empty block
		$y_group_names_row->add_child("td", xml_object::create("td", "", array("colspan"=>$col_count)));
		
	}
	
	/*
		draws the data for the rows in the report under the header, containing all the totals for the columns
		the user chose, grouped by the grouping criteria chosen
	*/
	function create_data_items(&$report) {
		$data_rows =& $report->add_child("data_items", xml_object::create("data_items"));

		$x_group_start = sizeof($this->model->y_groupings);
		$x_group_end = sizeof($this->unique_grouped_values);
		
		//determine the number of columns per display column that will be in each data row
		$col_count = 1;//sizeof($this->model->display_columns);
		for($i=$x_group_start; $i < $x_group_end; $i++) {
			$col_count *= sizeof($this->unique_grouped_values[$i]);
		}
		
		//create an array to store an index to keep track of the last header grouping value we've output 
		$last_output_group_value_indexes = array();
		for($i=0; $i < $x_group_start; $i++) {
			$last_output_group_value_indexes[$i] = 0;	
		}
		
		//get the flattened version of the matrix arrays array.
		$linear_matrices =& $this->get_linear_matrices();
		
		//split the array in to chunks the size of columns that will be displayed
		for($i=0; $i < sizeof($linear_matrices); $i++) {
			$chunked_matrices[$i] = array_chunk($linear_matrices[$i], $col_count);
		}
		
		//a helper value array which tells us how many rows each grouping value stays at the same value (before changing)
		$group_mod_array = $this->get_group_mod_array();
		//print_r($group_mod_array);exit();
		
		for($i=0; $i < sizeof($chunked_matrices[0]); $i++) {
			$data_row=& $data_rows->add_child("date_row", xml_object::create("data_row"));
			
			//write the vertical axis header values
			for($j=0; $j < $x_group_start; $j++) {
				
				//determine if we should write this group header value this time 
				//(it stays the same when a subgroup has multiple values)
				if($i % $group_mod_array[$j] ==  0) {
					//this header value needs to be written
					$val_index = $last_output_group_value_indexes[$j] % sizeof($this->unique_grouped_values[$j]);
					$data_val = $this->unique_grouped_values[$j][$val_index];
					if(trim($data_val) == "") $data_val = "[BLANK]";//if it's a blank value show a "blank" tag

					$last_output_group_value_indexes[$j]++;
					$yheader_attributes = array("level"=>$j);
				}
				else {
					//the header grouping value should remain the same, so don't output it for this row
					$data_val = " ";
					$yheader_attributes = array("level"=>$j);
				}
					
				$data_row->add_child("data_val", xml_object::create("data_val", $data_val, $yheader_attributes));
			}
			
			
			//write the data items
			for($j=0; $j < sizeof($chunked_matrices); $j++) {
				//$data_item->add_child("data_val", xml_object::create("data_val", $chunked_matrices[$j][$i]));
				for($k=0; $k < sizeof($chunked_matrices[$j][$i]); $k++) {
					$data_row->add_child("data_val", xml_object::create("data_val", $chunked_matrices[$j][$i][$k]));
				}
			}
			
		}
		
	}	

	/*
		returns a helper array for drawing vertical access row headers for the data rows.
		The result array contains information about how often the header value needs to be drawn
	*/
	function get_group_mod_array() {
		$mod_array = array();

		$x_group_count = sizeof($this->model->x_groupings);
		$y_group_count = sizeof($this->model->y_groupings);

		//initialize an item for every x group
		for($i=0; $i < $y_group_count; $i++) {
			$mod_array[$i] = 1;
		}
		
		
		//first, we know that the last grouping needs to write its value every time, so it will always mod by 1
		//$mod_array[$x_group_count-1] = 1;
		
		if($y_group_count > 1)
		for($i=$y_group_count-1; $i > 0; $i--) {
			//echo "mod_array[".$i."] = mod_array[".($i+1). "] * ".sizeof($this->unique_grouped_values[$i]). "<br>\n";
			$mod_array[$i-1] = $mod_array[$i] * sizeof($this->unique_grouped_values[$i]);
		}

		//we know that the last grouping needs to write its value every time, so it will always mod by 1
		//$mod_array[$x_group_count-1] = 1;
		
		
		//print_r($mod_array);exit();
		return $mod_array;
	}
	
	/*
		loops through the "matrices" instance variable and flattens each matrix within it
	*/
	function get_linear_matrices() {
		$linear_matrix = array();
		for($i=0; $i < sizeof($this->matrices); $i++) {
			$matrix =& $this->matrices[$i];
			
			//initialize an iterator
			for($j=0; $j < sizeof($this->unique_grouped_values); $j++) {
				$curr_indexes[$j] = 0;
			} 
			$curr_indexes = $this->get_map_indexes_from_numeric($curr_indexes);
			
			while((!empty($curr_indexes))) {

				unset($tmp);
				$tmp =& $matrix;
				for($j=0; $j < sizeof($curr_indexes); $j++) {
					$tmp =& $tmp[$curr_indexes[$j]];
				}
	
				//$data_item->add_child("data_val", xml_object::create("data_vals", $tmp['value']));
				$linear_matrix[$i][] = $tmp['value'];
				
				$curr_indexes = $this->increment_unique_indexes_map($curr_indexes);
			}
		}				
		return $linear_matrix;
	}
	
}
