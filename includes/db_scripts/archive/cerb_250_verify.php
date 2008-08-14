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
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|       Ben Halsted         (ben@webgroupmedia.com)         [BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.5.0 Database Verification Script");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20040901");
define("DB_SCRIPT_ONE_RUN","false");
define("DB_SCRIPT_PRECURSOR","");
define("DB_SCRIPT_TYPE","verify");

function cer_init()
{
	init_table_address();
	init_table_company();
	init_table_configuration();
	init_table_db_script_hash();
	init_table_email_domains();
	init_table_email_templates();
	init_table_entity_to_field_group();
	init_table_field_group();
	init_table_field_group_bindings();
	init_table_field_group_values();
	init_table_fields();
	init_table_fields_options();
	init_table_knowledgebase();
	init_table_knowledgebase_categories();
	init_table_knowledgebase_comments();
	init_table_knowledgebase_problem();
	init_table_knowledgebase_ratings();
	init_table_knowledgebase_solution();
	init_table_log();
	init_table_merge_forward();
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
	init_table_schedule();
	init_table_search_index();
	init_table_search_index_exclude();
	init_table_search_index_kb();
	init_table_search_words();
	init_table_session();
	init_table_session_vars();
	init_table_sla();
	init_table_sla_to_queue();
	init_table_spam_bayes_index();
	init_table_spam_bayes_stats();	
	init_table_stats_system();
	init_table_tasks();
	init_table_tasks_notes();
	init_table_tasks_projects();
	init_table_tasks_projects_categories();
	init_table_thread();
	init_table_thread_attachments();
	init_table_thread_attachments_parts();
	init_table_thread_attachments_temp();
	init_table_thread_content_part();
	init_table_thread_errors();
	init_table_ticket();
	init_table_ticket_audit_log();
	init_table_thread_time_tracking();
	init_table_ticket_views();
	init_table_trigram();
	init_table_trigram_stats();
	init_table_trigram_to_kb();
	init_table_trigram_to_ticket();
	init_table_trigram_training();
	init_table_user();
	init_table_user_access_levels();
	init_table_user_layout();
	init_table_user_login_log();
	init_table_user_notification();
	init_table_user_prefs();
	init_table_user_sig();
	init_table_war_check();
	init_table_whos_online();
	
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
	
//	if(!$TBL->check(false))
//		create_table($TBL,$TABLE_DEF); // create
	
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
	foreach($TABLE_DEF->fields as $idx => $fld)
		$TBL->verify_field($fld->field_name,$fld->field_type,$fld->field_null,$fld->field_key,$fld->field_default,$fld->field_extra,false);
		
	// check for index validity
	foreach($TABLE_DEF->indexes as $id => $idx) {
		$TBL->verify_index($idx->index_name, $idx->index_non_unique, $idx->index_fields, false);
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
								"  `name` varchar(128) NOT NULL default '',".
								"  `sla_id` bigint(20) NOT NULL default '0',".
								"  `sla_expire_date` datetime NOT NULL default '0000-00-00 00:00:00',".
								"  `company_account_number` varchar(64) NOT NULL default '',".
								"  `company_mailing_street1` varchar(64) NOT NULL default '',".
								"  `company_mailing_street2` varchar(64) NOT NULL default '',".
								"  `company_mailing_city` varchar(64) NOT NULL default '',".
								"  `company_mailing_state` varchar(64) NOT NULL default '',".
								"  `company_mailing_zip` varchar(64) NOT NULL default '',".
								"  `company_mailing_country` varchar(64) NOT NULL default '',".
								"  `company_phone` varchar(32) NOT NULL default '',".
								"  `company_fax` varchar(32) NOT NULL default '',".
								"  `company_website` varchar(64) NOT NULL default '',".
								"  `company_email` varchar(64) NOT NULL default '',".
								"  PRIMARY KEY  (`id`),".
								"  KEY `sla_id` (`sla_id`),".
								"  KEY `sla_expire_date` (`sla_expire_date`)".
								") TYPE=MyISAM;";
	  	
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","varchar(128)","","","","");
	$TABLE_DEF->fields["sla_id"] = new CER_DB_FIELD("sla_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["sla_expire_date"] = new CER_DB_FIELD("sla_expire_date","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["company_account_number"] = new CER_DB_FIELD("company_account_number","varchar(64)","","","","");
	$TABLE_DEF->fields["company_mailing_street1"] = new CER_DB_FIELD("company_mailing_street1","varchar(64)","","","","");
	$TABLE_DEF->fields["company_mailing_street2"] = new CER_DB_FIELD("company_mailing_street2","varchar(64)","","","","");
	$TABLE_DEF->fields["company_mailing_city"] = new CER_DB_FIELD("company_mailing_city","varchar(64)","","","","");
	$TABLE_DEF->fields["company_mailing_state"] = new CER_DB_FIELD("company_mailing_state","varchar(64)","","","","");
	$TABLE_DEF->fields["company_mailing_zip"] = new CER_DB_FIELD("company_mailing_zip","varchar(64)","","","","");
	$TABLE_DEF->fields["company_mailing_country"] = new CER_DB_FIELD("company_mailing_country","varchar(64)","","","","");
	$TABLE_DEF->fields["company_phone"] = new CER_DB_FIELD("company_phone","varchar(32)","","","","");
	$TABLE_DEF->fields["company_fax"] = new CER_DB_FIELD("company_fax","varchar(32)","","","","");
	$TABLE_DEF->fields["company_website"] = new CER_DB_FIELD("company_website","varchar(64)","","","","");
	$TABLE_DEF->fields["company_email"] = new CER_DB_FIELD("company_email","varchar(64)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
	$TABLE_DEF->indexes["sla_id"] = new CER_DB_INDEX("sla_id","1","sla_id");
	$TABLE_DEF->indexes["sla_expire_date"] = new CER_DB_INDEX("sla_expire_date","1","sla_expire_date");
	
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
		 "`run_date` DATETIME NOT NULL ,".
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
								"  `domain` varchar(128) NOT NULL default '',".
								"  `company_id` bigint(20) NOT NULL default '0',".
								"  PRIMARY KEY  (`id`),".
								"  KEY `company_id` (`company_id`)".
								") TYPE=MyISAM";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["domain"] = new CER_DB_FIELD("domain","varchar(128)","","","","");
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
								"  `template_name` varchar(128) NOT NULL default '',".
								"  `template_description` varchar(255) NOT NULL default '',".
								"  `template_text` text NOT NULL,".
								"  `template_created_by` bigint(20) NOT NULL default '0',".
								"  `template_private` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`template_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["template_id"] = new CER_DB_FIELD("template_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["template_name"] = new CER_DB_FIELD("template_name","varchar(128)","","","","");
	$TABLE_DEF->fields["template_description"] = new CER_DB_FIELD("template_description","varchar(255)","","","","");
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
								"  `order` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`group_instance_id`),".
								"  KEY `group_id` (`group_id`),".
								"  KEY `entity_code` (`entity_code`),".
								"  KEY `entity_index` (`entity_index`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_instance_id"] = new CER_DB_FIELD("group_instance_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["entity_code"] = new CER_DB_FIELD("entity_code","char(1)","","MUL","","");
	$TABLE_DEF->fields["entity_index"] = new CER_DB_FIELD("entity_index","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["order"] = new CER_DB_FIELD("order","tinyint(3) unsigned","","","0","");
	
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
		"`group_template_id` BIGINT UNSIGNED NOT NULL ,".
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
// `fields` table
// ***************************************************************************
function init_table_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("fields",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `fields` (".
								"  `field_id` bigint(20) unsigned NOT NULL auto_increment,".
								"  `field_name` char(64) NOT NULL default '',".
								"  `field_type` char(1) NOT NULL default 'S',".
								"  `field_not_searchable` tinyint(3) unsigned NOT NULL default '0',".
								"  `field_group_id` bigint(20) unsigned NOT NULL default '0',".
								"  `order` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`field_id`),".
								"  KEY `field_not_searchable` (`field_not_searchable`),".
								"  KEY `field_group_id` (`field_group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_name"] = new CER_DB_FIELD("field_name","char(64)","","","","");
	$TABLE_DEF->fields["field_type"] = new CER_DB_FIELD("field_type","char(1)","","","S","");
	$TABLE_DEF->fields["field_not_searchable"] = new CER_DB_FIELD("field_not_searchable","tinyint(3) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_group_id"] = new CER_DB_FIELD("field_group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["order"] = new CER_DB_FIELD("order","tinyint(3) unsigned","","","0","");
	
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
								"  `order` tinyint(3) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`option_id`),".
								"  KEY `field_id` (`field_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["option_id"] = new CER_DB_FIELD("option_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["option_value"] = new CER_DB_FIELD("option_value","char(64)","","","","");
	$TABLE_DEF->fields["order"] = new CER_DB_FIELD("order","tinyint(3) unsigned","","","0","");
	
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
								"  `kb_entry_date` datetime default NULL,".
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
	$TABLE_DEF->fields["kb_entry_date"] = new CER_DB_FIELD("kb_entry_date","datetime","YES","","","");
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
								"  `poster_email` varchar(128) NOT NULL default '',".
								"  `poster_comment` text NOT NULL,".
								"  `poster_ip` varchar(16) NOT NULL default '0.0.0.0',".
								"  PRIMARY KEY  (`kb_comment_id`),".
								"  KEY `kb_article_id` (`kb_article_id`),".
								"  KEY `kb_comment_approved` (`kb_comment_approved`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_comment_id"] = new CER_DB_FIELD("kb_comment_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["kb_comment_approved"] = new CER_DB_FIELD("kb_comment_approved","tinyint(4)","","MUL","0","");
	$TABLE_DEF->fields["kb_comment_date"] = new CER_DB_FIELD("kb_comment_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["poster_email"] = new CER_DB_FIELD("poster_email","varchar(128)","","","","");
	$TABLE_DEF->fields["poster_comment"] = new CER_DB_FIELD("poster_comment","text","","","","");
	$TABLE_DEF->fields["poster_ip"] = new CER_DB_FIELD("poster_ip","varchar(16)","","","0.0.0.0","");
	
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
								"  `kb_problem_summary` varchar(128) NOT NULL default '',".
								"  `kb_problem_text` text NOT NULL,".
								"  `kb_problem_text_is_html` tinyint(4) NOT NULL default '0',".
								"  PRIMARY KEY  (`kb_problem_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_problem_id"] = new CER_DB_FIELD("kb_problem_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_id"]  = new CER_DB_FIELD("kb_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_problem_summary"] = new CER_DB_FIELD("kb_problem_summary","varchar(128)","","","","");
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
								"  `kb_solution_text` text NOT NULL,".
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
								"  `message` text NOT NULL,".
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
			"  `pm_subject` varchar(128) NOT NULL default '',".
			"  `pm_date` datetime NOT NULL default '0000-00-00 00:00:00',".
			"  `pm_folder_id` bigint(20) NOT NULL default '0',".
			"  `pm_message` text NOT NULL,".
			"  `pm_marked_read` tinyint(4) NOT NULL default '0',".
			"  `pm_read_receipt` tinyint(4) NOT NULL default '0',".
			"  `pm_notified` tinyint(4) NOT NULL default '0',".
			"  PRIMARY KEY  (`pm_id`)".
			") TYPE=MyISAM;";
	  
			
	$TABLE_DEF->fields["pm_id"] = new CER_DB_FIELD("pm_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["pm_to_user_id"] = new CER_DB_FIELD("pm_to_user_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["pm_from_user_id"] = new CER_DB_FIELD("pm_from_user_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["pm_subject"] = new CER_DB_FIELD("pm_subject","varchar(128)","","","","");
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
  		"`key_file` text NOT NULL,".
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
		"`key_domains` tinytext NOT NULL,".
  		"`key_maxqueues` int(11) NOT NULL default '0',".
  		"`key_tagline` varchar(255) NOT NULL default '',".
  		"`key_lastupdate` timestamp(14) NOT NULL,".
  		"`key_type` tinyint(4) NOT NULL default '0',".
  		"`key_expiration` datetime NOT NULL default '0000-00-00 00:00:00'".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["key_domains"] = new CER_DB_FIELD("key_domains","tinytext","","","","");
	$TABLE_DEF->fields["key_maxqueues"] = new CER_DB_FIELD("key_maxqueues","int(11)","","","0","");
	$TABLE_DEF->fields["key_tagline"] = new CER_DB_FIELD("key_tagline","varchar(255)","","","","");
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
								"  `group_name` varchar(64) NOT NULL default '',".
								"  `group_fields` text NOT NULL,".
								"  PRIMARY KEY  (`group_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","varchar(64)","","","","");
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
								"  `profile_name` varchar(64) NOT NULL default '',".
								"  `pub_company_name` varchar(128) NOT NULL default '',".
								"  `pub_company_email` varchar(128) NOT NULL default '',".
								"  `pub_queues` text NOT NULL,".
								"  `pub_confirmation_subject` varchar(128) NOT NULL default '',".
								"  `pub_confirmation_body` text NOT NULL,".
								"  `pub_mod_registration` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_registration_mode` varchar(12) NOT NULL default '',".
								"  `pub_mod_kb` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_my_account` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_open_ticket` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_open_ticket_locked` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_track_tickets` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_announcements` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_welcome` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_welcome_title` varchar(64) NOT NULL default '',".
								"  `pub_mod_welcome_text` text NOT NULL,".
								"  `pub_mod_contact` tinyint(4) NOT NULL default '0',".
								"  `pub_mod_contact_text` text NOT NULL,".
								"  `login_plugin_id` bigint(20) unsigned NOT NULL,".
								"  PRIMARY KEY  (`profile_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["profile_id"] = new CER_DB_FIELD("profile_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["profile_name"] = new CER_DB_FIELD("profile_name","varchar(64)","","","","");
	$TABLE_DEF->fields["pub_company_name"] = new CER_DB_FIELD("pub_company_name","varchar(128)","","","","");
	$TABLE_DEF->fields["pub_company_email"] = new CER_DB_FIELD("pub_company_email","varchar(128)","","","","");
	$TABLE_DEF->fields["pub_queues"] = new CER_DB_FIELD("pub_queues","text","","","","");
	$TABLE_DEF->fields["pub_confirmation_subject"] = new CER_DB_FIELD("pub_confirmation_subject","varchar(128)","","","","");
	$TABLE_DEF->fields["pub_confirmation_body"] = new CER_DB_FIELD("pub_confirmation_body","text","","","","");
	$TABLE_DEF->fields["pub_mod_registration"] = new CER_DB_FIELD("pub_mod_registration","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_registration_mode"] = new CER_DB_FIELD("pub_mod_registration_mode","varchar(12)","","","","");
	$TABLE_DEF->fields["pub_mod_kb"] = new CER_DB_FIELD("pub_mod_kb","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_my_account"] = new CER_DB_FIELD("pub_mod_my_account","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_open_ticket"] = new CER_DB_FIELD("pub_mod_open_ticket","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_open_ticket_locked"] = new CER_DB_FIELD("pub_mod_open_ticket_locked","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_track_tickets"] = new CER_DB_FIELD("pub_mod_track_tickets","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_announcements"] = new CER_DB_FIELD("pub_mod_announcements","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_welcome"] = new CER_DB_FIELD("pub_mod_welcome","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_mod_welcome_title"] = new CER_DB_FIELD("pub_mod_welcome_title","varchar(64)","","","","");
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
								"  `full_name` char(64) NOT NULL default '',".
								"  `mailing_street1` char(64) NOT NULL default '',".
								"  `mailing_street2` char(64) NOT NULL default '',".
								"  `mailing_city` char(64) NOT NULL default '',".
								"  `mailing_state` char(64) NOT NULL default '',".
								"  `mailing_zip` char(32) NOT NULL default '',".
								"  `mailing_country` char(64) NOT NULL default '',".
								"  `phone_work` char(32) NOT NULL default '',".
								"  `phone_home` char(32) NOT NULL default '',".
								"  `phone_mobile` char(32) NOT NULL default '',".
								"  `phone_fax` char(32) NOT NULL default '',".
								"  `password` char(32) NOT NULL default '',".
								"  `company_id` bigint(20) unsigned NOT NULL default '0',".
								"  PRIMARY KEY  (`public_user_id`),".
								"  KEY `company_id` (`company_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["public_user_id"] = new CER_DB_FIELD("public_user_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["full_name"] = new CER_DB_FIELD("full_name","char(64)","","","","");
	$TABLE_DEF->fields["mailing_street1"] = new CER_DB_FIELD("mailing_street1","char(64)","","","","");
	$TABLE_DEF->fields["mailing_street2"] = new CER_DB_FIELD("mailing_street2","char(64)","","","","");
	$TABLE_DEF->fields["mailing_city"] = new CER_DB_FIELD("mailing_city","char(64)","","","","");
	$TABLE_DEF->fields["mailing_state"] = new CER_DB_FIELD("mailing_state","char(64)","","","","");
	$TABLE_DEF->fields["mailing_zip"] = new CER_DB_FIELD("mailing_zip","char(32)","","","","");
	$TABLE_DEF->fields["mailing_country"] = new CER_DB_FIELD("mailing_country","char(64)","","","","");
	$TABLE_DEF->fields["phone_work"] = new CER_DB_FIELD("phone_work","char(32)","","","","");
	$TABLE_DEF->fields["phone_home"] = new CER_DB_FIELD("phone_home","char(32)","","","","");
	$TABLE_DEF->fields["phone_mobile"] = new CER_DB_FIELD("phone_mobile","char(32)","","","","");
	$TABLE_DEF->fields["phone_fax"] = new CER_DB_FIELD("phone_fax","char(32)","","","","");
	$TABLE_DEF->fields["password"] = new CER_DB_FIELD("password","char(32)","","","","");
	$TABLE_DEF->fields["company_id"] = new CER_DB_FIELD("company_id","bigint(20) unsigned","","MUL","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","public_user_id");
	$TABLE_DEF->indexes["company_id"] = new CER_DB_INDEX("company_id","1","company_id");
	
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
								"  `queue_name` varchar(32) NOT NULL default '',".
								"  `queue_prefix` varchar(32) NOT NULL default '',".
								"  `queue_response_open` text NOT NULL,".
								"  `queue_response_close` text NOT NULL,".
								"  `queue_send_open` tinyint(4) NOT NULL default '0',".
								"  `queue_send_closed` tinyint(4) NOT NULL default '0',".
								"  `queue_core_update` tinyint(4) NOT NULL default '0',".
								"  `queue_email_display_name` varchar(64) NOT NULL default '',".
								"  `queue_mode` tinyint(4) NOT NULL default '0',".
								"  `queue_response_gated` text NOT NULL,".
								"  `queue_default_schedule` bigint(20) NOT NULL default '0',".
								"  `queue_default_response_time` int(11) NOT NULL default '0',".
								"  `queue_addresses_inherit_qid` bigint(20) NOT NULL default '0',".
								"  PRIMARY KEY  (`queue_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","PRI","","auto_increment");
	$TABLE_DEF->fields["queue_name"] = new CER_DB_FIELD("queue_name","varchar(32)","","","","");
	$TABLE_DEF->fields["queue_prefix"] = new CER_DB_FIELD("queue_prefix","varchar(32)","","","","");
	$TABLE_DEF->fields["queue_response_open"] = new CER_DB_FIELD("queue_response_open","text","","","","");
	$TABLE_DEF->fields["queue_response_close"] = new CER_DB_FIELD("queue_response_close","text","","","","");
	$TABLE_DEF->fields["queue_send_open"] = new CER_DB_FIELD("queue_send_open","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_send_closed"] = new CER_DB_FIELD("queue_send_closed","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_core_update"] = new CER_DB_FIELD("queue_core_update","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_email_display_name"] = new CER_DB_FIELD("queue_email_display_name","varchar(64)","","","","");
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
								"  `queue_address` varchar(128) NOT NULL default '',".
								"  `queue_domain` varchar(128) NOT NULL default '',".
								"  PRIMARY KEY  (`queue_addresses_id`),".
								"  UNIQUE KEY `address_unique` (`queue_address`,`queue_domain`),".
								"  KEY `queue_id` (`queue_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_addresses_id"] = new CER_DB_FIELD("queue_addresses_id","int(11)","","PRI","","auto_increment");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["queue_address"] = new CER_DB_FIELD("queue_address","varchar(128)","","MUL","","");
	$TABLE_DEF->fields["queue_domain"] = new CER_DB_FIELD("queue_domain","varchar(128)","","","","");
	
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
		"`catchall_to_qid` BIGINT UNSIGNED NOT NULL ,".
		"`catchall_order` INT UNSIGNED NOT NULL ,".
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
								"  `action_value` char(128) NOT NULL default '',".
								"  KEY `rule_id` (`rule_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["action_type"] = new CER_DB_FIELD("action_type","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["action_value"] = new CER_DB_FIELD("action_value","char(128)","","","","");
	
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
								"  PRIMARY KEY  (`rule_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["rule_name"] = new CER_DB_FIELD("rule_name","char(128)","","","","");
	$TABLE_DEF->fields["rule_order"] = new CER_DB_FIELD("rule_order","int(10) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","rule_id");
	
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

	$result = $cerberus_db->query("SELECT exclude_id FROM `search_index_exclude` LIMIT 1");
	
	if(0==$cerberus_db->num_rows($result)) {
		$sql = "INSERT IGNORE INTO `search_index_exclude` (exclude_word) VALUES ".
	       "('to'),('is'),('if'),('of'),('or'),('it'),('in'),('do'),('no'),('on'),('id'),('by'),('be'),('us'),".
	       "('the'),('you'),('for'),('any'),('not'),('and'),('llc'),('inc'),('key'),('has'),('let'),('new'),('can'),('was'),('are'),('get'),".
	       "('this'),('have'),('name'),('from'),('that'),('your'),('been'),('know'),('need'),('with'),('mail'),('will'),".
	       "('email'),('please'),('company'),('questions'),('message'),('reply'),('domain'),('thanks'),('contact'),('information'),".
	       "('address'),('internet'),('phone'),('number'),('support'),".
	       "('there'),('might'),('but'),('means'),('our'),('upon'),('all'),('when'),('while'),('among'),('thank'),('now'),('would'),".
	       "('could'),('like'),('just'),('may'),('use'),('again'),('what'),('how'),('where'),('want'),('about')";

    	$TABLE_DEF->run_sql($sql,"<b>Adding exclude words to `search_index_exclude`</b>");
	}

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
								"  `var_name` varchar(64) NOT NULL default '',".
								"  `var_val` text NOT NULL,".
								"  KEY `s_id` (`s_id`),".
								"  KEY `var_name` (`var_name`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["s_id"] = new CER_DB_FIELD("s_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["var_name"] = new CER_DB_FIELD("var_name","varchar(64)","","MUL","","");
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
								"  `task_summary` varchar(255) NOT NULL default '',".
								"  `task_description` text NOT NULL,".
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
	$TABLE_DEF->fields["task_summary"] = new CER_DB_FIELD("task_summary","varchar(255)","","","","");
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
		"note_text text NOT NULL,".
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
								"  `project_name` varchar(128) NOT NULL default '',".
								"  `project_manager_uid` bigint(20) NOT NULL default '0',".
								"  `project_acl` text NOT NULL,".
								"  PRIMARY KEY  (`project_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["project_id"] = new CER_DB_FIELD("project_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["project_name"] = new CER_DB_FIELD("project_name","varchar(128)","","","","");
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
								"  `thread_subject` char(255) default '',".
								"  `thread_to` char(255) default '' NOT NULL,".
								"  `thread_cc` char(255) default '',".
								"  `thread_replyto` char(255) default '',".
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
	$TABLE_DEF->fields["thread_subject"] = new CER_DB_FIELD("thread_subject","char(255)","YES","","","");
	$TABLE_DEF->fields["thread_to"] = new CER_DB_FIELD("thread_to","char(255)","","","","");
	$TABLE_DEF->fields["thread_cc"] = new CER_DB_FIELD("thread_cc","char(255)","YES","","","");
	$TABLE_DEF->fields["thread_replyto"] = new CER_DB_FIELD("thread_replyto","char(255)","YES","","","");
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
								"  `file_name` char(255) NOT NULL default '',".
								"  `file_size` bigint(11) NOT NULL default '0',".
								"  PRIMARY KEY  (`file_id`),".
								"  KEY `thread_id` (`thread_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["file_name"] = new CER_DB_FIELD("file_name","char(255)","","","","");
	$TABLE_DEF->fields["file_size"] = new CER_DB_FIELD("file_size","bigint(11)","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","file_id");
	$TABLE_DEF->indexes["thread_id"] = new CER_DB_INDEX("thread_id","1","thread_id");
	
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
								"  `part_content` mediumblob NOT NULL,".
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
				"  `temp_name` varchar(255) NOT NULL default '',".
				"  `file_name` varchar(255) NOT NULL default '',".
				"  `size` bigint(20) NOT NULL default '0',".
				"  `browser_mimetype` varchar(255) NOT NULL default '',".
				"  PRIMARY KEY  (`file_id`),".
				"  KEY `ticket_id` (`ticket_id`,`user_id`,`file_id`)".
				") TYPE=MyISAM;";

	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","bigint(20)","","","0","");
	$TABLE_DEF->fields["temp_name"] = new CER_DB_FIELD("temp_name","varchar(255)","","","","");
	$TABLE_DEF->fields["file_name"] = new CER_DB_FIELD("file_name","varchar(255)","","","","");
	$TABLE_DEF->fields["size"] = new CER_DB_FIELD("size","bigint(20)","","","0","");
	$TABLE_DEF->fields["browser_mimetype"] = new CER_DB_FIELD("browser_mimetype","varchar(255)","","","","");

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
								"  `error_msg` text NOT NULL,".
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
								"  `ticket_status` enum('new','responded','in progress','info needed','acceptance','on hold','escalated','fixed','resolved','reopened','dead') NOT NULL default 'new',".
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
	$TABLE_DEF->fields["ticket_status"] = new CER_DB_FIELD("ticket_status","enum('new','responded','in progress','info needed','acceptance','on hold','escalated','fixed','resolved','reopened','dead')","","MUL","new","");
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
		"thread_time_summary text NOT NULL, ".
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
								 " `view_name` varchar(64) NOT NULL default '',".
								 " `view_created_by_id` bigint(20) NOT NULL default '0',".
								 " `view_private` tinyint(4) NOT NULL default '0',".
								 " `view_queues` varchar(255) NOT NULL default '',".
								 " `view_columns` text NOT NULL,".
								 " `view_hide_statuses` varchar(255) NOT NULL default '',".
								 " `view_only_assigned` tinyint(4) NOT NULL default '0',".
								 " `view_adv_2line` tinyint(1) NOT NULL default '1',".
								 " `view_adv_controls` tinyint(1) NOT NULL default '1',".
								 " PRIMARY KEY  (`view_id`)".
								") TYPE=MyISAM;";

	$TABLE_DEF->fields["view_id"] = new CER_DB_FIELD("view_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["view_name"] = new CER_DB_FIELD("view_name","varchar(64)","","","","");
	$TABLE_DEF->fields["view_created_by_id"] = new CER_DB_FIELD("view_created_by_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["view_private"] = new CER_DB_FIELD("view_private","tinyint(4)","","","0","");
	$TABLE_DEF->fields["view_queues"] = new CER_DB_FIELD("view_queues","varchar(255)","","","","");
	$TABLE_DEF->fields["view_columns"] = new CER_DB_FIELD("view_columns","text","","","","");
	$TABLE_DEF->fields["view_hide_statuses"] = new CER_DB_FIELD("view_hide_statuses","varchar(255)","","","","");	
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
								"  `trigram` char(30) binary NOT NULL default '',".
								"  PRIMARY KEY  (`trigram_id`),".
								"  UNIQUE KEY `trigram` (`trigram`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["trigram"] = new CER_DB_FIELD("trigram","char(30) binary","","UNI","","");
	
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
		"`user_id` BIGINT UNSIGNED NOT NULL ,".
		"`layout_data` TEXT NOT NULL ,".
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
								"  `notify_options` text NOT NULL,".
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
								"  `gmt_offset` varchar(5) default '0' NOT NULL,".
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
	$TABLE_DEF->fields["gmt_offset"] = new CER_DB_FIELD("gmt_offset","varchar(5)","","","0","");
	
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
								"  `sig_content` text NOT NULL,".
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


?>