<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_public_gui_fields_edit.php
|
| Purpose: The configuration include for creating and editing 
|		custom field groups.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicGUISettings.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		kbase values
if(!$priv->has_priv(ACL_PUBLIC_GUI,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

if(!isset($fid))
	{ echo LANG_KB_NO_CATID; exit(); }	

$option_array = array(1 => "Optional Field",
					  2 => "Required Field"
				);
	
$pg_group = new cer_PublicGUIFieldGroups($fid);

$field_handler = new cer_CustomFieldGroupHandler();
$field_handler->loadGroupTemplates();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pfid" value="<?php echo $pg_group->active_group->group_id; ?>">
<input type="hidden" name="module" value="public_gui_fields">
<input type="hidden" name="form_submit" value="public_gui_fields_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
  <tr class="boxtitle_orange_glass">  
<?php
if($fid==0) {
    ?><td>Create Public GUI Custom Field Group</td><?php
}
else {
    ?><td>Edit Public GUI Custom Field Group '<?php echo @htmlspecialchars(stripslashes($pg_group->active_group->group_name), ENT_QUOTES, LANG_CHARSET_CODE) ?>'</td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Group Name:</td>
            <td width="81%">
              <input type="text" name="group_name" size="32" maxlength="64" value="<?php echo @htmlspecialchars(stripslashes($pg_group->active_group->group_name), ENT_QUOTES, LANG_CHARSET_CODE); ?>"><br>
              <span class="cer_footer_text">
              	You can assign this group of custom fields to any queue in a Public GUI Profile.  You should use a descriptive name 
              	to identify this group, such as: "Bug Tracking Fields" or "Sales Lead Fields".  Adding custom fields to your Public 
              	GUI ensures your customers are providing your agents with all the necessary information to quickly solve tickets.
              	<br></span>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
            		
            		<?php
            		foreach($field_handler->group_templates as $idx => $group) {
            			?>
            			
		            	<table border="0" cellspacing="1" cellpadding="1" width="100%" bgcolor="#FFFFFF">
		            		<tr class="boxtitle_gray_glass_dk"> 
		            			<td colspan="3"> 
		            				<span><?php echo $group->group_name; ?></span>
		            			</td>
		            		</tr>
		            		
		            		<tr bgcolor="#999999" class="cer_maintable_header">
		            			<td width="1%" align="center" nowrap>Include</td>
		            			<td width="98%">Field Name</td>
		            			<td width="1%" nowrap>Setting</td>
		            		</tr>
		            		
		            		<?php
		            		foreach($group->fields as $field)
		            		{
		            			$fld = $pg_group->field_exists($pg_group->active_group->fields,$field->field_id);
		            		?>
		            		
			            		<tr bgcolor="#DDDDDD">
			            			<td width="1%" align="center" nowrap><input type="checkbox" name="fld_ids[]" value="<?php echo $field->field_id; ?>" <?php echo (($fld) ? "checked" : ""); ?>></td>
			            			<td width="98%" class="cer_maintable_text">
			            				<?php echo @htmlspecialchars($field->field_name, ENT_QUOTES, LANG_CHARSET_CODE); ?>
			            				<input type="hidden" name="name_<?php echo $field->field_id; ?>" value="<?php echo @htmlspecialchars($field->field_name, ENT_QUOTES, LANG_CHARSET_CODE); ?>">
			            			</td>
			            			<td width="1%" nowrap>
			            				<select name="option_<?php echo $field->field_id; ?>">
			            					<?php
			            					foreach($option_array as $idx => $o)
			            						echo "<option value='$idx'" . (($fld && $fld->field_option == $idx) ? "selected" : "") . ">$o";
			            					?>
			            				</select>
			            			</td>
			            		</tr>
			            		
			            	<?php
            				}
			            	?>
            			
            			<?php
            		}
            		?>
            	</table>
            	
            	<br>
           	</td>
          </tr>
					<tr bgcolor="#B0B0B0" class="cer_maintable_text">
						<td colspan="2" align="right">
							<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>">
						</td>
					</tr>
        </table>    		
    </td>
  </tr>
</table>
</form>
<br>
