{if count($o_ticket->support_history->history)}

{literal}
<script>
	function toggleDisplayHistory() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_history").style.display=="block") {
				document.getElementById("ticket_display_history").style.display="none";
				document.getElementById("ticket_display_history_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_history.value = 0;
			}
			else {
				document.getElementById("ticket_display_history").style.display="block";
				document.getElementById("ticket_display_history_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_history.value = 1;
			}
		}
	}
</script>
{/literal}

<table cellpadding="2" cellspacing="0" border="0" width='100%'>
	<tr class="boxtitle_orange_glass">
		<td width="99%">
			{$smarty.const.LANG_DISPLAY_CUST_HISTORY}
		</td>
		<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_history_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_history}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayHistory();" onmouseover="javascript:this.style.cursor='hand';"></td>
	</tr>
</table>

<div id="ticket_display_history" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_history}block{else}none{/if};">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  	{section name=item loop=$o_ticket->support_history->history}
	<tr class="{if %item.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
		<td width="1%" nowrap align="right" class="cer_id_text">&nbsp;{$o_ticket->support_history->history[item]->ticket_mask}&nbsp;</td>
		<td width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
		<td width="40%" style="padding-left: 2px; padding-right: 2px;" class="cer_maintable_text">
			<a href="{$o_ticket->support_history->history[item]->ticket_url}" class="cer_maintable_subjectLink">{$o_ticket->support_history->history[item]->ticket_subject|short_escape}</a> 
			({$o_ticket->support_history->history[item]->ticket_status})
		</td>
		<td width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
		<td width="20%" nowrap>&nbsp;<span class="cer_maintable_heading">{$o_ticket->support_history->history[item]->ticket_queue}</span></td>
		<td width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
		<td width="38%">&nbsp;<span class="cer_footer_text">{$o_ticket->support_history->history[item]->ticket_date}</span></td>
	</tr>
 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
 	{/section}
  
    <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	<tr>
		<td colspan="7" align="right" class="cer_footer_text">
			{if $o_ticket->support_history->url_prev}
				<a href="{$o_ticket->support_history->url_prev}" class="cer_maintable_subjectLink">&lt;&lt; {$smarty.const.LANG_WORD_PREV} </a>
			{/if}
			( {$smarty.const.LANG_WORD_SHOWING} {$o_ticket->support_history->history_from}-{$o_ticket->support_history->history_to}
			  {$smarty.const.LANG_WORD_OF} {$o_ticket->support_history->history_total} )
			{if $o_ticket->support_history->url_next}
				<a href="{$o_ticket->support_history->url_next}" class="cer_maintable_subjectLink">{$smarty.const.LANG_WORD_NEXT} &gt;&gt;</a>
			{/if}
		</td>
	</tr>
</table>
</div>
  
<br>
{/if}

