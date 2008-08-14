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
|		Jeremy Johnstone	(jeremy@webgroupmedia.com)		[JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
set_time_limit(3600); // 1hr

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/acl_groups.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.6.x to 2.7.0 Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20051017");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","5b05fa352ab1b87eceebe854822676ff"); // 2.6.x Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	alter_table_queries();
	init_table_chat_agents_to_requests();
	init_table_chat_agents_to_rooms();
	init_table_chat_canned_text();
	init_table_chat_message_parts();
	init_table_chat_messages();
	init_table_chat_rooms();
	init_table_chat_transcripts();
	init_table_chat_visitor_chat_requests();
	init_table_chat_visitor_pages();
	init_table_chat_visitors();
	init_table_chat_visitors_to_invites();
	init_table_chat_visitors_to_rooms();
	init_table_country();
	init_table_industry();
	init_table_opportunity();
	init_table_skill();
	init_table_skill_category();
	init_table_skill_to_agent();
	init_table_skill_to_ticket();
	init_table_user_extended_info();
	init_table_dispatcher_delays();
	init_table_dispatcher_suggestions();
	init_table_department();
	init_table_department_teams();
	init_table_saved_reports();
	init_table_stat_browsers();
	init_table_stat_hosts();
	init_table_stat_urls();
	init_table_ticket_tasks();
	init_table_team();
	init_table_team_members();
	init_table_team_queues();
	init_table_gateway_session();
	init_table_heartbeat_event_payload();
	init_table_heartbeat_event_queue();
	init_table_user_prefs_xml();

	update_table_chat_visitor_pages();
	update_table_chat_visitors();
	update_table_company();
	update_table_configuration();
	update_table_public_gui_users();
	update_table_rule_action();
	update_table_opportunity();

	migrate_chat_strings();
	
	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
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
		"  KEY `has_skill` (`has_skill`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["skill_id"] = new CER_DB_FIELD("skill_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["has_skill"] = new CER_DB_FIELD("has_skill","tinyint(3) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["agent_to_skill"] = new CER_DB_INDEX("agent_to_skill","0",array("skill_id","agent_id"));
   $TABLE_DEF->indexes["has_skill"] = new CER_DB_INDEX("has_skill","1",array("has_skill"));

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

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('fbdb155c25f4ba500442f8cfaf6bc9bc',NOW())"; // 2.7.0 clean
	$cerberus_db->query($sql);
}

// ***************************************************************************
// [JAS]: STANDARD CALLBACKS -- Do not edit unless you know what you're doing.
// ***************************************************************************

