{assign var=col_span value=7}
<table cellpadding=0 cellspacing=0 border=0 style="border: 1px solid #888888;">
	
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	<tr class="boxtitle_blue_glass">
		<td align="center"><a href="{$cal->urls.prev_mo}" class="cer_maintable_header">&lt;</a></td>
		<td colspan='5' align=center>
			{$cal->cal_month_name} {$cal->cal_year}
		</td>
		<td align="center"><a href="{$cal->urls.next_mo}" class="cer_maintable_header">&gt;</a></td>
	</tr>
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	
	<tr class='cer_footer_text' bgcolor='bbbbbb'>
		<td width="15%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_SUN}&nbsp;</B></td>
		<td width="14%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_MON}&nbsp;</B></td>
		<td width="14%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_TUE}&nbsp;</B></td>
		<td width="14%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_WED}&nbsp;</B></td>
		<td width="14%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_THU}&nbsp;</B></td>
		<td width="14%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_FRI}&nbsp;</B></td>
		<td width="15%"><B>&nbsp;{$smarty.const.LANG_CHOOSEDATE_SAT}&nbsp;</B></td>
	</tr>
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	{section name=week loop=$cal->cal_matrix}
	
			<tr bgcolor='{if $cal->cal_matrix[week]->is_this_week ===true}#6CC8FC{else}#eeeeee{/if}'>
			
			{section name=day loop=$cal->cal_matrix[week]->days}
				<td align="center" valign="middle" style="padding:2px;">{if $cal->cal_matrix[week]->days[day]->day != 0}<a href="{$cal->cal_matrix[week]->days[day]->day_url}" class='{if $cal->is_this_month === true && $cal->cur_day == $cal->cal_matrix[week]->days[day]->day}cer_maintable_header{else}cer_footer_text{/if}'>{$cal->cal_matrix[week]->days[day]->day}</a>{/if}</td>
			{/section}
			
			</tr>

	{/section}

	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		
</table>