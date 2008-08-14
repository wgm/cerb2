<SCRIPT>
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
</SCRIPT>

<a name="task_new">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" name="task_create_form" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="form_submit" value="task_create">
<input type="hidden" name="task_project_id" value="{$dashboard->tasks->active_project->project_id}">
<input type="hidden" name="pid" value="{$dashboard->tasks->active_project->project_id}">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_green_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_HEADER} '{$dashboard->tasks->active_project->project_name|short_escape}'</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_SUMMARY}</td>
			<td width="99%" valign="top">
				<input type="text" name="task_summary" maxlength="255" size="64">
				<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_SUMMARY_INSTRUCTIONS}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<!--
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">Parent Task:</td>
			<td width="99%" valign="top" class="cer_maintable_text">
				<select name="task_parent_id">
					<option value="0">None (Top Level)
				</select>
			</td>
		</tr>
-->
		<input type="hidden" name="task_parent_id" value="0">
		
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_CATEGORY}</td>
			<td width="99%" valign="top" class="cer_maintable_text">
				<select name="task_project_category_id">
					<option value="0">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_CATEGORY_NONE}
					{foreach from=$dashboard->tasks->active_project->categories item=cat}
						<option value="{$cat->category_id}">{$cat->category_name|short_escape}
					{/foreach}
				</select>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		
		<tr>
			<td width="1%" valign="top" colspan="2">
			
				<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO}</span>
				<select name="task_assigned_uid">
					<option value="0">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_NOBODY}
					{foreach from=$dashboard->tasks->active_project->project_members item=user}
						<option value="{$user->user_id}">{$user->user_name} ({$user->user_login})
					{/foreach}
				</select>
			
				&nbsp;
				<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_PROGRESS}</span>
				<select name="task_progress">
					{html_options options=$dashboard->tasks->progress_options}
				</select>
				
				&nbsp;
				<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_PRIORITY}</span>
				<select name="task_priority">
					{html_options options=$dashboard->tasks->priority_options}
				</select>
				
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DUE}</td>
			<td width="99%" valign="top">
				<input type="text" name="task_due_date" maxlength="8" size="8" value="00/00/00">
				<span class="cer_footer_text">(<a href="javascript: calendarPopUp('document.task_create_form.task_due_date');" class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_CALENDAR}</a> {$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_INSTRUCTIONS}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_REMINDER}</td>
			<td width="99%" valign="top">
				<input type="text" name="task_reminder_date" maxlength="8" size="8" value="00/00/00">
				<span class="cer_footer_text">(<a href="javascript: calendarPopUp('document.task_create_form.task_reminder_date');" class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_CALENDAR}</a> {$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_INSTRUCTIONS}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DESCRIPTION}&nbsp;</td>
			<td width="99%" valign="top">
				<textarea name="task_description" rows="6" cols="50"></textarea>
			</td>
		</tr>
	</table>
	<table border=0 cellspacing=0 cellpadding=0 width="100%">
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right">
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>

</form>
