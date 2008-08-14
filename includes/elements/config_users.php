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
| File: config_users.php
|
| Purpose: The configuration include for configuring and deleting users.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
if(!$priv->has_priv(ACL_USER_CREATE,BITGROUP_1) && !$priv->has_priv(ACL_USER_EDIT,BITGROUP_1) && !$priv->has_priv(ACL_USER_CREATE,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

$sql = "SELECT `user_id`,`user_name` FROM `user` ";
if($session->vars["login_handler"]->user_superuser == 0) $sql .= "WHERE user_superuser = 0 ";
$sql .= " ORDER BY `user_name`";
$result = $cerberus_db->query($sql);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php?module=users" method="post" onsubmit="return confirm('<?php echo  LANG_CONFIG_USER_CONFIRM ?>');">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="users_delete">

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
  <tr class="boxtitle_orange_glass"> 
    <td><?php echo  LANG_CONFIG_USER_TITLE ?></td>
  </tr>
  
	<?php if($priv->has_priv(ACL_USER_CREATE,BITGROUP_1)) { ?>
	<tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
		<td>
			<a href="<?php echo cer_href("configuration.php?module=users&puid=0"); ?>" class="cer_maintable_subjectLink"><?php echo  LANG_CONFIG_USER_CREATE ?></a><br>
		</td>
	</tr>
	<?php } ?>

	<?php
	while($row = $cerberus_db->fetch_row($result))
		{
			echo '<tr bgcolor="#DDDDDD" class="cer_maintable_text">';
    		echo '<td align="left" bgcolor="#DDDDDD" class="cer_maintable_text">';
  				
    		if($priv->has_priv(ACL_USER_DELETE,BITGROUP_1)) { 
  				echo "<input type=\"checkbox\" name=\"uids[]\" value=\"" . $row["user_id"] . "\">&nbsp;";
  			}
			
  			echo "<a href=\"" . cer_href("configuration.php?module=users&puid=" . $row["user_id"]) . "\" class=\"cer_maintable_subjectLink\">" . cer_dbc($row["user_name"]) . "</a><br>";
	    	echo "</td>";
	  		echo "</tr>";
  		}
  		?>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
			<?php if($priv->has_priv(ACL_USER_CREATE,BITGROUP_1)) { ?><input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>"><?php } ?>&nbsp;
		</td>
	</tr>
</table>

</form>
<br>
