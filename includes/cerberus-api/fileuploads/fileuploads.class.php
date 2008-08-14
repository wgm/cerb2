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
| File: fileuploads.class.php
|
| Purpose: Class to handle file uploads.
|
| Contributors:
|       Jeremy Johnstone (jeremy@scriptd.net)   [JSJ]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class CER_FILE_UPLOADER 
{
	var $db;
	
	function CER_FILE_UPLOADER() 
	{
		$this->db = cer_Database::getInstance();
	}
	
	/**
	* @return string
	* @param $uploaded_file array
	* @param $ticket_id int
	* @param $user_id int
	* @desc Move an uploaded file to the script temp dir and add it to the db. Returns the file_id.	
 */
	function add_file($uploaded_file, $ticket_id, $user_id)
	{
		$cfg = CerConfiguration::getInstance();
		//@$tmp_dir_path = $cfg->settings["tmp_dir_path"];
		$tmp_dir_path = FILESYSTEM_PATH . "tempdir";
		
		// [JSJ]: Create the temporary filename to use for the file while in holding
		$tmp_name = tempnam($tmp_dir_path, "cerb");
		
		if(!move_uploaded_file($uploaded_file["tmp_name"], $tmp_name))
		{
			// [JAS]: Error Handling. 
			//	If file wasn't uploaded by browser, check to see if it exists anyway.
			//	May have been written by Cerberus itself to simulate a browser upload.
			if(file_exists($uploaded_file["tmp_name"]))
			{
				if(copy($uploaded_file["tmp_name"],$tmp_name)) {
					// [JAS]: Delete our source file for whatever functionality put it there, since 
					//		we're assuming it wasn't PHP's $_FILES[] in /tmp/.
					if(!@unlink($uploaded_file["tmp_name"]))
						return false;
				}
			}
			else
				return false;
		}
		
		if(file_exists($tmp_name));
		{
			$timestamp = time();
			$db_tmp_name = str_replace('\\',"/",$tmp_name);
			$sql = "INSERT INTO thread_attachments_temp (ticket_id, user_id, timestamp, temp_name, file_name, size, browser_mimetype) " . 
			       "VALUES ('".$ticket_id."', '".$user_id."', '".$timestamp."', '".$db_tmp_name."', '".$uploaded_file["name"].
			       "', '".$uploaded_file["size"]."', '".$uploaded_file["type"]."')";
			$this->db->query($sql);

			return $this->get_file_data($this->db->insert_id());
		}
	}
	
	function delete_file($file_id)
	{	
		global $session;
		
		$sql = "SELECT temp_name FROM thread_attachments_temp WHERE file_id = '".$file_id."'";
		$file_res = $this->db->query($sql);
		
		if($this->db->num_rows($file_res))
		{
			$file_data = $this->db->fetch_row($file_res);
			if(@unlink($file_data["temp_name"])) 
			{
				foreach($session->vars["uploaded_file_array"] as $idx=>$file_object)
					{ if($file_object->file_id == $file_id) unset($session->vars["uploaded_file_array"][$idx]); }	
				$sql = "DELETE FROM thread_attachments_temp WHERE file_id = '".$file_id."'";
				$this->db->query($sql);
				return true;
			}
			else 
			{
				// [JSJ]: Error occured. Do something about it.
			}
		}
		else
		{
			// [JSJ]: Error occured. Do something about it.
		}
	}
	
	function cleanup_stale_files()
	{
		$sql = "SELECT file_id FROM thread_attachments_temp WHERE timestamp < '".(time()-86400)."'";
		$file_res = $this->db->query($sql);
		
		if($this->db->num_rows($file_res)) {
			while($file_data = $this->db->fetch_row($file_res))
			{ $this->delete_file($file_data["file_id"]); }
		}
	}
	
	function get_file_data($file_id)
	{
		$sql = "SELECT * FROM thread_attachments_temp WHERE file_id = '".$file_id."'";
		$file_res = $this->db->query($sql);
		
		if($this->db->num_rows($file_res))
		{
			$file_data = $this->db->fetch_row($file_res);
			return new CER_UPLOADED_FILE_DB_DATA($file_data);
		}
		else
		{
			// [JSJ]: Error occured or file_id is invalid. Do something about it.
		}
	}
	
};

class CER_UPLOADED_FILE_DB_DATA
{
	var $file_id;
	var $user_id;
	var $timestamp;
	var $temp_name;
	var $file_name;
	var $size;
	var $browser_mimetype;

	
	function CER_UPLOADED_FILE_DB_DATA(&$db_row)
	{
		$this->file_id = $db_row["file_id"];
		$this->user_id = $db_row["user_id"];
		$this->timestamp = $db_row["timestamp"];
		$this->temp_name = $db_row["temp_name"];
		$this->file_name = $db_row["file_name"];
		$this->size = $db_row["size"];
		$this->browser_mimetype = $db_row["browser_mimetype"];
	}
};
