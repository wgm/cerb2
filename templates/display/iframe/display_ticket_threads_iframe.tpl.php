{if $iframe != "no"}
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
</head>

<body bgcolor="#FFFFFF" marginheight=2 marginwidth=2 topmargin=2 leftmargin=2>
{/if}

{include file="display/display_ticket_threads_list.tpl.php" suppress_links=true}

{if $iframe != "no"}
</body>
</html>
{/if}