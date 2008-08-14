<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top">
			<span class="cer_maintable_heading">Filter by Group:</span><br>
			<select name="report_group_id" class="cer_footer_text">
			<option value='-1'>- any group -
				{html_options options=$report->report_data->group_data->group_list selected=$report->report_data->group_data->report_group_id}
			</select>
			</td>
	</tr>
</table>