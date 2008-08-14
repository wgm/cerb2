<?php
	
	if(empty($audit_log)) {
		$audit_log = new CER_AUDIT_LOG();
	}

	switch($form_submit)
	{
		case "tickets_modify":
		{
			// [JAS]: Changed structure from if..elseif..else to if...if...if, so any
			//	combo of properties could be changed at the same time.
			if(-1!=$status_id && !empty($status_id) && !empty($bids)) {
				foreach($bids as $value) {
					$sql = "UPDATE `ticket` set `ticket_status`='$status_id' where `ticket_id`=$value";
					$cerberus_db->query($sql);
					$audit_log->log_action($value,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_STATUS,$status_id);
				}
			}
			if (-1!=$queue_id && !empty($queue_id) && !empty($bids)) {
				$sql = "SELECT q.queue_name FROM queue q WHERE q.queue_id = $queue_id";
				$queue_record = $cerberus_db->query($sql);
				if($cerberus_db->num_rows($queue_record) > 0) $queue_row = $cerberus_db->fetch_row($queue_record);
				
				foreach($bids as $value) {
					$sql = "UPDATE `ticket` set `ticket_queue_id`='$queue_id' where `ticket_id`=$value";
					$result = $cerberus_db->query($sql);
					$audit_log->log_action($value,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_QUEUE,$queue_row["queue_name"]);
				}
			}
			if (-1!=$owner_id && !empty($bids)) {
				
				if(!empty($owner_id)) {
					$sql = "SELECT u.user_name FROM user u WHERE u.user_id = $owner_id";
					$user_record = $cerberus_db->query($sql);
					if($cerberus_db->num_rows($user_record) > 0) $user_row = $cerberus_db->fetch_row($user_record);
					$user_name = $user_row["user_name"];
				}
				else
				$user_name = "Nobody";
				
				foreach($bids as $value) {
					$sql = "UPDATE `ticket` set `ticket_assigned_to_id`='$owner_id' where `ticket_id`=$value";
					$result = $cerberus_db->query($sql);
					$audit_log->log_action($value,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CHANGED_ASSIGN,$user_name);
					
					// [JAS]: Trigger the Assignment Notification
					$notification = new CER_NOTIFICATION($owner_id);
					$notification->trigger_event(NOTIFY_EVENT_ASSIGNMENT,$value);
				} // end foreach
			}
			
			if (-1!=$action_id && !empty($bids)) {
				switch($action_id)
				{
				case "due_24h":
					if(!empty($bids)) {
						$sql = sprintf("UPDATE ticket SET ticket_due = DATE_ADD(NOW(),INTERVAL \"24\" HOUR) WHERE ticket_id IN (%s)",
								implode(",",$bids)
							);
						$cerberus_db->query($sql);
					}
					break;
					
				case "due_now":
					if(!empty($bids)) {
						$sql = sprintf("UPDATE ticket SET ticket_due = DATE_SUB(NOW(),INTERVAL \"1\" SECOND) WHERE ticket_id IN (%s)",
								implode(",",$bids)
							);
						$cerberus_db->query($sql);
					}
					break;
					
				case "mark_as_spam":
					$bayes = new cer_BayesianAntiSpam();
					$bayes->mark_tickets_as_spam($bids);
					break;
					
				case "mark_as_ham":
					$bayes = new cer_BayesianAntiSpam();
					$bayes->mark_tickets_as_ham($bids);
					break;
					
				case "block_sender":
				case "unblock_sender":
					$tik_ids = implode(",",$bids);
					if(empty($tik_ids)) $tik_ids = "-1";
					
					$sql = "SELECT th.thread_address_id FROM ticket t LEFT JOIN thread th ON (t.min_thread_id = th.thread_id) WHERE t.ticket_id IN ($tik_ids)";
					$res = $cerberus_db->query($sql);
					
					$ban_ids = array();
					
					if($cerberus_db->num_rows($res))
					{
						while($row = $cerberus_db->fetch_row($res))
							$ban_ids[$row["thread_address_id"]] = 1;
							
						$ban_list = implode(",",array_keys($ban_ids));	
						
						$ban_value = (($action_id == "block_sender") ? 1 : 0);
						
						$sql = "UPDATE address SET address_banned = $ban_value WHERE address_id IN ($ban_list)";
						$cerberus_db->query($sql);
					}
					
					break;
					
				case "batch_add":
					foreach($bids as $value)
					{ $session->vars["login_handler"]->batch->batch_add($value); }
					break;
					
				case "batch_remove":
					foreach($bids as $value)
					{ $session->vars["login_handler"]->batch->batch_remove($value); }
					break;
					
				case "merge":
					$merge = new CER_TICKET_MERGE();
					if(!$merge->do_merge($bids)) {
						$merge_error = $merge->merge_error;
					}
					break;
				}
			}
			
			// Send satellite status updates to the master GUI about the
			//	ticket's property changes
			if($cfg->settings["satellite_enabled"])
			{
				if(count($bids))
				foreach($bids as $value) {
					$xsp_upd = new xsp_login_manager();
					$xsp_upd->xsp_send_summary($value);
				}
			}
			
			break;
		} // end case
	}
?>