function table(&$TABLE_DEF)
{
	global $cerberus_db;
	
	$TBL = new CER_DB_TABLE($TABLE_DEF->table_name);
	
	// [JAS]: Only create and verify a table if it doesn't exist.
	if(!$TBL->check(false)) {
		create_table($TBL,$TABLE_DEF); // create
		verify_table($TBL,$TABLE_DEF); // verify structure
	}
	
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
		$TBL->verify_field($fld->field_name,$fld->field_type,$fld->field_null,$fld->field_key,$fld->field_default,$fld->field_extra);
		
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
		"  `canned_text` text NOT NULL,".
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
		"  `page_name` char(255) default NULL,".
		"  `visitor_id` bigint(20) unsigned NOT NULL default '0',".
		"  `page_timestamp` bigint(20) default NULL,".
		"  `page_referrer` char(255) NOT NULL default '',".
		"  `page_referrer_host` char(128) NOT NULL default '',".
//		"  `page_referrer_url_id` bigint(20) unsigned NOT NULL default '',".
//		"  `page_referrer_host_id` int(10) unsigned NOT NULL default '',".
		"  PRIMARY KEY  (`page_id`),".
		"  KEY `visitor_id` (`visitor_id`),".
		"  KEY `page_referrer` (`page_referrer`),".
		"  KEY `page_referrer_host` (`page_referrer_host`)".
//		"  KEY `page_referrer_url_id` (`page_referrer_url_id`),".
//		"  KEY `page_referrer_host_id` (`page_referrer_host_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["page_id"] = new CER_DB_FIELD("page_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["page_name"] = new CER_DB_FIELD("page_name","char(255)","YES","","","");
   $TABLE_DEF->fields["visitor_id"] = new CER_DB_FIELD("visitor_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["page_timestamp"] = new CER_DB_FIELD("page_timestamp","bigint(20)","YES","","","");
   $TABLE_DEF->fields["page_referrer"] = new CER_DB_FIELD("page_referrer","char(255)","","MUL","","");
   $TABLE_DEF->fields["page_referrer_host"] = new CER_DB_FIELD("page_referrer_host","char(128)","","MUL","","");
//   $TABLE_DEF->fields["page_referrer_url_id"] = new CER_DB_FIELD("page_referrer_url_id","bigint(20) unsigned","","MUL","","");
//   $TABLE_DEF->fields["page_referrer_host_id"] = new CER_DB_FIELD("page_referrer_host_id","int(10) unsigned","","MUL","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","page_id");
   $TABLE_DEF->indexes["visitor_id"] = new CER_DB_INDEX("visitor_id","1","visitor_id");
   $TABLE_DEF->indexes["page_referrer"] = new CER_DB_INDEX("page_referrer","1","page_referrer");
   $TABLE_DEF->indexes["page_referrer_host"] = new CER_DB_INDEX("page_referrer_host","1","page_referrer_host");
//   $TABLE_DEF->indexes["page_referrer_url_id"] = new CER_DB_INDEX("page_referrer_url_id","1","page_referrer_url_id");
//   $TABLE_DEF->indexes["page_referrer_host_id"] = new CER_DB_INDEX("page_referrer_host_id","1","page_referrer_host_id");

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
		"  `invite_message` text NOT NULL,".
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
		"  `session_data` longtext NOT NULL,".
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
		"  `payload` text NOT NULL,".
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

function alter_table_queries()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("user_extended_info",true);
  
   if(!$TABLE_DEF->field_exists("notification_event_mask")) {
	   $query = "ALTER TABLE `user_extended_info` ADD `notification_event_mask` BIGINT( 20 ) UNSIGNED DEFAULT '0' NOT NULL";
	   $cerberus_db->query($query);
   }
   
   $query = "ALTER TABLE `chat_rooms` CHANGE `room_type` `room_type` ENUM( 'visitor', 'im', 'meeting') DEFAULT 'visitor' NOT NULL";
   $cerberus_db->query($query);
   
   $TABLE_DEF = new CER_DB_TABLE("department",true);

   if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("department_usage") && $TABLE_DEF->field_exists("usage")) {
		   $query = "ALTER TABLE `department` CHANGE `usage` `department_usage` BIGINT( 20 ) DEFAULT '0' NOT NULL";
		   $TABLE_DEF->run_sql($query,"Changing `department`.usage to department.department_usage");
	   }
	   
		if(!$TABLE_DEF->field_exists("department_offline_address")) {
			$query = "ALTER TABLE `department` ADD `department_offline_address` CHAR(64) NOT NULL";
			$TABLE_DEF->run_sql($query,"Adding `department`.department_offline_address");
		}
	}
	
   $TABLE_DEF = new CER_DB_TABLE("chat_transcripts",true);

   if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("department_id")) {
	   	$query = "ALTER TABLE `chat_transcripts` ADD `department_id` INT(11) UNSIGNED DEFAULT '0' NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `chat_transcripts`.department_id");
	
	  	   $query = "ALTER TABLE `chat_transcripts` ADD INDEX ( `department_id` )";
		   $cerberus_db->query($query);
		}
   }
	
   $TABLE_DEF = new CER_DB_TABLE("company",true);

   if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("company_mailing_address")) {
	   	$query = "ALTER TABLE `company` ADD `company_mailing_address` CHAR(128) DEFAULT '' NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `company`.company_mailing_address");
	   	
	   	if(migrate_company_2line_addresses()) {
	   		$TABLE_DEF->drop_field("company_mailing_street1");
	   		$TABLE_DEF->drop_field("company_mailing_street2");
	   	}
		}

		if($TABLE_DEF->field_exists("company_mailing_country")) {
			$query = "ALTER TABLE `company` CHANGE `company_mailing_country` `company_mailing_country_old` VARCHAR( 64 ) NOT NULL";
			$TABLE_DEF->run_sql($query,"Renaming company.`company_mailing_country` to company.`company_mailing_country_old`");
		}
		
		if(!$TABLE_DEF->field_exists("company_mailing_country_id")) {
	   	$query = "ALTER TABLE `company` ADD `company_mailing_country_id` int(10) unsigned NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `company`.company_mailing_country_id");
	   	$TABLE_DEF->add_index("company_mailing_country_id",1,array("company_mailing_country_id"));
		}
   }

	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",true);

   if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("mailing_address")) {
	   	$query = "ALTER TABLE `public_gui_users` ADD `mailing_address` CHAR(128) DEFAULT '' NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `public_gui_users`.mailing_address");
	   	
	   	if(migrate_contact_2line_addresses()) {
	   		$TABLE_DEF->drop_field("mailing_street1");
	   		$TABLE_DEF->drop_field("mailing_street2");
	   	}
		}
		
		if(!$TABLE_DEF->field_exists("name_first")) {
	   	$query = "ALTER TABLE `public_gui_users` ADD `name_first` CHAR(16) DEFAULT '' NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `public_gui_users`.name_first");
	   	
	   	if(!migrate_contact_first_names()) {
	   		die("Error migrating contact names...");
	   	}
		}
		
		if(!$TABLE_DEF->field_exists("name_last")) {
	   	$query = "ALTER TABLE `public_gui_users` ADD `name_last` CHAR(32) DEFAULT '' NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `public_gui_users`.name_last");
	   	
	   	if(!migrate_contact_last_names()) {
	   		die("Error migrating contact names...");
	   	}
		}
		
		if(!$TABLE_DEF->field_exists("name_salutation")) {
	   	$query = "ALTER TABLE `public_gui_users` ADD `name_salutation` ENUM('', 'Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.' ) DEFAULT '' NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `public_gui_users`.name_salutation");
		}
		
		if($TABLE_DEF->field_exists("full_name")) {
			$TABLE_DEF->drop_field("full_name");
		}
		
		if($TABLE_DEF->field_exists("mailing_country")) {
			$query = "ALTER TABLE `public_gui_users` CHANGE `mailing_country` `mailing_country_old` VARCHAR( 64 ) NOT NULL";
			$TABLE_DEF->run_sql($query,"Renaming public_gui_users.`mailing_country` to public_gui_users.`mailing_country_old`");
		}
		
		if(!$TABLE_DEF->field_exists("mailing_country_id")) {
	   	$query = "ALTER TABLE `public_gui_users` ADD `mailing_country_id` int(10) unsigned NOT NULL";
	   	$TABLE_DEF->run_sql($query,"Adding `public_gui_users`.mailing_country_id");
	   	$TABLE_DEF->add_index("mailing_country_id",1,array("mailing_country_id"));
		}
   }
	
	$TABLE_DEF = new CER_DB_TABLE("dispatcher_assignment_queue",true);

	if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("entity_type")) {
	      $query = "ALTER TABLE `dispatcher_assignment_queue` ADD `entity_type` ENUM( 'department', 'team', 'agent' ) NOT NULL AFTER `ticket_id`";
	      $TABLE_DEF->run_sql($query, "Adding `dispatcher_assignment_queue`.entity_type");
	   }
	   
	   if(!$TABLE_DEF->field_exists("event_type") && $TABLE_DEF->field_exists("type")) {
	      $query = "ALTER TABLE `dispatcher_assignment_queue` CHANGE `type` `event_type` TINYINT( 4 ) DEFAULT '0' NOT NULL";
	      $cerberus_db->query($query);
	   }
		
	   if(!$TABLE_DEF->field_exists("entity_id") && $TABLE_DEF->field_exists("user_id")) {
	      $query = "ALTER TABLE `dispatcher_assignment_queue` CHANGE `user_id` `entity_id` INT( 11 ) DEFAULT '0' NOT NULL";
	      $cerberus_db->query($query);
	   }
	   
	   if(!$TABLE_DEF->index_exists('entity_type')) {
	      $query = "ALTER TABLE `dispatcher_assignment_queue` ADD INDEX `entity_type` ( `entity_type` ) ";
		   $cerberus_db->query($query);  
	   }
	}
	
	$TABLE_DEF = new CER_DB_TABLE("thread_attachments",true);
	if($TABLE_DEF->check(false,true)) {
		if(!$TABLE_DEF->index_exists('search')) {
			$query = "ALTER TABLE `thread_attachments` ADD INDEX `search` ( `thread_id` , `file_name` )";
			$cerberus_db->query($query);
		}
	}
   
   
	$TABLE_DEF = new CER_DB_TABLE("skill_to_agent",true);
	
	if($TABLE_DEF->check(false,true)) {
		if(!$TABLE_DEF->check_index_fields('has_skill', 1, array('has_skill', 'agent_id'))) {
		   $query = "ALTER TABLE `skill_to_agent` DROP INDEX `has_skill`";
		   $cerberus_db->query($query);
		}
		
		if(!$TABLE_DEF->index_exists('has_skill')) {
		   $query = "ALTER TABLE `skill_to_agent` ADD INDEX `has_skill` ( `has_skill` , `agent_id` )";
		   $cerberus_db->query($query);
		}
	}
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);

	if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("skill_count")) {
	      $query = "ALTER TABLE `ticket` ADD `skill_count` MEDIUMINT NOT NULL ;";
	      $TABLE_DEF->run_sql($query, "Adding `ticket`.skillcount");
	   }
	}
	
	$TABLE_DEF = new CER_DB_TABLE("dispatcher_ticket_rejects",true);

	if($TABLE_DEF->check(false,true)) {
      $query = "DROP TABLE `dispatcher_ticket_rejects`";
      $TABLE_DEF->run_sql($query, "Dropping `dispatcher_ticket_rejects`");
	}
	
	$TABLE_DEF = new CER_DB_TABLE("dispatcher_assignment_queue",true);

	if($TABLE_DEF->check(false,true)) {
      $query = "DROP TABLE `dispatcher_assignment_queue`";
      $TABLE_DEF->run_sql($query, "Dropping `dispatcher_assignment_queue`");
	}
	
	$TABLE_DEF = new CER_DB_TABLE("team_members",true);

	if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("member_id")) {
	      $query = "ALTER TABLE `team_members` ADD `member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;";
	      $TABLE_DEF->run_sql($query, "Adding `team_members`.member_id");
	   }
	}
	
	$TABLE_DEF = new CER_DB_TABLE("dispatcher_delays",true);

	if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("reason")) {
	      $query = "ALTER TABLE `dispatcher_delays` ADD `reason` CHAR( 255 ) NOT NULL ;";
	      $TABLE_DEF->run_sql($query, "Adding `dispatcher_delays`.reason");
	   }
	}
   
   $TABLE_DEF = new CER_DB_TABLE("gateway_session",true);

	if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("chat_status")) {
	      $query = "ALTER TABLE `gateway_session` ADD `chat_status` TINYINT UNSIGNED NOT NULL ;";
	      $TABLE_DEF->run_sql($query, "Adding `gateway_session`.chat_status");
	      $query = "ALTER TABLE `gateway_session` ADD INDEX ( `chat_status` ) ;";
	      $TABLE_DEF->run_sql($query, "Adding `gateway_session`.chat_status index");
	   }
	}
   
   $TABLE_DEF = new CER_DB_TABLE("user_extended_info",true);

	if($TABLE_DEF->check(false,true)) {
	   if($TABLE_DEF->field_exists("chat_status")) {
	      $query = "ALTER TABLE `user_extended_info` DROP `chat_status`";
	      $TABLE_DEF->run_sql($query, "Dropping `user_extended_info`.chat_status");
	   }
	}
   
   $TABLE_DEF = new CER_DB_TABLE("team_members",true);

	if($TABLE_DEF->check(false,true)) {
		if($TABLE_DEF->field_exists("agent_options")) {
			$TABLE_DEF->drop_field("agent_options");
		}
		
		if(!$TABLE_DEF->field_exists("ticket_pull")) {
	      $query = "ALTER TABLE `team_members` ADD `ticket_pull` TINYINT UNSIGNED NOT NULL";
	      $TABLE_DEF->run_sql($query, "Adding `team_members`.ticket_pull");
	   }
	}
	
	$TABLE_DEF = new CER_DB_TABLE("team_queues",true);

	if($TABLE_DEF->check(false,true)) {
	   if(!$TABLE_DEF->field_exists("queue_access")) {
	      $query = "ALTER TABLE `team_queues` ADD `queue_access` TINYINT UNSIGNED NOT NULL";
	      $TABLE_DEF->run_sql($query, "Adding `team_queues`.queue_access");
	   }
	}
	
	strip_slashes_from_contact_names();
}

