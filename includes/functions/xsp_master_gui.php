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
\file xsp_master_gui.php
\brief Cerberus xSP Master GUI classes to extend sessions + doLogin.

Allow remote "master gui" access to this (satellite) helpdesk.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2003
*/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_handlers.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");

class xsp_login_manager
{
	var $db;
	var $callback_acl=null;
	
	function xsp_login_manager()
	{
		$this->db = cer_Database::getInstance();
	}
	
	function xsp_is_enabled()
	{
		$cfg = CerConfiguration::getInstance();
		if($cfg->settings["satellite_enabled"]) return true; else return false;
	}
	
	function is_xsp_login($p_login)
	{
		if(substr($p_login,0,5)=="xsp_") return true; else return false;
	}
	
	function check_master_login($xsp_passport)
	{
		$cfg = CerConfiguration::getInstance();
		
		if(empty($xsp_passport)) return false;
		
		// check that we can connect to remote urls using fopen
		$ini_allow_url_fopen = ini_get("allow_url_fopen");
		if(empty($ini_allow_url_fopen) || 1!=$ini_allow_url_fopen) {
			return false;
		}
		
		
		
		$hs_gen = mktime() . str_replace(".","",$_SERVER['REMOTE_ADDR']) . rand(1000,9999);
		$handshake = md5($hs_gen);
		
		$url = @$cfg->settings["xsp_url"];
		$xsp_user = @$cfg->settings["xsp_login"];
		$xsp_pass = @$cfg->settings["xsp_password"];
		
		$xml_packet = urlencode("<?xml version=\"1.0\"?>".
		"<xsp_login>".
			"<passport>$xsp_passport</passport>".
			"<handshake>$handshake</handshake>".
		"</xsp_login>"
		);
		
		// instead of cURL
		$url = "$url?xsp_login=$xsp_user&xsp_pass=$xsp_pass&action=login&xml=$xml_packet";
		$file = fopen ($url, "r");
		 if (!$file) {
		    echo "<p>Unable to connect to xSP server.\n";
		    exit();
		 }
		 while (!feof ($file)) {
		    $xml_user .= fgets ($file, 1024);
		 }
		 fclose($file);
		 
		// [JAS]: If no user record was returned.
		if(strlen($xml_user) == 1) return false;
		
		$xsp_login_handler = new CERB_XSP_LOGIN_RESPONSE();
		$xsp_login_handler->xsp_login=$xsp_user;
		$xsp_login_handler->xsp_pass=$xsp_pass;
		$xml_handler = new CERB_XML_XSP_LOGIN_RESPONSE_HANDLER($xsp_login_handler);
		$xml_handler->parser->read_xml_string($xml_user);
		
		// [JAS]: For XSP 1.0, Force XSP user to be supeurser
		// Check for a default core user group
//		$sql = "SELECT g.group_id FROM user_access_levels g WHERE g.is_core_default = 1 LIMIT 0,1";
//		$g_res = $this->db->query($sql);
//		if($this->db->num_rows($g_res))
//		{
//			$gr = $this->db->fetch_row($g_res);
//			$user_group_id = $gr["group_id"];
//			$user_superuser=0;
//		}
//		else
//		{
			$user_group_id = 0;
			$user_superuser=1;
//		}
		
		$sql = sprintf("SELECT user_id FROM user WHERE user_login = '%s'",$xsp_login_handler->user_login);
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res))
		{
			$user_id = $row["user_id"];
			$pass = $xsp_login_handler->user_password;
			$local_hashed = md5($pass);
			//$local_hashed = crypt($pass,substr($pass,1,2));
			
			$sql = sprintf("UPDATE user SET user_name=%s,user_email=%s,user_password=%s,user_group_id=%d,user_superuser=%d,user_xsp=%d WHERE user_id=%d",
					$this->db->escape($xsp_login_handler->user_name . " (XSP)"),
					$this->db->escape($xsp_login_handler->user_email),
					$this->db->escape($local_hashed),
					$user_group_id,
					$user_superuser,
					1,
					$user_id
				);
			$this->db->query($sql);
			
			$sql = sprintf("DELETE FROM queue_access WHERE user_id = %d",$user_id);
			$this->db->query($sql);
		}
		else
		{
			// local user for this core user does not exist
			$pass = $xsp_login_handler->user_password;
			$local_hashed = crypt($pass,substr($pass,1,2));
			$sql = sprintf("INSERT INTO user (user_name,user_email,user_login,user_password,user_group_id,user_superuser,user_xsp) ".
				"VALUES ('%s','%s','%s','%s',%d,%d,%d)",
					$xsp_login_handler->user_name . " (XSP)",
					$xsp_login_handler->user_email,
					$xsp_login_handler->user_login,
					$local_hashed,
					$user_group_id,
					$user_superuser,
					1
				);
			$this->db->query($sql);
			$user_id = $this->db->insert_id();
		}
		
	
		// grant user privleges on all core update queues on this satellite
		// [JAS]: For XSP 1.0, give XSP access to all queues on login
		$sql = sprintf("SELECT q.queue_id FROM queue q");
