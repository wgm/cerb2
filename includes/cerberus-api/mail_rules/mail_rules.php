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
| File: mail_rules.php
|
| Purpose: Parser mail rules functionality.  These rules are run
|	when mail is received and trigger the appropriate actions.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

define("RULE_TYPE_PRE",1);
define("RULE_TYPE_POST",0);
define("RULE_TYPE_ANY",2);

define("RULE_OPER_EQUAL",1);
define("RULE_OPER_NOT_EQUAL",2);
define("RULE_OPER_CONTAINS",3);
define("RULE_OPER_NOT_CONTAINS",4);
define("RULE_OPER_REGEXP",5);
define("RULE_OPER_LTE",6);
define("RULE_OPER_GTE",7);

define("RULE_FIELD_SENDER",1);
define("RULE_FIELD_SUBJECT",2);
define("RULE_FIELD_QUEUE",3);
define("RULE_FIELD_NEW_TICKET",4);
define("RULE_FIELD_REOPENED_TICKET",5);
define("RULE_FIELD_ATTACHMENT_NAME",6);
define("RULE_FIELD_SPAM_PROBABILITY",7);

define("RULE_ACTION_CHANGE_OWNER",1);
define("RULE_ACTION_CHANGE_QUEUE",2);
define("RULE_ACTION_CHANGE_STATUS",3);
define("RULE_ACTION_CHANGE_PRIORITY",5);
define("RULE_ACTION_STOP_PROCESSING",4);
define("RULE_ACTION_PRE_REDIRECT",6);
define("RULE_ACTION_PRE_BOUNCE",7);
define("RULE_ACTION_PRE_IGNORE",8);
define("RULE_ACTION_PRE_NO_AUTOREPLY",9);
define("RULE_ACTION_PRE_NO_NOTIFICATION",10);

function rule_proc_eq($arg1,$arg2)
{
	if(@strcasecmp($arg1,$arg2) == 0)
		return true;
	else
		return false;
}

function rule_proc_neq($arg1,$arg2)
{
	if(@strcasecmp($arg1,$arg2) != 0)
		return true;
	else
		return false;
}

function rule_proc_contains($arg1,$arg2)
{
	if(@stristr($arg1,$arg2) !== false)
		return true;
	else
		return false;
}

function rule_proc_not_contains($arg1,$arg2)
{
	if(@stristr($arg1,$arg2) === false)
		return true;
	else
		return false;
}

// [JSJ]: Added preg matching for rules
function rule_proc_regexp($arg1,$arg2)
{
	if(@preg_match($arg2,$arg1) != 0)
		return true;
	else
		return false;
}

function rule_proc_gte($arg1,$arg2)
{
	if($arg1 >= $arg2)
		return true;
	else
		return false;
}

function rule_proc_lte($arg1,$arg2)
{
	if($arg1 <= $arg2)
		return true;
	else
		return false;
}

function action_proc_change_owner($proc_args)
{
	$cerberus_db = cer_Database::getInstance();
	
	$sql = sprintf("UPDATE ticket SET ticket_assigned_to_id = %d WHERE ticket_id = %d",
		$proc_args["action_value"],$proc_args["ticket_obj"]->ticket_id);
	$cerberus_db->query($sql);
	
	// [JAS]: Trigger the Assignment Notification
	$notification = new CER_NOTIFICATION($proc_args["action_value"]);
	$notification->trigger_event(NOTIFY_EVENT_ASSIGNMENT,$proc_args["ticket_obj"]->ticket_id);
	
	$sql = "SELECT user_name FROM user WHERE user_id = " . $proc_args["action_value"];
	$u_res = $cerberus_db->query($sql,false);
	$user_name = "";
	if($cerberus_db->num_rows($u_res)) {
		$u_row = $cerberus_db->fetch_row($u_res);
		$user_name = $u_row[0];
	}
	else if($proc_args["action_value"]==0) { $user_name = "Nobody"; }
	
	if(strlen($user_name))
		$proc_args["audit_log"]->log_action($proc_args["ticket_obj"]->ticket_id,0,AUDIT_ACTION_RULE_CHOWNER,$user_name);
	
	return true;
}