function strip_slashes_from_contact_names() {
        global $cerberus_db;
        $cerberus_db->query("UPDATE public_gui_users SET name_first = REPLACE(name_first, \"\\\\'\", \"'\");");
        $cerberus_db->query("UPDATE public_gui_users SET name_last = REPLACE(name_last, \"\\\\'\", \"'\");");                  
}

function migrate_company_2line_addresses() {
   global $cerberus_db;
	$sql = "UPDATE `company` SET `company_mailing_address` = CONCAT(company_mailing_street1,'\n',company_mailing_street2)";
	return $cerberus_db->query($sql);	
}

function migrate_contact_2line_addresses() {
   global $cerberus_db;
	$sql = "UPDATE `public_gui_users` SET `mailing_address` = CONCAT(mailing_street1,'\n',mailing_street2)";
	return $cerberus_db->query($sql);	
}

function migrate_contact_first_names() {
	global $cerberus_db;
	$sql = "UPDATE `public_gui_users` SET `name_first` = LEFT(full_name, LOCATE(' ',full_name)-1)";
	return $cerberus_db->query($sql);
}

function migrate_contact_last_names() {
	global $cerberus_db;
	$sql = "UPDATE `public_gui_users` SET `name_last` = RIGHT(full_name, LENGTH(full_name) - LOCATE(' ', full_name)+1)";
	$bool = $cerberus_db->query($sql);
	
	$sql = "UPDATE `public_gui_users` SET `name_last` = TRIM(`name_last`)";
	$cerberus_db->query($sql);
	
	return $bool;
}

