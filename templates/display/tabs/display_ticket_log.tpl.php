<input type="hidden" name="sid" value="{$session_id}">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<form>
  <tr> 
		<td>
	      <table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr class="boxtitle_blue_glass_pale">
          	<td>&nbsp;{$smarty.const.LANG_AUDIT_LOG_TITLE}</td>
          </tr>
          <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		  <tr bgcolor="#DDDDDD">
          	<td>
          	<table cellspacing="0" cellpadding="0" width="100%" border="0">
			{section name=item loop=$o_ticket->log->entries}
          		<tr bgcolor="#dddddd"> 
                    <td class="cer_footer_text" bgcolor="#cccccc" width="5%" align="left" nowrap style="padding-left: 2px; padding-right: 2px;">
	                    <b>{$o_ticket->log->entries[item]->log_timestamp}:</b>
                    </td>
			        <td bgcolor="#888888" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
                    <td class="cer_footer_text" width="95%" style="padding-left: 2px; padding-right: 2px;">
				      	{$o_ticket->log->entries[item]->log_text}
                    </td>
                  </tr>
			  	  <tr><td colspan="3" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
			 {sectionelse}
			  	  <tr><td class="cer_footer_text" bgcolor="#dddddd"><b>No log entries</b></td></tr>
			 {/section}
			 </table>
            </td>
          </tr>
		<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
        </table>        
		</td>
  </tr>
</form>
</table>