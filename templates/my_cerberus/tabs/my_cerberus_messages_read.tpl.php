<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="messages">
<input type="hidden" name="form_submit" value="pm_read">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="cer_home_preferences_background_4"> 
    <td class="cer_maintable_header">&nbsp;Read Private Message in '{$msgs->folder->folder_name}'</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="1" cellpadding="2" width="100%" border="0">
		{section name=pm loop=$msgs->msgs}
		<tr bgcolor="#CCCCCC">
			<td class="cer_maintable_text" width="20%" nowrap valign="top">
				<b>From: {$msgs->msgs[pm]->from}</b><br>
				<span class="cer_footer_text">{$msgs->msgs[pm]->date}</span><br>
				<br>
				<b>To: {$msgs->msgs[pm]->to}</b><br>
			</td>
			<td class="cer_maintable_text" bgcolor="#DDDDDD" valign="top">
			<b>{$msgs->msgs[pm]->subject|short_escape}</b><br>
			<br>
			<span class="cer_display_emailText">{$msgs->msgs[pm]->message|short_escape|nl2br}</span><br>
			<br>
			</td>
		</tr>
		<tr bgcolor="#888888">
			<td>&nbsp;</td>
			<td class="cer_footer_text">&nbsp;
			{if $msgs->folder->folder_id != -1}[ <a href="{$msgs->msgs[pm]->urls.reply}" class="cer_display_commentLink">Reply</a> ] {/if}
			[ <a href="{$msgs->msgs[pm]->urls.inbox}" class="cer_display_commentLink">Back to {$msgs->folder->folder_name}</a> ]
			{if $msgs->folder->folder_id != -1}[ <a href="{$msgs->msgs[pm]->urls.delete}" class="cer_display_commentLink">Delete</a> ]{/if}
			</td>
		</tr>
		{sectionelse}
		<tr>
			<td class="cer_maintable_text">Invalid private message.</td>
		</tr>
		{/section}
		
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</form>
</table>
<br>
