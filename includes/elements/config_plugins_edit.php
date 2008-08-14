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

//if(!$priv->has_priv(ACL_SLA_SCHEDULES,BITGROUP_2))
//{
//	echo LANG_CERB_ERROR_ACCESS;
//	exit();
//}
//

$login_mgr = new cer_LoginPluginHandler();
$plugin_data = $login_mgr->getPluginById($pgid);
$params = array();

require_once(PATH_LOGIN_PLUGINS . $login_mgr->getPluginFile($pgid));
$plugin = $login_mgr->instantiatePlugin($pgid,$params);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pgid" value="<?php echo $pgid; ?>">
<input type="hidden" name="module" value="plugins">
<input type="hidden" name="form_submit" value="plugins_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
  <tr class="boxtitle_orange_glass"> 
   <td>Configure Plugin '<?php echo @htmlspecialchars($plugin_data->plugin_name) ?>'</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap><?php echo LANG_WORD_ENABLED; ?>:</td>
            <td width="90%" bgcolor="#EEEEEE" class="cer_maintable_text">
            	<input type="radio" name="plugin_enabled" value="1" <?php echo (!empty($plugin_data->plugin_enabled) ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_TRUE; ?> 
            	<input type="radio" name="plugin_enabled" value="0" <?php echo (empty($plugin_data->plugin_enabled) ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_FALSE; ?>
            </td>
          </tr>
          
          <tr>
          	<td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" height="1" width="1"></td>
          </tr>
          
          <tr> 
            <td width="10%" class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD" nowrap>Type:</td>
            <td width="90%" class="cer_maintable_text" bgcolor="#EEEEEE">
            	&nbsp;<?php echo $plugin_data->plugin_type; ?>
            </td>
          </tr>
          
          <?php
   			$plugin_settings = $plugin->pluginConfigure();
  			if(!empty($plugin_settings))
  			{
          ?>
          <tr> 
            <td colspan="2" bgcolor="#EEEEEE">
            	
            	<table border="0" cellspacing="1" cellpadding="1" width="100%" bgcolor="#FFFFFF">
            		<tr class="boxtitle_gray_glass_dk">
            			<td colspan="2"><?php echo LANG_CONFIG_PLUGINS_CONFIGURE; ?>:</td>
            		</tr>
            		
            		<tr bgcolor="#999999" class="cer_maintable_header">
            			<td><?php echo LANG_WORD_SETTING; ?></td>
            			<td><?php echo LANG_WORD_VALUE; ?></td>
            		</tr>
            		
            		<?php
        			foreach($plugin_settings as $idx => $setting)
        			{
            		?>
            		
            		<tr>
            			<td class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD">
            				<?php echo $setting->name; ?>:
            			</td>
            			<td bgcolor="#EEEEEE">
            				<input type="text" name="plugin_var_<?php echo $setting->var; ?>" value="<?php echo $plugin->getVar($setting->var); ?>" size="<?php echo $setting->type_opts[0]; ?>"><br>
            				<span class="cer_footer_text"><?php echo $setting->desc; ?></span>
            			</td>
            		</tr>
            		
            		<?php
        			}
            		?>
            		
            	</table>
            	
			</td>
          </tr>
          
          <?php } /* end $plugin_settings check */ ?>
          
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
