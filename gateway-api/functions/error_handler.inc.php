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

function xml_php_error_handler($type, $message, $file=__FILE__, $line=__LINE__) {
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
         print('<?xml version="1.0" encoding="UTF-8" ?>');
         if(ob_get_level() == 0) {
            $backtrace = var_export(debug_backtrace(), true);
         }
         else {
            $backtrace = "PHP backtrace not available do to output buffering";
         }
         $error_msg = sprintf("\n\n\nPHP ERROR [Error Type: %s]: %s in %s at line %d.\n\n\nInput given to me:\n\n%s\n\n\nBacktrace:\n\n%s\n\n\n", $errortype[$type], $message, $file, $line, get_var("xml"), $backtrace);
         printf("<cerberus_xml><status>error</status><data><error_msg><![CDATA[%s]]></error_msg><error_code>99</error_code></data></cerberus_xml>", $error_msg);
         exit;
         break;
      }
   }
}

set_error_handler("xml_php_error_handler");