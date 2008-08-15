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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");

class thread_content_handler extends cer_ThreadContentHandler 
{
   
   function thread_content_handler() {
      $this->cer_ThreadContentHandler();
   }
   
   function load_ticket_content($ticket, $max_thread_id) {
		$sql = sprintf("SELECT t.ticket_id, th.thread_id ".
				"FROM (ticket t, thread th) ".
				"WHERE th.ticket_id = t.ticket_id ".
				"AND t.ticket_id = %d AND th.thread_id > %d " .
				"ORDER BY t.ticket_id, th.thread_id ",
					$ticket, $max_thread_id
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
}