// ***************************************************************************
// `ticket_tasks` table
// ***************************************************************************
function init_table_ticket_tasks()
{
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("ticket_tasks",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `ticket_tasks` (".
		"  `task_id` bigint(20) unsigned NOT NULL auto_increment,".
		"  `ticket_id` bigint(20) unsigned NOT NULL default '0',".
		"  `estimate` int(10) unsigned NOT NULL default '0',".
		"  `date_added` bigint(20) unsigned NOT NULL default '0',".
		"  `completed` tinyint(3) unsigned NOT NULL default '0',".
		"  `title` char(128) NOT NULL default '',".
		"  PRIMARY KEY  (`task_id`),".
		"  KEY `ticket_id` (`ticket_id`)".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["task_id"] = new CER_DB_FIELD("task_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["estimate"] = new CER_DB_FIELD("estimate","int(10) unsigned","","","0","");
   $TABLE_DEF->fields["date_added"] = new CER_DB_FIELD("date_added","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["completed"] = new CER_DB_FIELD("completed","tinyint(3) unsigned","","","0","");
   $TABLE_DEF->fields["title"] = new CER_DB_FIELD("title","char(128)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","task_id");
   $TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");

   table($TABLE_DEF);

}

function change_ticket_statuses()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	
	require_once("cerberus-api/ticket/cer_TicketStatuses.class.php");
	$ticket_status_handler = cer_TicketStatuses::getInstance();
	$ticket_statuses = $ticket_status_handler->getTicketStatuses();
	$orig_count = count($ticket_statuses);
	
	if(!isset($ticket_statuses["awaiting-reply"])) {
		$ticket_statuses["awaiting-reply"] = "awaiting-reply";
	}
	
	if(!isset($ticket_statuses["customer-reply"])) {
		$ticket_statuses["customer-reply"] = "customer-reply";
	}
	
	if(!isset($ticket_statuses["bounced"])) {
		$ticket_statuses["bounced"] = "bounced";
	}
	
	if(!isset($ticket_statuses["dead"])) {
		$ticket_statuses["dead"] = "dead";
	}
	
	if(!isset($ticket_statuses["resolved"])) {
		$ticket_statuses["resolved"] = "resolved";
	}
	
	if(!isset($ticket_statuses["new"])) {
		$ticket_statuses["new"] = "new";
	}
	
	// [JAS]: Add new statuses
	if(count($ticket_statuses) != $orig_count) {
		$sql = "ALTER TABLE `ticket` CHANGE `ticket_status` `ticket_status` ENUM('" . implode("','", array_values($ticket_statuses)) . "') DEFAULT 'new' NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding missing ticket status options");
	}
	
	// [JAS]: Catch any leftover tickets and set them to 'new'.	
	$sql = "SELECT count(*) as stray_tickets FROM `ticket` WHERE `ticket_status` = ''";
	$res = $cerberus_db->query($sql);
	if($row = $cerberus_db->grab_first_row($res)) {
		if($row["stray_tickets"] > 0) {
			$sql = "UPDATE `ticket` SET `ticket_status` = 'new' WHERE `ticket_status` = ''";
			$TABLE_DEF->run_sql($sql,"Cleaning up any stray ticket statuses");
		}
	}
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
		"`report_data` TEXT NOT NULL ,".
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
		"`user_id` INT(11) UNSIGNED NOT NULL ,".
		"`workspace_id` INT(11) UNSIGNED NOT NULL ,".
		"`pref_id` INT(11) UNSIGNED NOT NULL ,".
		"`pref_xml` TEXT NOT NULL ,".
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

function update_table_configuration()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["helpdesk_title"])) {
		$TABLE_DEF->add_field("helpdesk_title","VARCHAR(250) DEFAULT '' NOT NULL");
		
		$sql = "UPDATE configuration SET helpdesk_title = ''";
		$TABLE_DEF->run_sql($sql,"Adding browser title to configuration");
	}
}

