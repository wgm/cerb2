<a name="comment">
<br>
<span class="cer_knowledgebase_heading">Add a Comment</span><br>
<span class="cer_maintable_text">If you choose to leave an e-mail address, your address will be masked to aid in preventing automatic
harvesting of e-mail addresses from this system for unsolicited commercial e-mail purposes.  user@domain.com will become <b>user at domain dot com</b>.  You are welcome to
add your own spam protection as well, by adding a human-readable spam blocker such as user@<b>NOSPAM</b>.domain.com.<br>
<br>
Use this system to append to the current knowledgebase articles, posting notes you feel should be added to current documentation or may 
assist other users.  Please do <b>not</b> use the comments system to request support, report bugs or suggest new functionality.  There are other
avenues for pursuing such items, and offending notes will be removed.<br>
<br>
<b>HTML tags are disabled in comments.</b>  URLs will automatically be hyperlinked.  
{if $kb->show_comment_editors !== false}
<span class="cer_configuration_updated">All comments must be approved by an editor before becoming visible in the knowledgebase.</span>
{/if}
</span><br>
<br>
<form action="knowledgebase.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="view_entry">
<input type="hidden" name="form_submit" value="kb_comment">
<input type="hidden" name="kbid" value="{$kb->focus_article->article_id}">
<input type="hidden" name="kbcat" value="{$kbcat}">
<table cellpadding="2" width="0" cellspacing="1" bgcolor="#D0D0D0">
<tr class="boxtitle_green_glass">
	<td colspan="2">Article Comment</td>
</tr>
<tr>
	<td nowrap valign="top" class="cer_maintable_heading">Your E-mail Address<br>(or Name):</td>
	<td><input type="text" name="poster_email" size="55" value="user@domain.com"></td>
</tr>
<tr>
	<td valign="top" nowrap class="cer_maintable_heading">Comment:</td>
	<td><textarea name="poster_comment" rows="15" cols="65"></textarea></td>
</tr>
<tr>
	<td valign="top" nowrap class="cer_maintable_heading">IP Address:</td>
	<td class="cer_maintable_text">{$remote_addr}</td>
</tr>
<tr>
	<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="Add Comment"></td>
</tr>
</table>
</form>