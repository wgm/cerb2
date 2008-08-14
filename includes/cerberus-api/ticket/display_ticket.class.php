<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: display_ticket.class.php
|
| Purpose: Object to store all the data for a single ticket.  Used for
|	display purposes.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadTimeTracking.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_TimestampSelect.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicUser.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

class CER_TICKET_DISPLAY
{
	var $db = null;							// database reference pointer
	var $ticket_id = 0;
	var $ticket_mask = null;
	var $ticket_mask_id = null;
	var $ticket_subject = null;
	var $ticket_date = null;
	var $ticket_due = null;
	var $ticket_status = null;
	var $ticket_status_translated = null;	// cached translated status (passed through lang system)
	var $ticket_owner = 0;
	var $ticket_assigned_to_id = 0;
	var $ticket_priority = 0;
	var $ticket_queue_id = 0;
	var $ticket_queue_name = null;
	var $queue_addresses_id = 0;
	var $requestor_address = null;
	var $ticket_reopenings = 0;
	var $min_thread_id = 0;					// first ticket thread id
	var $max_thread_id = 0;					// last ticket thread id
	var $properties = null;					// Properties container (CER_TICKET_DISPLAY_PROPERTIES)
	
	var $threads = array();					// array of ticket's threads (objects)
	var $activity_threads = array();
	var $time_tracking_threads = array();
	var $thread_content_handler = null;		// thread content handler hook (cer_ThreadContentHandler)
	
	var $time_worked = 0;					// cumulative number of minutes worked on ticket
	var $time_created = null;				// formatted string for time created
	var $time_due = null;					// formatted string for time due
	
	var $num_requesters = 0;				// the number of requesters this ticket has
	var $requesters = null;					// requester object (CER_TICKET_DISPLAY_REQUESTER)
	var $ticket_users = array();			// array of users on this ticket and their actions (reply/comment/etc)
	
	var $r_field_handler = null;			// requester custom field handler (cer_CustomFieldGroupHandler)
	var $t_field_handler = null;			// ticket custom field handler (cer_CustomFieldGroupHandler)
	var $field_handler = null;				// custom field handler for group templates
	
	var $support_history = null;			// Customer/Company support history class
	var $batch = null;						// batch object (CER_TICKET_DISPLAY_BATCH)
	
	var $sla = null;						// Company and SLA information (CER_TICKET_DISPLAY_SLA)
	var $public_user_id = 0;
	
	var $log = null;						// Audit log Object (CER_TICKET_DISPLAY_AUDIT_LOG)
	var $thread_errors = null;				// Thread errors object	
	var $batch_id = false;					// ticket's order in batch, or false if not batched
	var $ptr_first_thread = null;			// pointer to the first thread chronologically
	var $ptr_last_thread = null;			// pointer to the last thread chronologically
	var $ticket_spam_trained = 0;			// ticket spam training bit (0=false 1=true)
	var $ticket_spam_rating = null;			// a dynamic ticket spam probability
	var $ticket_spam_words = array();		// array of words used in the spam analysis & decision
	var $writeable = false;					// if the ticket is writeable by the current user
	
	var $ticket_due_str = "00/00/00 0:00am";
	var $ticket_due_date = "00/00/00";
	var $ticket_due_h = "0";
	var $ticket_due_m = "00";
	var $ticket_due_ampm = "am";
	
	var $timestamp_select = null;
	
	var $queue_handler = null;
	
	function CER_TICKET_DISPLAY()
	{
		$this->db = cer_Database::getInstance();
		$this->timestamp_select = new cer_TimestampSelect();
	}
	
	function build_ticket($thread_order="")
	{
		global $session;
		global $cerberus_translate;
		global $cerberus_format;
		$cfg = CerConfiguration::getInstance();
		
		global $mode;
		
		$this->queue_handler = 	new cer_QueueHandler(array($this->ticket_queue_id));
		
		$this->thread_content_handler = new cer_ThreadContentHandler();
		$this->thread_content_handler->loadTicketContentDB($this->ticket_id);
		
		// [JAS]: Check if we have any thread errors that need to be displayed along with the ticket.
		$this->thread_errors = new CER_TICKET_THREAD_ERRORS();
		$this->thread_errors->load_errors_by_ticket($this->ticket_id);

		$this->_check_write_access();
		$this->_check_queue_address();
		$this->_mask_ticket_id();
		$this->_build_thread_list($thread_order);
		$this->_count_requesters();
		$this->_load_ticket_users();
		$this->_load_custom_fields();
		$this->_load_support_history();
		
		$this->sla = new CER_TICKET_DISPLAY_SLA($this);
		$this->log = new CER_TICKET_DISPLAY_AUDIT_LOG($this);
		
		$this->properties = new CER_TICKET_DISPLAY_PROPERTIES($this);
		$this->requesters = new CER_TICKET_DISPLAY_REQUESTER($this);
		$this->batch = new CER_TICKET_DISPLAY_BATCH();
		
		// [JAS]: Cache our batch id + translated status
		$this->batch_id = $session->vars["login_handler"]->batch->in_batch($this->ticket_id);
		$this->ticket_status_translated  = $cerberus_translate->translate_status($this->ticket_status);
		
		// [JAS]: Calculate time string caches
		$date = new cer_DateTime($this->ticket_date);
		$this->time_created = $date->getUserDate();
		
		if($this->ticket_due != "0000-00-00 00:00:00") {
			$date = new cer_DateTime($this->ticket_due);
			$this->time_due = $date->getUserDate();
		}
		
		switch($mode)
		{
			case "anti_spam":
			{
				$bayes = new cer_BayesianAntiSpam();
				$text = $this->ticket_subject . "\r\n" . $this->ptr_first_thread->thread_content;
				$this->ticket_spam_words = $bayes->_analyze_raw_email($this->ticket_id,1);
				break;
			}
		}
		
	}
	
	function _mask_ticket_id()
	{
		$cfg = CerConfiguration::getInstance();
		
		if($cfg->settings["enable_id_masking"] && !empty($this->ticket_mask)) {
			$this->ticket_mask_id = $this->ticket_mask;
		}
		else {
			$this->ticket_mask_id = $this->ticket_id;
		}
		
		return;
	}
	
	function _load_support_history()
	{
		$this->support_history = new CER_TICKET_DISPLAY_HISTORY($this);
	}
	
	function _count_requesters()
	{
      	$sql = "SELECT address_id FROM requestor WHERE ticket_id = " . $this->ticket_id;
      	$req_res = $this->db->query($sql);
      	$this->num_requesters = $this->db->num_rows($req_res);
      	unset($req_res);
	}
	
