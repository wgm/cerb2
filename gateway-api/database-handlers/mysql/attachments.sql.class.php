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

/**
 * Database abstraction layer for attachments
 *
 */
class attachments_sql
{
	/**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
	var $db;

	/**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return attachmentse_sql
    */
	function attachments_sql(&$db) {
		$this->db =& $db;
	}

	/**
    * Get attachment parts
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
	function get_parts($params) {
		$file_id = $params["file_id"];
		$sql = "SELECT part_content FROM thread_attachments_parts WHERE file_id = '%d' ORDER BY part_id";
		return $this->db->Execute(sprintf($sql, $file_id));
	}

	/**
    * Check attachment exists
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
	function attachment_exists($params) {
		extract($params);
		$sql = "SELECT COUNT(*) FROM thread_attachments WHERE file_id = '%d'";
		return $this->db->GetOne(sprintf($sql, $file_id));
	}

	/**
    * Get attachment file info
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
	function get_file_info($params) {
		extract($params);
		$sql = "SELECT file_name, file_size FROM thread_attachments WHERE file_id = '%d'";
		return $this->db->GetRow(sprintf($sql, $file_id));
	}

	/**
    * Get attachment filename
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
	function get_filename($params) {
		extract($params);
		$sql = "SELECT file_name FROM thread_attachments WHERE file_id = '%d'";
		return $this->db->GetOne(sprintf($sql, $file_id));
	}
}