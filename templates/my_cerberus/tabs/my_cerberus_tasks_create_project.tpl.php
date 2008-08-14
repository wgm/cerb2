<script>
{literal}
	function addMember()
	{
		box1 = document.task_project_create_form.project_members;
		box2 = document.task_project_create_form.available_users;

		if(box2.selectedIndex == -1) return;
		
		mobile = box2.options[box2.selectedIndex];
		box2.options[box2.selectedIndex] = null;
		box1.options[box1.length] = mobile;
		
		listMembers();
	}
	
	function removeMember()
	{
		box1 = document.task_project_create_form.project_members;
		box2 = document.task_project_create_form.available_users;

		if(box1.selectedIndex == -1) return;

		mobile = box1.options[box1.selectedIndex];
		box1.options[box1.selectedIndex] = null;
		box2.options[box2.length] = mobile;
		
		listMembers();
	}
	
	function listMembers()
	{
		box1 = document.task_project_create_form.project_members;
		box2 = document.task_project_create_form.available_users;
		box3 = document.task_project_create_form.project_acl;
		members = '';
		
		for(x=0;x < box1.length; x++)
		{
			members = members + '' + box1.options[x].value;
			if(x + 1 != box1.length) members = members + ',';
		}
		
		box3.value = members;
	}
{/literal}
</script>

<a name="create_project">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" name="task_project_create_form" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="tasks">
<input type="hidden" name="project_acl" value="">
<input type="hidden" name="form_submit" value="task_project_create">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_glass"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_PROJECTNAME}&nbsp;</td>
			<td width="99%" valign="top">
				<input type="text" name="project_name" maxlength="255" size="64">
				<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_PROJECTNAME_INSTRUCTIONS}</span>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_MANAGER}</td>
			<td width="99%" valign="top" class="cer_maintable_text">
				<select name="project_manager_uid">	
					{foreach from=$dashboard->tasks->user_list item=user}
						<option value="{$user->user_id}" {if $user->user_id == $session->vars.login_handler->user_id}selected{/if}>{$user->user_name} ({$user->user_login})
					{/foreach}
				</select>
			</td>
		</tr>
		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr>
			<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_RESOURCES}</td>
			<td width="99%" valign="top">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_MEMBERS}</span></td>
						<td></td>
						<td><span class="cer_maintable_heading">{$smarty.const.LANG_MYCERBERUS_PROJECTS_CREATE_AVAILABLE}</span></td>
					</tr>
					<tr>
						<td>
							<select name="project_members" size="5" style="width=300">
								{foreach from=$dashboard->tasks->active_project->project_members item=member}
									<option value="{$member->user_id}">{$member->user_name} ({$member->user_login})
								{/foreach}
							</select>
						</td>
							<td valign="middle" align="center">
								<input type="button" value="&lt;" onclick="javascript: addMember();"><br>
								<br>
								<input type="button" value="&gt;" onclick="javascript: removeMember();">
							</td>
						<td>
							<select name="available_users" size="5" style="width=300">
								{foreach from=$dashboard->tasks->user_list item=user}
									<option value="{$user->user_id}">{$user->user_name} ({$user->user_login})
								{/foreach}
							</select>
						</td>
					</tr>
				</table>
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
