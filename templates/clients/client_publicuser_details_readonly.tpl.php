<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000">
	
	<tr class="boxtitle_blue_glass">
		<td colspan="2">{$smarty.const.LANG_CONTACTS_CONTACT_EDIT_HEADER}</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">First Name:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_name_first}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">Last Name:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_name_last}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">Self-Help Access:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{if $user->account_access_level == 0}Contact (can only view own ticket history){/if}
			{if $user->account_access_level == 5}Manager (can view entire company's ticket history){/if}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STREET}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_address|nl2br}<br>
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_CITY}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_city}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_STATE}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_state}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_ZIP}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_zip}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COUNTRY}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_country}
		</td>
	</tr>

	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE_WORK}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_phone_work}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE_HOME}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_phone_home}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE_MOBILE}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_phone_mobile}
		</td>
	</tr>
	
	<tr>
		<td bgcolor="#DDDDDD" class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_FAX}:</td>
		<td bgcolor="#EEEEEE" class="cer_maintable_text">
			{$user->account_phone_fax}
		</td>
	</tr>
	
	{if $user->custom_fields}
		<tr>
			<td bgcolor="#EEEEEE" colspan="2">&nbsp;</td>
		</tr>
		
		{* [JAS]: Display custom fields if they are bound to contact records *}
		{foreach from=$user->custom_fields->fields name=custom item=field}
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
