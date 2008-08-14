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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

define("CER_SYSTEMSTATS_TICKET",1);
define("CER_SYSTEMSTATS_THREAD",2);

class cer_SystemStats {
	var $db=null;
	
	function cer_SystemStats() {
		$this->db = cer_Database::getInstance();
	}
	
	function _getDate($epoch=null) {
		if(!empty($epoch)) {
			$server_date = new cer_DateTime($epoch);
			$date = $server_date->getDate("%Y-%m-%d");
		}
		else {
			$server_date = new cer_DateTime(date("Y-m-d H:i:s"));
			$date = $server_date->getDate("%Y-%m-%d");
		}
		return $date;
	}
	
	
	function _queryStats($date, $type, $extra=0) {
		$stats = array(); // return array of the stats
		
		$select = "SELECT SUM(`stat_count`) as stat_count_sum ";
		
		$where_extra = "";
		if(!empty($extra)) {
			// if we're also getting the extra info, do not use SUM()
			$select = "SELECT `stat_count` as stat_count_sum ,`stat_extra` ";
			$where_extra = sprintf("`stat_extra` = %d AND ", $extra);
		}
		
		$sql = sprintf("%s ".
						"FROM stats_system ".
						"WHERE %s `stat_type`=%d AND `stat_date`='%s'",
							$select,
							$where_extra,
							$type,
							$date
						);
		$result = $this->db->query($sql);

		if(0<$this->db->num_rows($result)) {
			while($row = $this->db->fetch_row($result)) {
				$stat_extra = 0;
				if(!empty($extra)) {
					$stat_extra = $row["stat_extra"];
				}
				
				// extract the sum before so we can test
				// if sum() returned NULL
				$sum = $row["stat_count_sum"];
				if(!empty($sum)) {
					$stats[$stat_extra] = $sum;
				}
			}
		}
		
		// since the sum() function always returns something,
		// if it didn't have a sum() of anything it returns null.
		// check that the stats array has something, if not, add
		// zero to the results.
		if(empty($stats)) {
			$stats[0]=0;
		}
		
		return $stats;
	}
	
	function _incrementStat($date, $type, $extra=0, $count=1) {
		
		$where_extra = "";
		if(!empty($extra)) {
			$where_extra = sprintf("`stat_extra` = %d AND ", $extra);
		}
		
		// select row we want to update from the db
		$sql = sprintf("SELECT `stat_count` ".
						"FROM stats_system ".
						"WHERE %s `stat_type`=%d AND `stat_date`='%s'",
							$where_extra,
							$type,
							$date
						);
		$result=$this->db->query($sql);
		
		// if the row exists, update the row count				
		if($this->db->grab_first_row($result)) {
			$sql = sprintf("UPDATE `stats_system` ".
							"SET `stat_count`=`stat_count`+%d ".
							"WHERE %s `stat_type`=%d AND `stat_date`='%s'",
								$count,
								$where_extra,
								$type,
								$date
							);
			$result=$this->db->query($sql);
		}
		else {
			// if the row doesn't exist, insert one with a count of 1
			$sql = sprintf("INSERT INTO `stats_system` (`stat_date`,`stat_type`,`stat_extra`,`stat_count`) ".
							" VALUES ('%s',%d,%d,%d)",
								$date,
								$type,
								$extra,
								$count
							);
			$result=$this->db->query($sql);
		}
	}
	
	function incrementTicket($queue_id=0) {
		if(!empty($queue_id))
			$this->_incrementStat($this->_getDate(),CER_SYSTEMSTATS_TICKET,$queue_id);
	}

	function getTicketCount($epoch=0) {
		$stats = null;
		
		if(!empty($epoch)) {
			// pass a zero as the last parameter because we want the stats for all tickets,
			// not grouped by queue_id
			$stats = $this->_queryStats($this->_getDate($epoch), CER_SYSTEMSTATS_TICKET, 0);
		}
		
		return $stats;	
	}
	
};

?>