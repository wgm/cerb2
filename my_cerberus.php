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
|
| File: my_cerberus.php
|
| Purpose: User's settings, preferences, customization, tasks, 
|	notification, scheduler, etc.
|
| Developers involved with this file: 
|		Jeff Standen	(jeff@webgroupmedia.com)	[JAS]
|		Ben Halsted 	(ben@webgroupmedia.com)		[BGH]
|		Trent Ramseyer (trent@webgroupmedia.com)	[TAR]
|
| Contributors:
|		Ralf Ebeling	(ralf.ebeling@gmx.net)		[REB]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_OB_CALLBACK",true); // [JAS]: Leave this true

// [JAS]: To change the language immediatly, even for the current page.
//   this needs to be defined before the call to the site.config.php page
$prefs_user_language = (isset($_REQUEST["prefs_user_language"])) ? $_REQUEST["prefs_user_language"] : "";

require("site.config.php");

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/user/user_prefs.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/my_cerberus/my_cerberus.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/layout/cer_Layout.class.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();
$project_prefs = array();

if(!$priv->has_priv(ACL_PREFS_USER,BITGROUP_1)) { echo LANG_CERB_ERROR_ACCESS; exit; }

@$form_submit = $_REQUEST["form_submit"];
@$mode = $_REQUEST["mode"];

@$refresh_rate = $_REQUEST["refresh_rate"];
@$ticket_order = $_REQUEST["ticket_order"];
@$signatureBox = $_REQUEST["signatureBox"];
@$password_current = $_REQUEST["password_current"];
@$password_new = $_REQUEST["password_new"];
@$password_verify = $_REQUEST["password_verify"];
@$signature_pos = $_REQUEST["signature_pos"];
@$signature_autoinsert = $_REQUEST["signature_autoinsert"]; // [JSJ]: Whether to autoinsert signature or not.
if(!$signature_autoinsert) @$signature_autoinsert = 0;
$keyboard_shortcuts = isset($_REQUEST["keyboard_shortcuts"]) ? $_REQUEST["keyboard_shortcuts"] : 0;
$gmt_offset = isset($_REQUEST["gmt_offset"]) ? $_REQUEST["gmt_offset"] : $cfg->settings["server_gmt_offset_hrs"];

@$notify_new_enabled = $_REQUEST["notify_new_enabled"];
@$notify_new_emails = $_REQUEST["notify_new_emails"];
@$notify_new_qlist = $_REQUEST["notify_new_qlist"];
@$notify_new_template = $_REQUEST["notify_new_template"];
@$notify_assigned_enabled = $_REQUEST["notify_assigned_enabled"];
@$notify_assigned_emails = $_REQUEST["notify_assigned_emails"];
@$notify_assigned_template = $_REQUEST["notify_assigned_template"];
@$notify_client_reply_enabled = $_REQUEST["notify_client_reply_enabled"];
@$notify_client_reply_emails = $_REQUEST["notify_client_reply_emails"];
@$notify_client_reply_template = $_REQUEST["notify_client_reply_template"];

@$watcher_q = $_REQUEST["watcher_q"];
@$assign_q = $_REQUEST["assign_q"];
@$assign_max = $_REQUEST["assign_max"];

@$pm_to_user_id = $_REQUEST["pm_to_user_id"];
@$pm_subject = $_REQUEST["pm_subject"];
@$pm_message = $_REQUEST["pm_message"];
@$pm_action = $_REQUEST["pm_action"];
@$pm_id = $_REQUEST["pm_id"];
@$pm_ids = $_REQUEST["pm_ids"];
@$pm_do = $_REQUEST["pm_do"];
@$pm_folder = $_REQUEST["pm_folder"];
@$pm_uid = $_REQUEST["pm_uid"];

@$mo_offset = $_REQUEST["mo_offset"];
@$mo_d = $_REQUEST["mo_d"];
@$mo_m = $_REQUEST["mo_m"];
@$mo_y = $_REQUEST["mo_y"];

@$pid = $_REQUEST["pid"];
@$tid = $_REQUEST["tid"];
@$nid = $_REQUEST["nid"];

