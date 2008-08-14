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

class cer_LoginPlugin
{
	// [JAS]: Meta data for cer_LoginPluginHandler
	var $db = null;
	var $plugin_id = null;
	var $plugin_name = null;
	var $plugin_type = null;
	var $plugin_author = null;
	var $plugin_class = null;
	var $plugin_file = null;
	
	var $login_string = "Login"; // [JAS]: The login string shown on the Support Center login form
	var $password_string = "Password";  // [JAS]: The pass string shown on the Support Center login form
	
	var $vars = array();
	var $params = array();

	function cer_LoginPlugin($plugin_id,&$params) {
		$this->db = cer_Database::getInstance();
		$this->plugin_id = $plugin_id;
		$this->params = &$params;
	}

	// [JAS]: Loads the plugin settings stored by the server
	function _loadVars() {
		$sql = "SELECT v.plugin_id, v.var_name, v.var_value FROM `plugin_var` v WHERE v.plugin_id = " . $this->plugin_id;
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) { 
			while($row = $this->db->fetch_row($res)) {
				$this->vars[stripslashes($row["var_name"])] = stripslashes($row["var_value"]);
			}
		}
	}
	
	// [JAS]: Return a plugin parameter or the preferred default
	function getParam($name,$default=null) {
		if(isset($this->params[$name]))
			return $this->params[$name];
		else
			return $default;
	}
	
	function setParam($name,$value) {
		$this->params[$name] = $value;
	}

	// [JAS]: Return a plugin variable or the preferred default
	function getVar($name,$default=null) {
		if(isset($this->vars[$name]))
			return $this->vars[$name];
		else
			return $default;
	}
	
	// [JAS]: Expects override
	function getRemoteUserId() {
		return false;
	}
	
	// [JAS]: Expects override
	function testPlugin() {
		return true;
	}
	
	// [JAS]: Expects override
	function pluginConfigure() {
		return array();
	}
};

// [JAS]: Stores Plugin Settings that are setup in [Configuration]
class cer_PluginSetting {
	var $name = null; // Name of the setting to be shown ("Database Server")
	var $var = null; // Variable name to use inside the class w/o $ ("db_server")
	var $type = null; /* Type of Setting: 
						'P' - Single Line PASSWORD Box (uses *'s)
						'T' - Single Line Text INPUT Box
					  */
	var $type_opts = array(); // Setting options (vary by $type)
	var $desc = null; // Description of the setting ("This is where you enter your Database server name")
	
	function cer_PluginSetting($name,$var,$type,$type_opts,$desc=null) {
		$this->name = $name;
		$this->var = $var;
		$this->type = $type;
		$this->type_opts = $type_opts;
		$this->desc = $desc;
	}
};

?>