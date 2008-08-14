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
| File: cerb_232_to_240.php
|
| Purpose: Upgrades the database structure from 2.3.2 to 2.4.0
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/stats/cer_SystemStats.class.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_handlers.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/acl_groups.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.3.2 to 2.4.0 Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20040304");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","b81131b5d008d10c09049d667dd6a4c4"); // 2.3.2 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	migrate_content_parts();
	migrate_custom_fields();
	
	init_table_sla_to_queue();
	init_table_schedule();
	init_table_trigram_training();
	init_table_trigram_to_ticket();
	init_table_public_gui_users();
	init_table_trigram_stats();
	init_table_stats_system();
	
	update_table_address();
	update_table_company();
	update_table_email_domains();
	update_table_email_templates();
	update_table_field_group_values();
	update_table_knowledgebase();
	update_table_log();
	update_table_public_gui_users();
	update_table_public_gui_profiles();
	update_table_queue();
	update_table_sla();
	update_table_thread();
	update_table_ticket();
	update_table_ticket_views();
	update_table_user();
	update_table_user_prefs();
	update_table_search_index();
	update_table_trigram_to_ticket();
	update_table_trigram_to_thread();
	update_table_trigram_to_kb();
	update_table_trigrams();
	
	find_invalid_threads();
	fix_max_thread_ids();
	set_default_acl();
	
	set_precursor_hashes();
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('8b01eecd923a838206d5f653478852b4',NOW())"; // 2.4.0 clean
	$cerberus_db->query($sql);
}


function find_invalid_threads()
{
	global $cerberus_db;
	$cer_parser = new CER_PARSER();
	$o_raw_email = new CERB_RAW_EMAIL();
	$unrecoverable_threads = array();
	
	$sql = "SELECT th.thread_id, th.thread_subject FROM `thread` th WHERE th.ticket_id = 0;";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$thread_id = $row["thread_id"];
			$ticket_id = $cer_parser->find_ticketid_in_subject($row["thread_subject"]);
			
			if(!$ticket_id) {
				$thread_handler = new cer_ThreadContentHandler();
				$thread_handler->loadThreadContent($thread_id);
				$o_raw_email->body = $thread_handler->threads[$thread_id]->content;
				$ticket_id = $cer_parser->find_ticketid_in_body($o_raw_email);
			}
			
			if(!$ticket_id) {
				$unrecoverable_threads[] = $thread_id;
			}
			else {
				echo "Fixing unlinked thread $thread_id for \"" . $row["thread_subject"] . "\".  Should be part of ticket " . $ticket_id . "<BR>";
				$sql = "UPDATE thread SET ticket_id = $ticket_id WHERE thread_id = $thread_id";
				$cerberus_db->query($sql);
			}
		}
		
		if(!empty($unrecoverable_threads)) {
			echo "Cleaning up unrecoverable threads, which are probably junk parts of previously deleted spam/notices<br>";
			$sql = "INSERT INTO ticket (ticket_status) VALUES ('dead')";
			$cerberus_db->query($sql);

			$tag_id = $cerberus_db->insert_id();
			
			$sql = "UPDATE thread SET ticket_id = $tag_id WHERE thread_id IN (" .
				implode(",",$unrecoverable_threads) . 
				")";
			$cerberus_db->query($sql);
		}
	}
}

// ***************************************************************************
// `search_index` table
// ***************************************************************************
function update_table_search_index()
{
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("search_index",true);
	$TABLE_DEF->check(true);	
	
	if(!isset($TABLE_DEF->fields["in_first_thread"])) {
		$TABLE_DEF->add_field("in_first_thread","TINYINT UNSIGNED DEFAULT '0' NOT NULL");
		$TABLE_DEF->add_index("in_first_thread","1",array('in_first_thread'));
	}
	
	$TABLE_DEF->add_index("ticket_id","1",array('ticket_id'));
}

function fix_max_thread_ids()
{
	global $cerberus_db;
	
	$sql = "SELECT t.ticket_id, t.max_thread_id, MAX( th.thread_id )  AS real_max_thread_id FROM  `ticket` t, thread th WHERE t.ticket_id = th.ticket_id AND t.ticket_status =  'resolved' GROUP  BY t.ticket_id HAVING t.max_thread_id <> real_max_thread_id";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$ticket_id = $row["ticket_id"];
			$old_max = $row["max_thread_id"];
			$new_max = $row["real_max_thread_id"];
			echo "Fixing invalid max_thread_id ( old: $old_max new: $new_max ) for ticket $ticket_id<br>";
			$sql = "UPDATE ticket SET max_thread_id = $new_max WHERE ticket_id = $ticket_id";
			$cerberus_db->query($sql);
		}
	}
}


