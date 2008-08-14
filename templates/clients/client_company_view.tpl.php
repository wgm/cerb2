<span class="cer_display_header">{$company->company_name}</span><br>
<span class="cer_maintable_text">{$smarty.const.LANG_CONTACTS_COMPANY_INSTRUCTIONS} {$smarty.const.LANG_CONTACTS_COMPANY_INSTRUCTIONS_VIEW}
</span><br>
<a href="{$urls.clients}" class="cer_maintable_heading">&lt;&lt; {$smarty.const.LANG_CONTACTS_BACK_TO_LIST} </a><br>
<br>

<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">

	<tr>
		<td valign="top">
		
			{if $priv->has_priv(ACL_CONTACTS_COMPANY_MANAGE,BITGROUP_3) }
				{include file="clients/client_company_details_editable.tpl.php" id=$company->company_id}
			{else}
				{include file="clients/client_company_details_readonly.tpl.php"}
			{/if}
			
			<br>
			
		</td>
		
		<td>
			<img src="includes/images/spacer.gif" width="10" height="1">
		</td>
		
		<td valign="top">
			
			{include file="clients/client_company_sla_details.tpl.php" sla=$company->sla_ptr}
			
			<br>
			
			{if $priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3) }
				{include file="clients/client_company_assign_contact.tpl.php"}
				<br>
			{/if}
			
			{if $priv->has_priv(ACL_CONTACTS_COMPANY_DELETE,BITGROUP_3) }
				{include file="clients/client_company_delete.tpl.php"}
			{/if}

		</td>
	</tr>
</table>

<br>

{include file="clients/client_publicuser_list.tpl.php" showcontrols=true}

{include file="clients/client_open_tickets.tpl.php" summary=$company->open_tickets}