function update_table_rule_action()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("rule_action",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["action_value"]) 
		&& strtolower($TABLE_DEF->fields["action_value"]->field_type) != "text") {
			$TABLE_DEF->run_sql("ALTER TABLE `rule_action` CHANGE `action_value` `action_value` TEXT NOT NULL","Changing rule_action.action_value type to 'text'");
	}
}

function update_table_chat_visitors()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("chat_visitors",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["visitor_host_id"])) {
		$TABLE_DEF->add_field("visitor_host_id","int(10) unsigned NOT NULL");
		$TABLE_DEF->add_index("visitor_host_id",1,array("visitor_host_id"));
	}

	if(!isset($TABLE_DEF->fields["visitor_browser_id"])) {
		$TABLE_DEF->add_field("visitor_browser_id","int(10) unsigned NOT NULL");
		$TABLE_DEF->add_index("visitor_browser_id",1,array("visitor_browser_id"));
	}
	
	if(!isset($TABLE_DEF->indexes["visitor_host"]) && $TABLE_DEF->field_exists("visitor_host")) {
		$TABLE_DEF->add_index("visitor_host",1,array("visitor_host"));
	}
	
}

function update_table_chat_visitor_pages()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("chat_visitor_pages",true);
	$TABLE_DEF->check();

	if(!isset($TABLE_DEF->indexes["page_name"]) && $TABLE_DEF->field_exists("page_name")) {
		$TABLE_DEF->add_index("page_name",1,array("page_name"));
	}
	
	if(!isset($TABLE_DEF->fields["page_url_id"])) {
		$TABLE_DEF->add_field("page_url_id","bigint unsigned NOT NULL");
		$TABLE_DEF->add_index("page_url_id",1,array("page_url_id"));
	}

	if(!isset($TABLE_DEF->fields["page_referrer_url_id"])) {
		$TABLE_DEF->add_field("page_referrer_url_id","bigint unsigned NOT NULL");
		$TABLE_DEF->add_index("page_referrer_url_id",1,array("page_referrer_url_id"));
	}
	
	if(!isset($TABLE_DEF->fields["page_referrer_host_id"])) {
		$TABLE_DEF->add_field("page_referrer_host_id","int(10) unsigned NOT NULL");
		$TABLE_DEF->add_index("page_referrer_host_id",1,array("page_referrer_host_id"));
	}

	
}