function init_table_sla_to_queue()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("sla_to_queue",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE sla_to_queue (".
		"sla_id bigint unsigned NOT NULL ,".
		"queue_id bigint unsigned NOT NULL,".
		"schedule_id bigint unsigned NOT NULL,".
		"response_time int NOT NULL,".
		"UNIQUE (".
		" `sla_id` ,".
		" `queue_id` ".
		" ) ".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["sla_id"] = new CER_DB_FIELD("sla_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["schedule_id"] = new CER_DB_FIELD("schedule_id","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["response_time"] = new CER_DB_FIELD("response_time","int(11)","","","0","");

	$TABLE_DEF->indexes["sla_id"] = new CER_DB_INDEX("sla_id","0",array("sla_id","queue_id"));	
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `schedule` table
// ***************************************************************************
function init_table_schedule()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("schedule",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE schedule (".
		"schedule_id bigint(20) unsigned NOT NULL auto_increment,".
		"schedule_name char(64) NOT NULL default '',".
		"sun_hrs char(10) NOT NULL default '',".
		"sun_open char(5) NOT NULL default '00:00',".
		"sun_close char(5) NOT NULL default '00:00',".
		"mon_hrs char(10) NOT NULL default '',".
		"mon_open char(5) NOT NULL default '00:00',".
		"mon_close char(5) NOT NULL default '00:00',".
		"tue_hrs char(10) NOT NULL default '',".
		"tue_open char(5) NOT NULL default '00:00',".
		"tue_close char(5) NOT NULL default '00:00',".
		"wed_hrs char(10) NOT NULL default '',".
		"wed_open char(5) NOT NULL default '00:00',".
		"wed_close char(5) NOT NULL default '00:00',".
		"thu_hrs char(10) NOT NULL default '',".
		"thu_open char(5) NOT NULL default '00:00',".
		"thu_close char(5) NOT NULL default '00:00',".
		"fri_hrs char(10) NOT NULL default '',".
		"fri_open char(5) NOT NULL default '00:00',".
		"fri_close char(5) NOT NULL default '00:00',".
		"sat_hrs char(10) NOT NULL default '',".
		"sat_open char(5) NOT NULL default '00:00',".
		"sat_close char(5) NOT NULL default '00:00',".
		"PRIMARY KEY  (schedule_id)".
		") TYPE=MyISAM";

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
	$TABLE_DEF->fields["stue_close"] = new CER_DB_FIELD("tue_close","char(5)","","","00:00","");
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

	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("schedule_id"));	
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `public_gui_users` table
// ***************************************************************************
function init_table_public_gui_users()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE public_gui_users (".
		"public_user_id bigint(20) unsigned NOT NULL auto_increment,".
		"full_name char(64) NOT NULL default '',".
		"mailing_street1 char(64) NOT NULL default '',".
		"mailing_street2 char(64) NOT NULL default '',".
		"mailing_city char(64) NOT NULL default '',".
		"mailing_state char(64) NOT NULL default '',".
		"mailing_zip char(32) NOT NULL default '',".
		"mailing_country char(64) NOT NULL default '',".
		"phone_work char(16) NOT NULL default '',".
		"phone_home char(16) NOT NULL default '',".
		"phone_mobile char(16) NOT NULL default '',".
		"phone_fax char(16) NOT NULL default '',".
		"password char(32) NOT NULL default '',".
		"PRIMARY KEY  (public_user_id) ".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["public_user_id"] = new CER_DB_FIELD("public_user_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["full_name"] = new CER_DB_FIELD("full_name","char(64)","","","","");
	$TABLE_DEF->fields["mailing_street1"] = new CER_DB_FIELD("mailing_street1","char(64)","","","","");
	$TABLE_DEF->fields["mailing_street2"] = new CER_DB_FIELD("mailing_street2","char(64)","","","","");
	$TABLE_DEF->fields["mailing_city"] = new CER_DB_FIELD("mailing_city","char(64)","","","","");
	$TABLE_DEF->fields["mailing_state"] = new CER_DB_FIELD("mailing_state","char(64)","","","","");
	$TABLE_DEF->fields["mailing_zip"] = new CER_DB_FIELD("mailing_zip","char(32)","","","","");
	$TABLE_DEF->fields["mailing_country"] = new CER_DB_FIELD("mailing_country","char(64)","","","","");
	$TABLE_DEF->fields["phone_work"] = new CER_DB_FIELD("phone_work","char(16)","","","","");
	$TABLE_DEF->fields["phone_home"] = new CER_DB_FIELD("phone_home","char(16)","","","","");
	$TABLE_DEF->fields["phone_mobile"] = new CER_DB_FIELD("phone_mobile","char(16)","","","","");
	$TABLE_DEF->fields["phone_fax"] = new CER_DB_FIELD("phone_fax","char(16)","","","","");
	$TABLE_DEF->fields["password"] = new CER_DB_FIELD("password","char(32)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("public_user_id"));	
	
	table($TABLE_DEF);
}

function update_table_public_gui_users()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["company_id"])) {
		$TABLE_DEF->add_field("company_id", "BIGINT UNSIGNED DEFAULT '0' NOT NULL");
		$TABLE_DEF->add_index("company_id","1",array('company_id'));	
	}
	
}

function update_table_company()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("company",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["company_account_number"])) {
		$TABLE_DEF->add_field("company_account_number", "char(64) DEFAULT '' NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["company_mailing_street1"])) {
		$TABLE_DEF->add_field("company_mailing_street1", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_mailing_street2"])) {
		$TABLE_DEF->add_field("company_mailing_street2", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_mailing_city"])) {
		$TABLE_DEF->add_field("company_mailing_city", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_mailing_state"])) {
		$TABLE_DEF->add_field("company_mailing_state", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_mailing_zip"])) {
		$TABLE_DEF->add_field("company_mailing_zip", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_mailing_country"])) {
		$TABLE_DEF->add_field("company_mailing_country", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_phone"])) {
		$TABLE_DEF->add_field("company_phone", "char(16) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_fax"])) {
		$TABLE_DEF->add_field("company_fax", "char(16) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_website"])) {
		$TABLE_DEF->add_field("company_website", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["company_email"])) {
		$TABLE_DEF->add_field("company_email", "char(64) DEFAULT '' NOT NULL");
	}
	
	if(isset($TABLE_DEF->indexes["id"])) {
		$sql = "ALTER TABLE `company` DROP INDEX `id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `company`.`id`.");
	}
	
}


// ***************************************************************************
// `trigram_training` table
// ***************************************************************************
function init_table_trigram_training()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("trigram_training",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE IF NOT EXISTS `trigram_training` (".
							" `ticket_id` BIGINT UNSIGNED DEFAULT '0' NOT NULL ,".
							" `kb_id` BIGINT UNSIGNED DEFAULT '0' NOT NULL ,".
							" `user_id` BIGINT UNSIGNED NOT NULL ,".
							" PRIMARY KEY ( `ticket_id` , `kb_id` ) ".
							");";

	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["user_id"] = new CER_DB_FIELD("user_id","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("ticket_id","kb_id"));	
	
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
								" `kb_id` BIGINT UNSIGNED DEFAULT '0' NOT NULL ,".
								" `num_good` BIGINT UNSIGNED DEFAULT '0' NOT NULL ,".
								" `num_bad` BIGINT UNSIGNED DEFAULT '0' NOT NULL ,".
								" PRIMARY KEY ( `kb_id` ) ".
								");";
								
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["num_good"] = new CER_DB_FIELD("num_good","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["num_bad"] = new CER_DB_FIELD("num_bad","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("kb_id"));	

	table($TABLE_DEF);
}


// ***************************************************************************
// `stats_system` table
// ***************************************************************************

function migrate_system_stats()
{
	global $cerberus_db;
	// get the earliest audit log entry:
	$sql = "SELECT MIN(epoch) as the_beginning FROM ticket_audit_log";
	$result = $cerberus_db->query($sql);
	$row = $cerberus_db->grab_first_row($result);
	$the_beginning = $row["the_beginning"];
	
	$cer_stats = new cer_SystemStats();
	
	$date = time();
	
	// loop forever, break out later
	if(NULL!=$the_beginning) {
		while($the_beginning < $date) {
		    $date_string = date("n,j,Y",$date);
		    $date_array = split(",",$date_string);
		    $day_begin = mktime(0,0,0,$date_array[0],$date_array[1],$date_array[2]);
		    $day_end = mktime(23,59,59,$date_array[0],$date_array[1],$date_array[2]);
		    
		    $sql = sprintf("SELECT count(*) As total, t.ticket_queue_id ".
		                   "FROM ticket_audit_log tal, ticket t WHERE tal.ticket_id=t.ticket_id AND tal.action=1 AND tal.epoch <= %s AND tal.epoch >= %s ".
		                   "GROUP BY t.ticket_queue_id",
		                   		$day_end,
		                        $day_begin
		                  );
		    $result = $cerberus_db->query($sql);
		    if($cerberus_db->num_rows($result)) {
		        while($row = $cerberus_db->fetch_row($result)) {
		       	   	$cer_stats->_incrementStat($cer_stats->_getDate($date), CER_SYSTEMSTATS_TICKET, $row["ticket_queue_id"], $row["total"]);
		        }
		    } 
	
			$date-=86400; // subtract day offset
		} // while
	} // if
}

function init_table_stats_system()
{
	global $cerberus_db;
	$migrate = false;
	
	$TABLE_DEF = new CER_DB_TABLE("stats_system",true);
	
	if(!isset($TABLE_DEF->fields["stat_id"])) {
		// table doesn't exist yet, migrate data after creation
		$migrate=true;
	}
	else {
		// table already created, remove index if it exists
		if(isset($TABLE_DEF->indexes["stat_count"])) {
			$sql = "ALTER TABLE `stats_system` DROP INDEX `stat_count`";
			$TABLE_DEF->run_sql($sql,"Removing extra index `stats_system`.`stat_count`.");
		}
		
		// re-read the information
		$TABLE_DEF = new CER_DB_TABLE("stats_system",true);
	}
	
	
	$TABLE_DEF->create_sql = "CREATE TABLE `stats_system` (".
								  "`stat_id` bigint(20) unsigned NOT NULL auto_increment,".
								  "`stat_date` date NOT NULL default '0000-00-00',".
								  "`stat_type` int(10) unsigned NOT NULL default '0',".
								  "`stat_extra` bigint(20) unsigned NOT NULL default '0',".
								  "`stat_count` bigint(20) unsigned NOT NULL default '0',".
								  "PRIMARY KEY  (`stat_id`),".
								  "KEY `stat_date` (`stat_date`),".
								  "KEY `stat_type` (`stat_type`),".
								  "KEY `stat_extra` (`stat_extra`)".
								") TYPE=MyISAM";

	$TABLE_DEF->fields["stat_id"]    = new CER_DB_FIELD("stat_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["stat_date"]  = new CER_DB_FIELD("stat_date","date","","MUL","0000-00-00","");
	$TABLE_DEF->fields["stat_type"]  = new CER_DB_FIELD("stat_type","int(10) unsigned","","MUL","0","");
	$TABLE_DEF->fields["stat_extra"] = new CER_DB_FIELD("stat_extra","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["stat_count"] = new CER_DB_FIELD("stat_count","bigint(20) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"]   = new CER_DB_INDEX("primary","0",array("stat_id"));
	$TABLE_DEF->indexes["stat_date"]   = new CER_DB_INDEX("stat_date","1",array("stat_date"));
	$TABLE_DEF->indexes["stat_type"]   = new CER_DB_INDEX("stat_type","1",array("stat_type"));
	$TABLE_DEF->indexes["stat_extra"]   = new CER_DB_INDEX("stat_extra","1",array("stat_extra"));
	
	table($TABLE_DEF);
	
	if($migrate) {
		migrate_system_stats();
	}
}

// ***************************************************************************
// `address` table
// ***************************************************************************
function update_table_address()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("address",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["address_password"])) {
		$TABLE_DEF->drop_field("address_password");
	}
	
	if(isset($TABLE_DEF->fields["company_id"])) {
		$TABLE_DEF->drop_field("company_id");
	}
	
	if(!isset($TABLE_DEF->fields["public_user_id"])) {
		$TABLE_DEF->add_field("public_user_id","bigint(20) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["confirmation_code"])) {
		$TABLE_DEF->add_field("confirmation_code","char(19) DEFAULT '' NOT NULL");
	}

	if(!isset($TABLE_DEF->indexes["public_user_id"])) {
		$TABLE_DEF->add_index("public_user_id","1",array('public_user_id'));
	}
}


// ***************************************************************************
// `email_domains` table
// ***************************************************************************
function update_table_email_domains()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("email_domains",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->indexes["id"])) {
		$sql = "ALTER TABLE `email_domains` DROP INDEX `id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `email_domains`.`id`.");
	}
}

// ***************************************************************************
// `email_templates` table
// ***************************************************************************
function update_table_email_templates()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("email_templates",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->indexes["template_id"])) {
		$sql = "ALTER TABLE `email_templates` DROP INDEX `template_id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `email_templates`.`template_id`.");
	}
} 


// ***************************************************************************
// `log` table
// ***************************************************************************
function update_table_log()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("log",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->indexes["log_id"])) {
		$sql = "ALTER TABLE `log` DROP INDEX `log_id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `log`.`log_id`.");
	}
} 



