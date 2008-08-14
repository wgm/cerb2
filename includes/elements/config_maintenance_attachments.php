<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC 
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

require_once(FILESYSTEM_PATH . "cerberus-api/attachments/cer_AttachmentManager.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

$cer_attachments = new cer_AttachmentManager();
$attachments = array();
$attachment_max_results = isset($_REQUEST["attachment_max_results"]) ? $_REQUEST["attachment_max_results"] : 100;

if(isset($form_submit)) {
	$attachment_filters = isset($_REQUEST["attachment_filters"]) ? $_REQUEST["attachment_filters"] : array();
	
	$attachment_name_oper = isset($_REQUEST["attachment_name_oper"]) ? $_REQUEST["attachment_name_oper"] : "";
	$attachment_name = isset($_REQUEST["attachment_name"]) ? $_REQUEST["attachment_name"] : "";
	$attachment_size_oper = isset($_REQUEST["attachment_size_oper"]) ? $_REQUEST["attachment_size_oper"] : "";
	$attachment_size = isset($_REQUEST["attachment_size"]) ? $_REQUEST["attachment_size"] : "";
	$attachment_size_unit = isset($_REQUEST["attachment_size_unit"]) ? $_REQUEST["attachment_size_unit"] : "";
	$attachment_date_oper = isset($_REQUEST["attachment_date_oper"]) ? $_REQUEST["attachment_date_oper"] : "";
	$attachment_date = isset($_REQUEST["attachment_date"]) ? $_REQUEST["attachment_date"] : "";
	$attachment_resolved = isset($_REQUEST["attachment_resolved"]) ? $_REQUEST["attachment_resolved"] : 0;
	
	$filters = array();
	
	if(!empty($attachment_filters)) {
		foreach($attachment_filters as $f) {
			switch($f) {
				case "name":
					$filters["name"] = array();
					$filters["name"]["oper"] = $attachment_name_oper;
					$filters["name"]["value"] = $attachment_name;
					break;
				case "size":
					$filters["size"] = array();
					$filters["size"]["oper"] = $attachment_size_oper;
					$filters["size"]["value"] = $attachment_size;
					$filters["size"]["unit"] = $attachment_size_unit;
					break;
				case "date":
					$filters["date"] = array();
					$filters["date"]["oper"] = $attachment_date_oper;
					$filters["date"]["value"] = $attachment_date;
					break;
				case "resolved":
					$filters["resolved"] = array();
					$filters["resolved"]["value"] = $attachment_resolved;
					break;
			}
		}
		
		$attachments = $cer_attachments->getAttachmentsByFilters($filters,$attachment_max_results);
		
//		print_r($attachments);
	}
	
}

?>

<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit) && isset($purged_files)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": Purged $purged_files temporary files!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2">

