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
| File: config_parser_log.php
|
| Purpose: The configuration include for displaying and deleting the
|		parser log. 
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
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

function doDeleteLog() {
	if(confirm("Are you sure you want to delete the parser/gui log?"))
  	document.location = formatURL('configuration.php?form_submit=log&module=log&action=delete'); 
} 
-->
</script>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="log">
<input type="hidden" name="module" value="log">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": Parser Log Reset!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td>Parser/GUI Error Log</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td class="cer_maintable_heading" valign="top" align="left"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php
          $sql = "SELECT log_id,DATE_FORMAT(`log_date`,'%Y-%m-%d %H:%i:%s') as log_date,message FROM log ORDER BY log_date DESC LIMIT 0,30";
          $result = $cerberus_db->query($sql);
          
          if($cerberus_db->num_rows($result) > 0) {
        ?>
          <tr>
          	<td colspan="2">
            	<table cellpadding=0 cellspacing=0 border=0 width="100%">
              <?php
              $x=0;
              while($log_row = $cerberus_db->fetch_row($result)) {
              	$date = new cer_DateTime($log_row["log_date"]);
              if($x%2==0) { $alt_color = "#D5D5D5"; } else { $alt_color="#E5E5E5"; } 
              ?>
              	<tr><td bgcolor="<?php echo $alt_color?>"><span class="cer_footer_text"><b><?php echo $date->getUserDate(); ?><b>:</span> <span class="cer_footer_text"><?php echo  $log_row["message"]; ?></span></td></tr>
              <?php $x++; } ?>
            	</table>
            </td>
          </tr>
          <tr>
          	<td colspan="2">
            	<input type="button" value="Clear Log" OnClick="javascript:doDeleteLog();" class="cer_button_face">
            </td>
          </tr>
          <?php } else { echo "<tr><td colspan=2 class=\"cer_maintable_text\">No log entries.</td></tr>"; } ?>
        </table>
    </td>
  </tr>
</table>
</form>