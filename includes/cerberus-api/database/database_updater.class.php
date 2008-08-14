<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: database_updater.class.php
|
| Purpose: API allowing uniform database upgrade scripts for Official
|	Cerberus Releases, Patches, Mods & more.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";

define("DB_FIELD",0);

class CER_DB_TABLE
{
	var $db = null;
	var $table_name = null;
	var $table_exists = false;
	var $create_sql = null;
	var $fields = array();
	var $indexes = array();

	// [JAS]: Object constructor.
	//		Arg 1: DB pointer
	//		Arg 2: Table Name in DB
	//		Arg 3: Boolean, Read info from DB (set false to use blank object)
	function CER_DB_TABLE($table_name="",$populate_from_db=true)
	{
		$this->db = cer_Database::getInstance(); // [JAS]: DB pointer
		$this->table_name = $table_name;

		if($populate_from_db) 
			if(!$this->_read_table())
				return false;
	}
	
	// [JAS]: Checks if a field exists in the database object
	//		Example: if($tbl->field_exists("money")) echo "Woo hoo!";
	function field_exists($field_name=null)
	{
		if(isset($this->fields[$field_name])) return true;
		
		return false;
	}
	
	// [JSJ]: Checks if a index exists in the database object
	//		Example: if($tbl->index_exists("money")) echo "Woo hoo!";
	function index_exists($index_name=null)
	{
		if(array_key_exists($index_name, $this->indexes)) {
	      return TRUE;
	   }
		
		return FALSE;
	}
	
	// [JSJ]: Checks if a indexes info matches the database object
	function check_index_fields($index_name=null,$index_unique=null,$index_fields=array())
	{
	   if(!array_key_exists($index_name, $this->indexes)) {
	      return FALSE;
	   }
	   if(!is_null($index_unique) && $this->indexes[$index_name]->index_non_unique != $index_unique) {
	      return FALSE;
	   }
		if(!is_array($index_fields) || $index_fields !== $this->indexes[$index_name]->index_fields) {
		   return FALSE;
		}
		
		return TRUE;
	}
	
	// [JAS]: Read a database table and fields into the object.  Uses the 'table_name' member.
	// 	This method is called internally (private) and should never be called directly.
	function _read_table()
	{
		if(empty($this->table_name)) return false; // No table name

		$sql = "DESCRIBE `" . $this->table_name . "`";
		$result = $this->db->query($sql);

		if($this->db->num_rows($result) == 0) return false; // No table info
		
		$this->table_exists = true;
		
		while($row = $this->db->fetch_row($result))
		{
			$new_field = new CER_DB_FIELD();
			$new_field->field_name = strtolower($row["Field"]);
			$new_field->field_type = strtolower($row["Type"]);
			$new_field->field_null = $row["Null"];
			$new_field->field_key = $row["Key"];
			$new_field->field_default = $row["Default"];
			$new_field->field_extra = strtolower($row["Extra"]);
			
			$this->fields[$new_field->field_name] = $new_field;
		}
		
		// load the indexes for the table
		$sql = sprintf("SHOW INDEX FROM `%s`",
						$this->table_name
					);
		$result = $this->db->query($sql);
						
		if($this->db->num_rows($result)) {
			while($row = $this->db->fetch_row($result)) {
				$idx_name = strtolower($row["Key_name"]);
				if(!isset($this->indexes[$idx_name])) {
					$new_index = new CER_DB_INDEX();
					$new_index->index_name = $idx_name;
					$new_index->index_non_unique = $row["Non_unique"];
					$this->indexes[$new_index->index_name] = $new_index;
				}
				$this->indexes[$new_index->index_name]->addField(strtolower($row["Column_name"]));					
			}
		}
	}
	
	// [JAS]: Run an open-ended SQL statement.  Args: SQL string, Output to prefix success/fail
	// 		Example: 
	//		 $sql = "DELETE FROM civilization";
	//		 $this->run_sql($sql,"Initializing Apocalypse");
	function run_sql($sql=null,$output=null)
	{
		$write = false;
		$result = false;
		
		if(empty($sql)) return false;
		if(!empty($output)) $write = true;
		
		if($write) $this->output($output . " ... ");
		
		if($this->db->query($sql))	$result = true;
			else $result = false;
			
		if($write) $this->report_resultcode($result);
		
		return($result);
	}
	
