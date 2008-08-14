
{if !empty($summary->tickets) }

	<a name="tickets">
	
	<table border="0" cellspacing="1" cellpadding="3" bgcolor="#888888" width="100%">

		<tr class="boxtitle_blue_glass_pale">
			<td colspan="7">{$summary->summary_title|short_escape}</td>
		</tr>
	
		<tr bgcolor="#CCCCCC">
			<td class="cer_maintable_headingSM">#</td>
			<td class="cer_maintable_headingSM">Subject</td>
			<td class="cer_maintable_headingSM">Status</td>
			<td class="cer_maintable_headingSM">Queue</td>
			<td class="cer_maintable_headingSM">Last Wrote</td>
			<td class="cer_maintable_headingSM">Age</td>
			<td class="cer_maintable_headingSM">Due</td>
		</tr>
	
		{foreach from=$summary->tickets item=ticket key=ticket_id name=ticket}
	
			<tr bgcolor="#{if $smarty.foreach.ticket.iteration % 2 == 0}EEEEEE{else}F5F5F5{/if}">
				<td class="cer_footer_text">#{$ticket->ticket_mask}</td>
				<td class="cer_maintable_text"><a href="{$ticket->ticket_url}" class="cer_maintable_heading">{$ticket->ticket_subject|short_escape}</a></td>
				<td class="cer_footer_text">{$ticket->ticket_status}</td>
				<td class="cer_footer_text"><a href="{$ticket->queue_url}" class="cer_maintable_heading">{$ticket->queue_name}</a></td>
				<td class="cer_footer_text">{$ticket->ticket_last_wrote_address}</td>
				<td class="cer_maintable_text">{$ticket->ticket_age}</td>
				<td class="cer_maintable_text">{$ticket->ticket_due}</td>
			</tr>
	
		{/foreach}
	
	</table>

	<br>
	
{/if}