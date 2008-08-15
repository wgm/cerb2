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
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|
| Contributors:
|		Sean Coates			(sean@php.net)					[SC]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file session.php
\brief Session and Login handling functions.

Classes to maintain objects for users, their groups, their privileges, etc.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/xsp_master_gui.php");
require_once(FILESYSTEM_PATH . "includes/functions/whos_online.php");
require_once(FILESYSTEM_PATH . "includes/functions/error_trapping.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/hash/core.hash.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/fileuploads/fileuploads.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/gui_parser_log.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_TicketPersistentSearch.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/layout/cer_Layout.class.php");

/**! Redirect browser using the META refresh tag
* @return void
* @param meta_url = "" string URL to redirect to
* @param meta_delay = 0 int number of seconds to wait before redirecting
* @desc Redirects the browser to a new URL using the META refresh HTML tag

*/
function meta_redirect($meta_url="", $meta_delay=0)
{
	?><meta http-equiv="refresh" content="<?php echo $meta_delay; ?>; url=<?php echo $meta_url; ?>"><?php
}

//! Prints a URL for displaying in an A HREF within the helpdesk.
/*!
Makes sure links are safe, encoded and optionally appends
session id to all URLs.

\param $link \c string URL
\return Formatted URL
*/
function cer_href($link,$anchor="")
{
	$cfg = CerConfiguration::getInstance();
	global $session;
	
	$sid = "sid=" . $session->session_id;
	
	if(@$cfg->settings["track_sid_url"])
	{
		$link .= ((strstr($link,"?")===FALSE) ? "?" : "&" ) . $sid;
	}
	
	if(!empty($anchor)) $link .= "#" . $anchor;
	
	return $link;
}

//! Cerberus User Group Object
/*!
Creates an instance of a user group object.  Currently this is used in the session
object to determine the access rights of the logged in user.
*/
class user_groups_obj {
	var $db = null;
	var $group_id; //!< Group ID from database (int)
	var $group_name; //!< Name of the user group (for example, "Administrators") (string)
	var $is_core_default = null; //!< Is the XSP login default group (if not set to superuser by default)
	var $group_acl; //!< Group Access Control List (bit field)
	var $group_acl2; //!< Group Access Control List (bit field)
	var $group_acl3; //!< Group Access Control List (bit field)
	
	//! Class constructor
	/*!
	Populates the group object with the properties from database user group \a $grp_id
	
	\param $grp_id \c integer group id
	\return void
	*/
	function user_groups_obj($grp_id="") {
		if($grp_id=="") return false;
		
		$this->db = cer_Database::getInstance();
		$this->group_id = $grp_id;
		$sql = "SELECT `group_id`,`group_name`,`group_acl`,`group_acl2`,`group_acl3` ".
		" FROM user_access_levels WHERE `group_id` = $grp_id";
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result) > 0)
		{
			$group_data = $this->db->fetch_row($result);
			$this->group_name = $group_data["group_name"];
			$this->group_acl = $group_data["group_acl"];
			$this->group_acl2 = $group_data["group_acl2"];
			$this->group_acl3 = $group_data["group_acl3"];
		}
	}
};


class cer_LoginLog {
	var $db = null;
	var $uid = null;
	var $login_tries = 0;
	var $last_login = array("local_time_logout" => "0000-00-00 00:00:00",
							"gmt_time_logout" => "0000-00-00 00:00:00"
							);
	
	function cer_LoginLog($uid) {
		$this->db = cer_Database::getInstance();
		$this->uid = $uid;
		
		$this->loadLoginLog();
	}
	
	function loadLoginLog() {
		$sql = sprintf("SELECT ll.id, ll.user_id, ll.local_time_logout, ll.gmt_time_logout, ll.local_time_login, ll.gmt_time_login ".
						"FROM user_login_log ll ".
					    "WHERE ll.user_id = %d ".
					    "ORDER BY ll.id DESC ".
					    "LIMIT 0,1",
					$this->uid
			   	);
		$res = $this->db->query($sql);
		
		if($log_row = $this->db->grab_first_row($res)) {
			foreach($log_row as $idx => $val) {
				$this->last_login[$idx] = $val;
			}
		}
	}
	