	function _check_write_access()
	{
		global $queue_access; // clean up
		$this->writeable = $queue_access->has_write_access($this->ticket_queue_id);
	}
	
	function _check_queue_address()
	{
		$queue_address_ids = array();
		$qa_id = 0;
		
		if(!empty($this->queue_handler->queues[$this->ticket_queue_id]->queue_addresses)) {
			foreach($this->queue_handler->queues[$this->ticket_queue_id]->queue_addresses as $qa_id => $qa_ad) {
				$queue_address_ids[$qa_id] = $qa_id;
			}
		}

		if(!isset($queue_address_ids[$this->queue_addresses_id]) && $qa_id) {
			$this->queue_addresses_id = $qa_id; // assign it the last ID
			
			$sql = sprintf("UPDATE ticket SET queue_addresses_id = %d WHERE ticket_id = %d",
					$this->queue_addresses_id,
					$this->ticket_id
				);
			$this->db->query($sql);
		}
	}
	
	function _load_custom_fields()
	{
		$this->field_handler = new cer_CustomFieldGroupHandler();
		$this->field_handler->loadGroupTemplates();
		
		$this->r_field_handler = new cer_CustomFieldGroupHandler();
		$this->r_field_handler->load_entity_groups(ENTITY_REQUESTER,$this->requestor_address->address_id);
		
		$this->t_field_handler = new cer_CustomFieldGroupHandler();
		$this->t_field_handler->load_entity_groups(ENTITY_TICKET,$this->ticket_id);
	}
	
	function _load_ticket_users()
	{
		$cfg = CerConfiguration::getInstance();
		
		$sql = sprintf("SELECT u.user_login, w.user_what_action FROM whos_online w, user u ".
			"WHERE w.user_id = u.user_id AND u.user_login != '' AND w.user_what_arg1 = %d AND w.user_what_action IN (%d,%d,%d) AND ".
			"w.user_timestamp BETWEEN DATE_SUB(NOW(),INTERVAL \"%d\" MINUTE) AND DATE_ADD(NOW(),INTERVAL \"1\" MINUTE)",
					$this->ticket_id,
					WHO_DISPLAY_TICKET,
					WHO_REPLY_TICKET,
					WHO_COMMENT_TICKET,
					$cfg->settings["who_max_idle_mins"]
				);
		$results = $this->db->query($sql);

		$user_count = $this->db->num_rows($results);
        if($user_count)
        {
        	while($wu = $this->db->fetch_row($results))
        	{
        		$user_instance = new CER_TICKET_DISPLAY_USER();
        		$user_instance->user_login = $wu["user_login"];
        		
        		switch($wu["user_what_action"])
        		{
        			default:
        			case WHO_DISPLAY_TICKET:
        				$user_instance->user_what = "(" . LANG_ACTION_USER_BROWSING . ")";
        			break;
        			case WHO_REPLY_TICKET:
        				$user_instance->user_what = "(<span class=\"cer_display_user_red\"><b>" . LANG_ACTION_USER_REPLYING . "</b></span>)";
        			break;
        			case WHO_COMMENT_TICKET:
        				$user_instance->user_what = "(" . LANG_ACTION_USER_COMMENTING . ")";
        			break;
        		}
        	
        	array_push($this->ticket_users,$user_instance);
        	}
        }

        unset($results);
	}
	
	function set_ticket_id($t_id)
	{ $this->ticket_id = $t_id; }
	
	function set_ticket_time_worked($time)
	{ $this->time_worked = cer_DateTimeFormat::secsAsEnglishString($time*60,true,2); }
	
	function set_ticket_mask($t_mask)
	{ $this->ticket_mask = $t_mask; }
	
	function set_ticket_subject($t_subject)
	{ $this->ticket_subject = stripslashes($t_subject); }
	
	function set_ticket_date($t_date)
	{ $this->ticket_date = $t_date; }
	
	function set_ticket_due($t_date)
	{ 
		$this->ticket_due = $t_date;
		
		if($t_date != "0000-00-00 00:00:00") {
			
			$due_date = new cer_DateTime($t_date);
			
			$this->ticket_due_date = $due_date->getUserDate("%m/%d/%y");
			$this->ticket_due_time_h = $due_date->getUserDate("%I");
			$mins = $due_date->getUserDate("%M");
			$this->ticket_due_time_ampm = strtolower($due_date->getUserDate("%p"));
			
			// 5 min blocks
			if($mins) {
				$chunk = ceil($mins/5);
				$mins = sprintf("%02d", 5 * $chunk);
			}
			
			$this->ticket_due_time_m = $mins;
			
			$this->ticket_due_str = sprintf("%s %d:%02d %s",
					$this->ticket_due_date,
					$this->ticket_due_time_h,
					$this->ticket_due_time_m,
					$this->ticket_due_time_ampm
				);
		}
	}
	
	function set_public_gui_user_id($uid=0) {
		$this->public_user_id = $uid;
	}
	
	function set_ticket_status($t_status)
	{ $this->ticket_status = stripslashes($t_status); }
	
	function set_spam_trained($t=0)
	{
		$this->ticket_spam_trained = $t;
	}
	
