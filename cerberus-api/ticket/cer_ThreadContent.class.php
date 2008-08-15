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

require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.class.php");

class cer_ThreadContent {
	var $thread_id = null;
	var $content = null;
	
	function cer_ThreadContent($thread_id) {
		$this->thread_id = $thread_id;
	}
	
	function setContent($content) {
		$this->content .= $content;
	}
};

class cer_ThreadContentHandler {
	var $db = null;
	var $threads = array();
	
	function cer_ThreadContentHandler() {
		$this->db = cer_Database::getInstance();
	}
	
	function loadTicketContentDB($ticket) {
		$sql = sprintf("SELECT t.ticket_id, th.thread_id ".
				"FROM (ticket t, thread th) ".
				"WHERE th.ticket_id = t.ticket_id ".
				"AND t.ticket_id = %d " .
				"ORDER BY t.ticket_id, th.thread_id ",
					$ticket
				);
		$res = $this->db->query($sql);
		
		$th_ids = array();
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$th_ids[] = $row["thread_id"];
			}
		}
		
		$this->loadThreadContent($th_ids);
	}
	
	function loadThreadContent($th_ids) {
		if(!is_array($th_ids)) $th_ids = array($th_ids);
		
		$sql = sprintf("SELECT c.content_id, c.thread_id, c.thread_content_part ".
					"FROM thread_content_part c ".
					"WHERE c.thread_id IN (%s) ".
					"ORDER BY c.thread_id ASC, c.content_id ASC",
						implode(',',$th_ids)
					);
		$res = $this->db->query($sql);
		
		$last_thread_id = 0;
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$thread_id = $row["thread_id"];
				
				if($thread_id != $last_thread_id) {
					$this->threads[$thread_id] = new cer_ThreadContent($thread_id);
				}
				
				$content = $row["thread_content_part"];
				
				if(strlen($content) < 255) $content .= " ";
				
				$this->threads[$thread_id]->setContent($content);
				
				$last_thread_id = $thread_id;
			}
		}
	}
	
	function writeThreadContent($th_id,$content) {
		$chunks = array();
		$values = array();
		
		$chunks = cer_String::strSplit($content,255);
		
		$sql = sprintf("DELETE FROM thread_content_part WHERE thread_id = %d",
				$th_id
			);
		$this->db->query($sql);
		
		foreach($chunks as $chunk) {
			$values[] = sprintf("(%d,%s)",
					$th_id,
					$this->db->escape($chunk)
				);
		}
		
		$sql = sprintf("INSERT INTO thread_content_part (thread_id, thread_content_part) VALUES %s",
				implode(',',$values)
			);
		$this->db->query($sql);
	}
	
};