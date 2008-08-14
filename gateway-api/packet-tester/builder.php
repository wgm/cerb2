<?php
/***********************************************************************
| MajorCRM (tm) developed by WebGroup Media, LLC.
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

define("FILESYSTEM_PATH", dirname(__FILE__) . "/../../");
define("VALID_INCLUDE", 1);

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/external.inc.php");
require_once(FILESYSTEM_PATH . "gateway-api/packet-tester/builder_functions.php");

// Remove any PHP notices so they don't break output
error_reporting(error_reporting() & ~E_NOTICE);

$xml_input = get_var("cached_xml", FALSE, FALSE);
if($xml_input !== FALSE && !empty($xml_input)) {
   $xml_parser =& new xml_parser();
   $xml_object =& $xml_parser->expat_parse(base64_decode($xml_input));
   $xml =& $xml_object->get_child("cerberus_xml", 0);
}
else {
   $xml =& new xml_object("cerberus_xml");
   $xml->add_child("channel", xml_object::create("channel"));
   $xml->add_child("module", xml_object::create("module"));
   $xml->add_child("command", xml_object::create("command"));
   $xml->add_child("data", xml_object::create("data"));
}

$xml_data =& $xml->get_child("data", 0);

$form_action = get_var("form_action");
$displaynext = get_var("displaynext");

if($form_action == "submit") {
   require_once(FILESYSTEM_PATH . "gateway-api/packet-tester/builder_submit.php");
}

if($displaynext == "createxml") {
   require_once(FILESYSTEM_PATH . "gateway-api/packet-tester/builder_createxml.php");
}
else {
   require_once(FILESYSTEM_PATH . "gateway-api/packet-tester/builder_viewer.php");
}


