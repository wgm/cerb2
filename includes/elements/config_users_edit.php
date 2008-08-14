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
| File: config_users_edit.php
|
| Purpose: The configuration include for creating and editing user properties.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

// Verify that the connecting user has access to modify configuration/user values
if((!$priv->has_priv(ACL_USER_CREATE,BITGROUP_1) && !$priv->has_priv(ACL_USER_EDIT,BITGROUP_1)) || (!$priv->has_priv(ACL_USER_CREATE,BITGROUP_1)&& $puid==0))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

if(!isset($uid))
	{ echo LANG_CONFIG_USER_EDIT_NOID; exit(); }	

$userdata=0;
	
require_once(FILESYSTEM_PATH . "includes/functions/general.php");
$cerberus_format = new cer_formatting_obj;
if($uid!=0) {	
	$sql = " SELECT `user_name`,`user_email`,`user_login`,`user_password`,`user_group_id`,DATE_FORMAT(`user_last_login`,'%Y-%m-%d %H:%i:%s') as user_last_login,`user_superuser` FROM `user` WHERE `user_id` = $uid";
	$result = $cerberus_db->query($sql);
	$user_data = $cerberus_db->fetch_row($result);

if($user_data["user_superuser"] > 0 && $session->vars["login_handler"]->user_superuser == 0) {
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}
}
?>

<SCRIPT LANGUAGE="JavaScript">
<!-- Begin

