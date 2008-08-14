<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");

define("STATUS_ANY_STATUS","");
define("STATUS_ANY_ACTIVE",-1);

class cer_TicketPersistentSearch {
	var $params = array("search_queue" => null,
						"search_status" => STATUS_ANY_ACTIVE,
						"search_sender" => null,
						"search_subject" => null,
						"search_content" => null,
						"search_owner" => -1,
						"search_company" => null,
						"search_date" => null,
						"search_fdate" => null,
						"search_tdate" => null,
						"advsearch" => 0,
					);
	
	function cer_TicketPersistentSearch() {
	}
					
	function updateParams($params=array()) {
		foreach($params as $idx => $val) {
			$this->params[$idx] = $val;
//			echo "$idx = $val <br>";
		}
	}
};

?>