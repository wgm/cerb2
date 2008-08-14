<html>
<head>
<title>{$smarty.const.LANG_ACTION_PRINT_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
{include file="cerberus.css.tpl.php"}
</head>

<body bgcolor="#FFFFFF">
<br>
{if $printlevel == "ticket"}
	{include file="print/print_ticket.tpl.php"}
        {include file="print/printfooter.tpl.php"}
{elseif $printlevel == "thread"}	
	{include file="print/print_thread.tpl.php"}
        {include file="print/printfooter.tpl.php"}
{else}
	{literal}
	  <script language="javascript">
		window.close();
	  </script>
	{/literal}
{/if}

</body>
</html>