	function set_ticket_owner($t_owner_id)
	{
		global $cer_hash; // clean
		// rewrite using hash object
		$this->ticket_assigned_to_id = $t_owner_id;
		
		$sql = "SELECT u.user_login FROM user u where user_id = " . $t_owner_id;
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result) == 0)
		{ $this->ticket_owner = "Nobody"; }
		else {
			$row = $this->db->fetch_row($result);
			$this->ticket_owner = $row["user_login"];
		}
	}
	
	function set_ticket_priority($t_priority)
	{	$this->ticket_priority = $t_priority; }
	
	function set_ticket_queue($t_queue_id)
	{ $this->ticket_queue_id = $t_queue_id; }
	
	function set_ticket_queue_name($t_queue_name)
	{ $this->ticket_queue_name = $t_queue_name; }
	
	function set_ticket_queue_address_id($q_address_id)
	{	$this->queue_addresses_id = $q_address_id; }
	
	function set_ticket_reopenings($t_reopen)
	{ $this->ticket_reopenings = $t_reopen; }
	
	function set_ticket_max_thread($th_id)
	{ $this->max_thread_id = $th_id; }
	
	function set_ticket_min_thread($th_id)
	{ $this->min_thread_id = $th_id; }
	
	function set_requestor_address($a_id,$a_address)
	{
		$this->requestor_address = new cer_email_address_struct($a_id,$a_address);
	}
	
	function _build_thread_list($ticket_order="")
	{
		global $session;
		
		// [JAS]: View thread in which order
		if(empty($ticket_order))
		{
			$ticket_order = "ASC";
			if ($session->vars["login_handler"]->user_prefs->user_ticket_order == "1") {
				$ticket_order = "DESC";	}
		}
		
		$sql = "SELECT th.thread_id, th.thread_type, th.thread_date, th.thread_time_worked, ad.address_banned,ad.address_id, ad.address_address " .
		", th.thread_subject, th.thread_to, th.thread_cc, th.thread_replyto, th.is_agent_message " . // [JAS]: jxdemel's Thread Reply/CC/Subject Patch
		"FROM thread th, ticket tk, address ad " .
		"WHERE th.ticket_id = tk.ticket_id AND th.thread_address_id = ad.address_id AND tk.ticket_id = " . $this->ticket_id . " ORDER BY th.thread_id ". $ticket_order;
		$result = $this->db->query($sql);
		
		// [JAS]: We have threads returned from our query
		if($this->db->num_rows($result) > 0)
		{
			while($o_th = $this->db->fetch_row($result))
			{
				$this->_add_thread($o_th["thread_id"],
								  $o_th["address_id"],
								  stripslashes($o_th["address_address"]),
								  $o_th["address_banned"],
								  $o_th["thread_type"],
								  $o_th["thread_date"],
								  $o_th["thread_time_worked"],
 								  stripslashes($o_th["thread_subject"]),
 								  stripslashes($o_th["thread_cc"]),
 								  stripslashes($o_th["thread_replyto"]),
 								  $o_th["is_agent_message"],
 								  stripslashes($o_th["thread_to"])
								  );
			}
		}
		
		// [JAS]: Load time tracking threads for this ticket
		$time_entry_handler = new cer_ThreadTimeTrackingHandler();
		$time_entry_handler->loadThreadsByTicketId($this->ticket_id);
		
		// [JAS]: Index time tracking threads chronologically
		foreach($time_entry_handler->time_threads as $th) {
			$date = new cer_DateTime($th->date);
			$this->time_tracking_threads[] = $th;
			$idx = $this->_makeUniqueThreadPtrIndex($date->mktime_datetime);
			$this->threads[$idx] = 
				new CER_THREAD_POINTER("time",$this->time_tracking_threads[count($this->time_tracking_threads)-1]);
		}

		unset($time_entry_handler);

		// [JAS]: Sort the threads according to user preferences
		$order = $session->vars["login_handler"]->user_prefs->user_ticket_order;
		
		if(!$order)
			ksort($this->threads);
		else
			krsort($this->threads);

	}
	
	// [JAS]: Check a passed pointer thread index and make sure it's unique, if not then append alphas (a)
	//	to the index until it's unique enough to sort on index/key.  Preserves chronological timeline.
	function _makeUniqueThreadPtrIndex($idx) {
		// [JAS]: Check if it's already unique
		if(!isset($this->threads[$idx])) {
			return $idx;
		}

		$token_idx = 0;
		
		// [JAS]: Unique (and sortable) array keys even for the same epoch, e.g.:
		//	 1029394785
		//	 1029394785a
		//	 1029394785aa
		//	 1029394785aaa
		while(isset($this->threads[$idx . str_repeat('a',$token_idx)])) {
			$token_idx++;
		}
		
		return $idx . str_repeat('a',$token_idx);
	}
	
	function _add_thread($t_id=0,$t_author_id=0,$t_author="",$t_author_banned="",$t_type="comment",$t_date="",$t_time=0,$t_source="",$t_subject="",$t_cc="",$t_replyto="",$t_to="")
	{
		global $session; // [JAS]: Clean
		
		$date = new cer_DateTime($t_date);
		
		$this->activity_threads[] = new CER_THREAD($this,$t_id,$t_author_id,$t_author,$t_author_banned,$t_type,$t_date,$t_time,$t_source,$t_subject,$t_cc,$t_replyto,$t_to);
		
		// [JAS]: Index threads chronologically w/ a pointer to an activity (email/comment) or timetracking thread.
		$idx = $this->_makeUniqueThreadPtrIndex($date->mktime_datetime);
		
		$this->threads[$idx] = 
			new CER_THREAD_POINTER($t_type,$this->activity_threads[count($this->activity_threads)-1]);
			
		// [JAS]: Set the thread pointers to only activity threads
		$order = $session->vars["login_handler"]->user_prefs->user_ticket_order;
		$pos = count($this->activity_threads);
		
		if(!$order) {
			$this->ptr_first_thread = &$this->activity_threads[0];
			$this->ptr_last_thread = &$this->activity_threads[$pos];
		}
		else {
			$this->ptr_last_thread = &$this->activity_threads[0];
			$this->ptr_first_thread = &$this->activity_threads[$pos];
		}
		
	}
	
	// end CER_TICKET_DISPLAY
};

class CER_THREAD_POINTER
{
	var $type = null;
	var $ptr = null;
	
	function CER_THREAD_POINTER($type,&$ptr) {
		$this->type = $type;
		$this->ptr = &$ptr;
	}
};

class CER_THREAD
{
	var $db = null;
	var $ticket_ptr = null;
	var $thread_id = null;
	var $thread_author = null;
	var $thread_display_author = null;
	var $thread_type = null;
	var $thread_date = null;
	var $thread_date_rfc = null;
	var $thread_display_date = null;
	var $thread_time_worked = null;
	
	var $thread_content = null;
	
	var $thread_style = null;
 	var $thread_subject = null;	// [JXD]: (jxdemel)
 	var $thread_to = null;
 	var $thread_cc = null;		// [JXD]: (jxdemel)
 	var $thread_replyto = null;	// [JXD]: (jxdemel)
 	var $is_agent_message = 0;
 	
	var $url_reply = null;
	var $url_quote_reply = null;
 	var $url_quote_forward = null;
 	var $url_bounce = null;         // [JXD]: 20030812 Feature Bounce
 	var $url_bounce_submit = null;  // [JXD]: 20030812
	var $url_comment = null;
	var $url_forward = null;
	var $url_forward_submit = null;
	var $url_add_req = null;
	var $url_block_sender = null;
	var $url_unblock_sender = null;
	var $url_strip_html = null;
	var $url_strip_html_submit = null;
	var $url_clear_errors = null;
	var $url_track_time_entry = null;

	var $file_attachments = array();
	var $thread_errors = array();
	
