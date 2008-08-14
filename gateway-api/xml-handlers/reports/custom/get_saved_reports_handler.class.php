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
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/custom_report_retriever.class.php");
//[todo]: remove this hardcoded "mysql"
require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/mysql/custom_report_mappings.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the weekly sales revenue based on the
 * close_date in opportunities
 *
 */
class get_saved_reports_handler extends xml_parser
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
   function get_saved_reports_handler(&$xml) {
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
		
		$report_retriever = new custom_report_retriever();		

		$report_list = $report_retriever->get_report_list();
		
		$xmlout =& xml_output::get_instance();
		$dataout =& $xmlout->get_child("data", 0);
		$categories =& $dataout->add_child("categories", xml_object::create("categories"));
		
		$category =& $categories->add_child("category", xml_object::create("category", NULL, array("id"=>"0")));
		$category->add_child("name", xml_object::create("name", "Unfiled Custom Reports"));
		
		for($i=0; $i < sizeof($report_list); $i++) {
			$report =& $category->add_child("report", xml_object::create("report", NULL, array("id"=>$report_list[$i]['report_id'])));
			$report->add_child("name", xml_object::create("name", $report_list[$i]['report_name']));
			$report->add_child("category", xml_object::create("category", $report_list[$i]['report_category']));
			$report->add_child("data", xml_object::create("data", $report_list[$i]['report_data']));
		}
		
		xml_output::success();
	}
}

