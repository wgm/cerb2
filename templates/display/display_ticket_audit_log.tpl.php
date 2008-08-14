{if count($o_ticket->log->entries) }

{literal}
<script>
	function toggleDisplayLog() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_log").style.display=="block") {
				document.getElementById("ticket_display_log").style.display="none";
				document.getElementById("ticket_display_log_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_log.value = 0;
			}
			else {
				document.getElementById("ticket_display_log").style.display="block";
				document.getElementById("ticket_display_log_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_log.value = 1;
			}
		}
	}
</script>
{/literal}

<table cellpadding="2" cellspacing="0" border="0" width='100%'>
	<tr class="boxtitle_blue_glass_pale">
		<td width="99%">
			{$smarty.const.LANG_AUDIT_LOG_TITLE_LATEST_5}
		</td>
		<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_log_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_log}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayLog();" onmouseover="javascript:this.style.cursor='hand';"></td>
	</tr>
</table>

<div id="ticket_display_log" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_log}block{else}none{/if};">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	{section name=item loop=$o_ticket->log->entries max=5}
	    <tr bgcolor="#EEEEEE"> 
	      <td class="cer_footer_text" bgcolor="#DDDDDD" width="5%" align="left" style="padding-left: 2px; padding-right: 2px;" nowrap>
	      	<b>{$o_ticket->log->entries[item]->log_timestamp}:</b>
	      </td>
	  	  <td bgcolor="#888888" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	      <td class="cer_footer_text" width="95%" style="padding-left: 2px;">
	      	{$o_ticket->log->entries[item]->log_text}
	      </td>
	    </tr>
	  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  	{/section}
	
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
</div>

<br>
{/if}