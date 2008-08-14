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
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

$handler = new cer_CustomFieldGroupHandler();
$handler->loadGroupTemplates();

$binding_handler = new cer_CustomFieldBindingHandler();
?>

<form action="configuration.php?module=custom_field_bindings" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="custom_field_bindings">

<table width="100%" border="0" cellspacing="1" cellpadding="2">
	<tr class="boxtitle_orange_glass"> 
		<td colspan="2">Custom Field Bindings</td>
	</tr>
	<tr bgcolor="#E0E0E0"> 
		<td colspan="2" class="cer_maintable_text">
			Certain areas of GUI functionality can be extended with custom fields.  Use this area to choose 
			what custom field group to associate with each GUI feature.  <B>NOTE:</B> Changing the field group
			for a feature will remove existing custom field data.
		</td>
	</tr>
	
	<!--- Company Records --->
	<tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
		<td align="left" valign="top" bgcolor="#D9D9D9" class="cer_maintable_heading" width="1%" nowrap> 
		 	Company Records:&nbsp;
		</td>
		<td align="left" bgcolor="#E0E0E0" width="99%"> 
			<input type="hidden" name="custom_binding[]" value="<?php echo ENTITY_COMPANY; ?>">
			<select name="custom_binding_val[]">
				<option value="0">- none -
				<?php
				$bind_id = $binding_handler->getEntityBinding(ENTITY_COMPANY);
				
				foreach($handler->group_templates as $group) {
					echo sprintf("<option value='%s' %s>%s\r\n",
							$group->group_id,
							($bind_id == $group->group_id) ? "SELECTED" : "",
							@htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE)
						);
				}
				?>
			</select>
			<br>
			<span class="cer_footer_text">These fields will be appended to any company record in the contacts area.</span>
		</td>
	</tr>

	<!--- Contact Records --->
	<tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
		<td align="left" valign="top" bgcolor="#D9D9D9" class="cer_maintable_heading" width="1%" nowrap> 
		 	Contact Records:&nbsp;
		</td>
		<td align="left" bgcolor="#E0E0E0" width="99%"> 
			<input type="hidden" name="custom_binding[]" value="<?php echo ENTITY_CONTACT; ?>">
			<select name="custom_binding_val[]">
				<option value="0">- none -
				<?php
				$bind_id = $binding_handler->getEntityBinding(ENTITY_CONTACT);
				
				foreach($handler->group_templates as $group) {
					echo sprintf("<option value='%s' %s>%s\r\n",
							$group->group_id,
							($bind_id == $group->group_id) ? "SELECTED" : "",
							@htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE)
						);
				}
				?>
			</select>
			<br>
			<span class="cer_footer_text">These fields will be appended to any contact record in the contacts area.</span>
		</td>
	</tr>

	<!--- Time Tracking Entries --->
	<tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
		<td align="left" valign="top" bgcolor="#D9D9D9" class="cer_maintable_heading" width="1%" nowrap> 
		 	Time Tracking Entries:&nbsp;
		</td>
		<td align="left" bgcolor="#E0E0E0" width="99%"> 
			<input type="hidden" name="custom_binding[]" value="<?php echo ENTITY_TIME_ENTRY; ?>">
			<select name="custom_binding_val[]">
				<option value="0">- none -
				<?php
				$bind_id = $binding_handler->getEntityBinding(ENTITY_TIME_ENTRY);
				
				foreach($handler->group_templates as $group) {
					echo sprintf("<option value='%s' %s>%s\r\n",
							$group->group_id,
							($bind_id == $group->group_id) ? "SELECTED" : "",
							@htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE)
						);
				}
				?>
			</select>
			<br>
			<span class="cer_footer_text">These fields will be appended to any time tracking entry listed with a ticket.</span>
		</td>
	</tr>
	
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>"></td>
	</tr>
  
</table>

</form>