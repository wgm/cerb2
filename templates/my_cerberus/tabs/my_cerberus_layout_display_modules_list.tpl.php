{if $module == "properties"}
	<option value="properties">{$smarty.const.LANG_DISPLAY_GLANCE} / {$smarty.const.LANG_DISPLAY_VITAL_SIGNS}
{/if}
{if $module == "history"}
	<option value="history">{$smarty.const.LANG_DISPLAY_CUST_HISTORY}
{/if}
{if $module == "log"}
	<option value="log">{$smarty.const.LANG_AUDIT_LOG_TITLE}
{/if}
{if $module == "sla"}
	<option value="sla">{$smarty.const.LANG_DISPLAY_COMPANYCONTACT}
{/if}
{if $module == "suggestions"}
	<option value="suggestions">{$smarty.const.LANG_FNR_TITLE}
{/if}
{if $module == "fields"}
	<option value="fields">{$smarty.const.LANG_CONFIG_CUSTOM_FIELDS}
{/if}
{if $module == "threads"}
	<option value="threads">{$smarty.const.LANG_DISPLAY_THREAD}
{/if}
