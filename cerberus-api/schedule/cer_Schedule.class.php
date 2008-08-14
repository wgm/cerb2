<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class cer_ScheduleHandler
{
	var $db = null;
	var $schedules = array();

	var $hrs_24 = array("00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23");
	var $hrs_12 = array("12","1","2","3","4","5","6","7","8","9","10","11","12","1","2","3","4","5","6","7","8","9","10","11");
	var $mins = array("00","15","30","45");
	
	var $days = array(
		"Sunday" => "sun",
		"Monday" => "mon",
		"Tuesday" => "tue",
		"Wednesday" => "wed",
		"Thursday" => "thu",
		"Friday" => "fri",
		"Saturday" => "sat",
	);
	
	var $times_opt = array();
	
	function cer_ScheduleHandler() {
		$this->db = cer_Database::getInstance();
		
		foreach($this->hrs_24 as $hr_i => $hr) {
			foreach($this->mins as $min) {
				$time_24h = $hr . ':' . $min;
				$time_12h = $this->hrs_12[$hr_i] . ':' . $min . (($hr >= 12) ? "p" : "a");
				$this->times_opt[$time_24h] = $time_12h;
			}
		}
		
		$this->_loadSchedules();
	}
	
	function _loadSchedules() {
		$sql = "SELECT s.schedule_id, s.schedule_name, ".
			"s.sun_hrs, s.sun_open, s.sun_close, ".
			"s.mon_hrs, s.mon_open, s.mon_close, ".
			"s.tue_hrs, s.tue_open, s.tue_close, ".
			"s.wed_hrs, s.wed_open, s.wed_close, ".
			"s.thu_hrs, s.thu_open, s.thu_close, ".
			"s.fri_hrs, s.fri_open, s.fri_close, ".
			"s.sat_hrs, s.sat_open, s.sat_close ".
			"FROM schedule s ".
			"ORDER BY s.schedule_name ";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$new_schedule = new cer_Schedule();
					$new_schedule->schedule_id = $row["schedule_id"];
					$new_schedule->schedule_name = stripslashes($row["schedule_name"]);
					$new_schedule->setDayHours(0,"Sun",$row["sun_hrs"],$row["sun_open"],$row["sun_close"]);
					$new_schedule->setDayHours(1,"Mon",$row["mon_hrs"],$row["mon_open"],$row["mon_close"]);
					$new_schedule->setDayHours(2,"Tue",$row["tue_hrs"],$row["tue_open"],$row["tue_close"]);
					$new_schedule->setDayHours(3,"Wed",$row["wed_hrs"],$row["wed_open"],$row["wed_close"]);
					$new_schedule->setDayHours(4,"Thu",$row["thu_hrs"],$row["thu_open"],$row["thu_close"]);
					$new_schedule->setDayHours(5,"Fri",$row["fri_hrs"],$row["fri_open"],$row["fri_close"]);
					$new_schedule->setDayHours(6,"Sat",$row["sat_hrs"],$row["sat_open"],$row["sat_close"]);
				$this->schedules[$row["schedule_id"]] = $new_schedule;
			}
		}
	}
};

class cer_Schedule
{
	var $schedule_id = null;
	var $schedule_name = null;
	var $weekday_hours = array(); // Sun=0 ... Sat=6

	function cer_Schedule() {
		$this->weekday_hours[0] = new cer_ScheduleWeekdayHours();
		$this->weekday_hours[1] = new cer_ScheduleWeekdayHours();
		$this->weekday_hours[2] = new cer_ScheduleWeekdayHours();
		$this->weekday_hours[3] = new cer_ScheduleWeekdayHours();
		$this->weekday_hours[4] = new cer_ScheduleWeekdayHours();
		$this->weekday_hours[5] = new cer_ScheduleWeekdayHours();
		$this->weekday_hours[6] = new cer_ScheduleWeekdayHours();
	}
	
	function setDayHours($day,$day_abbrev="",$hrs="custom",$open="00:00",$close="00:00") {
		$this->weekday_hours[$day] = new cer_ScheduleWeekdayHours($day_abbrev,$hrs,$open,$close);
	}
	
