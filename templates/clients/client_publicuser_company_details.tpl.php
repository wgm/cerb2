<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000" width="100%">

	<tr class="boxtitle_orange_glass">
		<td colspan="2">
			{$smarty.const.LANG_CONTACTS_REGISTRED_COMPANY_HEADER}
		</td>
	</tr>

{if !empty($user->company_ptr)}
	
	<tr bgcolor="#DDDDDD">
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_WORD_COMPANY}:</td>
		<td bgcolor="#EEEEEE">
			<a href="{$user->company_ptr->url_view}" class="cer_maintable_heading">{$user->company_ptr->company_name}</a>
		</td>
	</tr>
	
	{if $user->company_ptr->sla_ptr->sla_name }
	<tr bgcolor="#DDDDDD">
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_REGISTRED_COMPANY_SLAPLAN}:</td>
		<td bgcolor="#EEEEEE">
			<span class="cer_maintable_text">
					{$user->company_ptr->sla_ptr->sla_name}
			</span>
		</td>
	</tr>
	{/if}

{else}

	<tr bgcolor="#EEEEEE">
		<td colspan="2">
			<span class="cer_maintable_text">
			{$smarty.const.LANG_CONTACTS_REGISTRED_INSTRUCTIONS_NOCOMPANY}
			</span>
		</td>
	</tr>
		
{/if}
	
</table>		
