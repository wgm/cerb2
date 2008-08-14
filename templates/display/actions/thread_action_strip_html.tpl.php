<tr bgcolor="#A5A5A5">
	<form action="display.php" method="POST">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="thread" value="{$oThread->thread_id}">
	<input type="hidden" name="form_submit" value="strip_html">
	<input type="hidden" name="sid" value="{$session_id}">
	
	<td align="left" valign="middle" class="cer_maintable_text">
			&nbsp;<span class="cer_maintable_header">HTML Tags Stripped!  Save changes? </span>
			<input type="submit" value="Accept">
			<input type="button" value="Reject" OnClick="javascript:document.location='{$urls.tab_display}';">
	</td>
	
	</form>
</tr>