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
|
| File: cerb_210_to_220.php
|
| Purpose: Upgrades the database structure from 2.1.0 to 2.2.0
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";
require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.1.0 to 2.2.0 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20030917");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","");
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	update_table_configuration();
	update_table_ticket_views();
	update_table_thread();
	update_table_user();
	update_table_user_prefs();
	init_table_db_script_hash();
	init_table_tasks();
	init_table_tasks_projects();
	init_table_tasks_projects_categories();
	init_table_tasks_notes();
	init_table_ticket_id_masks();
	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
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
		$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('726d6bd3b4d21c52363bd20fab0bcbaa',NOW())"; // 2.2.0 clean install
		$cerberus_db->query($sql);                                       
	}
}

// ***************************************************************************
// `configuration` table
// ***************************************************************************
function update_table_configuration()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["bcc_watchers"])) {
		$sql = "ALTER TABLE `configuration` ADD `bcc_watchers` TINYINT DEFAULT '0' NOT NULL AFTER `auto_add_cc_reqs` ;";
		$TABLE_DEF->run_sql($sql,"Adding configuration.bcc_watchers");
	}
	
	if(!isset($TABLE_DEF->fields["watcher_no_system_attach"])) {
		$sql = "ALTER TABLE `configuration` ADD `watcher_no_system_attach` TINYINT DEFAULT '0' NOT NULL ;";
		$TABLE_DEF->run_sql($sql,"Adding configuration.watcher_no_system_attach");
	}
	
	if(isset($TABLE_DEF->fields["master_db_server"])) {
		$sql = "ALTER TABLE `configuration` DROP `master_db_server`;";
		$TABLE_DEF->run_sql($sql,"Dropping configuration.master_db_server");
	}
	
	if(isset($TABLE_DEF->fields["master_db_name"]))	{
		$sql = "ALTER TABLE `configuration` DROP `master_db_name`;";
		$TABLE_DEF->run_sql($sql,"Dropping configuration.master_db_name");
	}

	if(isset($TABLE_DEF->fields["master_db_user"]))	{
		$sql = "ALTER TABLE `configuration` DROP `master_db_user`;";
		$TABLE_DEF->run_sql($sql,"Dropping configuration.master_db_user");
	}

	if(isset($TABLE_DEF->fields["master_db_pass"]))	{
		$sql = "ALTER TABLE `configuration` DROP `master_db_pass`;";
		$TABLE_DEF->run_sql($sql,"Dropping configuration.master_db_pass");
	}
	
	if(!isset($TABLE_DEF->fields["xsp_url"])) {
		$sql = "ALTER TABLE `configuration` ADD `xsp_url` CHAR(255) NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding configuration.xsp_url");
	}
	
	if(!isset($TABLE_DEF->fields["xsp_login"]))	{
		$sql = "ALTER TABLE `configuration` ADD `xsp_login` CHAR(64) NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding configuration.xsp_login");
	}

	if(!isset($TABLE_DEF->fields["xsp_password"])) {
		$sql = "ALTER TABLE `configuration` ADD `xsp_password` CHAR(64) NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding configuration.xsp_password");
	}
	
	if(!isset($TABLE_DEF->fields["enable_id_masking"])) {
		$sql = "ALTER TABLE `configuration` ADD `enable_id_masking` TINYINT DEFAULT '1' NOT NULL AFTER `enable_customer_history` ;";

		$TABLE_DEF->run_sql($sql,"Adding configuration.enable_id_masking");
	}
	
	if(!isset($TABLE_DEF->fields["session_ip_security"])) {
		$sql = "ALTER TABLE `configuration` ADD `session_ip_security` TINYINT DEFAULT '0' NOT NULL AFTER `sendmail` ;";

		$TABLE_DEF->run_sql($sql,"Adding configuration.session_ip_security");
	}
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
// `tasks` table
// ***************************************************************************
function init_table_tasks()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("tasks",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `tasks` (".
		"task_id bigint(20) unsigned NOT NULL auto_increment,".
		"task_summary varchar(255) NOT NULL default '',".
		"task_description text,".
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
		"project_acl text,".
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
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `thread` table
// ***************************************************************************
function update_table_thread()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["thread_type"])) {
		$sql = "ALTER TABLE `thread` CHANGE `thread_type` `thread_type` ENUM( \"email\", \"comment\", \"forward\" ) DEFAULT 'email' NOT NULL";
		$TABLE_DEF->run_sql($sql,"Changing thread.thread_type enumeration to include 'forward'");
	}
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
// `ticket_views` table
// ***************************************************************************
function update_table_ticket_views()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_views",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["view_adv_2line"])) {
		$sql = "ALTER TABLE `ticket_views` ADD `view_adv_2line` TINYINT(1) DEFAULT 1 NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding ticket_views.view_adv_2line");
	}
	
	if(!isset($TABLE_DEF->fields["view_adv_controls"])) {
		$sql = "ALTER TABLE `ticket_views` ADD `view_adv_controls` TINYINT(1) DEFAULT 1 NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding ticket_views.view_adv_controls");
	}
}

// ***************************************************************************
// `user` table
// ***************************************************************************
function update_table_user()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["user_xsp"])) {
		$sql = "ALTER TABLE `user` ADD `user_xsp` TINYINT(1) DEFAULT 0 NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding user.user_xsp");
	}
	
	if(isset($TABLE_DEF->fields["user_password"]) && $TABLE_DEF->fields["user_password"]->field_type == "char(16)") {
		$sql = "ALTER TABLE `user` CHANGE `user_password` `user_password` CHAR(64) DEFAULT '' NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Changing user.user_password type to char(64)");
	}
}

// ***************************************************************************
// `user_prefs` table
// ***************************************************************************
function update_table_user_prefs()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_prefs",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["view_prefs"])) {
		$sql = "ALTER TABLE `user_prefs` ADD `view_prefs` TEXT ;";
		$TABLE_DEF->run_sql($sql,"Adding user_prefs.view_prefs");
	}

	if(!isset($TABLE_DEF->fields["assign_queues"])) {
		$sql = "ALTER TABLE `user_prefs` ADD `assign_queues` TEXT ;";
		$TABLE_DEF->run_sql($sql,"Adding user_prefs.assign_queues");
	}

    if(!isset($TABLE_DEF->fields["signature_autoinsert"])) {
     	$sql = "ALTER TABLE `user_prefs` ADD `signature_autoinsert` TINYINT(1) DEFAULT '1' NOT NULL AFTER `signature_pos`;";
     	$TABLE_DEF->run_sql($sql,"Adding user_prefs.signature_autoinsert");
    }
	
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
	
	foreach($TABLE_DEF->fields as $idx => $fld)
		$verify_fields[$fld->field_name] = DB_FIELD;
		
	$warn_fields = $TBL->verify_table($verify_fields);
	
	if(count($warn_fields[0])) print_extra_fields($TBL,$warn_fields);
	if(count($warn_fields[1])) print_missing_fields($TBL,$warn_fields);
								
	foreach($TABLE_DEF->fields as $idx => $fld)
		$TBL->verify_field($fld->field_name,$fld->field_type,$fld->field_null,$fld->field_key,$fld->field_default,$fld->field_extra);
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

?>