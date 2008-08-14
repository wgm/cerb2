<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr class="boxtitle_gray_glass">
		<td>{$smarty.const.LANG_KB_SEARCH_KEYWORD_HEADING}</span></td>
	</tr>
	
	<tr>
		<td bgcolor="#FFFFFF" class="cer_maintable_text">

			<form action="knowledgebase.php">
			<input type="hidden" name="form_submit" value="kb_search">
			<B>{$smarty.const.LANG_KB_SEARCH_KEYWORD_INTRO}:</B><br>
			<input type="text" name="kb_keywords" value="{$kb_keyword_string|short_escape}" size="45"><input type="submit" value="{$smarty.const.LANG_WORD_SEARCH}" class="stylized"><br>
			{$smarty.const.LANG_KB_SEARCH_KEYWORD_INSTRUCTIONS}<br>
			</form>		
		
		</td>
	</tr>
	
</table>

<br>