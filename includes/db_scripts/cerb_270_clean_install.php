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
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|       Ben Halsted         (ben@webgroupmedia.com)         [BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.7.0 Clean Database Install");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20051017");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","");
define("DB_SCRIPT_TYPE","clean");

function cer_init()
{
	init_table_address();
	init_table_chat_agents_to_requests();
	init_table_chat_agents_to_rooms();
	init_table_chat_canned_text();
	init_table_chat_message_parts();
	init_table_chat_messages();
	init_table_chat_transcripts();
	init_table_chat_visitor_chat_requests();
	init_table_chat_visitor_pages();
	init_table_chat_visitors();
	init_table_chat_visitors_to_invites();
	init_table_chat_visitors_to_rooms();
	init_table_company();
	init_table_configuration();
	init_table_country();
	init_table_db_script_hash();
	init_table_department();
	init_table_department_teams();
	init_table_dispatcher_delays();
	init_table_dispatcher_suggestions();
	init_table_email_domains();
	init_table_email_templates();
	init_table_entity_to_field_group();
	init_table_field_group();
	init_table_field_group_bindings();
	init_table_field_group_values();
	init_table_fields_custom();
	init_table_fields_options();
	init_table_gateway_session();
	init_table_heartbeat_event_payload();
	init_table_heartbeat_event_queue();
	init_table_industry();
	init_table_knowledgebase();
	init_table_knowledgebase_categories();
	init_table_knowledgebase_comments();
	init_table_knowledgebase_problem();
	init_table_knowledgebase_ratings();
	init_table_knowledgebase_solution();
	init_table_log();
	init_table_merge_forward();
	init_table_opportunity();
	init_table_plugin();
	init_table_plugin_var();
	init_table_private_messages();
	init_table_product_key();
	init_table_product_key_info();
	init_table_public_gui_fields();
	init_table_public_gui_profiles();
	init_table_public_gui_users();
	init_table_public_gui_users_to_plugin();
	init_table_queue();
	init_table_queue_access();
	init_table_queue_addresses();
	init_table_queue_catchall();
	init_table_queue_group_access();
	init_table_requestor();
	init_table_rule_action();
	init_table_rule_entry();
	init_table_rule_fov();
	init_table_saved_reports();
	init_table_schedule();
	init_table_search_index();
	init_table_search_index_exclude();
	init_table_search_index_kb();
	init_table_search_words();
	init_table_session();
	init_table_session_vars();
	init_table_skill();
	init_table_skill_category();
	init_table_skill_to_agent();
	init_table_skill_to_ticket();
	init_table_sla();
	init_table_sla_to_queue();
	init_table_spam_bayes_index();
	init_table_spam_bayes_stats();
	init_table_stat_browsers();
	init_table_stat_hosts();
	init_table_stat_urls();
	init_table_stats_system();
	init_table_tasks();
	init_table_tasks_notes();
	init_table_tasks_projects();
	init_table_tasks_projects_categories();
	init_table_team();
	init_table_team_members();
	init_table_team_queues();
	init_table_thread();
	init_table_thread_attachments();
	init_table_thread_attachments_parts();
	init_table_thread_attachments_temp();
	init_table_thread_content_part();
	init_table_thread_errors();
	init_table_thread_time_tracking();
	init_table_ticket();
	init_table_ticket_audit_log();
	init_table_ticket_views();
	init_table_trigram();
	init_table_trigram_stats();
	init_table_trigram_to_kb();
	init_table_trigram_to_ticket();
	init_table_trigram_training();
	init_table_user();
	init_table_user_access_levels();
	init_table_user_extended_info();
	init_table_user_layout();
	init_table_user_login_log();
	init_table_user_notification();
	init_table_user_prefs();
	init_table_user_prefs_xml();
	init_table_user_sig();
	init_table_war_check();
	init_table_whos_online();
	
	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}


// ***************************************************************************
// [JAS]: STANDARD CALLBACKS -- Do not edit unless you know what you're doing.
// ***************************************************************************

function table(&$TABLE_DEF)
{
	global $cerberus_db;
	
	$TBL = new CER_DB_TABLE($TABLE_DEF->table_name);
	
	if(!$TBL->check(false))
		create_table($TBL,$TABLE_DEF); // create
	
	verify_table($TBL,$TABLE_DEF); // verify structure
}


function create_table(&$TBL,&$TABLE_DEF)
{
	$TBL->run_sql($TABLE_DEF->create_sql,sprintf("<b>Creating table `%s`</b>",$TABLE_DEF->table_name));
	$TBL->_read_table();
}


function verify_table(&$TBL,&$TABLE_DEF)
{
	$verify_fields = array();
	$verify_indexes = array();
	
	// build list of fields to check for
	foreach($TABLE_DEF->fields as $idx => $fld)
		$verify_fields[$fld->field_name] = DB_FIELD;
		
	// build list of indexes to check for
	foreach($TABLE_DEF->indexes as $id => $idx) 
		$verify_indexes[$idx->index_name] = DB_FIELD;

	// check for fields and indexes
	$warn = $TBL->verify_table($verify_fields,$verify_indexes);
	
	if(count($warn[0])) print_extra_fields($TBL,$warn);
	if(count($warn[1])) print_missing_fields($TBL,$warn);
	if(count($warn[2])) print_extra_indexes($TBL,$warn);
	if(count($warn[3])) print_missing_indexes($TBL,$warn);
	
	// check for field validity
	foreach($TABLE_DEF->fields as $idx => $fld) {
		$TBL->verify_field($fld->field_name,$fld->field_type,$fld->field_null,$fld->field_key,$fld->field_default,$fld->field_extra);
	}
		
	// check for index validity
	foreach($TABLE_DEF->indexes as $id => $idx) {
		$TBL->verify_index($idx->index_name, $idx->index_non_unique, $idx->index_fields);
	}
}

