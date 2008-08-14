<span class="cer_display_header">{$smarty.const.LANG_REPORTS_HEADING}</span><br>
<span class="cer_maintable_text">{$smarty.const.LANG_REPORTS_INSTRUCTIONS}</span><br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_green_glass"> 
    <td>&nbsp;{$smarty.const.LANG_REPORTS_SYSTEMREPORTS}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text">
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left">
    
    	<table cellpadding="0" cellspacing="0" border="0" width="100%">
    		{foreach item=report from=$report_list->reports}
    		<tr>
    			<td class="cer_maintable_text" valign="middle" align="top">
    				&nbsp;<a href="{$report->report_url}" class="cer_maintable_heading">{$report->report_name}</a>
    				 -- {$report->report_summary}
    			</td>
    		</tr>
		  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		  	{/foreach}
		  	
		  	{if empty($report_list->reports) }
			  	<tr><td colspan="{$col_span}" class="cer_maintable_text">{$smarty.const.LANG_REPORTS_NOREPORTSAVAILABLE}</td></tr>
			{/if}
    	</table>
    	
    </td>
  </tr>
</table>
