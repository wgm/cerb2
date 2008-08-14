<br>
<span class="cer_knowledgebase_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_HEADER}</span><br>
<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="form_submit" value="projects_filter">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_orange_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
	{if !empty($dashboard->tasks)}
		<tr bgcolor="#BBBBBB">
			<td class="cer_maintable_heading">&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_NAME}</td>
			<td class="cer_maintable_heading" align="center">{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_TOTAL}</td>
			<td class="cer_maintable_heading" align="center">{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_INCOMPLETE}</td>
			<td class="cer_maintable_heading" align="center">{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_COMPLETE}</td>
			<td class="cer_maintable_heading" align="left">{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_MANAGER}</td>
		</tr>
		<tr><td colspan="5" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{foreach key=key from=$dashboard->tasks->projects item=project name=project}
		<tr class="{if $smarty.foreach.project.iteration % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
			<td>
			<a href="{$project->project_url}" class="cer_maintable_heading">{$project->project_name|short_escape}</a>
			</td>
			<td class="cer_footer_text" align="center">
				{$project->task_count}
			</td>
			<td class="cer_footer_text" align="center">
				{$project->tasks_incomplete}
			</td>
			<td class="cer_footer_text" align="center">
				{$project->tasks_complete}
			</td>
			<td class="cer_footer_text" align="left">
				{$project->project_manager_name} (<b>{$project->project_manager_login}</b>)
			</td>
		</tr>
		<tr><td colspan="5" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		{/foreach}
	{else}
		<tr>
			<td><span class="cer_maintable_text">
			No projects defined.</span>
			</td>
		</tr>
	{/if}
	</table>

	<table border=0 cellspacing=0 cellpadding=0 width="100%">
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
        	<td align="right">
        		<input type="checkbox" name="filter_hide_completed_projects" value="1" {if $dashboard->tasks->project_prefs.filter_hide_completed_projects}checked{/if}> <span class="cer_maintable_header"><B>{$smarty.const.LANG_MYCERBERUS_PROJECTS_ACTIVE_HIDECOMPLETED}</B></span>
        		<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_FILTER}">
        	</td>
      	</tr>
	</table>

    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>

</form>
