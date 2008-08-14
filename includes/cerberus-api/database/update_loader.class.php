<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: update_loader.class.php
|
| Purpose: Reads in any scripts in the includes/db_scripts/ directory
| 	And prompts the user which to run, then dynamically loads and
|	executes it.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";

define("PATH_DB_SCRIPTS","includes/db_scripts/");
define("NUM_SCRIPT_TAGS",6);

class CER_DB_UPDATE_LOADER_SCRIPT
{
	var $script_name=null;
	var $script_author=null;
	var $script_ident=null;
	var $script_date=null;
	var $script_precursor=null;
	var $script_one_run=null;
	var $script_file=null;
	var $script_type=null;
	var $precursor_ran=false;
};

class CER_DB_UPDATE_LOADER
{
	var $db = null;
	var $scripts = array();
	var $script_hash = null;
	var $active_scripts=0;
	var $ptrs_scripts_upgrade = array();
	var $ptrs_scripts_clean = array();
	var $ptrs_scripts_verify = array();
	
	function CER_DB_UPDATE_LOADER()
	{
		$this->db = cer_Database::getInstance();
		$this->script_hash = new CER_DB_SCRIPT_HASH();
		$this->read_db_scripts();
		$this->sort_scripts();
	}	

	function sort_scripts()
	{
		sort($this->scripts);
		
		foreach($this->scripts as $idx => $script) {
			switch($script->script_type) {
				default:
				case "upgrade":
					$dest = &$this->ptrs_scripts_upgrade;
					break;
				case "clean":
					$dest = &$this->ptrs_scripts_clean;
					break;
				case "verify":
					$dest = &$this->ptrs_scripts_verify;
					break;
			}
			
			$dest[] = &$this->scripts[$idx];
		}
	}
	
	function add_script($script_name=null,$script_author=null,$file=null,$script_date=null,$script_precursor=null,$script_one_run=null,$script_type=null)
	{
		if(empty($script_name) 
			|| empty($script_author) 
			|| empty($file)
			) 
			return false;
		
		$new_script = new CER_DB_UPDATE_LOADER_SCRIPT();
		$new_script->script_name = $script_name;
		$new_script->script_author = $script_author;
		$new_script->script_ident = md5($script_name);
		$new_script->script_date = $script_date;
		$new_script->script_precursor = $script_precursor;
		$new_script->script_one_run = $script_one_run;
		$new_script->script_type = $script_type;
		$new_script->script_file = $file;
		
		if(empty($new_script->script_precursor)
			|| $this->script_hash->script_has_run($new_script->script_precursor)
			)
			{
				$new_script->precursor_ran = true;
				$this->active_scripts++;
			}
		
		array_push($this->scripts,$new_script);
	}
	
	function scan_for_tag($tag,$line)
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
	
	function read_db_scripts()
	{
      $script_path = PATH_DB_SCRIPTS;
      
      if ($handle = opendir($script_path)) {
          while (false !== ($file = readdir($handle)))
          { 
          	  $found = 0;
		      $script_name = "";
		      $script_author = "";      
		      $script_date = "";      
		      $script_precursor = "";      
		      $script_one_run = "";
		      $script_type = "";
          	
          	  // [JAS]: only pull php scripts, exclude the ., the .. 
              //	and CVS dirs
              if($file != "." && $file != ".." && $file !="CVS") 
              {
	                // [JAS]: make sure the files we're seeing in this directory are PHP scripts 
                  	if(substr($file,-4) == ".php")
                	{
                  	// [JAS]: We found a database script, parse it.
                    if($script_handle = fopen ($script_path . $file, "r"))
                    {
                      $found=0;
                      
                      while(!feof($script_handle) && $found < NUM_SCRIPT_TAGS)
                      	{
							$line = fgets($script_handle,512);
							if(empty($script_name)) if($script_name = $this->scan_for_tag("DB_SCRIPT_NAME",$line)) $found++;
							if(empty($script_author)) if($script_author = $this->scan_for_tag("DB_SCRIPT_AUTHOR",$line)) $found++;
							if(empty($script_date)) if($script_date = $this->scan_for_tag("DB_SCRIPT_DATE",$line)) $found++;
							if(empty($script_precursor)) if(($script_precursor = $this->scan_for_tag("DB_SCRIPT_PRECURSOR",$line))!== false) $found++;
							if(empty($script_one_run)) if($script_one_run = $this->scan_for_tag("DB_SCRIPT_ONE_RUN",$line)) $found++;
							if(empty($script_type)) if($script_type = $this->scan_for_tag("DB_SCRIPT_TYPE",$line)) $found++;
                        }

                        if($found == NUM_SCRIPT_TAGS) {
                        	$hsh = md5($script_name);
                        	if(!($this->script_hash->script_has_run($hsh) 
                        		&& $script_one_run == "true")
                        		)
                        		$this->add_script($script_name,$script_author,$script_path . $file,$script_date,$script_precursor,$script_one_run,$script_type); // [JAS]: add to languages array
                        }
                    }
                    fclose($script_handle);
                  }
              }
          }
          closedir($handle); 
      }
	}
};

class CER_DB_SCRIPT_HASH
{
	var $db=null;
	var $run_scripts=null;
	
	function CER_DB_SCRIPT_HASH()
	{
		$this->db = cer_Database::getInstance();
		$this->_read_run_scripts();
	}
	
	function _read_run_scripts()
	{
		$sql = "SELECT script_md5,run_date FROM db_script_hash ORDER BY run_date ASC";
		$res = $this->db->query($sql);
		
		if(!$this->db->num_rows($res)) return;
		
		while($row = $this->db->fetch_row($res))
		{
			$script = new CER_DB_SCRIPT_HASH_ITEM();
			$script->script_md5 = $row["script_md5"];
			$script->run_date = $row["run_date"];
			$this->run_scripts[$script->script_md5] = $script;
		}
	}
	
	function script_has_run($script_hash)
	{
		if(isset($this->run_scripts[$script_hash])) return true;
		else return false;
	}
	
	function mark_script_run($script_hash)
	{
		$sql = sprintf("REPLACE INTO db_script_hash (script_md5,run_date) ".
			"VALUES (%s,NOW())",
				$this->db->escape($script_hash)
			);
		$this->db->query($sql);
	}
};

class CER_DB_SCRIPT_HASH_ITEM
{
	var $script_md5=null;
	var $run_date=null;
};

?>