@$task_summary = $_REQUEST["task_summary"];
@$task_parent_id = $_REQUEST["task_parent_id"];
@$task_project_id = $_REQUEST["task_project_id"];
@$task_project_category_id = $_REQUEST["task_project_category_id"];
@$task_progress = $_REQUEST["task_progress"];
@$task_priority = $_REQUEST["task_priority"];
@$task_assigned_uid = $_REQUEST["task_assigned_uid"];
@$task_due_date = $_REQUEST["task_due_date"];
@$task_reminder_date = $_REQUEST["task_reminder_date"];
@$task_description = $_REQUEST["task_description"];
@$task_delete = $_REQUEST["task_delete"];

@$note_text = $_REQUEST["note_text"];

@$project_name = $_REQUEST["project_name"];
@$project_acl = $_REQUEST["project_acl"];
@$project_manager_uid = $_REQUEST["project_manager_uid"];
@$project_delete = $_REQUEST["project_delete"];
@$add_project_category_name = $_REQUEST["add_project_category_name"];
@$pcids = $_REQUEST["pcids"];

@$filter_hide_completed_projects = $_REQUEST["filter_hide_completed_projects"];
@$filter_category = $_REQUEST["filter_category"];
@$filter_hide_completed = $_REQUEST["filter_hide_completed"];
@$filter_only_my_tasks = $_REQUEST["filter_only_my_tasks"];
@$sort_by = $_REQUEST["sort_by"];
@$sort_asc = $_REQUEST["sort_asc"];
@$pm_brief = $_REQUEST["pm_brief"];

$layout_display_modules = (isset($_REQUEST["layout_display_modules"])) ? $_REQUEST["layout_display_modules"] : "";

$layout_display_reset = isset($_REQUEST["layout_display_reset"]) ? $_REQUEST["layout_display_reset"] : 0;

$audit_log = new CER_AUDIT_LOG();

$uid = $session->vars["login_handler"]->user_id;

if(empty($pm_action)) $pm_action = "list";

if(!empty($prefs_user_language))
	$session->vars["login_handler"]->user_prefs->user_language = $prefs_user_language;

if(!empty($pm_folder))
	$session->vars["pm_folder"] = $pm_folder;

if(empty($pid)) $pid = 0;
if(empty($tid)) $tid = 0;

// [JAS]: Log user's action for Who's Online
switch($mode)
{
	case "preferences":
		log_user_who_action(WHO_PREFS);
	break;
	case "messages":
		log_user_who_action(WHO_MYCERB_PM);
	break;
	case "tasks":
		log_user_who_action(WHO_MYCERB_TASKS);
	break;
	default:
		log_user_who_action(WHO_MYCERB);
	break;
}
	
