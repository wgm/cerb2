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

define("ENTITY_REQUESTER",'R');
define("ENTITY_TICKET",'T');
define("ENTITY_TIME_ENTRY",'I');
define("ENTITY_COMPANY",'O');
define("ENTITY_CONTACT",'C');

class cer_CustomFieldGroupHandler {
	var $db = null;
	var $group_templates = array();
	var $group_instances = array();
	var $field_to_template = array();
	
	function cer_CustomFieldGroupHandler() {
		$this->db = cer_Database::getInstance();
	}
	
	function load_entity_groups($entity_code,$entity_index) {
		$sql = "SELECT efg.group_instance_id, efg.entity_code, efg.entity_index, g.group_id, g.group_name, ".
			"f.field_id, f.field_name, f.field_type, v.field_value ".
			"FROM `entity_to_field_group` efg ".
			"LEFT JOIN `field_group` g ON (g.group_id = efg.group_id) ".
			"LEFT JOIN `fields_custom` f ON (f.field_group_id = g.group_id) ".
			"LEFT JOIN `field_group_values` v ON (v.field_id = f.field_id AND v.group_instance_id = efg.group_instance_id) ".
			"WHERE efg.entity_code = '$entity_code' ".
			"AND efg.entity_index = $entity_index " .
			"ORDER BY efg.group_order, f.field_order, f.field_name";
		$res = $this->db->query($sql);
		
		$this->_loadInstancesFromResultSet($res);
		
		$cnt = $this->db->num_rows($res);
		
		if($cnt)
			return $cnt;
		else
			return false;
	}
	
	function loadSingleInstance($gi_id) {
		
		if(!isset($this->group_instances[$gi_id])) {
		$sql = "SELECT efg.group_instance_id, efg.entity_code, efg.entity_index, g.group_id, g.group_name, ".
			"f.field_id, f.field_name, f.field_type, v.field_value ".
			"FROM `entity_to_field_group` efg ".
			"LEFT JOIN `field_group` g ON (g.group_id = efg.group_id) ".
			"LEFT JOIN `fields_custom` f ON (f.field_group_id = g.group_id) ".
			"LEFT JOIN `field_group_values` v ON (v.field_id = f.field_id AND v.group_instance_id = efg.group_instance_id) ".
			"WHERE efg.group_instance_id = '$gi_id' ".
			"ORDER BY efg.group_order, f.field_order";
			$res = $this->db->query($sql);
			
			$this->_loadInstancesFromResultSet($res);
		}
			
	}
	
