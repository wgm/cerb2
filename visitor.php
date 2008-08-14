<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Tells all included files they were included and not called directly from browser
 * DO NOT SET THIS TO 1 UNLESS YOU KNOW WHAT YOUR DOING!
 *
 */
define("VALID_INCLUDE", 0);

/**
 * Block normal session system
 *
 */
define("NO_SESSION",true);

/**
 * Load in configuration information
 *
 */
require_once("site.config.php");

/**
 * Setup the chat server URL if we didn't already
 *
 */
if(!defined('CHAT_SERVER_URL')) {
   define('CHAT_SERVER_URL', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);
}
if(!defined('CHAT_SERVER_BASE_URL')) {
   define('CHAT_SERVER_BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));
}

/**
 * Start gateway's session system
 *
 */
session_name("cerberus_visitor_gateway");
session_cache_limiter('private');
session_start();

/**
 * Load needed base files
 *
 */
require_once(FILESYSTEM_PATH . "gateway-api/functions/html_error_handler.inc.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/external.inc.php");
require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/database_loader.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");

/**
 * Get module/command from the environment
 * Hard set channel to chat as it is all we will handle through this API point
 *
 */

$channel = get_var("channel", FALSE, "chat");
$module = get_var("module", TRUE);
$command = get_var("command", TRUE);


/**
 * Act on the command given
 *
 */
if(file_exists(FILESYSTEM_PATH . "gateway-api/html-handlers/" . $channel . "/". $module . "/" . $command . "_handler.class.php")) {
   require_once(FILESYSTEM_PATH . "gateway-api/html-handlers/" . $channel . "/". $module . "/" . $command . "_handler.class.php");
   $class = $command . "_handler";
   $command_obj =& new $class();
   $command_obj->process();
}
else {
   html_output::error("1", "Command handler not found - " . $channel . "/" . $module . "/" . $command);
}

/**
 * Output XML result packet
 *
 */
html_output::display();