function action_proc_change_status($proc_args)
{
	$cerberus_db = cer_Database::getInstance();
	
	$sql = sprintf("UPDATE ticket SET ticket_status = '%s' WHERE ticket_id = %d",
		$proc_args["action_value"],$proc_args["ticket_obj"]->ticket_id);
	$cerberus_db->query($sql);
	
	$proc_args["audit_log"]->log_action($proc_args["ticket_obj"]->ticket_id,0,AUDIT_ACTION_RULE_CHSTATUS,$proc_args["action_value"]);
	
	return true;
}

function action_proc_change_queue($proc_args)
{
	$cerberus_db = cer_Database::getInstance();
	
	$sql = sprintf("UPDATE ticket SET ticket_queue_id = %d WHERE ticket_id = %d",
		$proc_args["action_value"],$proc_args["ticket_obj"]->ticket_id);
	$cerberus_db->query($sql);
	
	$sql = "SELECT queue_name FROM queue WHERE queue_id = " . $proc_args["action_value"];
	$q_res = $cerberus_db->query($sql,false);
	if($cerberus_db->num_rows($q_res)) {
		$q_row = $cerberus_db->fetch_row($q_res);
		$proc_args["audit_log"]->log_action($proc_args["ticket_obj"]->ticket_id,0,AUDIT_ACTION_RULE_CHQUEUE,$q_row[0]);
	}
	
	return true;
}

function action_proc_change_priority($proc_args)
{
	$cerberus_db = cer_Database::getInstance();
	
	$sql = sprintf("UPDATE ticket SET ticket_priority = '%s' WHERE ticket_id = %d",
		$proc_args["action_value"],$proc_args["ticket_obj"]->ticket_id);
	$cerberus_db->query($sql);
	
	$proc_args["audit_log"]->log_action($proc_args["ticket_obj"]->ticket_id,0,AUDIT_ACTION_RULE_CHPRIORITY,$proc_args["action_value"]);
	
	return true;
}

function action_proc_stop_processing($proc_args)
{
	return true;
}

function action_proc_pre_redirect($proc_args)
{
	$cfg = CerConfiguration::getInstance();
	
	$redirect_to = stripslashes($proc_args["action_value"]);
	
	$from = $proc_args["email"]->headers->from;
	$to = $redirect_to;
	$subject = $proc_args["email"]->headers->subject;
	$body = $proc_args["email"]->body;
	
	if(empty($to) || empty($from))
		return false;
	
	$mail = new htmlMimeMail();
	$mail->setText(stripcslashes($body));
	$mail->setFrom($from);
	$mail->setReturnPath($from);
	$mail->setSubject(stripcslashes($subject));
	$mail->setHeader("Reply-To", $from);
	$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");
	
	$result = @$mail->send(array($to),$cfg->settings["mail_delivery"]);

	return true;
}

function action_proc_pre_bounce($proc_args)
{
	$cfg = CerConfiguration::getInstance();
	
	$bounce_msg = stripslashes($proc_args["action_value"]);
	
	$from = $proc_args["email"]->headers->to[0];
	$to = $proc_args["email"]->headers->from;
	$subject = $proc_args["email"]->headers->subject;
	
	if(empty($to) || empty($from))
		return false;
	
	$mail = new htmlMimeMail();
	$mail->setText(stripcslashes($bounce_msg));
	$mail->setFrom($from);
	$mail->setReturnPath($from);
	$mail->setSubject(stripcslashes("Re: " . $subject));
	$mail->setHeader("Reply-To", $from);
	$mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");
	
	$result = @$mail->send(array($to),$cfg->settings["mail_delivery"]);

	return true;
}

function action_proc_pre_ignore($proc_args)
{
	return true;
}

function action_proc_pre_no_autoreply($proc_args)
{
	return true;
}

function action_proc_pre_no_notification($proc_args)
{
	return true;
}

class CER_MAIL_RULE_HANDLER
{
	var $mail_rules = array();
	var $pre_rules = array();
	var $post_rules = array();
	var $db;
	
	function CER_MAIL_RULE_HANDLER($rid=0)
	{
		$this->db = cer_Database::getInstance();
		
		if($rid)
			$this->_load_single_mail_rule($rid);
		else
			$this->_load_all_mail_rules();
	}

