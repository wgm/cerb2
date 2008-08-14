{* Organize the ticket display page according to the user's layout preferences *}

{foreach from=$user_layout->layout_pages.display->params.display_modules item=module name=module}

	{if $module == "properties"}
		{* Ticket Properties at a Glance + Ticket Vitals *}
		{include file="display/display_ticket_properties_vitals.tpl.php"}
	{/if}
	
	{if $module == "history"}
		{* Customer/Company Support History *}
		{include file="display/display_ticket_history.tpl.php" col_span=7}
	{/if}
	
	{if $module == "log"}
		{* Ticket Audit Log *}
		{include file="display/display_ticket_audit_log.tpl.php" col_span=3}
	{/if}
	
	{if $module == "sla"}
		{* SLA/Company Info *}
		{include file="display/display_ticket_company_sla.tpl.php" col_span=3}
	{/if}
	
	{if $module == "suggestions"}
		{* AI Suggestion *}
		{include file="display/display_ticket_suggestion.tpl.php" col_span=7}
	{/if}
	
	{if $module == "fields"}
		{* Custom Fields *}
		{include file="display/display_ticket_custom_fields.tpl.php"}
	{/if}
	
	{if $module == "threads"}
		{* Ticket Threads *}
		{include file="display/display_ticket_threads.tpl.php"}
	{/if}

{/foreach}