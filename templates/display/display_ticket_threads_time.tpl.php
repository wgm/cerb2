<div id="thread_track_time_{$oThread->thread_time_id}" style="display:block;">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_purple_glass">
	<td>
		&nbsp;{$oThread->date_string} - {$smarty.const.LANG_DISPLAY_TIME_TRACKING_TITLE}
	</td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	
	<tr bgcolor="#FFFFFF">
		<td class="cer_maintable_text">
			<table width="100%" cellspacing="1" cellpadding="2" border="0">
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_DATE}:
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
						{$oThread->date_string}
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_DATE_BILLED}:
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
						{$oThread->date_billed_string}
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_AGENT}:
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
						{$oThread->working_agent_string}
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
						{$smarty.const.LANG_WORD_HOURS}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<table border="0" cellpadding="1" cellspacing="0">
							<tr>
								<td class="cer_maintable_text"><B>{$smarty.const.LANG_WORD_WORKED}:</B> {$oThread->hrs_spent}&nbsp;</td>
								<td class="cer_maintable_text"><B>{$smarty.const.LANG_WORD_CHARGEABLE}:</B> {$oThread->hrs_chargeable}&nbsp;</td>
								<td class="cer_maintable_text"><B>{$smarty.const.LANG_WORD_BILLABLE}:</B> {$oThread->hrs_billable}&nbsp;</td>
								<td class="cer_maintable_text"><B>{$smarty.const.LANG_WORD_PAYABLE}:</B> {$oThread->hrs_payable}&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_SUMMARY}:
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
						{$oThread->summary|short_escape|nl2br}
					</td>
				</tr>
				
				{* [JAS]: Display custom fields if they are bound to time entries *}
				{foreach from=$oThread->custom_fields->fields name=custom item=field}
					{if !empty($field->field_value)}
					<tr>
						<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
							{$field->field_name}:
						</td>
						<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
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
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
					{$smarty.const.LANG_WORD_CREATED}:
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_footer_text">
						Created on <B>{$oThread->created_date_string}</B> by <B>{$oThread->created_by_string}</B>
					</td>
				</tr>
				
				{* [JAS]: Check privs *}
				{if $priv->has_priv(ACL_TIME_TRACK_EDIT_ALL,BITGROUP_3) 
					|| ($priv->has_priv(ACL_TIME_TRACK_EDIT_OWN,BITGROUP_3) && $oThread->working_agent_id == $session->vars.login_handler->user_id) }
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						Options:					
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
						<a href="javascript:toggleThreadTime({$oThread->thread_time_id});" class="cer_custom_field_heading">toggle edit mode</a>
					</td>
				</tr>
				{/if}
				
			</table>
		</td>
	</tr>
	
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td align="right"><a href="#top" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_BACK_TO_TOP|lower}</a></td></tr>
</table>

<br>
</div>