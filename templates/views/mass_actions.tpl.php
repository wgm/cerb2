{if $view->show_mass && ($view->show_chowner) }
	<tr bgcolor="#999999" valign="middle">
		<td align="left" colspan="{$col_span}">
		{if $view->show_mass}		
		
			{if $view->show_chstatus }
			<select name="status_id" class="cer_footer_text">
			  {html_options options=$view->show_chstatus_options selected=-1}
			</select>
			<span class="cer_maintable_header">+</span>
			{/if}
			
			{if $view->show_chqueue }
			<select name="queue_id" class="cer_footer_text">
			  {html_options options=$view->show_chqueue_options selected=-1}
			</select>
			<span class="cer_maintable_header">+</span>
			{/if}
			
			{if $view->show_chowner }
			<select name="owner_id" class="cer_footer_text">
			  {html_options options=$view->show_chowner_options selected=-1}
			</select>
			<span class="cer_maintable_header">+</span>
			{/if}
			
			{if $view->show_chaction }
			<select name="action_id" class="cer_footer_text">
				<option value="-1"> - {$smarty.const.LANG_ACTION_PROMPT} -
				<option value="mark_as_spam">{$smarty.const.LANG_ACTION_MARK_AS_SPAM}
				<option value="mark_as_ham">{$smarty.const.LANG_ACTION_MARK_AS_HAM}
				<option value="block_sender">{$smarty.const.LANG_ACTION_BLOCK_SENDER}
				<option value="unblock_sender">{$smarty.const.LANG_ACTION_UNBLOCK_SENDER}
				<option value="batch_add">{$smarty.const.LANG_ACTION_BATCH_ADD}
				<option value="batch_remove">{$smarty.const.LANG_ACTION_BATCH_REMOVE}
				{if $priv->has_priv(ACL_TICKET_MERGE,BITGROUP_2) }
					<option value="merge">{$smarty.const.LANG_ACTION_MERGE}
				{/if}
				<option value="due_24h">Make Due in 24 Hrs
				<option value="due_now">Make Due Immediately
			</select>
			{/if}
			
			<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_COMMIT}">
		{/if}
		</td>
	</tr>
{/if}