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
| File: cerb_230_to_231.php
|
| Purpose: Upgrades the database structure from 2.3.0 to 2.3.1
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|		Ben Halsted			(ben@webgroupmedia.com)			[BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";
require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.3.0 to 2.3.1 Release Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20031112");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","aba12b36438a09371c9c6db4ccec75ec");
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	update_table_configuration();
	set_precursor_hashes();
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}


function set_precursor_hashes()
{
	global $cerberus_db;
																										
	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('bc4db140f76f67f505479bf7386d4def',NOW())"; // 2.3.1 clean
	$cerberus_db->query($sql);
}

// ***************************************************************************
// `configuration` table
// ***************************************************************************
function update_table_configuration()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["send_precedence_bulk"])) {
		$TABLE_DEF->add_field("send_precedence_bulk","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["auto_delete_spam"])) {
		$TABLE_DEF->add_field("auto_delete_spam","TINYINT DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["purge_wait_hrs"])) {
		$TABLE_DEF->add_field("purge_wait_hrs","INT DEFAULT '24' NOT NULL");
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