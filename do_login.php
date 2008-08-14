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
|
| File: do_login.php
|
| Purpose: This is the intermediate page between login and the system.
|		Handles user authentication and rejection.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com) 		[JAS]
|		Ben Halsted		(ben@webgroupmedia.com)		[BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_SESSION",true); // [JAS]: Leave this true

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/htmlMimeMail.php");
require_once(FILESYSTEM_PATH . "includes/functions/session.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/update_loader.class.php");

// [JAS]: Seek the random number generator
function make_seed() {
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}

// [JAS]: Set up the local variables from the scope objects
@$form_submit = $_REQUEST["form_submit"];
@$pemail = $_REQUEST["pemail"];
@$pemail_verify = $_REQUEST["pemail_verify"];
@$pass = $_REQUEST["pass"];
@$email_address = $_REQUEST["email_address"];
@$email_verify = $_REQUEST["email_verify"];
@$novars = $_REQUEST["novars"];
@$xsp_passport = $_REQUEST["xsp_passport"];
@$q = $_REQUEST["q"];
@$t = $_REQUEST["t"];
@$CER_AUTH_USER = $_REQUEST["CER_AUTH_USER"];
@$CER_AUTH_PASS = $_REQUEST["CER_AUTH_PASS"];

switch ($form_submit) {
	case "xsp":
	{
		$cerberus_db = cer_Database::getInstance();
		
		$xsp = new xsp_login_manager();
		
		if(!$user = $xsp->check_master_login($xsp_passport)) {
			meta_redirect($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/login.php?failed=x");
			exit();
		}
		
		$CER_AUTH_USER = $user->user_login;
		$CER_AUTH_PASS = $user->user_password;
		
		// [JAS]: BREAK is intentionally missing here, we want to hijack the login code
	}
	case "login":
	{
		$script_hash = new CER_DB_SCRIPT_HASH();
		if(!$script_hash->script_has_run(CURRENT_DB_SCRIPT)) {
			$error_msg = '<b>Cerberus [ERROR]:</b> Your database is not up to date. <a href="upgrade.php">Verify you have run all applicable database patch/upgrade scripts</a> for Cerberus Helpdesk ' . GUI_VERSION . '.';
			die($error_msg);
		}
		
		$login_handler = new login_handler_mgr();
		$login_handler->do_login($CER_AUTH_USER,$CER_AUTH_PASS);
		
		if($login_handler->user_id)
		{
			$session = new CER_SESSION();
			$session->vars["login_handler"] = $login_handler;
		
			$login_logger = new cer_LoginLog($login_handler->user_id);
			$login_logger->logLogin();
			unset($login_logger);
			
			// [JAS]: Force save the session since we'll be redirecting and there's no footer on this page
			if(isset($session) && method_exists($session,"save_session"))
			{ $session->save_session(); $session->flush_dead_sessions(); }
			
			echo "<html><head>";
				if(isset($q))
					meta_redirect(cer_href($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/ticket_list.php?queue_view=1&qid=$q"));
				else if(isset($t))
					meta_redirect(cer_href($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/display.php?ticket=$t"));
				else
					meta_redirect(cer_href($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/index.php"));
			echo "</head><body><i>" . LANG_LOGGING_IN ."</i></body></html>";
			exit();
			
		}
		else
		{
			meta_redirect($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/login.php?failed=x");
			exit();
		}
		break;
	}
	case "email":
	{
		$cerberus_db = cer_Database::getInstance();
		
		// pkolmann (2005-10-18): check if email is an Agent
		$sql = sprintf("SELECT user_email FROM user WHERE user_email=%s", $cerberus_db->escape($email_address));
		$check_result = $cerberus_db->query($sql);

		if($cerberus_db->num_rows($check_result) == 0) {
			header("Location: ".cer_href("login.php?novars="));
			break;
		}
		
		// [BGH]: Send new password
		srand(make_seed());
		$randval = substr(dechex(rand()), 0);
		$pass = crypt($randval,substr($randval,1,2));
		
		$sql = sprintf("UPDATE `user` SET `user`.`user_email_verify` = %s WHERE `user`.`user_email`=%s",
			$cerberus_db->escape($pass), $cerberus_db->escape($email_address));
		$cerberus_db->query($sql);
		
		// [JAS]: Ben, the below needs work.  It's simply pulling up any queue to
		//		send the password as.
		$sql = "SELECT `queue_address`,`queue_domain` from `queue_addresses` LIMIT 0, 1";
		$queue_result = $cerberus_db->query($sql);
		
		$mail = new htmlMimeMail();
		$message = LANG_LOGIN_PW_CODE . ": $randval\n";
		$mail->setText(stripcslashes($message));
		$mail->setSubject(stripcslashes(LANG_LOGIN_PW_RESET));
		$address = "HELPDESK";
		
		if($cerberus_db->num_rows($queue_result) > 0) {
			$queue_data = $cerberus_db->fetch_row($queue_result);
			$address = $queue_data["queue_address"] . "@" . $queue_data["queue_domain"];
		}
		
		$mail->setFrom($address);
		$mail->setReturnPath($address);
	    $mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
		$result = $mail->send(array($email_address),$cfg->settings["mail_delivery"]);
		
		// [JAS]: send back to login page
		header("Location: ".cer_href("forgot_pw.php?pemail_verify=x"));
		break;
	}
	case "email_verify":
	{
		$cerberus_db = cer_Database::getInstance();
		$pass = crypt($email_verify,substr($email_verify,1,2));
		$sql = sprintf("SELECT `user_id`, `user_email` from `user` where `user_email_verify`=%s", $cerberus_db->escape($pass));
		$data_result = $cerberus_db->query($sql);
		while($user_data = $cerberus_db->fetch_row($data_result)) {
			$email_address = $user_data["user_email"];
			// [BGH]: Send new password
			srand(make_seed());
			$randval = substr(dechex(rand()), 0);
			$pass = md5($randval);
			$sql = "UPDATE `user` SET `user`.`user_password` = '$pass', `user`.`user_email_verify`='' WHERE `user`.`user_email`='$email_address'";
			$cerberus_db->query($sql);
			
			// [JAS]: Ben, the below needs work.  It's simply pulling up any queue to
			//		send the password as.
			$sql = "SELECT `queue_address`,`queue_domain` from `queue_addresses` LIMIT 0, 1";
			$queue_result = $cerberus_db->query($sql);
			
			$mail = new htmlMimeMail();
			$message = LANG_LOGIN_PW_NEW . ": $randval\n";
			$mail->setText(stripcslashes($message));
			$mail->setSubject(stripcslashes(LANG_LOGIN_PW_RESET));
			$address = "HELPDESK";
			
			if($cerberus_db->num_rows($queue_result) > 0) {
				$queue_data = $cerberus_db->fetch_row($queue_result);
				$address=$queue_data["queue_address"] . "@" . $queue_data["queue_domain"];
			}
			
			$mail->setFrom($address);
			$mail->setReturnPath($address);
		    $mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
			$result = $mail->send(array($email_address),$cfg->settings["mail_delivery"]);
		}
		// [JAS]: Send back to login page
		header("Location: ".cer_href("forgot_pw.php?pemail=x"));
		break;
	}
	default:
	{
		header("Location: ".cer_href("login.php?novars="));
	}
}
?>
