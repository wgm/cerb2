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

define("PATH_LOGIN_PLUGINS",FILESYSTEM_PATH . "cerberus-api/plugins/");
define("NUM_PLUGIN_TAGS",4);

class cer_LoginPluginHandler {
	var $db = null;
	var $hash = null;
	var $plugins = array();

	function cer_LoginPluginHandler() {
		$this->db = cer_Database::getInstance();
		$this->hash = new cer_LoginPluginHandlerDBHash();
		$this->_readPlugins();
	}
	
	function _scan_for_tag($tag,$line)
	{
	    if(strstr($line,"define(\"$tag\"") !== false)
	    {
	      $line = str_replace("define(\"$tag\",\"","",$line);
	      // [JAS]: Find the semicolon line terminator, to exclude anything
	      //	after the line, such as comments.
	      $line_terminator = strpos($line,";");
	      if(!$line_terminator) $line_terminator = strlen($line)-1;
	      $script_tag = substr($line,0,$line_terminator-2);
	      return $script_tag;
	    }
	    else return false;
	}
	
	function _readPlugins() {
      $plugin_path = PATH_LOGIN_PLUGINS;
      
      if ($handle = opendir($plugin_path)) {
          while (false !== ($file = readdir($handle)))
          { 
          	  $found = 0;
		      $plugin_name = "";
		      $plugin_type = "";
		      $plugin_author = "";      
		      $plugin_class = "";
          	
          	  // [JAS]: only pull php scripts, exclude the ., the .. 
              //	and CVS dirs
              if($file != "." && $file != ".." && $file !="CVS") 
              {
	                // [JAS]: make sure the files we're seeing in this directory are Cerb plugins 
                  	if(substr($file,0,17) == "cer_plugin.login.")
                	{
	                  	// [JAS]: We found a plugin, parse it.
	                    if($plugin_handle = fopen ($plugin_path . $file, "r"))
	                    {
	                      $found = 0;
	                      
							while(!feof($plugin_handle) && $found < NUM_PLUGIN_TAGS)
							{
								$line = fgets($plugin_handle,512);
								if(empty($plugin_name)) if($plugin_name = $this->_scan_for_tag("CER_PLUGIN_NAME",$line)) $found++;
								if(empty($plugin_type)) if($plugin_type = $this->_scan_for_tag("CER_PLUGIN_TYPE",$line)) $found++;
								if(empty($plugin_author)) if($plugin_author = $this->_scan_for_tag("CER_PLUGIN_AUTHOR",$line)) $found++;
								if(empty($plugin_class)) if($plugin_class = $this->_scan_for_tag("CER_PLUGIN_CLASS",$line)) $found++;
							}
	
	                        if($found == NUM_PLUGIN_TAGS) {
	                        	$blank_params = array();
	                        	
	                        	if(!$plugin_id = $this->hash->getPluginIdByFile($file)) {
	                        		$plugin_id = $this->hash->addPlugin($plugin_name,$plugin_type,$plugin_class,$file);
	                        	}
	                        	
	                   			$new_plugin = new cer_LoginPlugin($plugin_id,$blank_params);
		                   			$new_plugin->plugin_id = $plugin_id;
		                   			$new_plugin->plugin_name = $plugin_name;
		                   			$new_plugin->plugin_type = $plugin_type;
		                   			$new_plugin->plugin_author = $plugin_author;
		                   			$new_plugin->plugin_class = $plugin_class;
		                   			$new_plugin->plugin_file = $file; 
		                   			
		                   		$this->hash->updatePlugin($plugin_id,$new_plugin);
	                        }
	                    }
                    fclose($plugin_handle);
                  }
              }
          }
          closedir($handle); 
      }
	} // _readPlugins()
	
	
	function instantiatePlugin($plugin_id, &$params) {
		if($plugin = $this->hash->getPluginById($plugin_id))
			return new $plugin->plugin_class($plugin_id,$params);
		else
			return false;
	}
	
	function getPluginFile($plugin_id) {
		if($plugin = $this->hash->getPluginById($plugin_id))
			return $plugin->plugin_file;
	}
	
	function getPluginById($plugin_id) {
		return $this->hash->getPluginById($plugin_id);
	}
	
};

class cer_LoginPluginHandlerDBHash {
	var $db = null;
	var $plugin_hash = array();
	var $plugins_by_file = array();
	
	function cer_LoginPluginHandlerDBHash() {
		$this->db = cer_Database::getInstance();
		$this->_loadPluginDBHash();
	}
	
	// [JAS]: Loads up plugin details from the DB hash
	function _loadPluginDBHash() {
		$sql = "SELECT p.plugin_id, p.plugin_name, p.plugin_type, p.plugin_enabled, p.plugin_class, p.plugin_file FROM plugin p";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) { 
			while($row = $this->db->fetch_row($res)) {
				$blank_array = array();
				$this->plugin_hash[$row["plugin_id"]] = new cer_LoginPlugin($row["plugin_id"],$blank_array);
				$plugin = &$this->plugin_hash[$row["plugin_id"]];
					$plugin->plugin_id = $row["plugin_id"];
					$plugin->plugin_name = stripslashes($row["plugin_name"]);
					$plugin->plugin_type = stripslashes($row["plugin_type"]);
					$plugin->plugin_enabled = $row["plugin_enabled"];
					$plugin->plugin_class = stripslashes($row["plugin_class"]);
					$plugin->plugin_file = stripslashes($row["plugin_file"]);
				$this->plugins_by_file[$plugin->plugin_file] = &$this->plugin_hash[$plugin->plugin_id];
			}
		}
	}
	
	// [JAS]: Returns a pointer array of plugins that match the given type
	function getPluginsByType($type) {
		$ptrs = array();
		
		foreach($this->plugin_hash as $idx => $plugin) {
			if($plugin->plugin_type == $type)
				$ptrs[$plugin->plugin_id] = &$this->plugin_hash[$idx];
		}
		
		return $ptrs;
	}
	
	// [JAS]: Returns a plugin object from its ID
	function getPluginById($id) {
		if(isset($this->plugin_hash[$id]))
			return $this->plugin_hash[$id];
		else
			return false;
	}
	
	// [JAS]: Takes a filename and returns the plugin ID from DB hash
	function getPluginIdByFile($file) {
		if(isset($this->plugins_by_file[$file])) {
			$plugin_id = $this->plugins_by_file[$file]->plugin_id;
			return $plugin_id;
		}
		else {
			return false;
		}
	}
	
	// [JAS]: Adds a plugin to the DB hash
	function addPlugin($name,$type,$class,$file) {
		$sql = sprintf("INSERT INTO plugin (plugin_name,plugin_type,plugin_class,plugin_file) ".
				"VALUES (%s,%s,%s,%s)",
					$this->db->escape($name),
					$this->db->escape($type),
					$this->db->escape($class),
					$this->db->escape($file)
			);
		$this->db->query($sql);
		
		$plugin_id = $this->db->insert_id();
		$this->_loadPluginDBHash();
				
		return $plugin_id;
	}
	
	// [JAS]: Updates the plugin DB hash using details from the plugin file
	function updatePlugin($id,$plugin) {
		if(isset($this->plugin_hash[$id])) {
			$sql = sprintf("UPDATE plugin SET plugin_name = %s, plugin_type = %s, plugin_class = %s, plugin_file = %s ".
					"WHERE plugin_id = %d",
						$this->db->escape($plugin->plugin_name),
						$this->db->escape($plugin->plugin_type),
						$this->db->escape($plugin->plugin_class),
						$this->db->escape($plugin->plugin_file),
						$id
				);
			$this->db->query($sql);
		}
	}
};

?>