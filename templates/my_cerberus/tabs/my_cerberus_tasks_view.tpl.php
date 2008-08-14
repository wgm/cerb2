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
	
	function validateTaskUpdate()
	{
		if (document.task_view_form.task_delete.checked == true) {
			if(confirm("{/literal}{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_DELETE_SURE}{literal}"))
				return true;
			else
				return false;			
		}
		
		return true;
	}
	
	function validateTaskDelete(nid,tid,pid)
	{
		if(confirm("{/literal}{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_DELETE_SURE}{literal}"))
		{
			url = "my_cerberus.php?mode=tasks&form_submit=task_delete_note&nid=" + nid + "&pid=" + pid + "&tid=" + tid;
			document.location = formatURL(url) + "#task_notes";
		}
	}
	
	{/literal}
</SCRIPT>

<br>
<span class="cer_knowledgebase_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_HEADER} {$dashboard->tasks->active_project->project_name|short_escape}</span><br>
<span class="cer_maintable_heading">
(<a href="{$dashboard->tasks->active_project->project_url}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_RETURNTOPROJECT}</a>)
(<a href="{$urls.tab_tasks}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_RETURNTOPROJECTLIST}</a>)
</span>
<br>
<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" name="task_view_form" method="post" OnSubmit="javascript:return validateTaskUpdate();">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="form_submit" value="task_update">
<input type="hidden" name="task_project_id" value="{$dashboard->tasks->active_project->project_id}">
<input type="hidden" name="pid" value="{$dashboard->tasks->active_project->project_id}">
<input type="hidden" name="tid" value="{$dashboard->tasks->active_project->active_task->task_id}">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_orange_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_HEADER} {$dashboard->tasks->active_project->active_task->task_summary|short_escape}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
	
		{if $dashboard->tasks->active_project->active_task->writable !== true}
		<tr>
			<td colspan="2" class="cer_maintable_text" valign="top" bgcolor="#EEEEEE">
				<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_NOTE}</span> {$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_INFO}
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{/if}

		{if $dashboard->tasks->active_project->active_task->writable === true}
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_SUMMARY}</td>
			<td width="99%" valign="top">
				<input type="text" name="task_summary" maxlength="255" size="64" value="{$dashboard->tasks->active_project->active_task->task_summary|short_escape}">
				<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_SUMMARY_INSTRUCTIONS}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{/if}

		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_CATEGORY}</td>
			<td width="99%" valign="top" class="cer_maintable_text">
				{if $dashboard->tasks->active_project->active_task->writable === true}
				<select name="task_project_category_id">
					<option value="0">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_CATEGORY_NONE}
					{foreach from=$dashboard->tasks->active_project->categories item=cat}
						<option value="{$cat->category_id}" {if $cat->category_id == $dashboard->tasks->active_project->active_task->task_project_category_id}SELECTED{/if}>
						{$cat->category_name|short_escape}
					{/foreach}
				</select>
				{else}
					<span class="cer_maintable_text">{$dashboard->tasks->active_project->active_task->task_project_category_name|short_escape}</span>
				{/if}
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		
		<tr>
			<td width="1%" valign="top" colspan="2">
				<span class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO}</span>
				
				{if $dashboard->tasks->active_project->active_task->writable !== true}
					{if !empty($dashboard->tasks->active_project->active_task->task_assigned_name) }
						{$dashboard->tasks->active_project->active_task->task_assigned_name}
					{else}
						<span class="cer_maintable_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_NOBODY}</span>
					{/if}
				{else}
					<select name="task_assigned_uid">
						<option value="0">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_NOBODY}
						{foreach from=$dashboard->tasks->active_project->project_members item=user}
							<option value="{$user->user_id}" {if $dashboard->tasks->active_project->active_task->task_assigned_uid == $user->user_id}selected{/if}>
							{$user->user_name} ({$user->user_login})
						{/foreach}
					</select>
				{/if}
				
				&nbsp;
				<span class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_PROGRESS}</span>
				{if $dashboard->tasks->active_project->active_task->writable !== true}
					<span class="cer_maintable_text">{$dashboard->tasks->active_project->active_task->task_progress}%</span>
				{else}
					<select name="task_progress">
						{html_options options=$dashboard->tasks->progress_options selected=$dashboard->tasks->active_project->active_task->task_progress}
					</select>
				{/if}
				
				&nbsp;
				<span class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_PRIORITY}</span>
				{if $dashboard->tasks->active_project->active_task->writable !== true}
					<span class="cer_maintable_text">{$dashboard->tasks->active_project->active_task->task_priority_string}</span>
				{else}
					<select name="task_priority">
						{html_options options=$dashboard->tasks->priority_options selected=$dashboard->tasks->active_project->active_task->task_priority}
					</select>				
				{/if}				
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DUE}</td>
			<td width="99%" valign="top">
				{if $dashboard->tasks->active_project->active_task->writable !== true}
					<span class="cer_maintable_text">{$dashboard->tasks->active_project->active_task->task_due_date}</span>
				{else}
					<input type="text" name="task_due_date" maxlength="8" size="8" value="{$dashboard->tasks->active_project->active_task->task_due_date_mdy}">
					<span class="cer_footer_text">(<a href="javascript: calendarPopUp('document.task_view_form.task_due_date');" class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_CALENDAR}</a> {$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_INSTRUCTIONS}</span>
				{/if}
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_REMINDER}</td>
			<td width="99%" valign="top">
				{if $dashboard->tasks->active_project->active_task->writable !== true}
					<span class="cer_maintable_text">{$dashboard->tasks->active_project->active_task->task_reminder_date}</span>
				{else}
					<input type="text" name="task_reminder_date" maxlength="8" size="8" value="{$dashboard->tasks->active_project->active_task->task_reminder_date_mdy}">
					<span class="cer_footer_text">(<a href="javascript: calendarPopUp('document.task_view_form.task_reminder_date');" class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_CALENDAR}</a> {$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_INSTRUCTIONS}</span>
				{/if}
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DESCRIPTION}&nbsp;</td>
			<td width="99%" valign="top" class="cer_display_emailText">
				{if $dashboard->tasks->active_project->active_task->writable !== true}
					{$dashboard->tasks->active_project->active_task->task_description|replace:"<":"&lt;"|replace:">":"&gt;"|replace:"  ":"&nbsp;&nbsp;"|regex_replace:"/(\n|^) /":"\n&nbsp;"|makehrefs:true:"cer_display_emailText"|nl2br}
				{else}
					<textarea class="cer_display_emailText" name="task_description" rows="6" cols="50">{$dashboard->tasks->active_project->active_task->task_description|short_escape}</textarea>
				{/if}
			</td>
		</tr>
		
		{if $dashboard->tasks->active_project->writable === true}
		<tr>
			<td width="1%" nowrap class="cer_maintable_text" valign="top" bgcolor="#EEEEEE">
				<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_DELETE}</span> 
			</td>
			<td width="99%" valign="top" bgcolor="#EEEEEE">
				<input type="checkbox" name="task_delete" value="1">
				<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_DELETE_INSTRUCTIONS}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{/if}
		
	</table>
	
	{if $dashboard->tasks->active_project->active_task->writable === true}
	<table border=0 cellspacing=0 cellpadding=0 width="100%">
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right">
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
        	</td>
      	</tr>
	</table>
	{/if}
	
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>
</form>

