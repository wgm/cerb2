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

require_once(FILESYSTEM_PATH . 'includes/cerberus-api/queue_access/cer_queue_access.class.php');

class cer_QueueHandler {
	var $db = null;
	var $queues = array();
	
	function cer_QueueHandler($ids=array()) {
		$this->db = cer_Database::getInstance();
		$this->_loadQueuesById($ids);
	}
	
	function _loadQueuesById($ids=array()) {
		global $queue_access;
		$q_ids = null;
		
		if(!is_array($ids)) return false;
		
		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();
		
		// [JAS]: Limit the viewer to only queues they are allowed to see.
		$u_qids = $queue_access->get_readable_qid_list();
		if(!empty($ids)) {
			$u_qids = $queue_access->elements_in_both_lists($u_qids,implode(",",$ids));
		}
		
		$sql = sprintf("SELECT q.queue_id, q.queue_name, q.queue_mode, q.queue_default_schedule, q.queue_default_response_time, q.queue_addresses_inherit_qid ".
				"FROM queue q ".
				"%s ".
				"ORDER BY q.queue_name ",
					((!empty($u_qids)) ? "WHERE q.queue_id IN (" . $u_qids . ")"  : "" )
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$new_queue = new cer_Queue();
					$new_queue->queue_id = $row["queue_id"];
					$new_queue->queue_name = stripslashes($row["queue_name"]);
					$new_queue->queue_mode = $row["queue_mode"];
					$new_queue->queue_schedule_id = $row["queue_default_schedule"];
					$new_queue->queue_response_time = $row["queue_default_response_time"];
					$new_queue->queue_addresses_inherit_qid = $row["queue_addresses_inherit_qid"];
				$this->queues[$new_queue->queue_id] = $new_queue;
			}
		}
		
		$this->_loadQueueAddresses();
	}
	
	// [JAS]: First pass: Load up a queues addresses from the database.
	//	Second pass: inherit any parent queue addresses according to the queue ID (container queues/subqueues)
	function _loadQueueAddresses() {
		if(empty($this->queues))
			return;
		
		$qids = array_keys($this->queues);
		
		$sql = sprintf("SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain, qa.queue_id ".
					"FROM queue_addresses qa ".
					"WHERE qa.queue_id IN (%s)",
						implode(",", $qids)
				);
		$res = $this->db->query($sql);
		
		// [JAS]: First pass
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$this->queues[$row["queue_id"]]->queue_addresses[$row["queue_addresses_id"]] = 
					sprintf("%s@%s",
						stripslashes($row["queue_address"]),
						stripslashes($row["queue_domain"])
					);
			}
		}
		
		$this->_loadSharedQueueAddresses();
	}
	
	function _loadSharedQueueAddresses() {
		
		// [JAS]: If we're going to be inheriting queue addresses, we may not have included everything
		//	we need above.  If a share queue isn't set above, add it to a list to be pulled up temporarily.
		$inherit_pull_hash = array();
		
		foreach($this->queues as $qidx => $qdata) {
			$q_share_id = $qdata->queue_addresses_inherit_qid;
			if($q_share_id) { // if we don't have queue data, store the ID pair for lookup
				if(!isset($this->queues[$q_share_id])) {
					$inherit_pull_hash[$qidx] = $q_share_id;
				}
				else { // if exists
					foreach($this->queues[$q_share_id]->queue_addresses as $qa_idx => $qa_val) {
						$this->queues[$qidx]->queue_addresses[$qa_idx] = $qa_val;
					}
				}
			}
		}
		
		// [JAS]: If we need to look up queue addresses, do it now.
		if(!empty($inherit_pull_hash)) {
			
			$share_queues = array();
			
			$sql = sprintf("SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain, qa.queue_id ".
						"FROM queue_addresses qa ".
						"WHERE qa.queue_id IN (%s) ",
							implode(",",array_values($inherit_pull_hash))
					);
			$res = $this->db->query($sql);
			
			if($this->db->num_rows($res)) {
				while($row = $this->db->fetch_row($res)) {
					// [JAS]: Push the share queue addresses into a small stack
					$share_queues[$row["queue_id"]][$row["queue_addresses_id"]] = 
						sprintf("%s@%s",
							stripslashes($row["queue_address"]),
							stripslashes($row["queue_domain"])
						);
				}
				
				// [JAS]: Merge the queue lists into the destination queue
				
				foreach($inherit_pull_hash as $qidx => $qshare) {
					foreach($share_queues[$qshare] as $qai => $qaa) {
						$this->queues[$qidx]->queue_addresses[$qai] = $qaa;
					}
				}
			}
		}
	}
	
};

class cer_Queue {
	var $queue_id = null;
	var $queue_name = null;
	var $queue_mode = null;
	var $queue_schedule_id = null;
	var $queue_response_time = null;
	var $queue_addresses_inherit_qid = null;
	var $queue_addresses = array();
};

?>