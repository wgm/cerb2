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

class custom_report_retriever
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $report_data;

   function custom_report_retriever() {
		$this->db =& database_loader::get_instance();
		$this->report_data =& $this->db->Get("reports", "get_saved_reports", array());
   }

	function get_report_list() {
		return $this->report_data;
   }
}
