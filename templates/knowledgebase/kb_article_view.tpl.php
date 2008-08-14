<script language="javascript">
{literal}

function doArticleDelete()
{
	{/literal}if(confirm("{$smarty.const.LANG_KB_ARTICLE_DEL_CONFIRM}")){literal}
	{
		{/literal}url = formatURL("{$kb->focus_article->url_article_delete}");{literal}
		document.location = url;
	}
}

function doCommentRemove(comment_id)
{
	if(confirm("Are you sure you want to remove this knowledgebase article comment?"))
	{
		{/literal}url = "{$kb->focus_article->url_comment_delete}";{literal}
		url = formatURL(url);
		document.location = url;
	}
}

{/literal}
</script>

<br>

<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr class="boxtitle_green_glass">
		<td>{$smarty.const.LANG_KB_VIEW_TITLE}:</td>
	</tr>
	
	<tr>
		<td bgcolor="#FFFFFF">
			<span class="cer_maintable_text">{$kb->focus_article->article_summary}</span><br>
		</td>
	</tr>
</table>
<br>


<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr class="boxtitle_gray_glass">
		<td>{$smarty.const.LANG_KB_SYMPTOMS}:</td>
	</tr>
	
	<tr>
		<td bgcolor="#FFFFFF">
			<span class="cer_maintable_text">
			{if $kb->focus_article->article_problem_text_is_html}
				{$kb->focus_article->article_problem_text}
			{else}
				{$kb->focus_article->article_problem_text|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_maintable_heading"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}
			{/if}
			</span>
		</td>
	</tr>
</table>
<br>


<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr class="boxtitle_gray_glass">
		<td>{$smarty.const.LANG_KB_RESOLUTION}:</td>
	</tr>
	
	<tr>
		<td bgcolor="#FFFFFF">
			<span class="cer_maintable_text">
			{if $kb->focus_article->article_solution_text_is_html}
				{$kb->focus_article->article_solution_text}
			{else}
				{$kb->focus_article->article_solution_text|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_maintable_heading"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}
			{/if}
			</span>
		</td>
	</tr>
</table>
<br>

<form>
<input type="button" class="cer_button_face" value="{$smarty.const.LANG_KB_RETURN} '{$kb->focus_article->article_category_name}'" onClick="javascript:document.location=formatURL('{$kb->focus_article->url_return}');">
{if $kb->show_article_edit !== false}<input type="button" class="cer_button_face" value="{$smarty.const.LANG_KB_EDIT}" OnClick="window.location='{$kb->focus_article->url_article_edit}'">&nbsp;{/if}
{if $kb->show_article_delete !== false}<input type="button" class="cer_button_face" value="{$smarty.const.LANG_KB_DELETE}" OnClick="javascript:doArticleDelete();">&nbsp;{/if}
</form>
<br>

<form method="post" action="knowledgebase.php">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="form_submit" value="kb_rating">
<input type="hidden" name="mode" value="view_article">
<input type="hidden" name="kbid" value="{$kb->focus_article->article_id}">
<input type="hidden" name="kbcat" value="{$kbcat}">
<table cellpadding="1" cellspacing="1">

<tr class="boxtitle_blue_glass">
	<td>How Helpful was this Article?</td>
</tr>
<tr bgcolor="#EEEEEE" class="cer_maintable_heading">
	<td valign="middle">
	<input type="radio" name="kb_rating" value="1"> 0%
	<input type="radio" name="kb_rating" value="2"> 25%
	<input type="radio" name="kb_rating" value="3" checked> 50%
	<input type="radio" name="kb_rating" value="4"> 75%
	<input type="radio" name="kb_rating" value="5"> 100%
	<input type="submit" class="cer_button_face" value="Rate">
	</td>
</tr>
<tr>
	<td bgcolor="#CCCCCCC"><img src="includes/images/spacer.gif" width="1" height="5"></td>
</tr>
</form>
</table>

{if $kb->focus_article->ip_has_voted === true}
	<span class='cer_footer_text'><b>NOTE: Our records indicate a user has rated this article from IP '
	{$remote_addr}'.<br>Choosing a new rating for this article will replace the one on file.</b></span>
	<br>
{/if}

<br>

<table cellpadding="1" cellspacing="1">
<tr>
	<td bgcolor="#D0D0D0"><span class="cer_maintable_heading">Access: </span></td>
	<td bgcolor="#EEEEEE"><span class="cer_maintable_text">{if $kb->focus_article->article_public}Public{else}Private{/if}</span></td>
</tr>
{if $kb->focus_article->article_rating_avg != 0.0 && $kb->focus_article->article_rating_avg != null}
<tr>
	<td bgcolor="#D0D0D0" class="cer_maintable_heading">Rating: </td>
	<td bgcolor="#EEEEEE" class="cer_maintable_text">
		<b>{$kb->focus_article->article_rating_avg|string_format:"%0.1f"}</b> ({$kb->focus_article->article_rating_count} votes)
		<table cellpadding="0" cellspacing="0" width="50">
			<tr>
				<td width="{$kb->focus_article->article_rating_percent}%" bgcolor="#EE0000"><img src="includes/images/spacer.gif" height="3" width="{$kb->focus_article->article_rating_width}"></td>
				<td width="{$kb->focus_article->article_rating_percent_i}%" bgcolor="#AEAEAE"></td>
			</tr>
		</table>
	</td>
</tr>
{/if}
<tr>
	<td bgcolor="#D0D0D0"><span class="cer_maintable_heading">{$smarty.const.LANG_WORD_CATEGORY}: </span></td>
	<td bgcolor="#EEEEEE"><span class="cer_maintable_text">{$kb->focus_article->article_category_name}</span></td>
</tr>
<tr>
	<td bgcolor="#D0D0D0"><span class="cer_maintable_heading">{$smarty.const.LANG_KB_KEYWORDS}: </span></td>
	<td bgcolor="#EEEEEE"><span class="cer_maintable_text">{$kb->focus_article->article_keywords}</span></td>
</tr>
<tr>
	<td bgcolor="#D0D0D0"><span class="cer_maintable_heading">{$smarty.const.LANG_KB_ENTRY_DATE}: </span></td>
	<td bgcolor="#EEEEEE"><span class="cer_maintable_text">{$kb->focus_article->article_entry_date}</span></td>
</tr>
<tr>
	<td bgcolor="#D0D0D0"><span class="cer_maintable_heading">{$smarty.const.LANG_KB_ENTRY_USER}: </span></td>
	<td bgcolor="#EEEEEE"><span class="cer_maintable_text">{$kb->focus_article->article_entry_user}</span></td>
</tr>
</table>
<br>

<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr class="boxtitle_blue_glass_pale">
		<td>User Contributed Comments:</td>
	</tr>
</table>
( <a href="{$kb->focus_article->url_add_comment}" class="cer_maintable_heading">add comment</a> )<br>

{include file="knowledgebase/kb_article_comment_list.tpl.php"}

{if !empty($kb_comment) }
	{include file="knowledgebase/kb_article_comment_add.tpl.php"}
{/if}