function validateUser() {

	if(validatePwd())
	{
		if(document.useredit.user_group_id[document.useredit.user_group_id.selectedIndex].value == 0 && document.useredit.user_superuser.checked != true)
		{
			if(confirm("WARNING: You are creating a user with no group.  Most functionality in the GUI will be disabled for this user.  Are you sure this is what you want to do?"))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	else
	{
		return false;
	}

}

function validatePwd() {
var invalid = " "; // Invalid character is a space
var minLength = 6; // Minimum length
var pw1 = document.useredit.user_password_1.value;
var pw2 = document.useredit.user_password_2.value;
<?php
if($uid!=0) {
?>
// check for a value in both fields.
if (pw1 == '' && pw2 == '') {
return true;
}
<?php
}
else {
?>
// check for a value in both fields.
if (pw1 == '' || pw2 == '') {
alert('<?php echo  LANG_CONFIG_USER_EDIT_PWTWICE ?>');
return false;
}
<?php
}
?>
// check for minimum length
if (document.useredit.user_password_1.length < minLength) {
alert('Your password must be at least ' + minLength + ' characters long. Try again.');
return false;
}
// check for spaces
if (document.useredit.user_password_1.value.indexOf(invalid) > -1) {
alert("<?php echo  LANG_CONFIG_USER_EDIT_NOSPACES ?>");
return false;
}
else {
if (pw1 != pw2) {
alert ("<?php echo  LANG_CONFIG_USER_EDIT_PWTWICE_ERROR ?>");
return false;
}
else {
return true;
      }
   }
}
//  End -->
</script>


<form action="configuration.php" method="post" name="useredit" onsubmit="return validateUser();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="puid" value="<?php echo  $uid ?>">
<input type="hidden" name="module" value="users">
<input type="hidden" name="form_submit" value="users_edit">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(!empty($user_error_msg)) echo "<span class=\"cer_configuration_updated\">" . $user_error_msg . "</span><br>"; ?>
<?php if(isset($form_submit) && empty($user_error_msg)) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_USER_EDIT_SUCCESS . "</span><br>"; ?>

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
<?php
if(0==$uid) {
?>
  <tr class="boxtitle_orange_glass"> 
    <td><?php echo  LANG_CONFIG_USER_EDIT_NEW ?></td>
  </tr>
<?php
}
else {
?>
  <tr class="boxtitle_orange_glass"> 
    <td><?php echo  LANG_CONFIG_USER_EDIT_EDIT ?> '<?php echo cer_dbc($user_data["user_name"]); ?>'</td>
  </tr>
<?php
}
?>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_NAME ?>:</td>
            <td width="81%">
              <input type="text" name="user_name" size="15" maxlength="32" value="<?php echo cer_dbc(@$user_data["user_name"]); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_NAME_IE ?></span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_EMAIL ?>:</td>
            <td width="81%">
              <input type="text" name="user_email" size="30" maxlength="128" value="<?php echo cer_dbc(@$user_data["user_email"]); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_EMAIL_IE ?></span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%"></td>
            <td width="81%">
              <span class="cer_footer_text"><b>NOTE: This address must be a valid e-mail account.  If this user plans to respond to 
              tickets from their e-mail client (i.e. Outlook Express) and NOT the GUI, this address must <i>exactly</i> match the account and reply-to  
              set up in that e-mail client.  Do not use queue addresses here or you will get duplicate tickets or mail loops.</b></span>
            </td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_LOGIN ?>:</td>
            <td width="81%">
              <input type="text" name="user_login" size="20" maxlength="32" value="<?php echo cer_dbc(@$user_data["user_login"]); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_LOGIN_IE ?></span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_PASS ?>:</td>
            <td width="81%">
              <input type="password" name="user_password_1" size="20" maxlength="32" value="">
						</td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_PASS_VER ?>:</td>
            <td width="81%">
              <input type="password" name="user_password_2" size="20" maxlength="32" value="">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_PASS_VER_IE ?></span></td>
          </tr>
          <?php
								$sql = "SELECT group_id, group_name FROM user_access_levels ORDER BY group_name";
								$grp_result = $cerberus_db->query($sql);
								?>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_GROUP ?>:</td>
            <td width="81%">
              <select name="user_group_id">
													<option value="0"><?php echo  LANG_CONFIG_USER_EDIT_NONE ?>
														<?php 
														while($grp_row = $cerberus_db->fetch_row($grp_result)) {
															echo "<option value=\"" . $grp_row["group_id"] . "\"";
															if(@$user_data["user_group_id"] == $grp_row["group_id"]) echo " SELECTED";
															echo ">" . stripslashes($grp_row["group_name"]); 
															}
														?>
												</select>
												<span class="cer_footer_text">&nbsp;<?php echo  LANG_CONFIG_USER_EDIT_GROUP_IE ?></span></td>
          </tr>
          <?php if($session->vars["login_handler"]->user_superuser > 0) { ?>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_SUPERUSER ?>:</td>
            <td width="81%">
              <input type="checkbox" name="user_superuser" size="20" maxlength="32" value="1"<?php if(@$user_data["user_superuser"]==1) {echo " checked";} ?>>
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_USER_EDIT_SUPERUSER_IE ?></span></td>
          </tr>
								<?php }
								else
											{	if($user_data["user_superuser"] == 1) echo "<input type=\"hidden\" name=\"user_superuser\" value=\"" . $user_data["user_superuser"] . "\">";	}
							 ?>
          <?php
if(0!=$uid) {
?>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_USER_EDIT_LASTLOGIN ?>:</td>
            <td width="81%"><span class="cer_footer_text"><?php $date = new cer_DateTime($user_data["user_last_login"]); echo $date->getUserDate(); ?></span></td>
          </tr>
<?php
	}
?>
          
		<tr class="boxtitle_green_glass">
			<td colspan="2"><?php echo LANG_CONFIG_USER_EDIT_QUEUES ?>:</td>
		</tr>
					
		<tr bgcolor="#DDDDDD"> 
            <td colspan="2">
            <span class="cer_maintable_text">You can manage queue access for this user at the <B>group-level</B> or <B>user-level</B>.  Group-level access simplifies managing queue permissions for large numbers of users and queues, 
             as updating the queue permissions on a group will automatically update all users associated with that group (e.g., support staff, 
             managers). User-level access overrides the group's queue access for this user only, to allow for special cases.</span><br>
             <br>
				<table cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
					<tr bgcolor="#999999">
						<td class="cer_maintable_header">Queue</td>
						<td align="center" bgcolor="#555555"><span class="cer_maintable_header">Group Access (Default)</span></td>
						<td align="center" bgcolor="#555555"><span class="cer_maintable_header">User Access (Override)</span></td>
						<td class="cer_maintable_header">Watcher</td>
					</tr>
					
<?php					
		$u_qids = $queue_access->get_readable_qid_list();
		
		$sql = "SELECT q.queue_id, q.queue_name FROM queue q ".
			"WHERE q.queue_id IN ($u_qids) ORDER BY q.queue_name ASC";
        $result = $cerberus_db->query($sql);
          
          while($queue_data = $cerberus_db->fetch_row($result)) {
					  $sql = "SELECT `queue_access`,`queue_watch` FROM `queue_access` WHERE `queue_id` = " . $queue_data["queue_id"] . " AND `user_id` = $uid";
  		      			$res = $cerberus_db->query($sql);
						$queue_access = $cerberus_db->fetch_row($res);
						$access = $queue_access["queue_access"];
						$watcher = $queue_access["queue_watch"];
						$qid = $queue_data["queue_id"];
						?>					
          <tr> 
            <td nowrap class="cer_maintable_heading" bgcolor="#CCCCCC"><?php echo stripslashes($queue_data["queue_name"]); ?>:</td>
            <td nowrap class="cer_maintable_text" bgcolor="#DDDDDD">
  				<input type="radio" name="qaccess_<?php echo $qid; ?>" value=""<?php if($access=="") {echo " checked";} ?>>use group access&nbsp;
  			</td>
            <td class="cer_maintable_text" bgcolor="#DDDDDD">						
  				<input type="radio" name="qaccess_<?php echo $qid; ?>" value="read"<?php if($access=="read") {echo " checked";} ?>><?php echo   LANG_WORD_READ ?>
				<input type="radio" name="qaccess_<?php echo $qid; ?>" value="write"<?php if($access=="write") {echo " checked";} ?>><?php echo  LANG_WORD_WRITE ?>
				<input type="radio" name="qaccess_<?php echo $qid; ?>" value="none"<?php if($access=="none") {echo " checked";} ?>><?php echo  LANG_WORD_NONE ?>
				<input type="hidden" name="qlist[]" value="<?php echo $qid; ?>">
			</td>
            <td nowrap class="cer_maintable_text" bgcolor="#DDDDDD">
				<input type="checkbox" name="qwatch[]" value="<?php echo $qid; ?>"<?php if($watcher=="1") {echo " checked";} ?>><?php echo  LANG_WORD_WATCHER ?>&nbsp;
			</td>
          </tr>
					<?php
					}
					?>
					
				</table>
		        <br>
            </td>
          </tr>
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
