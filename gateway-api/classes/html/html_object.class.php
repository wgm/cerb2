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

/**
 * Generic class container for HTML data
 *
 */
class html_object
{
   var $content;
   var $output;
   
   function html_object($html = '') {
      $this->content = $html;
   }
   
   function set_content($html) {
      $this->content = $html;
   }
   
   function append_content($html) {
      $this->content .= $html;
   }
   
   function prepend_content($html) {
      $this->content = $html . $this->content;
   }
   
   function to_string() {
      return $this->output;
   }
   
   function finalize_output() {
      $this->output = $this->content;
   }
}