{assign var="col_span" value="2"}
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

<script>
{literal}
	function selectTopScript() {
		for(e=0;document.upgrade_form.elements.length;e++) {
			if(document.upgrade_form.elements[e].type == "radio") {
				document.upgrade_form.elements[e].checked = 1;
				break;
			}
		}
	}
{/literal}
</script>

</head>

<body onload="selectTopScript();">
<img src="logo.gif"><br>
<br>
<span class="cer_display_header">Cerberus Helpdesk: Database Upgrade/Sync Tool</span><br>
<br>

<table border="0" cellspacing="0" cellpadding="0">
<form action="upgrade.php" method="post" name="upgrade_form">
<input type="hidden" name="form_submit" value="upgrade">
	<!---
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	<tr>
		<td colspan="{$col_span}" class="cer_home_preferences_background">
			<span class="cer_maintable_header">&nbsp; The following database upgrade scripts are available:&nbsp; &nbsp;</span>
		</td>
	</tr>	
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	--->
	
	{if !empty($cer_updater->ptrs_scripts_clean)}
		<td colspan="{$col_span}" bgcolor="#0099FF">
			<span class="cer_maintable_header">&nbsp; Brand New Installation Scripts:</span>
		</td>

		{include file="upgrade/upgrade_script_list.tpl.php" script_list=$cer_updater->ptrs_scripts_clean}
	{/if}

	{if !empty($cer_updater->ptrs_scripts_upgrade)}
		<td colspan="{$col_span}" bgcolor="#0099FF">
			<span class="cer_maintable_header">&nbsp; Upgrade Scripts:</span>
		</td>

		{include file="upgrade/upgrade_script_list.tpl.php" script_list=$cer_updater->ptrs_scripts_upgrade}
	{/if}
	
	{if !empty($cer_updater->ptrs_scripts_verify)}
		<td colspan="{$col_span}" bgcolor="#0099FF">
			<span class="cer_maintable_header">&nbsp; Database Verification Scripts:</span>
		</td>

		{include file="upgrade/upgrade_script_list.tpl.php" script_list=$cer_updater->ptrs_scripts_verify}
	{/if}
	
	{if $cer_updater->active_scripts}
      <tr bgcolor="#FFFFFF"> 
        <td colspan="{$col_span}" align="right"><input type="submit" class="cer_button_face" value="Run Script"></td>
      </tr>
	{/if}
	
</form>
</table>

<br>
<a href="index.php" class="cer_maintable_text">Return to Cerberus Helpdesk</a>

{include file="footer.tpl.php"}
</body>
</html>