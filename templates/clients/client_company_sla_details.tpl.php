<SCRIPT>
	{literal}
	
	function calendarPopUp(date_field)
	{
		{/literal}
		{if $track_sid eq "true"}
			url = "calendar_popup.php?sid={$session_id}&date_field=" + date_field;
		{else}
			url = "calendar_popup.php?date_field=" + date_field;
		{/if}
		{literal}
		window.open(url,"calendarWin","width=250,height=200");		
	}
	
	{/literal}
</SCRIPT>

<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000" width="100%">

	<tr class="boxtitle_green_glass">
		<td colspan="4">
			{if !empty($sla)}
				{$sla->sla_name}
			{else}
				{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_HEADERIFEMPTY}
			{/if}
		</td>
	</tr>

{if !empty($sla)}
	
	<tr bgcolor="#CCCCCC">
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_TABLE_QUEUE}</td>
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_TABLE_QUEUEMODE}</td>
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_TABLE_SLASCHEDULE}</td>
		<td class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_TABLE_TARGETRESPONSETIME}</td>
	</tr>


	{foreach from=$sla->queues item=queue name=queue key=qid}
		<tr bgcolor="#F5F5F5">
			<td class="cer_maintable_heading">{$queue->queue_name}</td>
			<td class="cer_maintable_text">{$queue->queue_mode}</td>
			<td class="cer_maintable_text">{$queue->queue_schedule_name}</a></td>
			<td class="cer_maintable_text" align="center">{$queue->queue_response_time}{$smarty.const.LANG_DATE_SHORT_HOURS_ABBR}</td>
		</tr>
	{/foreach}

	<form name="company_sla_update" action="clients.php">
	<input type="hidden" name="form_submit" value="company_sla_update">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	<input type="hidden" name="id" value="{$company->company_id}">
		
	{if $params.mode == "c_view" && $priv->has_priv(ACL_CONTACTS_SLA_ASSIGN,BITGROUP_3) }
		<tr bgcolor="#E5E5E5">
			<td colspan="4" class="cer_maintable_text">
				<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_EXPIRES}:</span>
					<input type="text" name="company_sla_expire" maxlength="8" size="8" value="{if $company->sla_expire_date}{$company->sla_expire_date->getUserDate("%m/%d/%y")}{/if}">
		          	<span class="cer_footer_text">
		          	<B>(</B><a href="javascript: calendarPopUp('document.company_sla_update.company_sla_expire');" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}</a><B>)</B><br>
		          	{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_CALENDAR}
		          	</span>				
			</td>
		</tr>
	{elseif $params.mode == "u_view"}
		<tr bgcolor="#E5E5E5">
			<td colspan="4" class="cer_maintable_text">
				<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_EXPIRES}:</span>
				{if $user->company_ptr->sla_expire_date}{$user->company_ptr->sla_expire_date->getUserDate("%m/%d/%y")}{/if}
			</td>
		</tr>
	{/if}
	
	{if $params.mode == "c_view" && $priv->has_priv(ACL_CONTACTS_SLA_ASSIGN,BITGROUP_3) }
		<tr bgcolor="#F5F5F5">
			<td colspan="4" align="right">
				<input type="checkbox" name="company_remove_sla" value="1">
				<span class="cer_maintable_text">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_REMOVEPLAN}</span>
				<input type="submit" value="{$smarty.const.LANG_WORD_UPDATE}" class="cer_button_face">
			</td>
		</tr>
	{/if}
	
	</form>
	
{else}

	<form name="company_add_sla" action="clients.php">
	<input type="hidden" name="form_submit" value="company_add_sla">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	<input type="hidden" name="id" value="{$company->company_id}">

	<tr>
		<td bgcolor="#EEEEEE" colspan="4">
			<span class="cer_maintable_headingSM">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_NOPLAN}</span><br>
		</td>
	</tr>
	
	{if $params.mode == "c_view" && $priv->has_priv(ACL_CONTACTS_SLA_ASSIGN,BITGROUP_3) }
		<tr bgcolor="#EEEEEE">
			<td colspan="4">
					<span class="cer_maintable_text"><B>{$smarty.const.LANG_CONTACTS_REGISTRED_COMPANY_SLAPLAN}:</B> </span>
					<select name="company_add_sla">
						<option value="">{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_SELECTPLAN_NONE}
						{foreach from=$company_handler->sla_handler->plans item=plan name=plan}
							<option value="{$plan->sla_id}">{$plan->sla_name}
						{/foreach}
					</select>
			</td>
		</tr>
		<tr bgcolor="#EEEEEE">
			<td colspan="4">
					<span class="cer_maintable_text"><B>{$smarty.const.LANG_WORD_EXPIRES}:</B></span>
					<input type="text" name="company_sla_expire" maxlength="8" size="8" value="">
		          	<span class="cer_footer_text">
		          	<B>(</B><a href="javascript: calendarPopUp('document.company_add_sla.company_sla_expire');" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}</a><B>)</B><br>
		          	{$smarty.const.LANG_CONTACTS_COMPANY_SLABOX_CALENDAR}
		          	</span>
			</td>
		</tr>
		<tr>
			<td bgcolor="#EEEEEE" colspan="4" align="right"><input type="submit" value="{$smarty.const.LANG_WORD_ADD}" class="cer_button_face"></td>
		</tr>
	{/if}
	
	</form>
		
{/if}
	
</table>		
