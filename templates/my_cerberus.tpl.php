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

<script language="javascript1.2" src="includes/scripts/listbox.js"></script>

</head>

<body bgcolor="#FFFFFF" OnLoad="load_init();" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}
<br>

{if $mode == "assign"}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_assign.tpl.php"}

{elseif $mode == "notification"}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_notification.tpl.php"}

{elseif $mode == "tasks"}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_tasks.tpl.php"}

{elseif $mode == "preferences"}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_preferences.tpl.php"}

{elseif $mode == "layout"}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_layout.tpl.php"}

{elseif $mode == "messages"}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_messages.tpl.php"}

{else}
	{include file="my_cerberus/my_cerberus_heading.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_dashboard.tpl.php"}

{/if}

{include file="footer.tpl.php"}
</body>
</html>