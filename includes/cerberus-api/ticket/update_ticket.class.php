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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");

class CER_TICKET_UPDATE extends CER_TICKET_DISPLAY
{
	var $quote_thread = null;									// Copy of thread we're replying to or commentin gon
	var $is_batch = false;										// Are we replying to or commenting on a batch?
	var $user_signature = null;									// User's e-mail signature
	var $user_signature_js = null;
	var $requesters_reply_string = null;
	var $queues = array();
	var $watchers_string = null;
	
	function CER_TICKET_UPDATE()
	{
		$this->db = cer_Database::getInstance();
	}
	
	function build_update()
	{
		$this->CER_TICKET_DISPLAY($this->db); // run parent class constructor
		
		$this->build_ticket();
		
		$this->_mask_ticket_id();
		$this->_check_batch();
		$this->_set_user_signature();
		$this->_get_ticket_queues();
		$this->_get_requester_list();
		$this->_get_watchers();
	}
	
	function _get_watchers()
	{
		$sql = "SELECT u.user_email FROM queue_access qa, user u ".
			"WHERE qa.user_id = u.user_id AND u.user_email != '' AND qa.queue_watch = 1 AND qa.queue_id = " . $this->ticket_queue_id;
        $result = $this->db->query($sql);
        $watchers = array();
        if($this->db->num_rows($result) > 0)
        {
        	while($watrow = $this->db->fetch_row($result))
        	array_push($watchers,$watrow["user_email"]);
        }
        if(count($watchers)>0)
        $this->watchers_string = implode(", ",$watchers);
	}
	
	function _get_requester_list()
	{
		$reqs = array();
		foreach($this->requesters->addresses as $idx => $addy)
		{
			if($addy->suppress != 1)
				array_push($reqs,display_email($addy->address_address));
		}		
		
		if(count($reqs))
			$this->requesters_reply_string = implode(", ",$reqs);
			
		unset($reqs);
	}

	function _get_ticket_queues()
	{
		if(!$this->is_batch)
		{
			$queue =& $this->queue_handler->queues[$this->ticket_queue_id];
			
			if(!empty($queue->queue_addresses)) {
				foreach($queue->queue_addresses as $qa_id => $qa) {
					$this->queues[$qa_id] = $qa;
				}
			}
		}
	}
	
	function _set_user_signature()
	{
		global $session; // fix
		
		$sql = "SELECT sig_content FROM user_sig WHERE user_id = " . $session->vars["login_handler"]->user_id;
		$result = $this->db->query($sql);
		$sig_data = $this->db->fetch_row($result);
		$this->user_signature = stripslashes($sig_data["sig_content"]);
		
		$js_sig = "\n" . addslashes($this->user_signature);
		$js_sig = str_replace("\r","\\r",$js_sig);
		$js_sig = str_replace("\n","\\n",$js_sig);
		
		$this->user_signature_js = $js_sig;
		
		unset($sig_data);
		unset($result);
	}
	
	/*!
	Adds any attachments from the database for this thread to the multiple file upload dialog box.
	This allows users to decide which files they want to forward and which they do not.
	
	\param int $thread_id
	\return boolean
	*/
	function quote_thread_attachments($thread_id)
	{
		global $session;
		require_once(FILESYSTEM_PATH . "includes/cerberus-api/fileuploads/fileuploads.class.php");
		
		$upload_handler = new CER_FILE_UPLOADER();
		
		if(!isset($session->vars["uploaded_file_array"]))
			$session->vars["uploaded_file_array"] = array();
			
		foreach($this->threads as $idx => $thread_ptr) {
			
			if($thread_ptr->type != "email" && $thread_ptr->type != "comment")
				continue;
				
			if($thread_ptr->ptr->thread_id == $thread_id) {
				foreach($thread_ptr->ptr->file_attachments as $file_idx => $file) {
					
					// [JAS]: We don't want to forward system attachments.
					if($file->file_name == "message_source.xml" || $file->file_name == "html_mime_part.html")
						continue;
					
					$tmp_name = tempnam(FILESYSTEM_PATH . "tempdir","cerbtmp_");
					$fp = fopen($tmp_name,"wb");
					
					$sql = "SELECT ap.part_content from thread_attachments_parts ap WHERE ap.file_id = " . $file->file_id;
					$res = $this->db->query($sql);
					
					if($this->db->num_rows($res)) {
						while($row = $this->db->fetch_row($res)) {
							fwrite($fp,$row["part_content"],strlen($row["part_content"]));
						}
					}
					
					fclose($fp);
					
					$tmp_file = array();
					$tmp_file['name'] = $file->file_name;
					$tmp_file['tmp_name'] = $tmp_name;
					$tmp_file['size'] = $file->file_size;
					$tmp_file['type'] = "application/octet-stream";
					
					if(!$file_data = $upload_handler->add_file($tmp_file,0,$session->vars["login_handler"]->user_id)) {
						return false;
					}
					else {
						array_push($session->vars["uploaded_file_array"], $file_data);
					}
				}
			}
		}
	
		return true;
	}
	
