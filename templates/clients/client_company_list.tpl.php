
	<a name="companies">
	
	{if $priv->has_priv(ACL_CONTACTS_COMPANY_MANAGE,BITGROUP_3) }
	<span class="cer_maintable_text">
		[ <a href="{$urls.company_add}" class="cer_maintable_heading">{$smarty.const.LANG_CONTACTS_ADD_COMPANY}</a> ] 
	</span>
	<br>
	{/if}
	
	<table border="0" cellspacing="1" cellpadding="3" bgcolor="#888888" width="100%">

		<tr class="boxtitle_orange_glass">
			<td colspan="5">{$smarty.const.LANG_CONTACTS_HEADER_COMPANIES}</td>
		</tr>
	
		<tr bgcolor="#CCCCCC">
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_NAME}</td>
			<td class="cer_maintable_headingSM">Service Level (SLA) Plan</td>
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_WORD_EXPIRES}</td>
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_PHONE}</td>
			<td class="cer_maintable_headingSM" align="center">{$smarty.const.LANG_CONTACTS_COMPANY_NUMBER}</td>
		</tr>
	
		{foreach from=$company_handler->companies item=company name=company}
	
			<tr bgcolor="#{if $smarty.foreach.company.iteration % 2 == 0}DFDFDF{else}EAEAEA{/if}">
				<td class="cer_maintable_text"><a href="{$company->url_view}" class="cer_maintable_heading">{$company->company_name}</a></td>
				<td class="cer_maintable_text">{$company->sla_ptr->sla_name}</td>
				<td class="cer_maintable_text">{if $company->sla_expire_date}{$company->sla_expire_date->getUserDate("%m/%d/%y")}{/if}</td>
				<td class="cer_maintable_text">{$company->company_phone}</td>
				<td class="cer_maintable_text" align="center">{$company->num_public_users}</td>
			</tr>
	
		{/foreach}
	
	</table>
	
	<table border="0" cellspacing="0" cellpadding="3" bgcolor="#888888" width="100%">
	
		<tr bgcolor="#FFFFFF">
			<td align="right" class="cer_maintable_text">
			{if $company_handler->set_url_prev}<a href="{$company_handler->set_url_prev}" class="cer_header_loginLink">&lt;&lt; Prev</a>{/if}
			{$smarty.const.LANG_WORD_SHOWING} <B>{$company_handler->set_from}</B> {$smarty.const.LANG_WORD_TO} <B>{$company_handler->set_to}</B> {$smarty.const.LANG_WORD_OF} <B>{$company_handler->set_of}</B>
			{if $company_handler->set_url_next}<a href="{$company_handler->set_url_next}" class="cer_header_loginLink">Next &gt;&gt;</a>{/if}
			</td>
		</tr>
	
	</table>
	
	<br>