<span class="cer_display_header">{$smarty.const.LANG_CONTACTS_REGISTRED_VIEW}</span><br>
<span class="cer_maintable_text"> {$smarty.const.LANG_CONTACTS_REGISTRED_INSTRUCTIONS} {$smarty.const.LANG_CONTACTS_REGISTRED_INSTRUCTIONS_VIEW}
</span><br>
<a href="{$urls.clients}" class="cer_maintable_heading">&lt;&lt; {$smarty.const.LANG_CONTACTS_BACK_TO_LIST} </a><br>
<br>

<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">

	<tr>
		<td valign="top">
		
			{if $priv->has_priv(ACL_CONTACTS_CONTACT_MANAGE,BITGROUP_3) }
				{include file="clients/client_publicuser_details_editable.tpl.php" id=$user->public_user_id}
			{else}
				{include file="clients/client_publicuser_details_readonly.tpl.php"}
			{/if}
			
		</td>
		
		<td>
			<img src="includes/images/spacer.gif" width="10" height="1">
		</td>
		
		<td valign="top">

			{include file="clients/client_publicuser_company_details.tpl.php"}
			
			<br>
			
			{include file="clients/client_company_sla_details.tpl.php" sla=$user->company_ptr->sla_ptr}
			
			<br>
			
			{if $priv->has_priv(ACL_CONTACTS_EMAIL_ASSIGN,BITGROUP_3) }
				{include file="clients/client_publicuser_assign_address.tpl.php"}
			{/if}
			
		</td>
	</tr>
</table>

<br>

<table border="0" cellspacing="1" cellpadding="3" bgcolor="#888888" width="100%">
	
	{if !empty($user->email_addresses) && $priv->has_priv(ACL_CONTACTS_EMAIL_ASSIGN,BITGROUP_3) }
		<form action="clients.php">
		<input type="hidden" name="form_submit" value="user_update">
		<input type="hidden" name="sid" value="{$session_id}">
		<input type="hidden" name="mode" value="{$params.mode}">
		<input type="hidden" name="id" value="{$params.id}">
	{/if}

	<tr class="boxtitle_gray_glass_dk">
		<td>{$smarty.const.LANG_CONTACTS_REGISTRED_MAILADR_HEADER}</td>
	</tr>

	<tr bgcolor="#DDDDDD">
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_MAIL}</td>
	</tr>

	{foreach from=$user->email_addresses item=email name=email key=addy_id}
		<tr bgcolor="#EEEEEE">
			<td class="cer_maintable_text">
				{if $priv->has_priv(ACL_CONTACTS_EMAIL_ASSIGN,BITGROUP_3) }
					<input type="checkbox" name="puaids[]" value="{$addy_id}">
				{/if}
				{$email}
			</td>
		</tr>
	{/foreach}
	
	{if !empty($user->email_addresses) && $priv->has_priv(ACL_CONTACTS_EMAIL_ASSIGN,BITGROUP_3) }
		
		<tr bgcolor="#BBBBBB" align="right">
			<td>
				<span class="cer_maintable_header">{$smarty.const.LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED} </span>
				<select name="user_email_action">
					<option value="">{$smarty.const.LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED_NOTHING}
					<option value="unassign">{$smarty.const.LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED_UNASSIGN}
				</select>
				<input type="submit" value="{$smarty.const.LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED_UPDATE}" class="cer_button_face">
			</td>
		</tr>
	
		</form>
	{/if}
	
</table>

<br>

{include file="clients/client_open_tickets.tpl.php" summary=$user->open_tickets}