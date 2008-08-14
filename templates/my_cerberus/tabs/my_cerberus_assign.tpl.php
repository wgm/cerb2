<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="assign">
<input type="hidden" name="form_submit" value="assign">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_green_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="2" width="100%" border="0">
		<tr>
			<td><span class="cer_maintable_text">{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_INSTRUCTIONS} &lt;<b>{$user_email}</b>&gt;.<br>
			</td>
		</tr>

		<tr>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>
			<table border="0" cellspacing="1" cellpadding="1">
				<tr bgcolor="#888888">
				  <td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_QUEUE}</td>
				  <td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_QUICKASSIGN}</td>
				  <td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_WATCHER}</td>
				</tr>
				{section name=watcher loop=$watcher_queues->queues}
				<tr>
				  <td class="cer_maintable_heading">{$watcher_queues->queues[watcher]->queue_name}</td>
				  <td class="cer_maintable_text" align="center"><input type="checkbox" name="assign_q[]" value="{$watcher_queues->queues[watcher]->queue_id}" {if $assign_queues->queues[watcher] == 1}CHECKED{/if}></td>
				  <td class="cer_maintable_text" align="center"><input type="checkbox" name="watcher_q[]" value="{$watcher_queues->queues[watcher]->queue_id}" {if $watcher_queues->queues[watcher]->queue_watcher == 1}CHECKED{/if}></td>
				</tr>
				{/section}
			</table>
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td class="cer_maintable_text">
				<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_NOMORETHAN_QUICKASSIGN}</span>
				{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_NOMORETHAN_TEXT1}
				<input type="text" name="assign_max" value="{$assign_queues->max}" size="2" maxlength="2">
				{$smarty.const.LANG_MYCERBERUS_QUICKASSIGN_NOMORETHAN_TEXT2} 
			</td>
		</tr>
	</table>
	<table border=0 cellspacing=0 cellpadding=4 width="100%">
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right">
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>

</form>
