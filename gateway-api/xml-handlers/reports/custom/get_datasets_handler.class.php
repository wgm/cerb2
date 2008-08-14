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
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/dataset_retriever.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/mysql/custom_report_mappings.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting the weekly sales revenue based on the
 * close_date in opportunities
 *
 */
class get_datasets_handler extends xml_parser
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
   function get_datasets_handler(&$xml) {
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

		//$model = new reports_custom_model($this->xml);
      
		$dataset_retriever =& new dataset_retriever();

		if(0) {
			xml_output::error(0, $reports_obj->get_status_message());
		}
		else {
			xml_output::success();
		}
	}
}
