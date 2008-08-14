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

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		kbase values
if(!$priv->has_priv(ACL_TICKET_FLDS_ADMIN,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$handler = new cer_CustomFieldGroupHandler();
$handler->loadGroupTemplates();

$group = &$handler->group_templates[$gid];

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<script>

function checkGroupName(){
	if (document.frmCustomGroup.group_name.value == "") {
		alert('Group Name is Required');
		document.frmCustomGroup.group_name.focus();
		return false;
	} 
	else {
		return true;
	}	
}

</script>

<form name="frmCustomGroup" action="configuration.php" method="post" onSubmit="return checkGroupName();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pgid" value="<?php echo $group->group_id; ?>">
<input type="hidden" name="module" value="custom_fields">
<input type="hidden" name="form_submit" value="custom_fields_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
<?php
if($gid==0) {
    ?><td>Create a New Custom Field Group</td><?php
}
else {
    ?><td>Edit Custom Field Group '<?php echo @htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE) ?>'</td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
			<tr>  
				<td width="1%" nowrap class="cer_maintable_heading" valign="top" bgcolor="#DDDDDD">Group Name:</td>
				<td bgcolor="#DDDDDD">
					<input type="text" name="group_name" size="32" maxlength="64" value="<?php echo @htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE); ?>">
					<span class="cer_footer_text"> (for example: Warranty Details)</span>
				</td>
			</tr>
        
			<tr class="boxtitle_gray_glass_dk">
				<td colspan="2">Fields</td>
        	</tr>
        	
			<tr bgcolor="#DDDDDD">
				<td colspan="2">
					<table cellspacing="2" cellpadding="1" border="0" width="100%">
						<tr bgcolor="#999999">
							<td width="1%" class="cer_maintable_header" nowrap>Delete</td>
							<td width="99%" class="cer_maintable_header">Field</td>
						</tr>
						
			        	<?php 
			        	if(!empty($group->fields))
			        	foreach($group->fields as $field) {
			        	?>
							<tr>
								<td bgcolor="#EEEEEE" align="center" valign="top" nowrap><input type="checkbox" name="field_ids[]" value="<?php echo $field->field_id; ?>"></td>
								<td bgcolor="#EEEEEE" valign="top">
									<span class="cer_maintable_heading"><?php echo $field->field_name; ?></span>
									<span class="cer_maintable_text">(<?php echo $field->getTypeName(); ?>)</span>
									
									<?php
										if($field->field_type == 'D') {
											?>
											<table cellpadding="0" cellspacing="0" border="0">
											<tr>
												<td>
													<img src="includes/images/spacer.gif" width="25" height="1">
												</td>
												<td valign="top">
													<input type="hidden" name="field_<?php echo $field->field_id; ?>_initial" value="<?php echo implode(",",array_keys($field->field_options)); ?>">
													<input type="hidden" name="field_<?php echo $field->field_id; ?>_ordered" value="<?php echo implode(",",array_keys($field->field_options)); ?>">
													<span class="cer_footer_text">Options:</span><br>
													<select name="field_<?php echo $field->field_id; ?>_opts" size="<?php echo count($field->field_options); ?>" multiple>
														<?php
														if(!empty($field->field_options))
														foreach($field->field_options as $opt_id => $opt)
														{
														?>
															<option value="<?php echo $opt_id; ?>"><?php echo $opt; ?>
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
													<input type="button" value="Move Up" class="cer_footer_text" onclick="javascript: moveUp(this.form.field_<?php echo $field->field_id; ?>_opts); saveListState(this.form.field_<?php echo $field->field_id; ?>_opts,this.form.field_<?php echo $field->field_id; ?>_ordered);"><BR>
													<input type="button" value="Move Down" class="cer_footer_text" onclick="javascript: moveDown(this.form.field_<?php echo $field->field_id; ?>_opts); saveListState(this.form.field_<?php echo $field->field_id; ?>_opts,this.form.field_<?php echo $field->field_id; ?>_ordered);"><BR>
													<input type="button" value="Sort (A to Z)" class="cer_footer_text" onclick="javascript: sortList(this.form.field_<?php echo $field->field_id; ?>_opts,0); saveListState(this.form.field_<?php echo $field->field_id; ?>_opts,this.form.field_<?php echo $field->field_id; ?>_ordered);"><BR>
													<input type="button" value="Sort (Z to A)" class="cer_footer_text" onclick="javascript: sortList(this.form.field_<?php echo $field->field_id; ?>_opts,1); saveListState(this.form.field_<?php echo $field->field_id; ?>_opts,this.form.field_<?php echo $field->field_id; ?>_ordered);"><BR>
													<input type="button" value="Delete" class="cer_footer_text" onclick="javascript: dropOptions(this.form.field_<?php echo $field->field_id; ?>_opts); saveListState(this.form.field_<?php echo $field->field_id; ?>_opts,this.form.field_<?php echo $field->field_id; ?>_ordered);">
												</td>
												
												</tr>
											</table>
											
											<img src="includes/images/spacer.gif" width="15" height="1">
											<?php
											echo "<span class='cer_maintable_text'>Add Option:</span> <input type='text' name='option_name_" . $field->field_id . "' size='45' maxlength='64' class='cer_footer_text'>";
											echo "<input type='hidden' name='dropdown_ids[]' value='" . $field->field_id . "'>";
										}
									?>
									
								</td>
							</tr>
			        	<?php 
			        	}
			        	?>
					</table>
				</td>
			</tr>
			
			<tr bgcolor="#666666">
				<td colspan="2" class="cer_maintable_header">Add a New Field</td>
        	</tr>
        
        <tr bgcolor="#DDDDDD"> 
            <td nowrap class="cer_maintable_heading" valign="top"><?php echo LANG_CONFIG_FIELDS_EDIT_NAME ?>:</td>
            <td>
              <input type="text" name="field_name" size="32" maxlength="64" value="<?php echo @htmlspecialchars(stripslashes($field_data["field_name"]), ENT_QUOTES, LANG_CHARSET_CODE); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_FIELDS_EDIT_NAME_IE ?></span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td nowrap class="cer_maintable_heading" valign="top"><?php echo  LANG_CONFIG_FIELDS_EDIT_TYPE ?>:</td>
            <td>
				<select name="field_type">
				  <option value="S" <?php if($field_data["field_type"]=="S") echo " SELECTED"; ?>><?php echo LANG_CONFIG_FIELDS_EDIT_TYPE_S ?>
				  <option value="T" <?php if($field_data["field_type"]=="T") echo " SELECTED"; ?>><?php echo LANG_CONFIG_FIELDS_EDIT_TYPE_T ?>
				  <option value="D" <?php if($field_data["field_type"]=="D") echo " SELECTED"; ?>><?php echo LANG_CONFIG_FIELDS_EDIT_TYPE_D ?>
				  <option value="E" <?php if($field_data["field_type"]=="E") echo " SELECTED"; ?>><?php echo LANG_CONFIG_FIELDS_EDIT_TYPE_E ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td nowrap class="cer_maintable_heading" valign="top">Search Exclude:</td>
            <td>
				<input type="checkbox" name="field_not_searchable" value="1" <?php if($field_data["field_not_searchable"]) echo "CHECKED"; ?>><br>
				<span class="cer_footer_text">If you prefer to exclude this field from the "Advanced Ticket Search" box throughout the helpdesk then
				check the box above.</span>
			</td>
	      </tr>
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0">
		<td align="right">
		
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<td align="left" width="50%">
					<input type="button" value="&lt;&lt; Back to Custom Field Groups (Don't Save)" onclick="javascript:document.location='configuration.php?module=custom_fields&sid=<?php echo $session->session_id; ?>';" class="cer_button_face">
				</td>
				<td align="right" width="50%">
					<input type="submit" value="<?php echo LANG_BUTTON_SAVE_CHANGES; ?>" class="cer_button_face">
				</td>
			</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<br>
