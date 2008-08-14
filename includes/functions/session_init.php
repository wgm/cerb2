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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file session_init.php
\brief Session logic

Determine whether we need a session scope up, or if we're logging in.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2003
*/

// Kill browser cache
header("Expires: Mon, 26 Nov 1962 00:00:00 GMT\n");
header("Last-Modified: " . gmdate("D, d M YH:i:s") . " GMT\n");
header("Cache-control: no-cache\n");
header("Pragma: no-cache\n");

// Class to grab configuration values from the database
require_once(FILESYSTEM_PATH . "cerberus-api/configuration/CerConfiguration.class.php");

define("GUI_VERSION","2.7.0 Release");
define("CURRENT_DB_SCRIPT","fbdb155c25f4ba500442f8cfaf6bc9bc");

$cfg = CerConfiguration::getInstance();
$status_options = array();

if(!defined("NO_OB_CALLBACK"))
	ob_start(@$cfg->settings["ob_callback"]);

// [JAS]: If the requesting page doesn't require a session, ignore session functionality,
//		but still set up a database connection.
if(defined("NO_SESSION")) {
	require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
	$cerberus_db = cer_Database::getInstance();
}
//else if(($_SERVER["PHP_SELF"] == $cfg->settings["cerberus_gui_path"] . "/do_login.php")) { }
//else if(($_SERVER["PHP_SELF"] == $cfg->settings["cerberus_gui_path"] . "/parser.php")) { }
else if(($_SERVER["PHP_SELF"] != $cfg->settings["cerberus_gui_path"] . "/login.php"))
{
	require_once(FILESYSTEM_PATH . "includes/functions/session.php");
	
	// [JAS]: Populate global status array with baseline + custom statuses
	require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_TicketStatuses.class.php");
	$ticket_status_handler = cer_TicketStatuses::getInstance();
	$status_options = $ticket_status_handler->getTicketStatuses();
}
else
{
	require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
}

// Language system selection -- DO NOT EDIT
if ($_SERVER["PHP_SELF"] != $install_path) {
if(isset($session)) {
	$use_lang = $session->vars["login_handler"]->user_prefs->user_language;
}
  
// Philipp Kolmann (kolmann@zid.tuwien.ac.at): Override Title if in $cfg->settings["helpdesk_title"]
if (@$cfg->settings["helpdesk_title"] != "" ) @define("LANG_HTML_TITLE", $cfg->settings["helpdesk_title"]);
  
if(!empty($prefs_user_language)) $use_lang = $prefs_user_language; // if on prefs page
if(empty($use_lang)) $use_lang = $cfg->settings["default_language"];
require_once(FILESYSTEM_PATH . "includes/languages/" . $use_lang . "/strings.php");
}
?>
