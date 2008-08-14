<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

if(!defined('E_STRICT')) {
   define('E_STRICT', 2048);
}

// Remove any PHP Notices as they shouldn't be shown in a production environment
error_reporting(error_reporting() & ~E_NOTICE & ~E_STRICT);

function html_php_error_handler($type, $message, $file=__FILE__, $line=__LINE__) {
   // if we're being told to suppress errors by php.ini or the @ operator, do so.
   if (error_reporting() == 0) return;
   $errortype = array (
         E_ERROR          => "Error",
         E_WARNING        => "Warning",
         E_PARSE          => "Parsing Error",
         E_NOTICE          => "Notice",
         E_CORE_ERROR      => "Core Error",
         E_CORE_WARNING    => "Core Warning",
         E_COMPILE_ERROR  => "Compile Error",
         E_COMPILE_WARNING => "Compile Warning",
         E_USER_ERROR      => "User Error",
         E_USER_WARNING    => "User Warning",
         E_USER_NOTICE    => "User Notice",
         E_STRICT          => "Runtime Notice"
   );
   switch($type) {
      case E_NOTICE: {
         return;
         break;
      }
      case E_STRICT: {
         return;
         break;
      }
      default: {
         printf("<br /><br /><b>PHP ERROR</b> [Error Type: %s]: <b>%s</b> in <b>%s</b> at line <b>%d</b>.", $errortype[$type], $message, $file, $line);
         exit;
         break;
      }
   }
}

set_error_handler("html_php_error_handler");