	function output($str=null)
	{
		if(!empty($str))
			echo $str;
		flush();
	}		
	
	// [JAS]: Checks for a table's existence.  Use this when you *expect* a table to exist, such as
	//	before a drop or add field.  Otherwise use the 'table_exists' member of the object.
	//		Example: $dbl->check();
	function check($fatal=true,$silent=false)
	{
		$result = false;
		
		if(!$silent)
		$this->output("<b>Checking `".$this->table_name."` table existence ... </b>");

		if($this->table_exists)
			$result = true;
		
		if(!$silent)
		$this->report_resultcode($result,$fatal);
		
		return($result);
	}
	
	// [JAS]: Draw success or failure in color codes depending on a boolean.  If fatal is set, script will terminate.
	//		Example: $tbl->report_resultcode(false);
	function report_resultcode($bool=false,$fatal=true)
	{
		if($bool)
			$this->output("<font color='green'>success!</font><br>");
		else {
			$this->output("<font color='red'>failure!</font><br>");
			if($fatal) $this->halt();
		}
	}
	
	// [JAS]: Check a table for all required fields (and no extras).
	//		Returns:	A two element array where element: 0=extra_fields array()  1=missing_fields array();
	//		Example: 	$bad_flds = $tbl->verify_table(array("address_id" => DB_FIELD));

	function verify_table($fields=array(),$indexes=array())
	{
		$result = true;
		$this->output("<b>Verifying `" . $this->table_name . "` table structure</b> ... ");
		
		$extra_fields = array();
		$missing_fields = array();
		
		foreach($this->fields as $idx => $field)
			if(!isset($fields[$idx])) $extra_fields[$idx] = 1;
		
		foreach($fields as $idx => $field)
			if(!isset($this->fields[$idx])) $missing_fields[$idx] = 1;
			
		$return[0] = $extra_fields;
		$return[1] = $missing_fields;
		
		$extra_indexes = array();
		$missing_indexes = array();
		
		foreach($this->indexes as $id => $idx)
			if(!isset($indexes[$id])) $extra_indexes[$id] = 1;
			
		foreach($indexes as $id => $idx)
			if(!isset($this->indexes[$id])) $missing_indexes[$id] = 1;
		
		$return[2] = $extra_indexes;
		$return[3] = $missing_indexes;
			
		$this->report_resultcode($result);
		return($return);
	}
	
	// [JAS]: Splits a database field type into type name, type size and type flags.
	//		Example: int (type name), 11 (type size), unsigned (type flags)
	//		Database returns the field as:  int(11) unsigned
	function _split_field_type($field_type)
	{
		$type = array('name' => "",
					  'size' => "",
					  'flags' => ""
					  );
		
		$tmp = explode('(',$field_type,2);
		$type["name"] = trim($tmp[0]);
		
		if(count($tmp) == 2)
		{
			$tmp = explode(')',$tmp[1]);
			$type["size"] = trim($tmp[0]);
			$type["flags"] = trim($tmp[1]);
		}
		
		return $type;		
	}
	
	// [JAS]: Method to compare database field types, with custom handling where
	//		needed (based on type, e.g. timestamp).  Expects two arrays of three
	//		elements (name, size and flags) sent by CER_DB_TABLE::verify_field()
	//		Ideally $type_a is the db schema and $type_b is the current db layout.
	function _compare_field_types($type_a,$type_b)
	{
		// Make sure we're passed 3 element arrays.
		if(count($type_a) != 3 || count($type_b) != 3) return false;
		
		$pass = false;

		// [JAS]: Allow type-based overrides.
		switch(strtolower($type_a["name"]))
		{
			case "timestamp":
				if($type_a["name"] == $type_b["name"])
					$pass = true;
				break;
			
			case "enum":
				if($type_a["name"] == $type_b["name"])
					$pass = true;
				break;
			
			case "char":
			case "varchar";
				if(($type_a["name"] == "char" || $type_a["name"] == "varchar")
					&& ($type_b["name"] == "char" || $type_b["name"] == "varchar")
					&& ($type_a["size"] == $type_b["size"])
					)
					$pass = true;
				break;
			
			case "tinyint":
			case "int":
			case "bigint":
				if($type_a["name"] == $type_b["name"] &&
					$type_a["flags"] == $type_b["flags"])
					$pass = true;
				break;
				
			default:
				if($type_a["name"] == $type_b["name"] &&
					$type_a["size"] == $type_b["size"] &&
					$type_a["flags"] == $type_b["flags"])
						$pass = true;
				break;
		}
		
		return $pass;
	}
	
