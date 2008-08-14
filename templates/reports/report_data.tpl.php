<table width="100%" border="0" cellspacing="0" cellpadding="0">
{foreach from=$report->report_data->rows item=row name=rows}
<tr bgcolor="{$row->bgcolor}" class="{$row->style}">
	{foreach from=$row->cols item=col name=cols}
		<td align="{$col->align}" valign="{$col->valign}" width="{$col->width}" {$col->nowrap} bgcolor="{$col->bgcolor}" colspan="{$col->col_span}"><span class="{$col->style}">{$col->data}</span></td>
	{/foreach}
</tr>
{/foreach}

{if empty($report->report_data->rows) }
	<tr><td><span class="cer_maintable_text">No data for selected criteria.</span></td></tr>
{/if}

</table>