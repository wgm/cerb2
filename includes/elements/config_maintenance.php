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
| File: config_maintenance.php
|
| Purpose: The configuration include for purging dead tickets, optimizing 
| 		and repairing the database.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

if(!$priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

if(!isset($num_purged_tickets)) $num_purged_tickets = 0;
?>
<script>
<!--
sid = "sid=<?php echo $session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

function formatURL(url)
{
  if(show_sid) { url = url + "&" + sid; }
  return(url);
}

function doTicketPurge() {
	document.location = formatURL('configuration.php?form_submit=maintenance&module=maintenance&action=purge'); 
} 
-->
</script>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance">
<input type="hidden" name="module" value="maintenance">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": $num_purged_tickets " . LANG_CONFIG_PURGE_SUCCESS . "!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td colspan="2"><?php echo  LANG_CONFIG_PURGE_TITLE ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="2" class="cer_maintable_heading" valign="top" align="left"> 
      <div class="cer_maintable_heading"> 
        <table width="98%" border="0" cellspacing="1" cellpadding="2">
          <tr> 
            <td width="30%" class="cer_maintable_heading">
	    		<?php 
			// [PK] Philipp Kolmann (kolmann@zid.tuwien.ac.at)
			// Print number of to be purged tickets also prior to purging
			$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.ticket_status='dead' AND t.ticket_date < DATE_SUB(NOW(),INTERVAL \"%d\" HOUR)",$cfg->settings["purge_wait_hrs"]);
	    		$purge_count = $cerberus_db->query($sql,false);
	    		$num_purged_tickets = $cerberus_db->num_rows($purge_count);
	    		
				echo  LANG_CONFIG_PURGE_DEAD;
				echo ": ";
				echo $num_purged_tickets; ?> Ticket(s)<br>
				<span class="cer_footer_text"><?php echo  LANG_CONFIG_PURGE_NOTE ?><br></span>
			</td>
            <td width="70%" valign="top"> 
              <input type="button" value="<?php echo  LANG_CONFIG_PURGE_SUBMIT ?>" class="cer_button_face" OnClick="javascript:doTicketPurge();">
              </td>
          </tr>
          <tr> 
            <td width="30%" class="cer_maintable_heading">&nbsp;</td>
            <td width="70%">&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
</table>
</form>