	// [JAS]: Checks a database field for validity (use 'DESCRIBE <table>' to get these valid values)
	// 		Example: $tbl->verify_field("address_id","int(10) unsigned","","PRI","","auto_increment");
	function verify_field($field_name=null,$field_type=null,$field_null=null,$field_key=null,$field_default=null,$field_extra=null,$fatal=true)
	{
		$result = true;
		$this->output("Verifying " . $this->table_name . "." . $field_name . " ... ");
		
		if(!isset($this->fields[$field_name])) {
			$result = false;
			$this->output("<b>doesn't exist!</b> ");
			$this->report_resultcode($result,$fatal);
			return($result);
		}

		$compare = $this->fields[$field_name];
		
		// [JAS]: Field Name Check ]===============================================================
		if ($compare->field_name != $field_name && $result) {
			$last_field = "field_name"; $last_compare = $compare->field_name;  $last_against = $field_name;
			$result = false; }
		
		// [JAS]: Field Type Check ]===============================================================
		$i_field_type = $this->_split_field_type($field_type);
		$c_field_type = $this->_split_field_type($compare->field_type);

		if (!$this->_compare_field_types($c_field_type,$i_field_type) && $result) {
			$last_field = "field_type"; $last_compare = $compare->field_type;  $last_against = $field_type;
			$result = false; }
		
		// [JAS]: Field Null Check ]===============================================================
//		if (($compare->field_null != $field_null) && $result) {
//			$last_field = "field_null"; $last_compare = $compare->field_null;  $last_against = $field_null;
//			$result = false; }
		
		// [JAS]: Field Key Check ]================================================================
		if (($compare->field_key != $field_key) && $result) {
			$last_field = "field_key"; $last_compare = $compare->field_key;  $last_against = $field_key;
			$result = false; }
		
		// [JAS]: Field Default Check ]============================================================
		if (($compare->field_default != $field_default) && $result && 0<strlen($field_default)) {
			$last_field = "field_default"; $last_compare = $compare->field_default;  $last_against = $field_default;
			$result = false; }
			
		// [JAS]: Field Extra Check ]==============================================================
		if (($compare->field_extra != $field_extra) && $result) {
			$last_field = "field_extra"; $last_compare = $compare->field_extra;  $last_against = $field_extra;
			$result = false; }
		
		if(!$result) 
			$this->output(sprintf("<b><font color='red'>WARNING:</font></b> <b>%s</b> is (<i><B>%s</B></i>) should be (<i><B>%s</B></i>): ",$last_field,$last_compare,$last_against));
			
		$this->report_resultcode($result,$fatal);
		return($result);
	}
	
	
	// [BGH]: Checks a database field index for validity (use 'SHOW INDEX FROM <table>' to get these valid values)
	// 		Example: $tbl->verify_index("primary","0",array("ticket_id"));
	function verify_index($idx_name=null,$idx_non_unique=1,$idx_fields=array(),$fatal=true)
	{
		$result = true;
		$this->output("Verifying index " . $this->table_name . "." . $idx_name . " ... ");
		
		if(!isset($this->indexes[$idx_name])) {
			$result = false;
			$this->output("<b>doesn't exist!</b> ");
			$this->report_resultcode($result,$fatal);
			return($result);
		}

		$compare = $this->indexes[$idx_name];
		
		// [BGH]: Index Name Check ]===============================================================
		if ($compare->index_name != $idx_name && $result) {
			$last_index = "index_name"; $last_compare = $compare->index_name;  $last_against = $idx_name;
			$result = false; }
		
		// [BGH]: Index Non Unique Check ]===============================================================
		if (($compare->index_non_unique != $idx_non_unique) && $result) {
			$last_index = "index_non_unique"; $last_compare = $compare->index_non_unique;  $last_against = $idx_non_unique;
			$result = false; }
		
		// [BGH]: Index Fields Check ]================================================================
		$comp = "";
		$agst = "";
		if(is_array($compare->index_fields) && !empty($compare->index_fields)) {
			$comp = implode(",",$compare->index_fields);
		}
		if(is_array($idx_fields) && !empty($idx_fields)) {
			$agst = implode(",",$idx_fields);
		}
		if ((0 != strcmp($comp,$agst)) && $result) {
			$last_index = "index_fields"; $last_compare = $comp;  $last_against = $agst;
			$result = false; }
		
		if(!$result) 
			$this->output(sprintf("<b><font color='red'>WARNING:</font></b> <b>%s</b> is (<i><B>%s</B></i>) should be (<i><B>%s</B></i>): ",$last_index,$last_compare,$last_against));
			
		$this->report_resultcode($result,$fatal);
		return($result);
	}
	
