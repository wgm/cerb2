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
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/login/cer_LoginPlugin.class.php");

define("CER_PLUGIN_NAME","Cerberus Login Handler (default)");
define("CER_PLUGIN_TYPE","login");
define("CER_PLUGIN_AUTHOR","WebGroup Media LLC.");
define("CER_PLUGIN_CLASS","plugin_loginCerberus"); // [JAS]: The function to act as main()

class plugin_loginCerberus extends cer_LoginPlugin {
	
	function plugin_loginCerberus($plugin_id,&$params) {
		$this->cer_LoginPlugin($plugin_id,$params);
		$this->_loadVars();
	}

//	function pluginConfigure() {
//		return array();
//	}
	
	function getRemoteUserId() {
		$username = $this->getParam("username"); // e-mail addy
		$password = $this->getParam("password");
		
		$sql = sprintf("SELECT a.public_user_id, pu.password ".
			"FROM address a ".
			"LEFT JOIN public_gui_users pu ON (a.public_user_id = pu.public_user_id) ".
			"WHERE a.address_address = %s ".
			"AND pu.password = '%s' ",
				$this->db->escape($username),
				md5($password)
			);
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			return $row["public_user_id"];
		}
		else {
			return false; // no user
		}
	}

};
	
?>