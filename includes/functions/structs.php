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
\file structs.php
\brief Common objects + data structures used in Cerberus Helpdesk.

\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/ticket_thread_errors.php");

//! Load page global object handlers
/*!
Check for existence of the following global objects and define an instance
if they do not exist: database object, format object, translate object,
queue access object.

\param void
\return void
*/

$cerberus_format = new cer_formatting_obj;
$cerberus_translate = new cer_translate;
$queue_access = new CER_QUEUE_ACCESS();
$acl = new cer_admin_list_struct;

class cer_admin_list_struct
{
	var $admin_list;
	
	function cer_admin_list_struct()
	{
		global $cerberus_db;
		static $admin_list = array();
		
		if(empty($admin_list))
		{
			$this->admin_list = array();
			$sql = "select user_email from user";
			
			$admin_emails = $cerberus_db->query($sql,false);
			while($row = ($cerberus_db->fetch_row($admin_emails)))
				array_push($admin_list,$row[0]);
				
		}
		
		$this->admin_list = &$admin_list;
	}
	
	//! Display the admin e-mail list in CSV (comma-seperated value) format
	/*!
	This function will display the current \a $admin_list in the format:
	jeff@webgroupmedia.com, ben@webgroupmedia.com, ...
	
	\param void
	\return A comma-seperated value \c string of helpdesk staff users
	*/
	function print_quoted_csv_admin_list()
	{
		return "'" . implode("','",$this->admin_list) . "'";
	}
	
	//! Checks if an e-mail address is a helpdesk staff user
	/*!
	\param $address a \c string e-mail address
	\return boolean (true/false)
	*/
	function is_admin($address)
	{
		$admin_list = implode(" ",$this->admin_list);
		if(!empty($address) && !empty($admin_list))
		{
			if(strpos(strtolower($admin_list),strtolower($address)) !== false)
			{ return true; }
			else
			{ return false; }
		}
	}
};

function display_email($address)
{
	global $priv;
	global $acl;
	global $session;
	
	if(!$priv->has_restriction(ACL_HIDE_REQUESTOR_EMAILS,BITGROUP_1) || $session->vars["login_handler"]->user_superuser) {
		$requestor_address = $address;
	}
	else {
		if(!$acl->is_admin($address))
		{ $requestor_address = LANG_DISPLAY_REQUESTOR; }
		else
		{ $requestor_address = $address; }
	}
	return $requestor_address;
}


// ===============[ E-mail Template Structures
class cer_template_struct
{
	var $template_id;
	var $template_name;
	var $template_description;
	var $template_text;
	var $template_private;
	
	function cer_template_struct()
	{
		$this->template_id = 0;
		$this->template_name = "";
		$this->template_description = "";
		$this->template_text = "";
		$this->template_private = 0;
	}
};

// ===============[ System User Structures
class cer_user_struct
{
	var $user_id = 0;
	var $user_name = null;
	var $user_login = null;
};

// ===============[ E-mail Address Structures
class cer_email_address_struct
{
	var $address_id = 0;
	var $address = null;
	var $address_display = null;
	var $address_banned = 0;
	
	function cer_email_address_struct($a_id=0,$a_address="",$a_address_banned="")
	{
		global $cerberus_db; // [JAS]: Fix

		$this->address_id = $a_id;
		$this->address = $a_address;
		$this->address_display = display_email($a_address);
		
		if(!empty($a_address_banned)) $this->address_banned = $a_address_banned;
		
		if($a_id==0 || empty($a_address_banned))
		{
			$sql = "SELECT a.address_id,a.address_banned FROM address a WHERE a.address_address = '" . $a_address . "'";
			$add_res = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($add_res))
			{
				$addy = $cerberus_db->grab_first_row($add_res);
				$this->address_id = $addy["address_id"];
				$this->address_banned = $addy["address_banned"];
			}
		}
	}
};

// ===============[ Queue Structures
class cer_queue_struct
{
	var $queue_id;
	var $queue_name;
	var $queue_active_tickets; // [JAS]: for system status
	
	function cer_queue_struct($queue_id,$queue_name)
	{
		$this->queue_id = $queue_id;
		$this->queue_name = $queue_name;
		$this->queue_active_tickets = 0;
	}
};



?>