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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class table { 
	var $name;
	var $parent_link_local_col;
	var $parent_link_foreign_col;
	var $link_type;
	var $parent=NULL;
	var $children = array();
	
	function table($name, $parent, $local_col, $foreign_col, $link_type, &$dataset) {
		$this->name = $name;
		$this->parent_link_local_col = $local_col;
		$this->parent_link_foreign_col = $foreign_col;
		$this->link_type = $link_type;
		
		if(!empty($parent)) {
			$p = &$dataset->tables[$parent];
			$this->parent = &$p;
			$p->children[$name] = $name;
		}
	}
}

class dataset {

	var $tables = array();
	
	function dataset() {
	}
	
	function add_table($table_name, $parent, $local_col, $foreign_col, $link_type) {
		$this->tables[$table_name] = new table($table_name, $parent, $local_col, $foreign_col, $link_type, $this);
	}

	/*
	[mdf] returns an array of tables needed for the query
	based on an array of tables that is known will be needed.
	In other words, it gets any ancestor tables the input tables depend on.
	(used as a helper method by get_query)
	*/
	function get_query_tables($tables_subset) {
		$query_tables = array();
		for($i=0; $i < sizeof($tables_subset); $i++) {
			//add the current looped subset table to the result if it's not there already
			if(!in_array($tables_subset[$i], $query_tables)) {
				$query_tables[] = $tables_subset[$i];
			}
			
			//add any ancestors of the current subset table to the result
			$tmp =& $this->tables[$tables_subset[$i]];
			while($tmp->parent != NULL) {
				$tmp =& $tmp->parent;
				if(!in_array($tmp->name, $query_tables)) {
					$query_tables[] = $tmp->name;
				}
			}
		}
		return $query_tables;
	}
	
	/*
		returns the root table, the one that will appear first in sql queries
	*/
	function get_root_table_name() {
		//returns the first table it finds with a null parent
		foreach($this->tables AS $key=>$table) {
			if($table->parent == NULL) {
				$root_table = $key;
				break;
			}
		}
		return $root_table;
	}
	
	/*
	* Returns a SQL FROM statement with the tables specified, and any other dependent tables
    *
    * @param object $tables_subset Array of table names known to be needed for a query
    * @return The entire from statement string for a query including tables needed, but not in $tables_subset
	*/
	function get_query($tables_subset) {
		$root_table = $this->get_root_table_name();
		$sql = ' FROM ' . $root_table . ' ';
		$needed_tables = $this->get_query_tables($tables_subset);
		$sql .= $this->recurse_children($this->tables[$root_table], $needed_tables);
		return $sql;
	}
	
	/*
		Recursively build a string by traversing child nodes and adding joins as needed
	*/
	function recurse_children(&$parent_table, &$tables_needed) {

		if(sizeof($parent_table->children) == 0) {
			return "";
		}
		
		foreach($parent_table->children AS $child_table_name) {
			$child_table =&  $this->tables[$child_table_name];

			//only add a join for the column if it's in our needed tables.
			if(in_array($child_table_name, $tables_needed)) {
				$sql .= ' ' . $child_table->link_type . ' JOIN ' . $child_table_name . ' ON ' .
				 $child_table->parent_link_foreign_col . ' = ' . $child_table->parent_link_local_col . ' ';
				 
				//recurse on the children of the node in the dataset we are on, so we traverse the entire dataset (adding join conditions as needed)
				$sql .= $this->recurse_children($child_table, $tables_needed);
			}
			
		}
		
		//return the complete from statement
		return $sql;

	}
}

/*
$test = new dataset();

$test->add_table("user", NULL, NULL, NULL, NULL);
$test->add_table("chat", "user", "chat.user_id", "user.id", "LEFT");
$test->add_table("ticket", "user", "ticket.user_id", "user.id", "LEFT");
$test->add_table("email", "ticket", "email.ticket_id", "ticket.id", "LEFT");

//echo $test->get_query(array("chat", "email"));
*/
//print_r($qTables);
//print_r($test);

?>