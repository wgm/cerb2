<tr bgcolor="#A5A5A5">
	
	<form action="display.php" method="POST">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="thread" value="{$oThread->thread_id}">
	<input type="hidden" name="form_submit" value="forward">
	<input type="hidden" name="sid" value="{$session_id}">
	
	<td align="left" valign="middle" class="cer_maintable_text">
			&nbsp;<span class="cer_maintable_header">Forward to address: </span>
			<input type="input" name="forward_to" size="35" value=""> 
			<input type="checkbox" name="add_to_req" value="1"> Add to Ticket Requesters 
			<input type="checkbox" name="no_attachments" value="1"> Don't Forward Attachments
			<input type="submit" value="Send">
	</td>
	
	</form>
</tr>