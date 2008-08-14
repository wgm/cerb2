<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000">
	<form action="clients.php" method="post" name="clients_company">
	<input type="hidden" name="form_submit" value="{if !empty($id) }company_edit{else}company_add{/if}">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	{if !empty($id) }
		<input type="hidden" name="id" value="{$id}">
	{/if}

	{if $record_edit_pass_msg}
	<tr>
		<td bgcolor="#FFFFFF" colspan="2" class="cer_configuration_success">{$record_edit_pass_msg}</td>
	</tr>
	{/if}
	
	<tr class="boxtitle_orange_glass">
		<td colspan="2">{$smarty.const.LANG_CONTACTS_COMPANY_EDIT_HEADER}</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_NAME}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_name" size="45" maxlength="64" value="{$company->company_name|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_ACCOUNT_NUM}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_account_number" size="45" maxlength="64" value="{$company->company_account_number|escape:"htmlall"}"><br>
			{if empty($id) }
				<input type="checkbox" name="company_account_number_auto" value="1" checked> 
				<span class="cer_maintable_text">Automatically Assign Account Number</span>
			{/if}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STREET}:</td>
		<td bgcolor="#EEEEEE">
			<textarea name="company_mailing_address" cols="45" rows="3" maxlength="128">{$company->company_mailing_address|escape:"htmlall"}</textarea><br>
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_CITY}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_mailing_city" size="45" maxlength="64" value="{$company->company_mailing_city|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STATE}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_mailing_state" size="45" maxlength="64" value="{$company->company_mailing_state|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_ZIP}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_mailing_zip" size="45" maxlength="64" value="{$company->company_mailing_zip|escape:"htmlall"}">
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COUNTRY}:</td>
		<td bgcolor="#EEEEEE">
			<select name="company_mailing_country_id">
				<option value="0">
				{html_options options=$country_list selected=$company->company_mailing_country_id}
			</select>
			
			{if !empty($company->company_mailing_country_old)}
			<br>
			<span class="cer_footer_text">
			Pre-MajorCRM value: {$company->company_mailing_country_old}
			</span>
			{/if}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_phone" size="32" maxlength="32" value="{$company->company_phone|escape:"htmlall"}">
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_FAX}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_fax" size="32" maxlength="32" value="{$company->company_fax|escape:"htmlall"}">
		</td>
	</tr>
		
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_MAIL_SHORT}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_email" size="45" maxlength="64" value="{$company->company_email|escape:"htmlall"}">
		</td>
	</tr>
		
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_WEBSITE}:</td>
		<td bgcolor="#EEEEEE">
			<input type="text" name="company_website" size="45" maxlength="64" value="{$company->company_website|escape:"htmlall"}">
		</td>
	</tr>
	
	{* [JAS]: Display custom fields if they are bound to company records *}
	{if $company_entry_defaults.custom_fields || $company->custom_fields}

	<tr>
		<td bgcolor="#EEEEEE" colspan="2">&nbsp;</td>
	</tr>
	
	{if empty($id) }
		<input type="hidden" name="company_custom_gid" value="{$company_entry_defaults.custom_gid}">
		{assign var=ptr value=$company_entry_defaults.custom_fields}
	{else}
		<input type="hidden" name="company_custom_inst_id" value="{$company->custom_fields->group_instance_id}">
		{assign var=ptr value=$company->custom_fields}
	{/if}
	
	{foreach from=$ptr->fields name=custom item=field}
		<tr>
			<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$field->field_name}:</td>
			<td bgcolor="#EEEEEE" class="cer_maintable_text">
              	{if $field->field_type == "S"}
                	<input type="text" name="company_custom_{$field->field_id}" size="45" value="{$field->field_value|short_escape}" class="cer_custom_field_text">
                {/if}

              	{if $field->field_type == "E"}
					<input type="text" name="company_custom_{$field->field_id}" maxlength="8" size="8" value="{$field->field_value}">
					<a href="javascript: calendarPopUp('document.clients_company.company_custom_{$field->field_id}');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
		          	<span class="cer_footer_text">
						(use calendar -or- enter <b><i>mm/dd/yy</i></b>)
		          	</span>
                {/if}
                
              	{if $field->field_type == "T"}
                	<textarea cols="45" rows="3" name="company_custom_{$field->field_id}" wrap="virtual" class="cer_custom_field_text">{$field->field_value|short_escape}</textarea><br>
                	<span class="cer_footer_text">(maximum 255 characters)</span>
                {/if}
                
              	{if $field->field_type == "D"}
                	<select name="company_custom_{$field->field_id}" class="cer_custom_field_text">
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
