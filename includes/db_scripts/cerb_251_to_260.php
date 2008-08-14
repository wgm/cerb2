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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/acl_groups.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.5.x to 2.6.1 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20050228");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","aeb47eb492c4faaabdf1b1f7980c4387"); // 2.5.0 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	update_table_names();
	fix_invisible_tickets();
	clear_orphaned_data();
	change_ticket_statuses();
	drop_content_exclude_words();
	update_table_entity_to_field_group();
	update_table_fields_custom();
	update_table_fields_options();
	update_table_public_gui_users();
	update_table_rule_entry();
	update_table_thread();
	update_table_thread_attachments();
	
	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function drop_content_exclude_words()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("search_index_exclude",false);
	
	$sql = 'DELETE FROM `search_index_exclude` WHERE `exclude_word` IN ("llc","inc","key","new","name","mail","email","please","company",'.
		'"questions","message","reply","domain","thanks","contact","information","addressl","internet","phone","number","support",'.
		'"thank","address")';
	$TABLE_DEF->run_sql($sql,"Clearing excessive text index excluded words");
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
		// [JSJ]: Changing to use array_values to make sure it doesn't have issues with the associative array
		$sql = "ALTER TABLE `ticket` CHANGE `ticket_status` `ticket_status` ENUM('" . implode("','", array_values($ticket_statuses)) . "') DEFAULT 'new' NOT NULL;";
		$TABLE_DEF->run_sql($sql,"Adding new ticket status options");
	}

	// [JAS]: Reload struct
//	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
//	
//	// [JAS]: Migrate deprecated statuses to new statuses
//	if(strtolower(trim($TABLE_DEF->fields["ticket_status"]->field_type)) == "enum('new','awaiting-reply','customer-reply','responded','in progress','info needed','acceptance','on hold','escalated','fixed','resolved','reopened','dead')") {
//		$sql = "UPDATE `ticket` SET `ticket_status` = 'customer-reply' ".
//			"WHERE `ticket_status` IN ('responded','in progress','info needed','acceptance','on hold','escalated','fixed','reopened') ".
//			"AND `last_reply_by_agent` = 0";
//		$TABLE_DEF->run_sql($sql,"Migrating customer replies to new statuses");
//		
//		$sql = "UPDATE `ticket` SET `ticket_status` = 'awaiting-reply' ".
//			"WHERE `ticket_status` IN ('responded','in progress','info needed','acceptance','on hold','escalated','fixed','reopened') ".
//			"AND `last_reply_by_agent` = 1";
//		$TABLE_DEF->run_sql($sql,"Migrating agent replies to new statuses");
//		
//		$sql = "ALTER TABLE `ticket` CHANGE `ticket_status` `ticket_status` ENUM( 'new', 'awaiting-reply', 'customer-reply', 'bounced', 'resolved', 'dead' ) DEFAULT 'new' NOT NULL;";
//		$TABLE_DEF->run_sql($sql,"Removing deprecated ticket statuses");
//	}
	
	// [JAS]: Catch any leftover tickets and set them to 'new'.	
	$sql = "SELECT count(*) as stray_tickets FROM `ticket` WHERE `ticket_status` = ''";
	$res = $cerberus_db->query($sql);
	if($row = $cerberus_db->grab_first_row($res)) {
		if($row["stray_tickets"] > 0) {
			$sql = "UPDATE `ticket` SET `ticket_status` = 'new' WHERE `ticket_status` = ''";
			$TABLE_DEF->run_sql($sql,"Cleaning up any stray ticket statuses");
		}
	}

//	$sql = "DELETE FROM rule_action WHERE `action_type` = 3 AND `action_value` IN ('responded','in progress','info needed','acceptance','on hold','escalated','fixed','reopened')";
//	$TABLE_DEF->run_sql($sql,"Clearing mail rule actions using deprecated ticket statuses");
}

function fix_invisible_tickets()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",false);
	
	$sql = "SELECT t.ticket_id, th.thread_id as miti, thr.thread_id as mxti ".
		"FROM ticket t ".
		"LEFT JOIN thread th ON th.thread_id = t.min_thread_id ".
		"LEFT JOIN thread thr ON thr.thread_id = t.max_thread_id ".
		"WHERE th.thread_id IS NULL OR thr.thread_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	// [JAS]: If we have any invisible tickets
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			// No threads
			if(empty($row["miti"]) && empty($row["mxti"])) {
				$sql = "UPDATE ticket SET min_thread_id=0, max_thread_id=0,ticket_status='dead' WHERE ticket_id = " . $row["ticket_id"];
				$TABLE_DEF->run_sql($sql,"Clearing invisible ticket #" . $row["ticket_id"]);
			}
			// only a min thread, copy to max
			elseif(empty($row["miti"]) && !empty($row["mxti"])) {
				$sql = sprintf("UPDATE ticket SET min_thread_id=%d WHERE ticket_id = %d",
						$row["mxti"],
						$row["ticket_id"]
					);
				$TABLE_DEF->run_sql($sql,"Recovering invisible ticket #" . $row["ticket_id"]);					
			}
			// only a max thread, copy to min
			elseif(!empty($row["miti"]) && empty($row["mxti"])) {
				$sql = sprintf("UPDATE ticket SET max_thread_id=%d WHERE ticket_id = %d",
						$row["miti"],
						$row["ticket_id"]
					);
				$TABLE_DEF->run_sql($sql,"Recovering invisible ticket #" . $row["ticket_id"]);					
			}
		}
	}
}

