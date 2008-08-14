<table width="100%" border="0" cellspacing="0" cellpadding="2">

{if $o_ticket->writeable}
	<form action="display.php" method="post" name="ticket_glance">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="form_submit" value="properties">
	<input type="hidden" name="hp" value="{$hp}">
	<input type="hidden" name="qid" value="{$o_ticket->ticket_queue_id}">
{/if}

	<tr> 
	{* Ticket At a Glance Box *}
	{include file="display/display_ticket_glance.tpl.php" col_span=6}
	
	{* Ticket Vital Signs *}
	{include file="display/display_ticket_vitals.tpl.php" col_span=3}
	</tr>
	
{if $o_ticket->writeable}
	</form>
{/if}

</table>
<br>
