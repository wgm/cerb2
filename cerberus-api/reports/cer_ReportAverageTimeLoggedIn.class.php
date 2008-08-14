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

class cer_ReportAverageTimeLoggedIn_User {
	var $samples = 0;
	var $avg_login_time = 0;
	
	function cer_ReportAverageTimeLoggedIn_User($samples,$login_time) {
		$this->samples = $samples;
		$this->avg_login_time = $login_time;
	}
};

class cer_ReportAverageTimeLoggedIn {
	var $db = null;
	var $avg_login_times = array();
	
	function cer_ReportAverageTimeLoggedIn() {
		$this->db = cer_Database::getInstance();
	}
	
	function getUserAverageTimeLoggedIn($uids=array()) {
		$sql = "select ll.user_id, count(ll.id) as samples, avg(ll.logged_secs) as avg_login_time ".
				"FROM user_login_log ll ".
				"WHERE local_time_logout > '0000-00-00 00:00:00' ".
				"GROUP BY user_id";
		$res = $this->db->query($sql);

		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$time = (($row["avg_login_time"]) ? $row["avg_login_time"] : "0");
				$this->avg_login_times[$row["user_id"]] = new cer_ReportAverageTimeLoggedIn_User($row["samples"],$time);
			}
			
			return $this->avg_login_times;
		}

		return array();
	}
	
};

?>