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
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/mail.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles new ticket creation
 *
 */
class create_handler extends xml_parser
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
    * @return create_handler
    */
	function create_handler(&$xml) {
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

		$mail = new email_mail();

		$from = $this->xml->get_child_data("from", 0);

		$recipients = array();
		$recipients["to"] = $recipients["cc"] = $recipients["bcc"] = array();

		$recipients = array();
		$recipients["to"] = $recipients["cc"] = $recipients["bcc"] = array();

		$recipients_xml =& $this->xml->get_child("recipients", 0);
		if(!is_object($recipients_xml)) {
			xml_output::error(0, "Recipients list not found");
		}
		$recipients_array = $recipients_xml->get_children();
		if(is_array($recipients_array)) {
			foreach($recipients_array as $recipient_instances) {
				foreach($recipient_instances as $recipient_xml) {
					if(is_object($recipient_xml) && array_key_exists($recipient_xml->get_token(), $recipients)) {
						$address = $recipient_xml->get_data_trim();
						if(strlen($address) > 1) {
							$recipients[$recipient_xml->get_token()][] = $address;
						}
					}
				}
			}
		}

		$count = 0;
		foreach($recipients as $value) $count += count($value);
		if($count < 1 || count($recipients["to"]) < 1) {
			xml_output::error(0, "You must specify atleast one recipient with a minimum of one address in To:");
		}

		$subject = $this->xml->get_child_data("subject", 0);
		$body = $this->xml->get_child_data("body", 0);
		$as_html = (stristr($this->xml->get_child_data("html_email", 0), "true") !== FALSE) ? TRUE : FALSE;

		$files = array();
		$files_xml =& $this->xml->get_child("files", 0);
		if(is_object($files_xml)) {
			$file_xml_array = $files_xml->get_child("file");
			if(is_array($file_xml_array)) {
				foreach($file_xml_array as $file_num=>$file_xml) {
					$files[$file_num]["name"] = $file_xml->get_child_data("name", 0);
					$files[$file_num]["size"] = $file_xml->get_child_data("size", 0);
					$chunks_xml =& $file_xml->get_child("chunks", 0);
					if(is_object($chunks_xml)) {
						$chunk_xml_array = $chunks_xml->get_child("chunk");
						if(is_array($chunk_xml_array)) {
							foreach($chunk_xml_array as $chunk_num=>$chunk_xml) {
								$files[$file_num]["chunks"][$chunk_xml->get_attribute("order", FALSE)]["name"] = $chunk_xml->get_child_data("name", 0);
								$files[$file_num]["chunks"][$chunk_xml->get_attribute("order", FALSE)]["size"] = $chunk_xml->get_child_data("size", 0);
							}
						}
					}
				}
			}
		}

		if($mail->new_outbound_email($from, $recipients, $subject, $body, $_SESSION['user_data']['user_id'], $as_html, $files) === FALSE) {
			xml_output::error(0, 'New email sending failed');
		}
		else {
			xml_output::success();
		}
	}
}