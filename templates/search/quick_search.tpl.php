<div id="search" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_home_show_search || $page == "ticket_list.php"}block{else}none{/if};">
<br>
<a name="search_box">
<form action="ticket_list.php" method="post" name="search">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="search_submit" value="1">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#DDDDDD">
<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr> 
  <td class="boxtitle_gray_dk">
  	{if $search_box->search_toggle_mode == 'advanced'}Advanced Ticket Search{else}{$smarty.const.LANG_SEARCH_TITLE}{/if}
  </td>
</tr>
<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
<tr> 
  <td bgcolor="#DDDDDD" width="20%" nowrap><span class="cer_maintable_headingSM">&nbsp;{$smarty.const.LANG_SEARCH_INQUEUE}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></span></td>
  <td bgcolor="#EEEEEE" width="30%">
  	<select name="qid">
  		{html_options options=$search_box->search_queue_options selected=$session->vars.psearch->params.search_queue}
  	</select>
  </td>
  <td bgcolor="#DDDDDD" width="20%" nowrap><span class="cer_maintable_headingSM">&nbsp;{$smarty.const.LANG_SEARCH_STATUS}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></span></td>
  <td bgcolor="#EEEEEE" width="30%">
  	<select name="search_status">
  		{html_options options=$search_box->search_status_options selected=$session->vars.psearch->params.search_status}
  	</select>
   </td>
</tr>

<tr bgcolor="#DDDDDD">
  <td class="cer_maintable_headingSM" width="20%" nowrap valign="top">&nbsp;{$smarty.const.LANG_SEARCH_SENDER}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_headingSM" width="30%" valign="top"> 
    <input type="text" size="15" name="search_sender" class="cer_maintable_text" value="{$session->vars.psearch->params.search_sender|short_escape}" style="width: 100%;">
  </td>
  <td class="cer_maintable_headingSM" width="20%" nowrap valign="top">&nbsp;{$smarty.const.LANG_SEARCH_SUBJECT}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"><SPAN class="help_link" onclick="toggle_subjectSearch();"><img src="includes/images/crystal/16x16/icon_search_syntax.gif" width="16" height="16" border="0" align="absmiddle"></span><img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_headingSM" width="30%" valign="top"> 
    {include file="tooltips/search_box_boolean_syntax.tpl.php" tip_name="subjectSearch"}
    <input type="text" size="15" name="search_subject" class="cer_maintable_text" value="{$session->vars.psearch->params.search_subject|short_escape}" style="width: 100%;">
  </td>
</tr>
<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="20%" nowrap valign="top">&nbsp;{$smarty.const.LANG_SEARCH_CONTENT}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"><SPAN class="help_link" onclick="toggle_contentSearch();"><img src="includes/images/crystal/16x16/icon_search_syntax.gif" width="16" height="16" border="0" align="absmiddle"></span><img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td class="cer_maintable_headingSM" bgcolor="#EEEEEE" width="30%" valign="top"> 
    {include file="tooltips/search_box_boolean_syntax.tpl.php" tip_name="contentSearch"}
    <input type="text" size="15" name="search_content" class="cer_maintable_text" value="{$session->vars.psearch->params.search_content|short_escape}" style="width: 100%;">
  </td>
  <td class="cer_maintable_headingSM" width="20%" nowrap valign="top">&nbsp;{$smarty.const.LANG_SEARCH_OWNER}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" width="30%" valign="top">
  	<select name="search_owner">
	 {html_options options=$search_box->search_owner_options selected=$session->vars.psearch->params.search_owner}
	</select>
  </td>
</tr>
	
<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="20%" nowrap>&nbsp;{$smarty.const.LANG_SEARCH_COMPANY}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" colspan="3"> 
    <select name="search_company">
     {html_options options=$search_box->search_company_options selected=$session->vars.psearch->params.search_company}
    </select>
  </td>
</tr>
<tr>
	<td colspan="4" bgcolor="#EEEEEE" align="left">
		<a href="{$search_box->search_toggle_url}" class="cer_maintable_heading"><b>{$search_box->search_toggle_text}</b></a>
	</td>
</tr>
</table>

{if $search_box->search_toggle_mode == 'advanced'}
	{include file="search/advsearch.tpl.php" col_span=$col_span}
	
{/if}

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
<tr>
	<td bgcolor="#CCCCCC" align="right">
		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_SEARCH_SUBMIT}">
	</td>
</tr>
</form>
</table>

</div>