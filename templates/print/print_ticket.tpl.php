{assign var="show_r_fields" value=$o_ticket->r_field_handler->group_instances}
{assign var="show_t_fields" value=$o_ticket->t_field_handler->group_instances}
<center><form><input type=button onClick="javascript: window.print();" value="{$smarty.const.LANG_ACTION_PRINT_SUBMIT}">&nbsp;&nbsp;<input type=button onClick="javascript: window.close();" value="{$smarty.const.LANG_ACTION_PRINT_CLOSE_WINDOW}">{if $show_r_fields or $show_t_fields}&nbsp;&nbsp;<input type=button onClick="javascript: toggleCustomFields();" value="{$smarty.const.LANG_ACTION_PRINT_HIDE_CUSTOM_FIELDS}">{/if}</form></center>
<center><h1>Ticket #{$o_ticket->ticket_mask_id}</h1></center>
<hr size=5 color=black>
<table>
<tr><td align=left><b>{$smarty.const.LANG_WORD_FROM}:&nbsp;&nbsp;</b></td><td>{section name=address loop=$o_ticket->requesters->addresses}{$o_ticket->requesters->addresses[address]->address_address}; {/section}</td></tr> 
<tr><td align=left><b>Queue:&nbsp;&nbsp;</b></td><td>{$o_ticket->ticket_queue_name}</td></tr>
<tr><td align=left><b>{$smarty.const.LANG_WORD_DATE}:&nbsp;&nbsp;</b></td><td>{$o_ticket->ticket_date}</td></tr>
<tr><td align=left><b>{$smarty.const.LANG_WORD_SUBJECT}:&nbsp;&nbsp;</b></td><td>{$o_ticket->ticket_subject|short_escape}</td></tr>
{if $show_r_fields or $show_t_fields}
{literal}
<script>
	function toggleCustomFields() {
		if (document.getElementById) {
			if(document.getElementById("ticket_custom_fields").style.display=="block") {
				document.getElementById("ticket_custom_fields").style.display="none";
			}
			else {
				document.getElementById("ticket_custom_fields").style.display="block";
			}
		}
	}
</script>
{/literal}
<tr><td colspan=2>
<div id="ticket_custom_fields" style="display:block">
<br>
<hr size=3 color=black>
<table width="100%">
{include file="print/print_custom_fields.tpl.php" field_handler=$o_ticket->r_field_handler}
{if $show_r_fields and $show_t_fields}
<tr><td colspan=2><hr size=3 color=black></td></tr>
{/if}
{if $show_t_fields}
{include file="print/print_custom_fields.tpl.php" field_handler=$o_ticket->t_field_handler}
{/if}
</table>
<hr size=3 color=black>
</div>
</td></tr>
{/if}
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
</table><table>
{foreach name=thread item=thread_ptr from=$o_ticket->threads}
	{if $thread_ptr->type == "email" || $thread_ptr->type == "comment"}
		<tr><td>&nbsp;</td><td><hr color=black size=1><b>{$thread_ptr->ptr->thread_display_author} - </b><br><br>{$thread_ptr->ptr->thread_content|replace:"<":"&lt;"|replace:">":"&gt;"|nl2br}<br><hr color=black size=1></td></tr>
	{/if}
{/foreach}
</table>
<BR>
<BR>
<BR>
<BR>
<hr size=5 color=black>


