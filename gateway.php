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
 * List of your own domains to exclude in referrer reports
 * Comma Seperated List of domains ie "cerberusweb.com,majorcrm.com,webgroupmedia.com"
 *
 */
define("OWN_SITE_DOMAINS", "cerberusweb.com,majorcrm.com,webgroupmedia.com");

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
 * Load error handler
 *
 */
require_once(FILESYSTEM_PATH . "gateway-api/functions/error_handler.inc.php");

require_once(FILESYSTEM_PATH . "gateway-api/functions/external.inc.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/database_loader.class.php");

/**
 * Start gateway's session system
 *
 */
require_once(FILESYSTEM_PATH . "gateway-api/functions/sessions.class.php");

/**
 * Get XML packet from environment
 *
 */
$xml = get_var("xml", TRUE);

/**
 * Parse out XML parent blocks from packet
 *
 */
$xml_parser =& new xml_parser();
$xml_object =& $xml_parser->expat_parse($xml);
$cerb_xml =& $xml_object->get_child("cerberus_xml", 0);

if(is_object($cerb_xml)) {
   $channel = $cerb_xml->get_child_data("channel", 0);
   $module = $cerb_xml->get_child_data("module", 0);
   $command = $cerb_xml->get_child_data("command", 0);
   $data =& $cerb_xml->get_child("data", 0);
}
else {
   xml_output::error("0", "Invalid Cerberus XML Packet");
}

/**
 * Act on the command given
 *
 */
if(file_exists(FILESYSTEM_PATH . "gateway-api/xml-handlers/" . $channel . "/". $module . "/" . $command . "_handler.class.php")) {
   require_once(FILESYSTEM_PATH . "gateway-api/xml-handlers/" . $channel . "/". $module . "/" . $command . "_handler.class.php");
   $class = $command . "_handler";
   $command_obj =& new $class($data);
   $command_obj->process();
}
else {
   xml_output::error("1", "Command handler not found\n\nYou provided channel: " . $channel . "\nmodule: " . $module . "\ncommand: " . $command);
}

/**
 * Output XML result packet
 *
 */
xml_output::display();
