<script>
{literal}
	function addModule()
	{
		box1 = document.layout_customize_form.layout_display_enabled;
		box2 = document.layout_customize_form.layout_display_disabled;

		if(box2.selectedIndex == -1) return;
		
		mobile = box2.options[box2.selectedIndex];
		box2.options[box2.selectedIndex] = null;
		box1.options[box1.length] = mobile;
		
		listModules();
	}
	
	function removeModule()
	{
		box1 = document.layout_customize_form.layout_display_enabled;
		box2 = document.layout_customize_form.layout_display_disabled;

		if(box1.selectedIndex == -1) return;

		mobile = box1.options[box1.selectedIndex];
		box1.options[box1.selectedIndex] = null;
		box2.options[box2.length] = mobile;
		
//		listModules();
		saveListState(box1, savebox);
	}
	
	function resetModules()
	{
		var uni = getCacheKiller();
		var url = "my_cerberus.php?mode=layout&form_submit=layout&layout_display_reset=1&sid={/literal}{$session_id}{literal}&ck=" + uni + "#layout_display";
		document.location = url;
	}
	
	function upModule(box1, savebox)
	{
	    moveUp(box1);
	    saveListState(box1,savebox);		
	}
	
	function downModule(box1, savebox)
	{
	    moveDown(box1);
	    saveListState(box1,savebox);
	}
	
	function saveLayoutState(f)
	{
		saveListState(f.layout_display_enabled, f.layout_display_modules);
		return true;
	}	
	
{/literal}
</script>

<br>
<span class="cer_knowledgebase_heading">Custom Layout for {$session->vars.login_handler->user_name} ({$session->vars.login_handler->user_login})</span><br>
<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post" name="layout_customize_form" onsubmit="javascript:return saveLayoutState(this);">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="layout">
<input type="hidden" name="form_submit" value="layout">
<input type="hidden" name="layout_display_modules" value="">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_orange_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_LAYOUT_PAGE_DISPLAY}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  
  {* Ticket Display Layout Customization *}
  
  <a name="layout_display">
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
		
    	<table cellspacing="1" cellpadding="2" border="0">
    		<tr>
    			<td class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_LAYOUT_ENABLED_MODULES}</td>
    			<td></td>
    			<td class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_LAYOUT_DISABLED_MODULES}</td>
    		</tr>
    		
    		<tr>
    			<td>
    				<select size="7" name="layout_display_enabled">
    					{foreach from=$user_layout->layout_pages.display->params.display_modules item=module name=module}
    						{include file="my_cerberus/tabs/my_cerberus_layout_display_modules_list.tpl.php" module=$module}
    					{/foreach}
    				</select>
    			</td>
    			<td>
    				<input type="button" value="&lt;- Enable" class="cer_footer_text" onclick="javascript:addModule();"><br>
    				<input type="button" value="Disable -&gt;" class="cer_footer_text" onclick="javascript:removeModule();"><br>
    				<br>
    				<input type="button" value="&lt;- Move Up" class="cer_footer_text" onclick="javascript:upModule(this.form.layout_display_enabled, this.form.layout_display_modules);"><br>
    				<input type="button" value="&lt;- Move Down" class="cer_footer_text" onclick="javascript:downModule(this.form.layout_display_enabled, this.form.layout_display_modules);"><br>
    				<br>
    				<input type="button" value="Reset to Defaults" class="cer_footer_text" onclick="javascript:resetModules();"><br>
    			</td>
    			<td>
    				<select size="7" name="layout_display_disabled">
    					{foreach from=$user_layout->layout_pages.display->params.display_modules_unused item=module name=module}
    						{include file="my_cerberus/tabs/my_cerberus_layout_display_modules_list.tpl.php" module=$module}
    					{/foreach}
    				</select>
    			</td>
    		</tr>
    	</table>
    
    </td>
  </tr>
  
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  
  
  
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
		  <input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_UPDATE}">
		</td>
	</tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

</form>
