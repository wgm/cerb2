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
|		Ben Halsted    (ben@webgroupmedia.com)   [BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/preference/preference.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting view data
 *
 */
class get_preference_handler extends xml_parser
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
    * @return get_preference_handler
    */
	function get_preference_handler(&$xml) {
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

		$workspace_id = $this->xml->get_child_data("workspace_id", 0);
	      	if(empty($workspace_id) || $workspace_id < 1) {
			xml_output::error(0, 'Invalid or missing workspace ID');
		}     
		
		$user_id = $this->xml->get_child_data("user_id", 0);
		if(empty($user_id) || $user_id < 1) {
			xml_output::error(0, 'Invalid or missing user ID');
		}     

		$pref_id = $this->xml->get_child_data("pref_id", 0);
		if(empty($pref_id) || $pref_id < 1) {
			xml_output::error(0, 'Invalid or missing pref ID');
		}     

		$obj = new preference();
		if($obj->get_preference($user_id, $workspace_id, $pref_id) === FALSE) {
			xml_output::error(0, 'Failed to get preference');
		}
		else {
			xml_output::success();
		}
	}
}
