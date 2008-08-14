<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
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
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

require_once(FILESYSTEM_PATH . "cerberus-api/login/cer_LoginPluginHandler.class.php");

//if(!$priv->has_priv(ACL_QUEUE_EDIT,BITGROUP_1) && !$priv->has_priv(ACL_QUEUE_DELETE,BITGROUP_1))
//{
//	echo LANG_CERB_ERROR_ACCESS;
//	exit();
//}

$login_mgr = new cer_LoginPluginHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="plugins">
<!--<input type="hidden" name="form_submit" value="queues_delete_confirm">-->

<span class="cer_display_header"><?php echo LANG_CONFIG_PLUGINS_MANAGE; ?></span><br>
<span class="cer_maintable_text"><?php echo LANG_CONFIG_PLUGINS_SETUP; ?></span><br>
<br>

<table width="99%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
  <tr class="boxtitle_orange_glass"> 
    <td width="99%"><?php echo "Support Center Login Plugins"; ?></td>
    <td width="1%" nowrap>&nbsp;
    <!-- [JSJ]: Removing hiding arrow as its really not needed and causes JS error anyway 
<img id="plugins_login_icon" src="includes/images/icon_expand.gif" width="16" height="16" onclick="javascript:togglePluginLogin();" onmouseover="javascript:this.style.cursor='hand';">
--></td>
  </tr>
</table>
  
<table width="99%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
  <?php
  foreach($login_mgr->hash->getPluginsByType("login") as $plugin) {
  ?>
  <tr bgcolor="#EAEAEA">
  	<td>
  		<a href="<?php echo cer_href("configuration.php?module=plugins&pgid=" . $plugin->plugin_id); ?>" class="cer_maintable_heading"><?php echo $plugin->plugin_name; ?></a> 
	</td>
	<td>
		<?php if($plugin->plugin_enabled) { ?>
			<span class="cer_footer_green">Enabled</span>
		<?php } else { ?>
			<span class="cer_footer_red">Disabled</span>
		<?php } ?>
	</td>
  </tr>
  <?php } ?>
</table>
