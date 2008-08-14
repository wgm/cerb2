<html>
<head>
<title>{$smarty.const.LANG_HTML_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma-directive" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Directive" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">

{include file="cerberus.css.tpl.php"}
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css" type="text/css">
{include file="keyboard_shortcuts_jscript.tpl.php}

</head>

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}

<br>

<span class="cer_display_header">{$smarty.const.LANG_WORD_KNOWLEDGEBASE}</span><br>

{* View Knowledgebase Categories *}
{if $mode == "view"}
	
	{if $kb->show_kb_search !== false} 
		{include file="knowledgebase/kb_search_box.tpl.php" col_span=3}
	{/if}
	
  <br>
  {include file="knowledgebase/kb_category_table.tpl.php"}
  
  {if $root != 0}
  	{include file="knowledgebase/kb_article_list.tpl.php" articles=$kb->cat_articles col_span=3}
  	{include file="knowledgebase/kb_create_link.tpl.php"}
  {/if}
{/if}
		

{* Create Knowledgebase Article *}
{if $mode == "create"}
	{include file="knowledgebase/kb_article_edit.tpl.php"}
{/if}
		

{* Edit Knowledgevase Article *}
{if $mode == "edit_entry"}
	{if $kb->show_article_edit !== false}
		{include file="knowledgebase/kb_article_edit.tpl.php"}
	{/if}
{/if}
		

{* View Knowledgebase Article *}
{if $mode == "view_entry"}
	{include file="knowledgebase/kb_article_view.tpl.php"}
{/if}
		

{* View Knowledgebase "Ask" Results *}
{if $mode == "ask_results"}
	{if $kb->show_kb_search !== false} 
		{include file="knowledgebase/kb_search_box.tpl.php" col_span=3}
	{else}
		<br>
	{/if}
	
	<br>
	{include file="knowledgebase/kb_article_ask_results.tpl.php" articles=$kb->search_articles col_span=3}
{/if}

{* View Knowledgebase Keyword Results *}
{if $mode == "keyword_results"}
	{if $kb->show_kb_search !== false} 
		{include file="knowledgebase/kb_search_box.tpl.php" col_span=3}
	{else}
		<br>
	{/if}
	
	<br>
	{include file="knowledgebase/kb_article_keyword_results.tpl.php" articles=$kb->search_articles col_span=3}
{/if}

{include file="footer.tpl.php"}
</body>
</html>
