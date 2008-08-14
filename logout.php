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
|
| File: logout.php
|
| Purpose: Clear the session object and redirect the user to the login page.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");

setcookie ("sid", "", time() - 3600); // [JAS]: Clear the session cookie if we set one

$uid = $session->vars["login_handler"]->user_id;

$login_logger = new cer_LoginLog($uid);
$login_logger->logLogout();
unset($login_logger);

$sql = sprintf("UPDATE whos_online SET user_what_action=0,user_timestamp='0000-00-00' WHERE user_id = %d",$uid);
$cerberus_db->query($sql);

$sql = "DELETE FROM session WHERE s_id = " . $session->s_id;
$cerberus_db->query($sql);

$sql = "DELETE FROM session_vars WHERE s_id = " . $session->s_id;
$cerberus_db->query($sql);

meta_redirect($cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"] . "/index.php");
exit;
?>