	function logLogin() {
		$this->login_tries++;
		
		// [JAS]: If two tries won't work, chances are we'll never get it.  Bail out of recursion!
		if($this->login_tries > 2) {
			$this->login_tries = 0;
			return false;
		}
		
		if($this->lastLogoutExists()) { // [JAS]: We can log in normally.
			
			list($local_login,$gmt_login) = $this->getCurrentDates();
			
			$sql = sprintf("INSERT INTO user_login_log (user_id,user_ip,local_time_login,gmt_time_login) " .
							"VALUES(%d,'%s','%s','%s')",
						$this->uid,
						$_SERVER['REMOTE_ADDR'],
						$local_login,
						$gmt_login
					);
			$this->db->query($sql);
		}
		else {
			$this->computeLastLogout();
			$this->logLogin();
		}
	}
	
	function logLogout() {
		if(!$this->lastLogoutExists()) {
			$row_id = $this->last_login["id"];
			
			list($local_logout,$gmt_logout) = $this->getCurrentDates();
			
			$secs = 0;
			if(($then = strtotime($this->last_login["local_time_login"])) != -1
					&& ($now = strtotime($local_logout)) != -1
					) {
				$secs = $now - $then;
			}
			
			$sql = sprintf("UPDATE user_login_log SET gmt_time_logout = '%s', local_time_logout = '%s', logged_secs = %d " .
							"WHERE id = %d",
						$gmt_logout,
						$local_logout,
						$secs,
						$row_id
					);
			$this->db->query($sql);
		}
		else { // [JAS]: We didn't have a last login, throw an exception
			// ...
		}
	}
	
	function getCurrentDates() {
		$cur_date = date("Y-m-d H:i:s");
		$local_logout = $cur_date;
		$stamp = new cer_DateTime($cur_date);
		$gmt_logout = $stamp->getGMTDate("Y-m-d H:i:s");
		
		return array($local_logout, $gmt_logout);
	}
	
	function lastLogoutExists() {
		// [JAS]: If we didn't have a previous login, assume we're ready for a new one
		if(!isset($this->last_login["id"]))
			return true;
		
		// [JAS]: Otherwise, if the previous login existed but had no logout
		if($this->last_login["local_time_logout"] == "0000-00-00 00:00:00"
			|| $this->last_login["gmt_time_logout"] == "0000-00-00 00:00:00"
		) {
			return false;
		}
		else { // [JAS]: There was a previous logout with valid logout stamps
			return true;
		}
	}
	
	function computeLastLogout() {
		$sql = sprintf("SELECT al.timestamp FROM ticket_audit_log al ".
						"WHERE al.user_id = %d ".
						"AND al.timestamp > '%s' " .
						"ORDER  BY al.timestamp DESC ".
						"LIMIT 0, 1",
					$this->uid,
					$this->last_login["local_time_login"]
				);
		$res = $this->db->query($sql);
		
		$row_id = $this->last_login["id"];

		if($row = $this->db->grab_first_row($res)) {
			$local_logout = $row["timestamp"];
			$stamp = new cer_DateTime($local_logout);
			$gmt_logout = $stamp->getGMTDate("Y-m-d H:i:s");
		}
		else {
			$gmt_logout = $this->last_login["gmt_time_login"];
			$local_logout = $this->last_login["local_time_login"];
		}
		
		$secs = 0;
		if(($then = strtotime($this->last_login["local_time_login"])) != -1
				&& ($now = strtotime($local_logout)) != -1
				) {
			$secs = $now - $then;
		}
			
		$sql = sprintf("UPDATE user_login_log SET gmt_time_logout = '%s', local_time_logout = '%s', logged_secs = %d " .
					"WHERE id = %d",
				$gmt_logout,
				$local_logout,
				$secs,
				$row_id
			);
		$this->db->query($sql);	
		
		// [JAS]: Reload login data
		$this->loadLoginLog();
	}
	
};

//! Cerberus Login Handler
/*!
Handles login/logout functionality.  Stays in sync with database, group and
user preferences changes on each page load.  Stores information related to
the current logged in user in the session for use in the GUI.
*/
class login_handler_mgr {
	var $db = null;
	var $user_id; //!< User ID (integer)
	var $user_name; //!< User Name (e.g., 'Jeff Standen') (string)
	var $user_email; //!<  User E-mail (e.g., 'jeff@webgroupmedia.com) (string)
	var $user_login; //!< User Login (e.g., 'jstanden') (string)
	var $user_password; //!< User Password (e.g., 'pass') (string)
	var $user_last_login; //!< User Last Login (timestamp)
	var $user_superuser; //!< User is a Superuser (boolean, 0 or 1)
	var $user_logoff; //!< User is Logging Off (boolean)
	var $user_access; //!< User Access Rights Object Instance
	var $ticket_id; //!< Last Ticket ID Viewed
	var $ticket_mask; //!< Last Ticket Mask Viewed
	var $ticket_subject; //!< Last Ticket Subject Viewed
	var $ticket_url; //!< Last Ticket Viewed Display URL
	var $user_prefs; //!< User Preferences Object Instance
	var $batch; //!< Batched Ticket Object Instance
	var $is_xsp_user = false; //!< Is the user a core (xSP Master GUI) user, not local
	var $has_new_pm = 0;
	var $has_unread_pm = 0;
	
