<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_public_gui.php
|
| Purpose: This config include handles public gui profiles
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/languages.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicGUISettings.class.php");

// Verify that the connecting user has access to modify configuration values
if(!$priv->has_priv(ACL_PUBLIC_GUI,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$pubgui = new cer_PublicGUISettings();

$sql = "SELECT `profile_id`,`profile_name` FROM `public_gui_profiles` ORDER BY `profile_name`";
$result = $cerberus_db->query($sql);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<script>
	function verifyProfileDelete()
	{
		if(confirm("Are you sure you want to permanently delete the selected profiles?"))
			return true;
		
		return false;
	}
</script>

<form action="configuration.php" method="post" onsubmit="javascript:return verifyProfileDelete();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="public_gui_profiles">
<input type="hidden" name="form_submit" value="public_gui_profiles_delete">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
  <tr class="cer_maintable_text"> 
    <td class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
        
		  <tr class="boxtitle_orange_glass"> 
			<td colspan="3" colspan="2">Public GUI Profiles</td>
		  </tr>
		  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="3" align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		<a href="<?php echo cer_href("configuration.php?module=public_gui_profiles&pfid=0"); ?>" class="cer_maintable_subjectLink">Create Public GUI Profile</a><br>
  	</td>
  </tr>
		  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="3" align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		<B>Explanation:</B> Public GUI Profiles allow you to manage multiple Public GUI installations, each suited for a specific purpose or market/group of  
  		clients.  <B>For example:</B> A company has two product brands: one for hosting and one for software. The company wants to provide a distinctly branded 
  		support portal for service on the respective product web sites.  Two public GUI profiles could be created in this section, allowing full customization 
  		of the support experience for each service. <B>Note:</B> Many helpdesks will simply need a single Profile and Public GUI.
  	</td>
  </tr>
  
  <tr class="boxtitle_gray_glass_dk"> 
    <td width="1%" align="left" nowrap>Sel</td>
    <td width="1%" align="center" nowrap>Profile ID</td>
    <td width="98%" align="left">Profile Name</td>
  </tr>
	<?php
	
	while(@$row = $cerberus_db->fetch_row($result))
		{
			echo 
		  '<tr bgcolor="#DDDDDD" class="cer_maintable_text">';
		  
		    echo '<td width="1%" align="left" bgcolor="#DDDDDD" class="cer_maintable_text" nowrap>';
  				echo "<input type=\"checkbox\" name=\"fids[]\" value=\"" . $row["profile_id"] . "\">";
  			echo '</td>';
  			
		    echo '<td width="1%" align="center" bgcolor="#DDDDDD" class="cer_maintable_text" nowrap>';
  				echo $row["profile_id"];
  			echo '</td>';
  			
		    echo '<td width="98%" align="left" bgcolor="#DDDDDD" class="cer_maintable_text">'.
  				'<a href="' . cer_href("configuration.php?module=public_gui_profiles&pfid=" . $row["profile_id"]) . '" class="cer_maintable_subjectLink">' . @htmlspecialchars(stripslashes($row["profile_name"]), ENT_QUOTES, LANG_CHARSET_CODE) . '</a>';
  			echo '</td>';
  			
  			echo '</tr>';
		}
  		?>
  		
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td colspan="3" align="left">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>">
			</td>
		</tr>
		  
        </table>
    </td>
  </tr>
</table>
</form>
<br>