	function _load_single_mail_rule($rid)
	{
		$new_rule = new CER_MAIL_RULE_STRUCT();
		
		$sql = "SELECT rule_id,rule_name,rule_pre_parse FROM rule_entry WHERE rule_id = " . $rid;
		$result = $this->db->query($sql);
		$rule_data = $this->db->fetch_row($result);
		
		$new_rule->rule_id = $rule_data["rule_id"];
		$new_rule->rule_name = $rule_data["rule_name"];
		$new_rule->rule_pre_parse = $rule_data["rule_pre_parse"];
		
		if($new_rule->rule_pre_parse) {
			$new_rule->rule_title = "Pre-Parse Mail Rule";
		} else {
			$new_rule->rule_title = "Post-Parse Mail Rule";
		}
		
		$sql = "SELECT `fov_field`,`fov_oper`,`fov_value` FROM `rule_fov` WHERE `rule_id` = " . $new_rule->rule_id;
		$fov_data = $this->db->query($sql);
		
		if($this->db->num_rows($fov_data)) {
			while($fr = $this->db->fetch_row($fov_data))
				$new_rule->add_fov($fr["fov_field"],$fr["fov_oper"],$fr["fov_value"]);
		}
		
		$sql = "SELECT `action_type`,`action_value` FROM `rule_action` WHERE `rule_id` = " . $new_rule->rule_id;
		$action_data = $this->db->query($sql);
		
		if($this->db->num_rows($action_data)) {
			while($fr = $this->db->fetch_row($action_data))
				$new_rule->add_action($fr["action_type"],$fr["action_value"]);
		}

		array_push($this->mail_rules,$new_rule);
		
		// [JAS]: Sort our rules into pre and post rule pointer arrays
		$rule_pos = count($this->mail_rules)-1;
		
		if($rule_pos >= 0) {
			$last_rule = &$this->mail_rules[$rule_pos];
			
			if($last_rule->rule_pre_parse) {
				$this->pre_rules[] = &$this->mail_rules[$rule_pos];
			} else {
				$this->post_rules[] = &$this->mail_rules[$rule_pos];
			}
		}
		
	}
	
	function _load_all_mail_rules()
	{
			
		$sql = "SELECT rule_id,rule_name,rule_pre_parse FROM rule_entry ORDER BY rule_order";
		$result = $this->db->query($sql);
		
		while($rule_data = $this->db->fetch_row($result))
		{
			$new_rule = new CER_MAIL_RULE_STRUCT();

			$new_rule->rule_id = $rule_data["rule_id"];
			$new_rule->rule_name = $rule_data["rule_name"];
			$new_rule->rule_pre_parse = $rule_data["rule_pre_parse"];

			if($new_rule->rule_pre_parse) {
				$new_rule->rule_title = "Pre-Parse Mail Rule";
			} else {
				$new_rule->rule_title = "Post-Parse Mail Rule";
			}
			
			$sql = "SELECT `fov_field`,`fov_oper`,`fov_value` FROM `rule_fov` WHERE `rule_id` = " . $new_rule->rule_id;
			$fov_data = $this->db->query($sql);
			
			if($this->db->num_rows($fov_data)) {
				while($fr = $this->db->fetch_row($fov_data))
					$new_rule->add_fov($fr["fov_field"],$fr["fov_oper"],$fr["fov_value"]);
			}
			
			$sql = "SELECT `action_type`,`action_value` FROM `rule_action` WHERE `rule_id` = " . $new_rule->rule_id;
			$action_data = $this->db->query($sql);
			
			if($this->db->num_rows($action_data)) {
				while($fr = $this->db->fetch_row($action_data))
					$new_rule->add_action($fr["action_type"],$fr["action_value"]);
			}
	
			array_push($this->mail_rules,$new_rule);
			
			// [JAS]: Sort our rules into pre and post rule pointer arrays
			$rule_pos = count($this->mail_rules)-1;
			
			if($rule_pos >= 0) {
				$last_rule = &$this->mail_rules[$rule_pos];
				
				if($last_rule->rule_pre_parse) {
					$this->pre_rules[] = &$this->mail_rules[$rule_pos];
				} else {
					$this->post_rules[] = &$this->mail_rules[$rule_pos];
				}
			}
		}
	}
};

