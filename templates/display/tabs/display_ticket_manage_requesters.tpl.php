<table width="100%" border="0" cellspacing="0" cellpadding="2">
<form action="display.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
<input type="hidden" name="qid" value="{$o_ticket->ticket_queue_id}">
<input type="hidden" name="mode" value="properties">
<input type="hidden" name="form_submit" value="requesters">
  <tr> 
	<td>
      <table width="100%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
        <tr class="boxtitle_blue_glass"> 
          <td colspan="2">{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_HEADER}</td>
        </tr>
        <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
          <td colspan="2" class="cer_maintable_text" valign="top" align="left"> 
			<span class="cer_maintable_heading">{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_INSTRUCTIONS}</span><br>
              <table border="0" cellspacing="2" cellpadding="1">
              		<tr>
              			<td bgcolor="#666666" class="cer_maintable_header" align="center" nowrap>{$smarty.const.LANG_WORD_DELETE}</td>
              			<td bgcolor="#666666" class="cer_maintable_header">{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_REQUESTER_ADDRESS}</td>
              			<td bgcolor="#666666" class="cer_maintable_header">{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_SUPPRESS}</td>
              		</tr>

              		{* Loop through Requester Addresses *}
              		{section name=req loop=$o_ticket->requesters->addresses}
      					<tr>
      						<td nowrap align="center" class="cer_maintable_text">
      						{if $o_ticket->requestor_address->address_id != $o_ticket->requesters->addresses[req]->address_id}
      							<input type="checkbox" name="req_ids[]" value="{$o_ticket->requesters->addresses[req]->address_id}">
							{else}
								<b>{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_PRIMARY}</b>
							{/if}
							</td>
      						<td class="cer_maintable_text">
      							{$o_ticket->requesters->addresses[req]->address_address|short_escape}
      						</td>
      						<td class="cer_maintable_text" align="center">
      							<input type="checkbox" name="req_suppress_ids[]" value="{$o_ticket->requesters->addresses[req]->address_id}" {if $o_ticket->requesters->addresses[req]->suppress}CHECKED{/if}>
      						</td>
      					</tr>
              		{/section}
              		
              		{if $o_ticket->properties->show_add_requester}
              		<tr>
              			<td colspan="2" valign="top">
              				<span class="cer_maintable_heading">{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_ADD}</span> 
              				<input type="text" size="45" name="req_address" value=""><br>
              				<span class="cer_footer_text">{$smarty.const.LANG_ACTION_MANAGE_REQUESTERS_ADD_INSTRUCTIONS}</span>
              			</td>
              		</tr>
              		{/if}
              </table>
          </td>
        </tr>
      </table>
	  <table border=0 cellspacing=0 cellpadding=4 width="100%">
        {if $o_ticket->writeable !== false}
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right">
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
      	{/if}
	  </table>
	</td>
  </tr>
</form>
</table>

<br>