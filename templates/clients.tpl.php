{assign var="col_span" value="1"}
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

{if !empty($params.mode) && $params.mode != "search" }

	{if $params.mode == "c_view" && !empty($params.id) }
		{include file="clients/client_company_view.tpl.php"}
	{elseif $params.mode == "u_view" && !empty($params.id)}
		{include file="clients/client_publicuser_view.tpl.php"}
	{elseif $params.mode == "c_add"}
		{include file="clients/client_company_add.tpl.php"}
	{elseif $params.mode == "u_add"}
		{include file="clients/client_publicuser_add.tpl.php"}
	{/if}
	
{else}
	<span class="cer_display_header">{$smarty.const.LANG_CONTACTS_HEADER}</span><br>
	<span class="cer_maintable_text">{$smarty.const.LANG_CONTACTS_INSTRUCTIONS}</span><br>
	<br>
	
	{include file="clients/client_contact_search.tpl.php"}
	{include file="clients/client_company_list.tpl.php"}
	{include file="clients/client_publicuser_list.tpl.php"}
{/if}

{include file="footer.tpl.php"}
</body>
</html>
