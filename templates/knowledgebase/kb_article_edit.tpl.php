<script>
{literal}
function checkSave()
	{
		if (document.knowledgebase.kb_problem_summary.value == "" ) {
		{/literal}alert("{$smarty.const.LANG_KB_ARTICLE_ERROR_SUMMARY}");{literal}
		document.knowledgebase.kb_problem_summary.focus();
		return false;
		}
		if (document.knowledgebase.kb_problem_text.value == "" ) {
		{/literal}alert("{$smarty.const.LANG_KB_ARTICLE_ERROR_DESC}");{literal}
		document.knowledgebase.kb_problem_text.focus();
		return false;
		}
		if (document.knowledgebase.kb_solution_text.value == "" ) {
		{/literal}alert("{$smarty.const.LANG_KB_ARTICLE_ERROR_SOLUTION}");{literal}
		document.knowledgebase.kb_solution_text.focus();
		return false;
		}
	}
{/literal}
</script>

<br>
<form method="post" name="knowledgebase" action="knowledgebase.php" onSubmit="return checkSave()">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="form_submit" value="kb_{$kb->focus_article->article_mode}">
<input type="hidden" name="kbid" value="{$kb->focus_article->article_id}">
<input type="hidden" name="kbcat" value="{$kbcat}">
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_green_glass"> 
    <td colspan="2">
    	{if $kb->focus_article->article_mode == "create"}{$smarty.const.LANG_WORD_CREATE}{else}{$smarty.const.LANG_WORD_EDIT}{/if} 
    	{$smarty.const.LANG_KB_ARTICLE_IN} '{$kb->category_name}'
    </td>
  </tr>
  <tr bgcolor="#CCCCCC" valign="bottom"> 
    <td width="15%" bgcolor="#CCCCCC" height="18" class="cer_maintable_heading" valign="middle"> 
      <div align="right" class="cer_maintable_heading">{$smarty.const.LANG_KB_SUMMARY}: </div>
    </td>
    <td width="85%" bgcolor="#DDDDDD" height="18"> 
      <input type="text" name="kb_problem_summary" size="55" maxlength="128" value="{$kb->focus_article->article_summary|short_escape}">
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle"> 
      <div align="right" class="cer_maintable_heading">{$smarty.const.LANG_KB_ENTRY_DATE}: </div>
    </td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD"> 
      <div align="left"> &nbsp;{$kb->focus_article->article_entry_date}</div>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle" align="right">{$smarty.const.LANG_KB_ENTRY_USER}: </td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD"> &nbsp;{$kb->focus_article->article_entry_user|short_escape}</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle" align="right">{$smarty.const.LANG_WORD_CATEGORY}: 
    </td>
    <td width="85%" bgcolor="#DDDDDD">
    	<select name="kb_category_id">
    		{if $kb->focus_article->article_mode == "create"}
    			{html_options options=$kb->tree->category_dropdown selected=$kb->category_id}
    		{else}
    			{html_options options=$kb->tree->category_dropdown selected=$kb->focus_article->article_category_id}
    		{/if}
    	</select>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle"> 
      <div align="right">{$smarty.const.LANG_KB_KEYWORDS}: </div>
    </td>
    <td width="85%" bgcolor="#DDDDDD"> 
      <input type="text" name="kb_keywords" size="55" maxlength="255" value="{$kb->focus_article->article_keywords|short_escape}"> {$smarty.const.LANG_KB_KEYWORDS_IE}
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle" align="right">{$smarty.const.LANG_KB_ARTICLE_USE}: </td>
    <td width="85%" bgcolor="#DDDDDD">
    	<input type="radio" name="kb_public" value="1" {if $kb->focus_article->article_public==1}CHECKED{/if}> {$smarty.const.LANG_WORD_PUBLIC} &nbsp;
    	<input type="radio" name="kb_public" value="0" {if $kb->focus_article->article_public==0}CHECKED{/if}> {$smarty.const.LANG_WORD_PRIVATE}
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle">&nbsp;</td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD">&nbsp;</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="top" align="right">{$smarty.const.LANG_KB_DESC_PROB}: </td>
    <td width="85%" bgcolor="#DDDDDD" valign="top">
      <textarea name="kb_problem_text" cols="80" rows="8">{$kb->focus_article->article_problem_text}</textarea><br>
      {assign var=is_html value=$kb->focus_article->article_problem_text_is_html}
      Text format: <input type="radio" name="kb_problem_text_type" value="0" {if $is_html == 0 || empty($is_html)}CHECKED{/if}> Plaintext <input type="radio" name="kb_problem_text_type" value="1" {if $is_html == 1}CHECKED{/if}> HTML<br>
      <span class="cer_footer_text">
      <B>Plaintext</B> will automatically hyperlink URLs and preserve wordwrapping.<br>
      <B>HTML</B> will be passed to the browser directly without additional formatting.
      </span>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text">
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle">&nbsp;</td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD">&nbsp;</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="top" align="right">{$smarty.const.LANG_KB_DESC_SOLUTION}: </td>
    <td width="85%" bgcolor="#DDDDDD" valign="top"> 
      <textarea name="kb_solution_text" cols="80" rows="8">{$kb->focus_article->article_solution_text}</textarea><br>
      {assign var=is_html value=$kb->focus_article->article_solution_text_is_html}
      Text format: <input type="radio" name="kb_solution_text_type" value="0" {if $is_html == 0 || empty($is_html)}CHECKED{/if}> Plaintext <input type="radio" name="kb_solution_text_type" value="1" {if $is_html == 1}CHECKED{/if}> HTML<br>
      <span class="cer_footer_text">
      <B>Plaintext</B> will automatically hyperlink URLs and preserve wordwrapping.<br>
      <B>HTML</B> will be passed to the browser directly without additional formatting.
      </span>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text">
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle">&nbsp;</td>
    <td width="85%" class="cer_maintable_text" bgcolor="#DDDDDD">&nbsp;</td>
  </tr>  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td width="15%" bgcolor="#CCCCCC" class="cer_maintable_heading" valign="middle" align="right">Reset Fetch & Retrieve Training: </td>
    <td width="85%" bgcolor="#DDDDDD">
    	<input type="radio" name="kb_clean_learning" value="0" CHECKED> No &nbsp;
    	<input type="radio" name="kb_clean_learning" value="1"> Yes
    </td>
  </tr>
  <tr bgcolor="#A5A5A5" class="cer_maintable_text" align="right">
    <td colspan="2" class="cer_maintable_heading">
      <input type="submit" class="cer_button_face" name="Submit" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
    </td>
  </tr>
</table>
</form>
