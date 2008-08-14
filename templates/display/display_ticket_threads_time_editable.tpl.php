<div id="thread_track_time_{$oThread->thread_time_id}_edit" style="display:none;">
<a name="thread_track_time_{$oThread->thread_time_id}_edit">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#6600cc">
	<td class="cer_display_thread_header">
		&nbsp;{$oThread->date_string} - {$smarty.const.LANG_DISPLAY_TIME_TRACKING_TITLE}
	</td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	
	<form action="display.php" name="thread_time_{$oThread->thread_time_id}" method="post">
	<input type="hidden" name="form_submit" value="thread_time_edit">
	<input type="hidden" name="thread_time_id" value="{$oThread->thread_time_id}">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="sid" value="{$session_id}">

	<tr bgcolor="#FFFFFF">
		<td class="cer_maintable_text">
			<table width="100%" cellspacing="1" cellpadding="2" border="0">
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
					{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_DATE}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<input type="text" name="thread_time_date" size="8" maxlength="8" value="{$oThread->date_mdy}">
						<a href="javascript:calendarPopUp('document.thread_time_{$oThread->thread_time_id}.thread_time_date');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						<span class="cer_footer_text">(<B><i>mm/dd/yy</i></B>)</span>
						
						<select name="thread_time_h">
							{html_options options=$o_ticket->timestamp_select->hrs_opts selected=$oThread->date_hr}
						</select>:<select name="thread_time_m">
							{html_options options=$o_ticket->timestamp_select->mins_opts selected=$oThread->date_min}
						</select><select name="thread_time_ampm">
							{html_options options=$o_ticket->timestamp_select->ampm_opts selected=$oThread->date_ampm}
						</select>
						
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_DATE_BILLED}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<input type="text" name="thread_time_date_billed" size="8" maxlength="8" value="{$oThread->date_billed_mdy}">
						<a href="javascript:calendarPopUp('document.thread_time_{$oThread->thread_time_id}.thread_time_date_billed');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						<span class="cer_footer_text">(<B><i>mm/dd/yy</i></B>)</span>
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
					{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_AGENT}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						{if $ticket_glance_owner_options}
							<select name="thread_time_working_agent_id">
								{html_options options=$ticket_glance_owner_options selected=$oThread->working_agent_id}
							</select>
						{/if}
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
					{$smarty.const.LANG_WORD_HOURS}:<br>
						<span class="cer_footer_text">(for example: <B>1.5</B>)</span>
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<table border="0" cellpadding="1" cellspacing="0">
							<tr>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_WORKED}:</span> <input name="thread_time_hrs_spent" type="text" size="5" value="{$oThread->hrs_spent}" onfocus="javascript:doTimeEntryAddHelp('time_edit_{$oThread->thread_time_id}_help',0);">&nbsp;</td>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_CHARGEABLE}:</span> <input name="thread_time_hrs_chargeable" type="text" size="5" value="{$oThread->hrs_chargeable}" onfocus="javascript:doTimeEntryAddHelp('time_edit_{$oThread->thread_time_id}_help',1);">&nbsp;</td>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_BILLABLE}:</span> <input name="thread_time_hrs_billable" type="text" size="5" value="{$oThread->hrs_billable}" onfocus="javascript:doTimeEntryAddHelp('time_edit_{$oThread->thread_time_id}_help',2);">&nbsp;</td>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_PAYABLE}:</span> <input name="thread_time_hrs_payable" type="text" size="5" value="{$oThread->hrs_payable}" onfocus="javascript:doTimeEntryAddHelp('time_edit_{$oThread->thread_time_id}_help',3);">&nbsp;</td>
							</tr>
						</table>
						
						{* Time Entry Help Section *}
						<div id="time_edit_{$oThread->thread_time_id}_help_0" style="display:none;"><B>{$smarty.const.LANG_WORD_WORKED}:</B> The actual hours worked by the agent.</div>
						<div id="time_edit_{$oThread->thread_time_id}_help_1" style="display:none;"><B>{$smarty.const.LANG_WORD_CHARGEABLE}:</B> The amount of hours chargeable to the client (e.g.: minus lunch breaks, etc.)</div>
						<div id="time_edit_{$oThread->thread_time_id}_help_2" style="display:none;"><B>{$smarty.const.LANG_WORD_BILLABLE}:</B> The amount of hours originally quoted to the client (if any).</div>
						<div id="time_edit_{$oThread->thread_time_id}_help_3" style="display:none;"><B>{$smarty.const.LANG_WORD_PAYABLE}:</B> The amount of hours payable to the agent.</div>
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_SUMMARY}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<textarea name="thread_time_summary" width="100%" rows="3">{$oThread->summary|short_escape}</textarea>
					</td>
				</tr>
				
				{* [JAS]: Display custom fields if they are bound to time entries *}
				
				{if $oThread->custom_fields}
					<input type="hidden" name="thread_time_custom_inst_id" value="{$oThread->custom_fields->group_instance_id}">
					
					{foreach from=$oThread->custom_fields->fields name=custom item=field}
						<tr>
							<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
								{$field->field_name}:
							</td>
							<td bgcolor="#E0E0E0" width="99%">
			                  	{if $field->field_type == "S"}
			                    	<input type="text" name="thread_time_custom_{$field->field_id}" size="65" value="{$field->field_value|short_escape}" class="cer_custom_field_text">
			                    {/if}
			
				              	{if $field->field_type == "E"}
									<input type="text" name="thread_time_custom_{$field->field_id}" maxlength="8" size="8" value="{$field->field_value}">
									<a href="javascript: calendarPopUp('document.thread_time_{$oThread->thread_time_id}.thread_time_custom_{$field->field_id}');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						          	<span class="cer_footer_text">
										(use calendar -or- enter <b><i>mm/dd/yy</i></b>)
						          	</span>
				                {/if}
			                    
			                  	{if $field->field_type == "T"}
			                    	<textarea cols="65" rows="3" name="thread_time_custom_{$field->field_id}" wrap="virtual" class="cer_custom_field_text">{$field->field_value|short_escape}</textarea><br>
			                    	<span class="cer_footer_text">(maximum 255 characters)</span>
			                    {/if}
			                    
			                  	{if $field->field_type == "D"}
			                    	<select name="thread_time_custom_{$field->field_id}" class="cer_custom_field_text">
				                      <option value="">
				                      {html_options options=$field->field_options selected=$field->field_value}
			                        </select>
			                    {/if}
							</td>
						</tr>
					{/foreach}
				{/if}
				
				{if $priv->has_priv(ACL_TIME_TRACK_DELETE_ALL,BITGROUP_3) 
					|| ($priv->has_priv(ACL_TIME_TRACK_DELETE_OWN,BITGROUP_3) && $oThread->working_agent_id == $session->vars.login_handler->user_id) }
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						Delete:					
					</td>
					<td bgcolor="#E0E0E0" width="99%" class="cer_maintable_text">
						<input type="text" name="thread_time_delete" size="3" maxlength="3"> 
						<span class="cer_footer_text">{$smarty.const.LANG_DISPLAY_TIME_TRACKING_CONFIRM_DEL}</span>
					</td>
				</tr>
				{/if}
				
				<tr>
					<td colspan="2" bgcolor="#BBBBBB" align="right">
						<input type="submit" value="Update" class="cer_footer_text">
						<input type="button" value="Cancel" class="cer_footer_text" onclick="javascript:toggleThreadTime({$oThread->thread_time_id});">
					</td>
				</tr>
				
			</table>
		</td>
	</tr>
	
	</form>
	
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td align="right"><a href="#top" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_BACK_TO_TOP|lower}</a></td></tr>
</table>

<br>
</div>