{assign var=col_span value="3"}
<a name="task_notes">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="cer_home_preferences_background_3"> 
    <td colspan="{$col_span}" class="cer_maintable_header">&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  {section name=note loop=$dashboard->tasks->active_project->active_task->task_notes}

  <tr bgcolor="#CCCCCC" class="cer_maintable_text"> 
	<td class="cer_maintable_text" width="1%" nowrap valign="top">
		<b>{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_POSTER} {$dashboard->tasks->active_project->active_task->task_notes[note]->note_poster_login}</b>&nbsp;<br>
		<span class="cer_footer_text">
			{$dashboard->tasks->active_project->active_task->task_notes[note]->note_timestamp}<br>
			
			{if $dashboard->tasks->active_project->writable === true}
			<a href="javascript:validateTaskDelete({$dashboard->tasks->active_project->active_task->task_notes[note]->note_id},{$dashboard->tasks->active_project->active_task->task_id},{$dashboard->tasks->active_project->project_id});" class="cer_footer_text">{$smarty.const.LANG_WORD_DELETE}</a>
			{/if}
			
		</span><br>
	</td>
	<td width="1" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td>
	<td width="99%" bgcolor="#DDDDDD" valign="top" style="padding-left: 2px;">
		<span class="cer_display_emailText">{$dashboard->tasks->active_project->active_task->task_notes[note]->note_text|replace:"<":"&lt;"|replace:">":"&gt;"|replace:"  ":"&nbsp;&nbsp;"|regex_replace:"/(\n|^) /":"\n&nbsp;"|makehrefs:true:"cer_display_emailText"|nl2br}</span><br>
		<br>
	</td>
  </tr>
  <tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  
  {sectionelse}
	  <tr><td colspan="{$col_span}" bgcolor="#DDDDDD" class="cer_maintable_text">&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_NONE}</td></tr>
	  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  {/section}
  
  {if count($dashboard->tasks->active_project->active_task->task_notes) != 0 }
	  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  {/if}
</table>

<br>

{assign var=col_span value="1"}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" name="task_note_form" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="form_submit" value="task_add_note">
<input type="hidden" name="pid" value="{$dashboard->tasks->active_project->project_id}">
<input type="hidden" name="tid" value="{$dashboard->tasks->active_project->active_task->task_id}">

	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	<tr class="cer_home_preferences_background_2"> 
	<td colspan="{$col_span}" class="cer_maintable_header">&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_ADD_HEADER}</td>
	</tr>
	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	<tr>
		<td valign="top" bgcolor="#DDDDDD">
			<textarea class="cer_display_emailText" name="note_text" rows="6" cols="50"></textarea>
		</td>
	</tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
    	<td align="right">
    		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
    	</td>
  	</tr>

</form>
</table>
