<?php

// [TAR]: Main Variables for Configuration
@$module=$_REQUEST["module"];
@$action=$_REQUEST["action"];
@$puid=$_REQUEST["puid"];
@$pslid=$_REQUEST["pslid"];
@$pqid=$_REQUEST["pqid"];
@$pkbid=$_REQUEST["pkbid"];
@$pgid=$_REQUEST["pgid"];
@$pfid=$_REQUEST["pfid"];
@$prid=$_REQUEST["prid"];
@$form_submit=$_REQUEST["form_submit"];

// [JAS]: Global Settings Variables
@$cfg_gui_version = $_REQUEST["cfg_gui_version"];
@$cfg_http_server = $_REQUEST["cfg_http_server"];
@$cfg_cerberus_gui_path = $_REQUEST["cfg_cerberus_gui_path"];
@$cfg_warcheck_secs = $_REQUEST["cfg_warcheck_secs"];
@$cfg_debug_mode = $_REQUEST["cfg_debug_mode"];
@$cfg_mail_delivery = $_REQUEST["cfg_mail_delivery"];
@$cfg_smtp_server = $_REQUEST["cfg_smtp_server"];
@$cfg_who_max_idle_mins = $_REQUEST["cfg_who_max_idle_mins"];
@$cfg_auto_add_cc_reqs = $_REQUEST["cfg_auto_add_cc_reqs"];
@$cfg_enable_panel_stats = $_REQUEST["cfg_enable_panel_stats"];
@$cfg_enable_customer_history = $_REQUEST["cfg_enable_customer_history"];
@$cfg_enable_id_masking = $_REQUEST["cfg_enable_id_masking"];
@$cfg_enable_audit_log = $_REQUEST["cfg_enable_audit_log"];
@$cfg_track_sid_url = $_REQUEST["cfg_track_sid_url"];
@$cfg_satellite_enabled = $_REQUEST["cfg_satellite_enabled"];
@$cfg_xsp_url = $_REQUEST["cfg_xsp_url"];
@$cfg_xsp_login = $_REQUEST["cfg_xsp_login"];
@$cfg_xsp_password = $_REQUEST["cfg_xsp_password"];
@$cfg_overdue_hours = $_REQUEST["cfg_overdue_hours"];
@$cfg_customer_ticket_history_max = $_REQUEST["cfg_customer_ticket_history_max"];
@$cfg_session_lifespan = $_REQUEST["cfg_session_lifespan"];
@$cfg_session_ip_security = $_REQUEST["cfg_session_ip_security"];
@$cfg_sendmail = $_REQUEST["cfg_sendmail"];
@$cfg_search_index_numbers = $_REQUEST["cfg_search_index_numbers"];
@$cfg_time_adjust = $_REQUEST["cfg_time_adjust"];
@$cfg_show_kb = $_REQUEST["cfg_show_kb"];
@$cfg_show_kb_topic_totals = $_REQUEST["cfg_show_kb_topic_totals"];
@$cfg_default_language = $_REQUEST["cfg_default_language"];
@$cfg_kb_editors_enabled = $_REQUEST["cfg_kb_editors_enabled"];
@$cfg_ob_callback = $_REQUEST["cfg_ob_callback"];
@$cfg_bcc_watchers = $_REQUEST["cfg_bcc_watchers"];
@$cfg_watcher_assigned_tech = $_REQUEST["cfg_watcher_assigned_tech"];
@$cfg_watcher_no_system_attach = $_REQUEST["cfg_watcher_no_system_attach"];
@$cfg_not_to_self = $_REQUEST["cfg_not_to_self"];
@$cfg_watcher_from_user = $_REQUEST["cfg_watcher_from_user"];
@$cfg_send_precedence_bulk = $_REQUEST["cfg_send_precedence_bulk"]; // [JXD]
@$cfg_user_only_assign_own_queues = $_REQUEST["cfg_user_only_assign_own_queues"];
@$cfg_auto_delete_spam = $_REQUEST["cfg_auto_delete_spam"];
@$cfg_purge_wait_hrs = $_REQUEST["cfg_purge_wait_hrs"];
@$cfg_parser_secure_enabled = $_REQUEST["cfg_parser_secure_enabled"];
@$cfg_parser_secure_user = $_REQUEST["cfg_parser_secure_user"];
@$cfg_parser_secure_password = $_REQUEST["cfg_parser_secure_password"];
@$cfg_parser_version = $_REQUEST["cfg_parser_version"];
@$cfg_save_message_xml = $_REQUEST["cfg_save_message_xml"];
@$cfg_server_gmt_offset_hrs = $_REQUEST["cfg_server_gmt_offset_hrs"];
@$cfg_helpdesk_title = $_REQUEST["cfg_helpdesk_title"];

