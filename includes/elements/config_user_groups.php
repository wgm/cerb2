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
| File: config_users_groups.php
|
| Purpose: The configuration include for user privilege groups.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
if(!$priv->has_priv(ACL_GROUPS_CREATE,BITGROUP_2) 
	AND !$priv->has_priv(ACL_GROUPS_EDIT,BITGROUP_2) 
  AND !$priv->has_priv(ACL_GROUPS_DELETE,BITGROUP_2))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

$sql = "SELECT `group_id`,`group_name`,`is_core_default` FROM `user_access_levels` ORDER BY `group_name`";
$result = $cerberus_db->query($sql);
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<script>
	function deleteUserGroup(id) {
		url = "configuration.php?module=groups&sid=<?php echo $session->session_id; ?>&form_submit=group_delete&ugid=" + id;
		if(confirm("<?php echo LANG_CONFIG_GROUPS_DEL_CONFIRM; ?>")) {
			document.location = url;
		}
	}

	function cloneUserGroup(id) {
		url = "configuration.php?module=groups&sid=<?php echo $session->session_id; ?>&form_submit=group_clone&ugid=" + id;
		document.location = url;
	}
</script>

<table width="98%" border="0" cellspacing="1" cellpadding="2">
  
<tr class="cer_config_option_background"> 
    <td class="cer_maintable_header" colspan="3"><?php echo  LANG_CONFIG_GROUPS_TITLE ?></td>
  </tr>
  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text" colspan="3"> 
		<?php if($priv->has_priv(ACL_GROUPS_CREATE,BITGROUP_2)) { ?>
		  	<a href="<?php echo cer_href("configuration.php?module=groups&pgid=0"); ?>" class="cer_maintable_subjectLink"><?php echo  LANG_CONFIG_GROUPS_CREATE ?></a><br>
		<?php } ?>
	</td>
  </tr>	
	
	<?php
	while($row = $cerberus_db->fetch_row($result))
	{
	?>
	  <tr bgcolor="#DDDDDD" class="cer_maintable_text">
	    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text" width="98%"> 
	<?php
		if($priv->has_priv(ACL_GROUPS_EDIT,BITGROUP_2)) { echo "<a href=\"" . cer_href("configuration.php?module=groups&pgid=" . $row["group_id"]) . "\" class=\"cer_maintable_subjectLink\">"; }
		echo stripslashes($row["group_name"]) .
		(($row["is_core_default"]==1)?" (XSP User Default)":"");
		if($priv->has_priv(ACL_GROUPS_EDIT,BITGROUP_2)) { echo "</a>"; }
	?>
		</td>
		<td width="1%" nowrap><a href="javascript:cloneUserGroup(<?php echo $row["group_id"]; ?>);" class="cer_maintable_text">clone</a></td>
		<td width="1%" nowrap><a href="javascript:deleteUserGroup(<?php echo $row["group_id"]; ?>);" class="cer_maintable_text">delete</a></td>
	  </tr>	
	<?php
	}
	?>
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<?php if($priv->has_priv(ACL_GROUPS_DELETE,BITGROUP_2)) { ?>
			<td align="left" colspan="3">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_WORD_DELETE ?>">
			</td>
		<?php } ?>
	</tr>
	
</table>

<br>