	function isDayScheduledTime($day,$time) {
		
//		echo "Are you guys working yet?\r\n";
		
		if(!$this->isTimeBeforeDayOpen($time,$day)
			&& !$this->isTimeAfterDayClose($time,$day))
				return true;
				
		return false;
	}
	
	function getDayOpenTime($day) {
		if($this->weekday_hours[$day]->hrs == "24hrs")
			return "00:00";
			
//		echo "We open at exactly " . $this->weekday_hours[$day]->open . "\r\n";
			
		return $this->weekday_hours[$day]->open;
	}
	
	function getDayCloseTime($day) {
		if($this->weekday_hours[$day]->hrs == "24hrs")
			return "23:59";
			
//		echo "We close at exactly " . $this->weekday_hours[$day]->close . "\r\n";
		
		return $this->weekday_hours[$day]->close;
	}
	
	function isDayClosed($day) {
		if($this->weekday_hours[$day]->hrs == "closed")
			return true;
		
		return false;
	}
	
	function isTimeBeforeDayOpen($time,$day) {
		if($this->isDayClosed($day)) return false;
		if($this->weekday_hours[$day]->hrs == "24hrs") return false;
		
//		echo "\r\nOn $day we open in " . $this->secsToDayTimeOpen($day,$time) . " secs.\r\n";
		
		if($this->secsToDayTimeOpen($day,$time) > 0) {
//			echo "So we're CLOSED!\r\n";
			return true;
		}
			
//		echo "So we're OPEN!\r\n";
		return false;
	}
	
	function isTimeAfterDayClose($time,$day) {
		if($this->isDayClosed($day)) return true;
		if($this->weekday_hours[$day]->hrs == "24hrs") return false;
		
//		echo "\r\nOn $day we close in " . $this->secsToDayTimeClose($day,$time) . " secs.\r\n";
		
		if($this->secsToDayTimeClose($day,$time) <= 0)
			return true;
			
		return false;
	}
	
	// [JAS]: Expects a time in 00:00 to 24:00 format
	function secsToDayEnd($time) {
		list($c_hr, $c_min) = split(":",$time);
		$now = mktime($c_hr, $c_min, 0);
		$end_day = mktime(23,59,59);
		$secs_left = $end_day - $now;
		return $secs_left;
	}
	
	function secsToDayTimeClose($day,$time) {
		if($this->weekday_hours[$day]->hrs == "24hrs")
			return $this->secsToDayEnd($time);
		
		list($c_hr, $c_min) = split(":",$time);
		$now = mktime($c_hr, $c_min, 0);
		list($cl_hr, $cl_min) = explode(":",$this->weekday_hours[$day]->close);
		$day_close = mktime($cl_hr,$cl_min,00);
		$secs_left = $day_close - $now;
		return $secs_left;
	}
	
	function secsToDayTimeOpen($day,$time) {
		if($this->weekday_hours[$day]->hrs == "24hrs") return 0;
		
		list($c_hr, $c_min) = explode(":",$time);
		$now = mktime($c_hr, $c_min, 0);
		list($o_hr, $o_min) = split(":",$this->weekday_hours[$day]->open);
		$day_close = mktime($o_hr,$o_min,00);
		$secs_left = $day_close - $now;
		return $secs_left;
	}
	
	// [JAS]: Returns what day we end up on if you advance a certain day by a number of days
	// 0 = Sun, 6 = Sat
	function getDayInXDays($day,$days) {
		$day_no = $day;
		
		// this many days
		for($d=0;$d<$days;$d++) {
			$day_no++;
			if($day_no > 6) $day_no = 0;
		}
		
		return $day_no;
	}
	
};

class cer_ScheduleWeekdayHours
{
	var $day_abbrev = null;
	var $hrs = "custom";
	var $open = "00:00";
	var $close = "00:00";
	
	function cer_ScheduleWeekdayHours($day_abbrev="",$hrs="custom",$open="00:00",$close="00:00") {
		$this->day_abbrev = $day_abbrev;
		$this->hrs = $hrs;
		$this->open = $open;
		$this->close = $close;
	}
};

?>