// [JAS]: Plugins
@$plugin_enabled = $_REQUEST["plugin_enabled"];

// [JAS]: Public Tool Settings
@$profile_name = $_REQUEST["profile_name"];
@$login_plugin_id = $_REQUEST["login_plugin_id"];
@$pub_qmask = $_REQUEST["pub_qmask"];
@$pub_q = $_REQUEST["pub_q"];
@$pub_qs = $_REQUEST["pub_qs"];
@$pub_q_field_group = $_REQUEST["pub_q_field_group"];
@$pub_company_name = $_REQUEST["pub_company_name"];
@$pub_company_email = $_REQUEST["pub_company_email"];
@$pub_confirmation_subject = $_REQUEST["pub_confirmation_subject"];
@$pub_confirmation_body = $_REQUEST["pub_confirmation_body"];

@$pub_mod_registration = $_REQUEST["pub_mod_registration"];
@$pub_mod_registration_mode = $_REQUEST["pub_mod_registration_mode"];
@$pub_mod_kb = $_REQUEST["pub_mod_kb"];
@$pub_mod_my_account = $_REQUEST["pub_mod_my_account"];
@$pub_mod_open_ticket = $_REQUEST["pub_mod_open_ticket"];
@$pub_mod_open_ticket_locked = $_REQUEST["pub_mod_open_ticket_locked"];
@$pub_mod_track_tickets = $_REQUEST["pub_mod_track_tickets"];
@$pub_mod_announcements = $_REQUEST["pub_mod_announcements"];
@$pub_mod_welcome = $_REQUEST["pub_mod_welcome"];
@$pub_mod_welcome_title = $_REQUEST["pub_mod_welcome_title"];
@$pub_mod_welcome_text = $_REQUEST["pub_mod_welcome_text"];
@$pub_mod_contact = $_REQUEST["pub_mod_contact"];
@$pub_mod_contact_text = $_REQUEST["pub_mod_contact_text"];

