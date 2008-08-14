{if !empty($dashboard->tasks->active_project) && !empty($dashboard->tasks->active_project->active_task) }
	{include file="my_cerberus/tabs/my_cerberus_tasks_view.tpl.php"}
{elseif !empty($dashboard->tasks->active_project) }
	{include file="my_cerberus/tabs/my_cerberus_tasks_project_focus.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_tasks_create.tpl.php"}
{else}
	{include file="my_cerberus/tabs/my_cerberus_tasks_list.tpl.php"}
	{include file="my_cerberus/tabs/my_cerberus_tasks_create_project.tpl.php"}
{/if}