<form action="configuration.php" method="post" name="config_maintenance_attachments">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance_attachments">
<input type="hidden" name="module" value="maintenance_attachments">

  <tr class="boxtitle_orange_glass"> 
    <td colspan="2">Find Attachments to Clean-up</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td class="cer_maintable_heading" valign="top" align="left"> 
    
        <table width="98%" border="0" cellspacing="1" cellpadding="2">
       	  <tr> 
            <td class="cer_maintable_heading">Attachment Search:<br>
				<span class="cer_footer_text">
					Use the filters below to locate attachments which are no longer needed by 
					the helpdesk.
				</span><br>
				
				<table width="98%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td width="1%" nowrap class="cer_maintable_heading" style="border-top: 1px solid #999999;">
							<input type="checkbox" name="attachment_filters[]" value="name" <?php if(isset($filters["name"])) echo "CHECKED"; ?>>
							File name 
							<select name="attachment_name_oper">
								<option value="substr" <?php if(@$attachment_name_oper=="substr") echo "SELECTED";?>>contains
								<option value="equal" <?php if(@$attachment_name_oper=="equal") echo "SELECTED";?>>equals
							</select>
						</td>
						<td width="99%" style="border-top: 1px solid #999999;">
							<input type="text" name="attachment_name" size="45" value="<?php echo @$attachment_name; ?>">
							<span class="cer_footer_text">(case insensitive)</span>
						</td>
					</tr>
					<tr>
						<td width="1%" nowrap class="cer_maintable_heading"  style="border-top: 1px solid #999999;">
							<input type="checkbox" name="attachment_filters[]" value="size" <?php if(isset($filters["size"])) echo "CHECKED"; ?>>
							File size 
							<select name="attachment_size_oper">
								<option value="gte" <?php if(@$attachment_size_oper=="gte") echo "SELECTED";?>>bigger than
								<option value="lte" <?php if(@$attachment_size_oper=="lte") echo "SELECTED";?>>smaller than
							</select>
						</td>
						<td width="99%" class="cer_maintable_text"  style="border-top: 1px solid #999999;">
							<input type="text" name="attachment_size" size="8" value="<?php echo @$attachment_size; ?>">
							<select name="attachment_size_unit">
								<option value="kb" <?php if(@$attachment_size_unit=="kb") echo "SELECTED";?>>KB
								<option value="mb" <?php if(@$attachment_size_unit=="mb") echo "SELECTED";?>>MB
							</select>
						</td>
					</tr>
					<tr>
						<td width="1%" nowrap class="cer_maintable_heading" style="border-top: 1px solid #999999;">
							<input type="checkbox" name="attachment_filters[]" value="date" <?php if(isset($filters["date"])) echo "CHECKED"; ?>>
							File created 
							<select name="attachment_date_oper">
								<option value="gte" <?php if(@$attachment_date_oper=="gte") echo "SELECTED";?>>after
								<option value="lte" <?php if(@$attachment_date_oper=="lte") echo "SELECTED";?>>before
							</select>
						</td>
						<td width="99%" style="border-top: 1px solid #999999;">
							<input type="text" name="attachment_date" size="8" value="<?php echo @$attachment_date; ?>">
							<a href="javascript: calendarPopUp('document.config_maintenance_attachments.attachment_date');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="<?php echo LANG_DISPLAY_SHOW_CALENDAR; ?>"></a>
							<span class="cer_footer_text">(mm/dd/yy)</span>
						</td>
					</tr>
					<tr>
						<td colspan="2" nowrap class="cer_maintable_heading" style="border-top: 1px solid #999999;">
							<input type="checkbox" name="attachment_filters[]" value="resolved" <?php if(isset($filters["resolved"])) echo "CHECKED"; ?>>
							Attachment's ticket is 
							<select name="attachment_resolved">
								<option value="1" <?php if(@$attachment_resolved=="1") echo "SELECTED";?>>resolved
								<option value="0" <?php if(@$attachment_resolved=="0") echo "SELECTED";?>>not resolved
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="border-top: 1px solid #999999;">
							<span class="cer_maintable_heading">Return at most </span>
							<select name="attachment_max_results">
								<option value="25" <?php if($attachment_max_results=="25") echo "SELECTED";?>>25 results
								<option value="50" <?php if($attachment_max_results=="50") echo "SELECTED";?>>50 results
								<option value="100" <?php if($attachment_max_results=="100") echo "SELECTED";?>>100 results
								<option value="250" <?php if($attachment_max_results=="250") echo "SELECTED";?>>250 results
								<option value="500" <?php if($attachment_max_results=="500") echo "SELECTED";?>>500 results
								<option value="1000" <?php if($attachment_max_results=="1000") echo "SELECTED";?>>1000 results
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="left" style="border-top: 1px solid #999999;"><input type="submit" value="Search" class="cer_button_face"></td>
					</tr>
				</table>
            	
            </td>
          </tr>
        </table>
        
    </td>
  </tr>
</form>
</table>
<br>

<script>
var toggleCheck = 0;
function checkAttachmentAllToggle()
{
	toggleCheck = (toggleCheck) ? 0 : 1;
	for(e = 0;e < document.config_attachment_prune.elements.length; e++) {
		if(document.config_attachment_prune.elements[e].type == 'checkbox') {
			document.config_attachment_prune.elements[e].checked = toggleCheck;
		}
	}
}
</script>

<table width="98%" border="0" cellspacing="0" cellpadding="2">
<form action="configuration.php" method="post" name="config_attachment_prune">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance_attachments_purge">
<input type="hidden" name="action" value="attachments_purge">
<input type="hidden" name="module" value="maintenance_attachments">
<input type="hidden" name="attachment_max_results" value="<?php echo $attachment_max_results; ?>">