// [JAS]: Parser Mail Rule Variables
@$rule_id=$_REQUEST["rule_id"];
@$rule_name=$_REQUEST["rule_name"];
@$rule_order=$_REQUEST["rule_order"];
@$rule_pre_parse=$_REQUEST["rule_pre_parse"];
@$rule_crit_sender=$_REQUEST["rule_crit_sender"];
@$rule_crit_sender_oper=$_REQUEST["rule_crit_sender_oper"];
@$rule_crit_sender_value=stripslashes($_REQUEST["rule_crit_sender_value"]);
@$rule_crit_subject=$_REQUEST["rule_crit_subject"];
@$rule_crit_subject_oper=$_REQUEST["rule_crit_subject_oper"];
@$rule_crit_subject_value=stripslashes($_REQUEST["rule_crit_subject_value"]);
@$rule_crit_queue=$_REQUEST["rule_crit_queue"];
@$rule_crit_queue_oper=$_REQUEST["rule_crit_queue_oper"];
@$rule_crit_queue_value=stripslashes($_REQUEST["rule_crit_queue_value"]);
@$rule_crit_new=$_REQUEST["rule_crit_new"];
@$rule_crit_new_oper=$_REQUEST["rule_crit_new_oper"];
@$rule_crit_new_value=stripslashes($_REQUEST["rule_crit_new_value"]);
@$rule_crit_reopened=$_REQUEST["rule_crit_reopened"];
@$rule_crit_reopened_oper=$_REQUEST["rule_crit_reopened_oper"];
@$rule_crit_reopened_value=stripslashes($_REQUEST["rule_crit_reopened_value"]);
@$rule_crit_attachment_name=$_REQUEST["rule_crit_attachment_name"];
@$rule_crit_attachment_name_oper=$_REQUEST["rule_crit_attachment_name_oper"];
@$rule_crit_attachment_name_value=stripslashes($_REQUEST["rule_crit_attachment_name_value"]);
@$rule_crit_spam_probability=$_REQUEST["rule_crit_spam_probability"];
@$rule_crit_spam_probability_oper=$_REQUEST["rule_crit_spam_probability_oper"];
@$rule_crit_spam_probability_value=stripslashes($_REQUEST["rule_crit_spam_probability_value"]);
@$rule_act_chowner=$_REQUEST["rule_act_chowner"];
@$rule_act_chowner_value=stripslashes($_REQUEST["rule_act_chowner_value"]);
@$rule_act_chqueue=$_REQUEST["rule_act_chqueue"];
@$rule_act_chqueue_value=stripslashes($_REQUEST["rule_act_chqueue_value"]);
@$rule_act_chstatus=$_REQUEST["rule_act_chstatus"];
@$rule_act_chstatus_value=stripslashes($_REQUEST["rule_act_chstatus_value"]);
@$rule_act_chpriority=$_REQUEST["rule_act_chpriority"];
@$rule_act_chpriority_value=stripslashes($_REQUEST["rule_act_chpriority_value"]);
@$rule_act_break=$_REQUEST["rule_act_break"];
@$rule_act_pre_redirect=$_REQUEST["rule_act_pre_redirect"];
@$rule_act_pre_redirect_value=$_REQUEST["rule_act_pre_redirect_value"];
@$rule_act_pre_bounce=$_REQUEST["rule_act_pre_bounce"];
@$rule_act_pre_bounce_value=$_REQUEST["rule_act_pre_bounce_value"];
@$rule_act_pre_ignore=$_REQUEST["rule_act_pre_ignore"];
@$rule_act_pre_no_autoreply=$_REQUEST["rule_act_pre_no_autoreply"];
@$rule_act_pre_no_notification=$_REQUEST["rule_act_pre_no_notification"];

// [TAR]: New User Form Variables
@$user_name=$_REQUEST["user_name"];
@$user_email=$_REQUEST["user_email"];
@$user_login=$_REQUEST["user_login"];
@$user_password_1=$_REQUEST["user_password_1"];
@$user_password_2=$_REQUEST["user_password_2"];
@$user_superuser=$_REQUEST["user_superuser"];
@$qwatch=$_REQUEST["qwatch"];
@$supa=$_REQUEST["supa"];
@$user_group_id=$_REQUEST["user_group_id"];
$user_error_msg = null;

// [JAS]: \todo This could also be moved
//	to a _REQUEST[""] as needed in the code below and not figured out here.
//	See the custom field group code on display.php
@$qlist=$_REQUEST["qlist"];

if(!empty($qlist) && is_array($qlist)) {
	foreach($qlist as $q)
	{
		$var = "qaccess_" . $q;
		$$var = $_REQUEST["qaccess_" . $q];
	}
}

// [TAR]: Delete Users Array
@$uids=$_REQUEST["uids"];

// [JAS]: New/Edit Custom Field Groups
@$field_name=$_REQUEST["field_name"];
@$field_type=$_REQUEST["field_type"];
@$field_not_searchable=$_REQUEST["field_not_searchable"];
@$group_name = $_REQUEST["group_name"];
@$opt_ids = $_REQUEST["opt_ids"];
@$field_ids = $_REQUEST["field_ids"];
@$dropdown_ids = $_REQUEST["dropdown_ids"];
@$group_ids = $_REQUEST["group_ids"];

