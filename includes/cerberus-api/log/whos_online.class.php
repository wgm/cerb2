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
| File: whos_online_class.php
|
| Purpose: Who's Online Functionality for Home Section
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class CER_WHOS_ONLINE_USER
{
	var $user_id = 0;
	var $user_login = null;
	var $user_name = null;
	var $user_action_string = null;
	var $user_ip = null;
	var $user_idle_secs = 0;
	var $user_pm_url = null;
};

class CER_WHOS_ONLINE
{
	var $db;
	var $who_user_count; // for display
	var $who_user_count_string; // for display
	var $who_users;
	
	function CER_WHOS_ONLINE()
	{
		$cfg = CerConfiguration::getInstance();
		global $session; // clean
		global $cerberus_format;
		
		$this->db = cer_Database::getInstance();
		$this->who_users = array();
		$this->who_user_count = 0;
		$this->who_user_count_string = "";
		
		$sql = sprintf("SELECT u.user_id,u.user_login,u.user_name,w.user_what_action,w.user_what_arg1,w.user_ip,u.user_last_login,t.ticket_mask ".
				"FROM (whos_online w, user u) ".
				"LEFT JOIN ticket t ON (w.user_what_action IN (5,6,7) AND t.ticket_id=w.user_what_arg1) ".
				"WHERE u.user_id = w.user_id AND u.user_login != '' ".
				"AND w.user_timestamp BETWEEN DATE_SUB(NOW(),INTERVAL \"%d\" MINUTE) ".
				"AND DATE_ADD(NOW(),INTERVAL \"1\" MINUTE)",
					$cfg->settings["who_max_idle_mins"]);
		$who_res = $this->db->query($sql);
		
		if($this->db->num_rows($who_res))
		{
			$this->who_user_count = $this->db->num_rows($who_res);
			$this->who_user_count_string = $this->who_user_count .= " user" . (($this->who_user_count==1)?"":"s");
			
			while($wr = $this->db->fetch_row($who_res))
			{
			    $epoch_then = $cerberus_format->db_date_to_epoch($wr["user_last_login"]);
			    $epoch_now = mktime();
			    $log_time = $cerberus_format->date_diff_epoch($epoch_now,$epoch_then);
				
			    $who_user = new CER_WHOS_ONLINE_USER();
			    $who_user->user_id = $wr["user_id"];
			    $who_user->user_login = stripslashes($wr["user_login"]);
			    $who_user->user_name = stripslashes($wr["user_name"]);
			    
			    $args = array();
			    $args[] = $wr["user_what_arg1"];
			    $args[] = $wr["ticket_mask"];
			    
			    $who_user->user_action_string = display_who_action_string($wr["user_what_action"],$args);
				$who_user->user_ip = $wr["user_ip"];
				$who_user->user_idle_secs = $cerberus_format->format_seconds($log_time);
				
				if($session->vars["login_handler"]->user_id != $who_user->user_id)
					$who_user->user_pm_url = cer_href("my_cerberus.php?mode=messages&pm_action=send_pm&pm_uid=" . $who_user->user_id,"send_pm");
				
				array_push($this->who_users,$who_user);
			}
		}
		else
		{
			$this->who_user_count = 0;
			$this->who_user_count_string = "0 users";
		}
	}
};

?>