if(isset($form_submit)) // [JAS]: process incoming preferences updates
{
switch ($mode)
{
	case "preferences":
	{
		// [JAS]: Check and update/insert user preferences
		$sql = "SELECT prefs.user_id FROM user_prefs prefs WHERE prefs.user_id = " . $session->vars["login_handler"]->user_id;
		$result = $cerberus_db->query($sql);
		if($cerberus_db->num_rows($result) == 0)
			{
			$sql = "INSERT INTO user_prefs (user_id,refresh_rate,ticket_order,user_language,signature_pos,signature_autoinsert,keyboard_shortcuts,gmt_offset) VALUES(" . $session->vars["login_handler"]->user_id . ",'$refresh_rate','$ticket_order','$prefs_user_language',$signature_pos,$signature_autoinsert,$keyboard_shortcuts,'$gmt_offset')";
			$cerberus_db->query($sql);
			}
		else
			{
	   	$sql = "UPDATE user_prefs  SET refresh_rate='$refresh_rate', ticket_order='$ticket_order', signature_pos=$signature_pos, signature_autoinsert=$signature_autoinsert, keyboard_shortcuts=$keyboard_shortcuts, gmt_offset='$gmt_offset' ";
	    // [JAS]: If in DEMO mode, do not save the language preference to the 
	    //		database.  Only exist in current session.
	    if(!DEMO_MODE) $sql .= ", user_language='$prefs_user_language'";
	    $sql .= " WHERE user_id = " . $session->vars["login_handler"]->user_id;
			$cerberus_db->query($sql);
			}
		
	  // [JAS]: Check and update/insert signature
		$newSignature = addslashes($signatureBox);
		$sql = "SELECT sig.sig_content, sig.sig_id FROM user_sig sig WHERE sig.user_id = " . $session->vars["login_handler"]->user_id;
		$result = $cerberus_db->query($sql);
		if($cerberus_db->num_rows($result) == 0)
		{
			$sql = "INSERT INTO user_sig(sig_content,user_id) VALUES('$newSignature'," . $session->vars["login_handler"]->user_id . ")";
			$cerberus_db->query($sql);
		}
		else
		{
			$sql = "UPDATE user_sig SET sig_content = '$newSignature' WHERE user_id = " . $session->vars["login_handler"]->user_id;
			$cerberus_db->query($sql);
		}
	
		// Recache the user's preferences in the session
		$session->vars["login_handler"]->user_prefs = new user_prefs_mgr($session->vars["login_handler"]->user_id);
		
		// [TAR]: Check and update user password
		$crypt_pass = md5($password_current);
		//$crypt_pass = crypt($password_current,substr($password_current,1,2));
		
		$sql = "SELECT u.user_id FROM user u WHERE u.user_id = '" . $session->vars["login_handler"]->user_id ."' AND u.user_password = '" . $crypt_pass ."'";
		$result = $cerberus_db->query($sql);
		if($cerberus_db->num_rows($result) == 0)
			{
			 if (($password_current != "") || ($password_new != "") || ($password_verify != "")){
	 		  $password_error = LANG_PREF_NOMATCH;
			 }
			}
		else
			{
			 // [JAS]: If the password was changed + verified and we're not in DEMO
	     if (($password_new == $password_verify) && ($password_new != "") && !DEMO_MODE){
			 $pass = md5($password_new);
			 //$pass = crypt($password_new,substr($password_new,1,2));
			 $sql = "UPDATE `user` SET `user_password` = '$pass' where `user_id`='". $session->vars["login_handler"]->user_id ."'";
			 $cerberus_db->query($sql);
			 
		     // [REB]: continue the current session with my new password
		     $session->vars["login_handler"]->user_password = $password_new;
		     if(isset($session) && method_exists($session,"save_session"))
		     { $session->save_session(); }
		
			 meta_redirect(cer_href($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/index.php"));
			 exit;
			 } else {
			 $password_error = LANG_PREF_NOMATCH;
			 }
			}
		
			break;
			
			// [JAS]: Put new preferences into effect immediately
			$session->vars["login_handler"]->user_prefs = 
				new user_prefs_mgr($session->vars["login_handler"]->user_id);
			
		} // end case 'preferences'
		
		case "layout":
		{
			$display_modules = explode(",",$layout_display_modules);
			$user_layout_handler = &$session->vars["login_handler"]->user_prefs->layout_handler;
			$user_layout_pages_ptr = &$user_layout_handler->users[$uid]->layout_pages;
			
			if(!empty($layout_display_reset)) { // [JAS]: Are we clearing the display layout to defaults?
				$user_layout_pages_ptr["display"]->params["display_modules"] = $user_layout_handler->users[$uid]->display_module_defaults;
			} else {
				$user_layout_pages_ptr["display"]->params["display_modules"] = $display_modules;
			}

			$user_layout_handler->saveUserLayoutPages($uid,$user_layout_pages_ptr);
			
			break;
		}
		
		case "messages":
		{
			switch($form_submit)
			{
				case "pm_delete":
				case "pm_batch":
				{
					if(count($pm_ids) == 0)
					{ if(!empty($pm_id) && $form_submit == "pm_delete") {$pm_ids = array($pm_id); $pm_do = "delete";} else break; }
										
					switch($pm_do)
					{
						case "delete":
						{
							$sql = sprintf("DELETE FROM private_messages WHERE pm_to_user_id = %d AND pm_id IN (%s)",
								$session->vars["login_handler"]->user_id,implode(",",$pm_ids));
							$cerberus_db->query($sql);
							$pm_mode = "list";
							break;
						}
						case "mark_unread":
						case "mark_read":
						{
							$read_bit = (($pm_do == mark_read)?1:0);
							
							$sql = sprintf("UPDATE private_messages SET pm_marked_read = %d WHERE pm_to_user_id = %d AND pm_id IN (%s)",
								$read_bit,$session->vars["login_handler"]->user_id,implode(",",$pm_ids));
							$cerberus_db->query($sql);
							
							$session->vars["login_handler"]->check_for_pm();
							
							break;
						}
					}
					
					break;
				}
				case "pm_send":
				{
					if(empty($pm_to_user_id)) break;
					if(empty($pm_subject)) break;
					if(empty($pm_message)) break;
					
					$pm_folder = "ob";
					$session->vars["pm_folder"] = $pm_folder;
					
					$sql = sprintf("INSERT INTO private_messages (pm_to_user_id,pm_from_user_id,pm_subject,pm_date,pm_message) ".
						"VALUES (%d,%d,%s,NOW(),%s)",
						$pm_to_user_id,$session->vars["login_handler"]->user_id,$cerberus_db->escape($pm_subject),
						$cerberus_db->escape($pm_message));
					$cerberus_db->query($sql);
					break;	
				} // end case 'pm_send'
			}
			
			break;
		}
		
		case "assign":
		{
			if(empty($assign_max)) $assign_max = 5;
			if(empty($assign_q)) $assign_q = array();
			
			// [JAS]: Clear current watcher settings before we set the new ones.
			$sql = "UPDATE queue_access SET queue_watch = 0 WHERE user_id = " . $session->vars["login_handler"]->user_id;
			$cerberus_db->query($sql);
			
			// [JAS]: TODO, check queue permissions on new queues we're setting watcher on
			
			// [JAS]: Set the watcher permissions for this user from the notification form
			if(count($watcher_q))
			{
				$sql = sprintf("UPDATE queue_access SET queue_watch = 1 WHERE user_id = %d AND queue_id IN (%s)",
					$session->vars["login_handler"]->user_id,implode(",",$watcher_q));
				$cerberus_db->query($sql);
			}
			
			$aq = new user_prefs_mgr_assign_queues();
			$aq->max = (int) $assign_max;
			
			if(is_array($assign_q) && !empty($assign_q))
				foreach($assign_q as $q)
					$aq->queues[$q] = $q;
			
			$aqs = addslashes(serialize($aq));
			
			// [JAS]: Re-cache assign queue pref obj
			$session->vars["login_handler"]->user_prefs->assign_queues = $aq;
			
			$sql = sprintf("UPDATE user_prefs SET assign_queues = '%s' WHERE user_id=%d",
					$aqs,
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			
			break;
		} // end case 'assign'
		
		case "tasks":
		{
			$task_summary = trim($task_summary);
			if(empty($task_summary)) $task_summary = "Unnamed Task";
			
			$project_name = trim($project_name);
			if(empty($project_name)) $project_name = "Unnamed Project";
			
			switch($form_submit)
			{
				case "projects_filter":
				{
					if(!empty($filter_hide_completed_projects))
						$session->vars["project_filter_hide_completed_projects"] = $filter_hide_completed_projects;
					else
						$session->vars["project_filter_hide_completed_projects"] = 0;
						
					break;
				}
				case "task_create":
				{
					// [JAS]: MySQL Database format dates
					list($mm,$md,$my) = sscanf($task_due_date,"%d/%d/%d");
 					if(empty($mm) && empty($md) && empty($my))
 						$task_due_date = "0000-00-00 00:00:00";
 					else {
 						$date = new cer_DateTime(mktime(0,0,0,$mm,$md,$my));
 						$task_due_date = $date->getUserDate("%Y-%m-%d %H:%M:%S");
 					}
					
					list($mm,$md,$my) = sscanf($task_reminder_date,"%d/%d/%d");
 					if(empty($mm) && empty($md) && empty($my))
 						$task_reminder_date = "0000-00-00 00:00:00";
 					else {
 						$date = new cer_DateTime(mktime(0,0,0,$mm,$md,$my));
 						$task_reminder_date = $date->getUserDate("%Y-%m-%d %H:%M:%S");
 					}
					
					$sql = sprintf("INSERT INTO tasks (task_summary, task_parent_id, task_project_id, task_project_category_id, task_progress, task_priority, ".
								"task_assigned_uid, task_due_date, task_reminder_date, task_description, task_created_uid, ".
								"task_created_date, task_updated_date) ".
								"VALUES (%s,%d,%d,%d,%d,'%s',".
								"%d,'%s','%s',%s,%d,".
								"NOW(),NOW());",
							$cerberus_db->escape($task_summary),
							$task_parent_id,
							$task_project_id,
							$task_project_category_id,
							$task_progress,
							$task_priority,
							$task_assigned_uid,
							$task_due_date,
							$task_reminder_date,
							$cerberus_db->escape($task_description),
							$session->vars["login_handler"]->user_id
						);
						
					$res = $cerberus_db->query($sql);
					
					break;
				}
				case "task_update":
				{
					// [JAS]: Are we deleting? If so, are we authorized to?
					if(!empty($task_delete))
					{
						$sql = "SELECT p.project_manager_uid FROM tasks_projects p LEFT JOIN tasks t ON (p.project_id = t.task_project_id) WHERE t.task_id = " . $tid;
						$res = $cerberus_db->query($sql);
						
						if($row=$cerberus_db->grab_first_row($res))
						{
							if($row["project_manager_uid"] == $session->vars["login_handler"]->user_id
								|| $session->vars["login_handler"]->user_superuser)
								{
									$sql = "DELETE FROM tasks WHERE task_id = " . $tid;
									$cerberus_db->query($sql);
									
									$sql = "DELETE FROM tasks_notes WHERE task_id = " . $tid;
									$cerberus_db->query($sql);
									
									unset($tid);
									break;
								}
								
						}
					}
					
					// [JAS]: MySQL Database format dates
					list($mm,$md,$my) = sscanf($task_due_date,"%d/%d/%d");
 					if(empty($mm) && empty($md) && empty($my))
 						$task_due_date = "0000-00-00 00:00:00";
 					else {
 						$date = new cer_DateTime(mktime(0,0,0,$mm,$md,$my));
 						$task_due_date = $date->getUserDate("%Y-%m-%d %H:%M:%S");
 					}
 							
					list($mm,$md,$my) = sscanf($task_reminder_date,"%d/%d/%d");
 					if(empty($mm) && empty($md) && empty($my))
						$task_reminder_date = "0000-00-00 00:00:00";
 					else {
 						$date = new cer_DateTime(mktime(0,0,0,$mm,$md,$my));
						$task_reminder_date = $date->getUserDate("%Y-%m-%d %H:%M:%S");
 					}
					
					$sql = sprintf("UPDATE tasks SET task_summary=%s, task_progress=%d, task_priority=%d, task_project_category_id=%d, ".
						"task_assigned_uid=%d,task_due_date='%s',task_reminder_date='%s',task_description=%s, task_updated_date=NOW() ".
						"WHERE task_id = %d",
						$cerberus_db->escape($task_summary),
						$task_progress,
						$task_priority,
						$task_project_category_id,
						$task_assigned_uid,
						$task_due_date,
						$task_reminder_date,
						$cerberus_db->escape($task_description),
						$tid
						);
					$res = $cerberus_db->query($sql);
					unset($res);
					
					// [JAS]: End up using this in "next step" functionality?  (Goes back to project task list with tid=0)
					//$tid = 0;
					
					break;
				}
				case "task_project_create":
				{
					$project_acl = explode(",",$project_acl);					
					if(is_array($project_acl)) {
						$find=false;
						
						if(is_array($project_acl) && !empty($project_acl))
						foreach($project_acl as $a) // [JAS]: Make sure the project manager is in ACL
							if($a == $project_manager_uid) $find=true;
							
						// [JAS]: If not, add.
						if($find==false) array_push($project_acl,$project_manager_uid);
							
						$acl_list = implode(",",$project_acl);
					}
					else
						$acl_list = $project_manager_uid;
					
					$sql = sprintf("INSERT INTO tasks_projects (project_name,project_manager_uid,project_acl) ".
						"VALUES (%s,%d,'%s');",
						$cerberus_db->escape($project_name),$project_manager_uid,$acl_list
						);
					
					$res = $cerberus_db->query($sql);
					unset($res);
					
					break;
				}
				case "task_project_details":
				{
					// [JAS]: Are we deleting? If so, are we authorized to?
					if(!empty($project_delete))
					{
						$sql = "SELECT p.project_manager_uid FROM tasks_projects p WHERE p.project_id = $pid";
						$res = $cerberus_db->query($sql);
						
						if($row=$cerberus_db->grab_first_row($res))
						{
							if($row["project_manager_uid"] == $session->vars["login_handler"]->user_id
								|| $session->vars["login_handler"]->user_superuser)
								{
									$sql = "DELETE FROM tasks_projects WHERE project_id = " . $pid;
									$cerberus_db->query($sql);
									
									$sql = "DELETE FROM tasks_projects_categories WHERE project_id = " . $pid;
									$cerberus_db->query($sql);
									
									$sql = "SELECT t.task_id FROM tasks t WHERE t.task_project_id = " . $pid;
									$res = $cerberus_db->query($sql);
									
									if($cerberus_db->num_rows($res))
									{
										$task_ids = array();
										while($tr = $cerberus_db->fetch_row($res))
											array_push($task_ids,$tr["task_id"]);
										
										if(count($task_ids)) {
											$tid_list = implode(",",$task_ids);

											$sql = sprintf("DELETE FROM tasks WHERE task_id IN (%s)",
													$tid_list
												);
											$cerberus_db->query($sql);
											
											$sql = sprintf("DELETE FROM tasks_notes WHERE task_id IN (%s)",
													$tid_list
												);
											$cerberus_db->query($sql);
											
											unset($task_ids);
											unset($tid_list);
										}
									}
									
									unset($pid);
									break;
								}
						}
					}
					
					$sql = sprintf("UPDATE tasks_projects SET project_name=%s,project_acl='%s' ".
						"WHERE project_id = %d ",
						$cerberus_db->escape($project_name),
						$project_acl,
						$pid						
						);
					$res = $cerberus_db->query($sql);
					
					$sql = sprintf("UPDATE tasks SET task_assigned_uid = 0 ".
						"WHERE task_project_id = %d AND task_assigned_uid NOT IN (%s)",
						$pid,
						$project_acl
						);
					$res = $cerberus_db->query($sql);
					
					// [JAS]: If we had text in add category
					if(!empty($add_project_category_name))
					{
						$sql = sprintf("INSERT INTO tasks_projects_categories (project_id,category_name) ".
							"VALUES (%d,%s)",
								$pid,
								$cerberus_db->escape($add_project_category_name)
							);
						$res = $cerberus_db->query($sql);
					}
					
					// [JAS]: See if we're deleting task categories from the project.  If so, unassign any tasks using them
					if(count($pcids))
					{
						$sql = sprintf("DELETE FROM tasks_projects_categories WHERE project_id = %d AND category_id IN (%s)",
								$pid,
								implode(",",$pcids)
							);
						$res = $cerberus_db->query($sql);
						
						$sql = sprintf("UPDATE tasks SET task_project_category_id = 0 WHERE task_project_id = %d AND task_project_category_id IN (%s)",
								$pid,
								implode(",",$pcids)
							);
						$res = $cerberus_db->query($sql);
					}
						
					unset($res);
					break;
				}
				case "task_add_note":
				{
					if(empty($tid) || empty($pid)) break;
					
					$sql = sprintf("INSERT INTO tasks_notes (task_id,note_poster_uid,note_timestamp,note_text)".
						"VALUES (%d,%d,NOW(),%s) ",
						$tid,
						$session->vars["login_handler"]->user_id,
						$cerberus_db->escape($note_text)
						);
					$res = $cerberus_db->query($sql);
					
					$sql = sprintf("UPDATE tasks SET task_updated_date = NOW() WHERE task_id = %d",
						$tid
						);
					$res = $cerberus_db->query($sql);
					
					unset($res);
					break;
				}
				case "task_delete_note":
				{
					if(empty($nid) || empty($tid) || empty($pid)) break;
					
					$sql = "DELETE FROM tasks_notes WHERE note_id = $nid";
					$cerberus_db->query($sql);
					
					break;
				}
				case "tasks_filter":
				{
					if(isset($pm_brief)) {
						if(!empty($pm_brief))
							$session->vars["project_pm_brief"] = $pm_brief;
						else
							$session->vars["project_pm_brief"] = $pm_brief;
					}
					
					if(!isset($sort_by) && !isset($pm_brief))
					{
						if(!empty($filter_category)) 
							$session->vars["project_filter_category"] = $filter_category;
						else
							$session->vars["project_filter_category"] = 0;
						
						if(!empty($filter_hide_completed)) 
							$session->vars["project_filter_hide_completed"] = $filter_hide_completed;
						else
							$session->vars["project_filter_hide_completed"] = 0;
						
						if(!empty($filter_only_my_tasks)) 
							$session->vars["project_filter_only_my_tasks"] = $filter_only_my_tasks;
						else
							$session->vars["project_filter_only_my_tasks"] = 0;
					}
					
					if(!isset($filter_category) && !isset($pm_brief))
					{
						if(!empty($sort_by)) 
							$session->vars["project_sort_by"] = $sort_by;
						else
							$session->vars["project_sort_by"] = "";
						
						if(!empty($sort_asc)) 
							$session->vars["project_sort_asc"] = 1;
						else
							$session->vars["project_sort_asc"] = 0;
					}
					
					break;
				}
			}
			
			break;
		} // end case 'tasks'
		
		case "notification":
		{
			$notification = new CER_NOTIFICATION_USER($session->vars["login_handler"]->user_id);
			$queues = array();
			
			// [JAS]: New Ticket
			if(is_array($notify_new_qlist) && !empty($notify_new_qlist))
			foreach($notify_new_qlist as $idx => $q)
				if(is_array($notify_new_enabled))
					if(array_search($q,$notify_new_enabled) !== false)
						$queues[$q]	= $notify_new_emails[$idx];
			
			$notification->n_new_ticket->queues_send_to = $queues;
			$notification->n_new_ticket->template = stripslashes($notify_new_template);
			
			// [JAS]: Assigned
			$notification->n_assignment->enabled = $notify_assigned_enabled;
			$notification->n_assignment->send_to = $notify_assigned_emails;
			$notification->n_assignment->template = stripslashes($notify_assigned_template);
			
			// [JAS]: Client Reply
			$notification->n_client_reply->enabled = $notify_client_reply_enabled;
			$notification->n_client_reply->send_to = $notify_client_reply_emails;
			$notification->n_client_reply->template = stripslashes($notify_client_reply_template);
			
			// [JAS]: Serialize
			$sql = "REPLACE INTO user_notification (user_id,notify_options) ".
				sprintf("VALUES (%d,'%s')",
					$session->vars["login_handler"]->user_id,
					addslashes(serialize($notification))
				);
			
			$cerberus_db->query($sql);
				
			break;
		}
		
	} // end switch 'mode'
} // end form submit

// ***************************************************************************************************************************
// [JAS]: Header Functionality
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);
// ***************************************************************************************************************************

$user_prefs = new CER_USER_PREFS($uid);
$cer_tpl->assign_by_ref('user_prefs',$user_prefs);
$cer_tpl->assign('password_error',$password_error);

// [JAS]: We're giving the user a popup, remove the 'new' flag from messages so it doesn't keep popping up
if($session->vars["login_handler"]->has_new_pm)
{
	$cer_tpl->assign('new_pm',$session->vars["login_handler"]->has_new_pm);
}

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);
$cer_tpl->assign('user_email',$session->vars["login_handler"]->user_email);

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php")
			  );

