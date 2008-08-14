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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file cer_Database.class.php
\brief Database encapsulation classes.

Global database functions.  Will eventually be the loader for
different database platforms such as MS-SQL, Oracle, PgSQL -- as well
as the existing MySQL.  User configurable.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2004
*/

require_once(FILESYSTEM_PATH . "includes/third_party/adodb/adodb.inc.php");
require_once(FILESYSTEM_PATH . "cerberus-api/configuration/CerConfiguration.class.php");

//! Cerberus database clean string
/*!
Cleans a string pulled from a database field.  Converts quotes to HTML &quot;
and strips escape slashes.
\param $string string to make HTML safe
\return Cleaned string
*/
function cer_dbc($string="")
{
	$clean = stripslashes($string);
	$clean = str_replace("\"","&quot;",$clean);
	
	return $clean;
}

//! Microtime difference
/*!
Find the difference between microtime \a $a and \a $b.
\param $a \c microtime
\param $b \c microtime
\return difference in microtime
*/
function microtime_diff($a,$b) {
	list($a_micro, $a_int)=explode(' ',$a);
	list($b_micro, $b_int)=explode(' ',$b);
	if ($a_int>$b_int) {
		return ($a_int-$b_int)+($a_micro-$b_micro);
	} elseif ($a_int==$b_int) {
		if ($a_micro>$b_micro) {
			return ($a_int-$b_int)+($a_micro-$b_micro);
		} elseif ($a_micro<$b_micro) {
			return ($b_int-$a_int)+($b_micro-$a_micro);
		} else {
			return 0;
		}
	} else { // $a_int<$b_int
	return ($b_int-$a_int)+($b_micro-$a_micro);
	}
}

//! Cerberus database object
/*!
Database encapsulation object and functions.
*/
class cer_Database
{
	var $db; //!< Database connection handler
	
	function getInstance() {
		static $instance = NULL;
		
		if($instance == NULL) {
			$instance = new cer_Database();
			$instance->connect();
		}
		
		return $instance;
	}
	
	//! Connect and log in to the database server
	/*!
	Authenticate with the database server.  Reads server, user and password from
	the "DB_" constants in the site.config.php file
	
	\return Boolean true/false of success or failure.
	*/
	function connect($dbs="",$dbn="",$dbu="",$dbp="",$dbplat="mysql")
	{
		// [JAS]: If no alternative database is specified, use the default from site.config.php
		if($dbs=="")
		{
			$this->db = &ADONewConnection(DB_PLATFORM);
			@$this->db->connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)
				or die("Cerberus [ERROR]:  Could not connect to database.  Check your config.php DB_* settings.<br>Reason: (" . mysql_error() . ")");
		}
		else
		{
			$this->db = &ADONewConnection($dbplat);
			$this->db->connect($dbs, $dbu, $dbp, $dbn);
		}
		if($this->db) { return true; } else { return false; }
	}
	
	function close() {
		$this->db->close();
	}
	
	//! Execute a SQL query
	/*!
	Run a SQL query against the current database.  If running in \e DEBUG_MODE
	then all queries will be output directly to the screen in addition to being
	executed.
	
	\param $sqlString SQL \c string to be executed
	\param $return_asoc Return an associative array result set (if false, returns numerical array)
	\return \c Resultset object.
	*/
	function query($sqlString,$return_assoc=true)
	{
		$cfg = CerConfiguration::getInstance();
		
		if($cfg->settings["debug_mode"]) {
			$time_start = microtime();
		}
		
		//$res = mysql_query($sqlString,$this->db) or die ("Cerberus [ERROR]: Could not query database. (" . mysql_error() . ")");
		if($return_assoc === true) $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		else $this->db->SetFetchMode(ADODB_FETCH_NUM);
	
		$res = $this->db->Execute($sqlString);
		
		if($cfg->settings["debug_mode"]) {
			$time_end = microtime();
			$query_time = microtime_diff($time_end,$time_start) * 1000; // convert secs to millisecs
			echo "<b>[CERBERUS QUERY]:</b> " . $sqlString . " (Ran: <b>" . sprintf("%0.3f",$query_time) . "ms</b>)<hr>";
		}
		
		return $res;
	}
	
	//! Escape a string
	/*!
	Escape a string of all single and double quotes.
	\param $str \c string to escape
	\return escaped \c string
	*/
	function escape($str) 
	{
		return $this->db->qstr($str,false); // [JAS]: 2nd arg was get_magic_quotes_gpc()
	}
	
	//! Return the number of rows in the database \c resultset object
	/*!
	Find the difference between microtime \a $a and \a $b.
	\param $results database \c resultset.
	\return The number of rows in the \c resultset.
	*/
	function num_rows(&$results) 
	{
		if(!$results) return false;
		$retval = $results->RecordCount();
		return ($retval);
	}
	
	function affected_rows()
	{
		$retval = $this->db->Affected_Rows();
		return ($retval);
	}
	
	function grab_first_row(&$results)
	{
		if(!$this->num_rows($results)) return false; // [JAS]: Make sure we have rows to pull
		$row = $this->fetch_row($results);
		return $row;		
	}
	
	function fetch_object(&$results)
	{
		return $results->FetchNextObject();
	}
	
	function fetch_row(&$results) 
	{
		return $results->FetchRow();
	}
	
	//! Move the database record pointer
	/*!
	\param $results database \c resultset
	\param $seek \c integer position in resultset to seek to
	\return A \c boolean.
	*/
	function data_seek(&$results,$seek) 
	{
		return $results->Move($seek);
	}
	
	//! Return the record id produced by the last executed SQL query.
	/*!
	\return A record id (generally \c integer/\c bigint).
	*/
	function insert_id() 
	{
		return $this->db->Insert_ID();
	}
	
};
?>