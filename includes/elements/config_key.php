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
| File: config_key.php
|
| Purpose: The configuration include for managing the product key.  Inserts or
|			updates in the database.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		key values
if(!$priv->has_priv(ACL_OPTIONS_UPLOAD_KEY,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

$sql = "SELECT `product_key`.`key_file` FROM `product_key`;";
$key_result = $cerberus_db->query($sql);
$key_row = $cerberus_db->fetch_row($key_result);	
	
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="key">
<input type="hidden" name="form_submit" value="key_update">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_KEY_WARNING . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_KEY_SUCCESS . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="cer_config_option_background"> 
    <td class="cer_maintable_header"><?php echo  LANG_CONFIG_KEY_TITLE ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
      <div align="left" class="cer_maintable_text">
				<textarea name="product_key" rows="10" cols="45"><?php if(!DEMO_MODE) { echo @$key_row["key_file"]; } ?></textarea>
				</div>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
  <!--
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_CONFIG_KEY_SUBMIT ?>">
-->
		</td>
	</tr>
</table>
<br>
<span class="cer_configuration_updated">
In Cerberus 2.0.0+ the key is no longer managed through the GUI.  Place your product key(s) in the config.xml file.
</span>