// ***************************************************************************
// `knowledgebase` table
// ***************************************************************************
function update_table_knowledgebase()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["kb_avg_rating"])) {
		$TABLE_DEF->add_field("kb_avg_rating","FLOAT(1) UNSIGNED NOT NULL");
		$TABLE_DEF->add_index("kb_avg_rating","1",array('kb_avg_rating'));		
		
		if(!isset($TABLE_DEF->fields["kb_rating_votes"])) {
			$TABLE_DEF->add_field("kb_rating_votes","int(10) UNSIGNED NOT NULL");
			$TABLE_DEF->add_index("kb_rating_votes","1",array('kb_rating_votes'));		
		}
		
		migrate_kb_ratings();
	}
	
	if(!isset($TABLE_DEF->fields["kb_public_views"])) {
		$TABLE_DEF->add_field("kb_public_views","BIGINT(11) UNSIGNED NOT NULL");
		$TABLE_DEF->add_index("kb_public_views","1",array('kb_public_views'));		
	}
}

function migrate_kb_ratings() {
	global $cerberus_db;
	
	$sql = "select kb_article_id, avg(rating) as rating_avg,count(rating_id) as rating_count from knowledgebase_ratings GROUP BY kb_article_id";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($kbr = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE knowledgebase SET kb_avg_rating = %f, kb_rating_votes = %d WHERE kb_id = %d",
					sprintf("%0.1f",$kbr["rating_avg"]),
					$kbr["rating_count"],
					$kbr["kb_article_id"]
				);
			$cerberus_db->query($sql);
			flush();
		}
	}
}


