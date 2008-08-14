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
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/tempdir/cer_Tempdir.class.php");
$cer_tempdir = new cer_Tempdir();

?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance_tempdir">
<input type="hidden" name="module" value="maintenance_tempdir">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit) && isset($purged_files)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": Purged $purged_files temporary files!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td colspan="2">Purge Temporary Files</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td class="cer_maintable_heading" valign="top" align="left"> 
      <div class="cer_maintable_heading"> 
        <table width="98%" border="0" cellspacing="1" cellpadding="2">
          <?php if($cer_tempdir->total_files) { ?>
        	<tr> 
            <td width="21%" class="cer_maintable_heading">Purge Temporary Files:<br>
				<span class="cer_footer_text">Temporary files that are older than 24 hours are generally never used again.  By purging these <?php echo $cer_tempdir->total_files; ?> files you'll recover <?php echo sprintf("%0.0f",$cer_tempdir->total_sizes/1000) . "KB"; ?> of disk space.<br></span>
			</td>
            <td width="79%" valign="top"> 
            	<input type="submit" value="Purge" class="cer_button_face">
            </td>
          </tr>
          <?php } else { ?>
          	<tr>
          		<td class="cer_maintable_text">There are currently no temporary files pending purge.</td>
          	</tr>
          <?php } ?>
        </table>
      </div>
    </td>
  </tr>
</table>
</form>
