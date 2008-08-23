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
| File: cerb_220_to_230.php
|
| Purpose: Upgrades the database structure from 2.2.0 to 2.3.0
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|		Ben Halsted			(ben@webgroupmedia.com)			[BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";
require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.2.0 to 2.3.0 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20031106");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","726d6bd3b4d21c52363bd20fab0bcbaa");
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_public_gui();
	init_table_public_gui_fields();
	init_table_public_gui_profiles();
	init_table_queue_group_access();
	init_table_spam_bayes_index();
	init_table_spam_bayes_stats();	
	init_table_trigrams();
	init_table_trigram_to_kb();
	init_table_trigram_to_thread();
	init_table_user_notification();
	update_table_address_fields();
	update_table_address_values();
	update_table_configuration();
	update_table_queue();
	update_table_queue_access();
	update_table_ticket();
	update_table_ticket_fields();
	update_table_ticket_values();
	set_precursor_hashes();
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

// ***************************************************************************
// `public_gui` table
// ***************************************************************************
function init_table_public_gui()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui",true);
	
	if($TABLE_DEF->table_exists)
	{
		$sql = "DROP TABLE IF EXISTS public_gui";
		$TABLE_DEF->run_sql($sql,"Dropping `public_gui` table");
	}
}

// ***************************************************************************
// `public_gui_fields` table
// ***************************************************************************
function init_table_public_gui_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_fields",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE public_gui_fields (".
		"group_id bigint(20) unsigned NOT NULL auto_increment,".
		"group_name varchar(64) NOT NULL default '',".
		"group_fields text,".
		"PRIMARY KEY  (group_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","varchar(64)","","","","");
	$TABLE_DEF->fields["group_fields"] = new CER_DB_FIELD("group_fields","text","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `public_gui_profiles` table
// ***************************************************************************
function init_table_public_gui_profiles()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE public_gui_profiles (".
		"profile_id bigint(20) unsigned NOT NULL auto_increment,".
		"profile_name varchar(64) NOT NULL default '',".
		"pub_locked_submit tinyint(4) NOT NULL default '0',".
		"pub_hide_kb tinyint(4) NOT NULL default '0',".
		"pub_registration_mode varchar(16) NOT NULL default '',".
		"pub_company_name varchar(128) NOT NULL default '',".
		"pub_company_email varchar(128) NOT NULL default '',".
		"pub_new_registration_subject varchar(250) NOT NULL default '',".
		"pub_new_registration_body text,".
		"pub_queues text,".
		"pub_kb_resolved_issue text,".
		"pub_kb_not_resolved_issue text,".
		"PRIMARY KEY  (profile_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["profile_id"] = new CER_DB_FIELD("profile_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["profile_name"] = new CER_DB_FIELD("profile_name","varchar(64)","","","","");
	$TABLE_DEF->fields["pub_locked_submit"] = new CER_DB_FIELD("pub_locked_submit","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_hide_kb"] = new CER_DB_FIELD("pub_hide_kb","tinyint(4)","","","0","");
	$TABLE_DEF->fields["pub_registration_mode"] = new CER_DB_FIELD("pub_registration_mode","varchar(16)","","","","");
	$TABLE_DEF->fields["pub_company_name"] = new CER_DB_FIELD("pub_company_name","varchar(128)","","","","");
	$TABLE_DEF->fields["pub_company_email"] = new CER_DB_FIELD("pub_company_email","varchar(128)","","","","");
	$TABLE_DEF->fields["pub_new_registration_subject"] = new CER_DB_FIELD("pub_new_registration_subject","varchar(250)","","","","");
	$TABLE_DEF->fields["pub_new_registration_body"] = new CER_DB_FIELD("pub_new_registration_body","text","","","","");
	$TABLE_DEF->fields["pub_queues"] = new CER_DB_FIELD("pub_queues","text","","","","");
	$TABLE_DEF->fields["pub_kb_resolved_issue"] = new CER_DB_FIELD("pub_kb_resolved_issue","text","","","","");
	$TABLE_DEF->fields["pub_kb_not_resolved_issue"] = new CER_DB_FIELD("pub_kb_not_resolved_issue","text","","","","");
	
	table($TABLE_DEF);
}

// [JAS]: If all is well, note that our precursor scripts have run since this is the first time the new
//	DB patcher tracking fields are being used.
function set_precursor_hashes()
{
	global $cerberus_db;
	
	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('aba12b36438a09371c9c6db4ccec75ec',NOW())"; // 2.3.0 clean
	$cerberus_db->query($sql);
}

// ***************************************************************************
// `queue_group_access` table
// ***************************************************************************
function init_table_queue_group_access()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue_group_access",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE queue_group_access (".
		"queue_id bigint(20) unsigned NOT NULL default '0',".
		"group_id bigint(20) unsigned NOT NULL default '0',".
		"queue_access enum('read','write','none','') NOT NULL default '',".
		"KEY queue_id (queue_id),".
		"KEY group_id (group_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["queue_access"] = new CER_DB_FIELD("queue_access","enum('read','write','none','')","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `spam_bayes_index` table
// ***************************************************************************
function init_table_spam_bayes_index()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("spam_bayes_index",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE spam_bayes_index (".
		"word_id bigint(20) unsigned NOT NULL default '0',".
		"in_spam bigint(20) unsigned NOT NULL default '0',".
		"in_nonspam bigint(20) unsigned NOT NULL default '0',".
		"UNIQUE KEY word_id (word_id)".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["word_id"] = new CER_DB_FIELD("word_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["in_spam"] = new CER_DB_FIELD("in_spam","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["in_nonspam"] = new CER_DB_FIELD("in_nonspam","bigint(20) unsigned","","","0","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `spam_bayes_stats` table
// ***************************************************************************
function init_table_spam_bayes_stats()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("spam_bayes_stats",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE spam_bayes_stats (".
		"num_spam bigint(20) unsigned NOT NULL default '0',".
		"num_nonspam bigint(20) unsigned NOT NULL default '0'".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["num_spam"] = new CER_DB_FIELD("num_spam","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["num_nonspam"] = new CER_DB_FIELD("num_nonspam","bigint(20) unsigned","","","0","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `trigrams` table
// ***************************************************************************
function init_table_trigrams()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigrams",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigrams` (" .
		"`trigram_id` BIGINT NOT NULL AUTO_INCREMENT ," .
		"`trigram_a` BIGINT UNSIGNED DEFAULT '0' NOT NULL ," .
		"`trigram_b` BIGINT UNSIGNED DEFAULT '0' NOT NULL ," .
		"`trigram_c` BIGINT UNSIGNED DEFAULT '0' NOT NULL ," .
		"`trigram_void` SMALLINT UNSIGNED DEFAULT '0' NOT NULL ," .
			"PRIMARY KEY ( `trigram_id` ) ," .
			"UNIQUE `trigrams_abc` ( " .
			"`trigram_a` , " .
			"`trigram_b` , " .
			"`trigram_c` " .
			")" .
		") TYPE=MyISAM";
	  
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20)","","PRI","","auto_increment");
	$TABLE_DEF->fields["trigram_a"] = new CER_DB_FIELD("trigram_a","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["trigram_b"] = new CER_DB_FIELD("trigram_b","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["trigram_c"] = new CER_DB_FIELD("trigram_c","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["trigram_void"] = new CER_DB_FIELD("trigram_void","smallint(5) unsigned","","","0","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `trigram_to_thread` table
// ***************************************************************************
function init_table_trigram_to_thread()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_thread",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_to_thread` (" .
		"`trigram_id` BIGINT UNSIGNED NOT NULL ," .
		"`thread_id` BIGINT UNSIGNED NOT NULL ," .
			"UNIQUE (" .
			"`trigram_id` ," .
			"`thread_id`" .
			")" .
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","PRI","0","");
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `trigram_to_kb` table
// ***************************************************************************
function init_table_trigram_to_kb()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_kb",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_to_kb` (" .
		"`trigram_id` BIGINT UNSIGNED NOT NULL ," .
		"`knowledgebase_id` BIGINT  UNSIGNED NOT NULL ," .
		"`weight` BIGINT  UNSIGNED NOT NULL ," .
			"UNIQUE (" .
			"`trigram_id` ," .
			"`knowledgebase_id`" .
			")" .
		") TYPE=MyISAM";
	
	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["knowledgebase_id"] = new CER_DB_FIELD("knowledgebase_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["weight"] = new CER_DB_FIELD("weight","bigint(20) unsigned","","","0","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `user_notification` table
// ***************************************************************************
function init_table_user_notification()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_notification",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_notification` (" .
		"`user_id` BIGINT UNSIGNED NOT NULL ," .
		"`notify_options` TEXT," .
			"UNIQUE (" .
			"`user_id`" .
			")" .
		") TYPE=MyISAM";
	
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["notify_options"] = new CER_DB_FIELD("notify_options","text","","","","");
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `address_fields` table
// ***************************************************************************
function update_table_address_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address_fields",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["field_public"])) {
		$sql = "ALTER TABLE `address_fields` DROP `field_public`";
		$TABLE_DEF->run_sql($sql,"Dropping address_fields.field_public");
	}
	
	if(!isset($TABLE_DEF->fields["field_not_searchable"])) {
		$sql = "ALTER TABLE `address_fields` ADD `field_not_searchable` TINYINT DEFAULT '0' NOT NULL ;";
		$TABLE_DEF->run_sql($sql,"Adding address_fields.field_not_searchable");
	}
}

// ***************************************************************************
// `address_values` table
// ***************************************************************************
function update_table_address_values()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address_values",true);
	$TABLE_DEF->check(true);

	if($TABLE_DEF->fields["field_id"] && $TABLE_DEF->fields["field_id"]->field_key != "MUL") {
		$sql = "ALTER TABLE address_values ADD UNIQUE KEY Unique_Field_Address (field_id,address_id);";
		$TABLE_DEF->run_sql($sql,"Adding address_values UNIQUE Index on Field ID + Address ID pair");
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

	if(isset($TABLE_DEF->fields["demo"])) {
		$TABLE_DEF->drop_field("demo");
	}
	
	if(isset($TABLE_DEF->fields["auto_search_index"])) {
		$TABLE_DEF->drop_field("auto_search_index");
	}
}

// ***************************************************************************
// `queue` table
// ***************************************************************************
function update_table_queue()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["queue_email_display_name"])) {
		$sql = "ALTER TABLE `queue` ADD `queue_email_display_name` varchar(64) NOT NULL ;";
		$TABLE_DEF->run_sql($sql,"Adding queue.queue_email_display_name");
	}
	
}

// ***************************************************************************
// `queue_access` table
// ***************************************************************************
function update_table_queue_access()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue_access",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["queue_access"])) {
		$sql = "ALTER TABLE `queue_access` CHANGE `queue_access` `queue_access` ENUM('read','write','none','') DEFAULT '' NOT NULL ;";
		$TABLE_DEF->run_sql($sql,"Changing ENUM on queue_access.queue_access");
	}
	
}

// ***************************************************************************
// `ticket` table
// ***************************************************************************
function update_table_ticket()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["ticket_spam_trained"])) {
		$sql = "ALTER TABLE `ticket` ADD `ticket_spam_trained` tinyint(1) NOT NULL ;";
		$TABLE_DEF->run_sql($sql,"Adding ticket.ticket_spam_trained");
	}
	
}

// ***************************************************************************
// `ticket_fields` table
// ***************************************************************************
function update_table_ticket_fields()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_fields",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["field_public"])) {
		$sql = "ALTER TABLE `ticket_fields` DROP `field_public`";
		$TABLE_DEF->run_sql($sql,"Dropping ticket_fields.field_public");
	}
	
	if(!isset($TABLE_DEF->fields["field_not_searchable"])) {
		$sql = "ALTER TABLE `ticket_fields` ADD `field_not_searchable` TINYINT DEFAULT '0' NOT NULL ;";
		$TABLE_DEF->run_sql($sql,"Adding ticket_fields.field_not_searchable");
	}
}

// ***************************************************************************
// `ticket_values` table
// ***************************************************************************
function update_table_ticket_values()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_values",true);
	$TABLE_DEF->check(true);

	if($TABLE_DEF->fields["field_id"] && $TABLE_DEF->fields["field_id"]->field_key != "MUL") {
		$sql = "ALTER TABLE ticket_values ADD UNIQUE KEY Unique_Field_Ticket (field_id,ticket_id);";
		$TABLE_DEF->run_sql($sql,"Adding ticket_values UNIQUE Index on Field ID + Ticket ID pair");
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