// ***************************************************************************
// `public_gui_profiles` table
// ***************************************************************************
function update_table_public_gui_profiles()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",true);
	$TABLE_DEF->check();

	if(isset($TABLE_DEF->fields["pub_new_registration_subject"])) {
		$TABLE_DEF->drop_field("pub_new_registration_subject");
	}
	
	if(isset($TABLE_DEF->fields["pub_new_registration_body"])) {
		$TABLE_DEF->drop_field("pub_new_registration_body");
	}
	
	if(!isset($TABLE_DEF->fields["pub_confirmation_subject"])) {
		$TABLE_DEF->add_field("pub_confirmation_subject","VARCHAR(128) DEFAULT '' NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["pub_confirmation_body"])) {
		$TABLE_DEF->add_field("pub_confirmation_body","TEXT DEFAULT '' NOT NULL");
	}

	if(isset($TABLE_DEF->fields["pub_kb_resolved_issue"])) {
		$TABLE_DEF->drop_field("pub_kb_resolved_issue");
	}
	
	if(isset($TABLE_DEF->fields["pub_kb_not_resolved_issue"])) {
		$TABLE_DEF->drop_field("pub_kb_not_resolved_issue");
	}
	
	if(isset($TABLE_DEF->fields["pub_hide_kb"])) {
		$TABLE_DEF->drop_field("pub_hide_kb");
	}
	
	if(isset($TABLE_DEF->fields["pub_locked_submit"])) {
		$TABLE_DEF->drop_field("pub_locked_submit");
	}
	
	if(isset($TABLE_DEF->fields["pub_registration_mode"])) {
		$TABLE_DEF->drop_field("pub_registration_mode");
	}

	if(!isset($TABLE_DEF->fields["pub_mod_registration"])) {
		$TABLE_DEF->add_field("pub_mod_registration","TINYINT DEFAULT '0' NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["pub_mod_registration_mode"])) {
		$TABLE_DEF->add_field("pub_mod_registration_mode","CHAR(12) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_kb"])) {
		$TABLE_DEF->add_field("pub_mod_kb","TINYINT DEFAULT '0' NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["pub_mod_my_account"])) {
		$TABLE_DEF->add_field("pub_mod_my_account","TINYINT DEFAULT '0' NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["pub_mod_open_ticket"])) {
		$TABLE_DEF->add_field("pub_mod_open_ticket","TINYINT DEFAULT '0' NOT NULL");
	}

	if(!isset($TABLE_DEF->fields["pub_mod_open_ticket_locked"])) {
		$TABLE_DEF->add_field("pub_mod_open_ticket_locked","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_track_tickets"])) {
		$TABLE_DEF->add_field("pub_mod_track_tickets","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_announcements"])) {
		$TABLE_DEF->add_field("pub_mod_announcements","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_welcome"])) {
		$TABLE_DEF->add_field("pub_mod_welcome","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_welcome_title"])) {
		$TABLE_DEF->add_field("pub_mod_welcome_title","CHAR(64) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_welcome_text"])) {
		$TABLE_DEF->add_field("pub_mod_welcome_text","TEXT DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_contact"])) {
		$TABLE_DEF->add_field("pub_mod_contact","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["pub_mod_contact_text"])) {
		$TABLE_DEF->add_field("pub_mod_contact_text","TEXT DEFAULT '' NOT NULL");
	}
	
}

