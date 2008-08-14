{literal}
<script>
	function toggleDisplayGlance() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_glance").style.display=="block") {
				document.getElementById("ticket_display_glance").style.display="none";
				document.getElementById("ticket_display_glance_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_glance.value = 0;
			}
			else {
				document.getElementById("ticket_display_glance").style.display="block";
				document.getElementById("ticket_display_glance_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_glance.value = 1;
			}
		}
	}
</script>
{/literal}

    <td width="55%" valign="top">

	<table cellpadding="2" cellspacing="0" border="0" width='100%'>
		<tr class="boxtitle_blue_glass">
			<td width="99%">
				{$smarty.const.LANG_DISPLAY_GLANCE}
			</td>
			<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_glance_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_glance}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayGlance();" onmouseover="javascript:this.style.cursor='hand';"></td>
		</tr>
	</table>

	<div id="ticket_display_glance" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_glance}block{else}none{/if};">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
		<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

        <tr bgcolor="#CECECE"> 
          <td width="13%" class="cer_maintable_heading" align="right">&nbsp;{$smarty.const.LANG_WORD_ID}&nbsp;</td>
          <td width="10%" class="cer_maintable_heading">&nbsp;{$smarty.const.LANG_WORD_STATUS}&nbsp;</td>
		  <td width="18%" class="cer_maintable_heading">&nbsp;{$smarty.const.LANG_WORD_OWNER}&nbsp;</td>
          <td width="18%" class="cer_maintable_heading">&nbsp;{$smarty.const.LANG_WORD_WORKED}&nbsp;</td>
          <td width="13%" class="cer_maintable_heading">&nbsp;{$smarty.const.LANG_WORD_PRIORITY}&nbsp;</td>
          <td width="28%" class="cer_maintable_heading">&nbsp;{$smarty.const.LANG_WORD_QUEUE}&nbsp;</td>
        </tr>
		<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

		{* Ticket At a Glance - Quick Edit Controls *}
		<tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
		
		  {* Ticket ID *}
		  <td width="13%" bgcolor="#EEEEEE" class="cer_maintable_text" align="right"> 
            {$o_ticket->ticket_id}&nbsp;
          </td>

          {* Ticket Status *}
          <td width="10%" class="cer_maintable_text" align="left">
			<input type="hidden" name="initial_status" value="{$o_ticket->ticket_status}">
			{if $ticket_glance_status_options}
				<select name="ticket_status">
				  {html_options options=$ticket_glance_status_options selected=$o_ticket->ticket_status}
				</select>
			{else}
				{$o_ticket->ticket_status_translated}
				<input type="hidden" name="ticket_status" value="{$o_ticket->ticket_status}">
			{/if}
          </td>

          {* Ticket Owner *}

          <td width="18%" class="cer_maintable_text" align="left">

			<input type="hidden" name="initial_owner" value="{$o_ticket->ticket_assigned_to_id}">

			{if $ticket_glance_owner_options && $o_ticket->writeable}

				<select name="ticket_owner">

					{html_options options=$ticket_glance_owner_options selected=$o_ticket->ticket_assigned_to_id}

				</select>

			{else}

				{if $o_ticket->ticket_assigned_to_id == 0}

	            	{$smarty.const.LANG_WORD_NOBODY}

	            	<input type="hidden" name="ticket_owner" value="0">

	            {else}

	            	&nbsp;{$o_ticket->ticket_owner}

	            	<input type="hidden" name="ticket_owner" value="{$o_ticket->ticket_assigned_to_id}">

	            {/if}

			{/if}

          </td>

          

          {* Ticket Time Worked *}

          <td width="18%" class="cer_maintable_text" align="left">&nbsp;

            {$o_ticket->time_worked}

          </td>

          

          {* Ticket Priority *}

		  <input type="hidden" name="initial_priority" value="{$o_ticket->ticket_priority}">

		  <td width="13%" class="cer_maintable_text">

		   {if $ticket_glance_priority_options}

		   <select name="ticket_priority">  {* [JSJ]: Changed to drop down menu *}

		     {html_options options=$ticket_glance_priority_options selected=$o_ticket->ticket_priority}

		   </select>

		   {else}

		 	<input type="hidden" name="ticket_priority" value="{$o_ticket->ticket_priority}">

		  	&nbsp;{$o_ticket->ticket_priority_string}

		  {/if}

  		  </td>

  		   

          {* Ticket Queue *}

          <td width="28%" class="cer_maintable_text">

			<input type="hidden" name="initial_queue" value="{$o_ticket->ticket_queue_id}">

			{if $ticket_glance_queue_options}

				<select name="ticket_queue">

					{html_options options=$ticket_glance_queue_options selected=$o_ticket->ticket_queue_id}

				</select>

			{else}

				{$o_ticket->ticket_queue_name}

				<input type="hidden" name="ticket_queue" value="{$o_ticket->ticket_queue_id}">

			{/if}

          </td>

        </tr>

        

	 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	 	

	 	<tr>

	 		<td colspan="{$col_span}" bgcolor="#EEEEEE">

	 			<table border="0" cellpadding="2" cellspacing="0" bgcolor="#FFFFFF">

		 			<tr>

		 				<td bgcolor="#{if $o_ticket->ticket_spam_rating > 90}FF0000{else}00BB00{/if}">

		 					<span class="cer_maintable_header" valign="top">{$o_ticket->ticket_spam_rating|string_format:"%0.2f"}%</span>

		 				</td>

		 				<td bgcolor="#EEEEEE">

		 					<span class="cer_maintable_text" valign="top"><b>{$smarty.const.LANG_WORD_SPAM_PROBABILITY}</b></span>

		 					<span class="cer_footer_text">(<a class="cer_footer_text" href="{$urls.tab_antispam}">{$smarty.const.LANG_WHY_QUESTION}</a>)</span>

		 				</td>

		 				

		 				<td bgcolor="#DDDDDD" valign="middle">

		 					<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_TRAINING}:</span>

		 				</td>

		 				

		 				{if $o_ticket->ticket_spam_trained == 0}

		 				<td bgcolor="#EEEEEE" class="cer_footer_text" valign="middle">

		 					<select name="ticket_spam" class="cer_footer_text">

		 						<option value="spam" {if $o_ticket->ticket_spam_rating >= 90}selected{/if}>{$smarty.const.LANG_TICKET_SPAM_TRAINING_IS}

		 						<option value="notspam" {if $o_ticket->ticket_spam_rating < 90}selected{/if}>{$smarty.const.LANG_TICKET_SPAM_TRAINING_NOT}

		 					</select>

		 				</td>

		 				{else}

		 				<td bgcolor="#EEEEEE" class="cer_footer_text" valign="middle">

		 					{if $o_ticket->ticket_spam_trained == 1}{$smarty.const.LANG_TICKET_IS_HAM}{else}{$smarty.const.LANG_TICKET_IS_SPAM}{/if}

		 				</td>

		 				{/if}

		 				

		 			</tr>

	 			</table>

	 		</td>

	 	</tr>

 	

	 	{if $o_ticket->writeable}

		  <tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	      <tr bgcolor="#BBBBBB"> 

	        <td colspan="{$col_span}" align="right"><input type="submit" class="cer_button_face" value="{$smarty.const.LANG_DISPLAY_UPDATE}"></td>

	      </tr>

		{/if}

	 	

		<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

		

	  </table>

	  </div>

    </td>

