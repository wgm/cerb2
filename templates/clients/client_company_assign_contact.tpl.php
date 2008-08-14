<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000" width="100%">
	<form action="clients.php">
	<input type="hidden" name="form_submit" value="company_add_contact">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	<input type="hidden" name="id" value="{$company->company_id}">

	<tr class="boxtitle_gray_glass">
		<td>
			{$smarty.const.LANG_CONTACTS_COMPANY_ASIGNCONTACT_HEADER}
		</td>
	</tr>

	{if !empty($add_contact_pass_msg)}
	<tr bgcolor="#EEEEEE">
		<td class="cer_configuration_success">{$add_contact_pass_msg}</td>
	</tr>
	{/if}
	
	{if !empty($add_contact_fail_msg)}
	<tr bgcolor="#EEEEEE">
		<td class="cer_configuration_updated">{$add_contact_fail_msg}</td>
	</tr>
	{/if}
	
	<tr bgcolor="#EEEEEE">
		<td>
			<span class="cer_maintable_text">
			{$smarty.const.LANG_CONTACTS_COMPANY_ASIGNCONTACT_INSTRUCTIONS}
			</span>
			<input type="text" name="company_add_contact" value="" size="20" maxlength="64">
			<input type="submit" value="{$smarty.const.LANG_CONTACTS_COMPANY_ASIGNCONTACT_SUBMIT}" class="cer_button_face"><br>
		</td>
	</tr>
	
	</form>
		
</table>		
