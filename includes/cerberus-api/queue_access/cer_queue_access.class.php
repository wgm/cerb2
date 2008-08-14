<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
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
/*!
	\file cer_queue_access.class.php
	\brief Determine & manage queue access rights for a user.
	
	\author Jeff Standen, jeff@webgroupmedia.com
	\date 2002-2003
*/

//! Cerberus queue access object
/*!
	Determines the access levels in various queues carried by the user.
*/
class CER_QUEUE_ACCESS
{
	var $db = null;
	var $write_access_list = array(); //!< An array with all the queues the user has write access to
	var $queue_access_list = array(); //!< Read or write access
	var $group_access_list = array();
	var $queues = array();
	
	//! Class constructor
	/*!
	Initialize the array \a $write_access_list and import the user's writable queues.
	*/
	function CER_QUEUE_ACCESS()
	{
		$this->db = cer_Database::getInstance();
		$this->_build_user_queue_access_list();
	}
  
	//! Determine if the user has write access to the given queue
	/*!
		\param $qid an \c integer queue id
		\return Boolean (true/false)
	*/
	function has_write_access($qid=-1)
		{
		if(isset($this->write_access_list[$qid])) { 
				return true;
			}
		else
			return false;
	}

	function has_read_access($qid=-1)
		{
		if(isset($this->queue_access_list[$qid])) { 
				return true;
			}
		else
			return false;
	}
	
	function elements_in_both_lists($list1,$list2)
	{
		$in_both = array();
		
		$arr1 = explode(",",$list1);
		$arr2 = explode(",",$list2);
		
		foreach($arr1 as $a) {
			if(array_search($a,$arr2) !== false)
				$in_both[] = $a;
		}
		
		return implode(",",$in_both);
	}
	
	function get_writeable_qid_list()
	{
		$list = implode(",",array_keys($this->write_access_list));
		if(empty($list)) $list = "-1";
		return $list;
	}
	
	function get_readable_qid_list()
	{
		$list = implode(",",array_keys($this->queue_access_list));
		if(empty($list)) $list = "-1";
		return $list;
	}
	
	//! Return the number of queues the user has write access to
	/*!
		\return \c integer number of queues
	*/
	function number_writeable_queues()
	{
		$num = count($this->write_access_list);
		if(empty($num)) $num=0;
		return $num;
	}

	function get_queue_name($qid) {
		return $this->group_access_list[$qid]->queue_name;
	}
	
	function _build_user_queue_access_list()
	{
		global $session;
		
		$uid = $session->vars["login_handler"]->user_id;
		$is_superuser = $session->vars["login_handler"]->user_superuser;
		$gid = $session->vars["login_handler"]->user_access->group_id;
		
		// [JAS]: Group Queue Access Defaults for this User
		$sql = "SELECT q.queue_name, qa.`queue_id`, qa.`queue_access` FROM `queue_group_access` qa ".
			"LEFT JOIN queue q ON (qa.queue_id = q.queue_id) ".
			"WHERE qa.group_id = $gid ".
			"ORDER BY q.queue_name";
		$res = $this->db->query($sql);

		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				$new_queue = new CER_QUEUE_ACCESS_OBJECT();
				$new_queue->queue_id = $row["queue_id"];
				$new_queue->queue_name = $row["queue_name"];
				$new_queue->queue_access = $row["queue_access"];
				$new_queue->queue_watcher = 0;
				$this->queues[$new_queue->queue_id] = $new_queue;
				$this->group_access_list[$new_queue->queue_id] = &$this->queues[$new_queue->queue_id];
			}
		}
		
		// [JAS]: User Queue Access Overrides
		//	Give superusers access to all queues
		if($is_superuser)
			$sql = "SELECT q.queue_name, q.queue_id, 'write' as queue_access, qa.queue_watch FROM queue q ".
				"LEFT JOIN queue_access qa ON (qa.queue_id = q.queue_id AND qa.user_id = $uid) ".
				"ORDER BY q.queue_name";
		else
			$sql = "SELECT q.`queue_name`, qa.`queue_id`, qa.`queue_access`, qa.`queue_watch` FROM `queue` q ".
				"LEFT JOIN `queue_access` qa ON (qa.queue_id = q.queue_id AND qa.user_id = $uid) ".
				"ORDER BY q.queue_name";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				unset($q_ptr);
                $q_ptr = null;
                
				if(isset($this->queues[$row["queue_id"]])) {
					$q_ptr = &$this->queues[$row["queue_id"]];
				}
				
				if($q_ptr) {
					if(!empty($row["queue_access"])) $q_ptr->queue_access = $row["queue_access"];
					$q_ptr->queue_watcher = $row["queue_watch"];
				}
				else {
					if(!empty($row["queue_access"]))
					{
						$new_queue = new CER_QUEUE_ACCESS_OBJECT();
						$new_queue->queue_id = $row["queue_id"];
						$new_queue->queue_name = $row["queue_name"];
						$new_queue->queue_access = $row["queue_access"];
						$new_queue->queue_watcher = $row["queue_watch"];
						$this->queues[$new_queue->queue_id] = $new_queue;
					}
				}
			}
		}
		
		// [JAS]: Make pointer arrays of writeable/readable queues
		foreach($this->queues as $idx => $q)
		{
			switch($q->queue_access)
			{
				case "write":
				{
					$this->write_access_list[$q->queue_id] = &$this->queues[$idx];
					$this->queue_access_list[$q->queue_id] = &$this->queues[$idx];
					break;
				}
				case "read":
				{
					$this->queue_access_list[$q->queue_id] = &$this->queues[$idx];
					break;
				}
			}
		}
	}

	
};

class CER_QUEUE_ACCESS_OBJECT
{
	var $queue_id = 0;
	var $queue_name = null;
	var $queue_access = "none";
	var $queue_watcher = 0;
};
?>