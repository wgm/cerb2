{literal}
<script>
	function toggleDisplaySuggestions() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_suggestions").style.display=="block") {
				document.getElementById("ticket_display_suggestions").style.display="none";
				document.getElementById("ticket_display_suggestions_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_suggestions.value = 0;
			}
			else {
				document.getElementById("ticket_display_suggestions").style.display="block";
				document.getElementById("ticket_display_suggestions_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_suggestions.value = 1;
			}
		}
	}
	
	function toggleDisplaySuggestionsHelp() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_suggestions_help").style.display=="block") {
				document.getElementById("ticket_display_suggestions_help").style.display="none";
			}
			else {
				document.getElementById("ticket_display_suggestions_help").style.display="block";
			}
		}
	}
</script>
{/literal}

<table cellpadding="2" cellspacing="0" border="0" width='100%'>
	<tr class="boxtitle_blue_glass_dk">
		<td width="99%">
	      	{$smarty.const.LANG_FNR_TITLE}
		</td>
		<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_suggestions_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_suggestions}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplaySuggestions();" onmouseover="javascript:this.style.cursor='hand';"></td>
	</tr>
</table>

<div id="ticket_display_suggestions" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_suggestions}block{else}none{/if};">
<table cellspacing="0" cellpadding="0" width="100%" border="0">
{if count($o_ticket->suggestions) }

	<form action="display.php" method="post">
    <input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
    <input type="hidden" name="req_id" value="{$o_ticket->requestor_address->address_id}">
    <input type="hidden" name="sid" value="{$session_id}">
    <input type="hidden" name="form_submit" value="kb_suggestion_submit">
	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	<tr>
	  <td class="cer_maintable_heading" bgcolor="#D0D0D0" width="5%" align="center" style="padding-left: 2px; padding-right: 2px;" nowrap>Train</td>
	  <td bgcolor="#FFFFFF" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	  <td class="cer_maintable_heading" bgcolor="#D0D0D0" width="5%" align="center" style="padding-left: 2px; padding-right: 2px;" nowrap>Source</td>
	  <td bgcolor="#FFFFFF" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	  <td class="cer_maintable_heading" bgcolor="#D0D0D0" width="5%" align="center" style="padding-left: 2px; padding-right: 2px;" nowrap>Method</td>
	  <td bgcolor="#FFFFFF" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	  <td class="cer_maintable_heading" bgcolor="#D0D0D0" width="85%" align="left" style="padding-left: 2px; padding-right: 2px;" nowrap>Resource</td>
	</tr>
  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	{foreach item=item name=item from=$o_ticket->suggestions max=5}
	    <tr bgcolor="#EEEEEE">
	  	  <td class="cer_footer_text" bgcolor="#EEEEEE" width="5%" valign="top" align="center" style="padding-left: 2px; padding-right: 2px;" nowrap>
	  	  {if 1==$item->trained }
	  	    done
	  	  {else}
	  	  	<input type="checkbox" name="kb_suggestion[]" value="{$item->kb_id}">
	  	  {/if}
	  	  </td>
	      <td bgcolor="#FFFFFF" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	      <td class="cer_footer_text" bgcolor="#EEEEEE" width="5%" align="center" style="padding-left: 2px; padding-right: 2px;" nowrap>
	      	<b>KB</b>
	      </td>
	  	  <td bgcolor="#FFFFFF" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	      <td class="cer_footer_text" bgcolor="#EEEEEE" width="5%" align="center" style="padding-left: 2px; padding-right: 2px;" nowrap>
	      	{$item->method}
	      </td>
	  	  <td bgcolor="#FFFFFF" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>
	      <td width="85%" style="padding-left: 2px;">
	      	<span class="cer_footer_text">({$item->score}%)</span> <a class="cer_maintable_subjectLink" href="{$item->url}" target="blank">{$item->subject}</a>
	      </td>
	  	  </tr>
	  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  	{/foreach}	
  	<tr>
      <td colspan="{$col_span}" class="cer_footer_text" valign="middle" bgcolor="#CCCCCC">
	  	&nbsp;<select name="kb_teaching" size="1">
	  		<option value="choose" checked>- choose an action -</option>
	  		<option value="good">{$smarty.const.LANG_FNR_HELPFUL}</option>
	  		<option value="bad">{$smarty.const.LANG_FNR_NOT_HELPFUL}</option>
	  	</select>
	  	&nbsp;<input type="submit" class="cer_button_face" value="Teach"> 
	  	&nbsp;<span class="cer_maintable_text">(<a href="javascript:toggleDisplaySuggestionsHelp();" class="cer_footer_text">{$smarty.const.LANG_FNR_SUGGEST_ARTICLE}</a>)</span>
      </td>
    </tr>  	
	</form>
{else}
    <tr bgcolor="#dddddd">
      <td class="cer_footer_text" colspan="{$col_span}" bgcolor="#dddddd" align="left" style="padding-left: 2px; padding-right: 2px;" nowrap>
      	<b>{$smarty.const.LANG_FNR_NO_ARTICLES}</b>  <span class="cer_maintable_text"><a href="javascript:toggleDisplaySuggestionsHelp();" class="cer_footer_text"><b>{$smarty.const.LANG_FNR_NO_ARTICLES_2}</b></a></span>
      </td>
    </tr>
  	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
 {/if}  	
</table>
 	<div id="ticket_display_suggestions_help" style="display:none;">
    <form action="display.php" method="post">
    <input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
    <input type="hidden" name="req_id" value="{$o_ticket->requestor_address->address_id}">
    <input type="hidden" name="sid" value="{$session_id}">
    <input type="hidden" name="form_submit" value="kb_suggestion_submit">
  	
<table cellspacing="0" cellpadding="0" width="100%" border="0">
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  	<tr>
      <td colspan="{$col_span}" bgcolor="#888888">
        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tr bgcolor="#DDDDDD"> 
            <td bgcolor="#EEEEEE" class="cer_maintable_text">
	            To train Cerberus to intelligently fetch useful articles from the knowledgebase, you can manually suggest articles
	            that would have been helpful in solving this ticket.  By scanning the natural language of inbound e-mail, Cerberus
	            will try to provide the best possible solutions on similar tickets in the future.<br>
            </td>
          </tr>
	      <tr><td bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr> 
            <td bgcolor="#EEEEEE" class="cer_maintable_text">
	            <span class="cer_maintable_text"><B>Didn't get the answer you were looking for?</B>
	            Suggest a helpful knowledge article for tickets like this.  <B>Article ID:</B></span>
	            <input type="text" name="kb_suggestion_id" size="5" maxlength="10" value=""><input type="submit" class="cer_button_face" value="Teach">
            </td>
          </tr>
        </table>
      </td>
    </tr>
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
    <tr> 
      <td class="cer_footer_text" colspan="{$col_span}" width="100%" style="padding-left: 2px;" bgcolor="#AAAAAA"></td>
    </tr>
</table>
	</form>
	</div>
</div>

<br>