	function CER_THREAD(&$ticket_obj,$t_id=0,$t_author_id=0,$t_author="",$t_author_banned="",$t_type="comment",$t_date="",$t_time=0,
 		$t_subject="",$t_cc="",$t_replyto="", $t_isagent=0, $t_to=null)
	{
		global $cerberus_format; // fix
		
		$this->db = cer_Database::getInstance();
		$this->ticket_ptr = &$ticket_obj;
		
		$this->thread_id = $t_id;
		$this->thread_author = new cer_email_address_struct($t_author_id,$t_author,$t_author_banned);
		$this->thread_type = $t_type;
		$this->thread_date = $t_date;
		$this->thread_date_rfc = $cerberus_format->format_db_date_rfc($this->thread_date, "r");
		$this->thread_time_worked = $t_time;
		
		$this->thread_content = &$this->ticket_ptr->thread_content_handler->threads[$this->thread_id]->content;
 		
		$this->thread_subject = $t_subject;
 		$this->thread_to = $t_to;
 		$this->thread_cc = $t_cc;
 		$this->thread_replyto = $t_replyto;
		
		$this->thread_display_author = display_email($this->thread_author->address);
		
		$date = new cer_DateTime($this->thread_date);
		$this->thread_display_date = $date->getUserDate();
		
		$this->is_agent_message = $t_isagent;
		
		$this->_apply_thread_styles();
		$this->_generate_thread_urls();
		$this->_check_thread_errors();
		$this->_run_thread_actions();
		$this->_read_attachments();
	}
	
	function _check_thread_errors()
	{
		if($this->ticket_ptr->thread_errors->thread_has_errors($this->thread_id))
		{
			$this->thread_errors = $this->ticket_ptr->thread_errors->threads[$this->thread_id];
		}
	}
	
	function _run_thread_actions()
	{
		global $thread_action; // fix
		global $thread; // fix
		
		// [JAS]: If no HTML is present, do nothing -- otherwise we'll mess up plaintext formatting
//		if( preg_match("/\<*.?\>*.?\<\/*.?\>/",$this->thread_content) == 0) return true;
		
	    if(isset($thread_action) && $thread_action=="strip_html" && $this->thread_id == $thread)
	    {
			require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
			$strip = new cer_StripHTML();
			$this->thread_content = $strip->strip_html($this->thread_content);
	    }
	}
	
	function _generate_thread_urls()
	{
		global $cer_ticket; // fix
		global $priv; // fix
		
		$this->url_reply = cer_href(
							sprintf("update.php?qid=%d&ticket=%d&type=reply&thread=%d&quote=0",
								$this->ticket_ptr->ticket_queue_id,
								$this->ticket_ptr->ticket_id,
								$this->thread_id
							),
							"update");
								
		$this->url_quote_reply = cer_href(
							sprintf("update.php?qid=%d&ticket=%d&type=reply&thread=%d&quote=1",
								$this->ticket_ptr->ticket_queue_id,
								$this->ticket_ptr->ticket_id,
 								$this->thread_id
 							),
 							"update");
 
 		$this->url_quote_forward = cer_href(
 							sprintf("update.php?qid=%d&ticket=%d&type=forward&thread=%d&quote=1",
 								$this->ticket_ptr->ticket_queue_id,
 								$this->ticket_ptr->ticket_id,
								$this->thread_id
							),
							"update");
							
		$this->url_comment = cer_href(
							sprintf("update.php?qid=%d&ticket=%d&type=comment&thread=%d",
								$this->ticket_ptr->ticket_queue_id,
								$this->ticket_ptr->ticket_id,
								$this->thread_id
							),
							"update");
							
		$this->url_forward = cer_href(
							sprintf("display.php?ticket=%d&thread=%d&thread_action=forward",
								$this->ticket_ptr->ticket_id,
								$this->thread_id
								),
							"thread_" . $this->thread_id
							);
							
 		$this->url_bounce = cer_href(		// [JXD]: Feature bounce
 							sprintf("display.php?ticket=%d&thread=%d&thread_action=bounce",
 								$this->ticket_ptr->ticket_id,
 								$this->thread_id
 								),
 							"thread_" . $this->thread_id
 							);
 
		$this->url_track_time_entry = cer_href(
							sprintf("display.php?ticket=%d&thread=%d&form_submit=thread_create_time_entry",
								$this->ticket_ptr->ticket_id,
								$this->thread_id
								),
							"thread_track_time"
							);

		if(isset($cer_ticket)) {
			if(!$cer_ticket->is_ticket_requester_id($this->thread_author->address_id) 
				&& !$this->is_agent_message 
				&& $priv->has_priv(ACL_ADD_REQUESTER,BITGROUP_2))
			{
				$this->url_add_req =cer_href(
							sprintf("display.php?ticket=%d&thread=%d&form_submit=add_req",
									$this->ticket_ptr->ticket_id,
									$this->thread_id
									),
								"thread_" . $this->thread_id
								);
			}
		}

		if(!$this->thread_author->address_banned 
			&& !$this->is_agent_message
			&& $priv->has_priv(ACL_EMAIL_BLOCK_SENDERS))
		{
			$this->url_block_sender = cer_href(
						sprintf("display.php?ticket=%d&thread=%d&form_submit=block_req",
								$this->ticket_ptr->ticket_id,
								$this->thread_id
								),
							"thread_" . $this->thread_id
							);			
		}
		else if($this->thread_author->address_banned
			&& $priv->has_priv(ACL_EMAIL_BLOCK_SENDERS))
		{
			$this->url_unblock_sender = cer_href(
						sprintf("display.php?ticket=%d&thread=%d&form_submit=unblock_req",
								$this->ticket_ptr->ticket_id,
								$this->thread_id
								),
							"thread_" . $this->thread_id
							);
		}

		$this->url_strip_html = cer_href(
						sprintf("display.php?ticket=%d&thread=%d&thread_action=strip_html",
								$this->ticket_ptr->ticket_id,
								$this->thread_id
								),
							"thread_" . $this->thread_id
							);
							
		$this->url_clear_errors = cer_href(
						sprintf("display.php?ticket=%d&te_clear=%d",
								$this->ticket_ptr->ticket_id,
								$this->thread_id
								),
							"thread_" . $this->thread_id
							);
        $this->print_thread = cer_href(
						sprintf("printdisplay.php?level=thread&ticket=%d&thread=%d",
	                        $this->ticket_ptr->ticket_id,
							$this->thread_id
                            )
                        );
	}
	