// [TAR]: Delete Custom Fields
@$fids=$_REQUEST["fids"];

// [JAS]: Custom Field Bindings
$custom_binding = (isset($_REQUEST["custom_binding"])) ? $_REQUEST["custom_binding"] : array();
$custom_binding_val = (isset($_REQUEST["custom_binding_val"])) ? $_REQUEST["custom_binding_val"] : array();

// [TAR]: New/Edit Groups
@$group_name=$_REQUEST["group_name"];
@$is_core_default=$_REQUEST["is_core_default"];
@$group_acl=$_REQUEST["group_acl"];
@$group_acl2=$_REQUEST["group_acl2"];
@$group_acl3=$_REQUEST["group_acl3"];

// [TAR]: Delete Groups Array
// [JAS]: Also used for custom field groups
@$gids=$_REQUEST["gids"];
@$ugid=$_REQUEST["ugid"];

// [JAS]: Custom Field Groups
@$fld_ids = $_REQUEST["fld_ids"];

if(is_array($fld_ids))
foreach($fld_ids as $id) {
	@$name_{$id} = $_REQUEST["name_" . $id];
	@$option_{$id} = $_REQUEST["option_" . $id];
}

// [TAR]: New/Edit Queue Variables
@$queue_name=$_REQUEST["queue_name"];
@$queue_email_display_name=$_REQUEST["queue_email_display_name"];
@$queue_address=$_REQUEST["queue_address"];
@$queue_domain=$_REQUEST["queue_domain"];
@$queue_addresses=$_REQUEST["queue_addresses"];
@$queue_prefix=$_REQUEST["queue_prefix"];
@$queue_send_open=$_REQUEST["queue_send_open"];
@$queue_send_closed=$_REQUEST["queue_send_closed"];
@$queue_response_open=$_REQUEST["queue_response_open"];
@$queue_response_close=$_REQUEST["queue_response_close"];
@$queue_response_gated = $_REQUEST["queue_response_gated"];
@$queue_core_update=$_REQUEST["queue_core_update"];
@$queue_mode = $_REQUEST["queue_mode"];
@$queue_default_schedule = $_REQUEST["queue_default_schedule"];
@$queue_default_response_time = $_REQUEST["queue_default_response_time"];
$queue_addresses_inherit_qid = isset($_REQUEST["queue_addresses_inherit_qid"]) ? $_REQUEST["queue_addresses_inherit_qid"] : 0;

// [JAS]: \todo This could also be moved
//	to a _REQUEST[""] as needed in the code below and not figured out here.
//	See the custom field group code on display.php
@$glist = $_REQUEST["glist"];
if(!empty($glist)) {
	foreach($glist as $g)
	{
		$var  = "gaccess_" . $g;
		$$var = $_REQUEST["gaccess_" . $g];
	}
}

// [TAR]: Delete Queue Array
@$qids=$_REQUEST["qids"];
@$destination_queue=$_REQUEST["destination_queue"];

// [TAR]: New Knowledgebase
@$kbase_cat_name=$_REQUEST["kbase_cat_name"];
@$kbase_cat_parent_id=$_REQUEST["kbase_cat_parent_id"];

// [JAS]: Knowledgebase Article Comment Management
@$comment_ids = $_REQUEST["comment_ids"];
@$comment_action = $_REQUEST["comment_action"];

// [TAR]: Delete Queue Array
@$kbids=$_REQUEST["kbids"];

// [TAR]: Branding Logo
if(isset($_REQUEST["reset_default"])) $reset_default=true;
@$logo_img=$_FILES["logo_img"];

// [TAR]: Email Address
@$address_search_param=$_REQUEST["address_search_param"];
@$ban=$_REQUEST["ban"];
@$ban_emails=$_REQUEST["ban_emails"];
@$all_emails=$_REQUEST["all_emails"];

