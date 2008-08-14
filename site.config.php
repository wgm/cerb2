<?php

/* 
   Only uncomment and set the following path if auto detection doesn't work
   Path to the cerberus-gui files, *MUST* include a trailing slash '/'.
   i.e.: define("FILESYSTEM_PATH","/www/htdocs/cerberus-gui/");
   NOTE: If you run a Windows server enter paths escaped, such as: 
		c:\\Inetpub\\wwwroot\\cerberus-gui\\ or c:/Inetpub/wwwroot/cerberus-gui/
*/
//define("FILESYSTEM_PATH","/www/htdocs/cerberus-gui/");

// If you want to override the automatic hostname detection, set the
// HOST_NAME constant to the full URL to cerberus-gui.  No trailing slash.
// Otherwise leave blank for auto detection.
// For Example:
// If your URL is: http://localhost/cerberus-gui/
// Use: define("HOST_NAME","http://localhost");
define("HOST_NAME","");

// This will hide the GUI's XSP settings in Config->Global Settings.
// Unless you know what you're doing, this should be left at default (false).
define("HIDE_XSP_SETTINGS",false);

// Demo mode won't save any configuration values, it should NOT be enabled
// on live/production sites.  Use this to display the helpdesk as a public
// demo.  Default is false.
define("DEMO_MODE",false);

// [ UPGRADE SECURITY OPTIONS ]==================================================================================
define("UPGRADE_SECURE_MODE",true); // Set this to 'true' to require IP matching on upgrade.php

/*=====================================================================
!!!  WARNING:  DO NOT EDIT ANYTHING BELOW THIS LINE.
=====================================================================*/


// [JSJ]: If we didn't set the filesystem path manually above, then auto-detect it
if(!defined('FILESYSTEM_PATH')) {
   define("FILESYSTEM_PATH", dirname(__FILE__) . "/");
}

define("DB_PLATFORM","mysql");

// [JAS]: Set global error handling
require_once(FILESYSTEM_PATH . "includes/functions/error_trapping.php");
set_error_handler('cer_error_handler');

require_once(FILESYSTEM_PATH . "config.php");
require_once(FILESYSTEM_PATH . "includes/functions/compatibility.php");

if(!defined('DB_SERVER') || !defined('DB_NAME') || !defined('DB_USER') || DB_NAME == '' || DB_SERVER == '' || DB_USER == '') {
   $configgen_path = "/siteconfig/index.php";
   if (substr($_SERVER["PHP_SELF"],strlen($configgen_path) * -1) != $configgen_path) { 
      if(strstr($_SERVER["PHP_SELF"], "install") === FALSE) {
         header("Location: install/siteconfig/index.php"); 
      }
      else {
         header("Location: siteconfig/index.php");
      }
   }
}

// [JSJ]: Setup the default system default priority names
$priority_options = Array("0"=>"Unassigned",
		"5"=>"None",
		"25"=>"Low",
		"50"=>"Medium",
		"75"=>"High",
		"90"=>"Critical",
		"100"=>"Emergency");

$install_path = "/install/index.php";
$is_install_page = (substr($_SERVER["PHP_SELF"],strlen($install_path) * -1) == $install_path);
if(!$is_install_page)
{ require(FILESYSTEM_PATH . "includes/functions/session_init.php"); }