	// [JAS]: Add a field to the database table. Args: Field name, Field Params
	// 		Example: $tbl->add_field("thread_subject","char (255) default ''");
	function add_field($field_name=null,$field_params=null)
	{
		if(empty($field_name) || empty($field_params)) return false;

		if(!$this->field_exists($field_name)) {
			$sql = "ALTER TABLE `".$this->table_name."` ADD `".$field_name."` ".$field_params.";";
			$output = "Adding " . $this->table_name . "." . $field_name;
			
			$this->run_sql($sql,$output); 
		}
	}
	
	// [JAS]: Drop a field from the database table.  Args: Field name
	// 		Example: $tbl->drop_field("only_unassigned_in_new");
	function drop_field($field_name=null)
	{
		if(empty($field_name)) return false;

		if($this->field_exists($field_name)) {
			$sql = "ALTER TABLE `".$this->table_name."` DROP `".$field_name."`;";
			$output = "Dropping " . $this->table_name . "." . $field_name;
			
			$this->run_sql($sql,$output); 
		}
	}
	

	// [BGH]: Add a an index to the database table. Args: index name, non_unique, array of field names
	// 		Example: $tbl->add_index("new_index","0",array("ticket_id","queue_id"));
	function add_index($idx_name, $non_unique=1, $field_names=array())
	{
		if(empty($idx_name) || empty($field_names)) return false;

		if(!isset($this->indexes["$idx_name"])) {
			$type = "UNIQUE";
			if($non_unique) {
				$type="INDEX";
			}
			
			$sql = sprintf("ALTER TABLE `%s` ADD %s `%s` ( `%s` ) ",
							$this->table_name,
							$type,
							$idx_name,
							implode("`,`",$field_names)
						);
			$output = "Adding Index " . $this->table_name . "." . $idx_name . " on " . implode(",", $field_names);
			$this->run_sql($sql,$output); 
		}
	}	
	
	
	// [JAS]: Fatal exit routine.  Halts running a database script.
	//		Example: $tbl->halt();
	function halt()
	{
		$this->output("<br><font color='red'>A problem was encountered.  Script terminating.</font><br>");
		exit();
	}
	
};

class CER_DB_FIELD
{
	var $field_name=null;
	var $field_type=null;
	var $field_null=null;
	var $field_key=null;
	var $field_default=null;
	var $field_extra=null;
	
	function CER_DB_FIELD($fld_name=null,$fld_type=null,$fld_null=null,$fld_key=null,$fld_default=null,$fld_extra=null)
	{
		$this->field_name = $fld_name;
		$this->field_type = $fld_type;
		$this->field_null = $fld_null;
		$this->field_key = $fld_key;
		$this->field_default = $fld_default;
		$this->field_extra = $fld_extra;
	}
};

class CER_DB_INDEX
{
	var $index_name=null;
	var $index_non_unique=1; // 0 = unique, 1 = not unique
	var $index_fields=array(); // array of field names for this index

	function CER_DB_INDEX($idx_name=null,$non_unique=0,$idx_fields=null) {
		$this->index_name=$idx_name;
		$this->index_non_unique=$non_unique;
		if(!empty($idx_fields)) {
			if(is_array($idx_fields)) {
				// copy array to our array
				$this->index_fields=$idx_fields;
			}
			else {
				// convert the text to an array if it's not empty
				$this->index_fields=array($idx_fields);
			}
		}
	}
	
	function addField($idx_field) {
		$this->index_fields[] = $idx_field;
	}
	
};

?>