	function _apply_thread_styles()
	{
		if($this->thread_type != "comment")
		{
			if($this->is_agent_message)
			{ $this->thread_style = "boxtitle_gray_dk"; }
			else 
			{ $this->thread_style = "boxtitle_red_glass"; }
		}
		else
		{ $this->thread_style = "boxtitle_blue_glass"; }
	}
	
	function _read_attachments()
	{
		$sql = "SELECT file_id, file_name, file_size FROM thread_attachments WHERE thread_id = " . $this->thread_id;
		$file_res = $this->db->query($sql);
		
		if($this->db->num_rows($file_res) > 0)
		{
			while($file_row = $this->db->fetch_row($file_res))
			{ $this->_add_attachment($file_row["file_id"],$file_row["file_name"],$file_row["file_size"]); }
		}
	}
	
	function _add_attachment($f_id,$f_name,$f_size)
	{
		$pos = count($this->file_attachments);
		$this->file_attachments[$pos] = new CER_FILE_ATTACHMENT($f_id,$f_name,$f_size,$this->thread_id);
	}
	
};

class CER_FILE_ATTACHMENT
{
	var $file_id = 0;
	var $file_name = null;
	var $file_size = 0;
	var $thread_id = 0;
	var $display_size = 0;
	var $file_url = null;
	
	function CER_FILE_ATTACHMENT($f_id,$f_name,$f_size,$thread_id)
	{
		$this->file_id = $f_id;
		$this->file_name = $f_name;
		$this->file_size = $f_size;
		$this->thread_id = $thread_id;

    	$in_MB = false;
    	$file_size = sprintf("%d",$this->file_size/1000);
    	if($file_size == 0) $file_size = "&lt;1";
    	if($file_size >= 1000) { $file_size = sprintf("%0.2f",$file_size/1048); $in_MB = true; } // turn to MB
    	
		$this->display_size = sprintf("%s%s",(is_numeric($file_size)?sprintf("%0.1f",$file_size):$file_size), (($in_MB)?"MB":"KB"));
		
		$this->file_url = cer_href(sprintf("attachment_send.php?file_id=%d&thread_id=%d",$this->file_id,$this->thread_id));
	}
};

class CER_TICKET_DISPLAY_PROPERTIES
{
	var $ticket_ptr = null;					// Reference pointer to ticket object
	var $show_chsubject = false;			// Can we edit subject?
	var $show_chowner = false;
	var $show_chstatus = false;
	var $show_chqueue = false;
	var $show_chpriority = false;
	var $show_add_requester = false;
	var $show_forward_thread = false;

	function CER_TICKET_DISPLAY_PROPERTIES(&$ticket_ptr)
	{
		global $priv;
		
		$this->ticket_ptr = &$ticket_ptr;
		
		if($this->ticket_ptr->writeable && $priv->has_priv(ACL_TICKET_CHSUBJECT,BITGROUP_1))
			$this->show_chsubject = true;
			
		if($this->ticket_ptr->writeable && $priv->has_priv(ACL_TICKET_CHSTATUS,BITGROUP_1))
    		$this->show_chstatus = true;
           	
    	if($this->ticket_ptr->writeable && $priv->has_priv(ACL_TICKET_CHOWNER,BITGROUP_1))
			$this->show_chowner = true;

		if($this->ticket_ptr->writeable && $priv->has_priv(ACL_TICKET_CHQUEUE,BITGROUP_1))
			$this->show_chqueue = true;
			
		if($this->ticket_ptr->writeable && $priv->has_priv(ACL_TICKET_CHPRIORITY,BITGROUP_1))		
			$this->show_chpriority = true;
			
		// [JAS]: \todo Right here we should also be checking that the current thread author isn't
		//	already a requester.  Cuts down on the amount of useless options.
		if($this->ticket_ptr->writeable && $priv->has_priv(ACL_ADD_REQUESTER,BITGROUP_2))
			$this->show_add_requester = true;

		if($priv->has_priv(ACL_FORWARD_THREAD,BITGROUP_2))
			$this->show_forward_thread = true;
	}
};


class CER_TICKET_DISPLAY_REQUESTER
{
	var $db = null; 
	var $ticket_ptr = null;
	var $addresses = array();
	var $has_requesters = false;
	
	function CER_TICKET_DISPLAY_REQUESTER(&$ticket_obj)
	{
		$this->db = cer_Database::getInstance();
		$this->ticket_ptr = &$ticket_obj;
		
		$sql = "SELECT a.address_id,a.address_address,r.suppress FROM address a, requestor r ".
			"WHERE r.address_id = a.address_id AND r.ticket_id = " . $this->ticket_ptr->ticket_id . " " .
			"ORDER BY a.address_address ASC";
		$req_res = $this->db->query($sql);
		
		if($this->db->num_rows($req_res))
		{
			while($req_row = $this->db->fetch_row($req_res))
			{
				$req_instance = new CER_TICKET_DISPLAY_REQUESTER_ITEM();
				$req_instance->address_id = $req_row["address_id"];
				$req_instance->address_address = $req_row["address_address"];
				$req_instance->suppress = $req_row["suppress"];
				array_push($this->addresses,$req_instance);
			}
			
			$this->has_requesters = true;
		}
	}
};


class CER_TICKET_DISPLAY_REQUESTER_ITEM
{
	var $address_id = 0;
	var $address_address = null;
	var $suppress = 0;
};


class CER_TICKET_DISPLAY_BATCH
{
	var $batch_ids = array();									// Array of batch IDs
	var $batch_id_string = null;								// List of batch IDs (csv)
	
	function CER_TICKET_DISPLAY_BATCH()
	{
		global $session; // fix
		
		if(count($session->vars["login_handler"]->batch->tickets))
		{
			$this->batch_ids = $session->vars["login_handler"]->batch->tickets;
			$this->batch_id_string = implode(",",$this->batch_ids);
		}
	}

};


class CER_TICKET_DISPLAY_AUDIT_LOG
{
	var $db = null;												// database object reference pointer
	var $ticket_ptr = null;										// ticket object reference pointer
	var $entries = array();										// [JAS]: log entries (up to 5, FIFO stack)
	