// [TAR]: Product Key
@$product_key=$_REQUEST["product_key"];

// [TAR]: Bug Report
@$bug_subject=$_REQUEST["bug_subject"];
@$bug_sender=$_REQUEST["bug_sender"];
@$bug_sender_email=$_REQUEST["bug_sender_email"];
@$bug_description=$_REQUEST["bug_description"];

// [TAR]: Give Feedback
@$feedback_subject=$_REQUEST["feedback_subject"];
@$feedback_sender=$_REQUEST["feedback_sender"];
@$feedback_sender_email=$_REQUEST["feedback_sender_email"];
@$feedback_content=$_REQUEST["feedback_content"];

// [JAS]: SLA plans
@$sids = $_REQUEST["sids"];
@$sla_name = $_REQUEST["sla_name"];

// [JAS]: SLA Schedules
@$schedule_name = $_REQUEST["schedule_name"];
@$sun_hrs = $_REQUEST["sun_hrs"];
@$sun_open = $_REQUEST["sun_open"];
@$sun_close = $_REQUEST["sun_close"];
@$mon_hrs = $_REQUEST["mon_hrs"];
@$mon_open = $_REQUEST["mon_open"];
@$mon_close = $_REQUEST["mon_close"];
@$tue_hrs = $_REQUEST["tue_hrs"];
@$tue_open = $_REQUEST["tue_open"];
@$tue_close = $_REQUEST["tue_close"];
@$wed_hrs = $_REQUEST["wed_hrs"];
@$wed_open = $_REQUEST["wed_open"];
@$wed_close = $_REQUEST["wed_close"];
@$thu_hrs = $_REQUEST["thu_hrs"];
@$thu_open = $_REQUEST["thu_open"];
@$thu_close = $_REQUEST["thu_close"];
@$fri_hrs = $_REQUEST["fri_hrs"];
@$fri_open = $_REQUEST["fri_open"];
@$fri_close = $_REQUEST["fri_close"];
@$sat_hrs = $_REQUEST["sat_hrs"];
@$sat_open = $_REQUEST["sat_open"];
@$sat_close = $_REQUEST["sat_close"];

// [JAS]: Queue Catchall Rules
@$catchall_ids = $_REQUEST["catchall_ids"];
@$catchall_name = $_REQUEST["catchall_name"];
@$catchall_pattern = $_REQUEST["catchall_pattern"];
@$catchall_to_qid = $_REQUEST["catchall_to_qid"];
@$catchall_delete_ids = $_REQUEST["catchall_delete_ids"];

// [JAS]: Custom Ticket Statuses
@$statuses_initial = $_REQUEST["statuses_initial"];
@$statuses_ordered = $_REQUEST["statuses_ordered"];
@$statuses_add = $_REQUEST["statuses_add"];

// [JAS]: Mail Rules
$pre_rules_ordered = isset($_REQUEST["pre_rules_ordered"]) ? $_REQUEST["pre_rules_ordered"] : "";
$post_rules_ordered = isset($_REQUEST["post_rules_ordered"]) ? $_REQUEST["post_rules_ordered"] : "";

// [JAS]: Attachment purge
$attachment_purge_ids = isset($_REQUEST["attachment_purge_ids"]) ? $_REQUEST["attachment_purge_ids"] : array();

//*************************************************

// [BGH]: Use the session persistant values for IDs
$qid=0;
if(isset($pqid)) {
	$qid=$pqid;
}

$uid=0;
if(isset($puid)) {
	$uid=$puid;
}

$slid=0;
if(isset($pslid)) {
	$slid=$pslid;
}

$gid=0;
if(isset($pgid)) {
	$gid=$pgid;
}

$kbid=0;
if(isset($pkbid)) {
	$kbid=$pkbid;
}

$rid=0;
if(isset($prid)) {
	$rid=$prid;
}

$fid=0;
if(isset($pfid)) {
	$fid=$pfid;
}

?>
