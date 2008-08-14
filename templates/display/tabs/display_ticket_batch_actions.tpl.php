<tr>	
	<td colspan="{$col_span}" bgcolor="#B5B5B5">
	
	{if count($o_ticket->batch->batch_ids)}
	
		<select name="batch_action">
			<option value="comment">Batch Comment
			<option value="reply">Batch Reply
		</select>
		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_COMMIT}">&nbsp;
		<input type="button" class="cer_button_face" value="Clear Batch" OnClick="javascript:document.location=formatURL('display.php?ticket={$o_ticket->ticket_id}&mode=batch_clear');">
	
	{/if}
	
	</td>
</tr>
