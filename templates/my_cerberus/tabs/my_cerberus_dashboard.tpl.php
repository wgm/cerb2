<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="1%" nowrap valign="top">
		{include file=my_cerberus/tabs/my_cerberus_dashboard_calendar.tpl.php cal=$dashboard->cal}
	</td>
	<td width="1%" nowrap><img src="includes/images/spacer.gif" width="10" height="1"></td>
	<td valign="top" width="98%">

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	  <tr class="boxtitle_green_glass"> 
	    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_DASHBOARD_MYPERFORMANCE}</td>
	  </tr>
	  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
	    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
		<table cellspacing="0" cellpadding="0" width="100%" border="0">
			<tr>
				<td width="1%" nowrap class="cer_maintable_heading" bgcolor="#CCCCCC">&nbsp;{$smarty.const.LANG_MYCERBERUS_DASHBOARD_ASSIGNEDACTIVE}:&nbsp;</td>
				<td width="99%" class="cer_maintable_text"><b>{$dashboard->stats.active_tickets_assigned}</b>
				<span class="cer_footer_text">
					(<a href="{$dashboard->urls.my_tickets}" class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_DASHBOARD_LISTMYACTIVE}:</a>) 
					<b>{$dashboard->stats.my_percentage}%</b> ({$dashboard->stats.active_tickets_assigned} {$smarty.const.LANG_WORD_OF} {$dashboard->stats.active_tickets} {$smarty.const.LANG_MYCERBERUS_DASHBOARD_OFACTIVETICKETS})
				</span>
				</td>
			</tr>
			<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
			
			{if $dashboard->stats.latest_ticket_id != 0}
			<tr>
				<td width="1%" nowrap class="cer_maintable_heading" bgcolor="#CCCCCC">&nbsp;{$smarty.const.LANG_MYCERBERUS_DASHBOARD_OLDESTASSIGNED}:&nbsp;</td>
				<td class="cer_footer_text">[<a href="{$dashboard->urls.latest_ticket}" class="cer_footer_text">#{$dashboard->stats.latest_ticket_id|string_format:"%05d"}</a>]: {$dashboard->stats.latest_ticket_subject|short_escape} (<b>{$dashboard->stats.latest_ticket_age}</b> {$smarty.const.LANG_WORD_OLD})</span></td>
			</tr>
			<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
			{/if}
			
			<tr>
				<td width="1%" nowrap class="cer_maintable_heading" bgcolor="#CCCCCC" valign="top">&nbsp;{$smarty.const.LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY}&nbsp;<br>
				<span class="cer_footer_text">&nbsp;{$smarty.const.LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_REPLIESCOMMENTS}</span></td>
				<td class="cer_footer_text">
				<table border="0" cellpadding="0" cellspacing="1">
					{if count($dashboard->snapshot)}
						<tr>
							<td class="cer_footer_text" align="left"></td>
							<td class="cer_footer_text" align="center">&nbsp;<B>{$smarty.const.LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_EMAIL}</B>&nbsp;</td>
							<td class="cer_footer_text" align="center">&nbsp;<B>{$smarty.const.LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_COMMENTS}</B>&nbsp;</td>
						</tr>
					{/if}
					{section name=day loop=$dashboard->snapshot}
						<tr>
							<td class="cer_footer_text" bgcolor="#D0D0D0" align="left"><b>{$dashboard->snapshot[day]->day_str}:</b></td>
							<td class="cer_footer_text" align="center">{$dashboard->snapshot[day]->day_email_count}</td>
							<td class="cer_footer_text" align="center">{$dashboard->snapshot[day]->day_comment_count}</td>
						</tr>
					{sectionelse}
						<tr><td class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_NODATA}</td></tr>
					{/section}
				</table>
				</td>
			</tr>
			
		</table>
	    </td>
	  </tr>
	  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	</table>
	</td>
</tr>
</table>

<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_orange_glass"> 
    <td>&nbsp;{$dashboard->last_actions_title}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="cer_maintable_text"> 
    <td bgcolor="#FFFFFF" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		{section name=row loop=$dashboard->last_actions}
		<tr bgcolor="#DDDDDD">
			<td width="1%" nowrap class="cer_footer_text" bgcolor="#CCCCCC" style="padding-left: 1px;" class="cer_footer_text">#{$dashboard->last_actions[row]->ticket_id|string_format:"%05d"}:</td>
			<td width="1%" nowrap style="padding-left: 5px;"><a href="{$dashboard->last_actions[row]->ticket_url}" class="cer_maintable_heading">{$dashboard->last_actions[row]->ticket_subject|short_escape}</a></td>
			<td width="98%" class="cer_footer_text" style="padding-left: 5px;">{$dashboard->last_actions[row]->ticket_status}</td>
		  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		</tr>
		{sectionelse}
		<tr>
			<td bgcolor="#DDDDDD" class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_DASHBOARD_TICKETHISTORY_NO}</td>
		</tr>
	  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{/section}
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>
