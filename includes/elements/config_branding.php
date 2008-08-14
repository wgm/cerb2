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
| File: config_branding.php
|
| Purpose: The configuration include for branding the GUI, using a company's
|			own logo, etc.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/queue values
if(!$priv->has_priv(ACL_UPLOAD_LOGO,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<script>
sid = "sid=<?php echo $session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

function formatURL(url)
{
  if(show_sid) { url = url + "&" + sid; }
  return(url);
}
</script>
<form action="configuration.php?module=branding" method="post" enctype="multipart/form-data">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="branding">
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_green_glass"> 
    <td><?php echo LANG_CONFIG_BRANDING_TITLE ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
						<table width="98%" border="0" cellspacing="1" cellpadding="2">
          <tr> 
            <td width="10%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_BRANDING_LOGO ?></td>
            <td width="90%"> 
              <input type="file" name="logo_img"><input type="button" class="cer_button_face" value="<?php echo  LANG_CONFIG_BRANDING_DEFAULT ?>" OnClick="javascript:document.location=formatURL('configuration.php?module=branding&reset_default=yes&form_submit=branding');">
              <span class="cer_footer_text"><?php echo  LANG_CONFIG_BRANDING_RULES ?></span></td>
          </tr>
          <tr>
            <td colspan=2><span class="cer_footer_text"><?php echo  LANG_CONFIG_BRANDING_NOTE ?></td>
          </tr>
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT_CHANGES ?>">
		</td>
	</tr>
</table>
</form>
<br>
