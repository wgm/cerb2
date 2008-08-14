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
| File: config_addresses.php
|
| Purpose: The configuration include for banning addresses from the helpdesk
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted  	(ben@webgroupmedia.com)  	[BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/queue values
if(!$priv->has_priv(ACL_EMAIL_BLOCK_SENDERS,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td><?php echo  LANG_CONFIG_ADDRESSES_TITLE ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
      <form action="configuration.php" method="post">
			<input type="hidden" name="module" value="addresses">
			<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
      		<input type="hidden" name="form_submit" value="address_search">
			<div class="cer_maintable_text"><?php echo  LANG_CONFIG_ADDRESSES_SEARCH_EMAIL ?>&nbsp;<input type="text" name="address_search_param">&nbsp;<?php if(!DEMO_MODE) { ?><input type="submit" class="cer_button_face" value="<?php echo  LANG_CONFIG_ADDRESSES_SEARCH ?>"><?php } ?></div>
			</form>
			
      <form action="configuration.php" method="post">
		<input type="hidden" name="module" value="addresses">
   		<input type="hidden" name="form_submit" value="addresses">
		<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
		<input type="hidden" name="address_search_param" value="<?php echo cer_dbc(@$address_search_param); ?>">
			<table border="0" cellspacing="0" cellpadding="0">
			<tr class="cer_maintable_text">
			<td align="center" class="cer_maintable_heading">&nbsp;<?php echo  LANG_CONFIG_ADDRESSES_BANNED ?>&nbsp;</td>
			<td class="cer_maintable_heading">&nbsp;<?php echo  LANG_CONFIG_ADDRESSES_FROM ?>&nbsp;</td>
			</tr>
<?php
if(isset($address_search_param) && !empty($address_search_param)) {
  $all_emails = array();
  $cerberus_db = new cer_Database();
  $cerberus_db->connect();
  $sql = "SELECT `address_id`,`address_address`,`address_banned` FROM `address` WHERE (`address_address` LIKE '%".$address_search_param."%')";
  $address_result = $cerberus_db->query($sql);
  while($address_data = $cerberus_db->fetch_row($address_result)) {
		echo "<tr  class=\"cer_maintable_text\">\n";?>
		<td align="center"><input type="checkbox" name="ban_emails[]" value="<?php echo $address_data["address_id"]; ?>" <?php if($address_data["address_banned"]) { echo " checked"; } ?>></td>
		<?php
		echo "<td>&nbsp;" . $address_data["address_address"] . "&nbsp;</td>\n";
		echo "</tr>\n";	
  		array_push($all_emails,$address_data["address_id"]);
  }          
}
?>
			<tr><td colspan="2">&nbsp;<input type="submit" class="cer_button_face" name="ban" value="<?php echo LANG_BUTTON_SAVE ?>"></td></tr>
			</table>
<?php
	if(count($all_emails)) { 
		echo "<input type=\"hidden\" name=\"all_emails\" value=\"". implode(",",$all_emails) ."\">";
	}
?>
			</form>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
			&nbsp;
		</td>
	</tr>
</table>
<br>