	function CER_TICKET_DISPLAY_AUDIT_LOG(&$ticket_obj)
	{
		$cfg = CerConfiguration::getInstance();
		global $priv; // fix
		global $audit_log; // fix
		global $mode; // fix
		global $cer_hash; // [JSJ]: nasty hack but works!
		
		$this->db = cer_Database::getInstance();
		$this->ticket_ptr = &$ticket_obj;
		$priority_hash = @$cer_hash->get_priority_hash(); // [JSJ]: get priority string
		
		if(!isset($audit_log)) return false;
		if(!isset($priv)) return false;
		if(!isset($cfg)) return false;
		
		$max = 5;
		if(!empty($mode) && $mode == "log") $max = 0; // clean up reliance on global
		
		if($cfg->settings["enable_audit_log"] && $priv->has_priv(ACL_AUDIT_LOG,BITGROUP_2))
		{
			$log_result = $audit_log->get_log($this->ticket_ptr->ticket_id,$max);
			
			if($this->db->num_rows($log_result))
			{
				while($log_row = $this->db->fetch_row($log_result))
				{ 
					$log_item = new CER_TICKET_DISPLAY_AUDIT_LOG_ITEM();
					$log_item->log_timestamp = $audit_log->show_timestamp($log_row["timestamp"]);
					
					$log_action = @htmlspecialchars($log_row["action_value"], ENT_QUOTES, LANG_CHARSET_CODE);
					
					// [JAS]: If this is a priority number, translate it into a string.
					if($log_row["action"] == AUDIT_ACTION_CHANGED_PRIORITY
						|| $log_row["action"] == AUDIT_ACTION_RULE_CHPRIORITY
						) 
						$log_action = $priority_hash[$log_action];

					$log_item->log_text = @$audit_log->print_action($log_row["action"],$log_action,$log_row["user"],$log_row["timestamp"]);
					
					array_push($this->entries,$log_item);
				}
			}
		}
	}
};


class CER_TICKET_DISPLAY_AUDIT_LOG_ITEM
{
	var $log_timestamp;
	var $log_text;
};


class CER_TICKET_DISPLAY_SLA
{
	var $db = null;
	var $ticket_ptr = null;
	var $sla_ptr = null;
//	var $company_ptr = null;
	var $sla_queue_ptr = null;	

	var $sla_plan = null;
	var $pub_user_handler = null;
	var $pub_user = null;
	
//	var $company_id = 0;
//	var $company_name = null;

//	var $sla_id = 0;
//	var $sla_name = null;
//	var $total_addresses = 0;
//	var $total_tickets = 0;
//	var $select_company_options = null;						// dropdown of all companies, if needed
//	var $url_unset_company = null;
	
	function CER_TICKET_DISPLAY_SLA(&$ticket)
	{
		$this->db = cer_Database::getInstance();
		$this->ticket_ptr = &$ticket;
		$this->sla_ptr = new cer_SLA();
			
		$this->_load_sla_data();
	}
	
	function _load_sla_data()
	{
		if($this->ticket_ptr->public_user_id) {
		
			$this->pub_user_handler = new cer_PublicUserHandler();
			$this->pub_user_handler->loadUsersByIds(array($this->ticket_ptr->public_user_id));
			$this->pub_user = &$this->pub_user_handler->users[$this->ticket_ptr->public_user_id];
			
//			if(empty($this->pub_user->company_id)) {
//				$this->company_ptr = &$this->pub_user_handler->company_handler->companies[$this->pub_user->company_id];
//			}
			
			$req_id = $this->ticket_ptr->requestor_address->address_id;
		
			if($sla_id = $this->sla_ptr->getSlaIdForRequesterId($req_id)) {
				$this->sla_plan = &$this->sla_ptr->plans[$sla_id];
				
				if(isset($this->sla_plan->queues[$this->ticket_ptr->ticket_queue_id])) {
					$this->sla_queue_ptr = &$this->sla_plan->queues[$this->ticket_ptr->ticket_queue_id];
				}
			}
		}
	}
		
};

class CER_TICKET_DISPLAY_HISTORY
{
	var $ticket_ptr = null;							// Pointer to ticket object
	var $db = null;									// Pointer to database
	var $history_title = null; 						// Customer or Company Support History
	var $history = array();							// Array of CER_TICKET_DISPLAY_HISTORY_ITEM
	var $perpage = 0;								// How many items to show per page
	var $page = 0;									// What page we're on
	var $history_pp = 0;							// Previous page
	var $history_np = 0;							// Next page
	var $history_from = 0;							// What result we're displaying from ([x] to 2 of 2)
	var $history_to = 0;							// What result we're displaying to (1 to [x] of 2)
	var $history_total = 0;							// Total number of results (1 to 2 of [x])
	var $url_customer_history = null;				// Cached URL for customer history mode
	var $url_company_history = null;				// Cached URL for company history mode
	var $url_prev = null;							// Cached URL to the previous page
	var $url_next = null;							// Cached URL to the next page
	
	function CER_TICKET_DISPLAY_HISTORY(&$ticket_obj)
	{
		$cfg = CerConfiguration::getInstance();
		global $cfg; // fix
		
		$this->db = cer_Database::getInstance();
		$this->ticket_ptr = &$ticket_obj;
		$this->perpage = $cfg->settings["customer_ticket_history_max"];
		$this->page = $hp;
		if(empty($this->page)) $this->page = 1; // [JAS]: if history page isn't set, set to page 1
		
		$this->url_customer_history = cer_href("display.php?ticket=".$this->ticket_ptr->ticket_id."&c_history=customer");
		$this->url_company_history = cer_href("display.php?ticket=".$this->ticket_ptr->ticket_id."&c_history=company");
	
		if($cfg->settings["enable_customer_history"])
			$this->_load_history();
	}
	