// ***************************************************************************
// `thread_content_part` table
// ***************************************************************************
function init_table_thread_content_part()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_content_part",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `thread_content_part` (".
		"`content_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`thread_id` BIGINT UNSIGNED NOT NULL ,".
		"`thread_content_part` CHAR( 255 ) NOT NULL ,".
		"PRIMARY KEY ( `content_id` ) ,".
		"INDEX ( `thread_id` ) ".
		") TYPE = MYISAM;";
	  
	$TABLE_DEF->fields["content_id"] = new CER_DB_FIELD("content_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["thread_id"] = new CER_DB_FIELD("thread_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["thread_content_part"] = new CER_DB_FIELD("thread_content_part","char(255)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("content_id"));
	$TABLE_DEF->indexes["thread_id"] = new CER_DB_INDEX("thread_id","1",array("thread_id"));
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `entity_to_field_group` table
// ***************************************************************************
function init_table_entity_to_field_group()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("entity_to_field_group",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE entity_to_field_group (".
		"group_instance_id bigint(20) unsigned NOT NULL auto_increment,".
		"entity_code char(1) NOT NULL default '',".
		"entity_index bigint(20) unsigned NOT NULL default '0',".
		"group_id bigint(20) unsigned NOT NULL default '0',".
		" `order` tinyint(3) unsigned NOT NULL default '0',".
		" PRIMARY KEY ( group_instance_id ) ,".
		" KEY group_id( group_id ) ,".
		" KEY entity_code ( entity_code ),".
		" KEY entity_index ( entity_index )".
		") TYPE = MYISAM";
	  
	$TABLE_DEF->fields["group_instance_id"] = new CER_DB_FIELD("group_instance_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["entity_code"] = new CER_DB_FIELD("entity_code","char(1)","","MUL","","");
	$TABLE_DEF->fields["entity_index"] = new CER_DB_FIELD("entity_index","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["order"] = new CER_DB_FIELD("order","tinyint(3) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("group_instance_id"));
	$TABLE_DEF->indexes["group_id"] = new CER_DB_INDEX("group_id","1",array("group_id"));
	$TABLE_DEF->indexes["entity_code"] = new CER_DB_INDEX("entity_code","1",array("entity_code"));
	$TABLE_DEF->indexes["entity_index"] = new CER_DB_INDEX("entity_index","1",array("entity_index"));
	
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
		" `group_id` BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,".
		" `group_name` CHAR( 128 ) NOT NULL ,".
		" PRIMARY KEY ( `group_id` ) ".
		") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["group_id"] = new CER_DB_FIELD("group_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["group_name"] = new CER_DB_FIELD("group_name","char(128)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("group_id"));	

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
		" field_id bigint(20) unsigned NOT NULL auto_increment,".
		" field_name char(64) NOT NULL default '',".
		" field_type char(1) NOT NULL default 'S',".
		" field_not_searchable tinyint(3) unsigned NOT NULL default '0',".
		" field_group_id bigint(20) unsigned NOT NULL default '0',".
		" `order` tinyint(3) unsigned NOT NULL default '0',".
		" PRIMARY KEY  (field_id),".
		" KEY field_not_searchable (field_not_searchable),".
		" KEY field_group_id (field_group_id)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["field_name"] = new CER_DB_FIELD("field_name","char(64)","","","","");
	$TABLE_DEF->fields["field_type"] = new CER_DB_FIELD("field_type","char(1)","","","S","");
	$TABLE_DEF->fields["field_not_searchable"] = new CER_DB_FIELD("field_not_searchable","tinyint(3) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_group_id"] = new CER_DB_FIELD("field_group_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["order"] = new CER_DB_FIELD("order","tinyint(3) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("field_id"));	
	$TABLE_DEF->indexes["field_not_searchable"] = new CER_DB_INDEX("field_not_searchable","1",array("field_not_searchable"));	
	$TABLE_DEF->indexes["field_group_id"] = new CER_DB_INDEX("field_group_id","1",array("field_group_id"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `field_group_values` table
// ***************************************************************************
function init_table_field_group_values()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("field_group_values",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE field_group_values (".
		" field_id bigint(20) unsigned NOT NULL default '0',".
		" field_value char(255) NOT NULL default '',".
		" group_instance_id bigint(20) unsigned NOT NULL default '0',".
		" KEY field_id (field_id),".
		" KEY group_instance_id (group_instance_id)".
		") TYPE=MyISAM;";

	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["field_value"] = new CER_DB_FIELD("field_value","char(255)","","","","");
	$TABLE_DEF->fields["group_instance_id"] = new CER_DB_FIELD("group_instance_id","bigint(20) unsigned","","MUL","0","");
	
	$TABLE_DEF->indexes["field_id"] = new CER_DB_INDEX("field_id","1",array("field_id"));	
	$TABLE_DEF->indexes["group_instance_id"] = new CER_DB_INDEX("group_instance_id","1",array("group_instance_id"));	
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `fields_options` table
// ***************************************************************************
function init_table_fields_options()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("fields_options",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE fields_options (".
		"field_id bigint( 20 ) unsigned NOT NULL default '0',".
		" option_id bigint( 20 ) unsigned NOT NULL auto_increment,".
		" option_value char( 64 ) NOT NULL default '',".
		" `order` tinyint( 3 ) unsigned NOT NULL default '0',".
		" PRIMARY KEY ( option_id ) ,".
		" KEY field_id( field_id ) ".
		") TYPE = MYISAM;";

	$TABLE_DEF->fields["field_id"] = new CER_DB_FIELD("field_id","bigint(20) unsigned","","MUL","0","");
	$TABLE_DEF->fields["option_id"] = new CER_DB_FIELD("option_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["option_value"] = new CER_DB_FIELD("option_value","char(64)","","","","");
	$TABLE_DEF->fields["order"] = new CER_DB_FIELD("order","tinyint(3) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("option_id"));		
	$TABLE_DEF->indexes["field_id"] = new CER_DB_INDEX("field_id","1",array("field_id"));		
	
	table($TABLE_DEF);
}


// ***************************************************************************
// `field_group_values` table
// ***************************************************************************
function update_table_field_group_values() {
	global $cerberus_db;
	$needs_migrate = false;
	
	$TABLE_DEF = new CER_DB_TABLE("field_group_values",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["entity_code"])) {
		$TABLE_DEF->add_field("entity_code","CHAR(1) NOT NULL");
		$TABLE_DEF->add_index("entity_code","1",array('entity_code'));		
		$needs_migrate = true;
	}
	
	if(!isset($TABLE_DEF->fields["entity_index"])) {
		$TABLE_DEF->add_field("entity_index","bigint(20) unsigned NOT NULL");
		$TABLE_DEF->add_index("entity_index","1",array('entity_index'));
		$needs_migrate = true;
	}
	
	if(!isset($TABLE_DEF->fields["field_group_id"])) {
		$TABLE_DEF->add_field("field_group_id","bigint(20) unsigned NOT NULL");
		$TABLE_DEF->add_index("field_group_id","1",array('field_group_id'));
		$needs_migrate = true;
	}
	
	if($needs_migrate) {
		migrate_field_group_value_entity_codes();
	}
}

function migrate_field_group_value_entity_codes() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("field_group_values",false);
	
	$sql = "SELECT efg.group_instance_id, efg.entity_code, efg.entity_index, efg.group_id ".
		"FROM entity_to_field_group efg ".
		"ORDER BY efg.group_instance_id ";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE field_group_values SET field_group_id = %d, entity_code = '%s', entity_index = %d ".
					"WHERE group_instance_id = %d",
						$row["group_id"],
						$row["entity_code"],
						$row["entity_index"],
						$row["group_instance_id"]
				);
			$cerberus_db->query($sql);
		}
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

	if(!isset($TABLE_DEF->fields["ticket_mask"])) {
		$TABLE_DEF->add_field("ticket_mask","char(32) not null");
		$TABLE_DEF->add_index("ticket_mask","1",array('ticket_mask'));
		populate_ticket_masks();
	}
	
	if(!isset($TABLE_DEF->fields["ticket_spam_probability"])) {
		$TABLE_DEF->add_field("ticket_spam_probability","FLOAT UNSIGNED NOT NULL");
		$TABLE_DEF->add_index("ticket_spam_probability","1",array('ticket_spam_probability'));
	}	

	if(!isset($TABLE_DEF->fields["ticket_due"])) {
		$TABLE_DEF->add_field("ticket_due","datetime NOT NULL default '0000-00-00 00:00:00'");
		$TABLE_DEF->add_index("ticket_due","1",array('ticket_due'));
		populate_ticket_due();
	}
	
	$TABLE_DEF->add_index("ticket_date","1",array('ticket_date'));
	$TABLE_DEF->add_index("ticket_status","1",array('ticket_status'));
	
	if(isset($TABLE_DEF->indexes["ticket_id"])) {
		$sql = "ALTER TABLE `ticket` DROP INDEX `ticket_id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `ticket`.`ticket_id`.");
	}		
	
	if(!isset($TABLE_DEF->fields["ticket_time_worked"])) {
		$TABLE_DEF->add_field("ticket_time_worked","INT(11) DEFAULT '0' NOT NULL");
		populate_ticket_time_worked();
	}
	
}

function populate_ticket_time_worked() {
	global $cerberus_db;
	
	// [JAS]: rewrite to just loop through threads object and add time cumulatively
	$sql = "SELECT SUM(`thread_time_worked`) AS tworked, `thread`.ticket_id FROM `thread` GROUP BY `thread`.`ticket_id` HAVING tworked > 0";
	$t_result = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($t_result))
	{
		while($row = $cerberus_db->fetch_row($t_result)) {
			$sql = sprintf("UPDATE ticket SET ticket_time_worked = %d WHERE ticket_id = %d",
					$row["tworked"],
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
		}
	}
}

function populate_ticket_due() {
	global $cerberus_db;
	$cfg = CerConfiguration::getInstance();
	
	$due_hrs = $cfg->settings["overdue_hours"];
	
	$sql = "UPDATE ticket SET ticket_due = DATE_ADD(ticket.ticket_date, INTERVAL \"$due_hrs\" HOUR)";
	$cerberus_db->query($sql);
}


// ***************************************************************************
// `queue` table
// ***************************************************************************
function update_table_queue()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["queue_mode"])) {
		$TABLE_DEF->add_field("queue_mode","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["queue_response_gated"])) {
		$TABLE_DEF->add_field("queue_response_gated","TEXT NOT NULL");
	}

	if(isset($TABLE_DEF->fields["queue_restricted"])) {
		$TABLE_DEF->drop_field("queue_restricted");
	}
	
	if(isset($TABLE_DEF->fields["queue_response_restricted"])) {
		$TABLE_DEF->drop_field("queue_response_restricted");
	}
	
	if(!isset($TABLE_DEF->fields["queue_default_schedule"])) {
		$TABLE_DEF->add_field("queue_default_schedule","BIGINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["queue_default_response_time"])) {
		$TABLE_DEF->add_field("queue_default_response_time","INT(11) DEFAULT '0' NOT NULL");
	}
}
	

// ***************************************************************************
// `sla` table
// ***************************************************************************
function update_table_sla()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("sla",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["queues"])) {
		$TABLE_DEF->drop_field("queues");
	}
	
	if(isset($TABLE_DEF->indexes["id"])) {
		$sql = "ALTER TABLE `sla` DROP INDEX `id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `sla`.`id`.");
	}	
	
}
	


// ***************************************************************************
// `ticket_views` table
// ***************************************************************************
function update_table_ticket_views()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_views",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->indexes["view_id"])) {
		$sql = "ALTER TABLE `ticket_views` DROP INDEX `view_id`";
		$TABLE_DEF->run_sql($sql,"Removing duplicate index `ticket_views`.`view_id`.");
	}	
	
}



// ***************************************************************************
// `thread` table
// ***************************************************************************
function update_table_thread()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["is_agent_message"])) {
		$TABLE_DEF->add_field("is_agent_message","TINYINT DEFAULT '0' NOT NULL");
		populate_thread_agent_bit();
	}
	
	if(!isset($TABLE_DEF->fields["thread_received"])) {
		$TABLE_DEF->add_field("thread_received","DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL");
		
		// [JAS]: Populate the new thread_received field for existing threads.
		$sql = "UPDATE thread SET thread_received = thread_date;";
		$TABLE_DEF->run_sql($sql,"Populating new field thread.thread_received");
	}

	if(isset($TABLE_DEF->fields["thread_bytes"])) {
		$TABLE_DEF->drop_field("thread_bytes");
	}	

	$TABLE_DEF->add_index("is_agent_message","1",array('is_agent_message'));
}



