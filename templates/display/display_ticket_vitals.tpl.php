{literal}

<script>

	function toggleDisplayVitals() {

		if (document.getElementById) {

			if(document.getElementById("ticket_display_vitals").style.display=="block") {

				document.getElementById("ticket_display_vitals").style.display="none";

				document.getElementById("ticket_display_vitals_icon").src=icon_expand.src;

				document.formSaveLayout.layout_display_show_vitals.value = 0;

			}

			else {

				document.getElementById("ticket_display_vitals").style.display="block";

				document.getElementById("ticket_display_vitals_icon").src=icon_collapse.src;

				document.formSaveLayout.layout_display_show_vitals.value = 1;

			}

		}

	}

</script>

{/literal}



    <td width="45%" valign="top" align="right"> 

    

	<table cellpadding="2" cellspacing="0" border="0" width='98%'>

		<tr class="boxtitle_blue_glass">

			<td width="99%">

				{$smarty.const.LANG_DISPLAY_VITAL_SIGNS}

			</td>

			<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_vitals_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_vitals}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayVitals();" onmouseover="javascript:this.style.cursor='hand';"></td>

		</tr>

	</table>

	

	<div id="ticket_display_vitals" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_vitals}block{else}none{/if};">

	<table width="98%" border="0" cellspacing="0" cellpadding="0" align="right">

	 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

		{* Ticket Created *}

		<tr bgcolor="#EEEEEE" class="cer_maintable_text"> 

          <td bgcolor="#DDDDDD" class="cer_maintable_heading" valign="top" align="left"> 

            &nbsp;{$smarty.const.LANG_DISPLAY_CREATED}:

          </td>

		<td bgcolor="#888888" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>

          <td class="cer_maintable_text" align="left" style="padding-left: 2px;"> 

            {$o_ticket->time_created}

          </td>

        </tr>

	 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>



	 	{* Ticket Due Update *}

	 	{if $o_ticket->ticket_status != "resolved" && $o_ticket->ticket_status != "dead"}

	 	<tr bgcolor="#EEEEEE" class="cer_maintable_text"> 

          <td bgcolor="#DDDDDD" class="cer_maintable_heading" valign="top" align="left">

          	&nbsp;{$smarty.const.LANG_DISPLAY_DUE}:<br>

          	<span class="cer_footer_text">

          	&nbsp;{$smarty.const.LANG_DISPLAY_DUE_INSTRUCTIONS}

          	</span>

          </td>

		  <td bgcolor="#888888" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>

          <td class="cer_maintable_text" style="padding-left: 2px;" nowrap>

          {if !empty($o_ticket->time_due) }

          	{$o_ticket->time_due}

          {else}

          	No due date set.

          {/if}

          	<br>

          	<input type="hidden" name="initial_ticket_due" value="{$o_ticket->ticket_due_str}">

			<input type="text" name="ticket_due_date" maxlength="8" size="8" value="{$o_ticket->ticket_due_date}">
			
			<a href="javascript: calendarPopUp('document.ticket_glance.ticket_due_date');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>

			<br>

			<select name="ticket_due_time_h">

				{html_options options=$o_ticket->timestamp_select->hrs_opts selected=$o_ticket->ticket_due_time_h}

			</select>:<select name="ticket_due_time_m">

				{html_options options=$o_ticket->timestamp_select->mins_opts selected=$o_ticket->ticket_due_time_m}

			</select><select name="ticket_due_time_ampm">

				{html_options options=$o_ticket->timestamp_select->ampm_opts selected=$o_ticket->ticket_due_time_ampm}

			</select><input type="submit" value="Set" class="cer_button_face">

          </td>

        </tr>

	 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	 	{/if}

	 	

	 	{* # of Requesters *}

	 	<tr bgcolor="#EEEEEE" class="cer_maintable_text"> 

          <td bgcolor="#DDDDDD" class="cer_maintable_heading" valign="top" align="left">&nbsp;{$smarty.const.LANG_DISPLAY_NUMBER_REQUESTERS}:</td>

		  <td bgcolor="#888888" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>

          <td class="cer_maintable_text" style="padding-left: 2px;">

          	{$o_ticket->num_requesters} 

          	{if $urls.tab_edit}

          		<span class="cer_footer_text">(<a href="{$urls.tab_edit}" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_EDIT_SENDER}</a>)</span>

          	{/if}

          </td>

        </tr>

	 	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	 	

	 	{* Ticket Users *}

        <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 

          <td bgcolor="#DDDDDD" class="cer_maintable_heading" valign="top" align="left">&nbsp;{$smarty.const.LANG_WORD_TICKET_USERS}:</td>

		  <td bgcolor="#888888" width="1"><img src="includes/images/spacer.gif" height="1" width="1"></td>

          <td class="cer_display_user" style="padding-left: 2px;">

          {section name=user loop=$o_ticket->ticket_users}

          	<b>{$o_ticket->ticket_users[user]->user_login}</b> {$o_ticket->ticket_users[user]->user_what}{if !%user.last%}, {/if}

          {sectionelse}

          	{$smarty.const.LANG_WORD_NONE}

          {/section}

          </td>

        </tr>

		<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

      </table>

      </div>

    </td>

          