<?php
// [JAS]: Store the previous search in hidden fields so we repeat it after the file purge.
if(!empty($attachment_filters)) {
	foreach($attachment_filters as $f) {
		switch($f) {
			case "name":
			?>
				<input type="hidden" name="attachment_filters[]" value="name">
				<input type="hidden" name="attachment_name_oper" value="<?php echo $attachment_name_oper; ?>">
				<input type="hidden" name="attachment_name" value="<?php echo $attachment_name; ?>">
			<?php
				break;
			case "size":
			?>
				<input type="hidden" name="attachment_filters[]" value="size">
				<input type="hidden" name="attachment_size_oper" value="<?php echo $attachment_size_oper; ?>">
				<input type="hidden" name="attachment_size" value="<?php echo $attachment_size; ?>">
				<input type="hidden" name="attachment_size_unit" value="<?php echo $attachment_size_unit; ?>">
			<?php
				break;
			case "date":
			?>
				<input type="hidden" name="attachment_filters[]" value="date">
				<input type="hidden" name="attachment_date_oper" value="<?php echo $attachment_date_oper; ?>">
				<input type="hidden" name="attachment_date" value="<?php echo $attachment_date; ?>">
			<?php
				break;
			case "resolved":
			?>
				<input type="hidden" name="attachment_filters[]" value="resolved">
				<input type="hidden" name="attachment_resolved" value="<?php echo $attachment_resolved; ?>">
			<?php
				break;
		}
	}
}
?>

  <tr class="boxtitle_green_glass"> 
    <td>Remove Attachments</td>
  </tr>
  <?php if(!empty($attachments)) { ?>
  
	  <tr bgcolor="#DDDDDD">
	  	<td>
	  		<input type="submit" value="Delete Checked Attachments" class="cer_button_face">
	  	</td>
	  </tr>
	  <tr bgcolor="#DDDDDD">
	  	<td>
	  		<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#EEEEEE">
	  			<tr class="boxtitle_gray_glass">
	  				<td align="center" nowrap style="padding-left:3px;padding-right:3px;"><a href="javascript:checkAttachmentAllToggle();" class="boxtitle_gray_text">all</a></td>
	  				<td colspan="2" align="left" style="padding-left:3px;padding-right:3px;">Attachment</td>
	  				<td align="left" nowrap style="padding-left:3px;padding-right:3px;">Date</td>
	  				<td align="right" nowrap style="padding-left:3px;padding-right:3px;">Size</td>
	  			</tr>
	  			<?php foreach($attachments as $a) { ?>
		  			<tr>
		  				<td colspan="5" style="border-bottom:1px solid #ffffff;"><img src="includes/images/spacer.gif" width="1" height="1"></td>
		  			</tr>
		  			<tr>
		  				<td width="1%" align="center" nowrap style="padding:2px;"><input type="checkbox" name="attachment_purge_ids[]" value="<?php echo $a["id"]; ?>"></td>
		  				<td width="1%" align="left" style="padding:2px;"><img src="includes/images/crystal/16x16/icon_attachment_tar.gif" align="absmiddle"></td>
		  				<td width="69%" align="left" style="padding:2px;" class="cer_footer_text">
		  					<b><?php echo $a["name"]; ?></b><br>
		  					<?php echo sprintf("<a href='%s' class='cer_footer_text' target='_blank'>%s: %s</a>",cer_href("display.php?ticket=" . $a["ticket_id"],"thread_".$a["thread_id"]),LANG_WORD_TICKET,$a["ticket_subject"]); ?>
		  				</td>
		  				<td width="20%" align="left" nowrap style="padding:2px;" class="cer_footer_text"><?php $d = new cer_DateTime($a["date"]); echo $d->getUserDate(); ?></td>
		  				<td width="10%" align="right" nowrap style="padding:2px;" class="cer_footer_text"><?php echo display_bytes_size($a["size"]); ?></td>
		  			</tr>
	  			<?php } ?>
	  		</table>
	  	</td>
	  </tr>
	  <tr bgcolor="#DDDDDD">
	  	<td>
	  		<input type="submit" value="Delete Checked Attachments" class="cer_button_face">
	  	</td>
	  </tr>
  
  
  <? } else { ?>
  
	  <tr bgcolor="#EEEEEE">
	  	<td class="cer_maintable_text">No attachments found.  Run a search above to find attachments to prune.</td>
	  </tr>
	  
  <?php } ?>
</form>
</table>

