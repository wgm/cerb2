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

// [JAS]: \todo  This is a better example of a handler for a later 'factory' pattern

class cer_LayoutHandler
{
	var $db = null;
	var $users = array();
	
	function cer_LayoutHandler($ids=array()) {
		$this->db = cer_Database::getInstance();
		$this->_setDefaults($ids);
		$this->_loadUserLayouts($ids);
	}
	
	// [JAS]: Load a variable number of user layout prefs into the handler.
	function _loadUserLayouts($ids=array()) {
		$sql = sprintf("SELECT ul.layout_id, ul.user_id, ul.layout_data ".
					"FROM user_layout ul ".
					"WHERE 1 %s",
					(!empty($ids) && is_array($ids)) 
						? sprintf("AND ul.user_id IN (%s)", implode(",",$ids)) 
						: ""
				);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$user_layout = new cer_LayoutUser();
					$user_layout->layout_id = $row["layout_id"]; 
					$user_layout->user_id = $row["user_id"]; 
					$user_layout->importData(unserialize(stripslashes($row["layout_data"])));
				$this->users[$row["user_id"]] = $user_layout;
			}
		}
	}
	
	// [JAS]: Set the defaults for each user.  This will be built upon when
	// loading each user's actual layout prefs.
	function _setDefaults($ids=array()) {
		if(empty($ids))
			return;

		foreach($ids as $uid) {
			$this->users[$uid] = new cer_LayoutUser();
		}
	}
	
	// [JAS]: Save a users page layouts to the database.  Use insert or update.
	function saveUserLayoutPages($uid,$layout_pages) {
		$sql = sprintf("SELECT ul.layout_id FROM user_layout ul WHERE ul.user_id = %d",
				$uid
			);
		$c_res = $this->db->query($sql);

		// Update
		if($this->db->num_rows($c_res)) {
			$sql = sprintf("UPDATE user_layout SET layout_data = %s WHERE user_id = %d",
					$this->db->escape(serialize($layout_pages)),
					$uid
				);
			$this->db->query($sql);
		}
		else { // Insert
			$sql = sprintf("INSERT INTO user_layout (user_id, layout_data) ".
					"VALUES (%d, %s)",
						$uid,
						$this->db->escape(serialize($layout_pages))
				);
			$this->db->query($sql);
		}
		
		// [JAS]: Since we've probably changed something, do our post processing again.
		$this->users[$uid]->_importPostProcessing();
	}

};

class cer_LayoutUser
{
	var $user_id = null;
	var $layout_id = null;
	var $layout_pages = null;
	
	var $display_module_defaults = array("properties","history","log","suggestions","sla","fields","threads");
	
	// [JAS]: Set up pages and defaults
	function cer_LayoutUser() {
		// Pages
		$this->layout_pages["display"] = new cer_LayoutPage();
		
		// Default data
		$this->layout_pages["display"]->params = array(
				"display_modules" => $this->display_module_defaults,
				"display_modules_unused" => array()
			);
	}
	
	// [JAS]: Import on top of defaults.  We do this because serialization in the DB will
	//  decay and won't stay current with class changes in the API.
	function importData($prefs) {
		if(empty($prefs))
			return;
		
		if(!empty($prefs))
		foreach($prefs as $idx => $pg) {
			// [JAS]: See if we have this page defined already.  If so, import only
			//	stored data, keep existing defaults.
			if(isset($this->layout_pages[$idx])) {
				foreach($pg->params as $p_idx => $p_val) {
					$this->layout_pages[$idx]->params[$p_idx] = $p_val;
				}
			}
			else { // [JAS]: If not, just save time and bring it all in
				$this->layout_pages[$idx] = $pg;
			}
		}
		
		// [JAS]: After the import, do some post processing.
		$this->_importPostProcessing();
	}
	
	function _importPostProcessing() {
		// [JAS]: Find out which display modules this user doesn't have
		// enabled so we can give them a list to re-enable them later.
		if(is_array($this->display_module_defaults) && is_array($this->layout_pages["display"]->params["display_modules"])) {
			$this->layout_pages["display"]->params["display_modules_unused"] = array_diff($this->display_module_defaults,$this->layout_pages["display"]->params["display_modules"]);
		}
	}
};

class cer_LayoutPage
{
	var $params = array();
};

?>