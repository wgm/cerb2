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

<script language=javascript>
{literal}
	function drawDate()
	{
		{/literal}window.opener.{$date_field}.value = '{$mo_m}/{$mo_d}/{$mo_y}';{literal}
		window.close();
	}
{/literal}
</script>

</head>
<body bgcolor="#D3D3D3" {if $date_chosen == 1}onload="javascript: drawDate();"{/if}>

<table cellpadding="0" cellspacing="0" border="0" align="center">
<tr>
	<td align="center" class="cer_maintable_heading">{$smarty.const.LANG_CHOOSEDATE_CHOOSEDATE}</td>
</tr>
<tr>
	<td align="center">
		{include file="my_cerberus/tabs/my_cerberus_dashboard_calendar.tpl.php" cal=$cal}
	</td>
</tr>
</table>

</body>
</html>