// ***************************************************************************
// `trigrams` table
// ***************************************************************************
function update_table_trigrams()
{
	global $cerberus_db;
		
	$TABLE_DEF = new CER_DB_TABLE("trigram",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram` (".
							" `trigram_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,".
							" `trigram` CHAR( 30 ) BINARY NOT NULL ,".
							" PRIMARY KEY ( `trigram_id` ) ,".
							" UNIQUE ( `trigram` ) ".
							");";

	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["trigram"] = new CER_DB_FIELD("trigram","char(30) binary","","UNI","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("trigram_id"));		
	$TABLE_DEF->indexes["trigram"] = new CER_DB_INDEX("trigram","0",array("trigram"));		
	
	table($TABLE_DEF);	
	
	// get rid of other table
	$TABLE_DEF = new CER_DB_TABLE("trigrams",true);	
	if(!empty($TABLE_DEF->fields)) {
		$sql = "DROP TABLE IF EXISTS `trigrams`;";
		$TABLE_DEF->run_sql($sql,"Removing old trigrams table.");
	}
}



// ***************************************************************************
// `trigram_to_ticket` table
// ***************************************************************************
function init_table_trigram_to_ticket()
{
	global $cerberus_db;
	
	$sql = "ALTER TABLE `trigram_to_ticket` DROP INDEX `trigram_id`";
	$cerberus_db->query($sql);
	
	$sql = "ALTER TABLE `trigram_to_ticket` DROP INDEX `ticket_to_trigram`";
	$cerberus_db->query($sql);
	
	$sql = "ALTER TABLE `trigram_to_ticket` DROP INDEX `ticket_id`";
	$cerberus_db->query($sql);
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_ticket",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `trigram_to_ticket` (".
								" `trigram_id` BIGINT UNSIGNED DEFAULT '0' NOT NULL ,".
								" `ticket_id` BIGINT UNSIGNED DEFAULT '0' NOT NULL".
							");";

	$TABLE_DEF->fields["trigram_id"] = new CER_DB_FIELD("trigram_id","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","","0","");

	table($TABLE_DEF);	
}

function update_table_trigram_to_ticket() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_ticket",true);
//	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->indexes["ticket_to_trigram"]) || !isset($TABLE_DEF->indexes["ticket_id"])) {
		$sql = "DELETE FROM `trigram_to_ticket`";
		$TABLE_DEF->run_sql($sql,"Emptying table `trigram_to_ticket`.");
	}
	
	if(!isset($TABLE_DEF->indexes["ticket_to_trigram"])) {
		$sql = "ALTER TABLE `trigram_to_ticket` ADD UNIQUE `ticket_to_trigram` (`trigram_id`,`ticket_id`)";
		$TABLE_DEF->run_sql($sql,"Adding index `trigram_to_ticket`.`ticket_to_trigram`.");
	}
	
	if(!isset($TABLE_DEF->indexes["ticket_id"])) {
		$sql = "ALTER TABLE `trigram_to_ticket` ADD INDEX `ticket_id` (`ticket_id`)";
		$TABLE_DEF->run_sql($sql,"Adding index `trigram_to_ticket`.`ticket_id`.");
	}
}

function update_table_trigram_to_thread() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_thread",true);

	if(!empty($TABLE_DEF->fields)) {
		$sql = "DROP TABLE IF EXISTS `trigram_to_thread`;";
		$TABLE_DEF->run_sql($sql,"Removing old trigram_to_thread table.");
	}
}


// ***************************************************************************
// `trigram_to_kb` table
// ***************************************************************************
function update_table_trigram_to_kb()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("trigram_to_kb",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["good"])) {
		$TABLE_DEF->add_field("good","BIGINT DEFAULT '0' NOT NULL");
	}
	if(!isset($TABLE_DEF->fields["bad"])) {
		$TABLE_DEF->add_field("bad","BIGINT DEFAULT '0' NOT NULL");
	}
	if(isset($TABLE_DEF->fields["weight"])) {
		$TABLE_DEF->drop_field("weight");
	}	
}


