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

class cer_QueueCatchallRuleHandler {
	var $db = null;
	var $rules = array();
	
	function cer_QueueCatchallRuleHandler() {
		$this->db = cer_Database::getInstance();
		$this->_loadRules();
	}
	
	function _loadRules() {
		$sql = "SELECT c.catchall_id, c.catchall_name, c.catchall_pattern, c.catchall_to_qid, c.catchall_order ".
			"FROM queue_catchall c ".
			"ORDER BY catchall_order ASC";
		$res = $this->db->query($sql);

		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$new_rule = new cer_QueueCatchallRule();
					$new_rule->catchall_id = $row["catchall_id"];
					$new_rule->catchall_name = stripslashes($row["catchall_name"]);
					$new_rule->catchall_pattern = stripslashes($row["catchall_pattern"]);
					$new_rule->catchall_to_qid = $row["catchall_to_qid"];
					$new_rule->catchall_order = $row["catchall_order"];
				$this->rules[] = $new_rule;
			}
		}
	}
	
	function findAddressCatchallQueue($address) {
		
		foreach($this->rules as $rule) {
			if(preg_match($rule->catchall_pattern,$address)) {
				return $rule->catchall_to_qid;
				break;
			}
		}
		
		return false;
	}
};

class cer_QueueCatchallRule {
	var $catchall_id = null;
	var $catchall_name = null;
	var $catchall_pattern = null;
	var $catchall_to_qid = null;
	var $catchall_order = null;
};

?>