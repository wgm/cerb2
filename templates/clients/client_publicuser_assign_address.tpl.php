<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000" width="100%">
	<form action="clients.php">
	<input type="hidden" name="form_submit" value="user_add_address">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	<input type="hidden" name="id" value="{$user->public_user_id}">

	<tr class="boxtitle_gray_glass">
		<td>
			{$smarty.const.LANG_CONTACTS_REGISTRED_MAILASSIGN_HEADER}
		</td>
	</tr>

	{if !empty($user_email_pass_msg)}
	<tr bgcolor="#EEEEEE">
		<td class="cer_configuration_success">{$user_email_pass_msg}</td>
	</tr>
	{/if}
	
	{if !empty($user_email_fail_msg)}
	<tr bgcolor="#EEEEEE">
		<td class="cer_configuration_updated">{$user_email_fail_msg}</td>
	</tr>
	{/if}
	
	<tr bgcolor="#EEEEEE">
		<td>
			<span class="cer_maintable_text">
			{$smarty.const.LANG_CONTACTS_REGISTRED_MAILASSIGN_INSTRUCTIONS}
			</span>
			<input type="text" name="user_add_address" value="" size="20" maxlength="64">
			<input type="submit" value="{$smarty.const.LANG_CONTACTS_REGISTRED_MAILASSIGN_SUBMIT}" class="cer_button_face"><br>
		</td>
	</tr>
	
	</form>
		
</table>		