	function _load_history()
	{
		global $session; // fix
		global $cerberus_format; // fix
		global $cerberus_translate; // fix
		global $queue_access;
		
		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();
		
		$results_from = (($this->page-1)* $this->perpage);
		
		$this->history_pp = $this->page - 1; // previous page
		$this->history_np = $this->page + 1; // next page
		
		if($this->ticket_ptr->company_id == 0) $session->vars["c_history"] = "customer";
		
		$u_qids = $queue_access->get_readable_qid_list();
		
		$sql = "SELECT t.ticket_id, t.ticket_subject, t.ticket_status, t.ticket_date, th.thread_address_id, ".
			"t.ticket_queue_id, q.queue_name, t.ticket_mask ".
			"FROM ticket t, thread th, queue q ".
//			(($session->vars["c_history"]=="company")?", address ad, company c ":" ") .
			"WHERE t.min_thread_id = th.thread_id AND t.ticket_queue_id = q.queue_id ".
//			(($session->vars["c_history"]=="company")?" AND ad.address_id = th.thread_address_id AND c.id = ad.company_id ":" ") .
			" AND q.queue_id IN ($u_qids) ".
//			(($session->vars["c_history"] == "company") ? ("AND c.id = " . $this->ticket_ptr->company_id . " ") : "").
			(($session->vars["c_history"] == "customer") ? ("AND th.thread_address_id = " . $this->ticket_ptr->requestor_address->address_id . " ") : "");
		$results_count = $this->db->query($sql);

		$sql .=  "ORDER BY t.ticket_date DESC,t.ticket_id DESC LIMIT " . $results_from . "," . $this->perpage;
		$result = $this->db->query($sql);

		$this->history_total = $this->db->num_rows($results_count);
		$this->history_from = $results_from + 1;
		$this->history_to = ($this->history_from-1) + $this->db->num_rows($result);
		
		if($this->page > 1)
			$this->url_prev = cer_href($_SERVER["PHP_SELF"] . "?ticket=" . $this->ticket_ptr->ticket_id . "&hp=" . $this->history_pp);
		
		if($this->history_total > $this->history_to)
			$this->url_next = cer_href($_SERVER["PHP_SELF"] . "?ticket=" . $this->ticket_ptr->ticket_id . "&hp=" . $this->history_np);
		
		if($this->db->num_rows($result) > 0) 
		{
			while($prev_support_row = $this->db->fetch_row($result))
			{
				$history_instance = new CER_TICKET_DISPLAY_HISTORY_ITEM();
				$history_instance->ticket_id = $prev_support_row["ticket_id"];
				$history_instance->ticket_mask = ((!empty($prev_support_row["ticket_mask"])) ? $prev_support_row["ticket_mask"] : sprintf("%06d",$prev_support_row["ticket_id"]));
				$history_instance->ticket_subject = stripslashes($prev_support_row["ticket_subject"]); // [JAS]: removed htmentities
				$history_instance->ticket_status = $cerberus_translate->translate_status($prev_support_row["ticket_status"]);
				$history_instance->ticket_queue = $prev_support_row["queue_name"];
				$date = new cer_DateTime($prev_support_row["ticket_date"]);
				$history_instance->ticket_date = $date->getUserDate();
				$history_instance->ticket_url = cer_href("display.php?ticket=".$prev_support_row["ticket_id"]."&hp=".$this->page);
				
				array_push($this->history,$history_instance);
			}
		}
		
	}
	
};

class CER_TICKET_DISPLAY_HISTORY_ITEM
{
	var $ticket_id = 0;
	var $ticket_mask = null;
	var $ticket_subject = null;
	var $ticket_status = null;
	var $ticket_queue = null;
	var $ticket_date = null;
	var $ticket_url = null;
};

class CER_TICKET_DISPLAY_USER
{
	var $user_login = null;
	var $user_what = null;
};

class CER_TICKET_DISPLAY_FIELDS
{
	var $field_id = 0;
	var $field_name = null;
	var $field_type = null;
	var $field_value = null;
	var $field_options = array();
	
	function set_options($list="")
	{
		if(empty($list)) return true;
		
		$list = stripslashes($list);
		
		if(substr($list,0,1) == '"') $list = substr($list,1);
		if(substr($list,-1,1) == '"') $list = substr($list,0,strlen($list)-1);
		
       	$field_options_array = explode('","',$list);
       	
        foreach($field_options_array as $fldoption) {
        	$fldoption = cer_dbc($fldoption);
			$this->field_options[$fldoption] = $fldoption;
        }
		
	}
	
};

class CER_TICKET_DISPLAY_TABS
{
	var $tab_ticket_fields_bg_css = "boxtitle_gray_glass";
	var $tab_ticket_fields_css = "cer_navbar_heading";
	var $tab_props_bg_css = "boxtitle_gray_glass";
	var $tab_props_css = "cer_navbar_heading";
	var $tab_thread_bg_css = "boxtitle_gray_glass";
	var $tab_thread_css = "cer_navbar_heading";
	var $tab_antispam_bg_css = "boxtitle_gray_glass";
	var $tab_antispam_css = "cer_navbar_heading";
	var $tab_batch_bg_css = "boxtitle_gray_glass";
	var $tab_batch_css = "cer_navbar_heading";
	var $tab_log_bg_css = "boxtitle_gray_glass";
	var $tab_log_css = "cer_navbar_heading";

	function CER_TICKET_DISPLAY_TABS($mode="")
	{
		$this->set_tab_mode($mode);
	}
	
	function set_tab_mode($mode="")
	{
		switch($mode)
		{
			case "requesters":
			case "edit_fields":
			case "tkt_fields":
			case "properties":
				$this->tab_props_bg_css = "boxtitle_green_glass";
				$this->tab_props_css = "cer_navbar_selected";
			break;
			case "anti_spam":
				$this->tab_antispam_bg_css = "boxtitle_green_glass";
				$this->tab_antispam_css = "cer_navbar_selected";
			break;
			case "batch":
				$this->tab_batch_bg_css = "boxtitle_green_glass";
				$this->tab_batch_css = "cer_navbar_selected";
			break;
			case "log":
				$this->tab_log_bg_css = "boxtitle_green_glass";
				$this->tab_log_css = "cer_navbar_selected";
			break;
			default:
				$this->tab_thread_bg_css = "boxtitle_green_glass";
				$this->tab_thread_css = "cer_navbar_selected";
			break;
		}
	}
};

class CER_TICKET_MERGE
{
	var $db=null;
	var $merge_error=null;
	var $queue_access=null;
	
	function CER_TICKET_MERGE()
	{
		global $queue_access;
		
		$this->db = cer_Database::getInstance();
		
		if(empty($queue_access))
			$this->queue_access = new CER_QUEUE_ACCESS();
		else
			$this->queue_access = &$queue_access;
	}
	
	function do_merge($tids=array())
	{
		// [JAS]: We need at least 2 tickets to merge
		if(count(array_unique($tids)) < 2) {
			$this->merge_error = "You need at least two unique tickets to merge."; 
			return false;
		}
		
		// [JAS]: Make sure if we're dealing with ticket masks they're turned into DB ids.
		if(!$trans_ids = $this->find_ticket_ids($tids))
			return false;

		asort(array_unique($trans_ids)); // [JAS]: We want the first ticket
			
		// [JAS]: Merge everything else into the first (earliest) ticket
		$merge_to = array_shift($trans_ids);
		
		foreach($trans_ids as $idx => $t) {
			if(!$this->_merge_tickets($merge_to,$t))
				return false;
		}
		
		// [JAS]: Forward user to the merge destination ticket
		header("Location: " . cer_href("display.php?ticket=".$merge_to));
		
	}
	
