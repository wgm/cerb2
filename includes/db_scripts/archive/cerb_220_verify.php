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
| File: cerb_210_clean_install.php
|
| Purpose: Installs a clean 2.1.0 database structure
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once("includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.2.0 Database Verification Script");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20030917");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","");

function cer_init()
{
	init_table_address();
	init_table_address_fields();
	init_table_address_values();
	init_table_company();
	init_table_configuration();
	init_table_db_script_hash();
	init_table_email_domains();
	init_table_email_templates();
	init_table_knowledgebase();
	init_table_knowledgebase_categories();
	init_table_knowledgebase_comments();
	init_table_knowledgebase_problem();
	init_table_knowledgebase_ratings();
	init_table_knowledgebase_solution();
	init_table_merge_forward();
	init_table_log();
	init_table_private_messages();
	init_table_product_key();
	init_table_product_key_infoy();
	init_table_queue();
	init_table_queue_access();
	init_table_queue_addresses();
	init_table_requestor();
	init_table_rule_action();
	init_table_rule_entry();
	init_table_rule_fov();
	init_table_search_index();
	init_table_search_index_exclude();
	init_table_search_index_kb();
	init_table_search_words();
	init_table_session();
	init_table_session_vars();
	init_table_sla();
	init_table_tasks();
	init_table_tasks_projects();
	init_table_tasks_projects_categories();
	init_table_tasks_notes();
	init_table_thread();
	init_table_thread_attachments();
	init_table_thread_attachments_parts();
	init_table_thread_attachments_temp();
	init_table_thread_content();
	init_table_thread_errors();
	init_table_ticket();
	init_table_ticket_audit_log();
	init_table_ticket_fields();
	init_table_ticket_id_masks();
	init_table_ticket_values();
	init_table_ticket_views();
	init_table_user();
	init_table_user_access_levels();
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
	
	foreach($TABLE_DEF->fields as $idx => $fld)
		$verify_fields[$fld->field_name] = DB_FIELD;
		
	$warn_fields = $TBL->verify_table($verify_fields);
	
	if(count($warn_fields[0])) print_extra_fields($TBL,$warn_fields);
	if(count($warn_fields[1])) print_missing_fields($TBL,$warn_fields);
								
	foreach($TABLE_DEF->fields as $idx => $fld)
		$TBL->verify_field($fld->field_name,$fld->field_type,$fld->field_null,$fld->field_key,$fld->field_default,$fld->field_extra,false);
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

// ***************************************************************************
// `address` table
// ***************************************************************************
function init_table_address()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `address` (".
		"address_id int(10) unsigned NOT NULL auto_increment,".
		"address_password char(30) NOT NULL default '',".
		"address_address char(128) NOT NULL default '',".
		"address_banned tinyint(4) NOT NULL default '0',".
		"company_id bigint(20) NOT NULL default '0',".
		"PRIMARY KEY  (address_id),".
		"UNIQUE KEY address_address (address_address),".
		"KEY company_id (company_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["address_password"] = new CER_DB_FIELD("address_password","char(30)","","","","");
	$TABLE_DEF->fields["address_address"] = new CER_DB_FIELD("address_address","char(128)","","UNI","","");
	$TABLE_DEF->fields["address_banned"] = new CER_DB_FIELD("address_banned","tinyint(4)","","","0","");
	$TABLE_DEF->fields["company_id"] = new CER_DB_FIELD("company_id","bigint(20)","","MUL","0","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `address_fields` table
// ***************************************************************************
function init_table_address_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address_fields",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `address_fields` (".
		"`field_id` bigint(20) NOT NULL auto_increment,".
		"`field_name` varchar(64) NOT NULL default '',".
		"`field_type` enum('S','T','D') NOT NULL default 'S',".
		"`field_options` text NOT NULL,".
		"PRIMARY KEY  (`field_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_name"] = new CER_DB_FIELD("field_name","varchar(64)","","","","");
	$TABLE_DEF->fields["field_type"] = new CER_DB_FIELD("field_type","enum('s','t','d')","","","S","");
	$TABLE_DEF->fields["field_options"] = new CER_DB_FIELD("field_options","text","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `address_values` table
// ***************************************************************************
function init_table_address_values()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address_values",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `address_values` (".
		"`value_id` bigint(20) NOT NULL auto_increment,".
		"`field_id` bigint(20) NOT NULL default '0',".
		"`address_id` bigint(20) NOT NULL default '0',".
		"`value_text` text NOT NULL,".
		"PRIMARY KEY  (`value_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["value_id"] = new CER_DB_FIELD("value_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["value_text"] = new CER_DB_FIELD("value_text","text","","","","");
	
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
		"`id` bigint(20) unsigned NOT NULL auto_increment,".
		"`name` varchar(128) NOT NULL default '',".
		"`sla_id` bigint(20) NOT NULL default '0',".
		"PRIMARY KEY  (`id`),".
		"KEY `id` (`id`),".
		"KEY `sla_id` (`sla_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","varchar(128)","","","","");
	$TABLE_DEF->fields["sla_id"] = new CER_DB_FIELD("sla_id","bigint(20)","","MUL","0","");
	
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
		"`cfg_id` bigint(1) NOT NULL default '1',".
		"`auto_add_cc_reqs` tinyint(4) NOT NULL DEFAULT '0',".
		"`bcc_watchers` TINYINT DEFAULT '0' NOT NULL,".
		"`customer_ticket_history_max` tinyint(4) NOT NULL default '0',".
		"`debug_mode` tinyint(4) NOT NULL default '0',".
		"`default_language` char(3) NOT NULL default '',".
		"`demo` tinyint(4) NOT NULL default '0',".
		"`enable_audit_log` tinyint(4) NOT NULL default '0',".
		"`enable_customer_history` tinyint(4) NOT NULL default '0',".
		"`enable_id_masking` TINYINT DEFAULT '1' NOT NULL,".
		"`enable_panel_stats` tinyint(4) NOT NULL default '0',".
		"`gui_version` char(16) NOT NULL default '',".
		"`kb_editors_enabled` tinyint(4) NOT NULL default '0',".
		"`mail_delivery` char(8) NOT NULL default '',".
		"`ob_callback` char(64) NOT NULL default '',".
		"`overdue_hours` int(11) NOT NULL default '0',".
		"`parser_secure_enabled` tinyint(4) NOT NULL default '0',".
		"`parser_secure_user` char(64) NOT NULL default '',".
		"`parser_secure_password` char(64) NOT NULL default '',".
		"`satellite_enabled` tinyint(4) NOT NULL default '0',".
		"`session_ip_security` TINYINT DEFAULT '0' NOT NULL,".
		"`sendmail` tinyint(4) NOT NULL default '0',".
		"`session_lifespan` int(4) NOT NULL default '720',".
		"`show_kb` tinyint(4) NOT NULL default '0',".
		"`show_kb_topic_totals` tinyint(4) NOT NULL default '0',".
		"`smtp_server` char(64) NOT NULL default '',".
		"`time_adjust` bigint(20) NOT NULL default '0',".
		"`track_sid_url` tinyint(4) NOT NULL default '0',".
		"`warcheck_secs` int NOT NULL default '10',".
		"`who_max_idle_mins` int(11) NOT NULL default '0',".
		"`watcher_assigned_tech` tinyint(4) NOT NULL default '0',".
		"`watcher_from_user` tinyint(4) NOT NULL default '0',".
		"`not_to_self` tinyint(4) NOT NULL default '0',".
		"`watcher_no_system_attach` TINYINT DEFAULT '0' NOT NULL,".
		"`xsp_url` CHAR(255) NOT NULL,".
		"`xsp_login` CHAR(64) NOT NULL,".
		"`xsp_password` CHAR(64) NOT NULL,".
		"PRIMARY KEY  (`cfg_id`)".
		") TYPE=MyISAM;"; 
		
	$TABLE_DEF->fields["cfg_id"] = new CER_DB_FIELD("cfg_id","bigint(1)","","PRI","1","");
	$TABLE_DEF->fields["auto_add_cc_reqs"] = new CER_DB_FIELD("auto_add_cc_reqs","tinyint(4)","","","0","");
	$TABLE_DEF->fields["bcc_watchers"] = new CER_DB_FIELD("bcc_watchers","tinyint(4)","","","0","");
	$TABLE_DEF->fields["customer_ticket_history_max"] = new CER_DB_FIELD("customer_ticket_history_max","tinyint(4)","","","0","");
	$TABLE_DEF->fields["debug_mode"] = new CER_DB_FIELD("debug_mode","tinyint(4)","","","0","");
	$TABLE_DEF->fields["default_language"] = new CER_DB_FIELD("default_language","char(3)","","","","");
	$TABLE_DEF->fields["demo"] = new CER_DB_FIELD("demo","tinyint(4)","","","0","");
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
	$TABLE_DEF->fields["xsp_url"] = new CER_DB_FIELD("xsp_url","char(255)","","","","");
	$TABLE_DEF->fields["xsp_login"] = new CER_DB_FIELD("xsp_login","char(64)","","","","");
	$TABLE_DEF->fields["xsp_password"] = new CER_DB_FIELD("xsp_password","char(64)","","","","");
	
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
		"`id` bigint(20) unsigned NOT NULL auto_increment,".
		"`domain` varchar(128) NOT NULL default '',".
		"`company_id` bigint(20) NOT NULL default '0',".
		"PRIMARY KEY  (`id`),".
		"KEY `id` (`id`),".
		"KEY `company_id` (`company_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["domain"] = new CER_DB_FIELD("domain","varchar(128)","","","","");
	$TABLE_DEF->fields["company_id"] = new CER_DB_FIELD("company_id","bigint(20)","","MUL","0","");
	
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
		"`template_id` bigint(20) unsigned NOT NULL auto_increment,".
		"`template_name` varchar(128) NOT NULL default '',".
		"`template_description` varchar(255) NOT NULL default '',".
		"`template_text` text NOT NULL,".
		"`template_created_by` bigint(20) NOT NULL default '0',".
		"`template_private` tinyint(4) NOT NULL default '0',".
		"PRIMARY KEY  (`template_id`),".
		"KEY `template_id` (`template_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["template_id"] = new CER_DB_FIELD("template_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["template_name"] = new CER_DB_FIELD("template_name","varchar(128)","","","","");
	$TABLE_DEF->fields["template_description"] = new CER_DB_FIELD("template_description","varchar(255)","","","","");
	$TABLE_DEF->fields["template_text"] = new CER_DB_FIELD("template_text","text","","","","");
	$TABLE_DEF->fields["template_created_by"] = new CER_DB_FIELD("template_created_by","bigint(20)","","","0","");
	$TABLE_DEF->fields["template_private"] = new CER_DB_FIELD("template_private","tinyint(4)","","","0","");
	
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
		"`kb_id` int(10) unsigned NOT NULL auto_increment,".
		"`kb_entry_date` datetime default NULL,".
		"`kb_entry_user` int(10) unsigned NOT NULL default '0',".
		"`kb_category_id` int(10) unsigned NOT NULL default '0',".
		"`kb_keywords` char(255) NOT NULL default '',".
		"`kb_public` tinyint(3) unsigned NOT NULL default '0',".
		"PRIMARY KEY  (`kb_id`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_entry_date"] = new CER_DB_FIELD("kb_entry_date","datetime","YES","","","");
	$TABLE_DEF->fields["kb_entry_user"] = new CER_DB_FIELD("kb_entry_user","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_category_id"] = new CER_DB_FIELD("kb_category_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_keywords"] = new CER_DB_FIELD("kb_keywords","char(255)","","","","");
	$TABLE_DEF->fields["kb_public"] = new CER_DB_FIELD("kb_public","tinyint(3) unsigned","","","0","");
	
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
		"`kb_category_id` int(10) unsigned NOT NULL auto_increment,".
		"`kb_category_name` char(32) NOT NULL default '',".
		"`kb_category_parent_id` int(10) unsigned NOT NULL default '0',".
		"PRIMARY KEY  (`kb_category_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_category_id"] = new CER_DB_FIELD("kb_category_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_category_name"] = new CER_DB_FIELD("kb_category_name","char(32)","","","","");
	$TABLE_DEF->fields["kb_category_parent_id"] = new CER_DB_FIELD("kb_category_parent_id","int(10) unsigned","","","0","");
	
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
  		"`kb_comment_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`kb_article_id` bigint(20) unsigned NOT NULL default '0',".
    	"`kb_comment_approved` tinyint(4) NOT NULL default '0',".
  		"`kb_comment_date` datetime NOT NULL default '0000-00-00 00:00:00',".
  		"`poster_email` varchar(128) NOT NULL default '',".
  		"`poster_comment` text NOT NULL,".
  		"`poster_ip` varchar(16) NOT NULL default '0.0.0.0',".
  		"PRIMARY KEY  (`kb_comment_id`),".
  		"KEY `kb_article_id` (`kb_article_id`),".
  		"KEY `kb_comment_approved` (`kb_comment_approved`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_comment_id"] = new CER_DB_FIELD("kb_comment_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["kb_comment_approved"] = new CER_DB_FIELD("kb_comment_approved","tinyint(4)","","MUL","0","");
	$TABLE_DEF->fields["kb_comment_date"] = new CER_DB_FIELD("kb_comment_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["poster_email"] = new CER_DB_FIELD("poster_email","varchar(128)","","","","");
	$TABLE_DEF->fields["poster_comment"] = new CER_DB_FIELD("poster_comment","text","","","","");
	$TABLE_DEF->fields["poster_ip"] = new CER_DB_FIELD("poster_ip","varchar(16)","","","0.0.0.0","");
	
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
  		"`kb_problem_id` int(10) unsigned NOT NULL auto_increment,".
  		"`kb_id` int(10) unsigned NOT NULL default '0',".
  		"`kb_problem_summary` varchar(128) NOT NULL default '',".
  		"`kb_problem_text` text NOT NULL,".
  		"PRIMARY KEY  (`kb_problem_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["kb_problem_id"] = new CER_DB_FIELD("kb_problem_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_id"]  = new CER_DB_FIELD("kb_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_problem_summary"] = new CER_DB_FIELD("kb_problem_summary","varchar(128)","","","","");
	$TABLE_DEF->fields["kb_problem_text"] = new CER_DB_FIELD("kb_problem_text","text","","","","");

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
  		"`rating_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`kb_article_id` bigint(20) NOT NULL default '0',".
  		"`ip_addr` char(16) NOT NULL default '',".
  		"`rating` tinyint(4) NOT NULL default '0',".
  		"PRIMARY KEY  (`rating_id`),".
  		"UNIQUE KEY `kb_article_id` (`kb_article_id`,`ip_addr`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["rating_id"] = new CER_DB_FIELD("rating_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["ip_addr"] = new CER_DB_FIELD("ip_addr","char(16)","","","","");
	$TABLE_DEF->fields["rating"] = new CER_DB_FIELD("rating","tinyint(4)","","","0","");

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
  		"`kb_solution_id` int(10) unsigned NOT NULL auto_increment,".
  		"`kb_id` int(10) unsigned NOT NULL default '0',".
  		"`kb_solution_text` text NOT NULL,".
  		"PRIMARY KEY  (`kb_solution_id`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["kb_solution_id"] = new CER_DB_FIELD("kb_solution_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["kb_solution_text"] = new CER_DB_FIELD("kb_solution_text","text","","","","");

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
  		"`log_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`message` text NOT NULL,".
  		"`log_date` timestamp(14) NOT NULL,".
  		"PRIMARY KEY  (`log_id`),".
  		"KEY `log_id` (`log_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["log_id"] = new CER_DB_FIELD("log_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["message"] = new CER_DB_FIELD("message","text","","","","");
	$TABLE_DEF->fields["log_date"] = new CER_DB_FIELD("log_date","timestamp(14)","YES","","","");
	
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
		"from_ticket bigint(20) unsigned NOT NULL default '0',".
		"to_ticket bigint(20) unsigned NOT NULL default '0',".
		"UNIQUE KEY merge_pair (from_ticket,to_ticket)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["from_ticket"] = new CER_DB_FIELD("from_ticket","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["to_ticket"] = new CER_DB_FIELD("to_ticket","bigint(20) unsigned","","PRI","0","");
	
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
// `private_messages` table
// ***************************************************************************
function init_table_private_messages()
{
	global $cerberus_db;
	
	$TABLE_PM = new CER_DB_TABLE("private_messages");
	
	if($TABLE_PM->table_exists) {
		echo "<b>Table `private_messages` already exists ...</b> skipping.<br>";
		return false;
	}

	$sql = "CREATE TABLE private_messages (".
	  "pm_id bigint(20) unsigned NOT NULL auto_increment,".
	  "pm_to_user_id bigint(20) NOT NULL default '0',".
	  "pm_from_user_id bigint(20) NOT NULL default '0',".
	  "pm_subject varchar(128) NOT NULL default '',".
	  "pm_date datetime NOT NULL default '0000-00-00 00:00:00',".
	  "pm_folder_id bigint(20) NOT NULL default '0',".
	  "pm_message text NOT NULL,".
	  "pm_marked_read tinyint(4) NOT NULL default '0',".
	  "pm_read_receipt tinyint(4) NOT NULL default '0',".
	  "pm_notified tinyint(4) NOT NULL default '0',".
	  "PRIMARY KEY (pm_id)".
	  ") TYPE=MyISAM;";
	  
	$TABLE_PM->run_sql($sql,"<b>Creating `private_messages` table</b>");
}

// ***************************************************************************
// `product_key_info` table
// ***************************************************************************
function init_table_product_key_infoy()
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
// `queue` table
// ***************************************************************************
function init_table_queue()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `queue` (".
  		"`queue_id` int(11) NOT NULL auto_increment,".
  		"`queue_name` varchar(32) NOT NULL default '',".
  		"`queue_prefix` varchar(32) NOT NULL default '',".
  		"`queue_response_open` text NOT NULL,".
  		"`queue_response_close` text NOT NULL,".
  		"`queue_send_open` tinyint(4) NOT NULL default '0',".
  		"`queue_send_closed` tinyint(4) NOT NULL default '0',".
  		"`queue_restricted` tinyint(4) NOT NULL default '0',".
  		"`queue_response_restricted` text NOT NULL,".
  		"`queue_core_update` tinyint(4) NOT NULL default '0',".
  		"PRIMARY KEY  (`queue_id`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","PRI","","auto_increment");
	$TABLE_DEF->fields["queue_name"] = new CER_DB_FIELD("queue_name","varchar(32)","","","","");
	$TABLE_DEF->fields["queue_prefix"] = new CER_DB_FIELD("queue_prefix","varchar(32)","","","","");
	$TABLE_DEF->fields["queue_response_open"] = new CER_DB_FIELD("queue_response_open","text","","","","");
	$TABLE_DEF->fields["queue_response_close"] = new CER_DB_FIELD("queue_response_close","text","","","","");
	$TABLE_DEF->fields["queue_send_open"] = new CER_DB_FIELD("queue_send_open","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_send_closed"] = new CER_DB_FIELD("queue_send_closed","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_restricted"] = new CER_DB_FIELD("queue_restricted","tinyint(4)","","","0","");
	$TABLE_DEF->fields["queue_response_restricted"] = new CER_DB_FIELD("queue_response_restricted","text","","","","");
	$TABLE_DEF->fields["queue_core_update"] = new CER_DB_FIELD("queue_core_update","tinyint(4)","","","0","");
	
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
  		"`queue_id` int(11) NOT NULL default '0',".
  		"`user_id` int(11) NOT NULL default '0',".
  		"`queue_access` enum('read','write') NOT NULL default 'read',".
  		"`queue_watch` tinyint(1) NOT NULL default '0',".
  		"KEY `queue_id` (`queue_id`),".
  		"KEY `user_id` (`user_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["queue_access"] = new CER_DB_FIELD("queue_access","enum('read','write')","","","read","");
	$TABLE_DEF->fields["queue_watch"] = new CER_DB_FIELD("queue_watch","tinyint(1)","","","0","");

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
  		"`queue_addresses_id` int(11) NOT NULL auto_increment,".
  		"`queue_id` int(11) NOT NULL default '0',".
  		"`queue_address` varchar(128) NOT NULL default '',".
  		"`queue_domain` varchar(128) NOT NULL default '',".
  		"PRIMARY KEY  (`queue_addresses_id`),".
  		"KEY `queue_id` (`queue_id`),".
  		"UNIQUE `address_unique` ( `queue_address` , `queue_domain` )".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_addresses_id"] = new CER_DB_FIELD("queue_addresses_id","int(11)","","PRI","","auto_increment");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["queue_address"] = new CER_DB_FIELD("queue_address","varchar(128)","","MUL","","");
	$TABLE_DEF->fields["queue_domain"] = new CER_DB_FIELD("queue_domain","varchar(128)","","","","");
	
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
		"ticket_id bigint(20) unsigned NOT NULL default '0',".
		"address_id int(10) unsigned default '0',".
		"suppress tinyint(4) NOT NULL default '0',".
		"UNIQUE KEY ticket_and_address (ticket_id,address_id),".
		"KEY ticket_id (ticket_id),".
		"KEY address_id (address_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","int(10) unsigned","YES","MUL","0","");
	$TABLE_DEF->fields["suppress"] = new CER_DB_FIELD("suppress","tinyint(4)","","","0","");

	
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
  		"`rule_id` bigint(20) unsigned NOT NULL default '0',".
		"`action_type` int(10) unsigned NOT NULL default '0',".
  		"`action_value` char(128) NOT NULL default '',".
  		"KEY `rule_id` (`rule_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["action_type"] = new CER_DB_FIELD("action_type","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["action_value"] = new CER_DB_FIELD("action_value","char(128)","","","","");
	
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
  		"`rule_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`rule_name` char(128) NOT NULL default '',".
  		"`rule_order` int(10) unsigned NOT NULL default '0',".
  		"PRIMARY KEY  (`rule_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["rule_name"] = new CER_DB_FIELD("rule_name","char(128)","","","","");
	$TABLE_DEF->fields["rule_order"] = new CER_DB_FIELD("rule_order","int(10) unsigned","","","0","");
	
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
  		"`rule_id` bigint(20) unsigned NOT NULL default '0',".
  		"`fov_field` int(10) unsigned NOT NULL default '0',".
  		"`fov_oper` int(10) unsigned NOT NULL default '0',".
  		"`fov_value` char(128) NOT NULL default '',".
  		"KEY `rule_id` (`rule_id`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["rule_id"] = new CER_DB_FIELD("rule_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["fov_field"] = new CER_DB_FIELD("fov_field","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["fov_oper"] = new CER_DB_FIELD("fov_oper","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["fov_value"] = new CER_DB_FIELD("fov_value","char(128)","","","","");
	
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
  		"`word_id` bigint(20) NOT NULL default '0',".
  		"`ticket_id` bigint(20) NOT NULL default '0',".
  		"`in_subject` tinyint(4) NOT NULL default '0',".
  		"UNIQUE KEY `word_id` (`word_id`,`ticket_id`),".
  		"KEY `in_subject` (`in_subject`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["in_subject"] = new CER_DB_FIELD("in_subject","tinyint(4)","","MUL","0","");
	
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
		"`exclude_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`exclude_word` CHAR( 25 ) NOT NULL ,".
		"PRIMARY KEY ( `exclude_id` ) ,".
		"UNIQUE (".
		"`exclude_word` ".
		")".
		");";

	$TABLE_DEF->fields["exclude_id"] = new CER_DB_FIELD("exclude_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["exclude_word"] = new CER_DB_FIELD("exclude_word","char(25)","","UNI","","");

	table($TABLE_DEF);

	$sql = "INSERT IGNORE INTO `search_index_exclude` (exclude_word) VALUES ".
	       "('to'),('is'),('if'),('of'),('or'),('it'),('in'),('do'),('no'),('on'),('id'),('by'),('be'),('us'),".
	       "('the'),('you'),('for'),('any'),('not'),('and'),('llc'),('inc'),('key'),('has'),('let'),('new'),('can'),('was'),('are'),('get'),".
	       "('this'),('have'),('name'),('from'),('that'),('your'),('been'),('know'),('need'),('with'),('mail'),('will'),".
	       "('email'),('please'),('company'),('questions'),('message'),('reply'),('domain'),('thanks'),('contact'),('information'),".
	       "('address'),('internet'),('phone'),('number'),('support'),".
	       "('there'),('might'),('but'),('means'),('our'),('upon'),('all'),('when'),('while'),('among'),('thank'),('now'),('would'),".
	       "('could'),('like'),('just'),('may'),('use'),('again')";

    $TABLE_DEF->run_sql($sql,"<b>Adding exclude words to `search_index_exclude`</b>");

}

// ***************************************************************************
// `search_index_kb` table
// ***************************************************************************
function init_table_search_index_kb()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index_kb",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `search_index_kb` (".
  		"`word_id` bigint(20) NOT NULL default '0',".
  		"`kb_article_id` bigint(20) NOT NULL default '0',".
  		"`in_topic` tinyint(4) NOT NULL default '0',".
  		"UNIQUE KEY `word_id` (`word_id`,`kb_article_id`),".
  		"KEY `in_topic` (`in_topic`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["kb_article_id"] = new CER_DB_FIELD("kb_article_id","bigint(20)","","PRI","0","");
	$TABLE_DEF->fields["in_topic"] = new CER_DB_FIELD("in_topic","tinyint(4)","","MUL","0","");
	
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
  		"`word_id` bigint(20) NOT NULL auto_increment,".
  		"`word` char(45) NOT NULL default '',".
  		"PRIMARY KEY  (`word_id`),".
  		"UNIQUE KEY `word` (`word`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["word"] = new CER_DB_FIELD("word","char(45)","","UNI","","");
	
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
  		"`s_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`session_id` char(32) NOT NULL default '',".
  		"`session_ip` char(16) NOT NULL default '',".
  		"`session_timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
  		"PRIMARY KEY  (`s_id`),".
  		"UNIQUE KEY `session_id` (`session_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["s_id"] = new CER_DB_FIELD("s_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["session_id"] = new CER_DB_FIELD("session_id","char(32)","","UNI","","");
	$TABLE_DEF->fields["session_ip"] = new CER_DB_FIELD("session_ip","char(16)","","","","");
	$TABLE_DEF->fields["session_timestamp"] = new CER_DB_FIELD("session_timestamp","datetime","","","0000-00-00 00:00:00","");
	
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
  		"`s_id` bigint(20) unsigned NOT NULL default '0',".
  		"`var_name` varchar(64) NOT NULL default '',".
  		"`var_val` text NOT NULL,".
  		"KEY `s_id` (`s_id`),".
  		"KEY `var_name` (`var_name`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["s_id"] = new CER_DB_FIELD("s_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["var_name"] = new CER_DB_FIELD("var_name","varchar(64)","","MUL","","");
	$TABLE_DEF->fields["var_val"] = new CER_DB_FIELD("var_val","text","","","","");
	
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
  		"`id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`name` char(64) NOT NULL default '',".
  		"`queues` char(255) NOT NULL default '',".
  		"PRIMARY KEY  (`id`),".
  		"KEY `id` (`id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","char(64)","","","","");
	$TABLE_DEF->fields["queues"] = new CER_DB_FIELD("queues","char(255)","","","","");
	
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
		"task_id bigint(20) unsigned NOT NULL auto_increment,".
		"task_summary varchar(255) NOT NULL default '',".
		"task_description text NOT NULL,".
		"task_progress tinyint(4) NOT NULL default '0',".
		"task_created_uid bigint(20) NOT NULL default '0',".
		"task_assigned_uid bigint(20) NOT NULL default '0',".
		"task_priority tinyint(4) NOT NULL default '0',".
		"task_parent_id bigint(20) NOT NULL default '0',".
		"task_project_id bigint(20) NOT NULL default '0',".
		"task_project_category_id bigint(20) NOT NULL default '0',".
		"task_classification tinyint(4) NOT NULL default '0',".
		"task_created_date datetime NOT NULL default '0000-00-00 00:00:00',".
		"task_updated_date datetime NOT NULL default '0000-00-00 00:00:00',".
		"task_due_date datetime NOT NULL default '0000-00-00 00:00:00',".
		"task_reminder_date datetime NOT NULL default '0000-00-00 00:00:00',".
		"task_reminder_sent tinyint(4) NOT NULL default '0',".
		"PRIMARY KEY  (task_id)".
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
		"project_id bigint(20) unsigned NOT NULL auto_increment,".
		"project_name varchar(128) NOT NULL default '',".
		"project_manager_uid bigint(20) NOT NULL default '0',".
		"project_acl text NOT NULL,".
		"PRIMARY KEY  (project_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["project_id"] = new CER_DB_FIELD("project_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["project_name"] = new CER_DB_FIELD("project_name","varchar(128)","","","","");
	$TABLE_DEF->fields["project_manager_uid"] = new CER_DB_FIELD("project_manager_uid","bigint(20)","","","0","");
	$TABLE_DEF->fields["project_acl"] = new CER_DB_FIELD("project_acl","text","","","","");
	
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
		"`category_id` BIGINT NOT NULL AUTO_INCREMENT,".
		"`project_id` BIGINT NOT NULL ,".
		"`category_name` CHAR( 128 ) NOT NULL ,".
		"PRIMARY KEY ( `category_id` ) ,".
		"INDEX ( `project_id` ) ".
		");";
	  
	$TABLE_DEF->fields["category_id"] = new CER_DB_FIELD("category_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["project_id"] = new CER_DB_FIELD("project_id","bigint(20)","","MUL","0","");
	$TABLE_DEF->fields["category_name"] = new CER_DB_FIELD("category_name","char(128)","","","","");
	
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
  		"`ticket_id` int(11) NOT NULL default '0',".
  		"`thread_id` int(10) unsigned NOT NULL auto_increment,".
  		"`thread_message_id` char(255) NOT NULL default '',".
  		"`thread_inreplyto_id` int(11) NOT NULL default '0',".
  		"`thread_address_id` int(11) NOT NULL default '0',".
  		"`thread_type` enum('email','comment','forward') NOT NULL default 'email',".
  		"`thread_date` datetime NOT NULL default '0000-00-00 00:00:00',".
  		"`thread_time_worked` smallint(6) NOT NULL default '0',".
  		"`thread_bytes` int(11) NOT NULL default '0',".
  		"`thread_subject` char(255) default '',".
  		"`thread_cc` char(255) default '',".
  		"`thread_replyto` char(255) default '',".
  		"PRIMARY KEY  (`thread_id`),".
  		"KEY `ticket_sender_id` (`thread_address_id`),".
  		"KEY `ticket_id` (`ticket_id`),".
  		"KEY `thread_id` (`thread_id`),".
  		"KEY `thread_address_id` (`thread_address_id`),".
  		"KEY `thread_inreplyto_id` (`thread_inreplyto_id`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["thread_message_id"] = new CER_DB_FIELD("thread_message_id","char(255)","","","","");
	$TABLE_DEF->fields["thread_inreplyto_id"] = new CER_DB_FIELD("thread_inreplyto_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["thread_address_id"] = new CER_DB_FIELD("thread_address_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["thread_type"] = new CER_DB_FIELD("thread_type","enum('email','comment','forward')","","","email","");
	$TABLE_DEF->fields["thread_date"] = new CER_DB_FIELD("thread_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["thread_time_worked"] = new CER_DB_FIELD("thread_time_worked","smallint(6)","","","0","");
	$TABLE_DEF->fields["thread_bytes"] = new CER_DB_FIELD("thread_bytes","int(11)","","","0","");
	$TABLE_DEF->fields["thread_subject"] = new CER_DB_FIELD("thread_subject","char(255)","YES","","","");
	$TABLE_DEF->fields["thread_cc"] = new CER_DB_FIELD("thread_cc","char(255)","YES","","","");
	$TABLE_DEF->fields["thread_replyto"] = new CER_DB_FIELD("thread_replyto","char(255)","YES","","","");
	
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
		"`thread_id` BIGINT UNSIGNED NOT NULL ,".
		"`file_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`file_name` CHAR( 255 ) NOT NULL ,".
		"`file_size` BIGINT( 11 ) NOT NULL ,".
		"PRIMARY KEY ( `file_id` ) ,".
		"INDEX ( `thread_id` )".
		");";
	  
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["file_name"] = new CER_DB_FIELD("file_name","char(255)","","","","");
	$TABLE_DEF->fields["file_size"] = new CER_DB_FIELD("file_size","bigint(11)","","","0","");
	
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
		"`part_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`file_id` BIGINT UNSIGNED NOT NULL ,".
		"`part_content` MEDIUMBLOB NOT NULL ,".
		"PRIMARY KEY ( `part_id` ) ,".
		"INDEX ( `file_id` ) ".
		");";
	  
	$TABLE_DEF->fields["part_id"] = new CER_DB_FIELD("part_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["file_id"] = new CER_DB_FIELD("file_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["part_content"] = new CER_DB_FIELD("part_content","mediumblob","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `thread_attachments_temp` table
// ***************************************************************************
function init_table_thread_attachments_temp()
{
	global $cerberus_db;
	
	$TABLE_ATTACH_TEMP = new CER_DB_TABLE("thread_attachments_temp");
	
	if($TABLE_ATTACH_TEMP->table_exists) {
		echo "<b>Table `thread_attachments_temp` already exists ...</b> skipping.<br>";
		return false;
	}

	$sql = "CREATE TABLE `thread_attachments_temp` (".
	  "`file_id` bigint(20) unsigned NOT NULL auto_increment,".
	  "`ticket_id` bigint(20) unsigned NOT NULL default '0',".
	  "`user_id` int(10) unsigned NOT NULL default '0',".
	  "`timestamp` bigint(20) NOT NULL default '0',".
	  "`temp_name` varchar(255) NOT NULL default '',".
	  "`file_name` varchar(255) NOT NULL default '',".
	  "`size` bigint(20) NOT NULL default '0',".
	  "`browser_mimetype` varchar(255) NOT NULL default '',".
	  "PRIMARY KEY  (`file_id`),".
	  "KEY `ticket_id` (`ticket_id`,`user_id`,`file_id`)".
	  ") TYPE=MyISAM;";

	$TABLE_ATTACH_TEMP->run_sql($sql,"<b>Creating `thread_attachments_temp` table</b>");
}

// ***************************************************************************
// `thread_content` table
// ***************************************************************************
function init_table_thread_content()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_content",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_content` (".
  		"`thread_id` int(11) NOT NULL default '0',".
  		"`content_id` int(10) unsigned NOT NULL auto_increment,".
  		"`content_content` text NOT NULL,".
  		"PRIMARY KEY  (`content_id`),".
  		"KEY `thread_id` (`thread_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","int(11)","","MUL","0","");
	$TABLE_DEF->fields["content_id"] = new CER_DB_FIELD("content_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["content_content"] = new CER_DB_FIELD("content_content","text","","","","");
	
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
	"`error_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
	"`ticket_id` BIGINT UNSIGNED NOT NULL ,".
	"`thread_id` BIGINT UNSIGNED NOT NULL ,".
	"`error_msg` TEXT NOT NULL ,".
	"PRIMARY KEY ( `error_id` ), ".
	"INDEX (`ticket_id`),".
	"INDEX (`thread_id`)".
	");";
	  
	$TABLE_DEF->fields["error_id"] = new CER_DB_FIELD("error_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["error_msg"] = new CER_DB_FIELD("error_msg","text","","","","");
	
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
  		"`ticket_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`ticket_subject` char(128) NOT NULL default '',".
  		"`ticket_date` datetime NOT NULL default '0000-00-00 00:00:00',".
  		"`ticket_priority` tinyint(4) NOT NULL default '0',".
  		"`ticket_status` enum('new','responded','in progress','info needed','acceptance','on hold','escalated','fixed','resolved','reopened','dead') NOT NULL default 'new',".
  		"`last_update_date` timestamp(14) NOT NULL,".
  		"`last_update_user_id` int(10) unsigned NOT NULL default '0',".
  		"`ticket_assigned_to_id` int(10) unsigned NOT NULL default '0',".
  		"`ticket_queue_id` int(10) unsigned NOT NULL default '0',".
  		"`queue_addresses_id` int(11) NOT NULL default '0',".
  		"`ticket_reopenings` smallint(6) NOT NULL default '0',".
  		"`min_thread_id` bigint(21) default NULL,".
  		"`max_thread_id` bigint(21) default NULL,".
  		"PRIMARY KEY  (`ticket_id`),".
  		"KEY `ticket_id` (`ticket_id`),".
  		"KEY `ticket_queue_id` (`ticket_queue_id`),".
  		"KEY `min_thread_id` (`min_thread_id`),".
  		"KEY `max_thread_id` (`max_thread_id`)".
		") TYPE=MyISAM AUTO_INCREMENT=1;";

	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_subject"] = new CER_DB_FIELD("ticket_subject","char(128)","","","","");
	$TABLE_DEF->fields["ticket_date"] = new CER_DB_FIELD("ticket_date","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["ticket_priority"] = new CER_DB_FIELD("ticket_priority","tinyint(4)","","","0","");
	$TABLE_DEF->fields["ticket_status"] = new CER_DB_FIELD("ticket_status","enum('new','responded','in progress','info needed','acceptance','on hold','escalated','fixed','resolved','reopened','dead')","","","new","");
	$TABLE_DEF->fields["last_update_date"] = new CER_DB_FIELD("last_update_date","timestamp(14)","YES","","","");
	$TABLE_DEF->fields["last_update_user_id"] = new CER_DB_FIELD("last_update_user_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["ticket_assigned_to_id"] = new CER_DB_FIELD("ticket_assigned_to_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["ticket_queue_id"] = new CER_DB_FIELD("ticket_queue_id","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["queue_addresses_id"] = new CER_DB_FIELD("queue_addresses_id","int(11)","","","0","");
	$TABLE_DEF->fields["ticket_reopenings"] = new CER_DB_FIELD("ticket_reopenings","smallint(6)","","","0","");
	$TABLE_DEF->fields["min_thread_id"] = new CER_DB_FIELD("min_thread_id","bigint(21)","YES","MUL","","");
	$TABLE_DEF->fields["max_thread_id"] = new CER_DB_FIELD("max_thread_id","bigint(21)","YES","MUL","","");
	
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
  		"`audit_id` bigint(20) NOT NULL auto_increment,".
  		"`ticket_id` bigint(20) NOT NULL default '0',".
  		"`epoch` bigint(20) NOT NULL default '0',".
  		"`timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
  		"`user_id` bigint(20) NOT NULL default '0',".
  		"`action` int(11) NOT NULL default '0',".
  		"`action_value` char(128) NOT NULL default '',".
  		"PRIMARY KEY  (`audit_id`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["audit_id"] = new CER_DB_FIELD("audit_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["epoch"] = new CER_DB_FIELD("epoch","bigint(20)","","","0","");
	$TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["action"] = new CER_DB_FIELD("action","int(11)","","","0","");
	$TABLE_DEF->fields["action_value"] = new CER_DB_FIELD("action_value","char(128)","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `ticket_fields` table
// ***************************************************************************
function init_table_ticket_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_fields",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_fields` (".
  		"`field_id` bigint(20) NOT NULL auto_increment,".
  		"`field_name` varchar(64) NOT NULL default '',".
  		"`field_type` enum('S','T','D') NOT NULL default 'S',".
  		"`field_options` text NOT NULL,".
  		"PRIMARY KEY  (`field_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_name"] = new CER_DB_FIELD("field_name","varchar(64)","","","","");
	$TABLE_DEF->fields["field_type"] = new CER_DB_FIELD("field_type","enum('s','t','d')","","","S","");
	$TABLE_DEF->fields["field_options"] = new CER_DB_FIELD("field_options","text","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `ticket_id_masks` table
// ***************************************************************************
function init_table_ticket_id_masks()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_id_masks",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE ticket_id_masks (".
		"ticket_id bigint(20) unsigned NOT NULL default '0',".
		"ticket_mask char(32) NOT NULL default '',".
		"PRIMARY KEY  (ticket_id),".
		"KEY ticket_mask (ticket_mask)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["ticket_mask"] = new CER_DB_FIELD("ticket_mask","char(32)","","MUL","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `ticket_values` table
// ***************************************************************************
function init_table_ticket_values()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_values",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_values` (".
  		"`value_id` bigint(20) NOT NULL auto_increment,".
  		"`field_id` bigint(20) NOT NULL default '0',".
  		"`ticket_id` bigint(20) NOT NULL default '0',".
  		"`value_text` text NOT NULL,".
  		"PRIMARY KEY  (`value_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["value_id"] = new CER_DB_FIELD("value_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20)","","","0","");
	$TABLE_DEF->fields["value_text"] = new CER_DB_FIELD("value_text","text","","","","");
	
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
  		"`view_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`view_name` varchar(64) NOT NULL default '',".
  		"`view_created_by_id` bigint(20) NOT NULL default '0',".
  		"`view_private` tinyint(4) NOT NULL default '0',".
  		"`view_queues` varchar(255) NOT NULL default '',".
  		"`view_columns` text NOT NULL default '',".
  		"`view_hide_statuses` char(255) NOT NULL default '',".
  		"`view_only_assigned` tinyint(4) NOT NULL default '',".
  		"`view_adv_2line` tinyint(1) NOT NULL default 1,".
  		"`view_adv_controls` tinyint(1) NOT NULL default 1,".
  		"PRIMARY KEY  (`view_id`),".
  		"KEY `view_id` (`view_id`)".
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
  		"`user_id` int(10) unsigned NOT NULL auto_increment,".
  		"`user_name` char(64) NOT NULL default '',".
  		"`user_email` char(128) NOT NULL default '',".
  		"`user_email_verify` char(16) NOT NULL default '',".
  		"`user_icq` char(16) NOT NULL default '',".
  		"`user_login` char(32) NOT NULL default '',".
  		"`user_password` char(64) NOT NULL default '',".
  		"`user_group_id` int(10) unsigned NOT NULL default '0',".
		"`user_last_login` timestamp(14) NOT NULL,".
  		"`user_superuser` tinyint(1) NOT NULL default '0',".
  		"`user_disabled` tinyint(4) NOT NULL default '0',".
  		"`user_xsp` tinyint(1) NOT NULL default '0',".
  		"PRIMARY KEY  (`user_id`),".
  		"UNIQUE KEY `user_login` (`user_login`)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_name"] = new CER_DB_FIELD("user_name","char(64)","","","","");
	$TABLE_DEF->fields["user_email"] = new CER_DB_FIELD("user_email","char(128)","","","","");
	$TABLE_DEF->fields["user_email_verify"] = new CER_DB_FIELD("user_email_verify","char(16)","","","","");
	$TABLE_DEF->fields["user_icq"] = new CER_DB_FIELD("user_icq","char(16)","","","","");
	$TABLE_DEF->fields["user_login"] = new CER_DB_FIELD("user_login","char(32)","","UNI","","");
	$TABLE_DEF->fields["user_password"] = new CER_DB_FIELD("user_password","char(64)","","","","");
	$TABLE_DEF->fields["user_group_id"] = new CER_DB_FIELD("user_group_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["user_last_login"] = new CER_DB_FIELD("user_last_login","timestamp(14)","YES","","","");
	$TABLE_DEF->fields["user_superuser"] = new CER_DB_FIELD("user_superuser","tinyint(1)","","","0","");
	$TABLE_DEF->fields["user_disabled"] = new CER_DB_FIELD("user_disabled","tinyint(4)","","","0","");
	$TABLE_DEF->fields["user_xsp"] = new CER_DB_FIELD("user_xsp","tinyint(1)","","","0","");
	
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
  		"`group_id` int(10) unsigned NOT NULL auto_increment,".
  		"`group_name` char(40) NOT NULL default '0',".
  		"`is_core_default` tinyint(4) NOT NULL default '0',".
  		"`group_acl` char(20) NOT NULL default '0',".
  		"`group_acl2` char(20) NOT NULL default '0',".
  		"`group_acl3` char(20) NOT NULL default '0',".
  		"PRIMARY KEY  (`group_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","char(40)","","","0","");
	$TABLE_DEF->fields["is_core_default"] = new CER_DB_FIELD("is_core_default","tinyint(4)","","","0","");
	$TABLE_DEF->fields["group_acl"] = new CER_DB_FIELD("group_acl","char(20)","","","0","");
	$TABLE_DEF->fields["group_acl2"] = new CER_DB_FIELD("group_acl2","char(20)","","","0","");
	$TABLE_DEF->fields["group_acl3"] = new CER_DB_FIELD("group_acl3","char(20)","","","0","");
	
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
  		"`user_id` int(11) default NULL,".
  		"`refresh_rate` tinyint(4) default NULL,".
  		"`ticket_order` tinyint(4) default NULL,".
  		"`user_language` char(3) NOT NULL default 'en',".
  		"`signature_pos` tinyint(1) NOT NULL default '0',".
  		"`signature_autoinsert` tinyint(1) NOT NULL default '1',".
  		"`view_prefs` TEXT,".
  		"`assign_queues` TEXT".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(11)","YES","","","");
	$TABLE_DEF->fields["refresh_rate"] = new CER_DB_FIELD("refresh_rate","tinyint(4)","YES","","","");
	$TABLE_DEF->fields["ticket_order"] = new CER_DB_FIELD("ticket_order","tinyint(4)","YES","","","");
	$TABLE_DEF->fields["user_language"] = new CER_DB_FIELD("user_language","char(3)","","","en","");
	$TABLE_DEF->fields["signature_pos"] = new CER_DB_FIELD("signature_pos","tinyint(1)","","","0","");
	$TABLE_DEF->fields["signature_autoinsert"] = new CER_DB_FIELD("signature_autoinsert","tinyint(1)","","","1","");
	$TABLE_DEF->fields["view_prefs"] = new CER_DB_FIELD("view_prefs","text","YES","","","");
	$TABLE_DEF->fields["assign_queues"] = new CER_DB_FIELD("assign_queues","text","YES","","","");
	
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
  		"`sig_id` int(10) unsigned NOT NULL auto_increment,".
  		"`user_id` int(10) unsigned NOT NULL default '0',".
  		"`sig_content` text NOT NULL,".
  		"PRIMARY KEY  (`sig_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["sig_id"] = new CER_DB_FIELD("sig_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","int(10) unsigned","","","0","");
	$TABLE_DEF->fields["sig_content"] = new CER_DB_FIELD("sig_content","text","","","","");
	
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
		"`warcheck_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`address_id` BIGINT UNSIGNED NOT NULL ,".
		"`subject_md5` CHAR( 32 ) NOT NULL ,".
		"`queue_id` BIGINT UNSIGNED NOT NULL ,".
		"`timestamp` DATETIME NOT NULL ,".
		"PRIMARY KEY (`warcheck_id`),".
		"INDEX(`address_id`),".
		"INDEX(`queue_id`),".
		"INDEX(`subject_md5`)".
		");";
	  
	$TABLE_DEF->fields["warcheck_id"] = new CER_DB_FIELD("warcheck_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["address_id"] = new CER_DB_FIELD("address_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["subject_md5"] = new CER_DB_FIELD("subject_md5","char(32)","","MUL","","");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["timestamp"] = new CER_DB_FIELD("timestamp","datetime","","","0000-00-00 00:00:00","");
	
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
  		"`user_id` bigint(20) unsigned NOT NULL auto_increment,".
  		"`user_ip` char(20) NOT NULL default '',".
  		"`user_timestamp` datetime NOT NULL default '0000-00-00 00:00:00',".
  		"`user_what_action` int(11) NOT NULL default '0',".
  		"`user_what_arg1` char(64) NOT NULL default '',".
  		"PRIMARY KEY  (`user_id`)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_ip"] = new CER_DB_FIELD("user_ip","char(20)","","","","");
	$TABLE_DEF->fields["user_timestamp"] = new CER_DB_FIELD("user_timestamp","datetime","","","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["user_what_action"] = new CER_DB_FIELD("user_what_action","int(11)","","","0","");
	$TABLE_DEF->fields["user_what_arg1"] = new CER_DB_FIELD("user_what_arg1","char(64)","","","","");
	
	table($TABLE_DEF);
}


/*
// ***************************************************************************
// `company` table
// ***************************************************************************
/
function init_table_companyy()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("company",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `company` (".
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20)","","","","");

	$TABLE_DEF->fields[""] = new CER_DB_FIELD("","","","","","");
	$TABLE_DEF->fields[""] = new CER_DB_FIELD("","","","","","");
	
	table($TABLE_DEF);
}
*/

?>