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
| File: config_give_feedback.php
|
| Purpose: The configuration include for leaving feedback to the Cerberus
|			development team.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// Verify that the connecting user has access to modify configuration/kbase values
if(!$priv->has_priv(ACL_OPTIONS_FEEDBACK,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_FEEDBACK_SUCCESS . "</span><br>"; ?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="feedback">
<input type="hidden" name="form_submit" value="feedback_send">
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
        <tr class="boxtitle_orange_glass"> 
          <td colspan="2"><?php echo  LANG_CONFIG_FEEDBACK_TITLE ?></td>
        </tr>
        <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
          <td colspan="2" valign="top" align="left"> 
              <table width="98%" border="0" cellspacing="1" cellpadding="2">
                <tr> 
                  <td width="120" class="cer_maintable_heading"><?php echo  LANG_CONFIG_FEEDBACK_SUBJECT ?>:</td>
                  <td> 
                    <input type="text" name="feedback_subject" size="45" maxlength="64">
                  </td>
                </tr>
                <tr> 
                  <td width="120" class="cer_maintable_heading"><?php echo  LANG_CONFIG_FEEDBACK_NAME ?>:</td>
                  <td> 
                    <input type="text" name="feedback_sender" size="45" maxlength="64" value="<?php echo  $session->vars["login_handler"]->user_name; ?>">
                  </td>
                </tr>
                <tr> 
                  <td width="120" class="cer_maintable_heading"><?php echo  LANG_CONFIG_FEEDBACK_EMAIL ?>:</td>
                  <td> 
                    <input type="text" name="feedback_sender_email" size="45" maxlength="64" value="<?php echo  $session->vars["login_handler"]->user_email; ?>">
                  </td>
                </tr>
                <tr> 
                  <td width="120" class="cer_maintable_heading">&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td width="120" class="cer_maintable_heading" valign="top"><?php echo  LANG_CONFIG_FEEDBACK ?>:</td>
                  <td> 
                    <textarea name="feedback_content" cols="55" rows="15"></textarea>
                  </td>
                </tr>
              </table>
          </td>
        </tr>
        <tr bgcolor="#999999" class="cer_maintable_text" align="right"> 
          <td colspan="2" class="cer_maintable_heading" valign="top"> 
            <input type="submit" class="cer_button_face" value="<?php echo  LANG_CONFIG_FEEDBACK_SUBMIT ?>">
          </td>
        </tr>
      </table>
</form>

