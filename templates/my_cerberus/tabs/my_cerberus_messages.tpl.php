{if $msgs->pm_mode == "list"}
	{include file="my_cerberus/tabs/my_cerberus_messages_list.tpl.php"}
{elseif $msgs->pm_mode == "read" || $msgs->pm_mode == "reply"}
	{include file="my_cerberus/tabs/my_cerberus_messages_read.tpl.php"}
{/if}

<a name="send_pm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="messages">
<input type="hidden" name="form_submit" value="pm_send">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PMS_SEND_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="1" cellpadding="2" width="100%" border="0">
		<tr>
			<td class="cer_maintable_heading" bgcolor="#CCCCCC" width="1%" nowrap valign="top">{$smarty.const.LANG_MYCERBERUS_PMS_SEND_TOUSER}</td>
			<td class="cer_maintable_text" width="99%">
			<select name="pm_to_user_id">
				{if $msgs->pm_mode == "reply"}
					{html_options options=$msgs->to_users selected=$msgs->msgs[0]->from_id}
				{elseif $msgs->pm_to_id != 0}
					{html_options options=$msgs->to_users selected=$msgs->pm_to_id}
				{else}
					{html_options options=$msgs->to_users}
				{/if}
			</select>
			</td>
		</tr>
		<tr>
			<td class="cer_maintable_heading" bgcolor="#CCCCCC" width="1%" nowrap valign="top">{$smarty.const.LANG_MYCERBERUS_PMS_SEND_SUBJECT}</td>
			<td class="cer_maintable_text" width="99%"><input type="text" name="pm_subject" size="64" maxlength="120" {if $msgs->pm_mode == "reply"}value="{$msgs->msgs[0]->subject}"{/if}></td>
		</tr>
		<tr>
			<td class="cer_maintable_heading" bgcolor="#CCCCCC" width="1%" nowrap valign="top">{$smarty.const.LANG_MYCERBERUS_PMS_SEND_MESSAGE}</td>
			<td class="cer_maintable_text" width="99%"><textarea name="pm_message" rows="8"></textarea></td>
		</tr>
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right" colspan="2">
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</form>
</table>
<br>

