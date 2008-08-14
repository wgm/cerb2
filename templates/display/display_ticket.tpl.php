{if $o_ticket->writeable}
<script>
tkt = {$o_ticket->ticket_id};

{literal}
	function doClone()
  	{
      if(confirm("This will create an identical copy of this ticket's threads, comments, attachments and properties to a new ticket id.\r\nAfter the ticket is cloned a change to one ticket will not affect the other.\r\nAre you sure you want to clone this ticket?"))
      	{
			document.location=formatURL("display.php?form_submit=clone&ticket=" + tkt);				     	
        }
    }
    
	icon_expand = new Image;
	icon_expand.src = "includes/images/icon_expand.gif";
	
	icon_collapse = new Image;
	icon_collapse.src = "includes/images/icon_collapse.gif";

	function toggleThread(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_" + th).style.display=="block") {
				document.getElementById("thread_" + th).style.display="none";
			}
			else {
				document.getElementById("thread_" + th).style.display="block";
			}
		}
	}
	
	function toggleThreadTime(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_track_time_" + th).style.display=="block") {
				document.getElementById("thread_track_time_" + th).style.display="none";
				document.getElementById("thread_track_time_" + th + "_edit").style.display="block";
			}
			else {
				document.getElementById("thread_track_time_" + th).style.display="block";
				document.getElementById("thread_track_time_" + th + "_edit").style.display="none";
			}
		}
	}
	
	function toggleThreadTimeEntry() {
		if (document.getElementById) {
			if(document.getElementById("thread_add_time_entry").style.display=="block") {
				document.getElementById("thread_add_time_entry").style.display="none";
			}
			else {
				document.getElementById("thread_add_time_entry").style.display="block";
			}
		}
	}

	function doTimeEntryAddHelp(prefix,fld) {
		if (document.getElementById) {
			document.getElementById(prefix + "_0").style.display="none";
			document.getElementById(prefix + "_1").style.display="none";
			document.getElementById(prefix + "_2").style.display="none";
			document.getElementById(prefix + "_3").style.display="none";
			
			document.getElementById(prefix + "_" + fld).style.display="block";
		}
	}
	
	var threads_activity_enabled = 1;
	var threads_time_enabled = 1;
	
	function toggleThreadsActivity() {
		
		if(threads_activity_enabled) {
			toggle_to = "none";
			threads_activity_enabled = 0;
		}
		else {
			toggle_to = "block";
			threads_activity_enabled = 1;
		}
		
		if (document.getElementById) {
			{/literal}
				{foreach from=$o_ticket->threads item=thread_ptr}
					{if $thread_ptr->type == "email" || $thread_ptr->type == "comment"}
						document.getElementById("thread_{$thread_ptr->ptr->thread_id}").style.display=toggle_to;
					{/if}	
				{/foreach}
			{literal}
		}
	}
	
	function toggleThreadsTime() {
		
		if(threads_time_enabled) {
			toggle_to = "none";
			threads_time_enabled = 0;
		}
		else {
			toggle_to = "block";
			threads_time_enabled = 1;
		}
		
		if (document.getElementById) {
			{/literal}
				{foreach from=$o_ticket->threads item=thread_ptr}
					{if $thread_ptr->type == "time"}
						document.getElementById("thread_track_time_{$thread_ptr->ptr->thread_time_id}").style.display=toggle_to;
						document.getElementById("thread_track_time_{$thread_ptr->ptr->thread_time_id}_edit").style.display="none";
					{/if}	
				{/foreach}
			{literal}
		}
	}
	
	function toggleThreadOptions(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_" + th + "_options").style.display=="block") {
				document.getElementById("thread_" + th + "_options").style.display="none";
			}
			else {
				document.getElementById("thread_" + th + "_options").style.display="block";
			}
		}
	}

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
</script>
{/if}

{include file="display/display_ticket_heading.tpl.php"}

<table width="100%" border="0" cellspacing="0" cellpadding="2">
{if $o_ticket->writeable}
	<tr>
		<td colspan="2" valign="top" align="right">
		{* Batch Links *}
		{if $o_ticket->batch_id !== false}
			<span class="cer_maintable_heading">TICKET BATCHED</span>&nbsp;
		{/if}
		{if $urls.batch_prev}
			[ <a href="{$urls.batch_prev}" class="cer_maintable_heading">&lt;&lt; Prev Batched</a> ]&nbsp;
		{/if}
		{if $urls.batch_next}
			[ <a href="{$urls.batch_next}" class="cer_maintable_heading">Next Batched &gt;&gt;</a> ]&nbsp;
		{/if}
		
		{if $urls.batch_add}
			[ <a href="{$urls.batch_add}" class="cer_header_loginLink">{$smarty.const.LANG_ACTION_BATCH_ADD}</a> ]&nbsp;
		{/if}
		{if $urls.batch_remove}
			[ <a href="{$urls.batch_remove}" class="cer_header_loginLink">Remove from Batch</a> ]&nbsp;
		{/if}
		
		{* Merge Into *}
		{if $priv->has_priv(ACL_TICKET_MERGE,BITGROUP_2) }
			[ <a href="{$urls.tab_merge}" class="cer_header_loginLink">{$smarty.const.LANG_ACTION_MERGE}</a> ]&nbsp;
		{/if}
		
		{* Clone Link *}
		{if $priv->has_priv(ACL_TICKET_CLONE,BITGROUP_2) }
			[ <a href="javascript:doClone();" class="cer_header_loginLink">{$smarty.const.LANG_ACTION_CLONE}</a> ]&nbsp;
		{/if}
		
 		{* Print Ticket Link *}
 		[ <a href="javascript: printTicket('{$urls.print_ticket}');" class="cer_header_loginLink">{$smarty.const.LANG_ACTION_PRINT}</a> ]

 		{* Ticket Take Link *}
		{if $o_ticket->ticket_assigned_to_id == 0 && $priv->has_priv(ACL_TICKET_TAKE,BITGROUP_1) }
			[ <a href="{$urls.take_ticket}" class="cer_header_loginLink">{$smarty.const.LANG_DISPLAY_PROPS_TAKE}</a> ]
		{else}
			{if isset($ticket_preowned)} {* This needs to be implemented into the template *}
				<span class="cer_configuration_updated">{$smarty.const.LANG_DISPLAY_PROPS_NOTAKE}</span>
			{/if}
		{/if}
		
    	</td>
	</tr>
{else}
	<tr>
		<td colspan=2>&nbsp;</td>
	</tr>
{/if}
</table>

{include file="display/display_ticket_active_modules.tpl.php"}