function clear_orphaned_data()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",false);
	
	echo "Checking for orphaned threads...<br>";
	flush();
	
	// [JAS]: Orphaned threads
	$sql = "SELECT th.ticket_id, th.thread_id FROM thread th LEFT JOIN ticket t ON th.ticket_id = t.ticket_id WHERE t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM thread WHERE thread_id = %d",
					$row["thread_id"]
				);
			$TABLE_DEF->run_sql($sql,"Clearing orphaned thread #" . $row["thread_id"]);
		}
	}
	
	echo "Checking for orphaned thread content...<br>";
	flush();
	
	// [JAS]: Orphaned thread content
	$sql = "SELECT DISTINCT tc.thread_id FROM `thread_content_part` tc LEFT JOIN thread th ON tc.thread_id = th.thread_id WHERE th.thread_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM thread_content_part WHERE thread_id = %d",
					$row["thread_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned search index links...<br>";
	flush();
	
	// [JAS]: Orphaned index content
	$sql = "SELECT si.ticket_id FROM search_index si LEFT JOIN ticket t ON (t.ticket_id = si.ticket_id) WHERE t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM search_index WHERE ticket_id = %d",
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned attachments...<br>";
	flush();
	
	// [JAS]: Orphaned attachments
	$sql = "SELECT a.thread_id, a.file_id FROM thread_attachments a LEFT JOIN thread th ON a.thread_id = th.thread_id WHERE th.thread_id IS NULL;";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM thread_attachments WHERE file_id = %d",
					$row["file_id"]
				);
			$cerberus_db->query($sql);
			$sql = sprintf("DELETE FROM thread_attachments_parts WHERE file_id = %d",
					$row["file_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned attachment content...<br>";
	flush();
	
	// [JAS]: Orphaned attachment parts
	$sql = "SELECT ap.part_id, ap.file_id FROM `thread_attachments_parts` ap LEFT JOIN thread_attachments a ON ap.file_id = a.file_id WHERE a.file_id IS NULL;";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM thread_attachments_parts WHERE file_id = %d",
					$row["file_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned ticket forwards (merge)...<br>";
	flush();
	
	// [JAS]: Merge forward table
	$sql = "SELECT mf.to_ticket FROM `merge_forward` mf LEFT JOIN ticket t ON mf.to_ticket = t.ticket_id WHERE t.ticket_id IS NULL;";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM merge_forward WHERE to_ticket = %d",
					$row["to_ticket"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned requesters...<br>";
	flush();
	
	// [JAS]: Requesters
	$sql = "SELECT r.ticket_id FROM `requestor` r LEFT JOIN ticket t ON r.ticket_id = t.ticket_id WHERE t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM requestor WHERE ticket_id = %d",
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned trigram data...<br>";
	flush();
	
	// [JAS]: Trigram Training
	$sql = "SELECT tt.ticket_id FROM `trigram_training` tt LEFT JOIN ticket t ON tt.ticket_id = t.ticket_id WHERE t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM trigram_training WHERE ticket_id = %d",
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned time slips...<br>";
	flush();
	
	// [JAS]: Time Tracking
	$sql = "select tt.ticket_id FROM thread_time_tracking tt LEFT JOIN ticket t ON tt.ticket_id = t.ticket_id WHERE t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM thread_time_tracking WHERE ticket_id = %d",
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned audit log data...<br>";
	flush();
	
	// [JAS]: Audit log
	$sql = "SELECT al.ticket_id FROM `ticket_audit_log` al LEFT JOIN ticket t ON al.ticket_id = t.ticket_id WHERE t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM ticket_audit_log WHERE ticket_id = %d",
					$row["ticket_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	echo "Checking for orphaned custom field content...<br>";
	flush();
	
	// [JAS]: Custom Fields
	$sql = "SELECT efg.group_instance_id, efg.entity_code, efg.entity_index FROM  `entity_to_field_group` efg LEFT  JOIN ticket t ON efg.entity_index = t.ticket_id WHERE efg.entity_code =  'T' AND t.ticket_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("DELETE FROM entity_to_field_group WHERE group_instance_id = %d",
					$row["group_instance_id"]
				);
			$cerberus_db->query($sql);
			$sql = sprintf("DELETE FROM field_group_values WHERE group_instance_id = %d",
					$row["group_instance_id"]
				);
			$cerberus_db->query($sql);
		}
	}
	
	
}

function update_table_names()
{
	global $cerberus_db;
	
	// [JAS]: Fix the MySQL reserve word problem w/ this table
	$TABLE_DEF = new CER_DB_TABLE("fields",true);
	if($TABLE_DEF->check(false,true)) {
		$sql = "ALTER TABLE `fields` RENAME `fields_custom`";
		$TABLE_DEF->run_sql($sql,"Renaming `fields` to `fields_custom`");
	}
}

function update_table_rule_entry()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("rule_entry",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["rule_pre_parse"])) {
		$TABLE_DEF->add_field("rule_pre_parse","TINYINT UNSIGNED DEFAULT '0' NOT NULL");
		$TABLE_DEF->add_index("rule_pre_parse",1,array("rule_pre_parse"));
	}
}

