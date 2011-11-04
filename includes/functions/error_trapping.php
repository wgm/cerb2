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

define('DEBUG_MODE', false);

if(!defined('E_STRICT')) {
	define('E_STRICT', 2048);
}

// Remove any PHP Notices as they shouldn't be shown in a production environment
//error_reporting(error_reporting() & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
error_reporting(E_NONE);

function cer_error_handler ($type,
$message,
$file=__FILE__,
$line=__LINE__)
{
	// if we're being told to suppress errors by php.ini or the @ operator, do so.
	if (error_reporting() == 0) return;

	switch($type)
	{
		case E_ERROR:
		{
			echo sprintf("<b>Cerberus</b> [ERROR]: %s in <b>%s</b> at line <b>%d</b>. Aborting.<br>",
			$message,$file,$line);
			error_log("$message, $file, $line", 0);
			exit;
			break;
		}
		case E_NOTICE:
		{
			if(!DEBUG_MODE) return;
			echo sprintf("<b>Cerberus</b> [NOTICE]: %s in <b>%s</b> at line <b>%d</b>.<br>",
			$message,$file,$line);
			break;
		}
		case E_STRICT:
		{
			if(!DEBUG_MODE) return;
			echo sprintf("<b>Cerberus</b> [RUNTIME NOTICE]: %s in <b>%s</b> at line <b>%d</b>.<br>",
			$message,$file,$line);
			break;
		}
		default:
		{
			echo sprintf("<b>Cerberus</b> [Errno: %s]: %s in <b>%s</b> at line <b>%d</b>.<br>",
			$type,$message,$file,$line);
			exit;
			break;
		}
	}
}
?>
