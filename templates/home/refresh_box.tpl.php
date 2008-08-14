
<table width="200" border="0" cellpadding="0" cellspacing="0">
<form action="index.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr> 
    <td align="center" class="boxtitle_blue_vdk">{$smarty.const.LANG_REFRESH_TITLE}</td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr class="cer_footer_text"> 
    <td bgcolor="#EEEEEE"><select name="refresh_min" class="cer_footer_text">
		{html_options options=$refresh_times selected=$refresh_rate}		
		</select><input type="submit" value="{$smarty.const.LANG_WORD_SET}" class="cer_button_face">
  </tr>
</form>
</table>
