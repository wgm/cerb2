<table width="100%" border="0" cellspacing="0" cellpadding="2">
<form action="display.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
<input type="hidden" name="mode" value="properties">
<input type="hidden" name="qid" value="{$o_ticket->ticket_queue_id}">
<input type="hidden" name="form_submit" value="properties">

<input type="hidden" name="initial_owner" value="{$o_ticket->ticket_assigned_to_id}">
<input type="hidden" name="initial_status" value="{$o_ticket->ticket_status}">
<input type="hidden" name="initial_queue" value="{$o_ticket->ticket_queue_id}">
<input type="hidden" name="initial_priority" value="{$o_ticket->ticket_priority}">
  <tr> 
	<td>
      <table width="100%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
        <tr class="boxtitle_blue_glass_dk"> 
          <td colspan="2">{$smarty.const.LANG_WORD_TICKET} #{$o_ticket->ticket_mask_id} {$smarty.const.LANG_DISPLAY_PROPS}</td>
        </tr>
        <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
          <td colspan="2" valign="top" align="left"> 
              <table width="100%" border="0" cellspacing="0" cellpadding="2">
                <tr> 
                  <td class="cer_maintable_heading" colspan="5">{$smarty.const.LANG_DISPLAY_PROPS_TICKET_SUBJECT}</td>
                </tr>
                <tr> 
                  <td colspan="5">
                  	{if $o_ticket->properties->show_chsubject !== false}
                    	<input type="text" name="ticket_subject" size="65" value="{$o_ticket->ticket_subject|short_escape}">
                    {else}
                    	{$o_ticket->ticket_subject|short_escape} 
                    	<input type="hidden" name="ticket_subject" value="{$o_ticket->ticket_subject|short_escape}"> 
                    {/if}
                  </td>
                </tr>
                <tr class="cer_maintable_heading"> 
                  <td>{$smarty.const.LANG_DISPLAY_PROPS_TICKET_STATUS}</td>
                  <td>{$smarty.const.LANG_DISPLAY_PROPS_TICKET_OWNER}</td>
                  <td>{$smarty.const.LANG_DISPLAY_PROPS_TICKET_QUEUE}</td>
                  <td>{$smarty.const.LANG_DISPLAY_PROPS_TICKET_PRIORITY}</td>
                  <td>&nbsp;</td>
                </tr>
								<tr> 
                  <td valign="top">
                  {if $o_ticket->properties->show_chstatus !== false}
					<select name="ticket_status">
					  {html_options options=$ticket_glance_status_options selected=$o_ticket->ticket_status}
					</select>
                  {else}
                    {$o_ticket->ticket_status}
                    <input type='hidden' name='ticket_status' value='{$o_ticket->ticket_status|short_escape}'> 
                  {/if}
                  </td>
                  <td valign="top">
                  {if $o_ticket->properties->show_chowner !== false}
					<select name="ticket_owner">
						{html_options options=$ticket_glance_owner_options selected=$o_ticket->ticket_assigned_to_id}
					</select>
				  {else}
					{if $o_ticket->ticket_assigned_to_id == 0 }
						{$smarty.const.LANG_WORD_NOBODY}
						<input type="hidden" name="ticket_owner" value="0">
					{else}
						{$o_ticket->ticket_owner}
						<input type="hidden" name="ticket_owner" value="{$o_ticket->ticket_assigned_to_id}">
					{/if}
				  {/if}
				  </td>
                  <td valign="top">
                  {if $o_ticket->properties->show_chqueue !== false}
					<select name="ticket_queue">
						{html_options options=$ticket_glance_queue_options selected=$o_ticket->ticket_queue_id}
					</select>
                  {else}
                  	 {$o_ticket->ticket_queue_name}
                  	 <input type='hidden' name='ticket_queue' value='{$o_ticket->ticket_queue_id}'>
                  {/if}
                  </td>
                  <td valign="top">
                  {if $o_ticket->properties->show_chpriority !== false}	
                      <select name="ticket_priority">  {* [JSJ]: Changed to drop down menu *}
                         {html_options options=$ticket_glance_priority_options selected=$o_ticket->ticket_priority}
                      </select>
                  {else}
                      {$o_ticket->ticket_priority_string}
                 	<input type='hidden' name='ticket_priority' value='{$o_ticket->ticket_priority}'>
                  {/if}
                  </td>
                  <td>&nbsp;</td>
                </tr>
              </table>
          </td>
        </tr>
      </table>
	  <table border=0 cellspacing=0 cellpadding=4 width="100%">
       	{if $o_ticket->writeable !== false}
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
          <td align="right">
        	<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_DISPLAY_PROPS_SUBMIT}">
          </td>
      	</tr>
		{/if}
	  </table>
	</td>
  </tr>
</form>
</table>

<BR>