//		$sql = sprintf("SELECT q.queue_id FROM queue q WHERE q.queue_core_update = %d",1);
		$q_res = $this->db->query($sql);
		if($this->db->num_rows($q_res))
		{
			while($qr = $this->db->fetch_row($q_res))
			{
				$sql = sprintf("REPLACE INTO queue_access(queue_id,user_id,queue_access,queue_watch) ".
				"VALUES (%d,%d,'%s',%d)",$qr["queue_id"],$user_id,"write",0);
				$this->db->query($sql);
			}
		}
		
		return $xsp_login_handler;
	}
	
	function register_callback_acl(&$obj,$func)
	{
		$this->callback_acl = array(&$obj,$func);
	}
	
	function xsp_send_summary($ticket=0)
	{
		$cfg = CerConfiguration::getInstance();
		$queue_name = "";
		$thread_date = "0000-00-00 00:00:00";
		$is_admin = 0;
		
		// check that we can connect to remote urls using fopen
		$ini_allow_url_fopen = ini_get("allow_url_fopen");
		if(empty($ini_allow_url_fopen) || 1!=$ini_allow_url_fopen) {
			return false;
		}
		
		
		$cer_ticket = new CER_PARSER_TICKET();
		$cer_ticket->load_ticket_data($ticket);
		
//		$acl = new cer_admin_list_struct();
		
		$sql = sprintf("SELECT q.queue_name, q.queue_core_update FROM queue q WHERE q.queue_id = %d",
			$cer_ticket->ticket_queue_id
			);
		$q_res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($q_res)) {
			$queue_name = $row["queue_name"];
			$queue_core_update = $row["queue_core_update"];
		}
		
		// [JAS]: Only summary on flagged queues
		if(isset($queue_core_update) && $queue_core_update != 1) return;
		
		$sql = sprintf("select a1.address_address as requester, a2.address_address as updater, th2.thread_type, th2.thread_date ".
			"FROM ticket t ".
			"LEFT JOIN thread th1 ON (th1.thread_id = t.min_thread_id) ".
			"LEFT JOIN thread th2 ON (th2.thread_id = t.max_thread_id) ".
			"LEFT JOIN address a1 ON (a1.address_id = th1.thread_address_id) ".
			"LEFT JOIN address a2 ON (a2.address_id = th2.thread_address_id) ".
			"WHERE t.ticket_id = %d;",
			$cer_ticket->ticket_id			
			);
		$th_res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($th_res))
		{
			$address_requester = $row["requester"];
			$address_updater = $row["updater"];
			$thread_type = $row["thread_type"];
			$thread_date = $row["thread_date"];
			
			if(!empty($this->callback_acl))
			if(call_user_func($this->callback_acl,
					$address_updater
				))
				$is_admin = 1;
		}
		
		list($t_y,$t_m,$t_d,$t_h,$t_i,$t_s) = sscanf($thread_date,"%d-%d-%d %d:%d:%d");
		$tstamp = mktime($t_h,$t_i,$t_s,$t_m,$t_d,$t_y);
		$thread_date = date("YmdHis",$tstamp);
		
		list($t_y,$t_m,$t_d,$t_h,$t_i,$t_s) = sscanf($cer_ticket->ticket_date,"%d-%d-%d %d:%d:%d");
		$tstamp = mktime($t_h,$t_i,$t_s,$t_m,$t_d,$t_y);
		$ticket_date = date("YmdHis",$tstamp);
		
		$url = $cfg->settings["xsp_url"];
		$username = $cfg->settings["xsp_login"];
		$password = $cfg->settings["xsp_password"];
		
		$xml_packet = urlencode(sprintf("<?xml version=\"1.0\"?>
		<tickets_container>
		  <ticket>
		    <id>%d</id>
		    <satellite_id>%d</satellite_id>
		    <subject><![CDATA[
		        %s
		    	]]>
		    </subject>
		    <status>%s</status>
		    <queue_id>%d</queue_id>
		    <queue_name>%s</queue_name>
		    <priority>%d</priority>
		    <requestor>%s</requestor>
		    <thread_type>%s</thread_type>
		    <last_author>%s</last_author>
		    <last_author_is_staff>%d</last_author_is_staff>
		    <create_date>%s</create_date>
		    <last_update>%s</last_update>
		  </ticket>
		</tickets_container>
		",
		$cer_ticket->ticket_id,
		$username,
		addslashes($cer_ticket->ticket_subject),
		$cer_ticket->ticket_status,
		$cer_ticket->ticket_queue_id,
		addslashes($queue_name),
		$cer_ticket->ticket_priority,
		$address_requester,
		$thread_type,
		$address_updater,
		$is_admin,
		$ticket_date,
		$thread_date
		));
		
		// instead of cURL
		$url = "$url?user=$username&password=$password&action=summary&xml=$xml_packet";
		$file = fopen ($url, "r");
		 if ($file) {
		 	while (!feof ($file)) {
			    $ret .= fgets ($file, 1024);
		 	}
		 }
		 fclose($file);		
	}
	
};

?>