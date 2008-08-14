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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file general.php
\brief Global display and formatting functions.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

$cerberus_translate = new cer_translate; //!< An instance of the Cerberus translation object

//! Cerberus Global formatting object
/*!
Cleans a string pulled from a database field.  Converts quotes to HTML &quot;
and strips escape slashes.
*/
class cer_formatting_obj
{
	//! Make a string safe for HTML output.
	/*!
	Cleans a \c string pulled from a database field.  Converts quotes to HTML &quot;,
	ampersand to &amp;, changes line feeds & carriage returns to \c BRs and strips escape slashes.
	
	\param $str \c string to make HTML safe
	\return HTML safe \c string
	*/
	function make_html_safe($str)
	{
		$str = @htmlspecialchars($str, ENT_COMPAT, LANG_CHARSET_CODE);
		$str = str_replace("\n","<br>",$str);
		$str = str_replace("\r","",$str);
		$str = str_replace("&amp;nbsp;"," ",$str);
		
		// [JAS]: Strip slashes substitute to keep actual slashes \
		$str = str_replace("\\\"","\"",$str);
		$str = str_replace("\\'","'",$str);
		
		// [JAS]: Replace amperand HTML safe w/ real ampersand.  Otherwise messes up
		//		quoted HTML and foreign charsets, such as Greek.
		$str = str_replace("&amp;","&",$str);
		
		return $str;
	}
	
	//! Determine the format of a date string retrieved from a database.
	/*!
	\param $ticket_date a database \c timestamp
	\return The timestamp date as an epoch (mktime) value
	*/
	function detect_date_to_epoch($ticket_date)
	{
		if(strpos($ticket_date,"-") && strpos($ticket_date,":"))
		$epoch_ticket = mktime(substr($ticket_date,11,2),substr($ticket_date,14,2),substr($ticket_date,17,2),substr($ticket_date,5,2),substr($ticket_date,8,2),substr($ticket_date,0,4));
		else
		$epoch_ticket = mktime(substr($ticket_date,8,2),substr($ticket_date,10,2),substr($ticket_date,12,2),substr($ticket_date,4,2),substr($ticket_date,6,2),substr($ticket_date,0,4));
		return($epoch_ticket);
	}
	
	// Formats a timestamp for RFC-822 output
	function format_db_date_rfc($ticket_date,$date_format="r")
	{
		if($ticket_date == 0) return -1;
		// Determine the date string format YYYYMMDDHHMMSS or YYYY-MM-DD HH:MM:SS
		$epoch_ticket = $this->detect_date_to_epoch($ticket_date);
		$time_ticket = date($date_format,$epoch_ticket);
		return $time_ticket;
	}
	
	//! Convert a database date to an epoch timestamp
	/*!
	\param $ticket_date a database \c timestamp
	\return An epoch \c timestamp
	*/
	function db_date_to_epoch($ticket_date)
	{
		$epoch_ticket = $this->detect_date_to_epoch($ticket_date);
		return $epoch_ticket;
	}
	
	//! Return the difference between \a $epoch_then and \a $epoch_now in seconds.
	/*!
	\param $epoch_now an epoch \c timestamp
	\param $epoch_then an epoch \c timestamp
	\return \c Integer seconds between timestamps
	*/
	function date_diff_epoch($epoch_now,$epoch_then)
	{
		$cfg = CerConfiguration::getInstance();
		
		$ticket_secs = $epoch_now - $cfg->settings["time_adjust"] - $epoch_then;
		return $ticket_secs;
	}
	
	//! Convert a number of seconds into seconds, minutes, hours, days
	/*!
	
	Prints using the fundamental concepts:
	60 seconds = 1 minute
	60 minutes = 1 hour
	24 hours = 1 day
	
	\param $ticket_secs an \c integer number of seconds
	\return A string stating the number of seconds, minutes, hours or days the given \a $ticket_secs represents.
	*/
	function format_seconds($ticket_secs)
	{
		if($ticket_secs < 60) $ticket_age = $ticket_secs . " " . LANG_DATE_SHORT_SECONDS;
		else if ($ticket_secs < 3600) $ticket_age = round($ticket_secs / 60) . " " . LANG_DATE_SHORT_MINUTES;
		else if ($ticket_secs > 86400) $ticket_age = round($ticket_secs / 86400) . " " . LANG_DATE_SHORT_DAYS;
		else if ($ticket_secs >= 3600) $ticket_age = round($ticket_secs / 3600) . " " . LANG_DATE_SHORT_HOURS;
		return $ticket_age;
	}
};

//! Cerberus global display object
/*!
Functions for displaying information in HTML (select boxes, drop-down menus, etc.)
*/
class cer_display_obj
{
	var $doDraw; //!< Class database handler
	
	//! Class constructor
	/*!
	Initiate a database connection for use in the class.
	*/
	function cer_display_obj()
	{
		$this->doDraw = new cer_Database();
		$this->doDraw->connect();
	}
	
	//! Draws a select box for the selection of helpdesk users based on given criteria.
	/*!
	\param $box_name the name of the SELECT form object in HTML
	\param $selectedIdx the initially selected index
	\param $style the CSS style to apply to the select box
	\param $defaultLine the optional default text to display in the select box (such as "--Choose One--")
	\param $queue_priv_id only show users with access to this queue id
	\param $show_nobody list "Nobody" as a selectable user
	\param $nobody_value the value to associate with Nobody
	\note The "Nobody" user value in the database is 0.
	\return void
	*/
	function draw_owner_select($box_name,$selectedIdx=-1,$style="",$defaultLine="",$queue_priv_id="",$show_nobody=false,$nobody_value="")
	{
		if($queue_priv_id != "") {
			$sql = "SELECT `user`.`user_id`,`user`.`user_login` FROM `user` ".
			"LEFT JOIN `queue_access` USING (`user_id`) ".
			"WHERE `queue_access`.`queue_id`=$queue_priv_id ".
			"ORDER BY `user`.`user_name` ASC";
		}
		else {
			$sql = "select user_id, user_login from user u order by user_login asc";
		}
		$result = $this->doDraw->query($sql,false);
		if($show_nobody==true) { $firstLineName = "Nobody"; $firstLineValue = $nobody_value; }
		if(!isset($firstLineName)) $firstLineName = "";
		if(!isset($firstLineValue)) $firstLineValue = "";
		$this->draw_select($result,$box_name,$selectedIdx,$style,$defaultLine,$firstLineName,$firstLineValue);
	}
	
