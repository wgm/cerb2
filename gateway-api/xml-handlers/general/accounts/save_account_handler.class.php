<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/accounts.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting view data
 *
 */
class save_account_handler extends xml_parser
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
    * @return save_account_handler
    */
	function save_account_handler(&$xml) {
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

		$account_obj =& new general_accounts();

		$account_id = $this->xml->get_child_data("account_id", 0);

		$changes_xml =& $this->xml->get_child("changes", 0);
		if(is_object($changes_xml)) {
			$changes_xml_children =& $changes_xml->get_children();
		}
		$changes = array();

		if(is_array($changes_xml_children)) {
			foreach($changes_xml_children as $change_xml_instance) {
				foreach($change_xml_instance as $change_xml) {
					$token = strtolower($change_xml->get_token());
					if(!$change_xml->has_children()) {
						$changes[$token] = $change_xml->get_data_trim();
					}
					else {
						$changes[$filter_xml->get_token()] = array();
						$change_xml_children = $change_xml->get_children();
						if(is_array($change_xml_children)) {
							foreach($change_xml_children as $child_xml_instances) {
								foreach($child_xml_instances as $child_xml) {
									$child_token = $child_xml->get_token();
									
									$child_id = $child_xml->get_attribute("id", FALSE);
									if(is_numeric($child_id)) {
										$changes[$token][] = $child_id;
									}
									else {
										$changes[$token][] = $child_xml->get_data_trim();
									}
								}
							}
						}
					}
				}
			}
		}

		if($account_obj->save_account($account_id, $changes) === FALSE) {
			xml_output::error(0, 'Save account query failed');
		}
		else {
			xml_output::success();
		}
	}
}