	function login_handler_mgr() {
		$this->db = cer_Database::getInstance();
	}
	
	function do_logout() {
		$this->user_id = "";
		$this->user_logoff = 1;
		$this->user_password = "";
		$this->user_superuser = "";
	}
	
	function do_login($p_user,$p_pass) {
		$this->perform_local_login($p_user,$p_pass);
	}
	
	function check_for_pm()	{
		
		$sql = sprintf("SELECT pm.pm_id, u.user_id as from_id FROM (private_messages pm, user u) ".
			"WHERE u.user_id = pm.pm_from_user_id AND pm.pm_to_user_id = %d AND pm.pm_notified = 0 ".
			"ORDER BY pm.pm_date DESC LIMIT 0,1",
				$this->user_id);
		$result = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($result))
		{ $this->has_new_pm = $row["pm_id"]; } else { $this->has_new_pm = 0; }
		
		$sql = sprintf("SELECT count(*) as num_pm FROM private_messages WHERE pm_marked_read = 0 and pm_to_user_id = %d",
				$this->user_id);
		$result = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($result))
		{ $this->has_unread_pm = $row["num_pm"]; } else { $this->has_unread_pm = 0; }
	}

	function check_who_online_entry()
	{
		global $_SERVER;
		
		$sql = sprintf("SELECT w.user_id FROM whos_online w WHERE w.user_id = %d",
		$this->user_id);
		$who_res = $this->db->query($sql);
		if($this->db->num_rows($who_res))
		{
			// don't log, they'll hit a page to update log before long
		}
		else
		{
			$sql = sprintf("INSERT INTO whos_online (user_id,user_what_action,user_ip,user_timestamp) ".
			"VALUES (%d,'%s','%s',NOW())",$this->user_id,WHO_AUTH,$_SERVER["REMOTE_ADDR"]);
			$this->db->query($sql);
		}
	}
	
	function perform_local_login($p_user,$p_pass)
	{
		$update_pw_hash = false;
		
		$md5_pass = md5($p_pass);
		$sql = sprintf("SELECT u.user_id, u.user_name, u.user_login, u.user_password, u.user_last_login, u.user_superuser, u.user_email, u.user_group_id, u.user_xsp " .
					   "FROM user u " .
					   "WHERE u.user_login=%s AND u.user_password='%s'",
					$this->db->escape($p_user),
					$md5_pass
				);
		$result = $this->db->query($sql,false);

		// [JAS]: If our MD5 look-up failed, try crypt for previous versions
		if(!$this->db->num_rows($result))
		{
			$crypt_pass = crypt($p_pass,substr($p_pass,1,2));
			$sql = sprintf("SELECT u.user_id, u.user_name, u.user_login, u.user_password, u.user_last_login, u.user_superuser, u.user_email, u.user_group_id, u.user_xsp " .
			"FROM user u " .
			"WHERE u.user_login=%s AND u.user_password='$crypt_pass'",
			$this->db->escape($p_user));
			$result = $this->db->query($sql,false);
			
			$update_pw_hash = true;
		}
		
		if($this->db->num_rows($result)) // user match
		{
			$row = $this->db->fetch_row($result);
			$this->user_id = $row[0];
			$this->user_name = $row[1];
			$this->user_login = $row[2];
			$this->user_email = $row[6];
			$this->user_password = $p_pass;
			$this->user_superuser = $row[5];
			$this->user_last_login = $row[4];
			$this->user_logoff = 0;
			$this->user_access = new user_groups_obj($row[7]); // fire up the group class constructor
			$this->user_prefs = new user_prefs_mgr($row[0]); // fire up the prefs class constructor
			$this->batch = new batch_object;
			$sql = sprintf("UPDATE user SET user_last_login = NOW()%s WHERE user_id = %d",
				(($update_pw_hash)?",user_password='$md5_pass'":""),
				$this->user_id
				);
			$this->db->query($sql);
			if($row[8] == 1) $this->is_xsp_user = true;
			$this->check_who_online_entry();
			$this->check_for_pm();
			return true;
		}
		else
		{
			$this->user_id = "";
			$this->user_login = "";
			$this->user_superuser = "";
			$this->user_logoff = 1;
			$this->is_xsp_user = false;
			return false;
		}
	}
	
};

