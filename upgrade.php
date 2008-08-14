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
| File: upgrade.php
|
| Purpose: Reads in any scripts in the includes/db_scripts/ directory
| 	And prompts the user which to run, then dynamically loads and
|	executes it.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

// ======================================================================================================
// [JAS]: DO NOT EDIT BELOW THIS LINE
// ======================================================================================================

define("NO_SESSION",true); // [JAS]: Leave this true
define("NO_OB_CALLBACK",true); // [JAS]: Leave this true

require_once("site.config.php");

$cerberus_db = cer_Database::getInstance();

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/update_loader.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
			   
// [JAS]: Authorize IPs or close page
$pass = false;
foreach ($authorized_ips as $ip)
{
 	// [JAS]: Rewrite this to explode on the above into arrays and check as many elements as exist (2...4, etc)
	if(substr($ip,0,strlen($ip)) == substr($_SERVER['REMOTE_ADDR'],0,strlen($ip)))
 	{ $pass=true; break; }
}
if(UPGRADE_SECURE_MODE && !$pass) { echo "Cerberus [ERROR]: You are not authorized to use this tool. (Logged IP: " . $_SERVER['REMOTE_ADDR'] .")  If you're an authorized user, ask your admin to edit the config.php script and add your IP."; exit(); }

$form_submit = isset($_REQUEST["form_submit"]) ? $_REQUEST["form_submit"] : "";
$upgrade_script_name = isset($_REQUEST["upgrade_script_name"]) ? $_REQUEST["upgrade_script_name"] : "";

// [JAS]: Read in available DB scripts
$CER_UPDATER = new CER_DB_UPDATE_LOADER();

if(!empty($form_submit))
{
	foreach($CER_UPDATER->scripts as $script)
	{
		if($upgrade_script_name == $script->script_ident)
		{
			echo "<html><head><style>";
			require_once "cerberus.css";
			echo"</style></head><body class='cer_maintable_text'>";
			echo "<span class='cer_display_header'>Running ".$script->script_name."</span><br><br>";

			require_once $script->script_file;
			
			// [JAS]: Start our script and record if it ran properly.
			if(cer_init())
				$CER_UPDATER->script_hash->mark_script_run($upgrade_script_name);
			
			echo '<br><a href="upgrade.php" class="cer_maintable_text">Return to Database Upgrade/Sync Tool Index</a><br>';
			echo "</body></html>";
			break;
		}
	}
}
else
{
	// [JAS]: Load the template object
	$cer_tpl = new CER_TEMPLATE_HANDLER();
	$cer_tpl->assign_by_ref('cer_updater',$CER_UPDATER);
	$cer_tpl->display("updater.tpl.php");
}

?>