function migrate_chat_strings() {
	global $cerberus_db;

	echo "Migrating chat data to new MajorCRM format...";
	
	flush();
	
	$sql = "INSERT IGNORE INTO stat_hosts (host) VALUES('')";
	$cerberus_db->query($sql);
	
	$sql = "INSERT IGNORE INTO stat_urls (url) VALUES('')";
	$cerberus_db->query($sql);
	
	$sql = "INSERT IGNORE INTO stat_hosts (host) SELECT DISTINCT(cv.visitor_host) FROM chat_visitors cv";
	$cerberus_db->query($sql);

	$sql = "INSERT IGNORE INTO stat_urls (url) SELECT DISTINCT(cp.page_name) FROM chat_visitor_pages cp";
	$cerberus_db->query($sql);

	$sql = "INSERT IGNORE INTO stat_urls (url) SELECT DISTINCT(cp.page_referrer) FROM chat_visitor_pages cp";
	$cerberus_db->query($sql);

	$sql = "INSERT IGNORE INTO stat_hosts (host) SELECT DISTINCT(cp.page_referrer_host) FROM chat_visitor_pages cp";
	$cerberus_db->query($sql);

	$sql = "INSERT IGNORE INTO stat_browsers (browser) SELECT DISTINCT(cv.visitor_browser) FROM chat_visitors cv";
	$cerberus_db->query($sql);
	
	// chat_visitors.visitor_host -> chat_visitors.visitor_host_id
	$sql = "SELECT h.host_id, cv.visitor_id from stat_hosts h, chat_visitors cv WHERE h.host = cv.visitor_host AND cv.visitor_host_id = 0";
	$res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE chat_visitors SET visitor_host_id = %d WHERE visitor_id = %d AND visitor_host_id = 0",
					$row["host_id"],
					$row["visitor_id"]
				);
			$cerberus_db->query($sql);
		}
	}

	// chat_visitor_pages.page_referrer_host -> chat_visitor_pages.page_referrer_host_id
	$sql = "SELECT h.host_id, cp.page_id from stat_hosts h, chat_visitor_pages cp WHERE h.host = cp.page_referrer_host AND cp.page_referrer_host_id = 0;";
	$res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE chat_visitor_pages SET page_referrer_host_id = %d WHERE page_id = %d AND page_referrer_host_id = 0",
					$row["host_id"],
					$row["page_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	// chat_visitor_pages.page_referrer -> chat_visitor_pages.page_referrer_url_id
	$sql = "SELECT u.url_id, cp.page_id from stat_urls u, chat_visitor_pages cp WHERE u.url = cp.page_referrer AND cp.page_referrer_url_id = 0;";
	$res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE chat_visitor_pages SET page_referrer_url_id = %d WHERE page_id = %d AND page_referrer_url_id = 0",
					$row["url_id"],
					$row["page_id"]
				);
			$cerberus_db->query($sql);
		}
	}

	// chat_visitor_pages.page_name -> chat_visitor_pages.page_url_id
	$sql = "SELECT u.url_id, cp.page_id from stat_urls u, chat_visitor_pages cp WHERE u.url = cp.page_name AND cp.page_url_id = 0;";
	$res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE chat_visitor_pages SET page_url_id = %d WHERE page_id = %d AND page_url_id = 0",
					$row["url_id"],
					$row["page_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	// chat_visitors.visitor_browser -> chat_visitors.visitor_browser_id
	$sql = "SELECT b.browser_id, cv.visitor_id from stat_browsers b, chat_visitors cv WHERE b.browser = cv.visitor_browser AND cv.visitor_browser_id = 0;";
	$res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE chat_visitors SET visitor_browser_id = %d WHERE visitor_id = %d AND visitor_browser_id = 0",
					$row["browser_id"],
					$row["visitor_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	// [JAS]: Drop old indexes
	$TABLE_DEF = new CER_DB_TABLE("chat_visitor_pages",true);

//	if(isset($TABLE_DEF->indexes["page_referrer"])) {
//		$TABLE_DEF->run_sql("ALTER TABLE chat_visitor_pages DROP INDEX page_referrer","Dropping index chat_visitor_pages.page_referrer");
//	}
//
//	if(isset($TABLE_DEF->indexes["page_referrer_host"])) {
//		$TABLE_DEF->run_sql("ALTER TABLE chat_visitor_pages DROP INDEX page_referrer_host","Dropping index chat_visitor_pages.page_referrer_host");
//	}
//
	if($TABLE_DEF->field_exists("page_name")) {
		$TABLE_DEF->drop_field("page_name");
		flush();
	}

	if($TABLE_DEF->field_exists("page_referrer")) {
		$TABLE_DEF->drop_field("page_referrer");
		flush();
	}

	if($TABLE_DEF->field_exists("page_referrer_host")) {
		$TABLE_DEF->drop_field("page_referrer_host");
		flush();
	}

	$TABLE_DEF = new CER_DB_TABLE("chat_visitors",true);
	
	if($TABLE_DEF->field_exists("visitor_host")) {
		$TABLE_DEF->drop_field("visitor_host");
		flush();
	}

	if($TABLE_DEF->field_exists("visitor_browser")) {
		$TABLE_DEF->drop_field("visitor_browser");
		flush();
	}

	
	echo "<font color='green'>success!</font><br>";
	
	flush();
	
}

function update_table_company()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("company",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["import_source"])) {
		$TABLE_DEF->add_field("import_source","CHAR( 10 ) NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["import_id"])) {
		$TABLE_DEF->add_field("import_id","CHAR( 32 ) NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["created_date"])) {
		$TABLE_DEF->add_field("created_date","BIGINT(20) UNSIGNED NOT NULL");
	}
}

function update_table_public_gui_users()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["import_source"])) {
		$TABLE_DEF->add_field("import_source","CHAR( 10 ) NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["import_id"])) {
		$TABLE_DEF->add_field("import_id","CHAR( 32 ) NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["created_date"])) {
		$TABLE_DEF->add_field("created_date","BIGINT(20) UNSIGNED NOT NULL");
	}
}

function update_table_opportunity()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("opportunity",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["import_source"])) {
		$TABLE_DEF->add_field("import_source","CHAR( 10 ) NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["import_id"])) {
		$TABLE_DEF->add_field("import_id","CHAR( 32 ) NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["created_date"])) {
		$TABLE_DEF->add_field("created_date","BIGINT(20) UNSIGNED NOT NULL");
	}
}
