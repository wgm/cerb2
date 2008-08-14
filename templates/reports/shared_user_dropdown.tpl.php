<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top">
			<span class="cer_maintable_heading">Filter by Agent:</span><br>
			<select name="report_user_id" class="cer_footer_text">
			<option value='-1'> - any agent -
				{html_options options=$report->report_data->user_data->user_options selected=$report->report_data->user_data->report_user_id}
			</select>
			</td>
	</tr>
</table>