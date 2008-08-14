<SCRIPT>
{literal}
function changeFolder(box)
{
	url = "my_cerberus.php?mode=messages&form_submit=pm_folder&pm_folder=" + box.value;
    if(show_sid) { url = url + "&" + sid; } document.location = url;
}
{/literal}
</SCRIPT>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="messages">
<input type="hidden" name="form_submit" value="pm_batch">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_glass_dk"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_HEADER}'{$msgs->folder->folder_name}'</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="1" cellpadding="2" width="100%" border="0">
		{if count($msgs->msgs) != 0}
		<tr bgcolor="#888888">
			<td class="cer_maintable_header" width="3%" nowrap>&nbsp;</td>
			<td class="cer_maintable_header" width="91%">{$smarty.const.LANG_WORD_SUBJECT}</td>
			<td class="cer_maintable_header" width="3%" nowrap>{if $msgs->folder->folder_id==-1}{$smarty.const.LANG_WORD_TO2}{else}{$smarty.const.LANG_WORD_FROM}{/if}</td>
			<td class="cer_maintable_header" width="3%" nowrap>Date</td>
		</tr>
		{/if}
		
		{section name=pm loop=$msgs->msgs}
		<tr class="{if %pm.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
			<td class="cer_maintable_text" nowrap><input type="checkbox" name="pm_ids[]" value="{$msgs->msgs[pm]->pm_id}"></td>
			<td class="cer_maintable_text"><a href="{$msgs->msgs[pm]->pm_url}" class="{if $msgs->msgs[pm]->marked_read == 0}cer_maintable_heading{else}cer_maintable_text{/if}">{$msgs->msgs[pm]->subject|short_escape}</a></td>
			<td class="cer_maintable_text" nowrap><b>{if $msgs->folder->folder_id==-1}{$msgs->msgs[pm]->to}{else}{$msgs->msgs[pm]->from}{/if}</b></td>
			<td class="cer_footer_text" nowrap>{$msgs->msgs[pm]->date}</td>
		</tr>
		{sectionelse}
		<tr>
			<td class="cer_maintable_text">{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_NO}</td>
		</tr>
		{/section}
		
		{if count($msgs->msgs) != 0 && $msgs->folder->folder_id != -1}
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="left" colspan="4">
        		<select name="pm_do"">
        			<option value="mark_read">{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_MARK_READ}
        			<option value="mark_unread">{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_MARK_UNREAD}
        			<option value="delete">{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_MARK_DELETE}
        		</select>
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
		{/if}
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</form>
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="messages">
<input type="hidden" name="form_submit" value="pm_folder">
  <tr> 
    <td class="cer_maintable_header">&nbsp;</td>
  </tr>
  <tr> 
    <td align="right">
		<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_CHANGEFOLDER}</span>
		<select name="pm_folder" class="cer_footer_text" OnChange="javascript:changeFolder(this);">
			<option value="ib" {if $msgs->folder->folder_id == 0}selected{/if}>{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_INBOX}
			<option value="ob" {if $msgs->folder->folder_id == -1}selected{/if}>{$smarty.const.LANG_MYCERBERUS_PMS_VIEW_SENT}
		</select><input type="submit" class="cer_button_face" value="go">
		</span>
    </td>
  </tr>
</form>
</table>
<br>
