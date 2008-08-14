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
| File: config_maintenance_optimize.php
|
| Purpose: The configuration include for optimizing all the database tables. 
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

function doOptimize() {
	document.location = formatURL('configuration.php?form_submit=maintenance_optimize&module=maintenance_optimize&action=optimize'); 
} 
-->
</script>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance_optimize">
<input type="hidden" name="module" value="maintenance_optimize">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": All Database Tables Optimized!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td>Optimize Database Tables</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td class="cer_maintable_heading" valign="top" align="left"> 
      <div class="cer_maintable_heading"> 
        <table width="98%" border="0" cellspacing="1" cellpadding="2">
          <tr> 
            <td class="cer_maintable_heading">Optimize Tables:<br>
				<span class="cer_footer_text">Optimizing may take a while depending on the size of your database and 
				the time since your last optimization.  The 'overhead' column below shows how much disk space you will 
				recover.  If this is not significant then you do not require optimization.</span><br>
				<input type="button" value="Run Optimize" class="cer_button_face" OnClick="javascript:doOptimize();">
			</td>
          </tr>
          <?php if(isset($form_submit) && $cerberus_db->num_rows($opt_result) > 0) { ?>
          <tr>
          	<td>
            	<?php while($opt_row = $cerberus_db->fetch_row($opt_result)) { ?>
              	<span class="cer_maintable_heading"><?php echo  $opt_row["Table"]; ?>:</span> <span class="cer_footer_text"><?php echo  $opt_row["Msg_text"]; ?></span><br>
              <?php } ?>
            </td>
          </tr>
          <?php
          } elseif(!isset($form_submit)) {
          	$sql = "SHOW TABLE STATUS";
          	$res = $cerberus_db->query($sql);
          	
          	if($cerberus_db->num_rows($res)) {
          	?>
	          <tr>
	          	<td colspan="2">
	          	
	          		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #bbbbbb;" bgcolor="#eeeeee">
	          		<tr class="boxtitle_green_glass">
	          			<td colspan="5">Cerberus Helpdesk -Database Status-</td>
	          		</tr>
	          		<tr class="boxtitle_gray_glass">
	          			<td>Table</td>
	          			<td>Rows</td>
	          			<td align="right">Data Size&nbsp;</td>
	          			<td align="right">Indexes&nbsp;</td>
	          			<td align="right">Overhead&nbsp;</td>
	          		</tr>
	          		<?php
	          		$total_size = 0;
	          		$total_indexes = 0;
	          		$total_overhead = 0;
	          		while($row = $cerberus_db->fetch_row($res))
	          		{
	          		?>
          			<tr onmouseover="this.style.backgroundColor='#ffffff';" onmouseout="this.style.backgroundColor='#eeeeee';">
          				<td style="border-top: 1px #bbbbbb solid;" class="cer_maintable_heading"><?php echo $row["Name"]; ?></td>
          				<td style="border-top: 1px #bbbbbb solid;" class="cer_footer_text"><?php echo $row["Rows"]; ?></td>
          				<td align="right" style="border-top: 1px #bbbbbb solid;" class="cer_footer_text"><?php echo display_bytes_size($row["Data_length"]); ?>&nbsp;</td>
          				<td align="right" style="border-top: 1px #bbbbbb solid;" class="cer_footer_text"><?php echo display_bytes_size($row["Index_length"]); ?>&nbsp;</td>
          				<td align="right" style="border-top: 1px #bbbbbb solid;" class="<?php echo ($row["Data_free"] >= 1000000) ? "cer_footer_red" : "cer_footer_text"; ?>"><?php echo display_bytes_size($row["Data_free"]); ?>&nbsp;</td>
          			</tr>
          			<?php 
          				$total_size += $row["Data_length"];
          				$total_indexes += $row["Index_length"];
          				$total_overhead += $row["Data_free"];
	          		}
	          		?>
	          		<tr bgcolor='#888888'>
	          			<td></td>
	          			<td></td>
	          			<td align="right" class="cer_maintable_header"><?php echo display_bytes_size($total_size);?>&nbsp;</td>
	          			<td align="right" class="cer_maintable_header"><?php echo display_bytes_size($total_indexes);?>&nbsp;</td>
	          			<td align="right" class="cer_maintable_header"><?php echo display_bytes_size($total_overhead);?>&nbsp;</td>
	          		</tr>
          			</table>
          		</td>
          	</tr>
          <?php
          	} // end if num rows
          } // end if form submit
          ?>
          <tr> 
            <td>&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
</table>
</form>