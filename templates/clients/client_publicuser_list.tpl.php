
	<a name="users">
	
	{if $priv->has_priv(ACL_CONTACTS_CONTACT_MANAGE,BITGROUP_3) }
	<span class="cer_maintable_text">
		[ <a href="{$urls.contact_add}" class="cer_maintable_heading">{$smarty.const.LANG_CONTACTS_ADD_REGISTRED}</a> ] 
	</span>
	<br>
	{/if}
	
	<table border="0" cellspacing="1" cellpadding="3" bgcolor="#888888" width="100%">
		{if $showcontrols && $priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3) }
			<form action="clients.php">
			<input type="hidden" name="form_submit" value="company_update">
			<input type="hidden" name="sid" value="{$session_id}">
			<input type="hidden" name="mode" value="{$params.mode}">
			<input type="hidden" name="id" value="{$params.id}">
		{/if}

		<tr class="boxtitle_blue_glass">
			<td colspan="{if $showcontrols}6{else}5{/if}">{$user_handler->set_title}</td>
		</tr>
	
		<tr bgcolor="#CCCCCC">
		{if $showcontrols && $priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3) }<td class="cer_maintable_headingSM" align="center">{$smarty.const.LANG_WORD_SELECT}</td>{/if}
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_MAIL}</td>
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_CONTACT_NAME}</td>
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_WORD_COMPANY}</td>
			<td class="cer_maintable_headingSM">Self-Help Access</td>
			<td class="cer_maintable_headingSM" align="center">{$smarty.const.LANG_CONTACTS_REGISTRED_NUMBER}</td>
		</tr>
	
		{foreach from=$user_handler->users_by_email item=user key=email name=user}
	
			<tr bgcolor="#{if $smarty.foreach.user.iteration % 2 == 0}EEEEEE{else}F5F5F5{/if}">
				{if $showcontrols && $priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3) }<td class="cer_maintable_text" align="center"><input type="checkbox" name="puids[]" value="{$user->public_user_id}"></td>{/if}
				<td class="cer_maintable_text"><a href="{$user->url_view}" class="cer_maintable_heading">{$email}</a></td>
				<td class="cer_maintable_text">
					{$user->account_name_first} {$user->account_name_last}					
				</td>
				<td class="cer_maintable_text"><a href="{$user->company_ptr->url_view}" class="cer_maintable_heading">{$user->company_ptr->company_name}</a></td>
				<td class="cer_maintable_text">{if $user->account_access_level == 0}Contact{elseif $user->account_access_level == 5}Manager{/if}</td>
				<td class="cer_maintable_text" align="center">{$user->total_addresses}</td>
			</tr>
	
		{/foreach}
	
		{if $showcontrols && !empty($user_handler->users_by_email) && $priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3) }
		
			<tr>
				<td colspan="6" bgcolor="#BBBBBB" align="right">
					<span class="cer_maintable_header">{$smarty.const.LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED}</span>
					<select name="company_contact_action">
						<option value="">{$smarty.const.LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED_NOTHING}
						<option value="unassign">{$smarty.const.LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED_UNASSIGN}
					</select>
					<input type="submit" value="{$smarty.const.LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED_UPDATE}" class="cer_button_face">
				</td>
			</tr>
		
			</form>
			
		{/if}
		
	</table>

	<table border="0" cellspacing="0" cellpadding="3" width="100%">
	
		<tr>
			<td align="right" class="cer_maintable_text">
				{if $user_handler->set_url_prev}<a href="{$user_handler->set_url_prev}" class="cer_header_loginLink">&lt;&lt; Prev</a>{/if}
				{$smarty.const.LANG_WORD_SHOWING} <B>{$user_handler->set_from}</B> {$smarty.const.LANG_WORD_TO} <B>{$user_handler->set_to}</B> {$smarty.const.LANG_WORD_OF} <B>{$user_handler->set_of}</B>
				{if $user_handler->set_url_next}<a href="{$user_handler->set_url_next}" class="cer_header_loginLink">Next &gt;&gt;</a>{/if}
			</td>
		</tr>
	
	</table>
	
	<br>