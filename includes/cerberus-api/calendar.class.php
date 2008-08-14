<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
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
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

// [JAS]: [TODO] Function hook/callback for behavior on each calendar day, etc.

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

class CER_FUNCTION_CALLBACK
{
	var $method_name = null;
	var $method_obj = null;
};

class CER_CALENDAR
{
	var $cal_month_name = null;
	var $cal_month = 0;
	var $cal_year = 0;
	var $cur_month = 0;
	var $cur_day = 0;
	var $cur_year = 0;
	var $mo_offset = 0;
	var $is_this_month = false;
	var $cal_matrix = array();
	var $urls = array();
	var $callback_day_links=null; 			// [JAS]: function callback for drawing a calendar day link
	var $callback_month_links=null; 		// [JAS]: function callback for drawing next/prev month links
	
	// [JAS]: Initialize the calendar object
	function CER_CALENDAR($mo_offset=0)
	{
		global $_SERVER;
		global $mode; // [JAS]: clean up
		
		if(!empty($mo_offset)) $this->mo_offset = $mo_offset;

		$date = new cer_DateTime(date("Y-m-d H:i:s"));
		
		$this->cur_month = $date->getUserDate("%m");
		$this->cur_day = $date->getUserDate("%d");
		$this->cur_year = $date->getUserDate("%Y");

		$this->calculate_month_offset($this->mo_offset);		

		if($this->cur_year == $this->cal_year && $this->cur_month == $this->cal_month) $this->is_this_month = true;
		$this->cal_month_name = strftime("%B",mktime(0,0,0,$this->cal_month,1,$this->cal_year));
	}

	// [JAS]: Set the callback for drawing day links
	function register_callback_day_links($user_func,&$user_obj)
	{
		if(empty($user_func)) return false;
		
		$this->callback_day_links = new CER_FUNCTION_CALLBACK();		
		$this->callback_day_links->method_name = $user_func;
		$this->callback_day_links->method_obj = &$user_obj;
	}

	// [JAS]: Set the callback for drawing day links
	function register_callback_month_links($user_func,&$user_obj)
	{
		if(empty($user_func)) return false;
		
		$this->callback_month_links = new CER_FUNCTION_CALLBACK();		
		$this->callback_month_links->method_name = $user_func;
		$this->callback_month_links->method_obj = &$user_obj;
	}
	
	// [JAS]: Calculate month offset from current month/year
	function calculate_month_offset($mo_offset)
	{
		$set_month = $this->cur_month;
		$set_year = $this->cur_year;
		
		if(!empty($mo_offset))
		{
			$new_month = $set_month + $mo_offset;
			if($new_month < 1)
			{
				while($new_month < 1)
				{ $set_year--; $new_month -= -12; }
			}
			else if ($new_month > 12)
			{
				while($new_month > 12)
				{ $set_year++; $new_month -= 12; }
			}

			$set_month = $new_month;
		}
		
		$this->cal_month = $set_month;
		$this->cal_year = $set_year;	
	}
	
	
	// [JAS]: Build a matrix of weeks & days
	function populate_calendar_matrix()
	{
		$links = array();
		
		$prev_mo = $this->mo_offset - 1;
		$next_mo = $this->mo_offset + 1;
		
		if(!empty($this->callback_month_links))
		$links = call_user_func(
			array(&$this->callback_month_links->method_obj,$this->callback_month_links->method_name),
			$this->mo_offset,
			$prev_mo,
			$next_mo
			);
			
		$this->urls["prev_mo"] = @$links["prev_mo"];
		$this->urls["next_mo"] = @$links["next_mo"];

		// [JAS]: Find what day of the week cal_month starts on (Sun = 0, Mon = 1 ... Sat = 6)
		$month_first_day = date("w", mktime(0,0,0,$this->cal_month,1,$this->cal_year));
		
		$days_in_mo = $this->get_days_in_month($this->cal_month,$this->cal_year);
		
		$day_ptr = 1; // [JAS]: Start on day of month 1
		$week_ptr = $month_first_day; // [JAS]: start on the first day of the week for the 1st of this month (Sat,Sun,etc.)
		
		while($day_ptr <= $days_in_mo)
		{
			$new_week = new CER_CALENDAR_WEEK();
			
			while($week_ptr <= 6 && $day_ptr <= $days_in_mo)
			{
				$new_week->days[$week_ptr]->day = $day_ptr;
				
				// [JAS]: If we have a callback for drawing the day links, run it now for each day
				if($this->callback_day_links != null)
				{
					if(!empty($this->callback_day_links))
					$new_week->days[$week_ptr] = 
						call_user_func(
							array(&$this->callback_day_links->method_obj,$this->callback_day_links->method_name),
							$new_week->days[$week_ptr],
							$this->cal_month,
							$this->cal_year
							);
				}
				
				if($this->is_this_month && $day_ptr == $this->cur_day) $new_week->is_this_week = true;
				
				$day_ptr++;
				$week_ptr++;
			}
			
			$week_ptr = 0;
			array_push($this->cal_matrix,$new_week);
		}
		
	}
	
	// [JAS]: Return how many days are in the current month
	function get_days_in_month($month=0,$year=0)
	{
		if(empty($month)) $month = date("n");
		if(empty($year)) $year = date("Y");
		$days_in_mo = date("t", mktime(0,0,0,$month,1,$year));
		return ($days_in_mo);
	}
	
};

// [JAS]: The week object (7 days, Sun through Sat)
class CER_CALENDAR_WEEK
{
	var $days = array();
	var $is_this_week = false;
	var $d=0;
	
	function CER_CALENDAR_WEEK()
	{
		for($d=0;$d<=6;$d++)
		{ $this->days[$d] = new CER_CALENDAR_DAY(); }
	}

};

class CER_CALENDAR_DAY
{
	var $day;
	var $day_url = '#';
};

?>