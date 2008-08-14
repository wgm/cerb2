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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/html_template_parser.class.php");

class chat_js
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function chat_js() {
      $this->db =& database_loader::get_instance();
   }
   
   function get_javascript($GUID) {
      if(empty($GUID)) {
         // [JAS]: Make a globally unique id in case we need to set a cookie.
         $GUID = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . time());
         if(!headers_sent()) {
            setcookie("chatVisitor", $GUID);
         }
      }
      
      $html =& html_output::get_instance();
      $template_parser = new html_template_parser();
      $template_parser->assign("chat_server_url", CHAT_SERVER_URL);
      $template_parser->assign("guid", $GUID);
      $javascript = $template_parser->parse("chat.js");
      $html->set_content($javascript);
      return TRUE;
   }
}