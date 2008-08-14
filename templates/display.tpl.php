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

{literal}
<script>
	function savePageLayout() {
		// [JAS]: Force submit the form
		document.formSaveLayout.submit();
	}
</script>
{/literal}

<br>

<a name="top">

<form action="display.php" name="formSaveLayout" method="post">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="form_submit" value="save_layout">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="mode" value="{$mode}">
  	<input type="hidden" name="layout_home_header_advanced" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_header_advanced}">
	<input type="hidden" name="layout_display_show_log" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_log}">
	<input type="hidden" name="layout_display_show_suggestions" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_suggestions}">
	<input type="hidden" name="layout_display_show_history" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_history}">
	<input type="hidden" name="layout_display_show_contact" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_contact}">
	<input type="hidden" name="layout_display_show_fields" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_fields}">
	<input type="hidden" name="layout_display_show_glance" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_glance}">
	<input type="hidden" name="layout_display_show_vitals" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_vitals}">
  	<input type="hidden" name="layout_view_options_bv" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_view_options_bv}">
</form>

{if $mode == "tkt_fields" || $mode == "properties"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_properties.tpl.php"}

	{if $urls.tab_edit}
		{include file="display/tabs/display_ticket_manage_requesters.tpl.php"}
	{/if}
	
	{if $priv->has_priv(ACL_TICKET_MERGE,BITGROUP_2) }
		{include file="display/tabs/display_ticket_merge.tpl.php"}
	{/if}

{elseif $mode == "anti_spam"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_antispam.tpl.php"}

{elseif $mode == "batch"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_batch.tpl.php"}

{elseif $mode == "log"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_log.tpl.php"}

{else}
	{include file="display/display_ticket.tpl.php"}

{/if}

{include file="footer.tpl.php"}
</body>
</html>
