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
| File: ticket_thread_errors.php
|
| Purpose: Assorted errors that can be assigned to individual ticket
| 	threads.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once("site.config.php");

class CER_TICKET_THREAD_ERRORS
{
	var $threads;
	var $db;
	
	function CER_TICKET_THREAD_ERRORS()
	{
		$this->threads = array();
		$this->db = cer_Database::getInstance();
	}

	function _add_error($thread_id,$error_msg)
	{
		if(!isset($this->threads[$thread_id])) $this->threads[$thread_id] = new CER_TICKET_THREAD_ERRORS_ITEM();
		array_push($this->threads[$thread_id]->errors,$error_msg);
	}
	
	function load_errors_by_ticket($ticket_id)
	{
		$sql = "SELECT te.thread_id, te.error_msg FROM thread_errors te WHERE te.ticket_id = " . $ticket_id;
		$te_result = $this->db->query($sql);
		if($this->db->num_rows($te_result))
		{
			while($te_row = $this->db->fetch_row($te_result))
			{ $this->_add_error($te_row["thread_id"],$te_row["error_msg"]); }
		}
	}
	
	function log_thread_errors($thread_id,$ticket_id,$error_msgs)
	{
		$sql = "INSERT INTO thread_errors (ticket_id,thread_id,error_msg) VALUES ";
		$log_parts = array();
		foreach($error_msgs as $error_entry)
		{ 
			array_push($log_parts,sprintf( "(%d,%d,%s)",$ticket_id,$thread_id,$this->db->escape($error_entry) )); 
		}
		$sql .= implode(",",$log_parts);
		$this->db->query($sql);
	}
	
	function thread_has_errors($thread_id)
	{
		if(isset($this->threads[$thread_id])) return true;
		else return false;
	}
	
};

class CER_TICKET_THREAD_ERRORS_ITEM
{
	var $errors = array();
};
?>