class batch_object {
	var $tickets; // tickets batch array
	
	// The batch object constructor
	function batch_object() {
		$this->tickets = array();
	}
	
	function get_tickets() {
		return $this->tickets;
	}
	
	// Find ticket_id in batch object
	function in_batch($ticket_id=0) {
		foreach($this->tickets as $key=>$ticket) {
			if($ticket_id == $ticket) return($key);
		}
		return(false);
	}
	
	// Add a ticket to the batch, if not already in
	function batch_add($ticket_id=0) {
		if($this->in_batch($ticket_id)===false) {
			array_push($this->tickets,$ticket_id);
		}
	}
	
	// Remove ticket from batch if it exists
	function batch_remove($ticket_id=0) {
		$tmp_array = array();
		if($this->in_batch($ticket_id)!==false) {
			foreach($this->tickets as $tkt)
			{
				if($tkt != $ticket_id) array_push($tmp_array,$tkt);
			}
			$this->tickets = $tmp_array;
		}
	}
	
};

class user_prefs_mgr_view_prefs {
	var $vars;
};

class user_prefs_mgr_assign_queues {
	var $max=5;
	var $queues=array();
};

class user_prefs_mgr {
	var $db = null;
	var $user_refresh_rate;
	var $user_ticket_order;
	var $user_language;
	var $user_signature_pos;
	var $user_signature_autoinsert;
	var $view_prefs;
	var $assign_queues;
	var $keyboard_shortcuts;
	var $gmt_offset;
	
	var $page_layouts = array();
	var $layout_handler = null;
	var $layout_prefs = null;
	
	function user_prefs_mgr($u_id) {
		$cfg = CerConfiguration::getInstance();
		
		$this->db = cer_Database::getInstance();
		$this->layout_handler = new cer_LayoutHandler(array($u_id));
		$this->layout_prefs = &$this->layout_handler->users[$u_id];
		
		// [JAS]: DHTML page layout states
		$default_page_layouts = array("layout_home_show_queues" => 1,
										"layout_home_show_search" => 1,
										"layout_home_header_advanced" => 0,
										"layout_view_options_av" => 0,
										"layout_view_options_uv" => 0,
										"layout_view_options_sv" => 0,
										"layout_view_options_bv" => 0,
										"layout_display_show_log" => 1,
										"layout_display_show_suggestions" => 1,
										"layout_display_show_history" => 1,
										"layout_display_show_contact" => 1,
										"layout_display_show_fields" => 1,
										"layout_display_show_glance" => 1,
										"layout_display_show_vitals" => 1
										);
		
		$sql = "SELECT prefs.refresh_rate,prefs.ticket_order,prefs.user_language,".
			"prefs.signature_pos,prefs.view_prefs,prefs.assign_queues,prefs.signature_autoinsert,".
			"prefs.keyboard_shortcuts,prefs.page_layouts,gmt_offset " .
			"FROM user_prefs prefs " .
			"WHERE prefs.user_id='$u_id'";
		$result = $this->db->query($sql,false);
		
		if($this->db->num_rows($result) > 0) // user match
		{
			$row = $this->db->fetch_row($result);
			$this->user_refresh_rate = $row[0];
			$this->user_ticket_order = $row[1];
			$this->user_language = $row[2];
			$this->user_signature_pos = $row[3];
			$this->user_signature_autoinsert = $row[6];
			$this->view_prefs = new user_prefs_mgr_view_prefs();
			$this->view_prefs->vars = (unserialize(stripslashes($row[4]))) ? unserialize(stripslashes($row[4])) : array();
			$this->assign_queues = (unserialize(stripslashes($row[5]))) ? unserialize(stripslashes($row[5])) : array();
			$this->keyboard_shortcuts = $row[7];
			$this->gmt_offset = $row[9];
			$this->page_layouts = (unserialize(stripslashes($row[8]))) ? unserialize(stripslashes($row[8])) : $default_page_layouts;
		}
		else // invalid user
		{
			$sql = "INSERT INTO user_prefs (user_id) VALUES ($u_id)";
			$this->db->query($sql);
			
			$this->user_refresh_rate = "0";
			$this->user_ticket_order = "0";
			$lang = $cfg->settings["default_language"];
			if(empty($lang)) $lang = "en";
			$this->user_language = $lang;
			$this->signature_pos = "0";
			$this->user_signature_autoinsert = "1";
			$this->view_prefs = new user_prefs_mgr_view_prefs();
			$this->assign_queues = new user_prefs_mgr_assign_queues();
			$this->keyboard_shortcuts = 0;
			$this->gmt_offset = $cfg->settings["server_gmt_offset_hrs"];
			$this->page_layouts = $default_page_layouts;
		}
	}
};

