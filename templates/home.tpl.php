{* Index Template *}
<html>
<head>
<title>{$smarty.const.LANG_HTML_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma-directive" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Directive" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">
{if $do_meta_refresh}
<META HTTP-EQUIV="Refresh" content="{$refresh_sec};URL={$refresh_url}">
{/if}

{include file="cerberus.css.tpl.php"}
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css" type="text/css">

{include file="keyboard_shortcuts_jscript.tpl.php}

<script>
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


<body bgcolor="#FFFFFF" OnLoad="javascript:load_init();" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}

{literal}
<script>
	folders_on = new Image;
	folders_on.src = "includes/images/folders_on.gif";
	folders_off = new Image;
	folders_off.src = "includes/images/folders_off.gif";
	search_off = new Image;
	search_off.src = "includes/images/search_off.gif";
	search_on = new Image;
	search_on.src = "includes/images/search_on.gif";

	function toggleSystemStatus() {
		if (document.getElementById) {
			if(document.getElementById("system_status").style.display=="block") {
				document.getElementById("system_status").style.display="none";
				document.getElementById("folders_tab").src=folders_off.src;
				document.formSaveLayout.layout_home_show_queues.value = 0;
			}
			else {
				document.getElementById("system_status").style.display="block";
				document.getElementById("folders_tab").src=folders_on.src;
				document.formSaveLayout.layout_home_show_queues.value = 1;
			}
		}
	}

	function toggleSearch() {
		if (document.getElementById) {
			if(document.getElementById("search").style.display=="block") {
				document.getElementById("search").style.display="none";
				document.getElementById("search_tab").src=search_off.src;
				document.formSaveLayout.layout_home_show_search.value = 0;
			}
			else {
				document.getElementById("search").style.display="block";
				document.getElementById("search_tab").src=search_on.src;
				document.formSaveLayout.layout_home_show_search.value = 1;
			}
		}
	}
	
	function savePageLayout() {
		// [JAS]: Force submit the form
		document.formSaveLayout.submit();
	}

</script>
{/literal}

<br>
<table width="100%" border="0" cellspacing="5" cellpadding="1">
  <form action="index.php" name="formSaveLayout" method="post">
  	<input type="hidden" name="sid" value="{$session_id}">
  	<input type="hidden" name="form_submit" value="save_layout">
  	
  	<input type="hidden" name="layout_view_options_av" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_view_options_av}">
  	<input type="hidden" name="layout_view_options_uv" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_view_options_uv}">
  	<input type="hidden" name="layout_home_show_queues" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_show_queues}">
  	<input type="hidden" name="layout_home_show_search" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_show_search}">
  	<input type="hidden" name="layout_home_header_advanced" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_header_advanced}">
  </form>
  <tr> 
    <td width="1%" valign="top" nowrap> 
		{include file="home/system_status.tpl.php" col_span=3}
	</td>
    <td valign="top" width="99%"> 
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td>
				{include file="views/ticket_view.tpl.php" view=$a_view col_span=$a_view->view_colspan}
				<br>
				{include file="views/ticket_view.tpl.php" view=$u_view col_span=$u_view->view_colspan}
				
				{include file="search/quick_search.tpl.php"}
				<br>
				{include file="home/whos_online_box.tpl.php"}
				</td>
			</tr>
		</table>
    </td>
  </tr>
</table>
{include file="footer.tpl.php"}
</body>
</html>