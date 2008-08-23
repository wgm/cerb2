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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/acl_groups.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.4.0 to 2.5.0 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20040528");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","8b01eecd923a838206d5f653478852b4"); // 2.4.0 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_field_group_bindings();
	init_table_plugin();
	init_table_plugin_var();
	init_table_public_gui_users_to_plugin();
	init_table_queue_catchall();
	init_table_thread_time_tracking();
	init_table_user_layout();
	update_table_company();
	update_table_configuration();
	update_table_public_gui_profiles();
	update_table_public_gui_users();
	update_table_queue();
	update_table_search_excludes();
	update_table_ticket();
	update_table_thread();
	update_table_user_notification();
	update_table_user_prefs();
	set_default_acl();
	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
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

function init_table_user_layout()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("user_layout",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `user_layout` (".
		"`layout_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,".
		"`user_id` BIGINT UNSIGNED NOT NULL ,".
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

function update_table_thread_time_tracking()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("thread_time_tracking",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["thread_time_hrs_chargeable"])) {
		$TABLE_DEF->add_field("thread_time_hrs_chargeable","float NOT NULL default '0'");
	}
	if(!isset($TABLE_DEF->fields["thread_time_hrs_billable"])) {
		$TABLE_DEF->add_field("thread_time_hrs_billable","float NOT NULL default '0'");
	}
	if(!isset($TABLE_DEF->fields["thread_time_hrs_payable"])) {
		$TABLE_DEF->add_field("thread_time_hrs_payable","float NOT NULL default '0'");
	}
}

function update_table_configuration()
{
	global $cerberus_db;
	$zones = new cer_Timezone();
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["list_tickets_per_page"])) {
		$TABLE_DEF->drop_field("list_tickets_per_page");
	}
	if(!isset($TABLE_DEF->fields["server_gmt_offset_hrs"])) {
		$offset = $zones->getServerTimezoneOffset();
		$TABLE_DEF->add_field("server_gmt_offset_hrs","CHAR(5) NOT NULL DEFAULT '0'");
		
		$sql = "UPDATE configuration SET server_gmt_offset_hrs = '$offset'";
		$TABLE_DEF->run_sql($sql,"Setting server default timezone");
	}
}

function update_table_company()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("company",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["company_phone"])) {
		$sql = "ALTER TABLE `company` CHANGE `company_phone` `company_phone` CHAR(32) NOT NULL";
		$TABLE_DEF->run_sql($sql,"Increasing company.company_phone length to 32 characters");
	}
	
	if(isset($TABLE_DEF->fields["company_fax"])) {
		$sql = "ALTER TABLE `company` CHANGE `company_fax` `company_fax` CHAR(32) NOT NULL";
		$TABLE_DEF->run_sql($sql,"Increasing company.company_fax length to 32 characters");
	}
	
	if(!isset($TABLE_DEF->fields["sla_expire_date"])) {
		$TABLE_DEF->add_field("sla_expire_date","DATETIME NOT NULL AFTER `sla_id`");
		$TABLE_DEF->add_index("sla_expire_date",1,array("sla_expire_date"));
	}
}

function update_table_user_notification()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("user_notification",false);

	$sql = "SELECT u.user_id, un.user_id AS notify_id, un.notify_options ".
		"FROM user_notification un ".
		"LEFT JOIN user u USING ( user_id ) ".
		"WHERE u.user_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		$bad_user_ids = array();
		
		while($row = $cerberus_db->fetch_row($res)) {
			$bad_user_ids[] = $row["notify_id"];
		}
		
		if(!empty($bad_user_ids)) {
			$sql = sprintf("DELETE FROM `user_notification` WHERE `user_id` IN (%s)",
					implode(",",$bad_user_ids)
				);
			$TABLE_DEF->run_sql($sql,"Purging user notification records for previously deleted users");
		}
	}
}

function update_table_public_gui_profiles()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["login_plugin_id"])) {
		$TABLE_DEF->add_field("login_plugin_id","BIGINT UNSIGNED NOT NULL");
	}
}

function update_table_queue()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("queue",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["queue_addresses_inherit_qid"])) {
		$TABLE_DEF->add_field("queue_addresses_inherit_qid","BIGINT NOT NULL DEFAULT '0'");
	}
}

function update_table_public_gui_users()
{
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["phone_work"])) {
		$sql = "ALTER TABLE `public_gui_users` CHANGE `phone_work` `phone_work` CHAR(32) NOT NULL";
		$TABLE_DEF->run_sql($sql,"Increasing public_gui_users.phone_work length to 32 characters");
	}
	
	if(isset($TABLE_DEF->fields["phone_home"])) {
		$sql = "ALTER TABLE `public_gui_users` CHANGE `phone_home` `phone_home` CHAR(32) NOT NULL";
		$TABLE_DEF->run_sql($sql,"Increasing public_gui_users.phone_home length to 32 characters");
	}
	
	if(isset($TABLE_DEF->fields["phone_mobile"])) {
		$sql = "ALTER TABLE `public_gui_users` CHANGE `phone_mobile` `phone_mobile` CHAR(32) NOT NULL";
		$TABLE_DEF->run_sql($sql,"Increasing public_gui_users.phone_mobile length to 32 characters");
	}
	
	if(isset($TABLE_DEF->fields["phone_fax"])) {
		$sql = "ALTER TABLE `public_gui_users` CHANGE `phone_fax` `phone_fax` CHAR(32) NOT NULL";
		$TABLE_DEF->run_sql($sql,"Increasing public_gui_users.phone_fax length to 32 characters");
	}
}

