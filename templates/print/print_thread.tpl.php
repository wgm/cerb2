{foreach name=thread from=$o_ticket->threads item=thread_ptr}
  {if ($thread_ptr->type == "email" || $thread_ptr->type == "comment") && $thread_ptr->ptr->thread_id == $thread}
    <center><form><input type=button onClick="javascript: window.print();" value="{$smarty.const.LANG_ACTION_PRINT_SUBMIT}">&nbsp;&nbsp;<input type=button onClick="javascript: window.close();" value="{$smarty.const.LANG_ACTION_PRINT_CLOSE_WINDOW}"></form></center>
    <center><h1>Ticket #{$o_ticket->ticket_mask_id} - Message #{$thread_ptr->ptr->thread_id}</h1></center>
    <hr size=5 color=black>
    <table>
      <tr><td align=left><b>{$smarty.const.LANG_WORD_FROM}:&nbsp;&nbsp;</b></td><td>{section name=address loop=$o_ticket->requesters->addresses}{$o_ticket->requesters->addresses[address]->address_address}; {/section}</td></tr> 
      <tr><td align=left><b>Queue:&nbsp;&nbsp;</b></td><td>{$o_ticket->ticket_queue_name}</td></tr>
      <tr><td align=left><b>{$smarty.const.LANG_WORD_DATE}:&nbsp;&nbsp;</b></td><td>{$o_ticket->ticket_date}</td></tr>
      <tr><td align=left><b>{$smarty.const.LANG_WORD_SUBJECT}:&nbsp;&nbsp;</b></td><td>{$o_ticket->ticket_subject|short_escape}</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
    </table>
    <table>
      <tr><td>&nbsp;</td><td><hr color=black size=1><b>{$thread_ptr->ptr->thread_display_author} - </b><br><br>{$thread_ptr->ptr->thread_content|replace:"<":"&lt;"|replace:">":"&gt;"|nl2br}<br><hr color=black size=1></td></tr>
    </table>
    <BR>
    <BR>
    <BR>
    <BR>
    <hr size=5 color=black>
  {/if}
{/foreach}