function print_extra_fields(&$tbl,&$warn_fields)
{
	foreach($warn_fields[0] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is not an official database field.<br>",$tbl->table_name,$idx));
}

function print_missing_fields(&$tbl,&$warn_fields)
{
	foreach($warn_fields[1] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is required and does not exist.<br>",$tbl->table_name,$idx));
}

function print_extra_indexes(&$tbl,&$warn_fields)
{
	foreach($warn_fields[2] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is not an official index.<br>",$tbl->table_name,$idx));
}

function print_missing_indexes(&$tbl,&$warn_fields)
{
	foreach($warn_fields[3] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is a needed index and does not exist.<br>",$tbl->table_name,$idx));
}

// [JAS]: If all is well, note that our precursor scripts have run since this is the first time the new
//	DB patcher tracking fields are being used.
function set_precursor_hashes()
{
	global $cerberus_db;
	
	// [JAS]: See if 2.1.0 upgrade has been run
	$TABLE_PM = new CER_DB_TABLE("private_messages");
	if($TABLE_PM->table_exists) {
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('a51f9d89d7d026d9f72efcd5361b47f6',NOW())";
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('67311a2c1b53475ac519d7478993b24f',NOW())";
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('936e61192a253d41b82b43d111960744',NOW())";
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('403754aaa11286ca2ea6774d41421d84',NOW())";
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('31e03d317d07d1d4dd8b556e26cbb4c1',NOW())"; // 2.2.0 to 2.3.0
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('e976ab3d305425502d4e66146da32e1d',NOW())"; // 2.3.0 to 2.3.1
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('9a0ace403f9a41d23b640bf68cd94375',NOW())"; // 2.3.1 to 2.3.2 
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('b81131b5d008d10c09049d667dd6a4c4',NOW())"; // 2.3.2 Clean Install
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('6679ced0eca2b06c27eee38112dd56f0',NOW())"; // 2.3.2 to 2.4.0
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('8b01eecd923a838206d5f653478852b4',NOW())"; // 2.4.0 Clean Install
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('2a46819cce37d18d6cd871db762d8ce6',NOW())"; // 2.4.0 to 2.5.0
		$cerberus_db->query($sql);
        $sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('aeb47eb492c4faaabdf1b1f7980c4387',NOW())"; // 2.5.0 clean
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('d7e151a7ba790eb7f51b7d5795564c37',NOW())"; // 2.5.1 to 2.6.0
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('3df6c171ebaf5b99e8eff5ee7ad3ca2d',NOW())"; // 2.5.x to 2.6.1
		$cerberus_db->query($sql);
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('408cc7b71a99f09f32134ea5db4d4b0c',NOW())"; // 2.6.x to 2.7.0
		$cerberus_db->query($sql);
	}
}

// ***************************************************************************
// `address` table
// ***************************************************************************
function init_table_address()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `address` (".
								"  `address_id` int(10) unsigned NOT NULL auto_increment,".
								"  `address_address` char(128) NOT NULL default '',".
								"  `address_banned` tinyint(4) NOT NULL default '0',".
								"  `public_user_id` bigint(20) unsigned NOT NULL default '0',".
								"  `confirmation_code` char(19) NOT NULL default '',".
								"  PRIMARY KEY  (`address_id`),".
								"  UNIQUE KEY `address_address` (`address_address`),".
								"  KEY `public_user_id` (`public_user_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["address_address"] = new CER_DB_FIELD("address_address","char(128)","","UNI","","");
	$TABLE_DEF->fields["address_banned"] = new CER_DB_FIELD("address_banned","tinyint(4)","","","0","");
	$TABLE_DEF->fields["public_user_id"] = new CER_DB_FIELD("public_user_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["confirmation_code"] = new CER_DB_FIELD("confirmation_code","char(19)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","address_id");
	$TABLE_DEF->indexes["address_address"] = new CER_DB_INDEX("address_address","0","address_address");
	$TABLE_DEF->indexes["public_user_id"] = new CER_DB_INDEX("public_user_id","1","public_user_id");
								
	table($TABLE_DEF);
}

// ***************************************************************************
// `company` table
// ***************************************************************************
function init_table_company()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("company",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `company` (".
								"  `id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `name` char(128) NOT NULL default '',".
								"  `sla_id` bigint(20) NOT NULL default '0',".
								"  `sla_expire_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `company_account_number` char(64) NOT NULL default '',".
								"  `company_mailing_address` char(128) NOT NULL default '',".
								"  `company_mailing_city` char(64) NOT NULL default '',".
								"  `company_mailing_state` char(64) NOT NULL default '',".
								"  `company_mailing_zip` char(64) NOT NULL default '',".
								"  `company_mailing_country_id` int(10) unsigned NOT NULL default '0',".
								"  `company_mailing_country_old` char(64) NOT NULL default '',".
								"  `company_phone` char(32) NOT NULL default '',".
								"  `company_fax` char(32) NOT NULL default '',".
								"  `company_website` char(64) NOT NULL default '',".
								"  `company_email` char(64) NOT NULL default '',".
								"  `import_source` char(10) NOT NULL default '',".
								"  `import_id` char(32) NOT NULL default '',".
								"  `created_date` bigint(20) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`id`),".
								"  KEY `sla_id` (`sla_id`),".
								"  KEY `sla_expire_date` (`sla_expire_date`),".
								"  KEY `company_mailing_country_id` (`company_mailing_country_id`)".
								") TYPE=MyISAM;";
	  	
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","char(128)","","","","");
	$TABLE_DEF->fields["sla_id"] = new CER_DB_FIELD("sla_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["sla_expire_date"] = new CER_DB_FIELD("sla_expire_date","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["company_account_number"] = new CER_DB_FIELD("company_account_number","char(64)","","","","");
	$TABLE_DEF->fields["company_mailing_address"] = new CER_DB_FIELD("company_mailing_address","char(128)","","","","");
	$TABLE_DEF->fields["company_mailing_city"] = new CER_DB_FIELD("company_mailing_city","char(64)","","","","");
	$TABLE_DEF->fields["company_mailing_state"] = new CER_DB_FIELD("company_mailing_state","char(64)","","","","");
	$TABLE_DEF->fields["company_mailing_zip"] = new CER_DB_FIELD("company_mailing_zip","char(64)","","","","");
	$TABLE_DEF->fields["company_mailing_country_id"] = new CER_DB_FIELD("company_mailing_country_id","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["company_mailing_country_old"] = new CER_DB_FIELD("company_mailing_country_old","char(64)","","","","");
	$TABLE_DEF->fields["company_phone"] = new CER_DB_FIELD("company_phone","char(32)","","","","");
	$TABLE_DEF->fields["company_fax"] = new CER_DB_FIELD("company_fax","char(32)","","","","");
	$TABLE_DEF->fields["company_website"] = new CER_DB_FIELD("company_website","char(64)","","","","");
	$TABLE_DEF->fields["company_email"] = new CER_DB_FIELD("company_email","char(64)","","","","");
	$TABLE_DEF->fields["import_source"] = new CER_DB_FIELD("import_source","char(10)","","","","");
	$TABLE_DEF->fields["import_id"] = new CER_DB_FIELD("import_id","char(32)","","","","");
	$TABLE_DEF->fields["created_date"] = new CER_DB_FIELD("created_date","bigint(20) unsigned","","","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
	$TABLE_DEF->indexes["sla_id"] = new CER_DB_INDEX("sla_id","1","sla_id");
	$TABLE_DEF->indexes["sla_expire_date"] = new CER_DB_INDEX("sla_expire_date","1","sla_expire_date");
	$TABLE_DEF->indexes["company_mailing_country_id"] = new CER_DB_INDEX("company_mailing_country_id","1","company_mailing_country_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `configuration` table
// ***************************************************************************
function init_table_configuration()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",false);
		
	$TABLE_DEF->create_sql = "CREATE TABLE `configuration` (".
								"  `cfg_id` bigint(1) NOT NULL default '1',".
								"  `auto_add_cc_reqs` tinyint(4) NOT NULL default '0',".
								"  `bcc_watchers` tinyint(4) NOT NULL default '0',".
								"  `customer_ticket_history_max` tinyint(4) NOT NULL default '0',".
								"  `debug_mode` tinyint(4) NOT NULL default '0',".
								"  `default_language` char(3) NOT NULL default '',".
								"  `enable_audit_log` tinyint(4) NOT NULL default '0',".
								"  `enable_customer_history` tinyint(4) NOT NULL default '0',".
								"  `enable_id_masking` tinyint(4) NOT NULL default '1',".
								"  `enable_panel_stats` tinyint(4) NOT NULL default '0',".
								"  `gui_version` char(16) NOT NULL default '',".
								"  `helpdesk_title` char(250) NOT NULL default '',".
								"  `kb_editors_enabled` tinyint(4) NOT NULL default '0',".
								"  `mail_delivery` char(8) NOT NULL default '',".
								"  `ob_callback` char(64) NOT NULL default '',".
								"  `overdue_hours` int(11) NOT NULL default '0',".
								"  `parser_secure_enabled` tinyint(4) NOT NULL default '0',".
								"  `parser_secure_user` char(64) NOT NULL default '',".
								"  `parser_secure_password` char(64) NOT NULL default '',".
								"  `satellite_enabled` tinyint(4) NOT NULL default '0',".
								"  `search_index_numbers` tinyint(4) NOT NULL default '0',".
								"  `session_ip_security` tinyint(4) NOT NULL default '0',".
								"  `sendmail` tinyint(4) NOT NULL default '0',".
								"  `session_lifespan` int(4) NOT NULL default '720',".
								"  `show_kb` tinyint(4) NOT NULL default '0',".
								"  `show_kb_topic_totals` tinyint(4) NOT NULL default '0',".
								"  `smtp_server` char(64) NOT NULL default '',".
								"  `time_adjust` bigint(20) NOT NULL default '0',".
								"  `track_sid_url` tinyint(4) NOT NULL default '0',".
								"  `warcheck_secs` int(11) NOT NULL default '10',".
								"  `who_max_idle_mins` int(11) NOT NULL default '0',".
								"  `watcher_assigned_tech` tinyint(4) NOT NULL default '0',".
								"  `watcher_from_user` tinyint(4) NOT NULL default '0',".
								"  `not_to_self` tinyint(4) NOT NULL default '0',".
								"  `send_precedence_bulk` tinyint(4) NOT NULL default '0',".
								"  `user_only_assign_own_queues` tinyint(4) NOT NULL default '0',".
								"  `auto_delete_spam` tinyint(4) NOT NULL default '0',".
								"  `purge_wait_hrs` int(11) NOT NULL default '24',".
								"  `watcher_no_system_attach` tinyint(4) NOT NULL default '0',".
								"  `xsp_url` char(255) NOT NULL default '',".
								"  `xsp_login` char(64) NOT NULL default '',".
								"  `xsp_password` char(64) NOT NULL default '',".
								"  `parser_version` char(32) NOT NULL default '',".
								"  `save_message_xml` tinyint(4) NOT NULL default '1',".
								"  `server_gmt_offset_hrs` char(5) NOT NULL DEFAULT '0',".
								"  PRIMARY KEY  (`cfg_id`)".
								") TYPE=MyISAM;"; 
		
	$TABLE_DEF->fields["cfg_id"] = new CER_DB_FIELD("cfg_id","bigint(1)","","PRI","1","");
	$TABLE_DEF->fields["auto_add_cc_reqs"] = new CER_DB_FIELD("auto_add_cc_reqs","tinyint(4)","","","0","");
	$TABLE_DEF->fields["bcc_watchers"] = new CER_DB_FIELD("bcc_watchers","tinyint(4)","","","0","");
	$TABLE_DEF->fields["customer_ticket_history_max"] = new CER_DB_FIELD("customer_ticket_history_max","tinyint(4)","","","0","");
	$TABLE_DEF->fields["debug_mode"] = new CER_DB_FIELD("debug_mode","tinyint(4)","","","0","");
	$TABLE_DEF->fields["default_language"] = new CER_DB_FIELD("default_language","char(3)","","","","");
	$TABLE_DEF->fields["enable_audit_log"] = new CER_DB_FIELD("enable_audit_log","tinyint(4)","","","0","");
	$TABLE_DEF->fields["enable_customer_history"] = new CER_DB_FIELD("enable_customer_history","tinyint(4)","","","0","");
	$TABLE_DEF->fields["enable_id_masking"] = new CER_DB_FIELD("enable_id_masking","tinyint(4)","","","1","");
	$TABLE_DEF->fields["enable_panel_stats"] = new CER_DB_FIELD("enable_panel_stats","tinyint(4)","","","0","");
	$TABLE_DEF->fields["gui_version"] = new CER_DB_FIELD("gui_version","char(16)","","","","");
	$TABLE_DEF->fields["helpdesk_title"] = new CER_DB_FIELD("helpdesk_title","char(250)","","","","");
	$TABLE_DEF->fields["kb_editors_enabled"] = new CER_DB_FIELD("kb_editors_enabled","tinyint(4)","","","0","");
	$TABLE_DEF->fields["mail_delivery"] = new CER_DB_FIELD("mail_delivery","char(8)","","","","");
	$TABLE_DEF->fields["ob_callback"] = new CER_DB_FIELD("ob_callback","char(64)","","","","");
	$TABLE_DEF->fields["overdue_hours"] = new CER_DB_FIELD("overdue_hours","int(11)","","","0","");
	$TABLE_DEF->fields["parser_secure_enabled"] = new CER_DB_FIELD("parser_secure_enabled", "tinyint(4)", "", "", "0", "");
	$TABLE_DEF->fields["parser_secure_user"] = new CER_DB_FIELD("parser_secure_user", "char(64)", "", "", "", "");
	$TABLE_DEF->fields["parser_secure_password"] = new CER_DB_FIELD("parser_secure_password", "char(64)", "", "", "", "");
	$TABLE_DEF->fields["satellite_enabled"] = new CER_DB_FIELD("satellite_enabled","tinyint(4)","","","0","");
	$TABLE_DEF->fields["search_index_numbers"] = new CER_DB_FIELD("search_index_numbers","tinyint(4)","","","0","");
	$TABLE_DEF->fields["session_ip_security"] = new CER_DB_FIELD("session_ip_security","tinyint(4)","","","0","");
	$TABLE_DEF->fields["sendmail"] = new CER_DB_FIELD("sendmail","tinyint(4)","","","0","");
	$TABLE_DEF->fields["session_lifespan"] = new CER_DB_FIELD("session_lifespan","int(4)","","","720","");
	$TABLE_DEF->fields["show_kb"] = new CER_DB_FIELD("show_kb","tinyint(4)","","","0","");
	$TABLE_DEF->fields["show_kb_topic_totals"] = new CER_DB_FIELD("show_kb_topic_totals","tinyint(4)","","","0","");
	$TABLE_DEF->fields["smtp_server"] = new CER_DB_FIELD("smtp_server","char(64)","","","","");
	$TABLE_DEF->fields["time_adjust"] = new CER_DB_FIELD("time_adjust","bigint(20)","","","0","");
	$TABLE_DEF->fields["track_sid_url"] = new CER_DB_FIELD("track_sid_url","tinyint(4)","","","0","");
	$TABLE_DEF->fields["warcheck_secs"] = new CER_DB_FIELD("warcheck_secs","int(11)","","","10","");
	$TABLE_DEF->fields["who_max_idle_mins"] = new CER_DB_FIELD("who_max_idle_mins","int(11)","","","0","");
	$TABLE_DEF->fields["watcher_assigned_tech"] = new CER_DB_FIELD("watcher_assigned_tech","tinyint(4)","","","0","");
	$TABLE_DEF->fields["watcher_from_user"] = new CER_DB_FIELD("watcher_from_user","tinyint(4)","","","0","");
	$TABLE_DEF->fields["watcher_no_system_attach"] = new CER_DB_FIELD("watcher_no_system_attach","tinyint(4)","","","0","");
	$TABLE_DEF->fields["not_to_self"] = new CER_DB_FIELD("not_to_self","tinyint(4)","","","0","");
	$TABLE_DEF->fields["send_precedence_bulk"] = new CER_DB_FIELD("send_precedence_bulk","tinyint(4)","","","0","");
	$TABLE_DEF->fields["user_only_assign_own_queues"] = new CER_DB_FIELD("user_only_assign_own_queues","tinyint(4)","","","0","");
	$TABLE_DEF->fields["auto_delete_spam"] = new CER_DB_FIELD("auto_delete_spam","tinyint(4)","","","0","");
	$TABLE_DEF->fields["purge_wait_hrs"] = new CER_DB_FIELD("purge_wait_hrs","int(11)","","","24","");
	$TABLE_DEF->fields["xsp_url"] = new CER_DB_FIELD("xsp_url","char(255)","","","","");
	$TABLE_DEF->fields["xsp_login"] = new CER_DB_FIELD("xsp_login","char(64)","","","","");
	$TABLE_DEF->fields["xsp_password"] = new CER_DB_FIELD("xsp_password","char(64)","","","","");
	$TABLE_DEF->fields["parser_version"] = new CER_DB_FIELD("parser_version","char(32)","","","","");
	$TABLE_DEF->fields["save_message_xml"] = new CER_DB_FIELD("save_message_xml","tinyint(4)","","","1","");
	$TABLE_DEF->fields["server_gmt_offset_hrs"] = new CER_DB_FIELD("server_gmt_offset_hrs","char(5)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","cfg_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `db_script_hash` table
// ***************************************************************************
function init_table_db_script_hash()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("db_script_hash",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `db_script_hash` (".
		 "`script_md5` CHAR( 40 ) NOT NULL ,".
		 "`run_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',".
		 "UNIQUE ( `script_md5` )".
		");";
	  
	$TABLE_DEF->fields["script_md5"] = new CER_DB_FIELD("script_md5","char(40)","","PRI","","");
	$TABLE_DEF->fields["run_date"] = new CER_DB_FIELD("run_date","datetime","","","0000-00-00 00:00:00","");
	
	$TABLE_DEF->indexes["script_md5"] = new CER_DB_INDEX("script_md5","0","script_md5");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `email_domains` table
// ***************************************************************************
function init_table_email_domains()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("email_domains",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `email_domains` (".
								"  `id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `domain` char(128) NOT NULL default '',".
								"  `company_id` bigint(20) NOT NULL default '0',".
								"  PRIMARY KEY  (`id`),".
								"  KEY `company_id` (`company_id`)".
								") TYPE=MyISAM";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["domain"] = new CER_DB_FIELD("domain","char(128)","","","","");
	$TABLE_DEF->fields["company_id"] = new CER_DB_FIELD("company_id","bigint(20)","","MUL","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
	$TABLE_DEF->indexes["company_id"] = new CER_DB_INDEX("company_id","1","company_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `email_templates` table
// ***************************************************************************
function init_table_email_templates()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("email_templates",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `email_templates` (".
								"  `template_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `template_name` char(128) NOT NULL default '',".
								"  `template_description` char(255) NOT NULL default '',".
								"  `template_text` text,".
								"  `template_created_by` bigint(20) NOT NULL default '0',".
								"  `template_private` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`template_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["template_id"] = new CER_DB_FIELD("template_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["template_name"] = new CER_DB_FIELD("template_name","char(128)","","","","");
	$TABLE_DEF->fields["template_description"] = new CER_DB_FIELD("template_description","char(255)","","","","");
	$TABLE_DEF->fields["template_text"] = new CER_DB_FIELD("template_text","text","","","","");
	$TABLE_DEF->fields["template_created_by"] = new CER_DB_FIELD("template_created_by","bigint(20)","","","0","");
	$TABLE_DEF->fields["template_private"] = new CER_DB_FIELD("template_private","tinyint(4)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","template_id");
	
	table($TABLE_DEF);
}




// ***************************************************************************
// `entity_to_field_group` table
// ***************************************************************************
function init_table_entity_to_field_group()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("entity_to_field_group",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `entity_to_field_group` (".
								"  `group_instance_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `entity_code` char(1) NOT NULL default '',".
								"  `entity_index` bigint(20) unsigned NOT NULL default '0',".
								"  `group_id` bigint(20) unsigned NOT NULL default '0',".
								"  `group_order` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`group_instance_id`),".
								"  KEY `group_id` (`group_id`),".
								"  KEY `entity_code` (`entity_code`),".
								"  KEY `entity_index` (`entity_index`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_instance_id"] = new CER_DB_FIELD("group_instance_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["entity_code"] = new CER_DB_FIELD("entity_code","char(1)","","MUL","","");
	$TABLE_DEF->fields["entity_index"] = new CER_DB_FIELD("entity_index","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["group_order"] = new CER_DB_FIELD("group_order","tinyint(3) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","group_instance_id");
	$TABLE_DEF->indexes["group_id"] = new CER_DB_INDEX("group_id","1","group_id");
	$TABLE_DEF->indexes["entity_code"] = new CER_DB_INDEX("entity_code","1","entity_code");
	$TABLE_DEF->indexes["entity_index"] = new CER_DB_INDEX("entity_index","1","entity_index");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `field_group` table
// ***************************************************************************
function init_table_field_group()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("field_group",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `field_group` (".
								"  `group_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `group_name` char(128) NOT NULL default '',".
								"  PRIMARY KEY  (`group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","char(128)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","group_id");
	
	table($TABLE_DEF);
}


function init_table_field_group_bindings()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("field_group_bindings",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `field_group_bindings` ( ".
		"`entity_code` CHAR( 1 ) NOT NULL ,".
		"`group_template_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',".
		"PRIMARY KEY ( `entity_code` ) ".
		");";

	$TABLE_DEF->fields["entity_code"] = new CER_DB_FIELD("entity_code","char(1)","","PRI","","");
	$TABLE_DEF->fields["group_template_id"] = new CER_DB_FIELD("group_template_id","bigint(20) unsigned","","","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("entity_code"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `field_group_values` table
// ***************************************************************************
function init_table_field_group_values()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("field_group_values",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `field_group_values` (
								  `field_id` bigint(20) unsigned NOT NULL default '0',
								  `field_value` char(255) NOT NULL default '',
								  `group_instance_id` bigint(20) unsigned NOT NULL default '0',
								  `entity_code` char(1) NOT NULL default '',
								  `entity_index` bigint(20) unsigned NOT NULL default '0',
								  `field_group_id` bigint(20) unsigned NOT NULL default '0',
								  KEY `field_id` (`field_id`),
								  KEY `group_instance_id` (`group_instance_id`),
								  KEY `entity_code` (`entity_code`),
								  KEY `entity_index` (`entity_index`),
								  KEY `field_group_id` (`field_group_id`)
								) TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_value"] = new CER_DB_FIELD("field_value","char(255)","","","","");
	$TABLE_DEF->fields["group_instance_id"] = new CER_DB_FIELD("group_instance_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["entity_code"] = new CER_DB_FIELD("entity_code","char(1)","","MUL","","");
	$TABLE_DEF->fields["entity_index"] = new CER_DB_FIELD("entity_index","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_group_id"] = new CER_DB_FIELD("field_group_id","bigint(20) unsigned","","MUL","0","");
	
	$TABLE_DEF->indexes["field_id"] = new CER_DB_INDEX("field_id","1","field_id");
	$TABLE_DEF->indexes["group_instance_id"] = new CER_DB_INDEX("group_instance_id","1","group_instance_id");
	$TABLE_DEF->indexes["entity_code"] = new CER_DB_INDEX("entity_code","1","entity_code");
	$TABLE_DEF->indexes["entity_index"] = new CER_DB_INDEX("entity_index","1","entity_index");
	$TABLE_DEF->indexes["field_group_id"] = new CER_DB_INDEX("field_group_id","1","field_group_id");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `fields_custom` table
// ***************************************************************************
function init_table_fields_custom()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("fields_custom",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `fields_custom` (".
								"  `field_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `field_name` char(64) NOT NULL default '',".
								"  `field_type` char(1) NOT NULL default 'S',".
								"  `field_not_searchable` tinyint(3) unsigned NOT NULL default '0',".
								"  `field_group_id` bigint(20) unsigned NOT NULL default '0',".
								"  `field_order` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`field_id`),".
								"  KEY `field_not_searchable` (`field_not_searchable`),".
								"  KEY `field_group_id` (`field_group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_name"] = new CER_DB_FIELD("field_name","char(64)","","","","");
	$TABLE_DEF->fields["field_type"] = new CER_DB_FIELD("field_type","char(1)","","","S","");
	$TABLE_DEF->fields["field_not_searchable"] = new CER_DB_FIELD("field_not_searchable","tinyint(3) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_group_id"] = new CER_DB_FIELD("field_group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_order"] = new CER_DB_FIELD("field_order","tinyint(3) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","field_id");
	$TABLE_DEF->indexes["field_not_searchable"] = new CER_DB_INDEX("field_not_searchable","1","field_not_searchable");
	$TABLE_DEF->indexes["field_group_id"] = new CER_DB_INDEX("field_group_id","1","field_group_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `fields_options` table
// ***************************************************************************
function init_table_fields_options()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("fields_options",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `fields_options` (".
								"  `field_id` bigint(20) unsigned NOT NULL default '0',".
								"  `option_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `option_value` char(64) NOT NULL default '',".
								"  `option_order` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`option_id`),".
								"  KEY `field_id` (`field_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["option_id"] = new CER_DB_FIELD("option_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["option_value"] = new CER_DB_FIELD("option_value","char(64)","","","","");
	$TABLE_DEF->fields["option_order"] = new CER_DB_FIELD("option_order","tinyint(3) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","option_id");
	$TABLE_DEF->indexes["field_id"] = new CER_DB_INDEX("field_id","1","field_id");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `knowledgebase` table
// ***************************************************************************
function init_table_knowledgebase()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `knowledgebase` (".
								"  `kb_id` int(10) unsigned NOT NULL auto_increment,".
								"  `kb_entry_date` datetime default '0000-00-00 00:00:00',".
								"  `kb_entry_user` int(10) unsigned NOT NULL default '0',".
								"  `kb_category_id` int(10) unsigned NOT NULL default '0',".
								"  `kb_keywords` char(255) NOT NULL default '',".
								"  `kb_public` tinyint(3) unsigned NOT NULL default '0',".
								"  `kb_avg_rating` float unsigned NOT NULL default '0',".
								"  `kb_rating_votes` int(10) unsigned NOT NULL default '0',".
								"  `kb_public_views` bigint(11) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`kb_id`),".
								"  KEY `kb_avg_rating` (`kb_avg_rating`),".
								"  KEY `kb_rating_votes` (`kb_rating_votes`),".
								"  KEY `kb_public_views` (`kb_public_views`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_entry_date"] = new CER_DB_FIELD("kb_entry_date","datetime","YES","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["kb_entry_user"] = new CER_DB_FIELD("kb_entry_user","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_category_id"] = new CER_DB_FIELD("kb_category_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_keywords"] = new CER_DB_FIELD("kb_keywords","char(255)","","","","");
	$TABLE_DEF->fields["kb_public"] = new CER_DB_FIELD("kb_public","tinyint(3) unsigned","","","0","");
	$TABLE_DEF->fields["kb_avg_rating"] = new CER_DB_FIELD("kb_avg_rating","float unsigned","","MUL","0","");
	$TABLE_DEF->fields["kb_rating_votes"] = new CER_DB_FIELD("kb_rating_votes","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["kb_public_views"] = new CER_DB_FIELD("kb_public_views","bigint(11) unsigned","","MUL","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_id");
	$TABLE_DEF->indexes["kb_avg_rating"] = new CER_DB_INDEX("kb_avg_rating","1","kb_avg_rating");
	$TABLE_DEF->indexes["kb_rating_votes"] = new CER_DB_INDEX("kb_rating_votes","1","kb_rating_votes");
	$TABLE_DEF->indexes["kb_public_views"] = new CER_DB_INDEX("kb_public_views","1","kb_public_views");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `knowledgebase_categories` table
// ***************************************************************************
function init_table_knowledgebase_categories()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_categories",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `knowledgebase_categories` (".
								"  `kb_category_id` int(10) unsigned NOT NULL auto_increment,".
								"  `kb_category_name` char(32) NOT NULL default '',".
								"  `kb_category_parent_id` int(10) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`kb_category_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_category_id"] = new CER_DB_FIELD("kb_category_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_category_name"] = new CER_DB_FIELD("kb_category_name","char(32)","","","","");
	$TABLE_DEF->fields["kb_category_parent_id"] = new CER_DB_FIELD("kb_category_parent_id","int(10) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_category_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `knowledgebase_comments` table
// ***************************************************************************
function init_table_knowledgebase_comments()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_comments",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `knowledgebase_comments` (".
								"  `kb_comment_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `kb_article_id` bigint(20) unsigned NOT NULL default '0',".
								"  `kb_comment_approved` tinyint(4) NOT NULL default '0',".
								"  `kb_comment_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `poster_email` char(128) NOT NULL default '',".
								"  `poster_comment` text,".
								"  `poster_ip` char(16) NOT NULL default '0.0.0.0',".
								"  PRIMARY KEY  (`kb_comment_id`),".
								"  KEY `kb_article_id` (`kb_article_id`),".
								"  KEY `kb_comment_approved` (`kb_comment_approved`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_comment_id"] = new CER_DB_FIELD("kb_comment_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["kb_comment_approved"] = new CER_DB_FIELD("kb_comment_approved","tinyint(4)","","MUL","0","");
	$TABLE_DEF->fields["kb_comment_date"] = new CER_DB_FIELD("kb_comment_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["poster_email"] = new CER_DB_FIELD("poster_email","char(128)","","","","");
	$TABLE_DEF->fields["poster_comment"] = new CER_DB_FIELD("poster_comment","text","","","","");
	$TABLE_DEF->fields["poster_ip"] = new CER_DB_FIELD("poster_ip","char(16)","","","0.0.0.0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_comment_id");
	$TABLE_DEF->indexes["kb_article_id"] = new CER_DB_INDEX("kb_article_id","1","kb_article_id");
	$TABLE_DEF->indexes["kb_comment_approved"] = new CER_DB_INDEX("kb_comment_approved","1","kb_comment_approved");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `knowledgebase_problem` table
// ***************************************************************************
function init_table_knowledgebase_problem()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_problem",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `knowledgebase_problem` (".
								"  `kb_problem_id` int(10) unsigned NOT NULL auto_increment,".
								"  `kb_id` int(10) unsigned NOT NULL default '0',".
								"  `kb_problem_summary` char(128) NOT NULL default '',".
								"  `kb_problem_text` text,".
								"  `kb_problem_text_is_html` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`kb_problem_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_problem_id"] = new CER_DB_FIELD("kb_problem_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_id"]  = new CER_DB_FIELD("kb_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_problem_summary"] = new CER_DB_FIELD("kb_problem_summary","char(128)","","","","");
	$TABLE_DEF->fields["kb_problem_text"] = new CER_DB_FIELD("kb_problem_text","text","","","","");
	$TABLE_DEF->fields["kb_problem_text_is_html"] = new CER_DB_FIELD("kb_problem_text_is_html","tinyint(4)","","","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_problem_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `knowledgebase_ratings` table
// ***************************************************************************
function init_table_knowledgebase_ratings()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_ratings",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `knowledgebase_ratings` (".
								"  `rating_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `kb_article_id` bigint(20) NOT NULL default '0',".
								"  `ip_addr` char(16) NOT NULL default '',".
								"  `rating` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`rating_id`),".
								"  UNIQUE KEY `kb_article_id` (`kb_article_id`,`ip_addr`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["rating_id"] = new CER_DB_FIELD("rating_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["ip_addr"] = new CER_DB_FIELD("ip_addr","char(16)","","","","");
	$TABLE_DEF->fields["rating"] = new CER_DB_FIELD("rating","tinyint(4)","","","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","rating_id");
	$TABLE_DEF->indexes["kb_article_id"] = new CER_DB_INDEX("kb_article_id","0",array("kb_article_id","ip_addr"));
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `knowledgebase_solution` table
// ***************************************************************************
function init_table_knowledgebase_solution()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_solution",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `knowledgebase_solution` (".
								"  `kb_solution_id` int(10) unsigned NOT NULL auto_increment,".
								"  `kb_id` int(10) unsigned NOT NULL default '0',".
								"  `kb_solution_text` text,".
								"  `kb_solution_text_is_html` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`kb_solution_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["kb_solution_id"] = new CER_DB_FIELD("kb_solution_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_solution_text"] = new CER_DB_FIELD("kb_solution_text","text","","","","");
	$TABLE_DEF->fields["kb_solution_text_is_html"] = new CER_DB_FIELD("kb_solution_text_is_html","tinyint(4)","","","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_solution_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `log` table
// ***************************************************************************
function init_table_log()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("log",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `log` (".
								"  `log_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `message` text,".
								"  `log_date` timestamp(14) NOT NULL,".
								"  PRIMARY KEY  (`log_id`) ".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["log_id"] = new CER_DB_FIELD("log_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["message"] = new CER_DB_FIELD("message","text","","","","");
	$TABLE_DEF->fields["log_date"] = new CER_DB_FIELD("log_date","timestamp(14)","YES","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","log_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `merge_forward` table
// ***************************************************************************
function init_table_merge_forward()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("merge_forward",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `merge_forward` (".
								"  `from_ticket` bigint(20) unsigned NOT NULL default '0',".
								"  `to_ticket` bigint(20) unsigned NOT NULL default '0',".
								"  UNIQUE KEY `merge_pair` (`from_ticket`,`to_ticket`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["from_ticket"] = new CER_DB_FIELD("from_ticket","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["to_ticket"] = new CER_DB_FIELD("to_ticket","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["merge_pair"] = new CER_DB_INDEX("merge_pair","0",array("from_ticket","to_ticket"));
	
	table($TABLE_DEF);
}


function init_table_plugin()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("plugin",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `plugin` ( ".
	 	"`plugin_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ".
	 	"`plugin_name` CHAR( 128 ) NOT NULL , ".
	 	"`plugin_type` CHAR( 32 ) NOT NULL , ".
	 	"`plugin_class` CHAR( 128 ) NOT NULL , ".
	 	"`plugin_file` CHAR( 128 ) NOT NULL , ".
	 	"`plugin_enabled` TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL , ".
	 	"PRIMARY KEY ( `plugin_id` ) , ".
	 	"INDEX ( `plugin_type` ), ".
	 	"INDEX ( `plugin_enabled` ) ".
		");";

	$TABLE_DEF->fields["plugin_id"] = new CER_DB_FIELD("plugin_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["plugin_name"] = new CER_DB_FIELD("plugin_name","char(128)","","","","");
	$TABLE_DEF->fields["plugin_type"] = new CER_DB_FIELD("plugin_type","char(32)","","MUL","","");
	$TABLE_DEF->fields["plugin_class"] = new CER_DB_FIELD("plugin_class","char(128)","","","","");
	$TABLE_DEF->fields["plugin_file"] = new CER_DB_FIELD("plugin_file","char(128)","","","","");
	$TABLE_DEF->fields["plugin_enabled"] = new CER_DB_FIELD("plugin_enabled","tinyint(3) unsigned","","MUL","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("plugin_id"));	
	$TABLE_DEF->indexes["plugin_type"] = new CER_DB_INDEX("plugin_type","1",array("plugin_type"));	
	$TABLE_DEF->indexes["plugin_enabled"] = new CER_DB_INDEX("plugin_enabled","1",array("plugin_enabled"));	
	
	table($TABLE_DEF);
}

function init_table_plugin_var()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("plugin_var",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE plugin_var ( ".
		"plugin_id bigint(20) unsigned NOT NULL default '0', ".
		"var_name char(128) NOT NULL default '', ".
		"var_value char(128) NOT NULL default '', ".
		"UNIQUE KEY plugin_var (plugin_id,var_name), ".
		"KEY plugin_id (plugin_id), ".
		"KEY var_name (var_name) ".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["plugin_id"] = new CER_DB_FIELD("plugin_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["var_name"] = new CER_DB_FIELD("var_name","char(128)","","PRI","","");
	$TABLE_DEF->fields["var_value"] = new CER_DB_FIELD("var_value","char(128)","","","","");

	$TABLE_DEF->indexes["plugin_id"] = new CER_DB_INDEX("plugin_id","1",array("plugin_id"));	
	$TABLE_DEF->indexes["var_name"] = new CER_DB_INDEX("var_name","1",array("var_name"));	
	$TABLE_DEF->indexes["plugin_var"] = new CER_DB_INDEX("plugin_var","0",array("plugin_id","var_name"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `private_messages` table
// ***************************************************************************
function init_table_private_messages()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("private_messages",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `private_messages` (".
			"  `pm_id` bigint(20) unsigned NOT NULL auto_increment,".
			"  `pm_to_user_id` bigint(20) NOT NULL default '0',".
			"  `pm_from_user_id` bigint(20) NOT NULL default '0',".
			"  `pm_subject` char(128) NOT NULL default '',".
			"  `pm_date` datetime NOT NULL default '0000-00-00 00:00:00',".
			"  `pm_folder_id` bigint(20) NOT NULL default '0',".
			"  `pm_message` text,".
			"  `pm_marked_read` tinyint(4) NOT NULL default '0',".
			"  `pm_read_receipt` tinyint(4) NOT NULL default '0',".
			"  `pm_notified` tinyint(4) NOT NULL default '0',".
			"  PRIMARY KEY  (`pm_id`)".
			") TYPE=MyISAM;";
	  
			
	$TABLE_DEF->fields["pm_id"] = new CER_DB_FIELD("pm_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["pm_to_user_id"] = new CER_DB_FIELD("pm_to_user_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["pm_from_user_id"] = new CER_DB_FIELD("pm_from_user_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["pm_subject"] = new CER_DB_FIELD("pm_subject","char(128)","","","","");
	$TABLE_DEF->fields["pm_date"] = new CER_DB_FIELD("pm_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["pm_folder_id"] = new CER_DB_FIELD("pm_folder_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["pm_message"] = new CER_DB_FIELD("pm_message","text","","","","");
	$TABLE_DEF->fields["pm_marked_read"] = new CER_DB_FIELD("pm_marked_read","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pm_read_receipt"] = new CER_DB_FIELD("pm_read_receipt","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pm_notified"] = new CER_DB_FIELD("pm_notified","tinyint(4)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","pm_id");			
			
	table($TABLE_DEF);
}



// ***************************************************************************
// `product_key` table
// ***************************************************************************
function init_table_product_key()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("product_key",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `product_key` (".
  		"`key_file` text,".
  		"`key_date` timestamp(14) NOT NULL".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["key_file"] = new CER_DB_FIELD("key_file","text","","","","");
	$TABLE_DEF->fields["key_date"] = new CER_DB_FIELD("key_date","timestamp(14)","YES","","","");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `product_key_info` table
// ***************************************************************************
function init_table_product_key_info()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("product_key_info",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `product_key_info` (".
		"`key_domains` tinytext,".
  		"`key_maxqueues` int(11) NOT NULL default '0',".
  		"`key_tagline` char(255) NOT NULL default '',".
  		"`key_lastupdate` timestamp(14) NOT NULL,".
  		"`key_type` tinyint(4) NOT NULL default '0',".
  		"`key_expiration` datetime NOT NULL default '0000-00-00 00:00:00'".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["key_domains"] = new CER_DB_FIELD("key_domains","tinytext","","","","");
	$TABLE_DEF->fields["key_maxqueues"] = new CER_DB_FIELD("key_maxqueues","int(11)","","","0","");
	$TABLE_DEF->fields["key_tagline"] = new CER_DB_FIELD("key_tagline","char(255)","","","","");
	$TABLE_DEF->fields["key_lastupdate"] = new CER_DB_FIELD("key_lastupdate","timestamp(14)","YES","","","");
	$TABLE_DEF->fields["key_type"] = new CER_DB_FIELD("key_type","tinyint(4)","","","0","");
	$TABLE_DEF->fields["key_expiration"] = new CER_DB_FIELD("key_expiration","datetime","","","0000-00-00 00:00:00","");
		
	table($TABLE_DEF);
}

// ***************************************************************************
// `public_gui_fields` table
// ***************************************************************************
function init_table_public_gui_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_fields",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `public_gui_fields` (".
								"  `group_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `group_name` char(64) NOT NULL default '',".
								"  `group_fields` text,".
								"  PRIMARY KEY  (`group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","char(64)","","","","");
	$TABLE_DEF->fields["group_fields"] = new CER_DB_FIELD("group_fields","text","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","group_id");	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `public_gui_profiles` table
// ***************************************************************************
function init_table_public_gui_profiles()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `public_gui_profiles` (".
								"  `profile_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `profile_name` char(64) NOT NULL default '',".
								"  `pub_company_name` char(128) NOT NULL default '',".
								"  `pub_company_email` char(128) NOT NULL default '',".
								"  `pub_queues` text,".
								"  `pub_confirmation_subject` char(128) NOT NULL default '',".
								"  `pub_confirmation_body` text,".
								"  `pub_mod_registration` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_registration_mode` char(12) NOT NULL default '',".
								"  `pub_mod_kb` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_my_account` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_open_ticket` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_open_ticket_locked` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_track_tickets` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_announcements` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_welcome` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_welcome_title` char(64) NOT NULL default '',".
								"  `pub_mod_welcome_text` text,".
								"  `pub_mod_contact` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_contact_text` text,".
								"  `login_plugin_id` bigint(20) unsigned DEFAULT 0 NOT NULL,".
								"  PRIMARY KEY  (`profile_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["profile_id"] = new CER_DB_FIELD("profile_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["profile_name"] = new CER_DB_FIELD("profile_name","char(64)","","","","");
	$TABLE_DEF->fields["pub_company_name"] = new CER_DB_FIELD("pub_company_name","char(128)","","","","");
	$TABLE_DEF->fields["pub_company_email"] = new CER_DB_FIELD("pub_company_email","char(128)","","","","");
	$TABLE_DEF->fields["pub_queues"] = new CER_DB_FIELD("pub_queues","text","","","","");
	$TABLE_DEF->fields["pub_confirmation_subject"] = new CER_DB_FIELD("pub_confirmation_subject","char(128)","","","","");
	$TABLE_DEF->fields["pub_confirmation_body"] = new CER_DB_FIELD("pub_confirmation_body","text","","","","");
	$TABLE_DEF->fields["pub_mod_registration"] = new CER_DB_FIELD("pub_mod_registration","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_registration_mode"] = new CER_DB_FIELD("pub_mod_registration_mode","char(12)","","","","");
	$TABLE_DEF->fields["pub_mod_kb"] = new CER_DB_FIELD("pub_mod_kb","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_my_account"] = new CER_DB_FIELD("pub_mod_my_account","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_open_ticket"] = new CER_DB_FIELD("pub_mod_open_ticket","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_open_ticket_locked"] = new CER_DB_FIELD("pub_mod_open_ticket_locked","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_track_tickets"] = new CER_DB_FIELD("pub_mod_track_tickets","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_announcements"] = new CER_DB_FIELD("pub_mod_announcements","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_welcome"] = new CER_DB_FIELD("pub_mod_welcome","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_welcome_title"] = new CER_DB_FIELD("pub_mod_welcome_title","char(64)","","","","");
	$TABLE_DEF->fields["pub_mod_welcome_text"] = new CER_DB_FIELD("pub_mod_welcome_text","text","","","","");
	$TABLE_DEF->fields["pub_mod_contact"] = new CER_DB_FIELD("pub_mod_contact","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_contact_text"] = new CER_DB_FIELD("pub_mod_contact_text","text","","","","");
	$TABLE_DEF->fields["login_plugin_id"] = new CER_DB_FIELD("login_plugin_id","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","profile_id");	

	table($TABLE_DEF);
}



// ***************************************************************************
// `public_gui_users` table
// ***************************************************************************
function init_table_public_gui_users()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `public_gui_users` (".
								"  `public_user_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `name_salutation` enum('', 'Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.') NOT NULL default '',".
								"  `name_first` char(16) NOT NULL default '',".
								"  `name_last` char(32) NOT NULL default '',".
								"  `mailing_address` char(128) NOT NULL default '',".
								"  `mailing_city` char(64) NOT NULL default '',".
								"  `mailing_state` char(64) NOT NULL default '',".
								"  `mailing_zip` char(32) NOT NULL default '',".
								"  `mailing_country_id` int(10) unsigned NOT NULL default '0',".
								"  `mailing_country_old` char(64) NOT NULL default '',".
								"  `phone_work` char(32) NOT NULL default '',".
								"  `phone_home` char(32) NOT NULL default '',".
								"  `phone_mobile` char(32) NOT NULL default '',".
								"  `phone_fax` char(32) NOT NULL default '',".
								"  `password` char(32) NOT NULL default '',".
								"  `company_id` bigint(20) unsigned NOT NULL default '0',".
								"  `public_access_level` tinyint(3) unsigned NOT NULL default '0',".
								"  `import_source` char(10) NOT NULL default '',".
								"  `import_id` char(32) NOT NULL default '',".
								"  `created_date` bigint(20) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`public_user_id`),".
								"  KEY `public_access_level` (`public_access_level`),".
								"  KEY `company_id` (`company_id`),".
								"  KEY `mailing_country_id` (`mailing_country_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["public_user_id"] = new CER_DB_FIELD("public_user_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name_salutation"] = new CER_DB_FIELD("name_salutation","enum('', 'Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.')","","","","");
	$TABLE_DEF->fields["name_first"] = new CER_DB_FIELD("name_first","char(16)","","","","");
	$TABLE_DEF->fields["name_last"] = new CER_DB_FIELD("name_last","char(32)","","","","");
	$TABLE_DEF->fields["mailing_address"] = new CER_DB_FIELD("mailing_address","char(128)","","","","");
	$TABLE_DEF->fields["mailing_city"] = new CER_DB_FIELD("mailing_city","char(64)","","","","");
	$TABLE_DEF->fields["mailing_state"] = new CER_DB_FIELD("mailing_state","char(64)","","","","");
	$TABLE_DEF->fields["mailing_zip"] = new CER_DB_FIELD("mailing_zip","char(32)","","","","");
	$TABLE_DEF->fields["mailing_country_id"] = new CER_DB_FIELD("mailing_country_id","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["mailing_country_old"] = new CER_DB_FIELD("mailing_country_old","char(64)","","","","");
	$TABLE_DEF->fields["phone_work"] = new CER_DB_FIELD("phone_work","char(32)","","","","");
	$TABLE_DEF->fields["phone_home"] = new CER_DB_FIELD("phone_home","char(32)","","","","");
	$TABLE_DEF->fields["phone_mobile"] = new CER_DB_FIELD("phone_mobile","char(32)","","","","");
	$TABLE_DEF->fields["phone_fax"] = new CER_DB_FIELD("phone_fax","char(32)","","","","");
	$TABLE_DEF->fields["password"] = new CER_DB_FIELD("password","char(32)","","","","");
	$TABLE_DEF->fields["company_id"] = new CER_DB_FIELD("company_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["public_access_level"] = new CER_DB_FIELD("public_access_level","tinyint(3) unsigned","","MUL","0","");
	$TABLE_DEF->fields["import_source"] = new CER_DB_FIELD("import_source","char(10)","","","","");
	$TABLE_DEF->fields["import_id"] = new CER_DB_FIELD("import_id","char(32)","","","","");
	$TABLE_DEF->fields["created_date"] = new CER_DB_FIELD("created_date","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","public_user_id");
	$TABLE_DEF->indexes["company_id"] = new CER_DB_INDEX("company_id","1","company_id");
	$TABLE_DEF->indexes["public_access_level"] = new CER_DB_INDEX("public_access_level","1","public_access_level");
	$TABLE_DEF->indexes["mailing_country_id"] = new CER_DB_INDEX("mailing_country_id","1","mailing_country_id");
	
	table($TABLE_DEF);
}

function init_table_public_gui_users_to_plugin()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("public_gui_users_to_plugin",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE public_gui_users_to_plugin (".
		"public_user_id bigint(20) unsigned NOT NULL default '0',".
		"plugin_id bigint(20) unsigned NOT NULL default '0',".
		"remote_user_id bigint(20) unsigned NOT NULL default '0',".
		"UNIQUE KEY local_plugin_remote (public_user_id,plugin_id,remote_user_id),".
		"KEY public_user_id (public_user_id),".
		"KEY remote_user_id (remote_user_id),".
		"KEY plugin_id (plugin_id) ".
		");";
		
	$TABLE_DEF->fields["public_user_id"] = new CER_DB_FIELD("public_user_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["plugin_id"] = new CER_DB_FIELD("plugin_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["remote_user_id"] = new CER_DB_FIELD("remote_user_id","bigint(20) unsigned","","PRI","0","");

	$TABLE_DEF->indexes["local_plugin_remote"] = new CER_DB_INDEX("local_plugin_remote","0",array("public_user_id","plugin_id","remote_user_id"));	
	$TABLE_DEF->indexes["public_user_id"] = new CER_DB_INDEX("public_user_id","1",array("public_user_id"));	
	$TABLE_DEF->indexes["remote_user_id"] = new CER_DB_INDEX("remote_user_id","1",array("remote_user_id"));	
	$TABLE_DEF->indexes["plugin_id"] = new CER_DB_INDEX("plugin_id","1",array("plugin_id"));	
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `queue` table
// ***************************************************************************
function init_table_queue()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `queue` (".
								"  `queue_id` int(11) NOT NULL auto_increment,".
								"  `queue_name` char(32) NOT NULL default '',".
								"  `queue_prefix` char(32) NOT NULL default '',".
								"  `queue_response_open` text,".
								"  `queue_response_close` text,".
								"  `queue_send_open` tinyint(4) NOT NULL default '0',".
								"  `queue_send_closed` tinyint(4) NOT NULL default '0',".
								"  `queue_core_update` tinyint(4) NOT NULL default '0',".
								"  `queue_email_display_name` char(64) NOT NULL default '',".
								"  `queue_mode` tinyint(4) NOT NULL default '0',".
								"  `queue_response_gated` text,".
								"  `queue_default_schedule` bigint(20) NOT NULL default '0',".
								"  `queue_default_response_time` int(11) NOT NULL default '0',".
								"  `queue_addresses_inherit_qid` bigint(20) NOT NULL default '0',".
								"  PRIMARY KEY  (`queue_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","PRI","","auto_increment");
	$TABLE_DEF->fields["queue_name"] = new CER_DB_FIELD("queue_name","char(32)","","","","");
	$TABLE_DEF->fields["queue_prefix"] = new CER_DB_FIELD("queue_prefix","char(32)","","","","");
	$TABLE_DEF->fields["queue_response_open"] = new CER_DB_FIELD("queue_response_open","text","","","","");
	$TABLE_DEF->fields["queue_response_close"] = new CER_DB_FIELD("queue_response_close","text","","","","");
	$TABLE_DEF->fields["queue_send_open"] = new CER_DB_FIELD("queue_send_open","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_send_closed"] = new CER_DB_FIELD("queue_send_closed","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_core_update"] = new CER_DB_FIELD("queue_core_update","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_email_display_name"] = new CER_DB_FIELD("queue_email_display_name","char(64)","","","","");
	$TABLE_DEF->fields["queue_mode"] = new CER_DB_FIELD("queue_mode","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_response_gated"] = new CER_DB_FIELD("queue_response_gated","text","","","","");
	$TABLE_DEF->fields["queue_default_schedule"] = new CER_DB_FIELD("queue_default_schedule","bigint(20)","","","0","");
	$TABLE_DEF->fields["queue_default_response_time"] = new CER_DB_FIELD("queue_default_response_time","int(11)","","","0","");
	$TABLE_DEF->fields["queue_addresses_inherit_qid"] = new CER_DB_FIELD("queue_addresses_inherit_qid","bigint(20)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","queue_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `queue_access` table
// ***************************************************************************
function init_table_queue_access()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue_access",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `queue_access` (".
								"  `queue_id` int(11) NOT NULL default '0',".
								"  `user_id` int(11) NOT NULL default '0',".
								"  `queue_access` enum('read','write','none','') NOT NULL default '',".
								"  `queue_watch` tinyint(1) NOT NULL default '0',".
								"  KEY `queue_id` (`queue_id`),".
								"  KEY `user_id` (`user_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["queue_access"] = new CER_DB_FIELD("queue_access","enum('read','write','none','')","","","","");
	$TABLE_DEF->fields["queue_watch"] = new CER_DB_FIELD("queue_watch","tinyint(1)","","","0","");

	$TABLE_DEF->indexes["queue_id"] = new CER_DB_INDEX("queue_id","1","queue_id");
	$TABLE_DEF->indexes["user_id"] = new CER_DB_INDEX("user_id","1","user_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `queue_addresses` table
// ***************************************************************************
function init_table_queue_addresses()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue_addresses",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `queue_addresses` (".
								"  `queue_addresses_id` int(11) NOT NULL auto_increment,".
								"  `queue_id` int(11) NOT NULL default '0',".
								"  `queue_address` char(128) NOT NULL default '',".
								"  `queue_domain` char(128) NOT NULL default '',".
								"  PRIMARY KEY  (`queue_addresses_id`),".
								"  UNIQUE KEY `address_unique` (`queue_address`,`queue_domain`),".
								"  KEY `queue_id` (`queue_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_addresses_id"] = new CER_DB_FIELD("queue_addresses_id","int(11)","","PRI","","auto_increment");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["queue_address"] = new CER_DB_FIELD("queue_address","char(128)","","MUL","","");
	$TABLE_DEF->fields["queue_domain"] = new CER_DB_FIELD("queue_domain","char(128)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","queue_addresses_id");
	$TABLE_DEF->indexes["address_unique"] = new CER_DB_INDEX("address_unique","0",array("queue_address","queue_domain"));
	$TABLE_DEF->indexes["queue_id"] = new CER_DB_INDEX("queue_id","1","queue_id");
	
	table($TABLE_DEF);
}

function init_table_queue_catchall()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("queue_catchall",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `queue_catchall` (".
		"`catchall_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`catchall_name` CHAR( 64 ) NOT NULL ,".
		"`catchall_pattern` CHAR( 128 ) NOT NULL ,".
		"`catchall_to_qid` BIGINT UNSIGNED DEFAULT 0 NOT NULL ,".
		"`catchall_order` INT UNSIGNED DEFAULT '0' NOT NULL ,".
		"PRIMARY KEY ( `catchall_id` ) ".
		");";
		
	$TABLE_DEF->fields["catchall_id"] = new CER_DB_FIELD("catchall_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["catchall_name"] = new CER_DB_FIELD("catchall_name","char(64)","","","","");
	$TABLE_DEF->fields["catchall_pattern"] = new CER_DB_FIELD("catchall_pattern","char(128)","","","","");
	$TABLE_DEF->fields["catchall_to_qid"] = new CER_DB_FIELD("catchall_to_qid","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["catchall_order"] = new CER_DB_FIELD("catchall_order","int(10) unsigned","","","0","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("catchall_id"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `queue_group_access` table
// ***************************************************************************
function init_table_queue_group_access()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue_group_access",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `queue_group_access` (".
								"  `queue_id` bigint(20) unsigned NOT NULL default '0',".
								"  `group_id` bigint(20) unsigned NOT NULL default '0',".
								"  `queue_access` enum('read','write','none','') NOT NULL default '',".
								"  KEY `queue_id` (`queue_id`),".
								"  KEY `group_id` (`group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["queue_access"] = new CER_DB_FIELD("queue_access","enum('read','write','none','')","","","","");
	
	$TABLE_DEF->indexes["queue_id"] = new CER_DB_INDEX("queue_id","1","queue_id");
	$TABLE_DEF->indexes["group_id"] = new CER_DB_INDEX("group_id","1","group_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `requestor` table
// ***************************************************************************
function init_table_requestor()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("requestor",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `requestor` (".
								"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
								"  `address_id` int(10) unsigned default '0',".
								"  `suppress` tinyint(4) NOT NULL default '0',".
								"  UNIQUE KEY `ticket_and_address` (`ticket_id`,`address_id`),".
								"  KEY `ticket_id` (`ticket_id`),".
								"  KEY `address_id` (`address_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","int(10) unsigned","YES","MUL","0","");
	$TABLE_DEF->fields["suppress"] = new CER_DB_FIELD("suppress","tinyint(4)","","","0","");

	$TABLE_DEF->indexes["ticket_and_address"] = new CER_DB_INDEX("ticket_and_address","0",array("ticket_id","address_id"));
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
	$TABLE_DEF->indexes["address_id"] = new CER_DB_INDEX("address_id","1","address_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `rule_action` table
// ***************************************************************************
function init_table_rule_action()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("rule_action",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `rule_action` (".
								"  `rule_id` bigint(20) unsigned NOT NULL default '0',".
								"  `action_type` int(10) unsigned NOT NULL default '0',".
								"  `action_value` text,".
								"  KEY `rule_id` (`rule_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["action_type"] = new CER_DB_FIELD("action_type","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["action_value"] = new CER_DB_FIELD("action_value","text","","","","");
	
	$TABLE_DEF->indexes["rule_id"] = new CER_DB_INDEX("rule_id","1","rule_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `rule_entry` table
// ***************************************************************************
function init_table_rule_entry()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("rule_entry",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `rule_entry` (".
								"  `rule_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `rule_name` char(128) NOT NULL default '',".
								"  `rule_order` int(10) unsigned NOT NULL default '0',".
								"  `rule_pre_parse` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`rule_id`),".
								"  KEY `rule_pre_parse` (`rule_pre_parse`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["rule_name"] = new CER_DB_FIELD("rule_name","char(128)","","","","");
	$TABLE_DEF->fields["rule_order"] = new CER_DB_FIELD("rule_order","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["rule_pre_parse"] = new CER_DB_FIELD("rule_pre_parse","tinyint(3) unsigned","","MUL","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","rule_id");
	$TABLE_DEF->indexes["rule_pre_parse"] = new CER_DB_INDEX("rule_pre_parse","1","rule_pre_parse");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `rule_fov` table
// ***************************************************************************
function init_table_rule_fov()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("rule_fov",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `rule_fov` (".
								"  `rule_id` bigint(20) unsigned NOT NULL default '0',".
								"  `fov_field` int(10) unsigned NOT NULL default '0',".
								"  `fov_oper` int(10) unsigned NOT NULL default '0',".
								"  `fov_value` char(128) NOT NULL default '',".
								"  KEY `rule_id` (`rule_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["fov_field"] = new CER_DB_FIELD("fov_field","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["fov_oper"] = new CER_DB_FIELD("fov_oper","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["fov_value"] = new CER_DB_FIELD("fov_value","char(128)","","","","");
	
	$TABLE_DEF->indexes["rule_id"] = new CER_DB_INDEX("rule_id","1","rule_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `rule_fov` table
// ***************************************************************************
function init_table_schedule()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("schedule",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `schedule` (".
								"  `schedule_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `schedule_name` char(64) NOT NULL default '',".
								"  `sun_hrs` char(10) NOT NULL default '',".
								"  `sun_open` char(5) NOT NULL default '00:00',".
								"  `sun_close` char(5) NOT NULL default '00:00',".
								"  `mon_hrs` char(10) NOT NULL default '',".
								"  `mon_open` char(5) NOT NULL default '00:00',".
								"  `mon_close` char(5) NOT NULL default '00:00',".
								"  `tue_hrs` char(10) NOT NULL default '',".
								"  `tue_open` char(5) NOT NULL default '00:00',".
								"  `tue_close` char(5) NOT NULL default '00:00',".
								"  `wed_hrs` char(10) NOT NULL default '',".
								"  `wed_open` char(5) NOT NULL default '00:00',".
								"  `wed_close` char(5) NOT NULL default '00:00',".
								"  `thu_hrs` char(10) NOT NULL default '',".
								"  `thu_open` char(5) NOT NULL default '00:00',".
								"  `thu_close` char(5) NOT NULL default '00:00',".
								"  `fri_hrs` char(10) NOT NULL default '',".
								"  `fri_open` char(5) NOT NULL default '00:00',".
								"  `fri_close` char(5) NOT NULL default '00:00',".
								"  `sat_hrs` char(10) NOT NULL default '',".
								"  `sat_open` char(5) NOT NULL default '00:00',".
								"  `sat_close` char(5) NOT NULL default '00:00',".
								"  PRIMARY KEY  (`schedule_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["schedule_id"] = new CER_DB_FIELD("schedule_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["schedule_name"] = new CER_DB_FIELD("schedule_name","char(64)","","","","");
	$TABLE_DEF->fields["sun_hrs"] = new CER_DB_FIELD("sun_hrs","char(10)","","","","");
	$TABLE_DEF->fields["sun_open"] = new CER_DB_FIELD("sun_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["sun_close"] = new CER_DB_FIELD("sun_close","char(5)","","","00:00","");
	$TABLE_DEF->fields["mon_hrs"] = new CER_DB_FIELD("mon_hrs","char(10)","","","","");
	$TABLE_DEF->fields["mon_open"] = new CER_DB_FIELD("mon_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["mon_close"] = new CER_DB_FIELD("mon_close","char(5)","","","00:00","");
	$TABLE_DEF->fields["tue_hrs"] = new CER_DB_FIELD("tue_hrs","char(10)","","","","");
	$TABLE_DEF->fields["tue_open"] = new CER_DB_FIELD("tue_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["tue_close"] = new CER_DB_FIELD("tue_close","char(5)","","","00:00","");
	$TABLE_DEF->fields["wed_hrs"] = new CER_DB_FIELD("wed_hrs","char(10)","","","","");
	$TABLE_DEF->fields["wed_open"] = new CER_DB_FIELD("wed_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["wed_close"] = new CER_DB_FIELD("wed_close","char(5)","","","00:00","");
	$TABLE_DEF->fields["thu_hrs"] = new CER_DB_FIELD("thu_hrs","char(10)","","","","");
	$TABLE_DEF->fields["thu_open"] = new CER_DB_FIELD("thu_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["thu_close"] = new CER_DB_FIELD("thu_close","char(5)","","","00:00","");
	$TABLE_DEF->fields["fri_hrs"] = new CER_DB_FIELD("fri_hrs","char(10)","","","","");
	$TABLE_DEF->fields["fri_open"] = new CER_DB_FIELD("fri_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["fri_close"] = new CER_DB_FIELD("fri_close","char(5)","","","00:00","");
	$TABLE_DEF->fields["sat_hrs"] = new CER_DB_FIELD("sat_hrs","char(10)","","","","");
	$TABLE_DEF->fields["sat_open"] = new CER_DB_FIELD("sat_open","char(5)","","","00:00","");
	$TABLE_DEF->fields["sat_close"] = new CER_DB_FIELD("sat_close","char(5)","","","00:00","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","schedule_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `search_index` table
// ***************************************************************************
function init_table_search_index()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `search_index` (".
								"  `word_id` bigint(20) NOT NULL default '0',".
								"  `ticket_id` bigint(20) NOT NULL default '0',".
								"  `in_subject` tinyint(4) NOT NULL default '0',".
								"  `in_first_thread` tinyint(3) unsigned NOT NULL default '0',".
								"  UNIQUE KEY `word_id` (`word_id`,`ticket_id`),".
								"  KEY `ticket_id` (`ticket_id`),".
								"  KEY `in_subject` (`in_subject`),".
								"  KEY `in_first_thread` (`in_first_thread`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["in_subject"] = new CER_DB_FIELD("in_subject","tinyint(4)","","MUL","0","");
	$TABLE_DEF->fields["in_first_thread"] = new CER_DB_FIELD("in_first_thread","tinyint(3) unsigned","","MUL","0","");
	
	$TABLE_DEF->indexes["word_id"] = new CER_DB_INDEX("word_id","0",array("word_id","ticket_id"));
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
	$TABLE_DEF->indexes["in_subject"] = new CER_DB_INDEX("in_subject","1","in_subject");
	$TABLE_DEF->indexes["in_first_thread"] = new CER_DB_INDEX("in_first_thread","1","in_first_thread");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `search_index_exclude` table
// ***************************************************************************
function init_table_search_index_exclude()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index_exclude",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `search_index_exclude` (".
								"  `exclude_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `exclude_word` char(25) NOT NULL default '',".
								"  PRIMARY KEY  (`exclude_id`),".
								"  UNIQUE KEY `exclude_word` (`exclude_word`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["exclude_id"] = new CER_DB_FIELD("exclude_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["exclude_word"] = new CER_DB_FIELD("exclude_word","char(25)","","UNI","","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","exclude_id");
	$TABLE_DEF->indexes["exclude_word"] = new CER_DB_INDEX("exclude_word","0","exclude_word");
	
	table($TABLE_DEF);

}

// ***************************************************************************
// `search_index_kb` table
// ***************************************************************************
function init_table_search_index_kb()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index_kb",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `search_index_kb` (".
								"  `word_id` bigint(20) NOT NULL default '0',".
								"  `kb_article_id` bigint(20) NOT NULL default '0',".
								"  UNIQUE KEY `word_id` (`word_id`,`kb_article_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20)","","PRI","0","");
	
	$TABLE_DEF->indexes["word_id"] = new CER_DB_INDEX("word_id","0",array("word_id","kb_article_id"));
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `search_words` table
// ***************************************************************************
function init_table_search_words()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_words",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `search_words` (".
								"  `word_id` bigint(20) NOT NULL auto_increment,".
								"  `word` char(45) NOT NULL default '',".
								"  PRIMARY KEY  (`word_id`),".
								"  UNIQUE KEY `word` (`word`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["word"] = new CER_DB_FIELD("word","char(45)","","UNI","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","word_id");
	$TABLE_DEF->indexes["word"] = new CER_DB_INDEX("word","0","word");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `session` table
// ***************************************************************************
function init_table_session()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("session",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `session` (".
								"  `s_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `session_id` char(32) NOT NULL default '',".
								"  `session_ip` char(16) NOT NULL default '',".
								"  `session_timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  PRIMARY KEY  (`s_id`),".
								"  UNIQUE KEY `session_id` (`session_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["s_id"] = new CER_DB_FIELD("s_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["session_id"] = new CER_DB_FIELD("session_id","char(32)","","UNI","","");
	$TABLE_DEF->fields["session_ip"] = new CER_DB_FIELD("session_ip","char(16)","","","","");
	$TABLE_DEF->fields["session_timestamp"] = new CER_DB_FIELD("session_timestamp","datetime","","","0000-00-00 00:00:00","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","s_id");
	$TABLE_DEF->indexes["session_id"] = new CER_DB_INDEX("session_id","0","session_id");
		
	table($TABLE_DEF);
}

// ***************************************************************************
// `session_vars` table
// ***************************************************************************
function init_table_session_vars()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("session_vars",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `session_vars` (".
								"  `s_id` bigint(20) unsigned NOT NULL default '0',".
								"  `var_name` char(64) NOT NULL default '',".
								"  `var_val` text,".
								"  KEY `s_id` (`s_id`),".
								"  KEY `var_name` (`var_name`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["s_id"] = new CER_DB_FIELD("s_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["var_name"] = new CER_DB_FIELD("var_name","char(64)","","MUL","","");
	$TABLE_DEF->fields["var_val"] = new CER_DB_FIELD("var_val","text","","","","");
	
	$TABLE_DEF->indexes["s_id"] = new CER_DB_INDEX("s_id","1","s_id");
	$TABLE_DEF->indexes["var_name"] = new CER_DB_INDEX("var_name","1","var_name");	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `sla` table
// ***************************************************************************
function init_table_sla()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("sla",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `sla` (".
								"  `id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `name` char(64) NOT NULL default '',".
								"  PRIMARY KEY  (`id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","char(64)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `sla_to_queue` table
// ***************************************************************************
function init_table_sla_to_queue()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("sla_to_queue",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `sla_to_queue` (".
								"  `sla_id` bigint(20) unsigned NOT NULL default '0',".
								"  `queue_id` bigint(20) unsigned NOT NULL default '0',".
								"  `schedule_id` bigint(20) unsigned NOT NULL default '0',".
								"  `response_time` int(11) NOT NULL default '0',".
								"  UNIQUE KEY `sla_id` (`sla_id`,`queue_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["sla_id"] = new CER_DB_FIELD("sla_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["schedule_id"] = new CER_DB_FIELD("schedule_id","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["response_time"] = new CER_DB_FIELD("response_time","int(11)","","","0","");
	
	$TABLE_DEF->indexes["sla_id"] = new CER_DB_INDEX("sla_id","0",array("sla_id","queue_id"));
	
	table($TABLE_DEF);
}
// ***************************************************************************
// `spam_bayes_index` table
// ***************************************************************************
function init_table_spam_bayes_index()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("spam_bayes_index",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `spam_bayes_index` (".
								"  `word_id` bigint(20) unsigned NOT NULL default '0',".
								"  `in_spam` bigint(20) unsigned NOT NULL default '0',".
								"  `in_nonspam` bigint(20) unsigned NOT NULL default '0',".
								"  UNIQUE KEY `word_id` (`word_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["in_spam"] = new CER_DB_FIELD("in_spam","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["in_nonspam"] = new CER_DB_FIELD("in_nonspam","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["word_id"] = new CER_DB_INDEX("word_id","0","word_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `spam_bayes_stats` table
// ***************************************************************************
function init_table_spam_bayes_stats()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("spam_bayes_stats",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `spam_bayes_stats` (".
								"  `num_spam` bigint(20) unsigned NOT NULL default '0',".
								"  `num_nonspam` bigint(20) unsigned NOT NULL default '0'".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["num_spam"] = new CER_DB_FIELD("num_spam","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["num_nonspam"] = new CER_DB_FIELD("num_nonspam","bigint(20) unsigned","","","0","");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `stats_system` table
// ***************************************************************************
function init_table_stats_system()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("stats_system",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `stats_system` (".
								"  `stat_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `stat_date` date NOT NULL default '0000-00-00',".
								"  `stat_type` int(10) unsigned NOT NULL default '0',".
								"  `stat_extra` bigint(20) unsigned NOT NULL default '0',".
								"  `stat_count` bigint(20) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`stat_id`),".
								"  KEY `stat_date` (`stat_date`),".
								"  KEY `stat_type` (`stat_type`),".
								"  KEY `stat_extra` (`stat_extra`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["stat_id"] = new CER_DB_FIELD("stat_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["stat_date"] = new CER_DB_FIELD("stat_date","date","","MUL","0000-00-00","");
	$TABLE_DEF->fields["stat_type"] = new CER_DB_FIELD("stat_type","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["stat_extra"] = new CER_DB_FIELD("stat_extra","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["stat_count"] = new CER_DB_FIELD("stat_count","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","stat_id");
	$TABLE_DEF->indexes["stat_date"] = new CER_DB_INDEX("stat_date","1","stat_date");
	$TABLE_DEF->indexes["stat_type"] = new CER_DB_INDEX("stat_type","1","stat_type");
	$TABLE_DEF->indexes["stat_extra"] = new CER_DB_INDEX("stat_extra","1","stat_extra");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `tasks` table
// ***************************************************************************
function init_table_tasks()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("tasks",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `tasks` (".
								"  `task_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `task_summary` char(255) NOT NULL default '',".
								"  `task_description` text,".
								"  `task_progress` tinyint(4) NOT NULL default '0',".
								"  `task_created_uid` bigint(20) NOT NULL default '0',".
								"  `task_assigned_uid` bigint(20) NOT NULL default '0',".
								"  `task_priority` tinyint(4) NOT NULL default '0',".
								"  `task_parent_id` bigint(20) NOT NULL default '0',".
								"  `task_project_id` bigint(20) NOT NULL default '0',".
								"  `task_project_category_id` bigint(20) NOT NULL default '0',".
								"  `task_classification` tinyint(4) NOT NULL default '0',".
								"  `task_created_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `task_updated_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `task_due_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `task_reminder_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `task_reminder_sent` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`task_id`)".
								") TYPE=MyISAM;";
 
	$TABLE_DEF->fields["task_id"] = new CER_DB_FIELD("task_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["task_summary"] = new CER_DB_FIELD("task_summary","char(255)","","","","");
	$TABLE_DEF->fields["task_description"] = new CER_DB_FIELD("task_description","text","","","","");
	$TABLE_DEF->fields["task_progress"] = new CER_DB_FIELD("task_progress","tinyint(4)","","","0","");
	$TABLE_DEF->fields["task_created_uid"] = new CER_DB_FIELD("task_created_uid","bigint(20)","","","0","");
	$TABLE_DEF->fields["task_assigned_uid"] = new CER_DB_FIELD("task_assigned_uid","bigint(20)","","","0","");
	$TABLE_DEF->fields["task_priority"] = new CER_DB_FIELD("task_priority","tinyint(4)","","","0","");
	$TABLE_DEF->fields["task_parent_id"] = new CER_DB_FIELD("task_parent_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["task_project_id"] = new CER_DB_FIELD("task_project_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["task_project_category_id"] = new CER_DB_FIELD("task_project_category_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["task_classification"] = new CER_DB_FIELD("task_classification","tinyint(4)","","","0","");
	$TABLE_DEF->fields["task_created_date"] = new CER_DB_FIELD("task_created_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["task_updated_date"] = new CER_DB_FIELD("task_updated_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["task_due_date"] = new CER_DB_FIELD("task_due_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["task_reminder_date"] = new CER_DB_FIELD("task_reminder_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["task_reminder_sent"] = new CER_DB_FIELD("task_reminder_sent","tinyint(4)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","task_id");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `tasks_notes` table
// ***************************************************************************
function init_table_tasks_notes()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("tasks_notes",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `tasks_notes` (".
		"note_id bigint(20) unsigned NOT NULL auto_increment,".
		"task_id bigint(20) unsigned NOT NULL default '0',".
		"note_poster_uid bigint(20) unsigned NOT NULL default '0',".
		"note_timestamp datetime NOT NULL default '0000-00-00 00:00:00',".
		"note_text text,".
		"PRIMARY KEY  (note_id),".
		"KEY note_poster_uid (note_poster_uid),".
		"KEY task_id (task_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["note_id"] = new CER_DB_FIELD("note_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["task_id"] = new CER_DB_FIELD("task_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["note_poster_uid"] = new CER_DB_FIELD("note_poster_uid","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["note_timestamp"] = new CER_DB_FIELD("note_timestamp","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["note_text"] = new CER_DB_FIELD("note_text","text","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","note_id");
	$TABLE_DEF->indexes["note_poster_uid"] = new CER_DB_INDEX("note_poster_uid","1","note_poster_uid");
	$TABLE_DEF->indexes["task_id"] = new CER_DB_INDEX("task_id","1","task_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `tasks_projects` table
// ***************************************************************************
function init_table_tasks_projects()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("tasks_projects",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `tasks_projects` (".
								"  `project_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `project_name` char(128) NOT NULL default '',".
								"  `project_manager_uid` bigint(20) NOT NULL default '0',".
								"  `project_acl` text,".
								"  PRIMARY KEY  (`project_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["project_id"] = new CER_DB_FIELD("project_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["project_name"] = new CER_DB_FIELD("project_name","char(128)","","","","");
	$TABLE_DEF->fields["project_manager_uid"] = new CER_DB_FIELD("project_manager_uid","bigint(20)","","","0","");
	$TABLE_DEF->fields["project_acl"] = new CER_DB_FIELD("project_acl","text","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","project_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `tasks_projects_categories` table
// ***************************************************************************
function init_table_tasks_projects_categories()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("tasks_projects_categories",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `tasks_projects_categories` (".
								"  `category_id` bigint(20) NOT NULL auto_increment,".
								"  `project_id` bigint(20) NOT NULL default '0',".
								"  `category_name` char(128) NOT NULL default '',".
								"  PRIMARY KEY  (`category_id`),".
								"  KEY `project_id` (`project_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["category_id"] = new CER_DB_FIELD("category_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["project_id"] = new CER_DB_FIELD("project_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["category_name"] = new CER_DB_FIELD("category_name","char(128)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","category_id");
	$TABLE_DEF->indexes["project_id"] = new CER_DB_INDEX("project_id","1","project_id");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `thread` table
// ***************************************************************************
function init_table_thread()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread",false);

	$TABLE_DEF->create_sql = "CREATE TABLE `thread` (".
								"  `ticket_id` int(11) NOT NULL default '0',".
								"  `thread_id` int(10) unsigned NOT NULL auto_increment,".
								"  `thread_message_id` char(255) NOT NULL default '',".
								"  `thread_inreplyto_id` int(11) NOT NULL default '0',".
								"  `thread_address_id` int(11) NOT NULL default '0',".
								"  `thread_type` enum('email','comment','forward') NOT NULL default 'email',".
								"  `thread_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `thread_time_worked` smallint(6) NOT NULL default '0',".
								"  `thread_subject` char(128) default '',".
								"  `thread_to` char(64) default '' NOT NULL,".
								"  `thread_cc` char(64) default '',".
								"  `thread_replyto` char(64) default '',".
								"  `is_agent_message` tinyint(4) NOT NULL default '0',".
								"  `thread_received` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  PRIMARY KEY  (`thread_id`),".
								"  KEY `ticket_sender_id` (`thread_address_id`),".
								"  KEY `ticket_id` (`ticket_id`),".
								"  KEY `thread_id` (`thread_id`),".
								"  KEY `thread_message_id` (`thread_message_id`),".
								"  KEY `thread_address_id` (`thread_address_id`),".
								"  KEY `thread_inreplyto_id` (`thread_inreplyto_id`),".
								"  KEY `is_agent_message` (`is_agent_message`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["thread_message_id"] = new CER_DB_FIELD("thread_message_id","char(255)","","MUL","","");
	$TABLE_DEF->fields["thread_inreplyto_id"] = new CER_DB_FIELD("thread_inreplyto_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["thread_address_id"] = new CER_DB_FIELD("thread_address_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["thread_type"] = new CER_DB_FIELD("thread_type","enum('email','comment','forward')","","","email","");
	$TABLE_DEF->fields["thread_date"] = new CER_DB_FIELD("thread_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["thread_time_worked"] = new CER_DB_FIELD("thread_time_worked","smallint(6)","","","0","");
	$TABLE_DEF->fields["thread_subject"] = new CER_DB_FIELD("thread_subject","char(128)","YES","","","");
	$TABLE_DEF->fields["thread_to"] = new CER_DB_FIELD("thread_to","char(64)","","","","");
	$TABLE_DEF->fields["thread_cc"] = new CER_DB_FIELD("thread_cc","char(64)","YES","","","");
	$TABLE_DEF->fields["thread_replyto"] = new CER_DB_FIELD("thread_replyto","char(64)","YES","","","");
	$TABLE_DEF->fields["is_agent_message"] = new CER_DB_FIELD("is_agent_message","tinyint(4)","","MUL","0","");
	$TABLE_DEF->fields["thread_received"] = new CER_DB_FIELD("thread_received","datetime","","","0000-00-00 00:00:00","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","thread_id");
	$TABLE_DEF->indexes["ticket_sender_id"] = new CER_DB_INDEX("ticket_sender_id","1","thread_address_id");
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
	$TABLE_DEF->indexes["thread_id"] = new CER_DB_INDEX("thread_id","1","thread_id");
	$TABLE_DEF->indexes["thread_message_id"] = new CER_DB_INDEX("thread_message_id","1","thread_message_id");
	$TABLE_DEF->indexes["thread_address_id"] = new CER_DB_INDEX("thread_address_id","1","thread_address_id");
	$TABLE_DEF->indexes["thread_inreplyto_id"] = new CER_DB_INDEX("thread_inreplyto_id","1","thread_inreplyto_id");
	$TABLE_DEF->indexes["is_agent_message"] = new CER_DB_INDEX("is_agent_message","1","is_agent_message");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `thread_attachments` table
// ***************************************************************************
function init_table_thread_attachments()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_attachments",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_attachments` (".
								"  `thread_id` bigint(20) unsigned NOT NULL default '0',".
								"  `file_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `file_name` char(128) NOT NULL default '',".
								"  `file_size` bigint(11) NOT NULL default '0',".
								"  PRIMARY KEY  (`file_id`),".
								"  KEY `thread_id` (`thread_id`),".
								"  KEY `search` (`thread_id`,`file_name`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["file_name"] = new CER_DB_FIELD("file_name","char(128)","","","","");
	$TABLE_DEF->fields["file_size"] = new CER_DB_FIELD("file_size","bigint(11)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","file_id");
	$TABLE_DEF->indexes["thread_id"] = new CER_DB_INDEX("thread_id","1","thread_id");
	$TABLE_DEF->indexes["search"] = new CER_DB_INDEX("search","1",array("thread_id","file_name"));
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `thread_attachments_parts` table
// ***************************************************************************
function init_table_thread_attachments_parts()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_attachments_parts",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_attachments_parts` (".
								"  `part_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `file_id` bigint(20) unsigned NOT NULL default '0',".
								"  `part_content` mediumblob,".
								"  PRIMARY KEY  (`part_id`),".
								"  KEY `file_id` (`file_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["part_id"] = new CER_DB_FIELD("part_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["part_content"] = new CER_DB_FIELD("part_content","mediumblob","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","part_id");
	$TABLE_DEF->indexes["file_id"] = new CER_DB_INDEX("file_id","1","file_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `thread_attachments_temp` table
// ***************************************************************************
function init_table_thread_attachments_temp()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_attachments_temp",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_attachments_temp` (".
				"  `file_id` bigint(20) unsigned NOT NULL auto_increment,".
				"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
				"  `user_id` int(10) unsigned NOT NULL default '0',".
				"  `timestamp` bigint(20) NOT NULL default '0',".
				"  `temp_name` char(255) NOT NULL default '',".
				"  `file_name` char(255) NOT NULL default '',".
				"  `size` bigint(20) NOT NULL default '0',".
				"  `browser_mimetype` char(255) NOT NULL default '',".
				"  PRIMARY KEY  (`file_id`),".
				"  KEY `ticket_id` (`ticket_id`,`user_id`,`file_id`)".
				") TYPE=MyISAM;";

	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","bigint(20)","","","0","");
	$TABLE_DEF->fields["temp_name"] = new CER_DB_FIELD("temp_name","char(255)","","","","");
	$TABLE_DEF->fields["file_name"] = new CER_DB_FIELD("file_name","char(255)","","","","");
	$TABLE_DEF->fields["size"] = new CER_DB_FIELD("size","bigint(20)","","","0","");
	$TABLE_DEF->fields["browser_mimetype"] = new CER_DB_FIELD("browser_mimetype","char(255)","","","","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","file_id");
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1",array("ticket_id","user_id","file_id"));
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `thread_content_part` table
// ***************************************************************************
function init_table_thread_content_part()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_content_part",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_content_part` (".
								"  `content_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `thread_id` bigint(20) unsigned NOT NULL default '0',".
								"  `thread_content_part` char(255) NOT NULL default '',".
								"  PRIMARY KEY  (`content_id`),".
								"  KEY `thread_id` (`thread_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["content_id"] = new CER_DB_FIELD("content_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["thread_content_part"] = new CER_DB_FIELD("thread_content_part","char(255)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","content_id");
	$TABLE_DEF->indexes["thread_id"] = new CER_DB_INDEX("thread_id","1","thread_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `thread_errors` table
// ***************************************************************************
function init_table_thread_errors()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_errors",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_errors` (".
								"  `error_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
								"  `thread_id` bigint(20) unsigned NOT NULL default '0',".
								"  `error_msg` text,".
								"  PRIMARY KEY  (`error_id`),".
								"  KEY `ticket_id` (`ticket_id`),".
								"  KEY `thread_id` (`thread_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["error_id"] = new CER_DB_FIELD("error_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["error_msg"] = new CER_DB_FIELD("error_msg","text","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","error_id");
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
	$TABLE_DEF->indexes["thread_id"] = new CER_DB_INDEX("thread_id","1","thread_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `ticket` table
// ***************************************************************************
function init_table_ticket()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket` (".
								"  `ticket_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `ticket_subject` char(128) NOT NULL default '',".
								"  `ticket_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `ticket_priority` tinyint(4) NOT NULL default '0',".
								"  `ticket_status` enum('new','awaiting-reply','customer-reply','bounced','resolved','dead') NOT NULL default 'new',".
								"  `last_update_date` timestamp(14) NOT NULL,".
								"  `ticket_assigned_to_id` int(10) unsigned NOT NULL default '0',".
								"  `ticket_queue_id` int(10) unsigned NOT NULL default '0',".
								"  `queue_addresses_id` int(11) NOT NULL default '0',".
								"  `ticket_reopenings` smallint(6) NOT NULL default '0',".
								"  `min_thread_id` bigint(21) default NULL,".
								"  `max_thread_id` bigint(21) default NULL,".
								"  `ticket_spam_trained` tinyint(1) NOT NULL default '0',".
								"  `ticket_mask` char(32) NOT NULL default '',".
								"  `ticket_spam_probability` float unsigned NOT NULL default '0',".
								"  `ticket_due` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `ticket_time_worked` int(11) NOT NULL default '0',".
								"  `last_reply_by_agent` tinyint(3) unsigned NOT NULL default '0',".
								"  `skill_count` mediumint(9) NOT NULL default '0',".
								"  PRIMARY KEY  (`ticket_id`),".
								"  KEY `ticket_queue_id` (`ticket_queue_id`),".
								"  KEY `min_thread_id` (`min_thread_id`),".
								"  KEY `max_thread_id` (`max_thread_id`),".
								"  KEY `ticket_mask` (`ticket_mask`),".
								"  KEY `ticket_spam_probability` (`ticket_spam_probability`),".
								"  KEY `ticket_due` (`ticket_due`),".
								"  KEY `ticket_date` (`ticket_date`),".
								"  KEY `ticket_status` (`ticket_status`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_subject"] = new CER_DB_FIELD("ticket_subject","char(128)","","","","");
	$TABLE_DEF->fields["ticket_date"] = new CER_DB_FIELD("ticket_date","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["ticket_priority"] = new CER_DB_FIELD("ticket_priority","tinyint(4)","","","0","");
	$TABLE_DEF->fields["ticket_status"] = new CER_DB_FIELD("ticket_status","enum('new','awaiting-reply','customer-reply','bounced','resolved','dead')","","MUL","new","");
	$TABLE_DEF->fields["last_update_date"] = new CER_DB_FIELD("last_update_date","timestamp(14)","YES","","","");
	$TABLE_DEF->fields["ticket_assigned_to_id"] = new CER_DB_FIELD("ticket_assigned_to_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["ticket_queue_id"] = new CER_DB_FIELD("ticket_queue_id","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["queue_addresses_id"] = new CER_DB_FIELD("queue_addresses_id","int(11)","","","0","");
	$TABLE_DEF->fields["ticket_reopenings"] = new CER_DB_FIELD("ticket_reopenings","smallint(6)","","","0","");
	$TABLE_DEF->fields["min_thread_id"] = new CER_DB_FIELD("min_thread_id","bigint(21)","YES","MUL","","");
	$TABLE_DEF->fields["max_thread_id"] = new CER_DB_FIELD("max_thread_id","bigint(21)","YES","MUL","","");
	$TABLE_DEF->fields["ticket_spam_trained"] = new CER_DB_FIELD("ticket_spam_trained","tinyint(1)","","","0","");
	$TABLE_DEF->fields["ticket_mask"] = new CER_DB_FIELD("ticket_mask","char(32)","","MUL","","");
	$TABLE_DEF->fields["ticket_spam_probability"] = new CER_DB_FIELD("ticket_spam_probability","float unsigned","","MUL","0","");
	$TABLE_DEF->fields["ticket_due"] = new CER_DB_FIELD("ticket_due","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["ticket_time_worked"] = new CER_DB_FIELD("ticket_time_worked","int(11)","","","0","");
	$TABLE_DEF->fields["last_reply_by_agent"] = new CER_DB_FIELD("last_reply_by_agent","tinyint(3) unsigned","","","0","");
	$TABLE_DEF->fields["skill_count"] = new CER_DB_FIELD("skill_count","mediumint(9)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","ticket_id");
	$TABLE_DEF->indexes["ticket_queue_id"] = new CER_DB_INDEX("ticket_queue_id","1","ticket_queue_id");
	$TABLE_DEF->indexes["min_thread_id"] = new CER_DB_INDEX("min_thread_id","1","min_thread_id");
	$TABLE_DEF->indexes["max_thread_id"] = new CER_DB_INDEX("max_thread_id","1","max_thread_id");
	$TABLE_DEF->indexes["ticket_mask"] = new CER_DB_INDEX("ticket_mask","1","ticket_mask");
	$TABLE_DEF->indexes["ticket_spam_probability"] = new CER_DB_INDEX("ticket_spam_probability","1","ticket_spam_probability");
	$TABLE_DEF->indexes["ticket_due"] = new CER_DB_INDEX("ticket_due","1","ticket_due");
	$TABLE_DEF->indexes["ticket_date"] = new CER_DB_INDEX("ticket_date","1","ticket_date");
	$TABLE_DEF->indexes["ticket_status"] = new CER_DB_INDEX("ticket_status","1","ticket_status");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `ticket_audit_log` table
// ***************************************************************************
function init_table_ticket_audit_log()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_audit_log",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_audit_log` (".
								"  `audit_id` bigint(20) NOT NULL auto_increment,".
								"  `ticket_id` bigint(20) NOT NULL default '0',".
								"  `epoch` bigint(20) NOT NULL default '0',".
								"  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `user_id` bigint(20) NOT NULL default '0',".
								"  `action` int(11) NOT NULL default '0',".
								"  `action_value` char(128) NOT NULL default '',".
								"  PRIMARY KEY  (`audit_id`),".
								"  KEY `ticket_id` (`ticket_id`),".
								"  KEY `epoch` (`epoch`),".
								"  KEY `user_id` (`user_id`),".
								"  KEY `action` (`action`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["audit_id"] = new CER_DB_FIELD("audit_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["epoch"] = new CER_DB_FIELD("epoch","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["action"] = new CER_DB_FIELD("action","int(11)","","MUL","0","");
	$TABLE_DEF->fields["action_value"] = new CER_DB_FIELD("action_value","char(128)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","audit_id");
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
	$TABLE_DEF->indexes["epoch"] = new CER_DB_INDEX("epoch","1","epoch");
	$TABLE_DEF->indexes["user_id"] = new CER_DB_INDEX("user_id","1","user_id");
	$TABLE_DEF->indexes["action"] = new CER_DB_INDEX("action","1","action");
	
	table($TABLE_DEF);
}


function init_table_thread_time_tracking()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("thread_time_tracking",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE thread_time_tracking ( ".
		"thread_time_id bigint(20) unsigned NOT NULL auto_increment, ".
		"ticket_id bigint(20) unsigned NOT NULL default '0', ".
		"thread_time_date datetime NOT NULL default '0000-00-00 00:00:00', ".
		"thread_time_hrs_spent float NOT NULL default '0', ".
		"thread_time_hrs_chargeable float NOT NULL default '0', ".
		"thread_time_hrs_billable float NOT NULL default '0', ".
		"thread_time_hrs_payable float NOT NULL default '0', ".
		"thread_time_working_agent_id bigint(20) unsigned NOT NULL default '0', ".
		"thread_time_summary text, ".
		"thread_time_date_billed datetime NOT NULL default '0000-00-00 00:00:00', ".
		"thread_time_created_by_id bigint(20) unsigned NOT NULL default '0', ".
		"thread_time_created_date datetime NOT NULL default '0000-00-00 00:00:00', ".
		"PRIMARY KEY  (thread_time_id), ".
		"KEY ticket_id (ticket_id), ".
		"KEY thread_time_date (thread_time_date), ".
		"KEY thread_time_date_billed (thread_time_date_billed), ".
		"KEY thread_time_working_agent_id (thread_time_working_agent_id) ".
		") TYPE=MyISAM;";
		
	$TABLE_DEF->fields["thread_time_id"] = new CER_DB_FIELD("thread_time_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["thread_time_date"] = new CER_DB_FIELD("thread_time_date","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["thread_time_hrs_spent"] = new CER_DB_FIELD("thread_time_hrs_spent","float","","","0","");
	$TABLE_DEF->fields["thread_time_hrs_chargeable"] = new CER_DB_FIELD("thread_time_hrs_chargeable","float","","","0","");
	$TABLE_DEF->fields["thread_time_hrs_billable"] = new CER_DB_FIELD("thread_time_hrs_billable","float","","","0","");
	$TABLE_DEF->fields["thread_time_hrs_payable"] = new CER_DB_FIELD("thread_time_hrs_payable","float","","","0","");
	$TABLE_DEF->fields["thread_time_working_agent_id"] = new CER_DB_FIELD("thread_time_working_agent_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["thread_time_summary"] = new CER_DB_FIELD("thread_time_summary","text","","","","");
	$TABLE_DEF->fields["thread_time_date_billed"] = new CER_DB_FIELD("thread_time_date_billed","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["thread_time_created_by_id"] = new CER_DB_FIELD("thread_time_created_by_id","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["thread_time_created_date"] = new CER_DB_FIELD("thread_time_created_date","datetime","","","0000-00-00 00:00:00","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("thread_time_id"));	
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1",array("ticket_id"));	
	$TABLE_DEF->indexes["thread_time_date"] = new CER_DB_INDEX("thread_time_date","1",array("thread_time_date"));	
	$TABLE_DEF->indexes["thread_time_date_billed"] = new CER_DB_INDEX("thread_time_date_billed","1",array("thread_time_date_billed"));	
	$TABLE_DEF->indexes["thread_time_working_agent_id"] = new CER_DB_INDEX("thread_time_working_agent_id","1",array("thread_time_working_agent_id"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `ticket_views` table
// ***************************************************************************
function init_table_ticket_views()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_views",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_views` (".
								 " `view_id` bigint(20) unsigned NOT NULL auto_increment,".
								 " `view_name` char(64) NOT NULL default '',".
								 " `view_created_by_id` bigint(20) NOT NULL default '0',".
								 " `view_private` tinyint(4) NOT NULL default '0',".
								 " `view_queues` char(255) NOT NULL default '',".
								 " `view_columns` text,".
								 " `view_hide_statuses` char(255) NOT NULL default '',".
								 " `view_only_assigned` tinyint(4) NOT NULL default '0',".
								 " `view_adv_2line` tinyint(1) NOT NULL default '1',".
								 " `view_adv_controls` tinyint(1) NOT NULL default '1',".
								 " PRIMARY KEY  (`view_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["view_id"] = new CER_DB_FIELD("view_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["view_name"] = new CER_DB_FIELD("view_name","char(64)","","","","");
	$TABLE_DEF->fields["view_created_by_id"] = new CER_DB_FIELD("view_created_by_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["view_private"] = new CER_DB_FIELD("view_private","tinyint(4)","","","0","");
	$TABLE_DEF->fields["view_queues"] = new CER_DB_FIELD("view_queues","char(255)","","","","");
	$TABLE_DEF->fields["view_columns"] = new CER_DB_FIELD("view_columns","text","","","","");
	$TABLE_DEF->fields["view_hide_statuses"] = new CER_DB_FIELD("view_hide_statuses","char(255)","","","","");	
	$TABLE_DEF->fields["view_only_assigned"] = new CER_DB_FIELD("view_only_assigned","tinyint(4)","","","0","");	
	$TABLE_DEF->fields["view_adv_2line"] = new CER_DB_FIELD("view_adv_2line","tinyint(1)","","","1","");	
	$TABLE_DEF->fields["view_adv_controls"] = new CER_DB_FIELD("view_adv_controls","tinyint(1)","","","1","");	
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","view_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `trigram` table
// ***************************************************************************
function init_table_trigram()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram` (".
								"  `trigram_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `trigram` char(30) NOT NULL default '',".
								"  PRIMARY KEY  (`trigram_id`),".
								"  UNIQUE KEY `trigram` (`trigram`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["trigram"] = new CER_DB_FIELD("trigram","char(30)","","UNI","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","trigram_id");
	$TABLE_DEF->indexes["trigram"] = new CER_DB_INDEX("trigram","0","trigram");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `trigram_stats` table
// ***************************************************************************
function init_table_trigram_stats()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_stats",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_stats` (".
								"  `kb_id` bigint(20) unsigned NOT NULL default '0',".
								"  `num_good` bigint(20) unsigned NOT NULL default '0',".
								"  `num_bad` bigint(20) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`kb_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["num_good"] = new CER_DB_FIELD("num_good","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["num_bad"] = new CER_DB_FIELD("num_bad","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `trigram_to_kb` table
// ***************************************************************************
function init_table_trigram_to_kb()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_kb",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_to_kb` (".
								"  `trigram_id` bigint(20) unsigned NOT NULL default '0',".
								"  `knowledgebase_id` bigint(20) unsigned NOT NULL default '0',".
								"  `good` bigint(20) NOT NULL default '0',".
								"  `bad` bigint(20) NOT NULL default '0',".
								"  UNIQUE KEY `trigram_id` (`trigram_id`,`knowledgebase_id`)".
								") TYPE=MyISAM;";
	
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["knowledgebase_id"] = new CER_DB_FIELD("knowledgebase_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["good"] = new CER_DB_FIELD("good","bigint(20)","","","0","");
	$TABLE_DEF->fields["bad"] = new CER_DB_FIELD("bad","bigint(20)","","","0","");
	
	$TABLE_DEF->indexes["trigram_id"] = new CER_DB_INDEX("trigram_id","0",array("trigram_id","knowledgebase_id"));
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `trigram_to_ticket` table
// ***************************************************************************
function init_table_trigram_to_ticket()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_ticket",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_to_ticket` (".
								"  `trigram_id` bigint(20) unsigned NOT NULL default '0',".
								"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
								"  UNIQUE KEY `ticket_to_trigram` (`trigram_id`,`ticket_id`),".
								"  KEY `ticket_id` (`ticket_id`)".
								") TYPE=MyISAM;";
	
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["ticket_to_trigram"] = new CER_DB_INDEX("ticket_to_trigram","0",array("trigram_id","ticket_id"));
	$TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
	
	table($TABLE_DEF);
}



// ***************************************************************************
// `trigram_training` table
// ***************************************************************************
function init_table_trigram_training()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_training",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_training` (".
								"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
								"  `kb_id` bigint(20) unsigned NOT NULL default '0',".
								"  `user_id` bigint(20) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`ticket_id`,`kb_id`)".
								") TYPE=MyISAM;";
	
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("ticket_id","kb_id"));
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `user` table
// ***************************************************************************
function init_table_user()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user",false);

	$TABLE_DEF->create_sql = "CREATE TABLE `user` (".
								"  `user_id` int(10) unsigned NOT NULL auto_increment,".
								"  `user_name` char(64) NOT NULL default '',".
								"  `user_email` char(128) NOT NULL default '',".
								"  `user_email_verify` char(16) NOT NULL default '',".
								"  `user_login` char(32) NOT NULL default '',".
								"  `user_password` char(64) NOT NULL default '',".
								"  `user_group_id` int(10) unsigned NOT NULL default '0',".
								"  `user_last_login` timestamp(14) NOT NULL,".
								"  `user_superuser` tinyint(1) NOT NULL default '0',".
								"  `user_disabled` tinyint(4) NOT NULL default '0',".
								"  `user_xsp` tinyint(1) NOT NULL default '0',".
								"  PRIMARY KEY  (`user_id`),".
								"  UNIQUE KEY `user_login` (`user_login`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_name"] = new CER_DB_FIELD("user_name","char(64)","","","","");
	$TABLE_DEF->fields["user_email"] = new CER_DB_FIELD("user_email","char(128)","","","","");
	$TABLE_DEF->fields["user_email_verify"] = new CER_DB_FIELD("user_email_verify","char(16)","","","","");
	$TABLE_DEF->fields["user_login"] = new CER_DB_FIELD("user_login","char(32)","","UNI","","");
	$TABLE_DEF->fields["user_password"] = new CER_DB_FIELD("user_password","char(64)","","","","");
	$TABLE_DEF->fields["user_group_id"] = new CER_DB_FIELD("user_group_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["user_last_login"] = new CER_DB_FIELD("user_last_login","timestamp(14)","YES","","","");
	$TABLE_DEF->fields["user_superuser"] = new CER_DB_FIELD("user_superuser","tinyint(1)","","","0","");
	$TABLE_DEF->fields["user_disabled"] = new CER_DB_FIELD("user_disabled","tinyint(4)","","","0","");
	$TABLE_DEF->fields["user_xsp"] = new CER_DB_FIELD("user_xsp","tinyint(1)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","user_id");
	$TABLE_DEF->indexes["user_login"] = new CER_DB_INDEX("user_login","0","user_login");
	
	table($TABLE_DEF);
	
	$sql = sprintf("INSERT IGNORE INTO `user` (user_name,user_email,user_email_verify,user_login,user_password,user_group_id,user_superuser) ".
		" VALUES ('Super User', 'superuser@localhost', '', 'superuser', '%s', 0, 1);",
			md5("superuser")
		);

	$TABLE_DEF->run_sql($sql,"<b>Adding default superuser login to `user`</b>");
}

// ***************************************************************************
// `user_access_levels` table
// ***************************************************************************
function init_table_user_access_levels()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_access_levels",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_access_levels` (".
								"  `group_id` int(10) unsigned NOT NULL auto_increment,".
								"  `group_name` char(40) NOT NULL default '0',".
								"  `is_core_default` tinyint(4) NOT NULL default '0',".
								"  `group_acl` char(20) NOT NULL default '0',".
								"  `group_acl2` char(20) NOT NULL default '0',".
								"  `group_acl3` char(20) NOT NULL default '0',".
								"  PRIMARY KEY  (`group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","char(40)","","","0","");
	$TABLE_DEF->fields["is_core_default"] = new CER_DB_FIELD("is_core_default","tinyint(4)","","","0","");
	$TABLE_DEF->fields["group_acl"] = new CER_DB_FIELD("group_acl","char(20)","","","0","");
	$TABLE_DEF->fields["group_acl2"] = new CER_DB_FIELD("group_acl2","char(20)","","","0","");
	$TABLE_DEF->fields["group_acl3"] = new CER_DB_FIELD("group_acl3","char(20)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","group_id");
	
	table($TABLE_DEF);
}


function init_table_user_layout()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("user_layout",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_layout` (".
		"`layout_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`user_id` BIGINT UNSIGNED DEFAULT 0 NOT NULL ,".
		"`layout_data` TEXT,".
		"PRIMARY KEY ( `layout_id` ) ,".
		"INDEX ( `user_id` ) ".
		");";
		
	$TABLE_DEF->fields["layout_id"] = new CER_DB_FIELD("layout_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["layout_data"] = new CER_DB_FIELD("layout_data","text","","","","");

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("layout_id"));	
	$TABLE_DEF->indexes["user_id"] = new CER_DB_INDEX("user_id","1",array("user_id"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `user_login_log` table
// ***************************************************************************

function init_table_user_login_log()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_login_log",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_login_log` (".
								"  `id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `user_id` bigint(20) unsigned NOT NULL default '0',".
								"  `user_ip` char(15) NOT NULL default '\"\"',".
								"  `local_time_login` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `local_time_logout` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `gmt_time_login` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `gmt_time_logout` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `logged_secs` bigint(20) NOT NULL default '0',".
								"  PRIMARY KEY  (`id`),".
								"  KEY `user_id` (`user_id`),".
								"  KEY `local_time_login` (`local_time_login`),".
								"  KEY `local_time_logout` (`local_time_logout`),".
								"  KEY `gmt_time_login` (`gmt_time_login`),".
								"  KEY `gmt_time_logout` (`gmt_time_logout`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["user_ip"] = new CER_DB_FIELD("user_ip","char(15)","","","\"\"","");
	$TABLE_DEF->fields["local_time_login"] = new CER_DB_FIELD("local_time_login","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["local_time_logout"] = new CER_DB_FIELD("local_time_logout","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["gmt_time_login"] = new CER_DB_FIELD("gmt_time_login","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["gmt_time_logout"] = new CER_DB_FIELD("gmt_time_logout","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["logged_secs"] = new CER_DB_FIELD("logged_secs","bigint(20)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
	$TABLE_DEF->indexes["user_id"] = new CER_DB_INDEX("user_id","1","user_id");
	$TABLE_DEF->indexes["local_time_login"] = new CER_DB_INDEX("local_time_login","1","local_time_login");
	$TABLE_DEF->indexes["local_time_logout"] = new CER_DB_INDEX("local_time_logout","1","local_time_logout");
	$TABLE_DEF->indexes["gmt_time_login"] = new CER_DB_INDEX("gmt_time_login","1","gmt_time_login");
	$TABLE_DEF->indexes["gmt_time_logout"] = new CER_DB_INDEX("gmt_time_logout","1","gmt_time_logout");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `user_notification` table
// ***************************************************************************
function init_table_user_notification()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_notification",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_notification` (".
								"  `user_id` bigint(20) unsigned NOT NULL default '0',".
								"  `notify_options` text,".
								"  UNIQUE KEY `user_id` (`user_id`)".
								") TYPE=MyISAM";
	
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["notify_options"] = new CER_DB_FIELD("notify_options","text","","","","");
	
	$TABLE_DEF->indexes["user_id"] = new CER_DB_INDEX("user_id","0","user_id");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `user_prefs` table
// ***************************************************************************
function init_table_user_prefs()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_prefs",false);

	$TABLE_DEF->create_sql = "CREATE TABLE `user_prefs` (".
								"  `user_id` int(11) NOT NULL default '0',".
								"  `refresh_rate` tinyint(4) default NULL,".
								"  `ticket_order` tinyint(4) default NULL,".
								"  `user_language` char(3) NOT NULL default 'en',".
								"  `signature_pos` tinyint(1) NOT NULL default '0',".
								"  `signature_autoinsert` tinyint(1) NOT NULL default '1',".
								"  `keyboard_shortcuts` tinyint(3) unsigned NOT NULL default '0',".
								"  `view_prefs` text,".
								"  `assign_queues` text,".
								"  `page_layouts` text,".
								"  `gmt_offset` char(5) default '0' NOT NULL,".
								"  PRIMARY KEY  (`user_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(11)","","PRI","0","");
	$TABLE_DEF->fields["refresh_rate"] = new CER_DB_FIELD("refresh_rate","tinyint(4)","YES","","","");
	$TABLE_DEF->fields["ticket_order"] = new CER_DB_FIELD("ticket_order","tinyint(4)","YES","","","");
	$TABLE_DEF->fields["user_language"] = new CER_DB_FIELD("user_language","char(3)","","","en","");
	$TABLE_DEF->fields["signature_pos"] = new CER_DB_FIELD("signature_pos","tinyint(1)","","","0","");
	$TABLE_DEF->fields["signature_autoinsert"] = new CER_DB_FIELD("signature_autoinsert","tinyint(1)","","","1","");
	$TABLE_DEF->fields["keyboard_shortcuts"] = new CER_DB_FIELD("keyboard_shortcuts","tinyint(3) unsigned","","","0","");
	$TABLE_DEF->fields["view_prefs"] = new CER_DB_FIELD("view_prefs","text","YES","","","");
	$TABLE_DEF->fields["assign_queues"] = new CER_DB_FIELD("assign_queues","text","YES","","","");
	$TABLE_DEF->fields["page_layouts"] = new CER_DB_FIELD("page_layouts","text","YES","","","");
	$TABLE_DEF->fields["gmt_offset"] = new CER_DB_FIELD("gmt_offset","char(5)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","user_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `user_sig` table
// ***************************************************************************
function init_table_user_sig()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_sig",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_sig` (".
								"  `sig_id` int(10) unsigned NOT NULL auto_increment,".
								"  `user_id` int(10) unsigned NOT NULL default '0',".
								"  `sig_content` text,".
								"  PRIMARY KEY  (`sig_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["sig_id"] = new CER_DB_FIELD("sig_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["sig_content"] = new CER_DB_FIELD("sig_content","text","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","sig_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `war_check` table
// ***************************************************************************
function init_table_war_check()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("war_check",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `war_check` (".
								"  `warcheck_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `address_id` bigint(20) unsigned NOT NULL default '0',".
								"  `subject_md5` char(32) NOT NULL default '',".
								"  `queue_id` bigint(20) unsigned NOT NULL default '0',".
								"  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  PRIMARY KEY  (`warcheck_id`),".
								"  KEY `address_id` (`address_id`),".
								"  KEY `queue_id` (`queue_id`),".
								"  KEY `subject_md5` (`subject_md5`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["warcheck_id"] = new CER_DB_FIELD("warcheck_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["subject_md5"] = new CER_DB_FIELD("subject_md5","char(32)","","MUL","","");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","datetime","","","0000-00-00 00:00:00","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","warcheck_id");
	$TABLE_DEF->indexes["address_id"] = new CER_DB_INDEX("address_id","1","address_id");
	$TABLE_DEF->indexes["queue_id"] = new CER_DB_INDEX("queue_id","1","queue_id");
	$TABLE_DEF->indexes["subject_md5"] = new CER_DB_INDEX("subject_md5","1","subject_md5");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `whos_online` table
// ***************************************************************************
function init_table_whos_online()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("whos_online",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `whos_online` (".
								"  `user_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `user_ip` char(20) NOT NULL default '',".
								"  `user_timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `user_what_action` int(11) NOT NULL default '0',".
								"  `user_what_arg1` char(64) NOT NULL default '',".
								"  PRIMARY KEY  (`user_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_ip"] = new CER_DB_FIELD("user_ip","char(20)","","","","");
	$TABLE_DEF->fields["user_timestamp"] = new CER_DB_FIELD("user_timestamp","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["user_what_action"] = new CER_DB_FIELD("user_what_action","int(11)","","","0","");
	$TABLE_DEF->fields["user_what_arg1"] = new CER_DB_FIELD("user_what_arg1","char(64)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","user_id");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `chat_agents_to_requests` table
// ***************************************************************************
function init_table_chat_agents_to_requests()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_agents_to_requests",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_agents_to_requests` (".
		"  `agent_id` bigint(20) unsigned NOT NULL default '0',".
		"  `request_date` bigint(20) NOT NULL default '0',".
		"  `room_id` bigint(20) unsigned NOT NULL default '0',".
		"  `visitor_id` bigint(20) unsigned NOT NULL default '0',".
		"  `visitor_name` char(32) NOT NULL default '',".
		"  KEY `agent_id` (`agent_id`),".
		"  KEY `room_id` (`room_id`),".
		"  KEY `visitor_id` (`visitor_id`),".
		"  KEY `request_date` (`request_date`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["request_date"] = new CER_DB_FIELD("request_date","bigint(20)","","MUL","0","");
   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["visitor_name"] = new CER_DB_FIELD("visitor_name","char(32)","","","","");

   $TABLE_DEF->indexes["agent_id"] = new CER_DB_INDEX("agent_id","1","agent_id");
   $TABLE_DEF->indexes["room_id"] = new CER_DB_INDEX("room_id","1","room_id");
   $TABLE_DEF->indexes["visitor_id"] = new CER_DB_INDEX("visitor_id","1","visitor_id");
   $TABLE_DEF->indexes["request_date"] = new CER_DB_INDEX("request_date","1","request_date");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_agents_to_rooms` table
// ***************************************************************************
function init_table_chat_agents_to_rooms()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_agents_to_rooms",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_agents_to_rooms` (".
		"  `room_id` bigint(20) unsigned default NULL,".
		"  `agent_id` bigint(20) unsigned default NULL,".
		"  `join_flags` tinyint(4) NOT NULL default '0',".
		"  `line_id` bigint(20) unsigned NOT NULL default '0',".
		"  KEY `room_id` (`room_id`),".
		"  KEY `agent_id` (`agent_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","bigint(20) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["join_flags"] = new CER_DB_FIELD("join_flags","tinyint(4)","","","0","");
   $TABLE_DEF->fields["line_id"] = new CER_DB_FIELD("line_id","bigint(20) unsigned","","","0","");

   $TABLE_DEF->indexes["room_id"] = new CER_DB_INDEX("room_id","1","room_id");
   $TABLE_DEF->indexes["agent_id"] = new CER_DB_INDEX("agent_id","1","agent_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_canned_text` table
// ***************************************************************************
function init_table_chat_canned_text()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_canned_text",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_canned_text` (".
		"  `text_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `text_title` varchar(64) NOT NULL default '',".
		"  `text_private` tinyint(4) unsigned NOT NULL default '0',".
		"  `text_private_agent_id` bigint(20) unsigned NOT NULL default '0',".
		"  `text_category` bigint(20) unsigned NOT NULL default '0',".
		"  `canned_text` text,".
		"  `last_update` bigint(20) default '0',".
		"  PRIMARY KEY  (`text_id`),".
		"  KEY `text_private` (`text_private`),".
		"  KEY `text_private_agent_id` (`text_private_agent_id`),".
		"  KEY `text_category` (`text_category`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["text_id"] = new CER_DB_FIELD("text_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["text_title"] = new CER_DB_FIELD("text_title","varchar(64)","","","","");
   $TABLE_DEF->fields["text_private"] = new CER_DB_FIELD("text_private","tinyint(4) unsigned","","MUL","0","");
   $TABLE_DEF->fields["text_private_agent_id"] = new CER_DB_FIELD("text_private_agent_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["text_category"] = new CER_DB_FIELD("text_category","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["canned_text"] = new CER_DB_FIELD("canned_text","text","","","","");
   $TABLE_DEF->fields["last_update"] = new CER_DB_FIELD("last_update","bigint(20)","YES","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","text_id");
   $TABLE_DEF->indexes["text_private"] = new CER_DB_INDEX("text_private","1","text_private");
   $TABLE_DEF->indexes["text_private_agent_id"] = new CER_DB_INDEX("text_private_agent_id","1","text_private_agent_id");
   $TABLE_DEF->indexes["text_category"] = new CER_DB_INDEX("text_category","1","text_category");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_message_parts` table
// ***************************************************************************
function init_table_chat_message_parts()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_message_parts",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_message_parts` (".
		"  `message_part_id` int(11) NOT NULL auto_increment,".
		"  `message_id` int(11) NOT NULL default '0',".
		"  `message_part` char(128) default NULL,".
		"  PRIMARY KEY  (`message_part_id`),".
		"  KEY `message_id` (`message_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["message_part_id"] = new CER_DB_FIELD("message_part_id","int(11)","","PRI","","auto_increment");
   $TABLE_DEF->fields["message_id"] = new CER_DB_FIELD("message_id","int(11)","","MUL","0","");
   $TABLE_DEF->fields["message_part"] = new CER_DB_FIELD("message_part","char(128)","YES","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","message_part_id");
   $TABLE_DEF->indexes["message_id"] = new CER_DB_INDEX("message_id","1","message_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `country` table
// ***************************************************************************
function init_table_country()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("country",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `country` (".
		"`country_id` int(10) unsigned NOT NULL auto_increment,".
		"`country_name` char(50) NOT NULL default '',".
		"`region` char(60) default NULL,".
		"`area` decimal(10,0) default NULL,".
		"`population` decimal(11,0) default NULL,".
		"PRIMARY KEY  (`country_id`)".
		") TYPE=MyISAM AUTO_INCREMENT=266 ;";

   $TABLE_DEF->fields["country_id"] = new CER_DB_FIELD("country_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["country_name"] = new CER_DB_FIELD("country_name","char(50)","YES","","","");
   $TABLE_DEF->fields["region"] = new CER_DB_FIELD("region","char(60)","YES","","","");
   $TABLE_DEF->fields["area"] = new CER_DB_FIELD("area","decimal(10,0)","","","","");
   $TABLE_DEF->fields["population"] = new CER_DB_FIELD("population","decimal(11,0)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","country_id");

   table($TABLE_DEF);

   $sql = "SELECT count(*) FROM country";
   $res = $cerberus_db->query($sql);
   
   $row = $cerberus_db->grab_first_row($res);
   if(!is_array($row) || $row[0] < 265) {
   	$cerberus_db->query("DELETE FROM `country`");
		$cerberus_db->query("INSERT INTO `country` VALUES (1, 'Afghanistan', 'Asia', 647500, 21251821)");
		$cerberus_db->query("INSERT INTO `country` VALUES (2, 'Albania', 'Ethnic Groups in Eastern Europe,  Europe', 28750, 3413904)");
		$cerberus_db->query("INSERT INTO `country` VALUES (3, 'Algeria', 'Africa', 2381740, 28539321)");
		$cerberus_db->query("INSERT INTO `country` VALUES (4, 'American Samoa', 'Oceania', 199, 57366)");
		$cerberus_db->query("INSERT INTO `country` VALUES (5, 'Andorra', 'Europe', 450, 65780)");
		$cerberus_db->query("INSERT INTO `country` VALUES (6, 'Angola', 'Africa', 1246700, 10069501)");
		$cerberus_db->query("INSERT INTO `country` VALUES (7, 'Anguilla', 'Central America and the Caribbean', 91, 7099)");
		$cerberus_db->query("INSERT INTO `country` VALUES (8, 'Antarctica', 'Antarctic Region', 14, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (9, 'Antigua and Barbuda', 'Central America and the Caribbean', 440, 65176)");
		$cerberus_db->query("INSERT INTO `country` VALUES (10, 'Arctic Ocean', 'Arctic Region', 14, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (11, 'Argentina', 'South America', 2766890, 34292742)");
		$cerberus_db->query("INSERT INTO `country` VALUES (12, 'Armenia', 'Commonwealth of Independent States - European States', 29800, 3557284)");
		$cerberus_db->query("INSERT INTO `country` VALUES (13, 'Aruba', 'Central America and the Caribbean', 193, 65974)");
		$cerberus_db->query("INSERT INTO `country` VALUES (14, 'Ashmore and Cartier Islands', 'Southeast Asia', 5, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (15, 'Atlantic Ocean', 'World', 82, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (16, 'Australia', 'Oceania', 7686850, 18322231)");
		$cerberus_db->query("INSERT INTO `country` VALUES (17, 'Austria', 'Europe', 83850, 7986664)");
		$cerberus_db->query("INSERT INTO `country` VALUES (18, 'Azerbaijan', 'Commonwealth of Independent States - European States', 86600, 7789886)");
		$cerberus_db->query("INSERT INTO `country` VALUES (19, 'The Bahamas', 'Central America and the Caribbean', 13940, 256616)");
		$cerberus_db->query("INSERT INTO `country` VALUES (20, 'Bahrain', 'Middle East', 620, 575925)");
		$cerberus_db->query("INSERT INTO `country` VALUES (21, 'Baker Island', 'Oceania', 1, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (22, 'Bangladesh', 'Asia', 144000, 128094948)");
		$cerberus_db->query("INSERT INTO `country` VALUES (23, 'Barbados', 'Central America and the Caribbean', 430, 256395)");
		$cerberus_db->query("INSERT INTO `country` VALUES (24, 'Bassas da India', 'Africa', 0, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (25, 'Belarus', 'Commonwealth of Independent States - European States', 207600, 10437418)");
		$cerberus_db->query("INSERT INTO `country` VALUES (26, 'Belgium', 'Europe', 30510, 10081880)");
		$cerberus_db->query("INSERT INTO `country` VALUES (27, 'Belize', 'Central America and the Caribbean', 22960, 214061)");
		$cerberus_db->query("INSERT INTO `country` VALUES (28, 'Benin', 'Africa', 112620, 5522677)");
		$cerberus_db->query("INSERT INTO `country` VALUES (29, 'Bermuda', 'North America', 50, 61629)");
		$cerberus_db->query("INSERT INTO `country` VALUES (30, 'Bhutan', 'Asia', 47000, 1780638)");
		$cerberus_db->query("INSERT INTO `country` VALUES (31, 'Bolivia', 'South America', 1098580, 7896254)");
		$cerberus_db->query("INSERT INTO `country` VALUES (32, 'Bosnia and Herzegovina', 'Ethnic Groups in Eastern Europe,  Europe', 51233, 3201823)");
		$cerberus_db->query("INSERT INTO `country` VALUES (33, 'Botswana', 'Africa', 600370, 1392414)");
		$cerberus_db->query("INSERT INTO `country` VALUES (34, 'Bouvet Island', 'Antarctic Region', 58, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (35, 'Brazil', 'South America', 8511965, 160737489)");
		$cerberus_db->query("INSERT INTO `country` VALUES (36, 'British Indian Ocean Territory', 'World', 60, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (37, 'British Virgin Islands', 'Central America and the Caribbean', 150, 13027)");
		$cerberus_db->query("INSERT INTO `country` VALUES (38, 'Brunei', 'Southeast Asia', 5770, 292266)");
		$cerberus_db->query("INSERT INTO `country` VALUES (39, 'Bulgaria', 'Ethnic Groups in Eastern Europe,  Europe', 110910, 8775198)");
		$cerberus_db->query("INSERT INTO `country` VALUES (40, 'Burkina', 'Africa', 274200, 10422828)");
		$cerberus_db->query("INSERT INTO `country` VALUES (41, 'Burma', 'Southeast Asia', 678500, 45103809)");
		$cerberus_db->query("INSERT INTO `country` VALUES (42, 'Burundi', 'Africa', 27830, 6262429)");
		$cerberus_db->query("INSERT INTO `country` VALUES (43, 'Cambodia', 'Southeast Asia', 181040, 10561373)");
		$cerberus_db->query("INSERT INTO `country` VALUES (44, 'Cameroon', 'Africa', 475440, 13000000)");
		$cerberus_db->query("INSERT INTO `country` VALUES (45, 'Canada', 'North America', 9976140, 28434545)");
		$cerberus_db->query("INSERT INTO `country` VALUES (46, 'Cape Verde', 'World', 4030, 435983)");
		$cerberus_db->query("INSERT INTO `country` VALUES (47, 'Cayman Islands', 'Central America and the Caribbean', 260, 33192)");
		$cerberus_db->query("INSERT INTO `country` VALUES (48, 'Central African Republic', 'Africa', 622980, 3209759)");
		$cerberus_db->query("INSERT INTO `country` VALUES (49, 'Chad', 'Africa', 1, 5586505)");
		$cerberus_db->query("INSERT INTO `country` VALUES (50, 'Chile', 'South America', 756950, 14161216)");
		$cerberus_db->query("INSERT INTO `country` VALUES (51, 'China', 'Asia', 9596960, 1203097268)");
		$cerberus_db->query("INSERT INTO `country` VALUES (52, 'Christmas Island', 'Southeast Asia', 135, 889)");
		$cerberus_db->query("INSERT INTO `country` VALUES (53, 'Clipperton Island', 'World', 7, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (54, 'Cocos (Keeling) Islands', 'Southeast Asia', 14, 604)");
		$cerberus_db->query("INSERT INTO `country` VALUES (55, 'Colombia', 'South America', 1138910, 36200251)");
		$cerberus_db->query("INSERT INTO `country` VALUES (56, 'Comoros', 'Africa', 2170, 549338)");
		$cerberus_db->query("INSERT INTO `country` VALUES (57, 'Congo', 'Africa', 342000, 2504996)");
		$cerberus_db->query("INSERT INTO `country` VALUES (58, 'Cook Islands', 'Oceania', 240, 19343)");
		$cerberus_db->query("INSERT INTO `country` VALUES (59, 'Coral Sea Islands', 'Oceania', 0, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (60, 'Costa Rica', 'Central America and the Caribbean', 51100, 3419114)");
		$cerberus_db->query("INSERT INTO `country` VALUES (61, 'Cote d''Ivoire', 'Africa', 322460, 14791257)");
		$cerberus_db->query("INSERT INTO `country` VALUES (62, 'Croatia', 'Ethnic Groups in Eastern Europe,  Europe', 56538, 4665821)");
		$cerberus_db->query("INSERT INTO `country` VALUES (63, 'Cuba', 'Central America and the Caribbean', 110860, 10937635)");
		$cerberus_db->query("INSERT INTO `country` VALUES (64, 'Cyprus', 'Middle East', 9250, 744609)");
		$cerberus_db->query("INSERT INTO `country` VALUES (65, 'Czech Republic', 'Ethnic Groups in Eastern Europe,  Europe', 78703, 10432774)");
		$cerberus_db->query("INSERT INTO `country` VALUES (66, 'Denmark', 'Europe', 43070, 5199437)");
		$cerberus_db->query("INSERT INTO `country` VALUES (67, 'Djibouti', 'Africa', 22000, 421320)");
		$cerberus_db->query("INSERT INTO `country` VALUES (68, 'Dominica', 'Central America and the Caribbean', 750, 82608)");
		$cerberus_db->query("INSERT INTO `country` VALUES (69, 'Dominican Republic', 'Central America and the Caribbean', 48730, 7511263)");
		$cerberus_db->query("INSERT INTO `country` VALUES (70, 'Ecuador', 'South America', 283560, 10890950)");
		$cerberus_db->query("INSERT INTO `country` VALUES (71, 'Egypt', 'Africa', 1001450, 62359623)");
		$cerberus_db->query("INSERT INTO `country` VALUES (72, 'El Salvador', 'Central America and the Caribbean', 21040, 5870481)");
		$cerberus_db->query("INSERT INTO `country` VALUES (73, 'Equatorial Guinea', 'Africa', 28050, 420293)");
		$cerberus_db->query("INSERT INTO `country` VALUES (74, 'Eritrea', 'Africa', 121320, 3578709)");
		$cerberus_db->query("INSERT INTO `country` VALUES (75, 'Estonia', 'Europe', 45100, 1625399)");
		$cerberus_db->query("INSERT INTO `country` VALUES (76, 'Ethiopia', 'Africa', 1127127, 55979018)");
		$cerberus_db->query("INSERT INTO `country` VALUES (77, 'Europa Island', 'Africa', 28, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (78, 'Falkland Islands (Islas Malvinas)', 'South America', 12170, 2317)");
		$cerberus_db->query("INSERT INTO `country` VALUES (79, 'Faroe Islands', 'Europe', 1400, 48871)");
		$cerberus_db->query("INSERT INTO `country` VALUES (80, 'Fiji', 'Oceania', 18270, 772891)");
		$cerberus_db->query("INSERT INTO `country` VALUES (81, 'Finland', 'Europe', 337030, 5085206)");
		$cerberus_db->query("INSERT INTO `country` VALUES (82, 'France', 'Europe', 547030, 58109160)");
		$cerberus_db->query("INSERT INTO `country` VALUES (83, 'French Guiana', 'South America', 91000, 145270)");
		$cerberus_db->query("INSERT INTO `country` VALUES (84, 'French Polynesia', 'Oceania', 3941, 219999)");
		$cerberus_db->query("INSERT INTO `country` VALUES (85, 'French Southern and Antarctic Lands', 'Antarctic Region', 7781, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (86, 'Gabon', 'Africa', 267670, 1155749)");
		$cerberus_db->query("INSERT INTO `country` VALUES (87, 'The Gambia', 'Africa', 11300, 989273)");
		$cerberus_db->query("INSERT INTO `country` VALUES (88, 'Gaza Strip', 'Middle East', 360, 813322)");
		$cerberus_db->query("INSERT INTO `country` VALUES (89, 'Georgia', 'Middle East', 69700, 5725972)");
		$cerberus_db->query("INSERT INTO `country` VALUES (90, 'Germany', 'Europe', 356910, 81337541)");
		$cerberus_db->query("INSERT INTO `country` VALUES (91, 'Ghana', 'Africa', 238540, 17763138)");
		$cerberus_db->query("INSERT INTO `country` VALUES (92, 'Gibraltar', 'Europe', 6, 31874)");
		$cerberus_db->query("INSERT INTO `country` VALUES (93, 'Glorioso Islands', 'Africa', 5, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (94, 'Greece', 'Europe', 131940, 10647511)");
		$cerberus_db->query("INSERT INTO `country` VALUES (95, 'Greenland', 'Arctic Region', 2175600, 57611)");
		$cerberus_db->query("INSERT INTO `country` VALUES (96, 'Grenada', 'Central America and the Caribbean', 340, 94486)");
		$cerberus_db->query("INSERT INTO `country` VALUES (97, 'Guadeloupe', 'Central America and the Caribbean', 1780, 402815)");
		$cerberus_db->query("INSERT INTO `country` VALUES (98, 'Guam', 'Oceania', 541, 153307)");
		$cerberus_db->query("INSERT INTO `country` VALUES (99, 'Guatemala', 'Central America and the Caribbean', 108890, 10998602)");
		$cerberus_db->query("INSERT INTO `country` VALUES (100, 'Guernsey', 'Europe', 194, 64353)");
		$cerberus_db->query("INSERT INTO `country` VALUES (101, 'Guinea', 'Africa', 245860, 6549336)");
		$cerberus_db->query("INSERT INTO `country` VALUES (102, 'Guinea-Bissau', 'Africa', 36120, 1124537)");
		$cerberus_db->query("INSERT INTO `country` VALUES (103, 'Guyana', 'South America', 214970, 723774)");
		$cerberus_db->query("INSERT INTO `country` VALUES (104, 'Haiti', 'Central America and the Caribbean', 27750, 6539983)");
		$cerberus_db->query("INSERT INTO `country` VALUES (105, 'Heard Island and McDonald Islands', 'Antarctic Region', 412, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (106, 'Holy See (Vatican City)', 'Europe', 0, 830)");
		$cerberus_db->query("INSERT INTO `country` VALUES (107, 'Honduras', 'Central America and the Caribbean', 112090, 5459743)");
		$cerberus_db->query("INSERT INTO `country` VALUES (108, 'Hong Kong', 'Southeast Asia', 1040, 5542869)");
		$cerberus_db->query("INSERT INTO `country` VALUES (109, 'Howland Island', 'Oceania', 1, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (110, 'Hungary', 'Ethnic Groups in Eastern Europe,  Europe', 93030, 10318838)");
		$cerberus_db->query("INSERT INTO `country` VALUES (111, 'Iceland', 'Arctic Region', 103000, 265998)");
		$cerberus_db->query("INSERT INTO `country` VALUES (112, 'India', 'Asia', 3287590, 936545814)");
		$cerberus_db->query("INSERT INTO `country` VALUES (113, 'Indian Ocean', 'World', 73, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (114, 'Indonesia', 'Southeast Asia', 1919440, 203583886)");
		$cerberus_db->query("INSERT INTO `country` VALUES (115, 'Iran', 'Middle East', 1648000, 64625455)");
		$cerberus_db->query("INSERT INTO `country` VALUES (116, 'Iraq', 'Middle East', 437072, 20643769)");
		$cerberus_db->query("INSERT INTO `country` VALUES (117, 'Ireland', 'Europe', 70280, 3550448)");
		$cerberus_db->query("INSERT INTO `country` VALUES (118, 'Israel', 'Middle East', 20770, 5433134)");
		$cerberus_db->query("INSERT INTO `country` VALUES (119, 'Italy', 'Europe', 301230, 58261971)");
		$cerberus_db->query("INSERT INTO `country` VALUES (120, 'Jamaica', 'Central America and the Caribbean', 10990, 2574291)");
		$cerberus_db->query("INSERT INTO `country` VALUES (121, 'Jan Mayen', 'Arctic Region', 373, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (122, 'Japan', 'Asia', 377835, 125506492)");
		$cerberus_db->query("INSERT INTO `country` VALUES (123, 'Jarvis Island', 'Oceania', 4, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (124, 'Jersey', 'Europe', 117, 86649)");
		$cerberus_db->query("INSERT INTO `country` VALUES (125, 'Johnston Atoll', 'Oceania', 2, 327)");
		$cerberus_db->query("INSERT INTO `country` VALUES (126, 'Jordan', 'Middle East', 89213, 4100709)");
		$cerberus_db->query("INSERT INTO `country` VALUES (127, 'Juan de Nova Island', 'Africa', 4, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (128, 'Kazakhstan', 'Commonwealth of Independent States - Central Asian States', 2717300, 17376615)");
		$cerberus_db->query("INSERT INTO `country` VALUES (129, 'Kenya', 'Africa', 582650, 28817227)");
		$cerberus_db->query("INSERT INTO `country` VALUES (130, 'Kingman Reef', 'Oceania', 1, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (131, 'Kiribati', 'Oceania', 717, 79386)");
		$cerberus_db->query("INSERT INTO `country` VALUES (132, 'Korea,  North', 'Asia', 120540, 23486550)");
		$cerberus_db->query("INSERT INTO `country` VALUES (133, 'Korea,  South', 'Asia', 98480, 45553882)");
		$cerberus_db->query("INSERT INTO `country` VALUES (134, 'Kuwait', 'Middle East', 17820, 1817397)");
		$cerberus_db->query("INSERT INTO `country` VALUES (135, 'Kyrgyzstan', 'Commonwealth of Independent States - Central Asian States', 198500, 4769877)");
		$cerberus_db->query("INSERT INTO `country` VALUES (136, 'Laos', 'Southeast Asia', 236800, 4837237)");
		$cerberus_db->query("INSERT INTO `country` VALUES (137, 'Latvia', 'Europe', 64100, 2762899)");
		$cerberus_db->query("INSERT INTO `country` VALUES (138, 'Lebanon', 'Middle East', 10400, 3695921)");
		$cerberus_db->query("INSERT INTO `country` VALUES (139, 'Lesotho', 'Africa', 30350, 1992960)");
		$cerberus_db->query("INSERT INTO `country` VALUES (140, 'Liberia', 'Africa', 111370, 3073245)");
		$cerberus_db->query("INSERT INTO `country` VALUES (141, 'Libya', 'Africa', 1759540, 5248401)");
		$cerberus_db->query("INSERT INTO `country` VALUES (142, 'Liechtenstein', 'Europe', 160, 30654)");
		$cerberus_db->query("INSERT INTO `country` VALUES (143, 'Lithuania', 'Europe', 65200, 3876396)");
		$cerberus_db->query("INSERT INTO `country` VALUES (144, 'Luxembourg', 'Europe', 2586, 404660)");
		$cerberus_db->query("INSERT INTO `country` VALUES (145, 'Macau', 'Southeast Asia', 16, 490901)");
		$cerberus_db->query("INSERT INTO `country` VALUES (146, 'Macedonia', 'The Former Yugoslav Republic of Ethnic Groups in Eastern Eur', 25333, 2159503)");
		$cerberus_db->query("INSERT INTO `country` VALUES (147, 'Madagascar', 'Africa', 587040, 13862325)");
		$cerberus_db->query("INSERT INTO `country` VALUES (148, 'Malawi', 'Africa', 118480, 9808384)");
		$cerberus_db->query("INSERT INTO `country` VALUES (149, 'Malaysia', 'Southeast Asia', 329750, 19723587)");
		$cerberus_db->query("INSERT INTO `country` VALUES (150, 'Maldives', 'Asia', 300, 261310)");
		$cerberus_db->query("INSERT INTO `country` VALUES (151, 'Mali', 'Africa', 1, 9375132)");
		$cerberus_db->query("INSERT INTO `country` VALUES (152, 'Malta', 'Europe', 320, 369609)");
		$cerberus_db->query("INSERT INTO `country` VALUES (153, 'Man,  Isle of', 'Europe', 588, 72751)");
		$cerberus_db->query("INSERT INTO `country` VALUES (154, 'Marshall Islands', 'Oceania', 181, 56157)");
		$cerberus_db->query("INSERT INTO `country` VALUES (155, 'Martinique', 'Central America and the Caribbean', 1100, 394787)");
		$cerberus_db->query("INSERT INTO `country` VALUES (156, 'Mauritania', 'Africa', 1030700, 2263202)");
		$cerberus_db->query("INSERT INTO `country` VALUES (157, 'Mauritius', 'World', 1860, 1127068)");
		$cerberus_db->query("INSERT INTO `country` VALUES (158, 'Mayotte', 'Africa', 375, 97088)");
		$cerberus_db->query("INSERT INTO `country` VALUES (159, 'Mexico', 'North America', 1972550, 93985848)");
		$cerberus_db->query("INSERT INTO `country` VALUES (160, 'Micronesia,  Federated States of', 'Oceania', 702, 122950)");
		$cerberus_db->query("INSERT INTO `country` VALUES (161, 'Midway Islands', 'Oceania', 5, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (162, 'Moldova', 'Commonwealth of Independent States - European States', 33700, 4489657)");
		$cerberus_db->query("INSERT INTO `country` VALUES (163, 'Monaco', 'Europe', 1, 31515)");
		$cerberus_db->query("INSERT INTO `country` VALUES (164, 'Mongolia', 'Asia', 1, 2493615)");
		$cerberus_db->query("INSERT INTO `country` VALUES (165, 'Montserrat', 'Central America and the Caribbean', 100, 12738)");
		$cerberus_db->query("INSERT INTO `country` VALUES (166, 'Morocco', 'Africa', 446550, 29168848)");
		$cerberus_db->query("INSERT INTO `country` VALUES (167, 'Mozambique', 'Africa', 801590, 18115250)");
		$cerberus_db->query("INSERT INTO `country` VALUES (168, 'Namibia', 'Africa', 825418, 1651545)");
		$cerberus_db->query("INSERT INTO `country` VALUES (169, 'Nauru', 'Oceania', 21, 10149)");
		$cerberus_db->query("INSERT INTO `country` VALUES (170, 'Navassa Island', 'Central America and the Caribbean', 5, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (171, 'Nepal', 'Asia', 140800, 21560869)");
		$cerberus_db->query("INSERT INTO `country` VALUES (172, 'Netherlands', 'Europe', 37330, 15452903)");
		$cerberus_db->query("INSERT INTO `country` VALUES (173, 'Netherlands Antilles', 'Central America and the Caribbean', 960, 203505)");
		$cerberus_db->query("INSERT INTO `country` VALUES (174, 'New Caledonia', 'Oceania', 19060, 184552)");
		$cerberus_db->query("INSERT INTO `country` VALUES (175, 'New Zealand', 'Oceania', 268680, 3407277)");
		$cerberus_db->query("INSERT INTO `country` VALUES (176, 'Nicaragua', 'Central America and the Caribbean', 129494, 4206353)");
		$cerberus_db->query("INSERT INTO `country` VALUES (177, 'Niger', 'Africa', 1, 9280208)");
		$cerberus_db->query("INSERT INTO `country` VALUES (178, 'Nigeria', 'Africa', 923770, 101232251)");
		$cerberus_db->query("INSERT INTO `country` VALUES (179, 'Niue', 'Oceania', 260, 1837)");
		$cerberus_db->query("INSERT INTO `country` VALUES (180, 'Norfolk Island', 'Oceania', 34, 2756)");
		$cerberus_db->query("INSERT INTO `country` VALUES (181, 'Northern Mariana Islands', 'Oceania', 477, 51033)");
		$cerberus_db->query("INSERT INTO `country` VALUES (182, 'Norway', 'Europe', 324220, 4330951)");
		$cerberus_db->query("INSERT INTO `country` VALUES (183, 'Oman', 'Middle East', 212460, 2125089)");
		$cerberus_db->query("INSERT INTO `country` VALUES (184, 'Pacific Ocean', 'World', 165, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (185, 'Pakistan', 'Asia', 803940, 131541920)");
		$cerberus_db->query("INSERT INTO `country` VALUES (186, 'Palau', 'Oceania', 458, 16661)");
		$cerberus_db->query("INSERT INTO `country` VALUES (187, 'Palmyra Atoll', 'Oceania', 11, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (188, 'Panama', 'Central America and the Caribbean', 78200, 2680903)");
		$cerberus_db->query("INSERT INTO `country` VALUES (189, 'Papua New Guinea', 'Oceania', 461690, 4294750)");
		$cerberus_db->query("INSERT INTO `country` VALUES (190, 'Paracel Islands', 'Southeast Asia', 0, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (191, 'Paraguay', 'South America', 406750, 5358198)");
		$cerberus_db->query("INSERT INTO `country` VALUES (192, 'Peru', 'South America', 1285220, 24087372)");
		$cerberus_db->query("INSERT INTO `country` VALUES (193, 'Philippines', 'Southeast Asia', 300000, 73265584)");
		$cerberus_db->query("INSERT INTO `country` VALUES (194, 'Pitcairn Islands', 'Oceania', 47, 73)");
		$cerberus_db->query("INSERT INTO `country` VALUES (195, 'Poland', 'Ethnic Groups in Eastern Europe,  Europe', 312680, 38792442)");
		$cerberus_db->query("INSERT INTO `country` VALUES (196, 'Portugal', 'Europe', 92080, 10562388)");
		$cerberus_db->query("INSERT INTO `country` VALUES (197, 'Puerto Rico', 'Central America and the Caribbean', 9104, 3812569)");
		$cerberus_db->query("INSERT INTO `country` VALUES (198, 'Qatar', 'Middle East', 11000, 533916)");
		$cerberus_db->query("INSERT INTO `country` VALUES (199, 'Reunion', 'World', 2510, 666067)");
		$cerberus_db->query("INSERT INTO `country` VALUES (200, 'Romania', 'Ethnic Groups in Eastern Europe,  Europe', 237500, 23198330)");
		$cerberus_db->query("INSERT INTO `country` VALUES (201, 'Russia', 'Asia', 17075200, 149909089)");
		$cerberus_db->query("INSERT INTO `country` VALUES (202, 'Rwanda', 'Africa', 26340, 8605307)");
		$cerberus_db->query("INSERT INTO `country` VALUES (203, 'Saint Helena', 'Africa', 410, 6762)");
		$cerberus_db->query("INSERT INTO `country` VALUES (204, 'Saint Kitts and Nevis', 'Central America and the Caribbean', 269, 40992)");
		$cerberus_db->query("INSERT INTO `country` VALUES (205, 'Saint Lucia', 'Central America and the Caribbean', 620, 156050)");
		$cerberus_db->query("INSERT INTO `country` VALUES (206, 'Saint Pierre and Miquelon', 'North America', 242, 6757)");
		$cerberus_db->query("INSERT INTO `country` VALUES (207, 'Saint Vincent and the Grenadines', 'Central America and the Caribbean', 340, 117344)");
		$cerberus_db->query("INSERT INTO `country` VALUES (208, 'San Marino', 'Europe', 60, 24313)");
		$cerberus_db->query("INSERT INTO `country` VALUES (209, 'Sao Tome and Principe', 'Africa', 960, 140423)");
		$cerberus_db->query("INSERT INTO `country` VALUES (210, 'Saudi Arabia', 'Middle East', 1960582, 18729576)");
		$cerberus_db->query("INSERT INTO `country` VALUES (211, 'Senegal', 'Africa', 196190, 9007080)");
		$cerberus_db->query("INSERT INTO `country` VALUES (212, 'Serbia and Montenegro', 'Ethnic Groups in Eastern Europe,  Europe', 102350, 10614558)");
		$cerberus_db->query("INSERT INTO `country` VALUES (213, 'Seychelles', 'Africa', 455, 72709)");
		$cerberus_db->query("INSERT INTO `country` VALUES (214, 'Sierra Leone', 'Africa', 71740, 4753120)");
		$cerberus_db->query("INSERT INTO `country` VALUES (215, 'Singapore', 'Southeast Asia', 632, 2890468)");
		$cerberus_db->query("INSERT INTO `country` VALUES (216, 'Slovakia', 'Ethnic Groups in Eastern Europe,  Europe', 48845, 5432383)");
		$cerberus_db->query("INSERT INTO `country` VALUES (217, 'Slovenia', 'Ethnic Groups in Eastern Europe,  Europe', 20296, 2051522)");
		$cerberus_db->query("INSERT INTO `country` VALUES (218, 'Solomon Islands', 'Oceania', 28450, 399206)");
		$cerberus_db->query("INSERT INTO `country` VALUES (219, 'Somalia', 'Africa', 637660, 7347554)");
		$cerberus_db->query("INSERT INTO `country` VALUES (220, 'South Africa', 'Africa', 1219912, 41743459)");
		$cerberus_db->query("INSERT INTO `country` VALUES (221, 'South Georgia and the South Sandwich Islands', 'Antarctic Region', 4066, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (222, 'Spain', 'Europe', 504750, 39404348)");
		$cerberus_db->query("INSERT INTO `country` VALUES (223, 'Spratly Islands', 'Southeast Asia', 0, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (224, 'Sri Lanka', 'Asia', 65610, 18342660)");
		$cerberus_db->query("INSERT INTO `country` VALUES (225, 'Sudan', 'Africa', 2505810, 30120420)");
		$cerberus_db->query("INSERT INTO `country` VALUES (226, 'Suriname', 'South America', 163270, 429544)");
		$cerberus_db->query("INSERT INTO `country` VALUES (227, 'Svalbard', 'Arctic Region', 62049, 2914)");
		$cerberus_db->query("INSERT INTO `country` VALUES (228, 'Swaziland', 'Africa', 17360, 966977)");
		$cerberus_db->query("INSERT INTO `country` VALUES (229, 'Sweden', 'Europe', 449964, 8821759)");
		$cerberus_db->query("INSERT INTO `country` VALUES (230, 'Switzerland', 'Europe', 41290, 7084984)");
		$cerberus_db->query("INSERT INTO `country` VALUES (231, 'Syria', 'Middle East', 185180, 15451917)");
		$cerberus_db->query("INSERT INTO `country` VALUES (232, 'Taiwan', 'Southeast Asia', 35980, 21500583)");
		$cerberus_db->query("INSERT INTO `country` VALUES (233, 'Tajikistan', 'Commonwealth of Independent States - Central Asian States', 143100, 6155474)");
		$cerberus_db->query("INSERT INTO `country` VALUES (234, 'Tanzania', 'Africa', 945090, 28701077)");
		$cerberus_db->query("INSERT INTO `country` VALUES (235, 'Thailand', 'Southeast Asia', 514000, 60271300)");
		$cerberus_db->query("INSERT INTO `country` VALUES (236, 'Togo', 'Africa', 56790, 4410370)");
		$cerberus_db->query("INSERT INTO `country` VALUES (237, 'Tokelau', 'Oceania', 10, 1503)");
		$cerberus_db->query("INSERT INTO `country` VALUES (238, 'Tonga', 'Oceania', 748, 105600)");
		$cerberus_db->query("INSERT INTO `country` VALUES (239, 'Trinidad and Tobago', 'Central America and the Caribbean', 5130, 1271159)");
		$cerberus_db->query("INSERT INTO `country` VALUES (240, 'Tromelin Island', 'Africa', 1, 0)");
		$cerberus_db->query("INSERT INTO `country` VALUES (241, 'Tunisia', 'Africa', 163610, 8879845)");
		$cerberus_db->query("INSERT INTO `country` VALUES (242, 'Turkey', 'Middle East', 780580, 63405526)");
		$cerberus_db->query("INSERT INTO `country` VALUES (243, 'Turkmenistan', 'Commonwealth of Independent States - Central Asian States', 488100, 4075316)");
		$cerberus_db->query("INSERT INTO `country` VALUES (244, 'Turks and Caicos Islands', 'Central America and the Caribbean', 430, 13941)");
		$cerberus_db->query("INSERT INTO `country` VALUES (245, 'Tuvalu', 'Oceania', 26, 9991)");
		$cerberus_db->query("INSERT INTO `country` VALUES (246, 'Uganda', 'Africa', 236040, 19573262)");
		$cerberus_db->query("INSERT INTO `country` VALUES (247, 'Ukraine', 'Commonwealth of Independent States - European States', 603700, 51867828)");
		$cerberus_db->query("INSERT INTO `country` VALUES (248, 'United Arab Emirates', 'Middle East', 75581, 2924594)");
		$cerberus_db->query("INSERT INTO `country` VALUES (249, 'United Kingdom', 'Europe', 244820, 58295119)");
		$cerberus_db->query("INSERT INTO `country` VALUES (250, 'United States', 'North America', 9372610, 263814032)");
		$cerberus_db->query("INSERT INTO `country` VALUES (251, 'Uruguay', 'South America', 176220, 3222716)");
		$cerberus_db->query("INSERT INTO `country` VALUES (252, 'Uzbekistan', 'Commonwealth of Independent States - Central Asian States', 447400, 23089261)");
		$cerberus_db->query("INSERT INTO `country` VALUES (253, 'Vanuatu', 'Oceania', 14760, 173648)");
		$cerberus_db->query("INSERT INTO `country` VALUES (254, 'Venezuela', 'South America', 912050, 21004773)");
		$cerberus_db->query("INSERT INTO `country` VALUES (255, 'Vietnam', 'Southeast Asia', 329560, 74393324)");
		$cerberus_db->query("INSERT INTO `country` VALUES (256, 'Virgin Islands', 'Central America and the Caribbean', 352, 97229)");
		$cerberus_db->query("INSERT INTO `country` VALUES (257, 'Wake Island', 'Oceania', 6, 302)");
		$cerberus_db->query("INSERT INTO `country` VALUES (258, 'Wallis and Futuna', 'Oceania', 274, 14499)");
		$cerberus_db->query("INSERT INTO `country` VALUES (259, 'West Bank', 'Middle East', 5860, 1319991)");
		$cerberus_db->query("INSERT INTO `country` VALUES (260, 'Western Sahara', 'Africa', 266000, 217211)");
		$cerberus_db->query("INSERT INTO `country` VALUES (261, 'Western Samoa', 'Oceania', 2860, 209360)");
		$cerberus_db->query("INSERT INTO `country` VALUES (262, 'Yemen', 'Middle East', 527970, 14728474)");
		$cerberus_db->query("INSERT INTO `country` VALUES (263, 'Zaire', 'Africa', 2345410, 44060636)");
		$cerberus_db->query("INSERT INTO `country` VALUES (264, 'Zambia', 'Africa', 752610, 9445723)");
		$cerberus_db->query("INSERT INTO `country` VALUES (265, 'Zimbabwe', 'Africa', 390580, 11139961)");   	
   }
   
}


// ***************************************************************************
// `industry` table
// ***************************************************************************
function init_table_industry()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("industry",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `industry` (".
		"`industry_id` int(10) unsigned NOT NULL auto_increment,".
		"`industry_name` char(32) NOT NULL default '',".
		"`industry_sector` enum('Basic Materials','Capital Goods','Conglomerates','Consumer Cyclical','Consumer Non-Cyclical','Energy','Financial','Healthcare','Public Administration','Services','Technology','Transportation','Utilities') NOT NULL default 'Basic Materials',".
		"PRIMARY KEY  (`industry_id`),".
		"KEY `industry_sector` (`industry_sector`)".
		") TYPE=MyISAM AUTO_INCREMENT=110 ;";

   $TABLE_DEF->fields["industry_id"] = new CER_DB_FIELD("industry_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["industry_name"] = new CER_DB_FIELD("industry_name","char(32)","","","","");
   $TABLE_DEF->fields["industry_sector"] = new CER_DB_FIELD("industry_sector","enum('Basic Materials','Capital Goods','Conglomerates','Consumer Cyclical','Consumer Non-Cyclical','Energy','Financial','Healthcare','Public Administration','Services','Technology','Transportation','Utilities')","","MUL","Basic Materials","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","industry_id");
   $TABLE_DEF->indexes["industry_sector"] = new CER_DB_INDEX("industry_sector","1","industry_sector");

   table($TABLE_DEF);

   $sql = "SELECT count(*) FROM industry";
   $res = $cerberus_db->query($sql);
   
   $row = $cerberus_db->grab_first_row($res);
   if(!is_array($row) || !array_key_exists(0, $row) || $row[0] == 0) {
		$cerberus_db->query("INSERT INTO `industry` VALUES (1, 'Chemical Manufacturing', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (2, 'Containers & Packaging', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (3, 'Fabricated Plastic & Rubber', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (4, 'Forestry & Wood Products', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (5, 'Gold & Silver', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (6, 'Iron & Steel', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (7, 'Metal Mining', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (8, 'Misc. Fabricated Products', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (9, 'Non-Metallic Mining', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (10, 'Paper & Paper Products', 'Basic Materials')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (11, 'Aerospace & Defense', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (12, 'Constr. & Agric. Machinery', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (13, 'Constr. - Supplies & Fixtures', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (14, 'Construction - Raw Materials', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (15, 'Construction Services', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (16, 'Misc. Capital Goods', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (17, 'Mobile Homes & RVs', 'Capital Goods')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (18, 'Conglomerates', 'Conglomerates')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (19, 'Apparel/Accessories', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (20, 'Appliance & Tool', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (21, 'Audio & Video Equipment', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (22, 'Auto & Truck Manufacturers', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (23, 'Auto & Truck Parts', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (24, 'Footwear', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (25, 'Furniture & Fixtures', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (26, 'Jewelry & Silverware', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (27, 'Photography', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (28, 'Recreational Products', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (29, 'Textiles - Non Apparel', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (30, 'Tires', 'Consumer Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (31, 'Beverages (Alcoholic)', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (32, 'Beverages (Non-Alcoholic)', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (33, 'Crops', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (34, 'Fish/Livestock', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (35, 'Food Processing', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (36, 'Office Supplies', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (37, 'Personal & Household Products', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (38, 'Tobacco', 'Consumer Non-Cyclical')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (39, 'Coal', 'Energy')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (40, 'Oil & Gas', 'Energy')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (41, 'Oil Well Services & Equipment', 'Energy')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (42, 'Consumer Financial Services', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (43, 'Insurance (Accident & Health)', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (44, 'Insurance (Life)', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (45, 'Insurance (Miscellaneous)', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (46, 'Insurance (Prop. & Casualty)', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (47, 'Investment Services', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (48, 'Misc. Financial Services', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (49, 'Money Center Banks', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (50, 'Regional Banks', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (51, 'S&Ls/Savings Banks', 'Financial')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (52, 'Biotechnology & Drugs', 'Healthcare')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (53, 'Healthcare Facilities', 'Healthcare')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (54, 'Major Drugs', 'Healthcare')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (55, 'Medical Equipment & Supplies', 'Healthcare')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (56, 'Advertising', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (57, 'Broadcasting & Cable TV', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (58, 'Business Services', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (59, 'Casinos & Gaming', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (60, 'Communications Services', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (61, 'Hotels & Motels', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (62, 'Motion Pictures', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (63, 'Personal Services', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (64, 'Printing & Publishing', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (65, 'Real Estate', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (66, 'Recreational Activities', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (67, 'Rental & Leasing', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (68, 'Restaurants', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (69, 'Retail (Apparel)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (70, 'Retail (Catalog & Mail Order)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (71, 'Retail (Department & Discount)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (72, 'Retail (Drugs)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (73, 'Retail (Grocery)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (74, 'Retail (Home Improvement)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (75, 'Retail (Specialty)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (76, 'Retail (Technology)', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (77, 'Schools', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (78, 'Legal Services', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (79, 'Security Systems & Services', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (80, 'Waste Management Services', 'Services')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (81, 'Communications Equipment', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (82, 'Computer Hardware', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (83, 'Computer Networks', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (84, 'Computer Peripherals', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (85, 'Computer Services', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (86, 'Computer Storage Devices', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (87, 'Electronic Instruments & Control', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (88, 'Office Equipment', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (89, 'Scientific & Technical Instr.', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (90, 'Semiconductors', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (91, 'Software & Programming', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (92, 'Website Hosting', 'Technology')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (93, 'Air Courier', 'Transportation')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (94, 'Airline', 'Transportation')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (95, 'Misc. Transportation', 'Transportation')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (96, 'Railroads', 'Transportation')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (97, 'Trucking', 'Transportation')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (98, 'Water Transportation', 'Transportation')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (99, 'Electric Utilities', 'Utilities')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (100, 'Natural Gas Utilities', 'Utilities')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (101, 'Water Utilities', 'Utilities')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (102, 'Exec., Leg., Gen. Government', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (103, 'Justice, Public Order & Safety', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (104, 'Pub. Finance, Tax & Monetary', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (105, 'Adm. of Human Resource Prog.', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (106, 'Adm. of Environ. & Housing Progs', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (107, 'Adm. of Economic Progs', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (108, 'Nat''l Security & Int''l Affairs', 'Public Administration')");
		$cerberus_db->query("INSERT INTO `industry` VALUES (109, 'Nonclassifiable Establishments', 'Public Administration')");
   }
}

// ***************************************************************************
// `chat_messages` table
// ***************************************************************************
function init_table_chat_messages()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_messages",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_messages` (".
		"  `message_id` int(11) NOT NULL auto_increment,".
		"  `room_id` int(11) NOT NULL default '0',".
		"  `owner_id` int(11) NOT NULL default '0',".
		"  `message_code` tinyint(4) default '0',".
		"  `message_date` bigint(20) NOT NULL default '0',".
		"  `message_prefix` char(32) NOT NULL default '',".
		"  PRIMARY KEY  (`message_id`),".
		"  KEY `room_id` (`room_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["message_id"] = new CER_DB_FIELD("message_id","int(11)","","PRI","","auto_increment");
   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","int(11)","","MUL","0","");
   $TABLE_DEF->fields["owner_id"] = new CER_DB_FIELD("owner_id","int(11)","","","0","");
   $TABLE_DEF->fields["message_code"] = new CER_DB_FIELD("message_code","tinyint(4)","YES","","0","");
   $TABLE_DEF->fields["message_date"] = new CER_DB_FIELD("message_date","bigint(20)","","","0","");
   $TABLE_DEF->fields["message_prefix"] = new CER_DB_FIELD("message_prefix","char(32)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","message_id");
   $TABLE_DEF->indexes["room_id"] = new CER_DB_INDEX("room_id","1","room_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_rooms` table
// ***************************************************************************
function init_table_chat_rooms()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_rooms",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_rooms` (".
		"  `room_id` int(11) NOT NULL auto_increment,".
		"  `room_name` char(32) NOT NULL default '',".
		"  `room_status` int(11) NOT NULL default '0',".
		"  `room_type` enum('visitor','im','meeting') NOT NULL default 'visitor',".
		"  `room_created` bigint(20) default '0',".
		"  PRIMARY KEY  (`room_id`),".
		"  KEY `room_status` (`room_status`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","int(11)","","PRI","","auto_increment");
   $TABLE_DEF->fields["room_name"] = new CER_DB_FIELD("room_name","char(32)","","","","");
   $TABLE_DEF->fields["room_status"] = new CER_DB_FIELD("room_status","int(11)","","MUL","0","");
   $TABLE_DEF->fields["room_type"] = new CER_DB_FIELD("room_type","enum('visitor','im','meeting')","","","visitor","");
   $TABLE_DEF->fields["room_created"] = new CER_DB_FIELD("room_created","bigint(20)","YES","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","room_id");
   $TABLE_DEF->indexes["room_status"] = new CER_DB_INDEX("room_status","1","room_status");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_transcripts` table
// ***************************************************************************
function init_table_chat_transcripts()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_transcripts",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_transcripts` (".
		"  `transcript_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `transcript_date` bigint(20) NOT NULL default '0',".
		"  `room_id` bigint(20) unsigned NOT NULL default '0',".
		"  `room_name` char(128) NOT NULL default '',".
		"  `department_id` int(11) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`transcript_id`),".
		"  KEY `transcript_date` (`transcript_date`),".
		"  KEY `room_id` (`room_id`),".
		"  KEY `department_id` (`department_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["transcript_id"] = new CER_DB_FIELD("transcript_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["transcript_date"] = new CER_DB_FIELD("transcript_date","bigint(20)","","MUL","0","");
   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["room_name"] = new CER_DB_FIELD("room_name","char(128)","","","","");
   $TABLE_DEF->fields["department_id"] = new CER_DB_FIELD("department_id","int(11) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","transcript_id");
   $TABLE_DEF->indexes["transcript_date"] = new CER_DB_INDEX("transcript_date","1","transcript_date");
   $TABLE_DEF->indexes["room_id"] = new CER_DB_INDEX("room_id","1","room_id");
   $TABLE_DEF->indexes["department_id"] = new CER_DB_INDEX("department_id","1","department_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_visitor_chat_requests` table
// ***************************************************************************
function init_table_chat_visitor_chat_requests()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_visitor_chat_requests",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_visitor_chat_requests` (".
		"  `chat_request_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `visitor_id` bigint(20) unsigned default NULL,".
		"  `request_time_start` bigint(20) default NULL,".
		"  `request_time_heartbeat` bigint(20) default '0',".
		"  `room_id` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`chat_request_id`),".
		"  KEY `visitor_id` (`visitor_id`),".
		"  KEY `accepting_agent_id` (`room_id`),".
		"  KEY `room_id` (`room_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["chat_request_id"] = new CER_DB_FIELD("chat_request_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","bigint(20) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["request_time_start"] = new CER_DB_FIELD("request_time_start","bigint(20)","YES","","","");
   $TABLE_DEF->fields["request_time_heartbeat"] = new CER_DB_FIELD("request_time_heartbeat","bigint(20)","YES","","0","");
   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","bigint(20) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","chat_request_id");
   $TABLE_DEF->indexes["visitor_id"] = new CER_DB_INDEX("visitor_id","1","visitor_id");
   $TABLE_DEF->indexes["accepting_agent_id"] = new CER_DB_INDEX("accepting_agent_id","1","room_id");
   $TABLE_DEF->indexes["room_id"] = new CER_DB_INDEX("room_id","1","room_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_visitor_pages` table
// ***************************************************************************
function init_table_chat_visitor_pages()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_visitor_pages",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_visitor_pages` (".
		"  `page_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `page_url_id` bigint(20) unsigned default '0',".
		"  `page_name` char(255) default NULL,".
		"  `visitor_id` bigint(20) unsigned NOT NULL default '0',".
		"  `page_timestamp` bigint(20) default NULL,".
		"  `page_referrer_url_id` bigint(20) unsigned NOT NULL default '0',".
		"  `page_referrer_host_id` int(10) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`page_id`),".
		"  KEY `visitor_id` (`visitor_id`),".
		"  KEY `page_url_id` (`page_url_id`),".
		"  KEY `page_referrer_url_id` (`page_referrer_url_id`),".
		"  KEY `page_referrer_host_id` (`page_referrer_host_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["page_id"] = new CER_DB_FIELD("page_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["page_url_id"] = new CER_DB_FIELD("page_url_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["page_timestamp"] = new CER_DB_FIELD("page_timestamp","bigint(20)","YES","","","");
   $TABLE_DEF->fields["page_referrer_url_id"] = new CER_DB_FIELD("page_referrer_url_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["page_referrer_host_id"] = new CER_DB_FIELD("page_referrer_host_id","int(10) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","page_id");
   $TABLE_DEF->indexes["visitor_id"] = new CER_DB_INDEX("visitor_id","1","visitor_id");
   $TABLE_DEF->indexes["page_url_id"] = new CER_DB_INDEX("page_url_id","1","page_url_id");
   $TABLE_DEF->indexes["page_referrer_url_id"] = new CER_DB_INDEX("page_referrer_url_id","1","page_referrer_url_id");
   $TABLE_DEF->indexes["page_referrer_host_id"] = new CER_DB_INDEX("page_referrer_host_id","1","page_referrer_host_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_visitors` table
// ***************************************************************************
function init_table_chat_visitors()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_visitors",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_visitors` (".
		"  `visitor_id` int(11) NOT NULL auto_increment,".
		"  `visitor_name` char(16) default NULL,".
		"  `visitor_hash` char(32) default NULL,".
		"  `visitor_sid` char(32) default NULL,".
		"  `visitor_ip` char(16) default NULL,".
		"  `visitor_host_id` int(10) unsigned default NULL,".
		"  `visitor_browser_id` int(10) unsigned default NULL,".
		"  `visitor_time_start` bigint(20) default NULL,".
		"  `visitor_time_latest` bigint(20) default NULL,".
		"  `visitor_question` char(255) default NULL,".
		"  PRIMARY KEY  (`visitor_id`),".
		"  KEY `visitor_sid` (`visitor_sid`),".
		"  KEY `visitor_hash` (`visitor_hash`),".
		"  KEY `visitor_host_id` (`visitor_host_id`),".
		"  KEY `visitor_browser_id` (`visitor_browser_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","int(11)","","PRI","","auto_increment");
   $TABLE_DEF->fields["visitor_name"] = new CER_DB_FIELD("visitor_name","char(16)","YES","","","");
   $TABLE_DEF->fields["visitor_hash"] = new CER_DB_FIELD("visitor_hash","char(32)","YES","MUL","","");
   $TABLE_DEF->fields["visitor_sid"] = new CER_DB_FIELD("visitor_sid","char(32)","YES","MUL","","");
   $TABLE_DEF->fields["visitor_ip"] = new CER_DB_FIELD("visitor_ip","char(16)","YES","","","");
   $TABLE_DEF->fields["visitor_host_id"] = new CER_DB_FIELD("visitor_host_id","int(10) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["visitor_browser_id"] = new CER_DB_FIELD("visitor_browser_id","int(10) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["visitor_time_start"] = new CER_DB_FIELD("visitor_time_start","bigint(20)","YES","","","");
   $TABLE_DEF->fields["visitor_time_latest"] = new CER_DB_FIELD("visitor_time_latest","bigint(20)","YES","","","");
   $TABLE_DEF->fields["visitor_question"] = new CER_DB_FIELD("visitor_question","char(255)","YES","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","visitor_id");
   $TABLE_DEF->indexes["visitor_sid"] = new CER_DB_INDEX("visitor_sid","1","visitor_sid");
   $TABLE_DEF->indexes["visitor_hash"] = new CER_DB_INDEX("visitor_hash","1","visitor_hash");
   $TABLE_DEF->indexes["visitor_host_id"] = new CER_DB_INDEX("visitor_host_id","1","visitor_host_id");
   $TABLE_DEF->indexes["visitor_browser_id"] = new CER_DB_INDEX("visitor_browser_id","1","visitor_browser_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_visitors_to_invites` table
// ***************************************************************************
function init_table_chat_visitors_to_invites()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_visitors_to_invites",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_visitors_to_invites` (".
		"  `invite_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `invite_date` bigint(20) NOT NULL default '0',".
		"  `visitor_id` bigint(20) unsigned NOT NULL default '0',".
		"  `agent_id` bigint(20) unsigned NOT NULL default '0',".
		"  `invite_message` text,".
		"  PRIMARY KEY  (`invite_id`),".
		"  KEY `visitor_id` (`visitor_id`),".
		"  KEY `agent_id` (`agent_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["invite_id"] = new CER_DB_FIELD("invite_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["invite_date"] = new CER_DB_FIELD("invite_date","bigint(20)","","","0","");
   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["invite_message"] = new CER_DB_FIELD("invite_message","text","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","invite_id");
   $TABLE_DEF->indexes["visitor_id"] = new CER_DB_INDEX("visitor_id","1","visitor_id");
   $TABLE_DEF->indexes["agent_id"] = new CER_DB_INDEX("agent_id","1","agent_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `chat_visitors_to_rooms` table
// ***************************************************************************
function init_table_chat_visitors_to_rooms()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("chat_visitors_to_rooms",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `chat_visitors_to_rooms` (".
		"  `visitor_id` bigint(20) unsigned default NULL,".
		"  `room_id` bigint(20) unsigned default NULL,".
		"  `last_update` bigint(20) default '0',".
		"  KEY `room_id` (`room_id`),".
		"  KEY `visitor_id` (`visitor_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","bigint(20) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["room_id"] = new CER_DB_FIELD("room_id","bigint(20) unsigned","YES","MUL","","");
   $TABLE_DEF->fields["last_update"] = new CER_DB_FIELD("last_update","bigint(20)","YES","","0","");

   $TABLE_DEF->indexes["room_id"] = new CER_DB_INDEX("room_id","1","room_id");
   $TABLE_DEF->indexes["visitor_id"] = new CER_DB_INDEX("visitor_id","1","visitor_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `user_extended_info` table
// ***************************************************************************
function init_table_user_extended_info()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("user_extended_info",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `user_extended_info` (".
		"  `user_id` int(10) unsigned NOT NULL default '0',".
		"  `chat_display_name` char(32) NOT NULL default '',".
		"  `notification_event_mask` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`user_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","PRI","0","");
   $TABLE_DEF->fields["chat_display_name"] = new CER_DB_FIELD("chat_display_name","char(32)","","","","");
   $TABLE_DEF->fields["notification_event_mask"] = new CER_DB_FIELD("notification_event_mask","bigint(20) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","user_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `department` table
// ***************************************************************************
function init_table_department()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("department",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `department` (".
		"  `department_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,".
		"  `department_name` char(32) NOT NULL default '',".
		"  `department_usage` bigint(20) NOT NULL default '0',".
		"  `department_offline_address` char(64) NOT NULL,".
		"  PRIMARY KEY  (`department_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["department_id"] = new CER_DB_FIELD("department_id","int(11) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["department_name"] = new CER_DB_FIELD("department_name","char(32)","","","","");
   $TABLE_DEF->fields["department_usage"] = new CER_DB_FIELD("department_usage","bigint(20)","","","0","");
   $TABLE_DEF->fields["department_offline_address"] = new CER_DB_FIELD("department_offline_address","char(64)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","department_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `department_teams` table
// ***************************************************************************
function init_table_department_teams()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("department_teams",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `department_teams` (".
		"  `department_id` int(11) NOT NULL default '0',".
		"  `team_id` int(11) NOT NULL default '0',".
		"  UNIQUE KEY `department_id` (`department_id`,`team_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["department_id"] = new CER_DB_FIELD("department_id","int(11)","","PRI","0","");
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(11)","","PRI","0","");

   $TABLE_DEF->indexes["department_id"] = new CER_DB_INDEX("department_id","0",array("department_id","team_id"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `stat_browsers` table
// ***************************************************************************
function init_table_stat_browsers()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("stat_browsers",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `stat_browsers` (".
		"`browser_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,".
		"`browser` CHAR( 128 ) NOT NULL ,".
		"PRIMARY KEY ( `browser_id` ) ,".
		"UNIQUE ( `browser` )".
		")";

   $TABLE_DEF->fields["browser_id"] = new CER_DB_FIELD("browser_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["browser"] = new CER_DB_FIELD("browser","char(128)","","UNI","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("browser_id"));
   $TABLE_DEF->indexes["browser"] = new CER_DB_INDEX("browser","0",array("browser"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `stat_hosts` table
// ***************************************************************************
function init_table_stat_hosts()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("stat_hosts",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `stat_hosts` (".
		"`host_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,".
		"`host` CHAR( 96 ) NOT NULL ,".
		"PRIMARY KEY ( `host_id` ) ,".
		"UNIQUE ( `host` )".
		")";

   $TABLE_DEF->fields["host_id"] = new CER_DB_FIELD("host_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["host"] = new CER_DB_FIELD("host","char(96)","","UNI","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("host_id"));
   $TABLE_DEF->indexes["host"] = new CER_DB_INDEX("host","0",array("host"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `stat_urls` table
// ***************************************************************************
function init_table_stat_urls()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("stat_urls",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `stat_urls` (".
		"`url_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,".
		"`url` CHAR( 255 ) NOT NULL ,".
		"PRIMARY KEY ( `url_id` ) ,".
		"UNIQUE ( `url` )".
		")";

   $TABLE_DEF->fields["url_id"] = new CER_DB_FIELD("url_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["url"] = new CER_DB_FIELD("url","char(255)","","UNI","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("url_id"));
   $TABLE_DEF->indexes["url"] = new CER_DB_INDEX("url","0",array("url"));

   table($TABLE_DEF);

}


// ***************************************************************************
// `team` table
// ***************************************************************************
function init_table_team()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("team",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `team` (".
		"  `team_id` int(11) NOT NULL auto_increment,".
		"  `team_name` char(32) NOT NULL default '',".
		"  `team_acl1` bigint(20) NOT NULL default '0',".
		"  `team_acl2` bigint(20) NOT NULL default '0',".
		"  `team_acl3` bigint(20) NOT NULL default '0',".
		"  PRIMARY KEY  (`team_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(11)","","PRI","","auto_increment");
   $TABLE_DEF->fields["team_name"] = new CER_DB_FIELD("team_name","char(32)","","","","");
   $TABLE_DEF->fields["team_acl1"] = new CER_DB_FIELD("team_acl1","bigint(20)","","","0","");
   $TABLE_DEF->fields["team_acl2"] = new CER_DB_FIELD("team_acl2","bigint(20)","","","0","");
   $TABLE_DEF->fields["team_acl3"] = new CER_DB_FIELD("team_acl3","bigint(20)","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","team_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `team_members` table
// ***************************************************************************
function init_table_team_members()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("team_members",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `team_members` (".
		"  `member_id` int(10) unsigned NOT NULL auto_increment,".
		"  `team_id` int(11) NOT NULL default '0',".
		"  `agent_id` int(11) NOT NULL default '0',".
		"  `ticket_pull` tinyint(3) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`member_id`),".
		"  KEY `team_id` (`team_id`,`agent_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["member_id"] = new CER_DB_FIELD("member_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(11)","","MUL","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","int(11)","","","0","");
   $TABLE_DEF->fields["ticket_pull"] = new CER_DB_FIELD("ticket_pull","tinyint(3) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","member_id");
   $TABLE_DEF->indexes["team_id"] = new CER_DB_INDEX("team_id","1",array("team_id","agent_id"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `team_queues` table
// ***************************************************************************
function init_table_team_queues()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("team_queues",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `team_queues` (".
		"  `team_id` int(11) NOT NULL default '0',".
		"  `queue_id` int(11) NOT NULL default '0',".
		"  `queue_access` tinyint(3) unsigned NOT NULL default '0',".
		"  UNIQUE KEY `team_id` (`team_id`,`queue_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(11)","","PRI","0","");
   $TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","PRI","0","");
   $TABLE_DEF->fields["queue_access"] = new CER_DB_FIELD("queue_access","tinyint(3) unsigned","","","0","");

   $TABLE_DEF->indexes["team_id"] = new CER_DB_INDEX("team_id","0",array("team_id","queue_id"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `gateway_session` table
// ***************************************************************************
function init_table_gateway_session()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("gateway_session",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `gateway_session` (".
		"  `session_id` bigint(20) NOT NULL auto_increment,".
		"  `user_id` int(11) NOT NULL default '0',".
		"  `php_sid_cookie` varchar(64) NOT NULL default '',".
		"  `ip_address` int(11) NOT NULL default '0',".
		"  `creation_timestamp` bigint(20) NOT NULL default '0',".
		"  `login_timestamp` bigint(20) NOT NULL default '0',".
		"  `last_timestamp` bigint(20) NOT NULL default '0',".
		"  `requests` bigint(20) NOT NULL default '0',".
		"  `session_data` longtext,".
		"  `chat_status` tinyint(3) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`session_id`),".
		"  KEY `session_checker` (`user_id`,`php_sid_cookie`,`ip_address`),".
		"  KEY `php_sid_cookie` (`php_sid_cookie`),".
		"  KEY `ip_address` (`ip_address`),".
		"  KEY `ip_address_2` (`ip_address`,`php_sid_cookie`),".
		"  KEY `last_timestamp` (`last_timestamp`),".
		"  KEY `chat_status` (`chat_status`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["session_id"] = new CER_DB_FIELD("session_id","bigint(20)","","PRI","","auto_increment");
   $TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(11)","","MUL","0","");
   $TABLE_DEF->fields["php_sid_cookie"] = new CER_DB_FIELD("php_sid_cookie","varchar(64)","","MUL","","");
   $TABLE_DEF->fields["ip_address"] = new CER_DB_FIELD("ip_address","int(11)","","MUL","0","");
   $TABLE_DEF->fields["creation_timestamp"] = new CER_DB_FIELD("creation_timestamp","bigint(20)","","","0","");
   $TABLE_DEF->fields["login_timestamp"] = new CER_DB_FIELD("login_timestamp","bigint(20)","","","0","");
   $TABLE_DEF->fields["last_timestamp"] = new CER_DB_FIELD("last_timestamp","bigint(20)","","MUL","0","");
   $TABLE_DEF->fields["requests"] = new CER_DB_FIELD("requests","bigint(20)","","","0","");
   $TABLE_DEF->fields["session_data"] = new CER_DB_FIELD("session_data","longtext","","","","");
   $TABLE_DEF->fields["chat_status"] = new CER_DB_FIELD("chat_status","tinyint(3) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","session_id");
   $TABLE_DEF->indexes["session_checker"] = new CER_DB_INDEX("session_checker","1",array("user_id","php_sid_cookie","ip_address"));
   $TABLE_DEF->indexes["php_sid_cookie"] = new CER_DB_INDEX("php_sid_cookie","1","php_sid_cookie");
   $TABLE_DEF->indexes["ip_address"] = new CER_DB_INDEX("ip_address","1","ip_address");
   $TABLE_DEF->indexes["ip_address_2"] = new CER_DB_INDEX("ip_address_2","1",array("ip_address","php_sid_cookie"));
   $TABLE_DEF->indexes["last_timestamp"] = new CER_DB_INDEX("last_timestamp","1","last_timestamp");
   $TABLE_DEF->indexes["chat_status"] = new CER_DB_INDEX("chat_status","1","chat_status");

   table($TABLE_DEF);

}

// ***************************************************************************
// `heartbeat_event_payload` table
// ***************************************************************************
function init_table_heartbeat_event_payload()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("heartbeat_event_payload",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `heartbeat_event_payload` (".
		"  `event_id` bigint(20) unsigned NOT NULL default '0',".
		"  `payload` text,".
		"  PRIMARY KEY  (`event_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["event_id"] = new CER_DB_FIELD("event_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["payload"] = new CER_DB_FIELD("payload","text","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","event_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `heartbeat_event_queue` table
// ***************************************************************************
function init_table_heartbeat_event_queue()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("heartbeat_event_queue",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `heartbeat_event_queue` (".
		"  `event_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `user_id` int(10) unsigned NOT NULL default '0',".
		"  `event_type` bigint(20) unsigned NOT NULL default '0',".
		"  `expiration` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`event_id`),".
		"  KEY `user_id` (`user_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["event_id"] = new CER_DB_FIELD("event_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","MUL","0","");
   $TABLE_DEF->fields["event_type"] = new CER_DB_FIELD("event_type","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["expiration"] = new CER_DB_FIELD("expiration","bigint(20) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","event_id");
   $TABLE_DEF->indexes["user_id"] = new CER_DB_INDEX("user_id","1","user_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `dispatcher_suggestions` table
// ***************************************************************************
function init_table_dispatcher_suggestions()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("dispatcher_suggestions",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `dispatcher_suggestions` (".
		"  `suggestion_id` int(10) unsigned NOT NULL auto_increment,".
		"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
		"  `member_id` int(11) NOT NULL default '0',".
		"  `timestamp` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`suggestion_id`),".
		"  UNIQUE KEY `ticket_id` (`ticket_id`,`member_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["suggestion_id"] = new CER_DB_FIELD("suggestion_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["member_id"] = new CER_DB_FIELD("member_id","int(11)","","","0","");
   $TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","bigint(20) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","suggestion_id");
   $TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","0",array("ticket_id","member_id"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `dispatcher_delays` table
// ***************************************************************************
function init_table_dispatcher_delays()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("dispatcher_delays",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `dispatcher_delays` (".
		"  `delay_id` int(10) unsigned NOT NULL auto_increment,".
		"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
		"  `agent_id` int(10) unsigned NOT NULL default '0',".
		"  `delay_type` tinyint(4) NOT NULL default '0',".
		"  `added_timestamp` bigint(20) unsigned NOT NULL default '0',".
		"  `expire_timestamp` bigint(20) unsigned NOT NULL default '0',".
		"  `reason` char(255) NOT NULL default '',".
		"  PRIMARY KEY  (`delay_id`),".
		"  UNIQUE KEY `ticket_id` (`ticket_id`,`agent_id`,`delay_type`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["delay_id"] = new CER_DB_FIELD("delay_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","int(10) unsigned","","","0","");
   $TABLE_DEF->fields["delay_type"] = new CER_DB_FIELD("delay_type","tinyint(4)","","","0","");
   $TABLE_DEF->fields["added_timestamp"] = new CER_DB_FIELD("added_timestamp","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["expire_timestamp"] = new CER_DB_FIELD("expire_timestamp","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["reason"] = new CER_DB_FIELD("reason","char(255)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","delay_id");
   $TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","0",array("ticket_id","agent_id","delay_type"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `opportunity` table
// ***************************************************************************
function init_table_opportunity()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("opportunity",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `opportunity` (".
		"  `opportunity_id` int(10) unsigned NOT NULL auto_increment,".
		"  `owner_id` int(10) unsigned NOT NULL default '0',".
		"  `team_id` int(10) unsigned NOT NULL default '0',".
		"  `company_id` int(10) unsigned NOT NULL default '0',".
		"  `opportunity_name` char(64) NOT NULL default '',".
		"  `source` enum('Cold Call','Existing Customer','Self Generated','Employee','Partner','Direct Mail','Conference','Trade Show','Web Site','Word of Mouth') NOT NULL default 'Cold Call',".
		"  `amount_currency` tinyint(3) unsigned NOT NULL default '0',".
		"  `amount` decimal(10,2) NOT NULL default '0.00',".
		"  `close_date` bigint(20) unsigned NOT NULL default '0',".
		"  `probability` tinyint(3) unsigned NOT NULL default '0',".
		"  `stage` enum('Prospecting','Qualification','Proposal','Negotiation','Closed Won','Closed Lost') NOT NULL default 'Prospecting',".
		"  `import_source` char(10) NOT NULL default '',".
		"  `import_id` char(32) NOT NULL default '',".
		"  `created_date` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`opportunity_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["opportunity_id"] = new CER_DB_FIELD("opportunity_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["owner_id"] = new CER_DB_FIELD("owner_id","int(10) unsigned","","","0","");
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(10) unsigned","","","0","");
   $TABLE_DEF->fields["company_id"] = new CER_DB_FIELD("company_id","int(10) unsigned","","","0","");
   $TABLE_DEF->fields["opportunity_name"] = new CER_DB_FIELD("opportunity_name","char(64)","","","","");
   $TABLE_DEF->fields["source"] = new CER_DB_FIELD("source","enum('cold call','existing customer','self generated','employee','partner','direct mail','conference','trade show','web site','word of mouth')","","","Cold Call","");
   $TABLE_DEF->fields["amount_currency"] = new CER_DB_FIELD("amount_currency","tinyint(3) unsigned","","","0","");
   $TABLE_DEF->fields["amount"] = new CER_DB_FIELD("amount","decimal(10,2)","","","0.00","");
   $TABLE_DEF->fields["close_date"] = new CER_DB_FIELD("close_date","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["probability"] = new CER_DB_FIELD("probability","tinyint(3) unsigned","","","0","");
   $TABLE_DEF->fields["stage"] = new CER_DB_FIELD("stage","enum('prospecting','qualification','proposal','negotiation','closed won','closed lost')","","","Prospecting","");
	$TABLE_DEF->fields["import_source"] = new CER_DB_FIELD("import_source","char(10)","","","","");
	$TABLE_DEF->fields["import_id"] = new CER_DB_FIELD("import_id","char(32)","","","","");
	$TABLE_DEF->fields["created_date"] = new CER_DB_FIELD("created_date","bigint(20) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","opportunity_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `saved_reports` table
// ***************************************************************************
function init_table_saved_reports() {
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("saved_reports",false);

	$TABLE_DEF->create_sql = "CREATE TABLE `saved_reports` (".
		"`report_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,".
		"`report_name` CHAR( 32 ) NOT NULL ,".
		"`report_category` INT ( 10 ) NOT NULL ,".
		"`report_data` TEXT,".
		"PRIMARY KEY ( `report_id` )".
		");";

   $TABLE_DEF->fields["report_id"] = new CER_DB_FIELD("report_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["report_name"] = new CER_DB_FIELD("report_name","char(32)","","","","");
   $TABLE_DEF->fields["report_category"] = new CER_DB_FIELD("report_category","int(10)","","","","");
   $TABLE_DEF->fields["report_data"] = new CER_DB_FIELD("report_data","text","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","report_id");

   table($TABLE_DEF);
	
}

// ***************************************************************************
// `user_prefs_xml` table
// ***************************************************************************
function init_table_user_prefs_xml() {
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("user_prefs_xml",false);

	$TABLE_DEF->create_sql = "CREATE TABLE `user_prefs_xml` (".
		"`user_id` INT(11) UNSIGNED DEFAULT 0 NOT NULL ,".
		"`workspace_id` INT(11) UNSIGNED DEFAULT 0 NOT NULL ,".
		"`pref_id` INT(11) UNSIGNED DEFAULT 0 NOT NULL ,".
		"`pref_xml` TEXT,".
		"UNIQUE pref_key (".
		"`user_id` ,".
		"`workspace_id` ,".
		"`pref_id`".
		")".
		");";

   $TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(11) unsigned","","PRI","0","");
   $TABLE_DEF->fields["workspace_id"] = new CER_DB_FIELD("workspace_id","int(11) unsigned","","PRI","0","");
   $TABLE_DEF->fields["pref_id"] = new CER_DB_FIELD("pref_id","int(11) unsigned","","PRI","0","");
   $TABLE_DEF->fields["pref_xml"] = new CER_DB_FIELD("pref_xml","text","","","","");

   $TABLE_DEF->indexes["pref_key"] = new CER_DB_INDEX("pref_key","0",array("user_id","workspace_id","pref_id"));

   table($TABLE_DEF);	
}

// ***************************************************************************
// `skill` table
// ***************************************************************************
function init_table_skill()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("skill",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `skill` (".
		"  `skill_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `skill_name` char(32) NOT NULL default '',".
		"  `skill_description` char(255) NOT NULL default '',".
		"  `skill_category_id` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`skill_id`),".
		"  KEY `skill_category_id` (`skill_category_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["skill_id"] = new CER_DB_FIELD("skill_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["skill_name"] = new CER_DB_FIELD("skill_name","char(32)","","","","");
   $TABLE_DEF->fields["skill_description"] = new CER_DB_FIELD("skill_description","char(255)","","","","");
   $TABLE_DEF->fields["skill_category_id"] = new CER_DB_FIELD("skill_category_id","bigint(20) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","skill_id");
   $TABLE_DEF->indexes["skill_category_id"] = new CER_DB_INDEX("skill_category_id","1","skill_category_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `skill_category` table
// ***************************************************************************
function init_table_skill_category()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("skill_category",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `skill_category` (".
		"  `category_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `category_name` char(32) NOT NULL default '',".
		"  `category_parent_id` bigint(20) unsigned NOT NULL default '0',".
		"  PRIMARY KEY  (`category_id`),".
		"  KEY `category_parent_id` (`category_parent_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["category_id"] = new CER_DB_FIELD("category_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["category_name"] = new CER_DB_FIELD("category_name","char(32)","","","","");
   $TABLE_DEF->fields["category_parent_id"] = new CER_DB_FIELD("category_parent_id","bigint(20) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","category_id");
   $TABLE_DEF->indexes["category_parent_id"] = new CER_DB_INDEX("category_parent_id","1","category_parent_id");

   table($TABLE_DEF);

}

// ***************************************************************************
// `skill_to_agent` table
// ***************************************************************************
function init_table_skill_to_agent()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("skill_to_agent",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `skill_to_agent` (".
		"  `skill_id` bigint(20) unsigned NOT NULL default '0',".
		"  `agent_id` bigint(20) unsigned NOT NULL default '0',".
		"  `has_skill` tinyint(3) unsigned NOT NULL default '0',".
		"  UNIQUE KEY `agent_to_skill` (`skill_id`,`agent_id`),".
		"  KEY `has_skill` ( `has_skill` , `agent_id` )".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["skill_id"] = new CER_DB_FIELD("skill_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["has_skill"] = new CER_DB_FIELD("has_skill","tinyint(3) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["agent_to_skill"] = new CER_DB_INDEX("agent_to_skill","0",array("skill_id","agent_id"));
   $TABLE_DEF->indexes["has_skill"] = new CER_DB_INDEX("has_skill","1",array("has_skill","agent_id"));

   table($TABLE_DEF);

}

// ***************************************************************************
// `skill_to_ticket` table
// ***************************************************************************
function init_table_skill_to_ticket()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("skill_to_ticket",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `skill_to_ticket` (".
		"  `skill_id` bigint(20) unsigned NOT NULL default '0',".
		"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
		"  UNIQUE KEY `skill_to_ticket` (`skill_id`,`ticket_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["skill_id"] = new CER_DB_FIELD("skill_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");

   $TABLE_DEF->indexes["skill_to_ticket"] = new CER_DB_INDEX("skill_to_ticket","0",array("skill_id","ticket_id"));

   table($TABLE_DEF);

}
?>