class CER_MAIL_RULE_STRUCT
{
	var $rule_id;
	var $rule_name;
	var $rule_title;
	var $rule_pre_parse;
	var $fovs;
	var $actions;
		
	function CER_MAIL_RULE_STRUCT()
	{
		$this->rule_id = 0;
		$this->rule_name = "";
		$this->rule_title = "Parser Mail Rule";
		$this->rule_pre_parse = 0;
		$this->fovs = array();
		$this->actions = array();
	}
	
	function add_fov($ff="",$fo="",$fv="")
	{
		$fov = new CER_MAIL_FOV_STRUCT($ff,$fo,$fv);
		array_push($this->fovs,$fov);
	}
	
	function add_action($at="",$av="")
	{
		$action = new CER_MAIL_ACTION_STRUCT($at,$av);
		array_push($this->actions,$action);
	}
	
	function is_enabled_fov($fid)
	{
		foreach($this->fovs as $f)
		{
			if($f->fov_field == $fid)
			{ return $f; }
		}
		return false;
	}
	
	function is_enabled_action($aid)
	{
		foreach($this->actions as $a)
		{
			if($a->action_type == $aid)
			{	return $a; }
		}
		return false;
	}
};

class CER_MAIL_FOV_STRUCT
{
	var $fov_field;
	var $fov_oper;
	var $fov_proc;
	var $fov_value;
	
	function CER_MAIL_FOV_STRUCT($ff="",$fo="",$fv="")
	{
		$this->fov_field = $ff;
		$this->fov_oper = $fo;
		$this->fov_value = $fv;
		
		switch($fo)
		{
			case RULE_OPER_EQUAL:
				$this->fov_proc = "rule_proc_eq";
				break;
			case RULE_OPER_NOT_EQUAL:
				$this->fov_proc = "rule_proc_neq";
				break;
			case RULE_OPER_CONTAINS:
				$this->fov_proc = "rule_proc_contains";
				break;
			case RULE_OPER_NOT_CONTAINS:
				$this->fov_proc = "rule_proc_not_contains";
				break;
 			case RULE_OPER_REGEXP:
 				$this->fov_proc = "rule_proc_regexp";
	 			break;
 			case RULE_OPER_LTE:
 				$this->fov_proc = "rule_proc_lte";
	 			break;
 			case RULE_OPER_GTE:
 				$this->fov_proc = "rule_proc_gte";
	 			break;
		}
	}
	
	function execute_proc($arg1,$arg2)
	{
		return call_user_func($this->fov_proc,$arg1,$arg2);
	}
	
};

class CER_MAIL_ACTION_STRUCT
{
	var $action_type;
	var $action_value;
	var $action_proc;
	
	function CER_MAIL_ACTION_STRUCT($at="",$av="")
	{
		$this->action_type=$at;
		$this->action_value=$av;
		
		switch($at)
		{
			case RULE_ACTION_CHANGE_OWNER:
				$this->action_proc = "action_proc_change_owner";
			break;
			
			case RULE_ACTION_CHANGE_QUEUE:
				$this->action_proc = "action_proc_change_queue";
			break;
			
			case RULE_ACTION_CHANGE_STATUS:
				$this->action_proc = "action_proc_change_status";
			break;
			
			case RULE_ACTION_CHANGE_PRIORITY:
				$this->action_proc = "action_proc_change_priority";
			break;
			
			case RULE_ACTION_STOP_PROCESSING:
				$this->action_proc = "action_proc_stop_processing";
			break;
			
			case RULE_ACTION_PRE_REDIRECT:
				$this->action_proc = "action_proc_pre_redirect";
			break;
			
			case RULE_ACTION_PRE_BOUNCE:
				$this->action_proc = "action_proc_pre_bounce";
			break;
			
			case RULE_ACTION_PRE_NO_AUTOREPLY:
				$this->action_proc = "action_proc_pre_no_autoreply";
			break;
			
			case RULE_ACTION_PRE_NO_NOTIFICATION:
				$this->action_proc = "action_proc_pre_no_notification";
			break;
			
			case RULE_ACTION_PRE_IGNORE:
				$this->action_proc = "action_proc_pre_ignore";
			break;
		}
	}
	
	function execute_proc(&$args)
	{
		return call_user_func($this->action_proc,$args);
	}
};

?>