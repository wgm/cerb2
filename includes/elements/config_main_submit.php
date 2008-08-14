<?php

if((!DEMO_MODE && isset($form_submit)))
{
	switch($form_submit)
	{
		case "global_settings":
		{
			if(empty($cfg_enable_panel_stats)) $cfg_enable_panel_stats = 0;
			if(empty($cfg_enable_customer_history)) $cfg_enable_customer_history = 0;
			if(empty($cfg_enable_id_masking)) $cfg_enable_id_masking = 0;
			if(empty($cfg_enable_audit_log)) $cfg_enable_audit_log = 0;
			if(empty($cfg_track_sid_url)) $cfg_track_sid_url = 0;
			if(empty($cfg_satellite_enabled)) $cfg_satellite_enabled = 0;
			if(empty($cfg_sendmail)) $cfg_sendmail = 0;
			if(empty($cfg_search_index_numbers)) $cfg_search_index_numbers = 0;
			if(empty($cfg_show_kb)) $cfg_show_kb = 0;
			if(empty($cfg_show_kb_topic_totals)) $cfg_show_kb_topic_totals = 0;
			if(empty($cfg_gui_version)) $cfg_gui_version = GUI_VERSION;
			if(empty($cfg_kb_editors_enabled)) $cfg_kb_editors_enabled = 0;
			if(empty($cfg_bcc_watchers)) $cfg_bcc_watchers = 0;
			if(empty($cfg_watcher_no_system_attach)) $cfg_watcher_no_system_attach = 0;
			if(empty($cfg_watcher_assigned_tech)) $cfg_watcher_assigned_tech = 0;
			if(empty($cfg_not_to_self)) $cfg_not_to_self = 0;
			if(empty($cfg_watcher_from_user)) $cfg_watcher_from_user = 0;
 			if(empty($cfg_send_precedence_bulk)) $cfg_send_precedence_bulk = 0; // [jxdemel] Feature
 			if(empty($cfg_user_only_assign_own_queues)) $cfg_user_only_assign_own_queues = 0;
 			if(empty($cfg_auto_delete_spam)) $cfg_auto_delete_spam = 0;
 			$cfg_purge_wait_hrs = sprintf("%d",$cfg_purge_wait_hrs);
			if(empty($cfg_parser_secure_enabled)) $cfg_parser_secure_enabled = 0;
			if(empty($cfg_save_message_xml)) $cfg_save_message_xml = 0;
			
			$sql = "REPLACE INTO configuration (cfg_id,gui_version,warcheck_secs,mail_delivery,smtp_server,".
				"who_max_idle_mins,auto_add_cc_reqs,enable_panel_stats,enable_customer_history,enable_id_masking,".
				"enable_audit_log,track_sid_url,satellite_enabled,xsp_url,xsp_login,xsp_password,".
				"overdue_hours,customer_ticket_history_max,sendmail,time_adjust,show_kb,show_kb_topic_totals,".
				"default_language,session_lifespan,kb_editors_enabled,ob_callback,watcher_assigned_tech,watcher_from_user,not_to_self,watcher_no_system_attach,".
 				"send_precedence_bulk,user_only_assign_own_queues,auto_delete_spam,purge_wait_hrs,".		
				"parser_secure_enabled,parser_secure_user,parser_secure_password,bcc_watchers,session_ip_security,".
				"search_index_numbers,parser_version,save_message_xml,server_gmt_offset_hrs,helpdesk_title".
				") ".
				sprintf("VALUES(1,'%s',%d,'%s','%s',".
					"%d,%d,%d,%d,%d,".
					"%d,%d,".
					((!HIDE_XSP_SETTINGS) ? 
						sprintf("%d,'%s','%s','%s',",$cfg_satellite_enabled,addslashes($cfg_xsp_url),addslashes($cfg_xsp_login),addslashes($cfg_xsp_password))
						:
						sprintf("%d,'%s','%s','%s',",$cfg->settings["satellite_enabled"],$cfg->settings["xsp_url"],$cfg->settings["xsp_login"],$cfg->settings["xsp_password"])
					).
					"%d,%d,%d,%d,%d,%d,".
					"'%s',%d,%d,'%s',%d,%d,%d,%d,".
 					"%d,%d,%d,%d,".				
					"%d,'%s','%s',%d,%d,".
					"%d,'%s',%d,'%s','%s')",
					$cfg_gui_version,$cfg_warcheck_secs,$cfg_mail_delivery,$cfg_smtp_server,
					$cfg_who_max_idle_mins,$cfg_auto_add_cc_reqs,$cfg_enable_panel_stats,$cfg_enable_customer_history,$cfg_enable_id_masking,
					$cfg_enable_audit_log,$cfg_track_sid_url,
					$cfg_overdue_hours,$cfg_customer_ticket_history_max,$cfg_sendmail,$cfg_time_adjust,$cfg_show_kb,$cfg_show_kb_topic_totals,
					addslashes($cfg_default_language),$cfg_session_lifespan,$cfg_kb_editors_enabled,$cfg_ob_callback,$cfg_watcher_assigned_tech,$cfg_watcher_from_user,$cfg_not_to_self,$cfg_watcher_no_system_attach,
 					$cfg_send_precedence_bulk,$cfg_user_only_assign_own_queues,$cfg_auto_delete_spam,$cfg_purge_wait_hrs,
					$cfg_parser_secure_enabled,addslashes($cfg_parser_secure_user),addslashes($cfg_parser_secure_password),$cfg_bcc_watchers,$cfg_session_ip_security,
					$cfg_search_index_numbers,addslashes($cfg_parser_version),$cfg_save_message_xml,$cfg_server_gmt_offset_hrs,$cfg_helpdesk_title
					);
					
			$cerberus_db->query($sql);
			$cfg = CerConfiguration::getInstance();
			$cfg->reload();
			
			break;
		}
		
		case "statuses":
		{
			$ticket_status_handler = cer_TicketStatuses::getInstance();
			$ticket_status_handler->computeTicketStatusCounts();
			$status_counts = $ticket_status_handler->getTicketStatusCounts();
			
			$status_init = explode(",", $statuses_initial); // pre-modify
			$status_process = explode(",", $statuses_ordered); // post-modify
			$status_diff = array_diff($status_init,$status_process); // deleted
			
			// [JAS]: If we've submitted an empty form, don't touch the DB
			if(empty($status_diff) && empty($statuses_add) && ($status_init == $status_process)) {
				return;
			}
			
			// [JAS]: Handle status deletes
			if(!empty($status_diff))
			foreach($status_diff as $idx => $st) {
				if($status_counts[$st] == 0 && !isset($ticket_status_handler->permanent_statuses[$st])) { // no tickets using status + not permanent
					$sql = sprintf("DELETE FROM rule_action WHERE action_type = 3 AND action_value = '%s'",
							addslashes($st)
						);
					$cerberus_db->query($sql);
				}
				else { // tickets using status or permanent status
					$status_process[] = $st; // add back from deleted
					unset($status_diff[$idx]); // remove from nuked array
				}
			}
			
			// [JAS]: Are we adding a new status?
			if(!empty($statuses_add)) {
				$matches = array();
				preg_match("/^([a-zA-Z_0-9\-\_ ]+)$/i",$statuses_add,$matches); // only alphanumerics, -, _ and space.
				if(!empty($matches)) {
					$status_process[] = $statuses_add;
				}
			}
			
			$sql = sprintf("ALTER TABLE `ticket` CHANGE `ticket_status` `ticket_status` ENUM('%s') DEFAULT 'new' NOT NULL",
					implode("','",$status_process)
				);
			$cerberus_db->query($sql);
			
			break;
		}
		
		case "plugins_edit":
		{
			if(empty($plugin_enabled)) $plugin_enabled = 0;
			
			$login_mgr = new cer_LoginPluginHandler();
			$plugin_data = $login_mgr->getPluginById($pgid);
			$params = array();
			
			require_once(PATH_LOGIN_PLUGINS . $login_mgr->getPluginFile($pgid));
			$plugin = $login_mgr->instantiatePlugin($pgid,$params);
			
			if($plugin_enabled != $plugin_data->plugin_enabled) {
				$sql = sprintf("UPDATE `plugin` SET `plugin_enabled` = %d WHERE `plugin_id` = %d",
							$plugin_enabled,			
							$pgid
						);
				$cerberus_db->query($sql);
			}
			
			// [JAS]: Handle the inserts for each setting individually for simplicity
			// 	in the REPLACE statement (not UPDATE/INSERT)
			foreach($plugin->pluginConfigure() as $var => $setting) {
				$val = isset($_REQUEST["plugin_var_" . $var]) ? $_REQUEST["plugin_var_" . $var] : "";
				
				$sql = sprintf("REPLACE INTO `plugin_var` (plugin_id, var_name, var_value) ".
						"VALUES (%d, %s, %s)",
							$pgid,
							$cerberus_db->escape($var),
							$cerberus_db->escape($val)
					);
				$cerberus_db->query($sql);
			}
			
			unset($plugin_data);
			unset($plugin);
			unset($login_mgr);
			
			break;
		}
		
		case "sla_edit":
		{
			$sla_queues = array();
			
			// [JAS]: Hash it
			if(!empty($qids)) {
				foreach($qids as $qid) {
					$sla_queues[$qid] = array($_REQUEST["q" . $qid . "_schedule"], $_REQUEST["q" . $qid . "_response_time"]);
				}
			}
			
			if(!empty($pslid)) { // clear old links
				
				$sql = sprintf("UPDATE `sla` SET `name` = %s WHERE id = %d",
						$cerberus_db->escape($sla_name),
						$pslid
					);
				$cerberus_db->query($sql);
			
				$sql = sprintf("DELETE FROM sla_to_queue WHERE sla_id = %d",
						$pslid
					);
				$cerberus_db->query($sql);
				
				$sla_id = $pslid;
			}
			else {
				$sql = sprintf("INSERT INTO `sla` (`name`) VALUES (%s)",
						$cerberus_db->escape($sla_name)
					);
				$cerberus_db->query($sql);
				
				$sla_id = $cerberus_db->insert_id();
			}
			
			$sla_vals = array();
			
			foreach($sla_queues as $q => $d) {
				$sla_vals[] = sprintf("(%d,%d,%d,%d)",
						$sla_id,
						$q,
						$d[0],
						$d[1]
					);
			}
			
			if(!empty($sla_vals)) {
				$sql = sprintf("INSERT INTO sla_to_queue (sla_id, queue_id, schedule_id, response_time) ".
						"VALUES %s ",
							implode(",",$sla_vals)
					);
				$cerberus_db->query($sql);
			}
			
			unset($pslid);
			unset($slid);
			
			break;
		}
		
		case "sla_delete":
		{
			if(!empty($sids)) {
				$sql = sprintf("DELETE FROM sla WHERE id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("DELETE FROM sla_to_queue WHERE sla_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE company SET sla_id = 0 WHERE sla_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "schedule_edit":
		{
			$days = array("sun","mon","tue","wed","thu","fri","sat");
			
			foreach($days as $day) {
				$v_hrs = $day . "_hrs";
				$v_open = $day . "_open";
				$v_close = $day . "_close";
				
				switch($$v_hrs) {
					case "24hrs":
							$$v_open = "00:00";
							$$v_close = "23:59";
						break;
					case "closed":
							$$v_open = "00:00";
							$$v_close = "00:00";
						break;
				}
			}
			
			if(!empty($pslid)) { // [JAS]: update
				$sql = sprintf("UPDATE schedule SET schedule_name = %s, sun_hrs=%s, sun_open=%s, sun_close=%s, mon_hrs=%s, mon_open=%s, mon_close=%s, ".
						"tue_hrs=%s, tue_open=%s, tue_close=%s, wed_hrs=%s, wed_open=%s, wed_close=%s, thu_hrs=%s, thu_open=%s, thu_close=%s, ".
						"fri_hrs = %s, fri_open=%s, fri_close=%s, sat_hrs=%s, sat_open=%s, sat_close=%s ".
						"WHERE schedule_id = %d",
							$cerberus_db->escape($schedule_name),
							$cerberus_db->escape($sun_hrs),
							$cerberus_db->escape($sun_open),
							$cerberus_db->escape($sun_close),
							$cerberus_db->escape($mon_hrs),
							$cerberus_db->escape($mon_open),
							$cerberus_db->escape($mon_close),
							$cerberus_db->escape($tue_hrs),
							$cerberus_db->escape($tue_open),
							$cerberus_db->escape($tue_close),
							$cerberus_db->escape($wed_hrs),
							$cerberus_db->escape($wed_open),
							$cerberus_db->escape($wed_close),
							$cerberus_db->escape($thu_hrs),
							$cerberus_db->escape($thu_open),
							$cerberus_db->escape($thu_close),
							$cerberus_db->escape($fri_hrs),
							$cerberus_db->escape($fri_open),
							$cerberus_db->escape($fri_close),
							$cerberus_db->escape($sat_hrs),
							$cerberus_db->escape($sat_open),
							$cerberus_db->escape($sat_close),
							$pslid
					);
				$cerberus_db->query($sql);
			}
			else { // insert
				$sql = sprintf("INSERT INTO schedule (schedule_name, sun_hrs, sun_open, sun_close, mon_hrs, mon_open, mon_close, ".
						"tue_hrs, tue_open, tue_close, wed_hrs, wed_open, wed_close, thu_hrs, thu_open, thu_close, fri_hrs, ".
						"fri_open, fri_close, sat_hrs, sat_open, sat_close) ".
						"VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s) ",
							$cerberus_db->escape($schedule_name),
							$cerberus_db->escape($sun_hrs),
							$cerberus_db->escape($sun_open),
							$cerberus_db->escape($sun_close),
							$cerberus_db->escape($mon_hrs),
							$cerberus_db->escape($mon_open),
							$cerberus_db->escape($mon_close),
							$cerberus_db->escape($tue_hrs),
							$cerberus_db->escape($tue_open),
							$cerberus_db->escape($tue_close),
							$cerberus_db->escape($wed_hrs),
							$cerberus_db->escape($wed_open),
							$cerberus_db->escape($wed_close),
							$cerberus_db->escape($thu_hrs),
							$cerberus_db->escape($thu_open),
							$cerberus_db->escape($thu_close),
							$cerberus_db->escape($fri_hrs),
							$cerberus_db->escape($fri_open),
							$cerberus_db->escape($fri_close),
							$cerberus_db->escape($sat_hrs),
							$cerberus_db->escape($sat_open),
							$cerberus_db->escape($sat_close)
					);
				$cerberus_db->query($sql);
			}
	
			unset($pslid); // go back to schedule screen, not edit.
			break;
		}
		
		case "schedule_delete":
		{
			if(!empty($sids)) {
				$sql = sprintf("DELETE FROM schedule WHERE schedule_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE sla_to_queue SET schedule_id = 0 WHERE schedule_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE queue SET queue_default_schedule = 0 WHERE queue_default_schedule IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "public_gui_profiles":
		{
			if(empty($login_plugin_id)) $login_plugin_id = 0;
			if(empty($pub_mod_registration)) $pub_mod_registration = 0;
			if(empty($pub_mod_kb)) $pub_mod_kb = 0;
			if(empty($pub_mod_my_account)) $pub_mod_my_account = 0;
			if(empty($pub_mod_open_ticket)) $pub_mod_open_ticket = 0;
			if(empty($pub_mod_open_ticket_locked)) $pub_mod_open_ticket_locked = 0;
			if(empty($pub_mod_track_tickets)) $pub_mod_track_tickets = 0;
			if(empty($pub_mod_announcements)) $pub_mod_announcements = 0;
			if(empty($pub_mod_welcome)) $pub_mod_welcome = 0;
			if(empty($pub_mod_contact)) $pub_mod_contact = 0;
			
			$qs = array();
			
			if(count($pub_q))
			{
				foreach($pub_q as $q)
				{
					$qs[$q] = new cer_PublicGUIQueue();
					$qs[$q]->queue_id = $q;
				}
				
				foreach($pub_qs as $idx => $qm)
				{
					if(isset($qs[$qm]))
					{
						$qs[$qm]->queue_mask = $pub_qmask[$idx];
						$qs[$qm]->queue_field_group = $pub_q_field_group[$idx];
					}
				}
			}
			
			$pub_queues = addslashes(serialize($qs));
			
			if(0 == $pfid) // INSERT
			{
				$sql = "INSERT INTO public_gui_profiles (profile_name,pub_company_name,pub_company_email,".
					"pub_confirmation_subject,pub_confirmation_body,pub_queues,pub_mod_registration,".
					"pub_mod_registration_mode,pub_mod_kb,pub_mod_my_account,pub_mod_open_ticket,".
					"pub_mod_open_ticket_locked,pub_mod_track_tickets,pub_mod_announcements,".
					"pub_mod_welcome,pub_mod_welcome_title,pub_mod_welcome_text, ".
					"pub_mod_contact, pub_mod_contact_text, login_plugin_id".
					") ".
					sprintf("VALUES (%s,%s,%s,%s,%s,%s,%d,'%s',%d,%d,%d,%d,%d,%d,%d,%s,%s,%d,%s,%d)",
							$cerberus_db->escape($profile_name),
							$cerberus_db->escape($pub_company_name),
							$cerberus_db->escape($pub_company_email),
							$cerberus_db->escape($pub_confirmation_subject),
							$cerberus_db->escape($pub_confirmation_body),
							$cerberus_db->escape($pub_queues),
							$pub_mod_registration,
							$pub_mod_registration_mode,
							$pub_mod_kb,
							$pub_mod_my_account,
							$pub_mod_open_ticket,
							$pub_mod_open_ticket_locked,
							$pub_mod_track_tickets,
							$pub_mod_announcements,
							$pub_mod_welcome,
							$cerberus_db->escape($pub_mod_welcome_title),
							$cerberus_db->escape($pub_mod_welcome_text),
							$pub_mod_contact,
							$cerberus_db->escape($pub_mod_contact_text),
							$login_plugin_id
						);
				$cerberus_db->query($sql);
				$pfid = $cerberus_db->insert_id();
			}
			else // UPDATE
			{
				$sql = sprintf("UPDATE public_gui_profiles ".
					"SET profile_name=%s,pub_company_name=%s,".
					"pub_company_email=%s,pub_confirmation_subject=%s,pub_confirmation_body=%s,pub_queues=%s,".
					"pub_mod_registration=%d,pub_mod_registration_mode='%s',pub_mod_kb=%d,pub_mod_my_account=%d, ".
					"pub_mod_open_ticket=%d,pub_mod_open_ticket_locked=%d,pub_mod_track_tickets=%d, ".
					"pub_mod_announcements=%d,pub_mod_welcome=%d,pub_mod_welcome_title=%s,pub_mod_welcome_text=%s, ".
					"pub_mod_contact=%d, pub_mod_contact_text = %s, login_plugin_id = %d ".
					"WHERE profile_id=%d",
							$cerberus_db->escape($profile_name),
							$cerberus_db->escape($pub_company_name),
							$cerberus_db->escape($pub_company_email),
							$cerberus_db->escape($pub_confirmation_subject),
							$cerberus_db->escape($pub_confirmation_body),
							$cerberus_db->escape($pub_queues),
							$pub_mod_registration,
							$pub_mod_registration_mode,
							$pub_mod_kb,
							$pub_mod_my_account,
							$pub_mod_open_ticket,
							$pub_mod_open_ticket_locked,
							$pub_mod_track_tickets,
							$pub_mod_announcements,
							$pub_mod_welcome,
							$cerberus_db->escape($pub_mod_welcome_title),
							$cerberus_db->escape($pub_mod_welcome_text),
							$pub_mod_contact,
							$cerberus_db->escape($pub_mod_contact_text),
							$login_plugin_id,
							$pfid
						);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "public_gui_profiles_delete":
		{
			foreach($fids as $key => $value) {
				$sql = "DELETE FROM public_gui_profiles WHERE profile_id = $value";
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "public_gui_fields_edit":
		{
			if(!isset($field_handler)) {
				$field_handler = new cer_CustomFieldGroupHandler();
				$field_handler->loadGroupTemplates();
			}
			
			$pg_group = new cer_PublicGUIGroup();
			$pg_group->group_name = $group_name;
			
			if(!empty($fld_ids))
			foreach($fld_ids as $f) {
				$fld = new cer_PublicGUIGroupField();
				$fld->field_id = $f;
				$fld->field_name = $name_{$f};
				$fld->field_option = $option_{$f};
				$fld->field_type = $field_handler->field_to_template[$f]->fields[$f]->field_type;
				array_push($pg_group->fields,$fld);
			}
			
			if(0 != $fid) { // [JAS]: update
				$sql = sprintf("UPDATE public_gui_fields SET `group_name`=%s, group_fields='%s' WHERE `group_id`=%d",
					$cerberus_db->escape($pg_group->group_name),
					addslashes(serialize($pg_group->fields)),
					$fid
				);
				$cerberus_db->query($sql);
			}
			else { // [JAS]: insert
				$sql = sprintf("INSERT INTO public_gui_fields (`group_name`,`group_fields`) VALUES(%s,'%s')",
					$cerberus_db->escape($pg_group->group_name),
					addslashes(serialize($pg_group->fields))
				);
				$cerberus_db->query($sql);
				$fid=$cerberus_db->insert_id();
			}
			break;
		}
		
		case "public_gui_fields_delete":
		{
			foreach($fids as $key => $value) {
				$sql = "DELETE FROM public_gui_fields WHERE group_id = $value";
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "catchall_edit":
		{
			if($priv->has_priv(ACL_QUEUE_CATCHALL,BITGROUP_3)) {

				$nuke_ids = array();
				
				if(!empty($catchall_ids))
				foreach($catchall_ids as $idx => $cid) {
					if(isset($catchall_delete_ids[$cid])) {
						$nuke_ids[] = $cid;
					}
					else {
						$sql = sprintf("UPDATE queue_catchall SET catchall_order = %d WHERE catchall_id = %d",
								$catchall_order[$idx],
								$cid
							);
						$cerberus_db->query($sql);
					}
				}
				
				$sql = sprintf("DELETE FROM queue_catchall WHERE catchall_id IN (%s)",
						implode(",",$nuke_ids)
					);
				$cerberus_db->query($sql);
				
			}
			break;
		}
		
		case "catchall_add":
		{
			$max_order = 0;
			
			$sql = "SELECT max(catchall_order) As max_order FROM queue_catchall";
			$res = $cerberus_db->query($sql);
			$highest = $cerberus_db->grab_first_row($res);
			
			if(isset($highest))
				$max_order = $highest["max_order"];
			
			$sql = "INSERT INTO queue_catchall (catchall_name, catchall_pattern, catchall_to_qid, catchall_order) ".
				sprintf("VALUES (%s,%s,%d,%d)",
						$cerberus_db->escape($catchall_name),
						$cerberus_db->escape($catchall_pattern),
						$catchall_to_qid,
						++$max_order
					);
				$cerberus_db->query($sql);
			break;
		}
		
		case "log":
		{
			if(isset($action) && $action=="delete") {
				$sql = "DELETE FROM log;";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "addresses":
		{
			if($priv->has_priv(ACL_EMAIL_BLOCK_SENDERS)) {
				if(isset($all_emails) && !empty($all_emails)) { 
					$sql = "UPDATE `address` SET `address_banned`=0 WHERE address_id IN (" . $all_emails . ")";
					$address_result = $cerberus_db->query($sql);
				}
	
				if(isset($ban_emails)) {
				  $cerberus_db = cer_Database::getInstance();
				
					foreach($ban_emails as $key => $value) {
				    	$sql = "UPDATE `address` SET `address_banned`=1  WHERE `address_id`=$value";
				    	$address_result = $cerberus_db->query($sql);
					}
				}
			}
			break;
		}
		case "maintenance":
		case "maintenance_optimize":
		case "maintenance_repair":
		case "maintenance_attachments_purge":
		{
			$sql = "SHOW TABLE STATUS";
			$res = $cerberus_db->query($sql);
			$db_tables = array();
			if($cerberus_db->num_rows($res)) {
				while($row = $cerberus_db->fetch_row($res)) {
					$db_tables[] = $row["Name"];
				}
			}
			$db_table_list = implode(",", $db_tables);
			
			if(isset($action) && $action == "optimize") {
				if(!$priv->has_priv(ACL_MAINT_OPTIMIZE,BITGROUP_1))
					break;
				$sql = "OPTIMIZE TABLE %s";
				foreach($db_tables as $db_table) {
					$opt_result = $cerberus_db->query(sprintf($sql,$db_table));
				}
			}
			else if(isset($action) && $action == "repair") {
				if(!$priv->has_priv(ACL_MAINT_OPTIMIZE,BITGROUP_1))
					break;
				$sql = "REPAIR TABLE %s";
				foreach($db_tables as $db_table) {
					$rep_result = $cerberus_db->query(sprintf($sql,$db_table));
				}
			}
			else if(isset($action) && $action == "attachments_purge") {
				if(empty($attachment_purge_ids) || !is_array($attachment_purge_ids))
					break;
					
				if(!$priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))
					break;
				
				// [JAS]: This should probably be in the attachment API
				$sql = sprintf("DELETE FROM thread_attachments_parts WHERE file_id IN (%s)",
						implode(",", $attachment_purge_ids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("DELETE FROM thread_attachments WHERE file_id IN (%s)",
						implode(",", $attachment_purge_ids)
					);
				$cerberus_db->query($sql);
			}
			else if(isset($action) && $action == "purge") {
				if(!$priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))
					break;
					
				$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.ticket_status='dead' AND t.ticket_date < DATE_SUB(NOW(),INTERVAL \"%d\" HOUR)",$cfg->settings["purge_wait_hrs"]);
				$purge_data = $cerberus_db->query($sql,false);
				if($purge_data && $cerberus_db->num_rows($purge_data) > 0) {
				   $num_purged_tickets = $cerberus_db->num_rows($purge_data);
				   $ticket_ids_arr = array();
				   $ticket_ids = -1;
					while($row = $cerberus_db->fetch_row($purge_data))
					   $ticket_ids_arr[$row[0]]= $row[0];
					if(count($ticket_ids_arr))
					   $ticket_ids = implode(",",$ticket_ids_arr);
					$sql = "SELECT th.thread_id FROM thread th WHERE th.ticket_id IN (" . $ticket_ids . ")";
					$purge_thread_data = $cerberus_db->query($sql,false);
					if($purge_thread_data && $cerberus_db->num_rows($purge_thread_data) > 0) {
						$purge_thread_arr = array(); 
						$purge_thread_ids = "-1";
						while($row = $cerberus_db->fetch_row($purge_thread_data))
							$purge_thread_arr[$row[0]] = $row[0];
						if(count($purge_thread_arr))
							$purge_thread_ids = implode(",",$purge_thread_arr);
						
						$sql = "DELETE FROM thread_content WHERE thread_id IN (" . $purge_thread_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread_content_part WHERE thread_id IN (" . $purge_thread_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread_errors WHERE thread_id IN (" . $purge_thread_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM merge_ticket WHERE to_ticket IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM ticket WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM requestor WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM trigram_training WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread_time_tracking WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM ticket_audit_log WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM search_index WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `trigram_to_ticket` WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `entity_to_field_group` WHERE entity_code = 'T' AND entity_index IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `field_group_values` WHERE entity_code = 'T' AND entity_index IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						
						$sql = "SELECT f.file_id FROM thread_attachments f WHERE f.thread_id IN (" . $purge_thread_ids . ")";
						$file_res = $cerberus_db->query($sql,false);
						if($file_res && $cerberus_db->num_rows($file_res))
						{
							$sql = "DELETE FROM thread_attachments WHERE thread_id IN (" . $purge_thread_ids . ")";
							$cerberus_db->query($sql);

							$purge_file_arr = array(); 
							$purge_file_ids = "-1";
							while($row = $cerberus_db->fetch_row($file_res))
								$purge_file_arr[$row[0]] = $row[0];
							if(count($purge_file_arr))
								$purge_file_ids = implode(",",$purge_file_arr);

							$sql = "DELETE FROM thread_attachments_parts WHERE file_id IN (" . $purge_file_ids . ")";
							$cerberus_db->query($sql);
						}
					}
				}
			}
			break;
		}
		
		case "maintenance_tempdir":
		{
			require_once(FILESYSTEM_PATH . "cerberus-api/utility/tempdir/cer_Tempdir.class.php");
			$cer_tempdir = new cer_Tempdir();
			$cer_tempdir->purge_db_tempdirs();
			$purged_files = $cer_tempdir->purge_tempdir();
			break;
		}
		
		case "rules_order":
		{
			$pre_ordered = explode(',',$_REQUEST["pre_rules_ordered"]);
			$post_ordered = explode(',',$_REQUEST["post_rules_ordered"]);
			
			if(!is_array($pre_ordered)) $pre_ordered = array($pre_ordered);
			if(!is_array($post_ordered)) $post_ordered = array($post_ordered);
			
			$order = 0;
			
			if(!empty($pre_ordered))
			foreach($pre_ordered as $o) {
				$sql = sprintf("UPDATE rule_entry SET `rule_order` = %d WHERE `rule_id` = %d",
						$order++,
						$o
					);
				$cerberus_db->query($sql);
			}
			
			$order = 0;
			
			if(!empty($post_ordered))
			foreach($post_ordered as $o) {
				$sql = sprintf("UPDATE rule_entry SET `rule_order` = %d WHERE `rule_id` = %d",
						$order++,
						$o
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "rules_edit":
		{
			if(0!=$rid) { // [JAS]: update
				$sql = sprintf("UPDATE rule_entry SET `rule_name`=%s, rule_pre_parse=%d WHERE `rule_id`=%d",
					$cerberus_db->escape($rule_name),
					$rule_pre_parse,
					$rid
				);
				$cerberus_db->query($sql);
			}
			else { // [JAS]: insert
				$sql = sprintf("INSERT INTO rule_entry(`rule_name`,`rule_pre_parse`) VALUES(%s,%d)",
					$cerberus_db->escape($rule_name),
					$rule_pre_parse
				);
				$cerberus_db->query($sql);
				$rid=$cerberus_db->insert_id();
			}
			
			// [JAS]: Clear out existing criteria + actions for this rule.
			$sql = "DELETE FROM rule_fov WHERE rule_id = $rid";
			$cerberus_db->query($sql);
			
			$sql = "DELETE FROM rule_action WHERE rule_id = $rid";
			$cerberus_db->query($sql);
			
			// [JAS]: Loop through form input for rule criteria and add to database
			$rule_crits = array("rule_crit_sender","rule_crit_subject","rule_crit_queue","rule_crit_new","rule_crit_reopened","rule_crit_attachment_name","rule_crit_spam_probability");
			foreach($rule_crits as $rc)
			{
				if($$rc) {
					$var = $rc . "_oper";
					$rc_oper = $$var;
					$var = $rc . "_value";
					$rc_value = $$var;
					
					$sql = "INSERT INTO rule_fov(rule_id,fov_field,fov_oper,fov_value) ".
					"VALUES($rid,".$$rc.",".$rc_oper.",".$cerberus_db->escape($rc_value).")";
					$cerberus_db->query($sql);
				}
			}
			
			// [JAS]: Loop through form input for rule actions and add to database
 			$rule_actions = array("rule_act_chowner","rule_act_chqueue","rule_act_chstatus","rule_act_chpriority","rule_act_break", "rule_act_pre_redirect", "rule_act_pre_bounce","rule_act_pre_ignore","rule_act_pre_no_autoreply","rule_act_pre_no_notification");
			foreach($rule_actions as $ra)
			{
				if($$ra) {
					$var = $ra . "_value";
					$ra_value = $$var;
					
					$sql = "INSERT INTO rule_action(rule_id,action_type,action_value) ".
						"VALUES($rid,".$$ra.",".$cerberus_db->escape($ra_value).")";
					$cerberus_db->query($sql);
				}
			}
			
			break;
		}
		
		case "rule_delete":
		{
			if(0==$rid)
				break;
			
			$sql = "DELETE FROM rule_entry WHERE rule_id = $rid";
			$cerberus_db->query($sql);
			$sql = "DELETE FROM rule_fov WHERE rule_id = $rid";
			$cerberus_db->query($sql);
			$sql = "DELETE FROM rule_action WHERE rule_id = $rid";
			$cerberus_db->query($sql);
			
			$prid = 0;
			$rid = 0;
			
			break;
		}
		
		case "queues_edit":
		{
			$open=0;
			$closed=0;
			$remote_access=0;
			
			if(empty($queue_mode)) $queue_mode = 0;
			if(empty($queue_default_schedule)) $queue_default_schedule = 0;
			if(empty($queue_default_response_time)) $queue_default_response_time = 0;
			
			if(isset($queue_send_open) && $queue_send_open) {
				$open=1;
			}
			if(isset($queue_send_closed) && $queue_send_closed) {
				$closed=1;
			}
			if(isset($queue_core_update) && $queue_core_update) {
				$remote_access=1;
			}
			
			if($qid!=0) { // update
				$sql = sprintf("UPDATE queue SET queue_name = %s, queue_email_display_name = %s, queue_prefix = %s,".
				" queue_response_open = %s, queue_response_close = %s, queue_response_gated = %s, " .
				" queue_send_open=$open, queue_send_closed=$closed, queue_core_update=$remote_access, queue_mode=%d, ".
				" queue_default_response_time = %d, queue_default_schedule = %d, queue_addresses_inherit_qid  = %d ".
				"WHERE queue_id = $qid",
					$cerberus_db->escape($queue_name),
					$cerberus_db->escape($queue_email_display_name),
					$cerberus_db->escape($queue_prefix),
					$cerberus_db->escape($queue_response_open),
					$cerberus_db->escape($queue_response_close),
					$cerberus_db->escape($queue_response_gated),
					$queue_mode,
					$queue_default_response_time,
					$queue_default_schedule,
					$queue_addresses_inherit_qid
				);
				$cerberus_db->query($sql);
			}
			else { // insert
				$sql = sprintf("INSERT INTO `queue` (`queue_name`, `queue_email_display_name`, `queue_prefix`, `queue_response_open`, ". 
				"`queue_response_close`,`queue_response_gated`,`queue_send_open`, `queue_send_closed`, `queue_core_update`, `queue_mode`, `queue_default_response_time`, `queue_default_schedule`, `queue_addresses_inherit_qid`) ".
				"VALUES (%s, %s, %s, %s, %s, %s, $open, $closed, $remote_access, %d, %d, %d, %d)",
						$cerberus_db->escape($queue_name),
						$cerberus_db->escape($queue_email_display_name),
						$cerberus_db->escape($queue_prefix),
						$cerberus_db->escape($queue_response_open),
						$cerberus_db->escape($queue_response_close),
						$cerberus_db->escape($queue_response_gated),
						$queue_mode,
						$queue_default_response_time,
						$queue_default_schedule,
						$queue_addresses_inherit_qid
					);
				$cerberus_db->query($sql);
				$qid=$cerberus_db->insert_id();
				
				// [JAS]: Give the queue creator write access
				$sql = "INSERT INTO `queue_access` (`queue_id`, `user_id`, `queue_access`) VALUES ($qid," . $session->vars["login_handler"]->user_id . ",'write')";
				$cerberus_db->query($sql);
			}
			
			// [JAS]: Group Privs on Queue
			if(!empty($glist))
			{
				$val_sets = array();
				
				// [JAS]: Remove the existing options for these groups
				$g_list = implode(",",$glist);
				$sql = "DELETE FROM queue_group_access WHERE group_id IN ($g_list) AND queue_id = $qid";
				$cerberus_db->query($sql);
				
				foreach($glist as $g)
				{
					$access = "gaccess_" . $g;
					$access = $$access;
					
					$val_sets[] = sprintf("(%d,%d,'%s')",
							$g,
							$qid,
							$access
						);
				}
				
				$sql = "INSERT INTO queue_group_access (group_id,queue_id,queue_access) VALUES " .
					implode(",",$val_sets);
				$cerberus_db->query($sql);
			}
			
			// [JAS]: If we're adding a queue address from the create/edit form
			if(!empty($queue_address) && !empty($queue_domain)) {
				$sql = sprintf("INSERT IGNORE INTO `queue_addresses` (`queue_id`,`queue_address`,`queue_domain`) ".
				"VALUES ($qid,%s,%s);",
				$cerberus_db->escape(strtolower($queue_address)),$cerberus_db->escape(strtolower($queue_domain)));
				$cerberus_db->query($sql);
			}
			
			// [JAS]: We need to do a check here for queue access and queue edit privs.
			if(isset($queue_addresses) && !empty($queue_addresses)) {
				$qa_ids = implode(",",$queue_addresses);
				$sql = "DELETE FROM `queue_addresses` WHERE `queue_addresses_id` IN ($qa_ids);";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "queues_delete":
		{
			// [BGH] Move tickets to new queue and delete the queue
			foreach($qids as $key => $value) {
				// [JAS]: get ticket ID for deleting stuff
				$sql = "UPDATE `ticket` SET `ticket_queue_id`=$destination_queue WHERE `ticket_queue_id` = $value";
				$ticket_result = $cerberus_db->query($sql);
				
				$sql = "DELETE FROM `queue` where `queue_id`=$value";
				$cerberus_db->query($sql);
				$sql = "DELETE FROM `queue_access` where `queue_id`=$value";
				$cerberus_db->query($sql);
				$sql = "DELETE FROM `queue_group_access` where `queue_id`=$value";
				$cerberus_db->query($sql);
				$sql = "DELETE FROM `queue_addresses` where `queue_id`=$value";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "groups_edit":
		{
			if(empty($is_core_default)) $is_core_default=0;
			
			if(empty($group_acl)) $group_acl_val=0;
			else {
				foreach($group_acl as $gacl)
				$group_acl_val += $gacl;
			}
			if(empty($group_acl2)) $group_acl2_val=0;
			else {
				foreach($group_acl2 as $gacl)
				$group_acl2_val += $gacl;
			}
			if(empty($group_acl3)) $group_acl3_val=0;
			else {
				foreach($group_acl3 as $gacl)
				$group_acl3_val += $gacl;
			}
			
			if($is_core_default == 1)
			{
				$sql = "UPDATE user_access_levels SET is_core_default = 0";
				$cerberus_db->query($sql);
			}
			
			if(0 != $gid) {
				$sql = sprintf("UPDATE user_access_levels SET ".
				"`group_name`=%s,`group_acl`='$group_acl_val',`group_acl2`='$group_acl2_val',`group_acl3`='$group_acl3_val', `is_core_default`=$is_core_default ".
				" WHERE `group_id` = $gid",
				$cerberus_db->escape($group_name));
				$cerberus_db->query($sql);
			}
			else
			{
				$sql = sprintf("INSERT INTO user_access_levels(`group_name`,`group_acl`,`group_acl2`,`group_acl3`,`is_core_default`) ".
				"VALUES (%s,'%s','%s','%s','%d');",
				$cerberus_db->escape($group_name),$group_acl_val,$group_acl2_val,$group_acl3_val,$is_core_default);
				$cerberus_db->query($sql);
				$gid = $cerberus_db->insert_id();
			}
			
			// If the user is the same group as the group they're changing, recache the group + priv values in session
			$group_id = $session->vars["login_handler"]->user_access->group_id;
			if($gid == $group_id)	
			{ 
				$session->vars["login_handler"]->user_access = new user_groups_obj($group_id);
				$priv = new privileges_obj();
			}
			
			if(isset($qlist)) {
				
				$q_list = implode(",",$qlist);
				if(empty($q_list)) $q_list = "-1";
				
				$sql = "DELETE FROM queue_group_access WHERE group_id = $pgid AND queue_id IN ($q_list)";
				$cerberus_db->query($sql);
				
				foreach($qlist as $q) {
					$fld = "qaccess_" . $q;
					$access = $$fld;
					$sql = "INSERT INTO `queue_group_access` (`queue_id`, `group_id`, `queue_access`) VALUES ($q, $gid, '$access')";
					$cerberus_db->query($sql);
				}
			}
			
			break;
		}
		
		case "group_delete":
		{
			if($priv->has_priv(ACL_GROUPS_DELETE,BITGROUP_2) && !empty($ugid)) {
				$sql = "DELETE FROM `user_access_levels` where `group_id`=$ugid";
				$cerberus_db->query($sql);
				$sql = "UPDATE `user` SET `user_group_id` = 0 WHERE `user_group_id`=$ugid";
				$cerberus_db->query($sql);
				$sql = "DELETE FROM `queue_group_access` where `group_id`=$ugid";
				$cerberus_db->query($sql);
			}
			break;
		}
		
		case "group_clone":
		{
			if($priv->has_priv(ACL_GROUPS_CREATE,BITGROUP_2) && !empty($ugid)) {
				$sql = "SELECT group_name, is_core_default, group_acl, group_acl2, group_acl3 FROM `user_access_levels` where `group_id` = $ugid";
				$res = $cerberus_db->query($sql);
				if($row = $cerberus_db->grab_first_row($res)) {
					$sql = sprintf("INSERT INTO `user_access_levels` (group_name, is_core_default, group_acl, group_acl2, group_acl3) ".
						"VALUES ('Copy of %s',%d,%d,%d,%d)",
							$row["group_name"],
							$row["is_core_default"],
							$row["group_acl"],
							$row["group_acl2"],
							$row["group_acl3"]
						);
					$cerberus_db->query($sql);
					
					$new_group_id = $cerberus_db->insert_id();
					
					if(empty($new_group_id))
						return;
					
					$sql = "SELECT queue_id, group_id, queue_access FROM queue_group_access WHERE group_id = $ugid";
					$res2 = $cerberus_db->query($sql);
					
					if($cerberus_db->num_rows($res2)) {
						while($row = $cerberus_db->fetch_row($res2)) {
							$sql = "INSERT INTO queue_group_access (queue_id, group_id, queue_access) ".
								sprintf("VALUES (%d, %d, '%s')",
									$row["queue_id"],
									$new_group_id,
									$row["queue_access"]
								);
							$cerberus_db->query($sql);
						}
					}
				}
			}
			break;
		}
		
		case "custom_fields_edit":
		{
			if(empty($field_not_searchable)) $field_not_searchable = 0;

			$handler = new cer_CustomFieldGroupHandler();
			
			// [JAS]: New group we're adding
			if(empty($gid)) {
				$gid = $handler->addGroup($group_name);
				$pgid = $gid; // [JAS]: Set persistent
			}
			// [JAS]: Editing a group
			else {
				$handler->updateGroupName($gid,$group_name);
			}
			
			// [JAS]: Add a new field			
			if(!empty($field_name) && !empty($field_type)) {
				$handler->addGroupField($field_name,$field_type,$gid,0);
			}
			
			// [JAS]: Delete any fields that were checked
			if(!empty($field_ids)) {
				$handler->deleteGroupFields($gid,$field_ids);
			}
			
			if(!empty($dropdown_ids)) {
				foreach($dropdown_ids as $drop_id) {
					
					$initial = explode(',',$_REQUEST["field_" . $drop_id . "_initial"]);
					$ordered = explode(',',$_REQUEST["field_" . $drop_id . "_ordered"]);
					$deleted = array_diff($initial,$ordered);
					
					// [JAS]: Delete any field options that were checked
					if(!empty($deleted)) {
						$handler->deleteFieldOptions($deleted);
					}
					
					if(!empty($ordered)) {
						$handler->updateFieldOptionOrdering($ordered);
					}
			
					// [JAS]: Check for new dropdown options
					$new_option = $_REQUEST["option_name_" . $drop_id];
					
					// [JAS]: If this dropdown had a new option to add
					if(!empty($new_option)) {
						$order = count($initial);
						$handler->addFieldOption($drop_id,$new_option,$order);
					}
				}
			}
			
			break;	
		}
		
		case "custom_field_bindings":
		{
			$handler = new cer_CustomFieldGroupHandler();
			
			if(!empty($custom_binding))
			foreach($custom_binding as $idx => $binding) {
				$val = (isset($custom_binding_val[$idx])) ? $custom_binding_val[$idx] : 0;
				
				$sql = sprintf("REPLACE INTO `field_group_bindings` (entity_code, group_template_id) ".
						"VALUES ('%s',%d)",
							$binding,
							$val
					);				
				$cerberus_db->query($sql);	
				
				// [JAS]: Remove old group instances if we're changing binding groups
				$sql = sprintf("SELECT efg.group_instance_id ".
					"FROM `entity_to_field_group` efg ".
					"WHERE efg.entity_code = '%s' ".
					"AND efg.group_id != %d",
						$binding,
						$val
					);
				$res = $cerberus_db->query($sql);
				
				$inst_ids = array();
				if($cerberus_db->num_rows($res)) {
					while($row = $cerberus_db->fetch_row($res)) {
						$inst_ids[] = $row["group_instance_id"];
					}
				}
				
				if(!empty($inst_ids)) {
					$handler->deleteGroupInstances($inst_ids);
				}
				
			}
			
			break;
		}
		
		case "custom_fields_delete":
		{
			$handler = new cer_CustomFieldGroupHandler();
			
			if(!empty($group_ids)) {
				foreach($group_ids as $drop_id) {
					$initial = explode(',',$_REQUEST["group_" . $drop_id . "_initial"]);
					$ordered = explode(',',$_REQUEST["group_" . $drop_id . "_ordered"]);
					$deleted = array_diff($initial,$ordered);
					
					// [JAS]: Delete any fields that were checked
//					if(!empty($deleted)) {
//						$handler->deleteGroupFields($drop_id,$deleted);
//					}
					
					if(!empty($ordered)) {
						$handler->updateFieldOrdering($ordered);
					}
				}
			}
				
			if(!empty($gids)) {
				$handler = new cer_CustomFieldGroupHandler();
				$handler->deleteGroups($gids);
			}	
		}
		
		case "users_edit":
		{
			$pass = md5($user_password_1);
			$supa=0;
			if(isset($user_superuser) && $user_superuser == 1) {
				$supa=1;
			}
			
			if($supa == 1 && $session->vars["login_handler"]->user_superuser == 0)
			{ echo "Cerberus [ERROR]: You are not permitted to make changes to superusers.";exit; }
			
			if(0!=$uid) {
				
				if($user_password_1!="") {
					$sql = sprintf("UPDATE `user` SET `user_name` = %s, `user_email` = %s, ".
					"`user_login` = %s, `user_password` = %s, ".
					"`user_group_id` = $user_group_id, `user_superuser` = '$supa' where `user_id`=$uid",
					$cerberus_db->escape($user_name),$cerberus_db->escape($user_email),$cerberus_db->escape($user_login),
					$cerberus_db->escape($pass));
				}
				else {
					$sql = sprintf("UPDATE `user` SET `user_name` = %s, `user_email` = %s, ".
					"`user_login` = %s, `user_group_id` = $user_group_id, ".
					"`user_superuser` = '$supa' where `user_id`=$uid",
					$cerberus_db->escape($user_name),$cerberus_db->escape($user_email),$cerberus_db->escape($user_login));
				}
				$cerberus_db->query($sql);
				
				if(isset($qlist)) {
					
					$q_list = implode(",",$qlist);
					if(empty($q_list)) $q_list = "-1";
					
					$sql = "DELETE FROM `queue_access` where `user_id`=$uid AND queue_id IN ($q_list)";
					$cerberus_db->query($sql);
				
					foreach($qlist as $q) {
						$fld = "qaccess_" . $q;
						$access = $$fld;
						$sql = "INSERT INTO `queue_access` (`queue_id`, `user_id`, `queue_access`, `queue_watch`) VALUES ('$q', '$uid', '$access', 0)";
						$cerberus_db->query($sql);
					}
				}
				
				if(isset($qwatch)) {
					foreach($qwatch as $qw) {
						$sql = "UPDATE `queue_access` SET `queue_watch` = 1 WHERE `user_id` = $uid AND `queue_id` = $qw";
						$cerberus_db->query($sql);
					}
				}
				
			}
			else {
				
				//[TAR]: Check to see if user login already exists in database.
				$sql = "SELECT count(*) as user_exists FROM user WHERE user_login='" . $user_login ."'";
				$usr_result = $cerberus_db->query($sql);
				$usr_count = $cerberus_db->fetch_row($usr_result);
								
				if($usr_count["user_exists"] == 1){ 
					$user_error_msg = "Cerberus [ERROR]: User login '" . $user_login ."' already exists."; 
					break;
				}

				$sql = sprintf("INSERT INTO `user` (`user_name`, `user_email`, `user_login`, `user_password`, `user_group_id`, `user_last_login`, `user_superuser`) ".
				"VALUES (%s, %s, %s, %s, $user_group_id, NOW(NULL), '$supa')",
				$cerberus_db->escape($user_name),$cerberus_db->escape($user_email),$cerberus_db->escape($user_login),$cerberus_db->escape($pass));
				$cerberus_db->query($sql);
				$uid=$cerberus_db->insert_id();
				if(isset($qlist)) {
					foreach($qlist as $q) {
						$q_access = "qaccess_" . $q;
						$q_access = $$q_access;
						$sql = "INSERT INTO `queue_access` (`queue_id`, `user_id`, `queue_access`, `queue_watch`) VALUES ('$q', '$uid', '". $q_access ."', 0)";
						$cerberus_db->query($sql);
					}
				}
				if(isset($qwatch)) {
					foreach($qwatch as $qw) {
						$sql = "UPDATE `queue_access` SET `queue_watch` = 1 WHERE `user_id` = $uid AND `queue_id` = $qw";
						$cerberus_db->query($sql);
					}
				}
			}
			break;
		}
		case "users_delete":
		{
			if($priv->has_priv(ACL_USER_DELETE,BITGROUP_1)) {
				foreach($uids as $key => $value) {
					$sql = "DELETE FROM `user` where `user_id`=$value";
					$cerberus_db->query($sql);
					$sql = "DELETE FROM `queue_access` where `user_id`=$value";
					$cerberus_db->query($sql);
					$sql = "DELETE FROM `user_sig` where `user_id`=$value";
					$cerberus_db->query($sql);
					$sql = "DELETE FROM `whos_online` where `user_id`=$value";
					$cerberus_db->query($sql);
					$sql = "DELETE FROM `user_notification` where `user_id`=$value";
					$cerberus_db->query($sql);
					// [JAS]: Unassign tickets from the deleted user
					$sql = "UPDATE `ticket` SET `ticket`.`ticket_assigned_to_id`=0 where `ticket`.`ticket_assigned_to_id`=$value";
					$cerberus_db->query($sql);
				}
			}
			break;
		}
		case "kbase_edit":
		{
			$kbase_cat_name = $cerberus_db->escape($kbase_cat_name);
			
			if($kbid!=0) { // [JAS]: update
			$sql = "UPDATE knowledgebase_categories SET kb_category_name = $kbase_cat_name, kb_category_parent_id = $kbase_cat_parent_id " .
			" WHERE kb_category_id = $kbid";
			$cerberus_db->query($sql);
			}
			else { // [JAS]: insert
			$sql = "INSERT INTO knowledgebase_categories (kb_category_name, kb_category_parent_id) " .
			" VALUES ($kbase_cat_name, $kbase_cat_parent_id)";
			$cerberus_db->query($sql);
			$kbid=$cerberus_db->insert_id();
			}
			
			unset($pkbid);
			unset($kbid);
			
			break;
		}
		case "kbase_delete":
		{
			foreach($kbids as $key => $value) {
				$sql = "DELETE FROM `knowledgebase_categories` where `kb_category_id`=$value";
				$cerberus_db->query($sql);
				$sql = "UPDATE knowledgebase SET kb_category_id=0 where kb_id=$value";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "kbase_comments":
		{
			if(count($comment_ids))	$coms = implode(",",$comment_ids); else $coms = "0";
			if($comment_action == "approve") // approve all checked comments
			{
				$sql = "UPDATE knowledgebase_comments SET kb_comment_approved = 1 WHERE kb_comment_id IN ($coms)";
				$cerberus_db->query($sql);
			}
			else if($comment_action=="reject") //reject and delete all checked comments
			{
				$sql = "DELETE FROM knowledgebase_comments WHERE kb_comment_id IN ($coms)";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "branding":
		{
			if(($logo_img["tmp_name"] != "none" && $logo_img["tmp_name"] != "") && !isset($reset_default))
			{
				if (!copy($logo_img["tmp_name"], "logo.gif")) {
					echo "failed to install logo image... check permissions on logo.gif<br>\n";
					exit();
				}
			}
			else if (isset($reset_default))
			{
				if (!copy("cer_inctr_logo.gif","logo.gif")) {
					echo "failed to restore default logo... check permissions on logo.gif<br>\n";
					exit();
				}
			}
			break;
		}
		case "key_update":
		{
			$sql = "SELECT `key_file` FROM `product_key`";
			$key_results = $cerberus_db->query($sql);
			if($cerberus_db->num_rows($key_results) > 0) {
				$sql = "UPDATE `product_key` SET `product_key`.`key_file` = '" . $product_key . "', `key_date`=NOW();";
				$cerberus_db->query($sql);
			}
			else {
				$sql = "INSERT `product_key`(`key_file`,`key_date`) VALUES('" . $product_key . "',NOW());";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "bug_send":
		{
			$mail_to = "bugs@cerberusweb.com";
			$message = "Reporter: $bug_sender\r\nDescription: $bug_description\r\n";
			
			$mail = new htmlMimeMail();
			$mail->setText(stripcslashes($message));
			$mail->setFrom($bug_sender_email);
			$mail->setSubject(stripcslashes($bug_subject));
			$mail->setReturnPath($bug_sender_email);
		    $mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
			$result = $mail->send(array($mail_to),$cfg->settings["mail_delivery"]);
			
			break;
		}
		case "feedback_send":
		{
			$mail_to = "feedback@cerberusweb.com";
			$message = "Reporter: $feedback_sender\r\nFeedback: $feedback_content\r\n";
			
			$mail = new htmlMimeMail();
			$mail->setText(stripcslashes($message));
			$mail->setFrom($feedback_sender_email);
			$mail->setSubject(stripcslashes($feedback_subject));
			$mail->setReturnPath($feedback_sender_email);
		    $mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
			$result = $mail->send(array($mail_to),$cfg->settings["mail_delivery"]);
			
			break;
		}
	}
}

?>
