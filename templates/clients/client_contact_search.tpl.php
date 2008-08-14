
	<table border="0" cellspacing="1" cellpadding="3" bgcolor="#888888" width="100%">
		<form action="clients.php">
		<input type="hidden" name="mode" value="search">
		<input type="hidden" name="sid" value="{$session_id}">
		<tr class="boxtitle_gray_glass">
			<td>{$smarty.const.LANG_CONTACTS_SEARCH_CAPTION}</td>
		</tr>
		<tr>
			<td align="left" bgcolor="#EEEEEE">
			<input type="text" name="contact_search" size="40" value="{$params.contact_search|escape:"htmlall"}">
			<input type="submit" value="{$smarty.const.LANG_WORD_SEARCH}" class="cer_button_face"><br>
			<span class="cer_footer_text">{$smarty.const.LANG_CONTACTS_SEARCH_INSTRUCTIONS}</span>
			</td>
		</tr>
		</form>
	</table>

	<br>