function update_table_entity_to_field_group()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("entity_to_field_group",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["order"])) {
		$sql = "ALTER TABLE `entity_to_field_group` CHANGE COLUMN `order` `group_order` TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL";
		$TABLE_DEF->run_sql($sql,"Renaming `entity_to_field_group`.`order` to `entity_to_field_group`.`group_order`");
	}
}

function update_table_fields_custom()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("fields_custom",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["order"])) {
		$sql = "ALTER TABLE `fields_custom` CHANGE COLUMN `order` `field_order` TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL";
		$TABLE_DEF->run_sql($sql,"Renaming `fields_custom`.`order` to `fields_custom`.`field_order`");
	}
}

function update_table_fields_options()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("fields_options",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["order"])) {
		$sql = "ALTER TABLE `fields_options` CHANGE COLUMN `order` `option_order` TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL";
		$TABLE_DEF->run_sql($sql,"Renaming `fields_options`.`order` to `fields_options`.`option_order`");
	}
}

function update_table_public_gui_users()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_users",true);
	$TABLE_DEF->check();
	
	if(!isset($TABLE_DEF->fields["public_access_level"])) {
		$TABLE_DEF->add_field("public_access_level","TINYINT UNSIGNED DEFAULT '0' NOT NULL");
		$TABLE_DEF->add_index("public_access_level",1,array("public_access_level"));
	}
}

function update_table_thread()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["thread_cc"])) {
		$sql = "ALTER TABLE `thread` CHANGE `thread_cc` `thread_cc` CHAR( 64 )";
		$TABLE_DEF->run_sql($sql,"Reducing size of `thread`.`thread_cc` to 64 chars");
	}
	flush();
	
	if(isset($TABLE_DEF->fields["thread_replyto"])) {
		$sql = "ALTER TABLE `thread` CHANGE `thread_replyto` `thread_replyto` CHAR( 64 )";
		$TABLE_DEF->run_sql($sql,"Reducing size of `thread`.`thread_replyto` to 64 chars");
	}
	flush();
	
	if(isset($TABLE_DEF->fields["thread_to"])) {
		$sql = "ALTER TABLE `thread` CHANGE `thread_to` `thread_to` CHAR( 64 )";
		$TABLE_DEF->run_sql($sql,"Reducing size of `thread`.`thread_to` to 64 chars");
	}
	flush();
	
	if(isset($TABLE_DEF->fields["thread_subject"])) {
		$sql = "ALTER TABLE `thread` CHANGE `thread_subject` `thread_subject` CHAR( 128 )";
		$TABLE_DEF->run_sql($sql,"Reducing size of `thread`.`thread_subject` to 128 chars");
	}
	flush();
	
}

function update_table_thread_attachments()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("thread_attachments",true);
	$TABLE_DEF->check();
	
	if(isset($TABLE_DEF->fields["file_name"])) {
		$sql = "ALTER TABLE `thread_attachments` CHANGE `file_name` `file_name` CHAR( 128 )";
		$TABLE_DEF->run_sql($sql,"Reducing size of `thread_attachments`.`file_name` to 128 chars");
	}
	flush();
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('de55ea2f635550d5a898b7226c0f4626',NOW())"; // 2.6.0 clean
	$cerberus_db->query($sql);
	
	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('5b05fa352ab1b87eceebe854822676ff',NOW())"; // 2.6.x clean
	$cerberus_db->query($sql);
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