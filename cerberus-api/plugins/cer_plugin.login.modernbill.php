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

define("CER_PLUGIN_NAME","ModernBill v4 Login Handler");
define("CER_PLUGIN_TYPE","login");
define("CER_PLUGIN_AUTHOR","WebGroup Media LLC.");
define("CER_PLUGIN_CLASS","plugin_loginModernbill"); // [JAS]: The function to act as main()

class plugin_loginModernbill extends cer_LoginPlugin {
	var $remote_db = null;
	var $login_string = "Billing Login";
	var $password_string = "Billing Password";
	
	function plugin_loginModernbill($plugin_id,&$params) {
		$this->cer_LoginPlugin($plugin_id,$params);
		$this->_loadVars();
		
		$this->remote_db = new cer_Database();
		
		$db_server = $this->getVar("db_server");
		$db_name = $this->getVar("db_name");
		$db_user = $this->getVar("db_user");
		$db_pass = $this->getVar("db_pass");
		
		if(!@$this->remote_db->connect($db_server,$db_name,$db_user,$db_pass)) {
			$this->remote_db = null;
			return false;
		}
	}
	
	// [JAS]: This method should return an array of cer_PluginSetting objects that can be configured
	//	by the helpdesk user/admin in the GUI.
	function pluginConfigure() {
		$plugin_settings = array();
			$plugin_settings["db_server"] = new cer_PluginSetting("Database Server","db_server","T",array(64),"This should be the hostname of your Modernbill database server.");
			$plugin_settings["db_name"] = new cer_PluginSetting("Database Name","db_name","T",array(32),"The name of your Modernbill database.");
			$plugin_settings["db_user"] = new cer_PluginSetting("Database User","db_user","T",array(32),"Your Modernbill database login.");
			$plugin_settings["db_pass"] = new cer_PluginSetting("Database Password","db_pass","P",array(32),"Your Modernbill database password.");
		return $plugin_settings;
	}
	
	// [JAS]: Interface with Modernbill for the login details
	function getRemoteUserId() {
		
		if(empty($this->remote_db))
			return false;
		
		$username = $this->getParam("username");
		$password = $this->getParam("password");
			
		$sql = sprintf("SELECT c.client_id ".
					   "FROM client_info c ".
					   "WHERE c.client_email = %s AND c.client_real_pass = %s ",
					   		$this->remote_db->escape($username),
					   		$this->remote_db->escape($password)
				);
		$res = $this->remote_db->query($sql);
		
		if($this->remote_db->num_rows($res) && 
			$row = $this->remote_db->grab_first_row($res)) {
				return $row["client_id"];
		}
		else {
			return false;  // invalid login
		}
	}
	
};

?>