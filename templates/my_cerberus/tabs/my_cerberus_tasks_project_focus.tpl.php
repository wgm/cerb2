<br>
<span class="cer_knowledgebase_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_HEADER} {$dashboard->tasks->active_project->project_name|short_escape}</span><br>
<span class="cer_maintable_heading">(<a href="#task_list" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_PROJECTTASKLIST}</a>)</span>
<span class="cer_maintable_heading">(<a href="#task_new" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_NEWTASK}</a>)</span>
<span class="cer_maintable_heading">(<a href="{$urls.tab_tasks}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_RETURNTOLIST}</a>)</span>
<br>
<br>

<script>
{literal}
	function addMember()
	{
		box1 = document.task_project_edit_form.project_members;
		box2 = document.task_project_edit_form.available_users;

		if(box2.selectedIndex == -1) return;
		
		mobile = box2.options[box2.selectedIndex];
		box2.options[box2.selectedIndex] = null;
		box1.options[box1.length] = mobile;
		
		listMembers();
	}
	
	function removeMember()
	{
		box1 = document.task_project_edit_form.project_members;
		box2 = document.task_project_edit_form.available_users;

		if(box1.selectedIndex == -1) return;

		mobile = box1.options[box1.selectedIndex];
		box1.options[box1.selectedIndex] = null;
		box2.options[box2.length] = mobile;
		
		listMembers();
	}
	
	function unassignAll()
	{
		box1 = document.task_project_edit_form.project_members;
		box2 = document.task_project_edit_form.available_users;
		
		for(x=0;box1.length > 0; x++)
		{
			mobile = box1.options[0];
			box1.options[0] = null;
			box2.options[box2.length] = mobile;
		}
	}
	
	function assignAll()
	{
		box1 = document.task_project_edit_form.project_members;
		box2 = document.task_project_edit_form.available_users;
		
		for(x=0;box2.length > 0; x++)
		{
			mobile = box2.options[0];
			box2.options[0] = null;
			box1.options[box1.length] = mobile;
		}
	}
	
	function listMembers()
	{
		box1 = document.task_project_edit_form.project_members;
		box2 = document.task_project_edit_form.available_users;
		box3 = document.task_project_edit_form.project_acl;
		members = '';
		
		for(x=0;x < box1.length; x++)
		{
			members = members + '' + box1.options[x].value;
			if(x + 1 != box1.length) members = members + ',';
		}
		
		box3.value = members;
	}
	
	function validateProjectUpdate()
	{
		if (document.task_project_edit_form.project_delete.checked == true) {
			if(confirm("{/literal}{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE_SURE}{literal}"))
				return true;
			else
				return false;			
		}
		
		return true;
	}
	
{/literal}
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" name="task_project_edit_form" method="post" OnSubmit="javascript:return validateProjectUpdate();">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="pid" value="{$dashboard->tasks->active_project->project_id}">
<input type="hidden" name="project_acl" value="{$dashboard->tasks->active_project->project_acl}">
<input type="hidden" name="form_submit" value="task_project_details">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_PROJECTNAME}&nbsp;</td>
			<td width="99%" valign="top">
				{if $dashboard->tasks->active_project->writable === true}
					<input type="text" name="project_name" maxlength="255" size="70" value="{$dashboard->tasks->active_project->project_name|short_escape}">
				{else}
					<span class="cer_maintable_heading">{$dashboard->tasks->active_project->project_name|short_escape}</span>
				{/if}
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_MANAGER}</td>
			<td width="99%" valign="top" class="cer_maintable_text">
				{$dashboard->tasks->active_project->project_manager_name} 
				(<B>{$dashboard->tasks->active_project->project_manager_login}</B>)
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_RESOURCES}</td>
			<td width="99%" valign="top">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_MEMBERS}</span></td>
						{if $dashboard->tasks->active_project->writable === true}
							<td></td>
							<td><span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_AVAILABLE}</span></td>
						{/if}
					</tr>
					<tr>
						<td>
							<select name="project_members" size="5" style="width=300">
								{foreach from=$dashboard->tasks->active_project->project_members item=member}
									<option value="{$member->user_id}">{$member->user_name} ({$member->user_login})
								{/foreach}
							</select>{if $dashboard->tasks->active_project->writable === true}<br><input type="button" value="{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_UNASSIGNALL}" class="cer_button_face" onclick="javascript: unassignAll();"">{/if}</td>
						{if $dashboard->tasks->active_project->writable === true}
							<td valign="top" align="center">
								<input type="button" value="&lt;" onclick="javascript: addMember();"><br>
								<br>
								<input type="button" value="&gt;" onclick="javascript: removeMember();">
							</td>
						<td>
							<select name="available_users" size="5" style="width=300">
								{foreach from=$dashboard->tasks->active_project->available_users item=user}
									<option value="{$user->user_id}">{$user->user_name} ({$user->user_login})
								{/foreach}
							</select>{if $dashboard->tasks->active_project->writable === true}<br><input type="button" value="{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_ASSIGNALL}" class="cer_button_face" onclick="javascript: assignAll();">{/if}</td>
						{/if}
					</tr>
				</table>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		
		{if $dashboard->tasks->active_project->writable === true}

		{if $dashboard->tasks->project_prefs.pm_brief == 1}
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES}</td>
			<td width="99%" valign="top" class="cer_maintable_text">
				<table border="0" cellspacing="1" cellpadding="1">
					<tr bgcolor="#666666">
						<td width="1%" class="cer_maintable_header" align="center" nowrap>{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE}</td>
						<td width="99%" class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_TASKCATEGORYNAME}</td>
					</tr>
					
					{foreach from=$dashboard->tasks->active_project->categories item=cat}
					<tr bgcolor="#CCCCCC">
						<td align="center"><input type="checkbox" name="pcids[]" value="{$cat->category_id}"></td>
						<td class="cer_maintable_text">{$cat->category_name|short_escape}</td>
					</tr>
					{/foreach}
					
					<tr>
						<td colspan="2">
							<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_ADD}</span> 
							<input type="text" name="add_project_category_name" size="45" maxlength="128">
							<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_ADD_INFO}</span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		
		<tr>
			<td width="1%" nowrap class="cer_maintable_text" valign="top" bgcolor="#EEEEEE">
				<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE}</span> 
			</td>
			<td width="99%" valign="top" bgcolor="#EEEEEE">
				<input type="checkbox" name="project_delete" value="1">
				<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE_INFO}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		
		{/if} {* End project manager panel logic *}
		
		<tr>
			<td colspan="2" valign="top" bgcolor="#EEEEEE" class="cer_maintable_text">
				[ 
				<a href="{$dashboard->tasks->active_project->heading_urls.manager_panel}" class="cer_maintable_heading">{if $dashboard->tasks->project_prefs.pm_brief == 1}{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_TOGGLE_BRIEF}{else}{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_TOGGLE_ADVANCED}{/if}</a>
				]
			</td>
		</tr>
		{/if}

		
	</table>
	{if $dashboard->tasks->active_project->writable === true}
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

