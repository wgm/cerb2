<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top">
			<span class="cer_maintable_heading">Filter by Queue:</span><br>
			<select name="report_queue_id" class="cer_footer_text">
			<option value='-1'>- any queue -
				{html_options options=$report->report_data->queue_data->queue_list selected=$report->report_data->queue_data->report_queue_id}
			</select>
			</td>
	</tr>
</table>