	//! Draws a select box for the selection of ticket statuses
	/*!
	\param $status_options the status options array from site.config.php (pre-translation status names)
	\param $box_name the name of the SELECT form object in HTML
	\param $selectedIdx the initially selected index
	\param $defaultLine the optional default text to display in the select box (such as "--Choose One--")
	\return void
	*/
	function draw_status_select($status_options,$box_name,$selectedIdx="",$defaultLine="",$style="")
	{
		global $cerberus_translate;
		echo "<select name=\"$box_name\" class=\"$style\">";
		if($defaultLine != "") echo "<option value=\"-1\">$defaultLine";
		foreach($status_options as $status)
		{
			echo "<option value=\"$status\"";
			if($status == $selectedIdx) echo " SELECTED";
			echo ">" . $cerberus_translate->translate_status($status);
		}
		echo "</select>";
	}
	
	//! Draws a select box for the selection of queues
	/*!
	\param $box_name the name of the SELECT form object in HTML
	\param $selectedIdx the initially selected index
	\param $style the CSS style to apply to the select box
	\param $defaultLine the optional default text to display in the select box (such as "--Choose One--")
	\param $jscript_event a JavaScript event to associate with the SELECT form element. (optional)
	\param $queue_scope queue privliege criteria, options are: all, write or read.
	\return void
	*/
	function draw_queue_select($box_name,$selectedIdx=0,$style="",$defaultLine="",$jscript_event="",$queue_scope=all)
	{
		global $queue_access;		
		global $session;
		global $cerberus_db;
		
		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();
		
		$u_qids = $queue_access->get_readable_qid_list();
		$w_qids = $queue_access->get_writeable_qid_list();
		
		if($queue_scope=="write")	{
			$sql = "SELECT q.queue_id, q.queue_name ".
			"FROM queue q ".
			"WHERE q.queue_id IN ($w_qids) ".
			"ORDER by q.queue_name";
		}
		else if($queue_scope=="read") {
			$sql = "SELECT q.queue_id, q.queue_name ".
			"FROM queue q ".
			"WHERE q.queue_id IN ($u_qids) ".
			"ORDER by q.queue_name";
		}
		else if($queue_scope=="all") {
			$sql = "SELECT q.queue_id, q.queue_name ".
			"FROM queue q IN ($u_qids) ".
			"ORDER by q.queue_name";
		}
				
		$result = $this->doDraw->query($sql,false);
		$this->draw_select($result,$box_name,$selectedIdx,$style,$defaultLine,"","",$jscript_event);
	}
	
	//! Draws a select box for the selection of ticket priority
	/*!
	\param $priority_options the status options array from site.config.php (pre-translation status names)
	\param $box_name the name of the SELECT form object in HTML
	\param $selectedIdx the initially selected index
	\param $defaultLine the optional default text to display in the select box (such as "--Choose One--")
	\return void
	*/
	function draw_priority_select($priority_options,$box_name,$selectedIdx="",$defaultLine="",$style="")
	{
		global $cerberus_translate;
		echo "<select name=\"$box_name\" class=\"$style\">";
		if($defaultLine != "") echo "<option value=\"-1\">$defaultLine";
		foreach($priority_options as $value=>$status)
		{
			echo "<option value=\"$value\"";
			if($value == $selectedIdx) echo " SELECTED";
			echo ">" . $cerberus_translate->translate_status($status);
		}
		echo "</select>";
	}

	//! Plain vanilla function to draw a dynamic select box
	/*!
	
	This function is called by the user/status/queue select functions to eliminate redundant code.
	
	\param $result the database \c resultset to loop through when drawing the select box
	\param $box_name the name of the \c SELECT form object in HTML
	\param $selectedIdx the initially selected index
	\param $style the CSS style to apply to the select box
	\param $defaultLine the optional default text to display in the select box (such as "--Choose One--")
	\param $firstLineName an optional first line name (this wouldn't exist in the \c resultset, but would be an option in the \c SELECT box)
	\param $firstLineValue the optional first line value
	\param $jscript_event a JavaScript event to associate with the SELECT form element. (optional)
	\return void
	*/
	function draw_select($result, $box_name, $selectedIdx, $style, $defaultLine,$firstLineName="",$firstLineValue="",$jscript_event="")
	{
		echo "<select name=\"$box_name\" class=\"$style\" " . $jscript_event . ">\n";
		if($defaultLine != "") echo "<option value=\"-1\"" . (($selectedIdx==-1)?" SELECTED":"") . ">$defaultLine";
		if($firstLineName != "") echo "<option value=\"$firstLineValue\"" . (($selectedIdx==$firstLineValue)?" SELECTED":"") . ">$firstLineName";
		while($data_list = $this->doDraw->fetch_row($result))
		{
			echo "<option value=\"" . $data_list[0] . "\"";
			if($selectedIdx == $data_list[0]) echo " SELECTED";
			echo ">" . $data_list[1] . "\n";
		}
		echo "</select>\n";
	}
};
?>