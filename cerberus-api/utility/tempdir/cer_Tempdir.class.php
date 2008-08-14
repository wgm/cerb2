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

define("TEMPDIR","tempdir/");

class cer_Tempdir {
	var $total_files = 0;
	var $total_sizes = 0;
	var $total_temp_attachments = 0;
	var $older_than_timestamp = 0;
	
	function cer_Tempdir($older_than_secs=86400) {
		$this->older_than_timestamp = (time() - $older_than_secs);
		
		$this->read_db_tempfiles();
		$this->read_tempdir();
	}
	
	function read_tempdir() {
		clearstatcache();
		
		if(file_exists(TEMPDIR) && is_writeable(TEMPDIR)) {
		  if ($handle = opendir(FILESYSTEM_PATH . TEMPDIR)) {
		  	while (false !== ($file = readdir($handle))) {
				if(substr($file,0,4) != "cerb")
					continue;

				$file_name = FILESYSTEM_PATH . TEMPDIR . $file;
				$fstat = stat($file_name);
				
				if($fstat["mtime"] < $this->older_than_timestamp) {
					$this->total_files++;
					$this->total_sizes += $fstat["size"];
				}
				else
					continue;
			}
		  	closedir($handle);
		  }
		}
	}
	
	function purge_tempdir() {
		$purged = 0;
		
		if(file_exists(TEMPDIR) && is_writeable(TEMPDIR)) {
		  if ($handle = opendir(FILESYSTEM_PATH . TEMPDIR)) {
		  	while (false !== ($file = readdir($handle))) {
				if(substr($file,0,4) != "cerb")
					continue;
					
				if($fstat["mtime"] > $this->older_than_timestamp)
					continue;
					
				$file_name = FILESYSTEM_PATH . TEMPDIR . $file;
				@unlink($file_name);
				
				$purged++;
		  	}
		  }
		}
		
		return $purged;
	}
	
	function read_db_tempfiles() {
		$cerberus_db = cer_Database::getInstance();
		
		$sql = "SELECT fa.file_id FROM thread_attachments_temp fa WHERE fa.timestamp < " . $this->older_than_timestamp;
		$res = $cerberus_db->query($sql);
		$this->total_temp_attachments = sprintf("%d",$cerberus_db->num_rows($res));
	}
	
	function purge_db_tempdirs() {
		$cerberus_db = cer_Database::getInstance();
		
		$sql = "DELETE FROM thread_attachments_temp WHERE `timestamp` < " . $this->older_than_timestamp;
		$cerberus_db->query($sql);
		
		$purged = $this->total_temp_attachments;
		$this->total_temp_attachments = 0;
		
		return $purged;
	}
	
};

?>