$page = "my_cerberus.php";
$cer_tpl->assign("page",$page);

$cer_tpl->assign('mode',$mode);

$tabs = new CER_MY_CERBERUS_TABS($mode);
$cer_tpl->assign_by_ref('tabs',$tabs);

$urls['tab_dashboard'] = cer_href("my_cerberus.php?mode=dashboard");
$urls['tab_preferences'] = cer_href("my_cerberus.php?mode=preferences");
$urls['tab_layout'] = cer_href("my_cerberus.php?mode=layout");
$urls['tab_messages'] = cer_href("my_cerberus.php?mode=messages");
$urls['tab_assign'] = cer_href("my_cerberus.php?mode=assign");
$urls['tab_notification'] = cer_href("my_cerberus.php?mode=notification");
$urls['tab_tasks'] = cer_href("my_cerberus.php?mode=tasks");

$cer_tpl->assign_by_ref('urls',$urls);

// [JAS]: Optimize. Only load the specific objects + variables we'll need for each tab.
switch($mode) {
	default:
	case "dashboard":
		$dashboard = new CER_MY_CERBERUS_DASHBOARD($pid,$tid,$project_prefs);
		$cer_tpl->assign_by_ref('dashboard',$dashboard);
		break;
		
	case "preferences":
		// [JAS]: User Timezone Handling
		$timezones = new cer_Timezone();
		$time_now = new cer_DateTime(date("Y-m-d H:i:s"));
		$cer_tpl->assign("time_now",$time_now->getUserDate("%I:%M%p"));
		$cer_tpl->assign_by_ref('timezones',$timezones);
		break;
		
	case "layout":
		$user_layout = &$session->vars["login_handler"]->user_prefs->layout_prefs;
		$cer_tpl->assign_by_ref('user_layout',$user_layout);
		break;
		
	case "notification":
		$notification = new CER_NOTIFICATION($session->vars["login_handler"]->user_id);
		$cer_tpl->assign_by_ref('notification',$notification);
		break;
		
	case "assign":
		$watcher_queues = new CER_MY_CERBERUS_WATCHER();
		$cer_tpl->assign_by_ref('watcher_queues',$watcher_queues);

		$assign_queues = new CER_MY_CERBERUS_ASSIGN_QUEUES();
		$cer_tpl->assign_by_ref('assign_queues',$assign_queues);
		break;
		
	case "tasks":
		// \todo We should probably split the tasks off from the dashboard.
		
		// [JAS]: Project/Task Preferences
		$opt = $session->vars["project_filter_hide_completed_projects"];
		if(isset($opt)) {
			$project_prefs["filter_hide_completed_projects"] = $opt;
		}
		$opt = $session->vars["project_filter_category"];
		if(isset($opt)) {
			$project_prefs["filter_category"] = $opt;
		}
		$opt = $session->vars["project_filter_hide_completed"];
		if(isset($opt)) {
			$project_prefs["filter_hide_completed"] = $opt;
		}
		$opt = $session->vars["project_filter_only_my_tasks"];
		if(isset($opt)) {
			$project_prefs["filter_only_my_tasks"] = $opt;
		}
		$opt = $session->vars["project_sort_by"];
		if(!empty($opt)) {
			$project_prefs["sort_by"] = $opt;
		}
		$opt = $session->vars["project_sort_asc"];
		if(isset($opt)) {
			$project_prefs["sort_asc"] = $opt;
		}
		$opt = $session->vars["project_pm_brief"];
		if(isset($opt)) {
			$project_prefs["pm_brief"] = $opt;
		}

		$dashboard = new CER_MY_CERBERUS_DASHBOARD($pid,$tid,$project_prefs);
		$cer_tpl->assign_by_ref('dashboard',$dashboard);
		
		break;
		
	case "messages":
		$msgs = new CER_MY_CERBERUS_PM_CONTAINER($pm_action,$pm_id);
		if(!empty($pm_uid))	$msgs->pm_to_id = $pm_uid;
		$cer_tpl->assign_by_ref('msgs',$msgs);
		break;
}	

$cer_tpl->display('my_cerberus.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>