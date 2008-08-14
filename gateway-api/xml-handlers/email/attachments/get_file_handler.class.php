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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/attachments.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class gets a file attachment
 *
 */
class get_file_handler extends xml_parser
{
	/**
    * XML data packet from client GUI
    *
    * @var object
    */
	var $xml;

	/**
    * Class constructor
    *
    * @param object $xml
    * @return get_file_handler
    */
	function get_file_handler(&$xml) {
		$this->xml =& $xml;
	}

	/**
    * main() function for this class. 
    *
    */
	function process() {
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Please login before proceeding!');
		}

		$attachments = new email_attachments();

		$attachment_id = $this->xml->get_child_data("attachment_id", 0);
		if(is_null($attachment_id) || $attachment_id < 1) {
			xml_output::error(0, 'Attachment ID is required');
		}

		if($attachments->send_file($attachment_id) === FALSE) {
			xml_output::error(0, 'Failed to get file attachment');
		}
		else {
			xml_output::success();
		}
	}
}