	function quote_thread($thread_id="",$quote=0)
	{
		global $session; // fix
		
 		$quotechar=">";		// [JXD]: correct quoting
 		$quoteclass="[>:|]";    // [JXD]: detected quoting characters
 
		if(empty($thread_id)) return false;
		
		@$sigpos = $session->vars["login_handler"]->user_prefs->user_signature_pos;
		@$siginsert = $session->vars["login_handler"]->user_prefs->user_signature_autoinsert;
		if(empty($sigpos)) $sigpos = 0;
		
		$thread_content = "";		

		// [JAS]: Search for the given thread id
 //		if(!$this->is_batch && $quote==1)
 		if(!$this->is_batch )   // [jxdemel] so quote_thread in all cases to have the thread-specific values available
		{
			foreach($this->threads as $idx => $thread_ptr)
			{
				// [JAS]: Fix for #CERB-20, allows quoting of forwards
				if(($thread_ptr->type == "email" || $thread_ptr->type == "comment" || $thread_ptr->type == "forward") 
					&& $thread_ptr->ptr->thread_id == $thread_id)
				{
					$this->quote_thread = $thread_ptr->ptr;
					
 					if ($quote==1) 	// [jxdemel] now we can check for quote
 					{
 						if($sigpos==1 && $siginsert==1) $thread_content .= "\n" . $this->user_signature . "\n\n";
 						
 						// [JAS]: If an Agent wrote the message, don't show their e-mail address to the requester in the quote.
 						//	Instead, show the queue address.
 						$q_addy = ((isset($this->queues[$this->queue_addresses_id])) ? $this->queues[$this->queue_addresses_id] : "");
 						$queue_address = !empty($q_addy) ? $q_addy : "Agent";
 						$thread_display_author = ($thread_ptr->ptr->is_agent_message) ? $queue_address : $this->quote_thread->thread_display_author ;
 						
 						$thread_content .= "On " . $this->quote_thread->thread_date_rfc . ", " . $thread_display_author . " wrote:\n";
 						// delay htmlspecialcharacter treatment to the end
 						$ticket_body_content = $this->quote_thread->thread_content;
 // [JXD]: OLD:
 //						$ticket_body_content = str_replace("\n","\n> ",wordwrap($ticket_body_content,75));
 // [JXD]: NEW:
 						// e-mail quoting with wordwrap and quoting hierachy inheritance
 						//	(2003-09-08 J.E. Klasek klasek@zid.tuwien.ac.at)
 
 						// mark old line breaks and remove trailing whitespaces:
 						$ticket_body_content= preg_replace("/[ \t]*\n/", "\x01\n",$ticket_body_content);
 
 						// insert new quoting level
 						// e.g. ">>> LINE"  => "> >>> LINE"
 						$ticket_body_content = preg_replace("/(^|\n)/","\\1${quotechar} ", $ticket_body_content);
 
 						// compress old quoting hierarchy (remove blank in stacked quotes)
 						// e.g. "> > > LINE"  => ">>> LINE"
 						// or "> >> LINE"  => ">>> LINE"
 						$ticket_body_content = preg_replace("/(^|\n)(${quoteclass}( |${quoteclass})+${quoteclass}( ?))/e",
 										    "'\\1'.str_replace(' ','','\\2').'\\4'",
 										    $ticket_body_content);
 
 						// special treatment for end of text (remove quoting 
 						// in the additional last line)
 						$ticket_body_content = preg_replace("/\n${quotechar} $/", "\n", $ticket_body_content);
 
 						// [BGH]: 11/20/03
 						// [JAS]: 01/29/04 - Moved this block from above 'insert new quoting level' to end to prevent wrapped
 						//		words from being quoted on a lower level.
 						$ticket_body_content = preg_replace("/(^|\n)((${quoteclass}(( |${quoteclass})*${quoteclass})? ?)?)([^\x01]*)(\x01|$)/",
//									    "\\1\\2\\6", wordwrap($ticket_body_content,78,"\n"));
									    "\\1\\2\\6", $ticket_body_content);

						
						// [JAS]: Perform our own intelligent word wrap.  Accounts for quoting characters (e.g.: >>> More text)
						$body_array = explode("\n",$ticket_body_content);
						
						foreach($body_array as $idx => $line)
						{
							$pad = NULL; $x=0;
							while($line[$x] == ' ' || $line[$x] == '>')
								{ $pad .= $line[$x]; $x++; }
							
							$pad_len = strlen($pad) + 75;
							
							// [JAS]: Figure out if we're using foreign charsets using text/html codes.
							$translate_len = preg_replace("/&#[0-9]+;/i","_",$body_array[$idx]);
							$str_len = strlen($body_array[$idx]);
							
							if($str_len == $translate_len) {
								$body_array[$idx] = wordwrap($body_array[$idx],$pad_len,"\n".$pad);
							}
							
//							echo $body_array[$idx] . " (" . strlen($translate_len) . ") (" . strlen($body_array[$idx]) . ")<br>"; //	[JAS]: DEBUG
						}							
						
						$ticket_body_content = implode("\n",$body_array);
									    
						// [BGH]: 11/20/2003
 						$thread_content .= $ticket_body_content . "\n";
 					
 						if($sigpos==0 && $siginsert==1) $thread_content .= "\n" . $this->user_signature;
 					}
 					else
 					{
 						if($siginsert==1) $thread_content = $this->user_signature;
 					}			
					
					break;
				}
			}
		}
		else
		{
			if($siginsert==1) $thread_content = $this->user_signature;
		}
	
		$this->quote_thread->thread_content = $thread_content;
	}
	
	function _check_batch()
	{
		global $mode; // clean
		
		if(!empty($mode) && $mode == "batch")
			$this->is_batch = true;
	}
	
};

?>