<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000">
	<form action="clients.php" method="post" name="clients_contact">
	<input type="hidden" name="form_submit" value="{if !empty($id)}user_edit{else}user_add{/if}">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	{if !empty($params.add_to) } <input type="hidden" name="add_to" value="{$params.add_to}"> {/if}
	{if !empty($id) } <input type="hidden" name="id" value="{$id}"> {/if}
	
	{if $user_add_error_msg}
	<tr>
		<td bgcolor="#FFFFFF" colspan="2" class="cer_configuration_updated">{$user_add_error_msg}</td>
	</tr>
	{/if}
	
	{if $record_edit_pass_msg}
	<tr>
		<td bgcolor="#FFFFFF" colspan="2" class="cer_configuration_success">{$record_edit_pass_msg}</td>
	</tr>
	{/if}
	
	<tr class="boxtitle_blue_glass">
		<td colspan="2">{$smarty.const.LANG_CONTACTS_CONTACT_EDIT_HEADER}</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">First Name:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_name_first" size="16" maxlength="16" value="{$user->account_name_first|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">Last Name:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_name_last" size="32" maxlength="32" value="{$user->account_name_last|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">Self-Help Access:</td>
		<td bgcolor="#EEEEEE">
			<select name="account_access_level">
				<option value="0" {if $user->account_access_level == 0}selected{/if}>Contact (can only view own ticket history)
				<option value="5" {if $user->account_access_level == 5}selected{/if}>Manager (can view entire company's ticket history)
			</select>
		</td>
	</tr>
	
	{* Only if Adding *}
	{if empty($id)}
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">E-mail Address:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_email_address" size="45" maxlength="64" value="{$params.add_email}">
		</td>
	</tr>
	{/if}
	
	{if !empty($params.add_to) && !empty($company)}
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">Company:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			<input type="checkbox" name="account_company_id" value="{$params.add_to}" checked> 
			Assign to: {$company->company_name}
		</td>
	</tr>
	{/if}
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STREET}:</td>
		<td bgcolor="#EEEEEE">
			<TEXTAREA name="account_mailing_address" cols="45" rows="3" maxlength="128">{$user->account_address|escape:"htmlall"}</TEXTAREA><br>
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_CITY}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_mailing_city" size="45" maxlength="64" value="{$user->account_city|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STATE}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_mailing_state" size="45" maxlength="64" value="{$user->account_state|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_ZIP}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_mailing_zip" size="45" maxlength="64" value="{$user->account_zip|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COUNTRY}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_mailing_country" size="45" maxlength="64" value="{$user->account_country|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE_WORK}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_phone_work" size="45" maxlength="32" value="{$user->account_phone_work|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE_HOME}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_phone_home" size="45" maxlength="32" value="{$user->account_phone_home|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE_MOBILE}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_phone_mobile" size="45" maxlength="32" value="{$user->account_phone_mobile|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_FAX}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_phone_fax" size="45" maxlength="32" value="{$user->account_phone_fax|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#EEEEEE" colspan="2">&nbsp;</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{if !empty($id)}{$smarty.const.LANG_CONTACTS_REGISTRED_RESET_PW}{else}{$smarty.const.LANG_CONTACTS_REGISTRED_SET_PW}{/if}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="account_password" size="35" maxlength="32" value="">
			{if !empty($id)}<br><span class="cer_footer_text">{$smarty.const.LANG_CONTACTS_REGISTRED_PW_NOTE}</span>{/if}
		</td>
	</tr>
	
	{* [JAS]: Display custom fields if they are bound to contact records *}
	{if $contact_entry_defaults.custom_fields || $user->custom_fields}

	<tr>
		<td bgcolor="#EEEEEE" colspan="2">&nbsp;</td>
	</tr>
	
	{if empty($id) }
		<input type="hidden" name="contact_custom_gid" value="{$contact_entry_defaults.custom_gid}">
		{assign var=ptr value=$contact_entry_defaults.custom_fields}
	{else}
		<input type="hidden" name="contact_custom_inst_id" value="{$user->custom_fields->group_instance_id}">
		{assign var=ptr value=$user->custom_fields}
	{/if}
	
	{foreach from=$ptr->fields name=custom item=field}
		<tr>
			<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$field->field_name}:</td>
			<td bgcolor="#EEEEEE" class="cer_maintable_text">
              	{if $field->field_type == "S"}
                	<input type="text" name="contact_custom_{$field->field_id}" size="45" value="{$field->field_value|short_escape}" class="cer_custom_field_text">
                {/if}
                
              	{if $field->field_type == "E"}
					<input type="text" name="contact_custom_{$field->field_id}" maxlength="8" size="8" value="{$field->field_value}">
					<a href="javascript: calendarPopUp('document.clients_contact.contact_custom_{$field->field_id}');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
		          	<span class="cer_footer_text">
						(use calendar -or- enter <b><i>mm/dd/yy</i></b>)
		          	</span>
                {/if}

              	{if $field->field_type == "T"}
                	<textarea cols="45" rows="3" name="contact_custom_{$field->field_id}" wrap="virtual" class="cer_custom_field_text">{$field->field_value|short_escape}</textarea><br>
                	<span class="cer_footer_text">(maximum 255 characters)</span>
                {/if}
                
              	{if $field->field_type == "D"}
                	<select name="contact_custom_{$field->field_id}" class="cer_custom_field_text">
                      <option value="">
                      {html_options options=$field->field_options selected=$field->field_value}
                    </select>
                {/if}
			</td>
		</tr>
	{/foreach}
	
	{/if}
	
	<tr>
		<td bgcolor="#EEEEEE" class="cer_maintable_headingSM" colspan="2" align="right">
			<input type="submit" value="{$smarty.const.LANG_WORD_SAVE_CHANGES}" class="cer_button_face">
		</td>
	</tr>
	
	</form>
</table>

<br>