	function find_ticket_ids($tkts=array())
	{
		if(!count($tkts)) return false;
		
		$sql = sprintf("SELECT t.ticket_id, t.ticket_mask FROM ticket t WHERE t.ticket_mask IN ('%s')",
				implode("','",$tkts)
			);
		$res = $this->db->query($sql);
		
		$trans_ids = array();
		
		foreach($tkts as $t)
			$trans_ids[strtoupper($t)] = $t;
		
		if($this->db->num_rows($res)) {
			while($row=$this->db->fetch_row($res)) {
				if(!empty($row["ticket_mask"])) {
					$trans_ids[strtoupper($row["ticket_mask"])] = $row["ticket_id"];
				}
			}
		}
		
		if(!empty($trans_ids))
			return $trans_ids;
		
		return false;		
	}
	
	function _merge_tickets($merge_to,$ticket)
	{
		static $cer_parser, $audit_log;
		global $session;  //fix
		
		if(empty($cer_parser)) $cer_parser = new CER_PARSER();
		if(empty($audit_log)) $audit_log = new CER_AUDIT_LOG();
		
		$sql = sprintf("SELECT t.ticket_id, t.ticket_queue_id, t2.ticket_subject, t2.ticket_time_worked ".
				"FROM ticket t, ticket t2 WHERE t.ticket_id = %d AND t2.ticket_id = %d",
			$merge_to,
			$ticket
			);
		$res = $this->db->query($sql);
		
		if(!is_numeric($merge_to))
		{
			$this->merge_error = "Destination Ticket ID was not a number!";
			return false;
		}
		
		if($merge_to == $ticket)
		{
			$this->merge_error = "You can't merge a ticket into itself!";
			return false;
		}
	
		$forward_ticket = $cer_parser->check_if_merged($merge_to);
		if($forward_ticket != $merge_to)
		{
			$this->merge_error = "Ticket #$merge_to has already been merged into Ticket #$forward_ticket!";
			return false;
		}
	
		if($this->db->num_rows($res))
		{
			$t_row = $this->db->fetch_row($res);
			if($this->queue_access->has_write_access($t_row["ticket_queue_id"]))
			{
				// [JAS]: Merge Requesters
				$sql = "SELECT r.ticket_id, r.address_id, r.suppress FROM requestor r WHERE r.ticket_id = " . $ticket;
				$r_res = $this->db->query($sql);
				
				if($this->db->num_rows($r_res))
				{
					$reqs = array();
					$sql = "INSERT IGNORE INTO requestor (ticket_id,address_id,suppress) VALUES ";
					
					while($r_row = $this->db->fetch_row($r_res))
					{
						array_push($reqs,sprintf("(%d,%d,%d)",
							$merge_to,
							$r_row["address_id"],
							$r_row["suppress"]
							));
					}
					
					$sql .= implode(",",$reqs);
					$this->db->query($sql);
				}
	
				// [JAS]: Merge threads
				$sql = sprintf("UPDATE thread SET ticket_id = %d WHERE ticket_id = %d",
						$merge_to,
						$ticket	
					);
				$this->db->query($sql);

				// [JAS]: Merge time tracking entries
				$sql = sprintf("UPDATE thread_time_tracking SET ticket_id = %d WHERE ticket_id = %d",
						$merge_to,
						$ticket
					);
				$this->db->query($sql);
				
				// [JAS]: Merge custom field groups
				$sql = sprintf("UPDATE entity_to_field_group SET entity_index = %d WHERE entity_code = 'T' AND entity_index = %d",
						$merge_to,
						$ticket
					);
				$this->db->query($sql);
				
				// [JAS]: Determine new min/max threads on destination ticket
				$sql = "SELECT min(th.thread_id) as min_thread, max(th.thread_id) as max_thread FROM thread th WHERE th.ticket_id = " . $merge_to;
				$th_res = $this->db->query($sql);
				
				if($this->db->num_rows($th_res))
				{
					$th_row = $this->db->fetch_row($th_res);
					$sql = sprintf("UPDATE ticket SET min_thread_id = %d, max_thread_id = %d, ticket_time_worked = ticket_time_worked + %d WHERE ticket_id = %d",
							$th_row["min_thread"],
							$th_row["max_thread"],
							$t_row["ticket_time_worked"],
							$merge_to
						);
					$this->db->query($sql);
				}
				
				// [JAS]: Set the original ticket to dead
				$sql = "UPDATE ticket SET ticket_status = 'dead' WHERE ticket_id = " . $ticket;
				$this->db->query($sql);
				
				// [JAS]: Add an audit log entry about the merge
				$log_string = "#" . $ticket . sprintf(' (%s)',
						substr(stripslashes($t_row["ticket_subject"]),0,45) . ((strlen(stripslashes($t_row["ticket_subject"])) > 45)?"...":"")
					);
				$audit_log->log_action($merge_to,$session->vars["login_handler"]->user_id,AUDIT_ACTION_MERGE_TICKET,$log_string);
				
				// [JAS]: Add the ticket IDs to the merge forward table (for parser + ticket list)
				$sql = "INSERT IGNORE INTO merge_forward (from_ticket,to_ticket) ".
					sprintf("VALUES (%d,%d)",
						$ticket,
						$merge_to	
					);
				$this->db->query($sql);
				
				// [BGH]: Update the search table so the search works on merged info
				// [JAS]: This crude fix may lead to dupes on matches.  
				//		It would be better to just reindex the merge_to ticket using the Cerb API
				$sql = "UPDATE IGNORE `search_index` SET ".
					sprintf("`ticket_id`=%d WHERE `ticket_id`=%d",
						$merge_to,
						$ticket	
					);
				$this->db->query($sql);
				
				// [BGH]: Now we remove all the non-linked data from the update duplication
				$sql = "DELETE FROM `search_index` WHERE ".
					sprintf("`ticket_id`=%d", $ticket);
				$this->db->query($sql);
				
				// [BGH]: Merge the ticket audit_log
				$sql = sprintf("UPDATE `ticket_audit_log` SET `ticket_id`=%d WHERE `ticket_id`=%d",
											$merge_to,
											$ticket
											);
				$this->db->query($sql);
			}
			else
			{
				$this->merge_error = "You do not have write access to the destination ticket ID #$merge_to!";
				return false;
			}
		}
		else
		{
			$this->merge_error = "Merge destination ticket ID #$merge_to does not exist!";
			return false;
		}
	
	return true;
	}
	
};

?>