// ***************************************************************************
// `user_prefs` table
// ***************************************************************************
function update_table_user_prefs()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user_prefs",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->indexes["primary"])) {
		$sql = "ALTER TABLE `user_prefs` CHANGE `user_id` `user_id` INT(11) DEFAULT '0' NOT NULL";
		$TABLE_DEF->run_sql($sql);
		$sql = "ALTER TABLE `user_prefs` ADD PRIMARY KEY (`user_id`)";
		$TABLE_DEF->run_sql($sql,"Adding primary key to table `user_prefs`.");
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

	if(isset($TABLE_DEF->fields["user_icq"])) {
		$TABLE_DEF->drop_field("user_icq");
	}
}

function migrate_content_parts() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_content_part",true);
	
	if(empty($TABLE_DEF->fields)) {
		init_table_thread_content_part();
	}

	$TABLE_DEF = new CER_DB_TABLE("thread_content",true);
	
	if(!empty($TABLE_DEF->fields)) {
		migrate_threads();
	}
}

function migrate_threads()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_content",true);
	$TABLE_DEF->check(true);
	
	$TABLE_DEF = new CER_DB_TABLE("thread_content_part",true);
	$TABLE_DEF->check(true);
	
	$thread_handler = new cer_ThreadContentHandler();
	
	$sql = "SELECT max( content_id ) as max_id, min(content_id) as min_id  FROM `thread_content` ORDER BY content_id ASC";
	$res = $cerberus_db->query($sql);
	
	$ids = $cerberus_db->grab_first_row($res);
	$id = $ids["min_id"];
	$max_id = $ids["max_id"];
	$rows = 0;
	
	while($id <= $max_id) {
		$sql = sprintf("SELECT tc.thread_id, tc.content_id, tc.content_content ".
			"FROM thread_content tc ".
			"WHERE tc.content_id >= %d AND tc.content_id <= %d",
				$id,
				$id + 10
			);
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res)) {

			$content_ids = array();
			
			while($row = $cerberus_db->fetch_row($res)) {
				$thread_id = $row["thread_id"];
				$thread_handler->writeThreadContent($thread_id,$row["content_content"]);
				$content_ids[] = $row["content_id"];
			}
			
			$sql = sprintf("DELETE FROM thread_content WHERE content_id IN (%s)",
					implode(',',$content_ids)
				);
			$cerberus_db->query($sql);
		
			$rows += 10;

			if($rows % 500 == 0 || $rows >= $max_id) {
				echo sprintf("Migrated thread_content.content_id to %d of %d<br>",
						(($id > $max_id) ? $max_id : $id),
						$max_id
					);
				flush();
			}
		}
		
		$id += 10;
	}


	$sql = "SELECT count(*) as num_content from thread_content";
	$res = $cerberus_db->query($sql);
	
	// [JAS]: If we were able to pull up a count of how many content items
	//	are left in the old database structure.
	if($row = $cerberus_db->grab_first_row($res)) {
		
		// [JAS]: If the table is empty, nuke it.
		if($row["num_content"] == 0) {
			$sql = "DROP TABLE IF EXISTS thread_content";
			$cerberus_db->query($sql);
		}
	}

}

function migrate_custom_fields()
{
	global $cerberus_db;
	$needs_migration = false;
	
	$TABLE_DEF = new CER_DB_TABLE("entity_to_field_group",true);
	if(empty($TABLE_DEF->fields)) {
		init_table_entity_to_field_group();	
		$needs_migration = true;
	}

	$TABLE_DEF = new CER_DB_TABLE("field_group",true);
	if(empty($TABLE_DEF->fields)) {
		init_table_field_group();	
		$needs_migration = true;
	}
	
	$TABLE_DEF = new CER_DB_TABLE("fields",true);
	if(empty($TABLE_DEF->fields)) {
		init_table_fields();	
		$needs_migration = true;
	}
	
	$TABLE_DEF = new CER_DB_TABLE("field_group_values",true);
	if(empty($TABLE_DEF->fields)) {
		init_table_field_group_values();	
		$needs_migration = true;
	}
	
	$TABLE_DEF = new CER_DB_TABLE("fields_options",true);
	if(empty($TABLE_DEF->fields)) {
		init_table_fields_options();	
		$needs_migration = true;
	}
	
	if($needs_migration) {
		clear_new_custom_fields();
		import_requester_custom_fields();
		import_ticket_custom_fields();
		drop_old_custom_fields();
	}
}

function clear_new_custom_fields()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("entity_to_field_group",false);
	
	$sql = "DELETE FROM entity_to_field_group";
	$TABLE_DEF->run_sql($sql,"Cleaning new custom field schema");
	
	$sql = "DELETE FROM field_group";
	$TABLE_DEF->run_sql($sql);
	
	$sql = "DELETE FROM `fields`";
	$TABLE_DEF->run_sql($sql);
	
	$sql = "DELETE FROM field_group_values";
	$TABLE_DEF->run_sql($sql);
	
	$sql = "DELETE FROM fields_options";
	$TABLE_DEF->run_sql($sql);
}

function drop_old_custom_fields()
{
	global $cerberus_db;
	
	foreach(array("address_fields","address_values","ticket_fields","ticket_values") as $table) {
	
		$TABLE_DEF = new CER_DB_TABLE($table,true);
		
		if(!empty($TABLE_DEF->fields)) {
			$sql = "DROP TABLE IF EXISTS `$table`";
			$output = "Dropping deprecated table $table";
			$TABLE_DEF->run_sql($sql,$output);
		}
	}
}

