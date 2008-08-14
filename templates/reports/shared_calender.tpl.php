<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top">
			{include file="my_cerberus/tabs/my_cerberus_dashboard_calendar.tpl.php" cal=$report_list->report->report_data->cal}
		</td>
		<td><img src="includes/images/spacer.gif" width="10" height="1"></td>
		<td valign="top">
			<span class="cer_maintable_heading">Use the calendar to the left to choose which month or day to display a report for, or choose a quick option below.</span><br>
			<br>
			{section name=link loop=$report_list->report->report_data->quick_links}
				<a class="cer_maintable_heading" href="{$report_list->report->report_data->quick_links[link]->link_url}">{$report_list->report->report_data->quick_links[link]->link_name}</a><br>
			{/section}
			<br>
			<input type="hidden" name="mo_m" value="">
			<input type="hidden" name="mo_d" value="">
			<input type="hidden" name="mo_y" value="">
			<span class="cer_maintable_heading">Enter Date Range:</span>
			
			<input type="text" name="from_date" value="{$report_list->report->report_dates->from_date_calender}" size="8" maxlength="8">
			
			<span class="cer_maintable_text">to</span>
			
			<input type="text" name="to_date" value="{$report_list->report->report_dates->to_date_calender}" size="8" maxlength="8">
			<span class="cer_footer_text">(enter as mm/dd/yy)</span>
			
		</td>
		
	</tr>
</table>