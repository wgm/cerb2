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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

class email_attachments
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;

	function email_attachments() {
		$this->db =& database_loader::get_instance();

		// Eliminate any output buffers now before we get into dealing with attachments
		while(@ob_end_clean());
	}

	function send_file($file_id) {
		header("Content-Type: application/force-download");
		header("Content-transfer-encoding: binary");
		header(sprintf('Content-Disposition: attachment; filename="%s"', $this->db->Get("attachments", "get_filename", array("file_id"=>$file_id))));

		$attachment_data = $this->db->Get("attachments", "get_parts", array("file_id"=>$file_id));

		$tmp_dir = FILESYSTEM_PATH . "tempdir";
		$temp_file_name = @tempnam($tmp_dir,"cerbfile_");
		$fp = @fopen($temp_file_name,"wb");
		if(!$fp) {
			die("Error opening temporary file " . $temp_file_name);
		}
		while(!$attachment_data->EOF)
		{
			$data = $attachment_data->fields("part_content");
			$chunk_len = strlen(str_replace(chr(0)," ",$data)); // [JAS]: Don't stop counting on a NULL
			if(fwrite($fp,$data,$chunk_len) === FALSE) {
				die("Error writing to temporary file " . $temp_file_name);
			}
			$attachment_data->MoveNext();
		}
		if(fflush($fp) === FALSE) {
			die("Error flushing data to temporary file " . $temp_file_name);
		}
		$fstat = @fstat($fp);
		header(sprintf("Content-Length: %d", $fstat["size"]));
		@fclose($fp);
		if(@readfile($temp_file_name) === FALSE) {
			die("Error reading temporary file " . $temp_file_name);
		}
		@unlink($temp_file_name);
		exit();
	}

	function receive_attachments_file($files) {


	}


}