function import_requester_custom_fields()
{
	global $cerberus_db;
	
	$group_handler = new cer_CustomFieldGroupHandler();
	
	// [JAS]: Grab all deprecated requester fields	
	$sql = "SELECT f.field_id, f.field_name, f.field_type, f.field_options, f.field_not_searchable, v.address_id, v.value_text ".
		"FROM address_fields f ".
       	"LEFT JOIN address_values v ON (f.field_id = v.field_id) ".
		"ORDER BY v.address_id, f.field_id";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		$group_id = $group_handler->addGroup("Imported Requester Fields");
		
		$last_address_id = 0;
		$migrated_fields = array();
		
		// [JAS]: Loop through all existing fields per group
		while($row = $cerberus_db->fetch_row($res)) {
			$addy_id = $row["address_id"];
			
			// [JAS]: If this is for a new requester, add a new entity instance.
			if($last_address_id != $addy_id) {
				$gi_id = $group_handler->addGroupInstance(ENTITY_REQUESTER,$addy_id,$group_id);
			}
			
			// [JAS]: If we haven't migrated this field up yet, do it now.
			if(!isset($migrated_fields[$row["field_id"]])) {
				$options = parse_option_list($row["field_options"]);
				$field_id = $group_handler->addGroupField($row["field_name"],$row["field_type"],$group_id,0,$options,$row[" field_not_searchable"]);
				$migrated_fields[$row["field_id"]] = $field_id;
			}
			
			$value = $row["value_text"];
			
			// [JAS]: See if we're migrating one of the old 'D'ropdown fields.  If so, convert the text
			//	of the option into the option_id.
			if($row["field_type"] == 'D') {
				$sql = sprintf("SELECT o.option_id ".
					"FROM fields_options o ".
					"WHERE o.option_value = %s ".
					"AND o.field_id = %d",
						$cerberus_db->escape($value),
						$migrated_fields[$row["field_id"]]
					);
				$o_res = $cerberus_db->query($sql);
				
				if($o_row = $cerberus_db->grab_first_row($o_res)) {
					$value = $o_row["option_id"];
				}
			}
			
			// [JAS]: Add values to new field group instances.
			$new_field_id = $migrated_fields[$row["field_id"]];
			$group_handler->addFieldValue($new_field_id,$value,$gi_id);
			
			$last_address_id = $addy_id;
		}
	}
}

function import_ticket_custom_fields()
{
	global $cerberus_db;
	
	$group_handler = new cer_CustomFieldGroupHandler();
	
	// [JAS]: Grab all deprecated requester fields	
	$sql = "SELECT f.field_id, f.field_name, f.field_type, f.field_options, f.field_not_searchable, v.ticket_id, v.value_text ".
		"FROM ticket_fields f ".
       	"LEFT JOIN ticket_values v ON (f.field_id = v.field_id) ".
		"ORDER BY v.ticket_id, f.field_id";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		$group_id = $group_handler->addGroup("Imported Ticket Fields");
		
		$last_ticket_id = 0;
		$migrated_fields = array();
		
		// [JAS]: Loop through all existing fields per group
		while($row = $cerberus_db->fetch_row($res)) {
			$ticket_id = $row["ticket_id"];
			
			// [JAS]: If this is for a new requester, add a new entity instance.
			if($last_ticket_id != $ticket_id) {
				$gi_id = $group_handler->addGroupInstance(ENTITY_TICKET,$ticket_id,$group_id);
			}
			
			// [JAS]: If we haven't migrated this field up yet, do it now.
			if(!isset($migrated_fields[$row["field_id"]])) {
				$options = parse_option_list($row["field_options"]);
				$field_id = $group_handler->addGroupField($row["field_name"],$row["field_type"],$group_id,0,$options,$row[" field_not_searchable"]);
				$migrated_fields[$row["field_id"]] = $field_id;
			}
			
			$value = $row["value_text"];
			
			// [JAS]: See if we're migrating one of the old 'D'ropdown fields.  If so, convert the text
			//	of the option into the option_id.
			if($row["field_type"] == 'D') {
				$sql = sprintf("SELECT o.option_id ".
					"FROM fields_options o ".
					"WHERE o.option_value = %s ".
					"AND o.field_id = %d",
						$cerberus_db->escape($value),
						$migrated_fields[$row["field_id"]]
					);
				$o_res = $cerberus_db->query($sql);
				
				if($o_row = $cerberus_db->grab_first_row($o_res)) {
					$value = $o_row["option_id"];
				}
			}
			
			// [JAS]: Add values to new field group instances.
			$new_field_id = $migrated_fields[$row["field_id"]];
			$group_handler->addFieldValue($new_field_id,$value,$gi_id);
			
			$last_ticket_id = $ticket_id;
		}
	}
}

function parse_option_list($option_list)
{
	if(empty($option_list)) return array();
	
	$list = stripslashes($option_list);
	
	// [JAS]: Replace this with a regexp looking for outer quotes + any number of spaces?
	if(substr($list,0,1) == '"') $list = substr($list,1);
	if(substr($list,-1,1) == '"') $list = substr($list,0,strlen($list)-1);
	
   	$field_options_array = explode('","',$list);
   	
    foreach($field_options_array as $fldoption) {
    	$fldoption = stripslashes($fldoption);
    }
    
    return $field_options_array;
}

function populate_thread_agent_bit()
{
	global $cerberus_db;
	
	$agent_emails = array();
	
	// [JAS]: Grab agent e-mail addresses
	$sql = "SELECT u.user_email, a.address_id from user u, address a WHERE a.address_address = u.user_email";
	$res = $cerberus_db->query($sql);
	
	while($row = $cerberus_db->fetch_row($res)) {
		$agent_emails[$row["user_email"]] = $row["address_id"];
	}
	
	unset($res);
	
	foreach($agent_emails as $email => $addy_id) {
		$sql = "UPDATE thread SET is_agent_message = 1 WHERE thread_address_id = " . $addy_id;
		$cerberus_db->query($sql);
	}
}

function populate_ticket_masks()
{
	global $cerberus_db;
	
	$sql = "SELECT m.ticket_id, m.ticket_mask FROM  `ticket_id_masks` m";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE ticket SET ticket_mask = %s WHERE ticket_id = %d",
					$cerberus_db->escape($row["ticket_mask"]),
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
			flush();
		}
	}
	
	$sql = "DROP TABLE IF EXISTS ticket_id_masks";
	$cerberus_db->query($sql);
}

// [JAS]: Since some ACL permissions change between updates and users don't always check the
// 	group privs and update them, we're going to enable a few privileges by default -- so people
//	don't write in and ask where new functionality is -- or report sporadic appearances as a bug.
function set_default_acl() {
	global $cerberus_db;
		
	$sql = "SELECT acl.group_id, acl.group_acl, acl.group_acl2, acl.group_acl3 FROM user_access_levels acl";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$update = false;
			$acl2 = $row["group_acl2"];
			$gid = $row["group_id"];
			
			if(!cer_bitflag_is_set(ACL_CONTACTS,$acl2)) {
				$acl2 += ACL_CONTACTS;
				$update = true;
			}
			
			if(!cer_bitflag_is_set(ACL_CUSTOM_FIELDS_ENTRY,$acl2)) {
				$acl2 += ACL_CUSTOM_FIELDS_ENTRY;
				$update = true;
			}
			
			if($update) {
				$sql = sprintf("UPDATE user_access_levels SET group_acl2 = %d WHERE group_id = %d",
						$acl2,
						$gid
					);
				$cerberus_db->query($sql);
			}
		}
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


?>