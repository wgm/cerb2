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
| File: config_maintenance_repair.php
|
| Purpose: The configuration include for repairing all the database tables. 
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

?>
<script>
<!--
sid = "<?php echo "sid=".$session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

function formatURL(url)
{
  if(show_sid) { url = url + "&" + sid; }
  return(url);
}

function doRepair() {
	document.location = formatURL('configuration.php?form_submit=maintenance_repair&module=maintenance_repair&action=repair'); 
} 
-->
</script>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance_repair">
<input type="hidden" name="module" value="maintenance_repair">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": Database Repair Completed!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass">  
    <td colspan="2">Repair Database Tables</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="2" class="cer_maintable_heading" valign="top" align="left"> 
      <div class="cer_maintable_heading"> 
        <table width="98%" border="0" cellspacing="1" cellpadding="2">
          <tr> 
            <td width="21%" class="cer_maintable_heading">Repairing Tables:<br>
				<span class="cer_footer_text">Repairing may take a while depending on the size of your database.<br></span>
			</td>
            <td width="79%" valign="top"> 
            	<input type="button" value="Repair" class="cer_button_face" OnClick="javascript:doRepair();">
            </td>
          </tr>
          <?php if(isset($form_submit) && $cerberus_db->num_rows($rep_result) > 0) { ?>
          <tr>
          	<td colspan="2">
            	<?php while($rep_row = $cerberus_db->fetch_row($rep_result)) { ?>
              	<span class="cer_maintable_heading"><?php echo  $rep_row["Table"]; ?>:</span> <span class="cer_footer_text"><?php echo  $rep_row["Msg_text"]; ?></span><br>
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
          <tr> 
            <td width="21%" class="cer_maintable_heading">&nbsp;</td>
            <td width="79%">&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
</table>
</form>