<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: gui_parser_log.php
|
| Purpose: E-mail parser / GUI log classes
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once("site.config.php");

class CER_GUI_LOG
{
var $db;
	
	function CER_GUI_LOG()
	{
		$this->db = cer_Database::getInstance();
	}
	
	function log($str="")
	{
		if(!$str) return false;
		
		$sql = "INSERT INTO log (message, log_date) " . 
			sprintf("VALUES (%s,NOW())",
				$this->db->escape($str));
		$this->db->query($sql);
		
		return true;
	}
};

?>