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
| File: cerb_231_to_232.php
|
| Purpose: Upgrades the database structure from 2.3.1 to 2.3.2
|
| Developers involved with this file:
|		Ben Halsted			(ben@webgroupmedia.com)			[BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.3.1 to 2.3.2 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20040225");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","bc4db140f76f67f505479bf7386d4def");
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_user_login_log();
	update_table_configuration();
	update_table_knowledgebase_problem();
	update_table_knowledgebase_solution();
	update_table_search_index_kb();
	update_table_ticket_audit_log();
	set_precursor_hashes();
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}


function set_precursor_hashes()
{
	global $cerberus_db;
																										
	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('b81131b5d008d10c09049d667dd6a4c4',NOW())"; // 2.3.2 clean
	$cerberus_db->query($sql);
}

// ***************************************************************************
// `user_login_log` table
// ***************************************************************************

function init_table_user_login_log()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_login_log",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_login_log` (" .
		 "`id` bigint unsigned NOT NULL AUTO_INCREMENT ," .
		 "`user_id` bigint unsigned NOT NULL ," .
		 "`user_ip` char( 15 ) DEFAULT '\"\"' NOT NULL ," .
		 "`local_time_login` datetime NOT NULL ," .
		 "`local_time_logout` datetime NOT NULL ," .
		 "`gmt_time_login` datetime NOT NULL ," .
		 "`gmt_time_logout` datetime NOT NULL ," .
		 "`logged_secs` bigint(20) DEFAULT 0 NOT NULL ," .
		 "PRIMARY KEY ( `id` ) ," .
		 "INDEX ( `user_id` )," .
		 "INDEX ( `local_time_login` )," . 
		 "INDEX ( `local_time_logout`)," .
		 "INDEX ( `gmt_time_login`)," .
		 "INDEX ( `gmt_time_logout` )" .
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["user_ip"] = new CER_DB_FIELD("user_ip","char(15)","","","\"\"","");
	$TABLE_DEF->fields["local_time_login"] = new CER_DB_FIELD("local_time_login","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["local_time_logout"] = new CER_DB_FIELD("local_time_logout","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["gmt_time_login"] = new CER_DB_FIELD("gmt_time_login","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["gmt_time_logout"] = new CER_DB_FIELD("gmt_time_logout","datetime","","MUL","0000-00-00 00:00:00","");
	$TABLE_DEF->fields["logged_secs"] = new CER_DB_FIELD("logged_secs","bigint(20)","","","0","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `configuration` table
// ***************************************************************************
function update_table_configuration()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["search_index_numbers"])) {
		$TABLE_DEF->add_field("search_index_numbers","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["user_only_assign_own_queues"])) {
		$TABLE_DEF->add_field("user_only_assign_own_queues","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["parser_version"])) {
		$TABLE_DEF->add_field("parser_version","CHAR(32) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["save_message_xml"])) {
		$TABLE_DEF->add_field("save_message_xml","TINYINT DEFAULT '1' NOT NULL");
	}
}

// ***************************************************************************
// `knowledgebase_problem` table
// ***************************************************************************
function update_table_knowledgebase_problem()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_problem",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["kb_problem_text_is_html"])) {
		$TABLE_DEF->add_field("kb_problem_text_is_html","TINYINT DEFAULT '0' NOT NULL");
	}
}

// ***************************************************************************
// `knowledgebase_solution` table
// ***************************************************************************
function update_table_knowledgebase_solution()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_solution",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["kb_solution_text_is_html"])) {
		$TABLE_DEF->add_field("kb_solution_text_is_html","TINYINT DEFAULT '0' NOT NULL");
	}
}

// ***************************************************************************
// `search_index_kb` table
// ***************************************************************************
function update_table_search_index_kb()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index_kb",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["in_topic"])) {
		$TABLE_DEF->drop_field("in_topic");
	}
	
}

// ***************************************************************************
// `ticket_audit_log` table
// ***************************************************************************
function update_table_ticket_audit_log()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("ticket_audit_log",true);
	$TABLE_DEF->check(true);

	if($TABLE_DEF->fields["ticket_id"]->field_key != "MUL") {
		$sql = "ALTER TABLE `ticket_audit_log` ADD INDEX(`ticket_id`)";
		$TABLE_DEF->run_sql($sql,"Adding index to ticket_audit_log.ticket_id");
	}
	if($TABLE_DEF->fields["epoch"]->field_key != "MUL") {
		$sql = "ALTER TABLE `ticket_audit_log` ADD INDEX(`epoch`);";
		$TABLE_DEF->run_sql($sql,"Adding index to ticket_audit_log.epoch_id");
	}
	if($TABLE_DEF->fields["user_id"]->field_key != "MUL") {
		$sql = "ALTER TABLE `ticket_audit_log` ADD INDEX(`user_id`);";
		$TABLE_DEF->run_sql($sql,"Adding index to ticket_audit_log.user_id");
	}
	if($TABLE_DEF->fields["action"]->field_key != "MUL") {
		$sql = "ALTER TABLE `ticket_audit_log` ADD INDEX(`action`);";
		$TABLE_DEF->run_sql($sql,"Adding index to ticket_audit_log.action");
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