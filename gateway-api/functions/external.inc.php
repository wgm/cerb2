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
 * Pulls in a variable from the query
 *
 * @param string $varname The variable from the query to pull
 * @param bool $fail whether to fail or not if the variable isn't found
 * @param string $default optional default value if var isn't set.
 * @return string value of given variable
 */
function get_var($varname = '', $fail = FALSE, $default = '') {
   if($fail && !isset($_REQUEST[$varname])) {
      print("Error: Variable wasn't passed and was required (\$" . $varname . ")");
      exit();
   }
   if(get_magic_quotes_gpc()) {
      return stripslashes(isset($_REQUEST[$varname]) ? $_REQUEST[$varname] : $default);
   }
   else {
      return isset($_REQUEST[$varname]) ? $_REQUEST[$varname] : $default;
   }
}