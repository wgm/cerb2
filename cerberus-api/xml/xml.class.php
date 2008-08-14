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

require_once(FILESYSTEM_PATH . "gateway-api/version.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * XML communications version constant
 *
 */
define("XML_COMM_VERSION_STRING", GATEWAY_API_VERSION);

require_once(FILESYSTEM_PATH . "cerberus-api/xml/object/xml_object.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/output/xml_output.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/parser/xml_parser.class.php");