	function _loadInstancesFromResultSet($res) {
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				
				$gi_id = $row["group_instance_id"];
				$g_id = $row["group_id"];
				$fld_id = $row["field_id"];
				
				// [JAS]: If the group instance hasn't been initialized, do it now.
				if(!isset($this->group_instances[$gi_id])) {
					$group_instance = new cer_CustomFieldGroupInstance();
						$group_instance->group_instance_id = $row["group_instance_id"];
						$group_instance->group_id = $row["group_id"];
						$group_instance->group_name = stripslashes($row["group_name"]);
						$group_instance->setEntityCode($row["entity_code"]);
						$group_instance->entity_index = $row["entity_index"];
					$this->group_instances[$gi_id] = $group_instance;
				}
				
				$gi_ptr = &$this->group_instances[$gi_id];
				
				// [JAS]: Read in a field.
				$new_field = new cer_CustomField();
					$new_field->field_id = $fld_id;
					$new_field->field_name = stripslashes($row["field_name"]);
					$new_field->setType($row["field_type"]);
					$new_field->field_value = stripslashes($row["field_value"]);
				$gi_ptr->fields[$fld_id] = $new_field;
			}
		
		}
	}
	
	function loadGroupTemplates() {
		// [JAS]: Unfortunately, we LEFT JOIN for groups with no fields left to still show up
		$sql = "SELECT g.group_id, g.group_name, f.field_id, f.field_name, f.field_type, f.field_order ".
			"FROM field_group g ".
			"LEFT JOIN `fields_custom` f ON (f.field_group_id = g.group_id) " . 
			"ORDER BY f.field_group_id, f.field_order ";
		$res = $this->db->query($sql);
		
		$this->group_templates = array();
		
		$last_group_id = null;
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				
				$gid = $row["group_id"];
				$fld_id = $row["field_id"];
				
				if($last_group_id != $gid) {
					if(!isset($this->group_templates[$gid])) {
						$new_group = new cer_CustomFieldGroup();
						$new_group->group_id = $gid;
						$new_group->group_name = stripslashes($row["group_name"]);
						$this->group_templates[$gid] = $new_group;
					}
				}
				
				if(!empty($row["field_name"])) {
					$new_field = new cer_CustomField();
						$new_field->field_id = $fld_id;
						$new_field->field_name = stripslashes($row["field_name"]);
						$new_field->setType($row["field_type"]);
					$this->group_templates[$gid]->fields[$fld_id] = $new_field;
					$this->field_to_template[$fld_id] = &$this->group_templates[$gid];
				}
				
				$last_group_id = $gid;
			}
		}
	}
	
	function fieldValueExistsInInstance($field_id,$instance_id) {
		static $instances = array(); // [JAS]: Preserve this variable until the page is done, don't make redundant queries.
		
		if(!isset($instances[$instance_id])) {
			$sql = "SELECT v.field_id, v.field_value FROM field_group_values v WHERE v.group_instance_id = " . $instance_id;
			$res = $this->db->query($sql);
		
			$new_fields = array();
			
			if($this->db->num_rows($res)) {
				while($row = $this->db->fetch_row($res)) {
					$new_fields[$row["field_id"]] = 1; // $row["field_value"]
				}
				
				$instances[$instance_id] = $new_fields;
			}
		}
		
		if(isset($instances[$instance_id][$field_id]))
			return true;
		else
			return false;
	}
	
	function setFieldInstanceValue($field_id,$instance_id,$value) {

		// [JAS]: Load up the instance information if we haven't already.
		$this->loadSingleInstance($instance_id);
		
		$inst_ptr = &$this->group_instances[$instance_id];
		
		if($this->fieldValueExistsInInstance($field_id,$instance_id)) {
			
			$sql = sprintf("UPDATE field_group_values SET field_value=%s, entity_code='%s', entity_index=%d, field_group_id=%d ".
					"WHERE field_id = %d AND group_instance_id = %d ",
						$this->db->escape($value),
						$inst_ptr->entity_code,
						$inst_ptr->entity_index,
						$inst_ptr->group_id,
						$field_id,
						$instance_id
				);
		}
		else {
			// [JAS]: Don't inject null values.
			if(empty($value))
				return;
			
			$sql = sprintf("INSERT INTO field_group_values (field_id, field_value, group_instance_id, entity_code, entity_index, field_group_id) ".
					"VALUES (%d,%s,%d,'%s',%d,%d) ",
						$field_id,
						$this->db->escape($value),
						$instance_id,
						$inst_ptr->entity_code,
						$inst_ptr->entity_index,
						$inst_ptr->group_id
				);
		}
		
		$this->db->query($sql);
		return true;
	}
	
	function addGroup($group_name) {
		$sql = sprintf("INSERT INTO field_group (group_name) VALUES (%s);",
				$this->db->escape($group_name)
			);
		$this->db->query($sql);
		
		$id = $this->db->insert_id();
		return $id;
	}
	
	function addGroupInstance($entity_code, $entity_index, $group_id) {
		$sql = sprintf("INSERT INTO entity_to_field_group (entity_code, entity_index, group_id) ".
			"VALUES ('%s',%d,%d) ",
				$entity_code,
				$entity_index,
				$group_id
			);
		$this->db->query($sql);
		
		$id = $this->db->insert_id();
		return $id;
	}
	
	function addGroupField($f_name,$f_type,$f_gid,$f_order=0,$f_options=array(),$f_no_search=0) {
		$sql = sprintf("INSERT INTO `fields_custom` (field_name, field_type, field_not_searchable, field_group_id, `field_order`) " .
			"VALUES (%s,'%s',%d,%d,%d) ",
				$this->db->escape($f_name),
				$f_type,
				$f_no_search,
				$f_gid,
				$f_order
			);
		$this->db->query($sql);
		
		$id = $this->db->insert_id();
		
		// [JAS]: Import field options from an array into the database table.
		if(!empty($f_options)) {
			foreach($f_options as $idx => $opt) {
				$sql = "INSERT INTO fields_options (field_id,option_value,`option_order`) ".
					sprintf("VALUES (%d,%s,%d) ",
							$id,
							$this->db->escape($opt),
							$idx
						);
				$this->db->query($sql);
			}
		}
		
		return $id;
	}
	
	function addFieldOption($f_id, $f_option, $order=0) {
		$sql = "INSERT INTO fields_options (field_id, option_value, `option_order`) ".
			sprintf("VALUES (%d,%s,%d) ",
					$f_id,
					$this->db->escape($f_option),
					$order
				);
			$this->db->query($sql);
			
			$id = $this->db->insert_id();
			
			return $id;
	}
	
	function addFieldValue($f_id, $f_value, $g_instance_id) {
		
		$sql = sprintf("INSERT INTO field_group_values (field_id,field_value,group_instance_id) ".
			"VALUES (%d,%s,%d) ",
				$f_id,
				$this->db->escape($f_value),
				$g_instance_id
			);
		$this->db->query($sql);
		
		return true;
	}
	
	function updateGroupName($group_id,$group_name) {
		
		$sql = sprintf("UPDATE field_group SET group_name = %s WHERE group_id = %d",
				$this->db->escape($group_name),
				$group_id
			);
		$this->db->query($sql);
		
		return true;
	}
	
	function deleteFieldOptions($options) {
		if(!is_array($options)) $options = array($options);
		
		$sql = sprintf("DELETE FROM fields_options WHERE option_id IN (%s)",
				implode(",",$options)
			);
		$this->db->query($sql);
		
		return true;			
	}
	
	function updateFieldOptionOrdering($options) {
		if(!is_array($options)) $options = array($options);
		
		$order = 0;
		
		foreach($options as $o) {
			$sql = sprintf("UPDATE fields_options SET `option_order` = %d WHERE `option_id` = %d",
					$order++,
					$o
				);
			$this->db->query($sql);
		}
		
		return true;
	}
	
	function updateFieldOrdering($fields) {
		if(!is_array($fields)) $fields = array($fields);
		
		$order = 0;
		
		foreach($fields as $f) {
			$sql = sprintf("UPDATE `fields_custom` SET `field_order` = %d WHERE `field_id` = %d",
					$order++,
					$f
				);
			$this->db->query($sql);
		}
		
		return true;
	}
	
	function deleteGroupFields($group_id,$fields) {
		if(!is_array($fields)) $fields = array($fields);
		
		// [JAS]: Delete any options associated with these fields.
		$sql = sprintf("DELETE FROM fields_options WHERE field_id IN (%s)",
				implode(",",$fields)
			);
		$this->db->query($sql);
		
		// [JAS]: Delete any values associated with these fields.
		$sql = sprintf("DELETE FROM field_group_values WHERE field_id IN (%s)",
				implode(",",$fields)
			);
		$this->db->query($sql);
		
		// [JAS]: Delete the fields themselves.
		$sql = sprintf("DELETE FROM `fields_custom` WHERE field_group_id = %d AND field_id IN (%s)",
				$group_id,
				implode(",",$fields)
			);
		$this->db->query($sql);
		
		return true;
	}
	
	function deleteGroups($g_ids) {
		if(!is_array($g_ids)) $g_ids = array($g_ids);

		$this->loadGroupTemplates();
		
		foreach($g_ids as $gid) {
			
			$fld_ids = array();	
			
			// [JAS]: Nuke Fields
			if(!empty($this->group_templates[$gid]->fields))
			foreach($this->group_templates[$gid]->fields as $fld) {
				$fld_ids[] = $fld->field_id;
			}
			
			if(!empty($fld_ids)) {
				$this->deleteGroupFields($gid,$fld_ids);
			}
			
			// [JAS]: Nuke Instances of this group
			$sql = sprintf("SELECT g.group_instance_id FROM entity_to_field_group g WHERE g.group_id = %d",
					$gid
				);
			$res = $this->db->query($sql);
			
			if($this->db->num_rows($res)) {
				$inst_ids = array();
				
				while($row = $this->db->fetch_row($res)) {
					$inst_ids[] = $row["group_instance_id"];
				}
				$this->deleteGroupInstances($inst_ids);
			}
			
			// [JAS]: Delete the groups themselves
			$sql = sprintf("DELETE FROM field_group WHERE group_id IN (%s)",
					implode(",",$g_ids)
				);
			$this->db->query($sql);
		}

		
		return true;
	}
	
	function deleteGroupInstances($inst_ids) {
		if(!is_array($inst_ids)) $inst_ids = array($inst_ids);
		
		// [JAS]: Delete any values associated with these instances.
		$sql = sprintf("DELETE FROM field_group_values WHERE group_instance_id IN (%s)",
				implode(",",$inst_ids)
			);
		$this->db->query($sql);
		
		$sql = sprintf("DELETE FROM entity_to_field_group WHERE group_instance_id IN (%s)",
				implode(",",$inst_ids)
			);
		$this->db->query($sql);
		
		return true;
	}
};