function update_table_search_excludes()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index_exclude",true);
	$TABLE_DEF->check();

	$sql = "INSERT IGNORE INTO `search_index_exclude` (exclude_word) VALUES ".
       "('what'),('how'),('want'),('where'),('about');";

   	$TABLE_DEF->run_sql($sql,"<b>Adding common exclude words to `search_index_exclude`</b>");
}

function update_table_ticket()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["last_reply_by_agent"])) {
		$TABLE_DEF->add_field("last_reply_by_agent","TINYINT UNSIGNED DEFAULT '0' NOT NULL");
		echo "Please wait, this may take a few minutes ... <BR>";
		flush();
		retroactive_ticket_last_reply_bit();
	}
	
	if(isset($TABLE_DEF->fields["last_update_user_id"])) {
		$TABLE_DEF->drop_field("last_update_user_id");
	}
}


function update_table_thread()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["thread_to"])) {
		$TABLE_DEF->add_field("thread_to","char(255) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->indexes["thread_message_id"])) {
		$TABLE_DEF->add_index("thread_message_id",1,array("thread_message_id"));
	}	
	
}

// [JAS]: This function figures out the last 'email' thread on all existing tickets
//	and marks the new agent bit on tickets for the views column/filter code.
function retroactive_ticket_last_reply_bit() {
	global $cerberus_db;
	$new_rows = 1;
	$step = 0;

	$sql = "SELECT th.ticket_id, MAX( th.thread_id ) AS last_reply ".
		"FROM thread th ".
		"WHERE th.thread_type = 'email' ".
		"GROUP BY th.ticket_id ".
		"LIMIT %d, 250";

	$thr_sql = "SELECT th.ticket_id ".
		"FROM thread th ".
		"WHERE th.is_agent_message = 1 ".
		"AND th.thread_id IN (%s)";
					
	echo "Retroactively setting `ticket`.`last_reply_by_agent` bit ... ";
		
	while ($new_rows) {
		$res = $cerberus_db->query(sprintf($sql,$step));
		$new_rows = $cerberus_db->num_rows($res);
		
		if($new_rows) {
			$thread_vals = array();
			$row_vals = array();
			
			while($row = $cerberus_db->fetch_row($res)) {
				$thread_vals[] = $row["last_reply"];
			}
			
			if(!empty($thread_vals)) {
				$th_res = $cerberus_db->query(sprintf($thr_sql,implode(",",$thread_vals)));
				if($cerberus_db->num_rows($th_res)) {
					while($th_row = $cerberus_db->fetch_row($th_res)) {
						$row_vals[] = $th_row["ticket_id"];
					}
				}
			}
			
			if(!empty($row_vals)) {
				$upd_sql = sprintf("UPDATE ticket SET last_reply_by_agent = 1 WHERE ticket_id IN (%s)",
						implode(",",$row_vals)
					);
				$cerberus_db->query($upd_sql);
			}
		}
		$step += 250;
		flush();
	}
	
	echo "<font color='green'>success!</font><br>\r\n";
}

function update_table_user_prefs()
{
	global $cerberus_db;
	$zones = new cer_Timezone();
	
	$TABLE_DEF = new CER_DB_TABLE("user_prefs",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["page_layouts"])) {
		$TABLE_DEF->add_field("page_layouts","TEXT");
	}
	
	if(!isset($TABLE_DEF->fields["keyboard_shortcuts"])) {
		$TABLE_DEF->add_field("keyboard_shortcuts","TINYINT UNSIGNED DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["gmt_offset"])) {
		$TABLE_DEF->add_field("gmt_offset","CHAR(5) DEFAULT '0' NOT NULL");
		
		$offset = $zones->getServerTimezoneOffset();
		$sql = "UPDATE user_prefs SET gmt_offset = '$offset'";
		$TABLE_DEF->run_sql($sql,"Setting user default timezones according to server");
	}
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('aeb47eb492c4faaabdf1b1f7980c4387',NOW())"; // 2.5.0 clean
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
			$acl3 = $row["group_acl3"];
			$gid = $row["group_id"];
			
			if(!cer_bitflag_is_set(ACL_CREATE_TICKET,$acl3)) {
				$acl3 += ACL_CREATE_TICKET;
				$update = true;
			}
			
			// [JAS]: Enable New Time Tracking Bits, let the local admin disable as needed.
			if(!cer_bitflag_is_set(ACL_TIME_TRACK_CREATE,$acl3)) {
				$acl3 += ACL_TIME_TRACK_CREATE;
				$update = true;
			}
			if(!cer_bitflag_is_set(ACL_TIME_TRACK_VIEW_OWN,$acl3)) {
				$acl3 += ACL_TIME_TRACK_VIEW_OWN;
				$update = true;
			}
			if(!cer_bitflag_is_set(ACL_TIME_TRACK_VIEW_ALL,$acl3)) {
				$acl3 += ACL_TIME_TRACK_VIEW_ALL;
				$update = true;
			}
			if(!cer_bitflag_is_set(ACL_TIME_TRACK_EDIT_OWN,$acl3)) {
				$acl3 += ACL_TIME_TRACK_EDIT_OWN;
				$update = true;
			}
			if(!cer_bitflag_is_set(ACL_TIME_TRACK_DELETE_OWN,$acl3)) {
				$acl3 += ACL_TIME_TRACK_DELETE_OWN;
				$update = true;
			}
			
			if($update) {
				$sql = sprintf("UPDATE user_access_levels SET group_acl3 = %d WHERE group_id = %d",
						$acl3,
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