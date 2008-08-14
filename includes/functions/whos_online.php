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
	\file whos_online.php
	\brief Cerberus Who's Online Module.

	Functions to manage the "Who's Online" list.
  
	\author Jeff Standen, jeff@webgroupmedia.com
	\date 2002-2003
*/

define("WHO_AUTH",1);
define("WHO_ON_HOME",2);
define("WHO_IN_QUEUE",3);
define("WHO_IN_RESULTS",4);
define("WHO_DISPLAY_TICKET",5);
define("WHO_REPLY_TICKET",6);
define("WHO_COMMENT_TICKET",7);
define("WHO_CONFIG",8);
define("WHO_CREATE",9);
define("WHO_KNOWLEDGEBASE",10);
define("WHO_PREFS",11);
define("WHO_CONFIG_VIEWS",12);
define("WHO_MYCERB",13);
define("WHO_MYCERB_PM",14);
define("WHO_MYCERB_TASKS",15);

function log_user_who_action($action_id=0,$arg1="")
{
	global $cerberus_db;
	global $session;
  	global $_SERVER;
  
  	$cerberus_db = cer_Database::getInstance();
  
 	$sql = sprintf("UPDATE whos_online SET user_timestamp=NOW(), user_what_action=%d,user_ip = '%s',user_what_arg1=%s ".
  		"WHERE user_id = %d",
  			$action_id,@$_SERVER["REMOTE_ADDR"],$cerberus_db->escape($arg1),$session->vars["login_handler"]->user_id); 
  	$cerberus_db->query($sql);  	
}

function display_who_action_string($action_id=0,$args=array())
{
	switch($action_id)
  {
    case WHO_AUTH:
    {
  		return sprintf(LANG_ONLINE_AUTH);
    	break;
    }
    case WHO_ON_HOME:
    {
  		return sprintf(LANG_ONLINE_MAIN_SCREEN);
    	break;
    }
    case WHO_IN_QUEUE:
    {
  		return sprintf(LANG_ONLINE_LISTING_TICKETS,$args[0]);
    	break;
    }
    case WHO_IN_RESULTS:
    {
  		return sprintf(LANG_ONLINE_LISTING_RESULTS);
    	break;
    }
    case WHO_DISPLAY_TICKET:
    {
    	$ticket_mask = (($args[1]) ? $args[1] : $args[0]);
  		return sprintf(LANG_ONLINE_DISPLAY_TICKET,cer_href(sprintf("display.php?ticket=%d", $args[0])), $ticket_mask);
    	break;
    }
    case WHO_REPLY_TICKET:
    {
    	$ticket_mask = (($args[1]) ? $args[1] : $args[0]);
  		return sprintf(LANG_ONLINE_REPLY_TICKET,cer_href(sprintf("display.php?ticket=%d", $args[0])), $ticket_mask);
    	break;
    }
    case WHO_COMMENT_TICKET:
    {
    	$ticket_mask = (($args[1]) ? $args[1] : $args[0]);
  		return sprintf(LANG_ONLINE_COMMENT_TICKET,cer_href(sprintf("display.php?ticket=%d", $arg[0])), $ticket_mask);
    	break;
    }
    case WHO_CONFIG:
    {
  		return sprintf(LANG_ONLINE_CONFIGURATION);
    	break;
    }
    case WHO_CREATE:
    {
  		return sprintf(LANG_ONLINE_CREATE_TICKET);
    	break;
    }
    case WHO_KNOWLEDGEBASE:
    {
  		return sprintf(LANG_ONLINE_BROWSE_KB);
    	break;
    }
    case WHO_PREFS:
    {
  		return sprintf(LANG_ONLINE_PREF_EDIT);
    	break;
    }
    case WHO_CONFIG_VIEWS:
    {
  		return sprintf(LANG_ONLINE_CONFIG_TICKET_VIEWS);
    	break;
    }
    case WHO_MYCERB:
    {
  		return sprintf(LANG_ONLINE_MY_CERBERUS);
    	break;
    }
    case WHO_MYCERB_TASKS:
    {
  		return sprintf(LANG_ONLINE_TASKS);
    	break;
    }
    case WHO_MYCERB_PM:
    {
  		return sprintf(LANG_ONLINE_PM);
    	break;
    }
    default:
    {
	    break;
    }
    
  }
}

?>