<a name="task_list">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php#task_list" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="pid" value="{$dashboard->tasks->active_project->project_id}">
<input type="hidden" name="form_submit" value="tasks_filter">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_orange_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_HEADER} '{$dashboard->tasks->active_project->project_name|short_escape}'</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
	{if count($dashboard->tasks->active_project->tasks) }
		<tr bgcolor="#BBBBBB">
			<td align="left">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_summary}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_TASKNAME}</a></td>
			<td align="left">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_category}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_CATEGORY}</a>&nbsp;</td>
			<td align="left">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_assigned}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ASSIGNED}</a>&nbsp;</td>
			<td align="center">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_priority}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_PRIORITY}</a>&nbsp;</td>
			<td align="left">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_updated}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_UPDATED}</a>&nbsp;</td>
			<td align="left">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_due}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_DUE}</a>&nbsp;</td>
			<td align="center">&nbsp;<span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_NOTES}</span>&nbsp;</td>
			<td align="center">&nbsp;<a href="{$dashboard->tasks->active_project->heading_urls.task_progress}" class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_PROGRESS}</a>&nbsp;</td>
		</tr>
		<tr><td colspan="8" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{foreach from=$dashboard->tasks->active_project->tasks item=task name=tasks}
		<tr class="{if $smarty.foreach.tasks.iteration % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
			<td colspan="8">
				 &nbsp;<a href="{$task->task_url}" class="cer_maintable_heading">{$task->task_summary|short_escape|truncate:120}</a>
			</td>
		</tr>
		<tr class="{if $smarty.foreach.tasks.iteration % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
			<td>
				<span class="cer_footer_text">&nbsp;#{$task->task_id|string_format:"%05d"}</span>
			</td>
			<td class="cer_footer_text" align="left">&nbsp;
				{$task->task_project_category_name|short_escape}
			</td>
			<td class="cer_footer_text" align="left">&nbsp;
			{if !empty($task->task_assigned_user) }
				{$task->task_assigned_user} 
			{/if}
			{if !empty($task->task_assigned_login) }
				(<b>{$task->task_assigned_login}</b>)
			{/if}
			</td>
			<td class="cer_footer_text" align="center">
				{assign var=priority value=$task->task_priority}
				{if $priority == 100}<font color="red"><b>{/if}
				{$task->task_priority_string}
				{if $priority == 100}</b></font>{/if}
			</td>
			<td class="cer_footer_text" align="left">&nbsp;
				{$task->task_updated_date}
			</td>
			<td class="cer_footer_text" align="left">&nbsp;
				{if $task->task_past_due === true}<font color="red"><b>{/if}
				{$task->task_due_date_mdy}
				{if $task->task_past_due === true}</b></font>{/if}
			</td>
			<td class="cer_footer_text" align="center">
				{$task->task_note_count}
			</td>
			<td class="cer_footer_text" align="center">
				{assign var=progress value=$task->task_progress}
				{if $progress == 100}<b>{/if}
				{$progress}%
				{if $progress == 100}</b>{/if}
			</td>
		</tr>
		<tr><td colspan="8" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{/foreach}
	{else}
		<tr>
			<td><span class="cer_maintable_text">
			{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_NOTASKS}</span>
			</td>
		</tr>
	{/if}
	
		<tr><td colspan="8" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr bgcolor="#999999" class="cer_maintable_text">
        	<td colspan="8" align="right">
        		<span class="cer_maintable_header"><B>{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ONLYINCATEGORY}</B></span>
        		<select name="filter_category" class="cer_footer_text">
        			<option value=""> {$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ANYCATEGORY}
					{foreach from=$dashboard->tasks->active_project->categories item=cat}
						<option value="{$cat->category_id}" {if $dashboard->tasks->project_prefs.filter_category && $dashboard->tasks->project_prefs.filter_category == $cat->category_id}selected{/if}>{$cat->category_name|short_escape}
					{/foreach}
        		</select>
        		<input type="checkbox" name="filter_hide_completed" value="1" {if $dashboard->tasks->project_prefs.filter_hide_completed == 1}checked{/if}> <span class="cer_maintable_header"><B>{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_HIDECOMPLETED}</B></span>
        		<input type="checkbox" name="filter_only_my_tasks" value="1" {if $dashboard->tasks->project_prefs.filter_only_my_tasks}checked{/if}> <span class="cer_maintable_header"><B>{$smarty.const.LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ONLYMY}</B></span>
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_FILTER}">
        	</td>
      	</tr>
      	
	</table>
	<table border=0 cellspacing=0 cellpadding=0 width="100%">
	</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>
</form>
