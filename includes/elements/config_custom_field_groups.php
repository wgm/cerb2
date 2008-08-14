<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Purpose: The configuration include for custom ticket fields.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
if(!$priv->has_priv(ACL_TICKET_FLDS_ADMIN,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

//$sql = "SELECT `field_id`,`field_name`,`field_type`,`field_options`,`field_not_searchable` FROM `ticket_fields` ORDER BY `field_name`";
//$result = $cerberus_db->query($sql);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";

$handler = new cer_CustomFieldGroupHandler();
$handler->loadGroupTemplates();
?>
<form action="configuration.php?module=custom_fields" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="custom_fields_delete">
<table width="100%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td colspan="3">Custom Field Groups</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="3" align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		<a href="<?php echo cer_href("configuration.php?module=custom_fields&pgid=0"); ?>" class="cer_maintable_subjectLink">Create a New Custom Field Group</a><br>
  	</td>
  </tr>
  <tr class="boxtitle_gray_glass_dk"> 
    <td align="left">Delete</td>
    <td align="left">Group</td>
  </tr>
	<?php
	foreach($handler->group_templates as $group)
		{
			echo 
		  '<tr bgcolor="#DDDDDD" class="cer_maintable_text">';
		  
		    echo '<td width="1%" align="center" bgcolor="#DDDDDD" class="cer_maintable_text" valign="top" nowrap>';
  				echo "<input type=\"checkbox\" name=\"gids[]\" value=\"" . $group->group_id . "\">";
  			echo '</td>';
  			
		    echo '<td width="99%" align="left" bgcolor="#DDDDDD" class="cer_maintable_text">'.
  				'<a href="' . cer_href("configuration.php?module=custom_fields&pgid=" . $group->group_id) . '" class="cer_maintable_subjectLink">' . @htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE) . '</a>'.
  				' <span class="cer_footer_text">(click group name to add/delete fields)</span><br>';
?>
				<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<img src="includes/images/spacer.gif" width="25" height="1">
					</td>
					<td valign="top">
						<input type="hidden" name="group_<?php echo $group->group_id; ?>_initial" value="<?php echo implode(",",array_keys($group->fields)); ?>">
						<input type="hidden" name="group_<?php echo $group->group_id; ?>_ordered" value="<?php echo implode(",",array_keys($group->fields)); ?>">
						<span class="cer_footer_text">Fields:</span><br>
						<select name="group_<?php echo $group->group_id; ?>_opts" size="<?php echo count($group->fields); ?>" multiple>

						<?php
		  				if(!empty($group->fields))
  						foreach($group->fields as $field) {
							?>
								<option value="<?php echo $field->field_id; ?>"><?php echo $field->field_name . " (" . $field->getTypeName() . ")"; ?>
							<?php
							}
						?>
						</select>
					</td>
					<td>
						<img src="includes/images/spacer.gif" width="5" height="1">
					</td>
					<td valign="top">
						<br>
						<input type="button" value="Move Up" class="cer_footer_text" onclick="javascript: moveUp(this.form.group_<?php echo $group->group_id; ?>_opts); saveListState(this.form.group_<?php echo $group->group_id; ?>_opts,this.form.group_<?php echo $group->group_id; ?>_ordered);"><BR>
						<input type="button" value="Move Down" class="cer_footer_text" onclick="javascript: moveDown(this.form.group_<?php echo $group->group_id; ?>_opts); saveListState(this.form.group_<?php echo $group->group_id; ?>_opts,this.form.group_<?php echo $group->group_id; ?>_ordered);"><BR>
						<br>
						<input type="button" value="Sort (A to Z)" class="cer_footer_text" onclick="javascript: sortList(this.form.group_<?php echo $group->group_id; ?>_opts,0); saveListState(this.form.group_<?php echo $group->group_id; ?>_opts,this.form.group_<?php echo $group->group_id; ?>_ordered);"><BR>
						<input type="button" value="Sort (Z to A)" class="cer_footer_text" onclick="javascript: sortList(this.form.group_<?php echo $group->group_id; ?>_opts,1); saveListState(this.form.group_<?php echo $group->group_id; ?>_opts,this.form.group_<?php echo $group->group_id; ?>_ordered);"><BR>
<!---						<input type="button" value="Delete" class="cer_footer_text" onclick="javascript: dropOptions(this.form.group_<?php echo $group->group_id; ?>_opts); saveListState(this.form.group_<?php echo $group->group_id; ?>_opts,this.form.group_<?php echo $group->group_id; ?>_ordered);">--->
					</td>
					
					</tr>
				</table>
				
		  			<?php
		  			
  			echo '</td>';
 			echo '</tr>';
			echo "<input type='hidden' name='group_ids[]' value='" . $group->group_id . "'>";
		}
  		?>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td colspan="3" align="right">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
