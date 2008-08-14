{*if count($kb->focus_article->comments)*}
	<br>
	<table width='100%' cellpadding='2' cellspacing='1'>
	
	{section name=comment loop=$kb->focus_article->comments}
	<tr>
		<td bgcolor="#D0D0D0" class="cer_maintable_text">
			<b>{$kb->focus_article->comments[comment]->comment_poster}</b><br>
			{$kb->focus_article->comments[comment]->comment_date}

			{if $cfg->settings.kb_editors_enabled !== false && $kb->focus_article->comments[comment]->comment_approved == 0}
				(<span class='cer_configuration_updated'>Pending Approval</span>)
			{/if}
			
			{if $kb->show_comment_editor !== false} (<a href="{$kb->focus_article->comments[comment]->url_edit}" class='cer_header_loginLink'>edit</a>){/if}
			{if $kb->show_comment_editor !== false} (<a href='javascript:doCommentRemove({$kb->focus_article->comments[comment]->comment_id})' class='cer_header_loginLink'>remove</a>){/if}
	 	</td>
	</tr>
	<tr>
		<td bgcolor="#EEEEEE" class="cer_knowledgebase_comment">
			{if $kb->show_comment_editor !== false && !empty($kb_comment_edit) && $kb_comment_edit == $kb->focus_article->comments[comment]->comment_id}
			
				<form action="knowledgebase.php" method="post">
				<input type="hidden" name="mode" value="view_entry">
				<input type="hidden" name="form_submit" value="kb_comment_edit">
				<input type="hidden" name="kbid" value="{$kb->focus_article->article_id}">
				<input type="hidden" name="kbcat" value="{$kbcat}">
				<input type="hidden" name="kb_comment_id" value="{$kb->focus_article->comments[comment]->comment_id}">
				<input type="hidden" name="sid" value="{$session_id}">
				<textarea rows="10" cols="80" name="kb_comment_content">{$kb->focus_article->comments[comment]->comment_content}</textarea><br>
				<input type="submit" value="Save Changes">
				</form>
				
				{else}
				
					{$kb->focus_article->comments[comment]->comment_content}	
				
				{/if}				
	 	</td>
	</tr>
	
	{sectionelse}
		<tr>	
			<td bgcolor="#FFFFFF" class="cer_maintable_text">
				No user comments.
			</td>
		</tr>
	
	{/section}
	
	</table>
