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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/dataset.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_matrix.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_model.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_list.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_matrix_model.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_list_model.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_grouped_model.class.php");
//[todo]: remove this hardcoded "mysql"
require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/mysql/custom_report_mappings.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the weekly sales revenue based on the
 * close_date in opportunities
 *
 */
class get_custom_report_handler extends xml_parser
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
    * @return ticket_age_report_handler
    */
   function get_custom_report_handler(&$xml) {
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
				
		$groupings_data =& $this->xml->get_child("groupings", 0);
		$matrix_data =& $this->xml->get_child("grid_groupings", 0);

		
      	if(!empty($matrix_data)) {
			$model = new reports_custom_matrix_model($this->xml);
			$reports_obj =& new reports_custom_matrix($model);      		
      	}
      	elseif(!empty($groupings_data)) {
      		//$reports_obj =& new reports_custom_grouping($model);
			$model = new reports_custom_grouped_model($this->xml);
			$reports_obj =& new reports_custom($model);
      	}
      	else {
			$model = new reports_custom_list_model($this->xml);
			$reports_obj =& new reports_custom_list($model);      		
      	}
		
		if($reports_obj->get_status_message() !== "OK") {
			xml_output::error(0, $reports_obj->get_status_message());
		}
		else {
			xml_output::success();
		}
	}
}
