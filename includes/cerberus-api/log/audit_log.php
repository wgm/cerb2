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
| File: audit_log.php
|
| Purpose: Classes to insert, maintain and list ticket audit logs.  Defines
|	constants for audit log actions.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file audit_log.php
\brief Cerberus Audit Log Classes.

Classes to insert, maintain and list ticket audit logs.  Defines
constants for audit log actions.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "includes/functions/languages.php"); // for cer_translate
require_once(FILESYSTEM_PATH . "includes/functions/general.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

define("AUDIT_ACTION_OPENED",1); //! \def AUDIT_ACTION_OPENED a ticket was opened by the parser
define("AUDIT_ACTION_CHANGED_ASSIGN",2);
define("AUDIT_ACTION_CHANGED_STATUS",3);
define("AUDIT_ACTION_REPLIED",4);
define("AUDIT_ACTION_COMMENTED",5);
define("AUDIT_ACTION_CHANGED_QUEUE",6);
define("AUDIT_ACTION_CHANGED_PRIORITY",7);
define("AUDIT_ACTION_REQUESTOR_RESPONSE",8);
define("AUDIT_ACTION_TICKET_REOPENED",9);
define("AUDIT_ACTION_CUSTOM_FIELDS_REQUESTOR",10);
define("AUDIT_ACTION_CUSTOM_FIELDS_TICKET",11);
define("AUDIT_ACTION_TICKET_CLONED_FROM",12);
define("AUDIT_ACTION_RULE_CHOWNER",13);
define("AUDIT_ACTION_RULE_CHSTATUS",14);
define("AUDIT_ACTION_RULE_CHQUEUE",15);
define("AUDIT_ACTION_TICKET_CLONED_TO",16);
define("AUDIT_ACTION_THREAD_FORWARD",17);
define("AUDIT_ACTION_ADD_REQUESTER",18);
define("AUDIT_ACTION_MERGE_TICKET",19);
define("AUDIT_ACTION_RULE_CHPRIORITY",20);
define("AUDIT_ACTION_THREAD_BOUNCE",21);		// [jxdemel] for bounce-feature

//! Cerberus Audit Log Object
/*!
Creates an audit log object to store and read changes made to a ticket.
*/
class CER_AUDIT_LOG
{
	var $db;
	
	//! Constructor
	/*!
	*/
	function CER_AUDIT_LOG()
	{
		$this->db = cer_Database::getInstance();
	}
	
	//! Retrieve an audit log result set for a given \a ticket \a id
	/*!
	\param $ticket_id an \c integer ticket number
	\param $max_rows an \c integer maximum number of rows to return (0 = ALL)
	\return A database \c resultset.
	*/
	function get_log($ticket_id=0,$max_rows=0)
	{
		$sql = "SELECT al.audit_id, al.ticket_id, al.epoch, al.timestamp, al.user_id, al.action, al.action_value, u.user_login as user " .
		"FROM ticket_audit_log al LEFT JOIN user u ON (al.user_id = u.user_id) " .
		"WHERE al.ticket_id = " . $ticket_id . " " .
		"ORDER BY al.timestamp DESC, al.audit_id DESC";
		
		if($max_rows > 0) $sql .= " LIMIT 0," . $max_rows;
		
		$result = $this->db->query($sql);
		return $result;
	}
	
	//! Log a new action performed on a \a ticket \a id
	/*!
	\param $ticket_id an integer ticket ID
	\param $user_id an integer user ID
	\param $action_id an integer audit log action ID
	\param $action_value a string relating to the action (user name, status text, queue name, etc.)
	\return A boolean of success/failure (true/false).
	*/
	function log_action($ticket_id=0,$user_id=0,$action_id,$action_value="")
	{
		$epoch = mktime();
		$action_value = addslashes($action_value);
		$sql = "INSERT INTO ticket_audit_log (ticket_id,epoch,timestamp,user_id,action,action_value) " .
		"VALUES ($ticket_id,$epoch,NOW(),$user_id,$action_id,'$action_value')";
		$result = $this->db->query($sql);
		
		return true;
	}
	
	//! Show a formatted timestamp for a log entry
	/*!
	\param $timestamp a database timestamp
	\return A formatted text string.
	*/
	function show_timestamp($timestamp)
	{
		if($timestamp == 0) return -1;
		$date = new cer_DateTime($timestamp);
		return $date->getUserDate();
	}
	
	//! Print an audit log action template from dynamic data using the language system
	/*!
	\param $action_id an integer action id
	\param $action_value a string action value
	\param $user_name a string user name
	\param $timestamp a database timestamp
	\return A translated text string explaining the audit log action.
	*/
	function print_action($action_id=0,$action_value="",$user_name="",$timestamp="")
	{
		$translate = new cer_translate;
		
		if(empty($user_name)) $user_name = "[deleted]";
		
		switch($action_id)
		{
			case AUDIT_ACTION_OPENED:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_OPENED);
			break;
			case AUDIT_ACTION_CHANGED_ASSIGN:
				if(empty($action_value)) $action_value = LANG_WORD_NOBODY;
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_CHANGED_ASSIGN,$user_name,$action_value);
			break;
			case AUDIT_ACTION_CHANGED_STATUS:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_CHANGED_STATUS,$user_name,$action_value);
			break;
			case AUDIT_ACTION_REPLIED:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_REPLIED,$user_name);
			break;
			case AUDIT_ACTION_COMMENTED:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_COMMENTED,$user_name);
			break;
			case AUDIT_ACTION_CHANGED_QUEUE:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_CHANGED_QUEUE,$user_name,$action_value);
			break;
			case AUDIT_ACTION_CHANGED_PRIORITY:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_CHANGED_PRIORITY,$user_name,$action_value);
			break;
			case AUDIT_ACTION_REQUESTOR_RESPONSE:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_REQUESTOR_RESPONSE);
			break;
			case AUDIT_ACTION_TICKET_REOPENED:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_TICKET_REOPENED);
			break;
			case AUDIT_ACTION_CUSTOM_FIELDS_REQUESTOR:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_CUSTOM_FIELDS_REQUESTOR,$user_name);
			break;
			case AUDIT_ACTION_CUSTOM_FIELDS_TICKET:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_CUSTOM_FIELDS_TICKET,$user_name);
			break;
			case AUDIT_ACTION_TICKET_CLONED_FROM:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_TICKET_CLONED_FROM,$user_name,$action_value);
			break;
			case AUDIT_ACTION_TICKET_CLONED_TO:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_TICKET_CLONED_TO,$user_name,$action_value);
			break;
			case AUDIT_ACTION_RULE_CHOWNER:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_RULE_CHOWNER,$action_value);
			break;
			case AUDIT_ACTION_RULE_CHSTATUS:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_RULE_CHSTATUS,$action_value);
			break;
			case AUDIT_ACTION_RULE_CHQUEUE:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_RULE_CHQUEUE,$action_value);
 			break;
			case AUDIT_ACTION_RULE_CHPRIORITY:
 				return $translate->translate_sprintf(LANG_AUDIT_ACTION_RULE_CHPRIORITY,$action_value);
			break;
			case AUDIT_ACTION_THREAD_FORWARD:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_THREAD_FORWARD,$user_name,$action_value);
			break;
			case AUDIT_ACTION_ADD_REQUESTER:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_ADD_REQUESTER,$user_name,$action_value);
			break;
			case AUDIT_ACTION_MERGE_TICKET:
				return $translate->translate_sprintf(LANG_AUDIT_ACTION_MERGE_TICKET,$user_name,$action_value);
 			break;
 			case AUDIT_ACTION_THREAD_BOUNCE:	// [JXD]: for bounce feature
 				return $translate->translate_sprintf(LANG_AUDIT_ACTION_THREAD_BOUNCE,$user_name,$action_value);
 			break;
			default:
				return "";
			break;
		}
	}
	
};
?>