class cer_CustomFieldGroupInstance extends cer_CustomFieldGroup
{
	var $group_instance_id = null;
	var $entity_code = null;
	var $entity_index = null;
	var $entity_name = null;
	var $group_id = null;
	var $group_name = null;
	
	function cer_CustomFieldGroupInstance() {
		$this->cer_CustomFieldGroup();
	}
	
	function setEntityCode($code) {
		$this->entity_code = $code;
		$this->entity_name = $this->getEntityName($code);
	}
	
	function getEntityName($code) {
		$name = null;
		
		switch($code) {
			case ENTITY_TICKET:
				$name = "ticket";
				break;
			case ENTITY_REQUESTER:
				$name = "requester";
				break;
		}
		
		return $name;
	}
};

class cer_CustomFieldGroup
{
	var $db = null;
	var $group_id = null;
	var $group_name = null;
	var $fields = array();
	
	function cer_CustomFieldGroup() {
		$this->db = cer_Database::getInstance();
	}
};

class cer_CustomField
{
	var $db = null;
	var $field_id = null;
	var $field_name = null;
	var $field_type = null;
	var $field_value = null;
	var $field_options = array();
	
	function cer_CustomField() {
		$this->db = cer_Database::getInstance();
	}
	
	function setType($type_code) {
		$this->field_type = $type_code;
		
		// [JAS]: Special handling for different field types
		switch($type_code) {
			
			// [JAS]: Dropdown
			case 'D': {
				if(empty($this->field_id)) break;
				
				$sql = sprintf("SELECT o.field_id, o.option_id, o.option_value ".
						"FROM fields_options o ".
						"WHERE o.field_id = %d ".
						"ORDER BY o.option_order, o.option_value ASC",
							$this->field_id
					);
				$res = $this->db->query($sql);
				
				if($this->db->num_rows($res)) {
					while($row = $this->db->fetch_row($res)) {
						$this->field_options[$row["option_id"]] = stripslashes($row["option_value"]);
					}
				}
				break;
			}
		} // end switch
	}
	
