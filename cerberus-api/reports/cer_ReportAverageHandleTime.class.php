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

//require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");

class cer_ReportAverageHandleTime_User
{
	var $user_id = null;
	var $user_name = null;
	var $user_login = null;
	var $average_response_time = 0;
	var $average_forward_time = 0;
	var $r_samples = 0;
	var $f_samples = 0;
	var $r_response_times = array();
	var $f_response_times = array();
};

class cer_ReportAverageHandleTime_Queue
{
	var $queue_id = null;
	var $queue_name = null;
	var $average_response_time = 0;
	var $average_forward_time = 0;
	var $r_samples = 0;
	var $f_samples = 0;
	var $user_times = array();
};

class cer_ReportAverageHandleTime_Group
{
	var $group_id = null;
	var $group_name = null;
	var $average_response_time = 0;
	var $average_forward_time = 0;
	var $r_samples = 0;
	var $f_samples = 0;
	var $user_times = array();
};

class cer_ReportAverageHandleTime {
	var $db = null;
	var $user_times = array();
	
	function cer_ReportAverageHandleTime() {
		$this->db = cer_Database::getInstance();
	}
	
	function getUserAverageHandleTimes($uids=array(),$params=array()) {
				
		$sql = sprintf("SELECT log.ticket_id, log.epoch, log.user_id, log.action, u.user_name, u.user_login  ".
			"FROM `ticket_audit_log` log ".
			"LEFT JOIN `user` u USING (user_id) ".
			"LEFT JOIN `ticket` t ON (t.ticket_id = log.ticket_id) ".
			"WHERE log.action IN ( %d, %d, %d, %d ) ".
			"%s ".
			"AND log.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					1, // created
					4, // agent reply
					8, // req reply
					17, // agent forward
					(($params["queue_id"]) ? "AND t.ticket_queue_id = " . $params["queue_id"] . " " : ""),
					$params["from_date"],
					$params["to_date"]
					).
			"ORDER BY log.ticket_id, log.timestamp";
		$rt_res = $this->db->query($sql);

		// [JAS]: If we have data for factoring initial response time
		$row_count = $this->db->num_rows($rt_res);
		
		if($row_count && $row_count > 1)
		{
			$last_ticket_id = -1;
			$user_times = array();
			
			while($rt = $this->db->fetch_row($rt_res))
			{
				// [JAS]: If we're analyzing a new ticket, reset our pair search
				if($rt["ticket_id"] != $last_ticket_id)
				{
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
				}
				
				// [JAS]: Isolate a reply from requester and a staff member in that order.  Ignore anything
				//		that breaks this pattern.  Use earliest instances of both (i.e. a customer writing back
				//		3 times before a reply should have response time factored from message 1 alone.
				
				switch($rt["action"])
				{
					case 1: // Ticket open
					case 8: // Requester reply
						if(empty($reply_pair[0])) {
							$reply_pair[0] = -1;
							$epoch_pair[0] = $rt["epoch"];
						}
					break;
					
					case 4: // Agent reply
					case 17: // Agent forward
						if(!empty($reply_pair[0])) { // [JAS]: only add if we have an open
							$reply_pair[1] = $rt["user_id"];
							$epoch_pair[1] = $rt["epoch"];
							$type_pair[1] = (($rt["action"] == 4) ? "r" : "f"); // [JAS]: 'r'eply or 'f'orward
						}
					break;
				}
				
				// [JAS]: We have a pair.
				if(!empty($reply_pair[0]) && !empty($reply_pair[1]))
				{
					$uid = $reply_pair[1];
							
					// [JAS]: If the selected user doesn't match, ignore
					if (!empty($uids) && !isset($uids[$uid]))
						continue;
						
					if(!empty($rt["user_login"]))
					{
						$time_diff = ($epoch_pair[1] - $epoch_pair[0]);
						if(!isset($user_times[$uid])) {
							$user_times[$uid] = new cer_ReportAverageHandleTime_User();
							$user_times[$uid]->user_id = $rt["user_id"];
							$user_times[$uid]->user_name = $rt["user_name"];
							$user_times[$uid]->user_login = $rt["user_login"];
						}
						
						if ($type_pair[1] == "r") {
							array_push($user_times[$uid]->r_response_times,$time_diff);
						}
						else if ($type_pair[1] == "f") {
							array_push($user_times[$uid]->f_response_times,$time_diff);
						}
					}
					
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
					$type_pair = array(0,0);
				}
				
				$last_ticket_id = $rt["ticket_id"];
			}
			
			foreach($user_times as $idx => $ut)
			{
				$avg_reply_time = 0;
				$avg_forward_time = 0;
				
				foreach($ut->r_response_times as $r_time)
					$avg_reply_time += $r_time;
					
				foreach($ut->f_response_times as $f_time)
					$avg_forward_time += $f_time;
				
				$user_times[$idx]->r_samples = count($user_times[$idx]->r_response_times);
				$user_times[$idx]->f_samples = count($user_times[$idx]->f_response_times);
				
				if($user_times[$idx]->r_samples)
					$user_times[$idx]->average_response_time = round($avg_reply_time / $user_times[$idx]->r_samples);
				
				if($user_times[$idx]->f_samples)
					$user_times[$idx]->average_forward_time = round($avg_forward_time / $user_times[$idx]->f_samples);
				
				unset($user_times[$idx]->response_times);
				unset($user_times[$idx]->forward_times);
			}
			
			$this->user_times = $user_times;
		
		return $this->user_times;
		}
	
	return array();
	}

	
	function getQueueUserAverageHandleTimes($uids=array(),$params=array()) {
				
		$sql = sprintf("SELECT log.ticket_id, log.epoch, log.user_id, log.action, u.user_name, u.user_login, t.ticket_queue_id, q.queue_name  ".
			"FROM `ticket_audit_log` log ".
			"LEFT JOIN `user` u USING (user_id) ".
			"LEFT JOIN `ticket` t ON (t.ticket_id = log.ticket_id) ".
			"LEFT JOIN `queue` q ON (q.queue_id = t.ticket_queue_id) ".
			"WHERE log.action IN ( %d, %d, %d, %d ) ".
			"%s ".
			"AND log.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					1, // created
					4, // agent reply
					8, // req reply
					17, // agent forward
					(($params["queue_id"]) ? "AND t.ticket_queue_id = " . $params["queue_id"] . " " : ""),
					$params["from_date"],
					$params["to_date"]
					).
			"ORDER BY log.ticket_id, log.timestamp";
		$rt_res = $this->db->query($sql);

		// [JAS]: If we have data for factoring initial response time
		$row_count = $this->db->num_rows($rt_res);
		
		if($row_count && $row_count > 1)
		{
			$last_ticket_id = -1;
			$queue_times = array();
			
			while($rt = $this->db->fetch_row($rt_res))
			{
				// [JAS]: If we're analyzing a new ticket, reset our pair search
				if($rt["ticket_id"] != $last_ticket_id)
				{
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
				}
				
				// [JAS]: Isolate a reply from requester and a staff member in that order.  Ignore anything
				//		that breaks this pattern.  Use earliest instances of both (i.e. a customer writing back
				//		3 times before a reply should have response time factored from message 1 alone.
				
				switch($rt["action"])
				{
					case 1: // Ticket open
					case 8: // Requester reply
						if(empty($reply_pair[0])) {
							$reply_pair[0] = -1;
							$epoch_pair[0] = $rt["epoch"];
						}
					break;
					
					case 4: // Agent reply
					case 17: // Agent forward
						if(!empty($reply_pair[0])) { // [JAS]: only add if we have an open
							$reply_pair[1] = $rt["user_id"];
							$epoch_pair[1] = $rt["epoch"];
							$type_pair[1] = (($rt["action"] == 4) ? "r" : "f"); // [JAS]: 'r'eply or 'f'orward
						}
					break;
				}
				
				// [JAS]: We have a pair.
				if(!empty($reply_pair[0]) && !empty($reply_pair[1]))
				{
					$uid = $reply_pair[1];
							
					// [JAS]: If the selected user doesn't match, ignore
					if (!empty($uids) && !isset($uids[$uid]))
						continue;
						
					if(!empty($rt["user_login"]))
					{
						$time_diff = ($epoch_pair[1] - $epoch_pair[0]);
						$uid = $reply_pair[1];
						$gid = $rt["ticket_queue_id"];
						
						if(!isset($queue_times[$gid])) {
							$queue_times[$gid] = new cer_ReportAverageHandleTime_Queue();
							$queue_times[$gid]->queue_id = $rt["ticket_queue_id"];
							$queue_times[$gid]->queue_name = $rt["queue_name"];
						}
						
						if(!isset($queue_times[$gid]->user_times[$uid])) {
							$queue_times[$gid]->user_times[$uid] = new cer_ReportAverageHandleTime_User();
							$u_ptr = &$queue_times[$gid]->user_times[$uid];
							$u_ptr->user_id = $rt["user_id"];
							$u_ptr->user_name = $rt["user_name"];
							$u_ptr->user_login = $rt["user_login"];
						}
						
						$u_ptr = &$queue_times[$gid]->user_times[$uid];
						
						if ($type_pair[1] == "r") {
							array_push($u_ptr->r_response_times,$time_diff);
						}
						else if ($type_pair[1] == "f") {
							array_push($u_ptr->f_response_times,$time_diff);
						}
						
//						array_push($u_ptr->tickets,$rt["ticket_id"]);
					}
					
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
					$type_pair = array(0,0);
				}
				
				$last_ticket_id = $rt["ticket_id"];
			}
			
			
			foreach($queue_times as $q_idx => $qt)
			{
				$queue_avg_reply = new cer_WeightedAverage();
				$queue_avg_forward = new cer_WeightedAverage();
				$g_ptr = &$queue_times[$q_idx];
				
				foreach($qt->user_times as $idx => $ut)
				{
					$avg_reply_time = 0;
					$avg_forward_time = 0;
					
					$u_ptr = &$queue_times[$q_idx]->user_times[$idx];
					
					$r_samples = count($u_ptr->r_response_times);
					$f_samples = count($u_ptr->f_response_times);
					
					foreach($ut->r_response_times as $r_time) {
						$avg_reply_time += $r_time;
						$queue_avg_reply->addSample($r_time,$r_samples);
					}
						
					foreach($ut->f_response_times as $f_time) {
						$avg_forward_time += $f_time;
						$queue_avg_forward->addSample($f_time,$f_samples);
					}
					
					$u_ptr->r_samples = $r_samples;
					$u_ptr->f_samples = $f_samples;
					
					if($u_ptr->r_samples) {
						$u_ptr->average_response_time = round($avg_reply_time / $u_ptr->r_samples);
						$g_ptr->r_samples += $r_samples;
					}
					
					if($u_ptr->f_samples) {
						$u_ptr->average_forward_time = round($avg_forward_time / $u_ptr->f_samples);
						$g_ptr->f_samples += $f_samples;
					}
					
					unset($u_ptr->response_times);
					unset($u_ptr->forward_times);
				}
				
				$g_ptr->average_response_time = $queue_avg_reply->getAverage();
				$g_ptr->average_forward_time = $queue_avg_forward->getAverage();
			}
			
//		echo "<pre>"; print_r($queue_times); echo "</pre>";
			
		return $queue_times;
		}
	
	return array();
	}

	
	function getGroupUserAverageHandleTimes($uids=array(),$params=array()) {
				
		$sql = sprintf("SELECT log.ticket_id, log.epoch, log.user_id, log.action, u.user_name, u.user_login, u.user_group_id, g.group_name ".
			"FROM `ticket_audit_log` log ".
			"LEFT JOIN `user` u USING (user_id) ".
			"LEFT JOIN `ticket` t ON (t.ticket_id = log.ticket_id) ".
			"LEFT JOIN `user_access_levels` g ON (g.group_id = u.user_group_id) ".
			"WHERE log.action IN ( %d, %d, %d, %d ) ".
			"%s ".
			"AND log.timestamp BETWEEN  '%s 00:00:00' AND  '%s 23:59:59' ",
					1, // created
					4, // agent reply
					8, // req reply
					17, // agent forward
					(($params["queue_id"]) ? "AND t.ticket_queue_id = " . $params["queue_id"] . " " : ""),
					$params["from_date"],
					$params["to_date"]
					).
			"ORDER BY log.ticket_id, log.timestamp";
		$rt_res = $this->db->query($sql);

		// [JAS]: If we have data for factoring initial response time
		$row_count = $this->db->num_rows($rt_res);
		
		if($row_count && $row_count > 1)
		{
			$last_ticket_id = -1;
			$group_times = array();
			
			while($rt = $this->db->fetch_row($rt_res))
			{
				// [JAS]: If we're analyzing a new ticket, reset our pair search
				if($rt["ticket_id"] != $last_ticket_id)
				{
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
				}
				
				// [JAS]: Isolate a reply from requester and a staff member in that order.  Ignore anything
				//		that breaks this pattern.  Use earliest instances of both (i.e. a customer writing back
				//		3 times before a reply should have response time factored from message 1 alone.
				
				switch($rt["action"])
				{
					case 1: // Ticket open
					case 8: // Requester reply
						if(empty($reply_pair[0])) {
							$reply_pair[0] = -1;
							$epoch_pair[0] = $rt["epoch"];
						}
					break;
					
					case 4: // Agent reply
					case 17: // Agent forward
						if(!empty($reply_pair[0])) { // [JAS]: only add if we have an open
							$reply_pair[1] = $rt["user_id"];
							$epoch_pair[1] = $rt["epoch"];
							$type_pair[1] = (($rt["action"] == 4) ? "r" : "f"); // [JAS]: 'r'eply or 'f'orward
						}
					break;
				}
				
				// [JAS]: We have a pair.
				if(!empty($reply_pair[0]) && !empty($reply_pair[1]))
				{
					$uid = $reply_pair[1];
							
					// [JAS]: If the selected user doesn't match, ignore
					if (!empty($uids) && !isset($uids[$uid]))
						continue;
						
					if(!empty($rt["user_login"]))
					{
						$time_diff = ($epoch_pair[1] - $epoch_pair[0]);
						$uid = $reply_pair[1];
						$gid = $rt["user_group_id"];
						
						if(!isset($group_times[$gid])) {
							$group_times[$gid] = new cer_ReportAverageHandleTime_Group();
							$group_times[$gid]->group_id = $gid;
							$group_name = (($rt["group_name"]) ? stripslashes($rt["group_name"]) : "Unassigned");
							$group_times[$gid]->group_name = $group_name;
						}
						
						if(!isset($group_times[$gid]->user_times[$uid])) {
							$group_times[$gid]->user_times[$uid] = new cer_ReportAverageHandleTime_User();
							$u_ptr = &$group_times[$gid]->user_times[$uid];
							$u_ptr->user_id = $uid;
							$u_ptr->user_name = stripslashes($rt["user_name"]);
							$u_ptr->user_login = stripslashes($rt["user_login"]);
						}
						
						$u_ptr = &$group_times[$gid]->user_times[$uid];
						
						if ($type_pair[1] == "r") {
							array_push($u_ptr->r_response_times,$time_diff);
						}
						else if ($type_pair[1] == "f") {
							array_push($u_ptr->f_response_times,$time_diff);
						}
					}
					
					$reply_pair = array(0,0);
					$epoch_pair = array(0,0);
					$type_pair = array(0,0);
				}
				
				$last_ticket_id = $rt["ticket_id"];
			}
			
			foreach($group_times as $g_idx => $gt)
			{
				$group_avg_reply = new cer_WeightedAverage();
				$group_avg_forward = new cer_WeightedAverage();
				$g_ptr = &$group_times[$g_idx];
				
				foreach($gt->user_times as $idx => $ut)
				{
					$avg_reply_time = 0;
					$avg_forward_time = 0;
					
					$u_ptr = &$group_times[$g_idx]->user_times[$idx];
					
					$r_samples = count($u_ptr->r_response_times);
					$f_samples = count($u_ptr->f_response_times);
					
					foreach($ut->r_response_times as $r_time) {
						$avg_reply_time += $r_time;
						$group_avg_reply->addSample($r_time,$r_samples);
					}
						
					foreach($ut->f_response_times as $f_time) {
						$avg_forward_time += $f_time;
						$group_avg_forward->addSample($f_time,$f_samples);
					}
					
					$u_ptr->r_samples = $r_samples;
					$u_ptr->f_samples = $f_samples;
					
					if($u_ptr->r_samples) {
						$u_ptr->average_response_time = round($avg_reply_time / $u_ptr->r_samples);
						$g_ptr->r_samples += $r_samples;
					}
					
					if($u_ptr->f_samples) {
						$u_ptr->average_forward_time = round($avg_forward_time / $u_ptr->f_samples);
						$g_ptr->f_samples += $f_samples;
					}
					
					unset($u_ptr->response_times);
					unset($u_ptr->forward_times);
				}
				
				$g_ptr->average_response_time = $group_avg_reply->getAverage();
				$g_ptr->average_forward_time = $group_avg_forward->getAverage();
			}
			
//		echo "<pre>"; print_r($group_times); echo "</pre>";
			
		return $group_times;
		}
	
	return array();
	}
	
};

?>