$cerberus_db = cer_Database::getInstance();

class CER_SESSION
{
	var $db;
	var $s_id;
	var $session_id;
	var $session_ip;
	var $session_timestamp;
	var $vars;
	var $not_in_db;

	/**
	 * @return CER_SESSION
	 * @param uid int
	 * @desc Constructor, takes in user id and establishes/restores session object from serialized database
	 */
	function CER_SESSION($sid=-1)
	{
		global $_SERVER;
		global $cerberus_db;
		
		$this->vars = array(); // variables array for serialization

		$this->db = &$cerberus_db;
		
		// If this session doesn't exist yet, create a new session.
		if(!$this->_restore_session($sid))
		{
			$sid_gen = mktime() . str_replace(".","",$_SERVER['REMOTE_ADDR']) . rand(1000,9999);
			$this->session_id = md5($sid_gen);
			setcookie("sid",$this->session_id);
			
			$this->_restore_session($this->session_id);
		}
	}
	
	// [JAS]: Do an IP comparison on the current client and the sessions original IP
	function _perform_security_check($ses_ip)
	{
		$cfg = CerConfiguration::getInstance();
		global $_SERVER;
		$cli_ip = $_SERVER['REMOTE_ADDR'];
		
		// [JAS]: Pull session + client IP into arrays split on the dots (.)
		$session_ip = explode('.',$ses_ip);
		$client_ip = explode('.',$cli_ip);
		
		switch($cfg->settings["session_ip_security"])
		{
			case 0: // Full IP Match
				$sub_max = 3;
				break;
			case 1: // Class C Mask (12.34.56.xxx)
				$sub_max = 2;
				break;
			case 2: // Class B Mask (12.34.xxx.xxx)
				$sub_max = 1;
				break;
			case 3: // Class A Mask (12.xxx.xxx.xxx)
				$sub_max = 0;
				break;
			default:
			case 4: // Disabled (xxx.xxx.xxx.xxx)
				$sub_max = -1;
				break;
		}
		
		// [JAS]: Compare each subset to the depth requested
		for($x=0;$x<=$sub_max;$x++) {
			if($session_ip[$x] != $client_ip[$x]) return false;
		}
		
		return true;
	}
	
	function _restore_session($sid=-1)
	{
		global $_SERVER, $cerberus_db;
		
		$sql = "SELECT s.s_id,s.session_id,s.session_ip,s.session_timestamp FROM session s WHERE s.session_id = '" . $sid . "'";
		$ses_res = $this->db->query($sql);
		
		if($this->db->num_rows($ses_res))
		{
			$ses_row = $this->db->fetch_row($ses_res);
			
			// [JAS]: Verify the IP of the client against the IP of the session originator
			if(!$this->_perform_security_check($ses_row["session_ip"])) {
				$log = new CER_GUI_LOG();
				$log->log(sprintf("Session IP Security failed.  Logged attempt from <%s>.",$_SERVER['REMOTE_ADDR']));
				$this->_destroy_session();
				die(sprintf("Cerberus [ERROR]: Session IP Security failed.  Logged attempt from <%s>.",$_SERVER['REMOTE_ADDR']));
			}
			
			$this->s_id = $ses_row["s_id"];
			$this->session_id = $sid;
			$this->session_ip = $_SERVER['REMOTE_ADDR'];
			$this->session_timestamp = $ses_row["session_timestamp"];
			
			$sql = "SELECT s.s_id,s.var_name,s.var_val FROM session_vars s WHERE s.s_id = " . $this->s_id;
			$sv_res = $this->db->query($sql);
			
			//echo "<pre>"; print_r($sv_res); echo "</pre>";
			
			if($this->db->num_rows($sv_res))
			{
				while($var_row = $this->db->fetch_row($sv_res))
				{
					$this->vars[stripslashes($var_row["var_name"])] = unserialize(stripslashes($var_row["var_val"]));
				}
			}
			
			$this->vars["login_handler"]->db = &$cerberus_db;
			
			$this->not_in_db = false;
			return true;
		}
		else 
		{ $this->not_in_db = true; return false; }
	}
	