	function getTypeName()
	{
		switch($this->field_type) {
			case "S":
				$type_name = LANG_CONFIG_FIELDS_EDIT_TYPE_S;
				break;
				
			case "T":
				$type_name = LANG_CONFIG_FIELDS_EDIT_TYPE_T;
				break;
				
			case "D":
				$type_name = LANG_CONFIG_FIELDS_EDIT_TYPE_D;
				break; 	
				
			case "E":
				$type_name = LANG_CONFIG_FIELDS_EDIT_TYPE_E;
				break; 	
		}
		
		return $type_name;
	}
};

class cer_CustomFieldBindingHandler
{
	var $db;
	var $bindings = array(); // [JAS]: index is entity_code, value is group_template_id
	
	function cer_CustomFieldBindingHandler() {
		$this->db = cer_Database::getInstance();
		
		$sql = sprintf("SELECT cfb.entity_code, cfb.group_template_id ".
				"FROM field_group_bindings cfb "
			);
		$res = $this->db->query($sql);
		
		// [JAS]: Populate entity code hash
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$this->bindings[$row["entity_code"]] = $row["group_template_id"];
			}
		}
		
		return true;
	}
	
	function getEntityBinding($code) {
		if(!isset($this->bindings[$code]) || empty($this->bindings[$code]))
			return false;
			
		return $this->bindings[$code];
	}
};

?>