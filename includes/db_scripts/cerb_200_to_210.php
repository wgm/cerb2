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
| File: cerb_200_to_210.php
|
| Purpose: Upgrades the database structure from 2.0.0 to 2.1.0
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";
require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.0.0 to 2.1.0 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20030819");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","");
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	update_table_thread();
	update_table_configuration();
	insert_table_private_messages();
	insert_table_thread_attachments_temp();
	combine_address_table_dupes();
	make_address_unique_index();
	make_queue_address_ids();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function update_table_thread()
{
	global $cerberus_db;
	
	$TABLE_THREAD = new CER_DB_TABLE("thread");
	$TABLE_THREAD->check();

	$TABLE_THREAD->add_field("thread_subject","char (255) default ''");
	$TABLE_THREAD->add_field("thread_cc","char (255) default ''");
	$TABLE_THREAD->add_field("thread_replyto","char (255) default ''");
}

function update_table_configuration()
{
	global $cerberus_db;

	$TABLE_CONFIG = new CER_DB_TABLE("configuration");
	$TABLE_CONFIG->check();

	$TABLE_CONFIG->drop_field("only_unassigned_in_new");
	$TABLE_CONFIG->add_field("auto_add_cc_reqs","TINYINT DEFAULT '0' NOT NULL AFTER `cfg_id`");
	$TABLE_CONFIG->add_field("watcher_assigned_tech","TINYINT DEFAULT '0' NOT NULL");
	$TABLE_CONFIG->add_field("watcher_from_user","TINYINT DEFAULT '0' NOT NULL");
	$TABLE_CONFIG->add_field("parser_secure_enabled","TINYINT DEFAULT '0' NOT NULL AFTER `overdue_hours`");
	$TABLE_CONFIG->add_field("parser_secure_user","CHAR( 64 ) NOT NULL AFTER `parser_secure_enabled`");
	$TABLE_CONFIG->add_field("parser_secure_password","CHAR( 64 ) NOT NULL AFTER `parser_secure_user`");
	$TABLE_CONFIG->add_field("not_to_self","TINYINT DEFAULT '0' NOT NULL");
}

function insert_table_private_messages()
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
	  "pm_message text,".
	  "pm_marked_read tinyint(4) NOT NULL default '0',".
	  "pm_read_receipt tinyint(4) NOT NULL default '0',".
	  "pm_notified tinyint(4) NOT NULL default '0',".
	  "PRIMARY KEY (pm_id)".
	  ") TYPE=MyISAM;";
	  
	$TABLE_PM->run_sql($sql,"<b>Creating `private_messages` table</b>");
}

function insert_table_thread_attachments_temp()
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

function combine_address_table_dupes()
{
	global $cerberus_db;

	echo "<b>Combining any dupes in the `address` table ...</b> ";

	$sql = "SELECT count(address_id) as dupes, address_id, address_address FROM `address` GROUP BY address_address HAVING dupes > 1;";
	$result = $cerberus_db->query($sql);
	
	// [JAS]: We have dupes.
	if($cerberus_db->num_rows($result))
	{
		while($row = $cerberus_db->fetch_row($result))
		{
			$sql = "SELECT ad.address_id FROM address ad WHERE ad.address_address = '". $row["address_address"]."' ".
				"AND ad.address_id != " . $row["address_id"];
			$dupe_res = $cerberus_db->query($sql);
			
			$dupe_ids = array();
			
			if($cerberus_db->num_rows($dupe_res))
			{
				while($dupe_row = $cerberus_db->fetch_row($dupe_res))
				{ array_push($dupe_ids,$dupe_row["address_id"]); }
				
				$dupe_id_list = implode(",",$dupe_ids);
				
				$sql = "DELETE FROM address WHERE address_id IN ($dupe_id_list)";
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE thread SET thread_address_id = %d WHERE thread_address_id IN (%s)",
					$row["address_id"],$dupe_id_list);
				$cerberus_db->query($sql);

				$sql = sprintf("UPDATE requestor SET address_id = %d WHERE address_id IN (%s)",
					$row["address_id"],$dupe_id_list);
				$cerberus_db->query($sql);
			}
		}
			
		echo "<font color='green'>fixed!</font><br>";
	}
	else { echo "none found.<br>"; }
}

function make_address_unique_index()
{
	global $cerberus_db;
	
	$TABLE_ADDRESS = new CER_DB_TABLE("address");
	
	$TABLE_ADDRESS->check();
	$sql = "ALTER TABLE address DROP INDEX `address_address`, ADD UNIQUE(`address_address`);";
	$TABLE_ADDRESS->run_sql($sql,"<b>Making sure `address.address_address` is a UNIQUE field</b>");
}

function make_queue_address_ids()
{
	global $cerberus_db;
	
	$TABLE_ADDRESS = new CER_DB_TABLE("address");
	
	$sql = "INSERT IGNORE INTO address (address_address) SELECT CONCAT(qa.queue_address,'@',qa.queue_domain) FROM `queue_addresses` qa";
	$TABLE_ADDRESS->run_sql($sql,"<b>Making sure queue addresses have `address` table entries</b>");
}

?>