	// [SC]: Destroys a session - useful when IP conflicts
	function _destroy_session()
	{
		// [SC]: make cookie expire in the past (immediately; delete cookie):
		return setcookie('sid', '', time() - 3600);
	}
 
 	function save_session()
	{
		if($this->not_in_db) // if this is a new session, save the session before the vars
		{		
			$sql = "INSERT INTO session (session_id,session_ip,session_timestamp) ".
				sprintf("VALUES ('%s','%s',NOW())",$this->session_id,$_SERVER['REMOTE_ADDR']);
			$this->db->query($sql);

			$this->s_id = $this->db->insert_id();
			
			$this->not_in_db = false;
		}
		
		$sql = "UPDATE session SET session_timestamp = NOW() WHERE s_id = " . $this->s_id;
		$this->db->query($sql);
		
		$sql = "DELETE FROM session_vars WHERE s_id = " . $this->s_id;
		$this->db->query($sql);
		
		$sql = "INSERT INTO session_vars (s_id,var_name,var_val) ";
		
		$val_ary = array();
		foreach(array_keys($this->vars) as $var_name)
		{
			$var_str = sprintf("(%d,'%s','%s')",
				$this->s_id,addslashes($var_name),addslashes(str_replace("\\", "\\\\",serialize($this->vars[$var_name]))));
			array_push($val_ary,$var_str);
		}
		
		if(count($val_ary))
		{ 
			$sql .= "VALUES " . implode(",",$val_ary);
			$this->db->query($sql);
		}
		
		$sql = "UPDATE user_prefs SET view_prefs = '" . 
			addslashes(serialize($this->vars["login_handler"]->user_prefs->view_prefs->vars)) . "' WHERE user_id = '" . 
			$this->vars["login_handler"]->user_id . "'";
		$this->db->query($sql);
		
	}
	
	function flush_dead_sessions()
	{
		$cfg = CerConfiguration::getInstance();
		
		$sql = "SELECT s_id FROM `session` WHERE session_timestamp < DATE_SUB(NOW(), INTERVAL \"". $cfg->settings["session_lifespan"] ."\" MINUTE)";
		$purge_ses_res = $this->db->query($sql);
		if($this->db->num_rows($purge_ses_res))
		{
			$ses_ary = array();
			while($old_ses = $this->db->fetch_row($purge_ses_res))
			{
				array_push($ses_ary,$old_ses["s_id"]);
			}				
			$ses_ids = implode(",",$ses_ary);
			$sql = "DELETE FROM session WHERE s_id IN ($ses_ids)";
			$this->db->query($sql);
			$sql = "DELETE FROM session_vars WHERE s_id IN ($ses_ids)";
			$this->db->query($sql);
		}
		
	}

};

// [JAS]: Our OWN session object needs to populate from the serialized data in the database
@$sid = $_REQUEST["sid"];

// [JAS]: If SID wasn't passed in URL, check to see if we have one in a cookie.
if(empty($sid) && isset($_COOKIE["sid"])) $sid = $_COOKIE["sid"]; 

$session = new CER_SESSION($sid);
$cer_hash = new CER_HASH_CONTAINER();

// If the user isn't trying to log in, and the user isn't logged in already, force login page
if($_SERVER["PHP_SELF"] == $cfg->settings["cerberus_gui_path"] . "/do_login.php")
{
	// Do nothing, let do_login.php do the work
}
else if(!isset($session->vars["login_handler"]->user_id))
{
	meta_redirect(cer_href($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/login.php"));
	exit();
}
else // if user is logged in
{
	if($_SERVER["PHP_SELF"] != $cfg->settings["cerberus_gui_path"] . "/logout.php")
	{
		$tickets = $session->vars["login_handler"]->batch->get_tickets();
		if(DEMO_MODE) $lang_pref = $session->vars["login_handler"]->user_prefs->user_language;
		$session->vars["login_handler"]->do_login($session->vars["login_handler"]->user_login,$session->vars["login_handler"]->user_password);
		$session->vars["login_handler"]->batch->tickets = $tickets;
		
		// [JAS]: If we're in demo mode, compensate for the fact we're not saving
		//		the language in the DB and continue to use this session's pref.
		if(DEMO_MODE) $session->vars["login_handler"]->user_prefs->user_language = $lang_pref;
	}
	
	if($session->vars["login_handler"]->user_logoff == 1 || $session->vars["login_handler"]->user_id == "") {
		meta_redirect(cer_href($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/login.php"));
		exit();
	}
}
?>