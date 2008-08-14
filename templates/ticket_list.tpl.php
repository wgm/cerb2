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

<script>
{literal}
	function savePageLayout() {
		// [JAS]: Force submit the form
		document.formSaveLayout.submit();
	}
{/literal}

{literal}
function calendarPopUp(date_field)
{
	{/literal}
	{if $track_sid eq "true"}
		url = "calendar_popup.php?sid={$session_id}&date_field=" + date_field;
	{else}
		url = "calendar_popup.php?date_field=" + date_field;
	{/if}
	{literal}
	window.open(url,"calendarWin","width=250,height=200");		
}
{/literal}
</script>

</head>

<body bgcolor="#FFFFFF" OnLoad="load_init();" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}
<br>

{if !empty($queue_view)}
	<span class="cer_display_header">{$header_readwrite_queues.$qid}</span>&nbsp;&nbsp;
	<span class="cer_maintable_text">{$smarty.const.LANG_LIST_ACTIVE}: {$s_view->show_of}</span>
{else}
	<span class="cer_display_header">{$smarty.const.LANG_WORD_SEARCH_RESULTS}</span>&nbsp;&nbsp;
	<span class="cer_maintable_text">Matched {$s_view->show_of} Tickets</span>
{/if}

{include file="views/ticket_view.tpl.php" view=$s_view col_span=$s_view->view_colspan}
<table width="100%">
<form action="ticket_list.php" name="formSaveLayout" method="post">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="form_submit" value="save_layout">
	
	<input type="hidden" name="layout_view_options_sv" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_view_options_sv}">
	<input type="hidden" name="layout_home_header_advanced" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_header_advanced}">
</form>
	<tr>
		<td>{include file="search/quick_search.tpl.php"}</td>
	</tr>
</table>
<br>
{include file="footer.tpl.php"}
</body>
</html>