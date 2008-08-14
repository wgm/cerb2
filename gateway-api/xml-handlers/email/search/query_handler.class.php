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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/search.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting view data
 *
 */
class query_handler extends xml_parser
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
    * @return query_handler
    */
	function query_handler(&$xml) {
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

		$search_obj =& new email_search();

		$search_type = $this->xml->get_child_data("search_type", 0);
		if(empty($search_type)) {
			$search_type = 'keyword';
		}

		$fields_xml =& $this->xml->get_child("fields", 0);
		if(is_object($fields_xml)) {
			$fields_xml_children =& $fields_xml->get_children();
		}
		$fields = array();

		if(is_array($fields_xml_children)) {
			foreach($fields_xml_children as $field_xml_instance) {
				foreach($field_xml_instance as $field_xml) {
					$fields[$field_xml->get_token()] = $field_xml->get_data_trim();
				}
			}
		}

		$filters_xml =& $this->xml->get_child("filters", 0);
		if(is_object($filters_xml)) {
			$filters_xml_children =& $filters_xml->get_children();
		}
		$filters = array();

		if(is_array($filters_xml_children)) {
			foreach($filters_xml_children as $filters_xml_instance) {
				foreach($filters_xml_instance as $filter_xml) {
					$token = strtolower($filter_xml->get_token());
					if(!$filter_xml->has_children()) {
						$filters[$token] = $filter_xml->get_data_trim();
					}
					else {
						$filters[$filter_xml->get_token()] = array();
						$filter_xml_children = $filter_xml->get_children();
						if(is_array($filter_xml_children)) {
							foreach($filter_xml_children as $child_xml_instances) {
								foreach($child_xml_instances as $child_xml) {
									$child_token = $child_xml->get_token();
									if("date_range" == $child_token) {
										$from = $child_xml->get_attribute("from", FALSE);
										$to = $child_xml->get_attribute("to", FALSE);
										if(!is_numeric($from) && !is_numeric($to)) {
											xml_output::error(0, "Invalid date_range specified for " . $token);
										}
										$filters[$token]["from"] = $from;
										$filters[$token]["to"] = $to;
									}
									else {
										$child_id = $child_xml->get_attribute("id", FALSE);
										if(is_numeric($child_id)) {
											$filters[$token][] = $child_id;
										}
										else {
											$filters[$token][] = $child_xml->get_data_trim();
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if($search_obj->do_search($search_type, $fields, $filters) === FALSE) {
			xml_output::error(0, 'Search query failed');
		}
		else {
			xml_output::success();
		}
	}
}