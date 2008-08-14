<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000">
	
	<tr class="boxtitle_orange_glass">
		<td colspan="2">{$smarty.const.LANG_CONTACTS_COMPANY_EDIT_HEADER}</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_NAME}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_name}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_ACCOUNT_NUM}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_account_number}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STREET}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_mailing_address|nl2br}<br>
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_CITY}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_mailing_city}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STATE}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_mailing_state}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_ZIP}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_mailing_zip}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COUNTRY}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$company->company_mailing_country_name}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">{$company->company_phone}</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_FAX}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">{$company->company_fax}</td>
	</tr>
		
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_MAIL_SHORT}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">{$company->company_email}</td>
	</tr>
		
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_WEBSITE}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">{$company->company_website}</td>
	</tr>
	
	{if $company->custom_fields}
		<tr>
			<td bgcolor="#EEEEEE" colspan="2">&nbsp;</td>
		</tr>
	
		{* [JAS]: Display custom fields if they are bound to company records *}
		{foreach from=$company->custom_fields->fields name=custom item=field}
			{if !empty($field->field_value)}
			<tr>
				<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$field->field_name}:</td>
				<td bgcolor="#EEEEEE" class="cer_maintable_text">
	              	{if $field->field_type == "D"}
	              		{assign var=opt_val value=$field->field_value}
	              		{$field->field_options.$opt_val}
	              	{else}
						{$field->field_value|short_escape}
					{/if}
				</td>
			</tr>
			{/if}
		{/foreach}
	{/if}
	
</table>
