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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
	\file cer_notification.class.php
	
	\author Jeff Standen, jeff@webgroupmedia.com
	\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/email_templates/cer_email_templates.class.php");

define("NOTIFY_EVENT_NEW_TICKET",1);
define("NOTIFY_EVENT_ASSIGNMENT",2);
define("NOTIFY_EVENT_CLIENT_REPLY",3);

class CER_NOTIFICATION
{
	var $db;
	var $users = array();
	var $active_user = null;

	function CER_NOTIFICATION($uid=0)
	{
		$this->db = cer_Database::getInstance();
		$this->_load_notify_options($uid);
	}
	
	function _load_notify_options($uid=0)
	{
		$sql = "SELECT n.user_id, n.notify_options FROM user_notification n ";
		if(!empty($uid)) $sql .= "WHERE n.user_id = $uid ";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				$u_id = $row["user_id"];
				$this->users[$u_id] = unserialize($row["notify_options"]);
				
				if(!empty($uid))
					$this->active_user = &$this->users[$u_id];
			}
		}
		
		// [JAS]: If we asked for a specific user entry that doesn't exist, make one.
		if(!empty($uid) && empty($this->users[$uid])) {
			$this->_add_notify_user($uid);
		}
	}
	
	function _add_notify_user($u_id)
	{
		$this->users[$u_id] = new CER_NOTIFICATION_USER($u_id);
		$this->active_user = &$this->users[$u_id];
	}
	
	function trigger_event($event_id,$ticket_id)
	{
		global $session;
		
		$parser = new CER_PARSER();
		$cer_ticket = new CER_PARSER_TICKET();
		$cer_email = new CERB_RAW_EMAIL();
	
		$cer_ticket->load_ticket_data($ticket_id);
		
		switch($event_id)
		{
			case NOTIFY_EVENT_NEW_TICKET:
			{
				foreach($this->users as $idx => $u)
				{
					$emails = @$u->n_new_ticket->queues_send_to[$cer_ticket->ticket_queue_id];
					if(!empty($emails)) {
						$text = $u->n_new_ticket->template;
						$this->_send_notification($parser,$cer_email,$cer_ticket,$emails,$text);
					}
				}
				break;
			}
				
			case NOTIFY_EVENT_ASSIGNMENT:
			{
				// [JAS]: If we have notification options for the given user and they have
				//	an event set for assignment.
				if(isset($this->active_user) 
					&& $this->active_user->n_assignment->enabled)
					{
						if(isset($this->active_user->n_assignment->send_to))
							$a_emails = explode(",",@$this->active_user->n_assignment->send_to);
						else
							$a_emails = array();
						
						// [JAS]: If a session exists for the user doing assigning, make sure
						//		We're not sending them e-mail about their own assignments to
						//		themself.
						if(isset($session)) {
							$current_email = $session->vars["login_handler"]->user_email;
							
							foreach($a_emails as $idx => $email) {
								if(trim($email) == trim($current_email))
									unset($a_emails[$idx]);
							}
						}
						
						$emails = implode(",",$a_emails);
						
						if(!empty($emails))
						{
							$text = $this->active_user->n_assignment->template;
							$this->_send_notification($parser,$cer_email,$cer_ticket,$emails,$text);
						}
					}
				break;
			}
				
			case NOTIFY_EVENT_CLIENT_REPLY:
			{
				// [JAS]: If we have notification options for the given user and they have
				//	an event set for client replies.
				if(isset($this->active_user) 
					&& $this->active_user->n_client_reply->enabled)
					{
						$emails = @$this->active_user->n_client_reply->send_to;
						if(!empty($emails))
						{
							$text = $this->active_user->n_client_reply->template;
							$this->_send_notification($parser,$cer_email,$cer_ticket,$emails,$text);
						}
					}
				break;
			}
		}
	}
	
	function _send_notification(&$parser,&$cer_email,&$cer_ticket,$send_to,$text)
	{
		$email_templates = new CER_EMAIL_TEMPLATES();
		$send_to = explode(",",$send_to);
		
		// [JAS]: Remove any spaces before or after commas from the email list
		foreach($send_to as $idx => $email)
			$send_to[$idx] = trim($email);
			
		$cer_email->body = $email_templates->parse_template_text($text,$cer_ticket->ticket_id);
		
		foreach($send_to as $addy)
			$parser->send_email_to_address($addy,$cer_email,$cer_ticket);
	}
	
};

class CER_NOTIFICATION_USER
{
	var $user_id = null;
	var $n_new_ticket = null;
	var $n_assignment = null;
	var $n_client_reply = null;
	
	function CER_NOTIFICATION_USER($uid)
	{
		$this->user_id = $uid;
		$this->n_new_ticket = new CER_NOTIFICATION_NEW_TICKET();
		$this->n_assignment = new CER_NOTIFICATION_ASSIGNMENT();
		$this->n_client_reply = new CER_NOTIFICATION_CLIENT_REPLY();
	}
}

class CER_NOTIFICATION_NEW_TICKET
{
	var $queues_send_to = null;
	var $template = "============================\r\nNEW TICKET NOTIFICATION\r\n============================\r\nTicket ID: ##ticket_id##\r\nTicket Subject: ##ticket_subject##\r\n";
};

class CER_NOTIFICATION_ASSIGNMENT
{
	var $enabled = 0;
	var $send_to = null;
	var $template = "============================\r\nNEW ASSIGNMENT NOTIFICATION\r\n============================\r\nTicket ID: ##ticket_id##\r\nTicket Subject: ##ticket_subject##\r\n";
};

class CER_NOTIFICATION_CLIENT_REPLY
{
	var $enabled = 0;
	var $send_to = null;
	var $template = "============================\r\nNEW CLIENT REPLY\r\n============================\r\nTicket ID: ##ticket_id##\r\nTicket Subject: ##ticket_subject##\r\n";
};

?>