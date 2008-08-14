{if $oThread->thread_id == $o_ticket->max_thread_id}<a name="latest">{/if}

<div id="thread_{$oThread->thread_id}" style="display:block;">

<a name="thread_{$oThread->thread_id}">
<table width="100%" border="0" cellspacing="0" cellpadding="0" {* onclick="javascript:toggleThread({$oThread->thread_id});" *}>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="{$oThread->thread_style}">
	<td>
		&nbsp;{$oThread->thread_display_date} 
		{$smarty.const.LANG_WORD_BY} 
		{$oThread->thread_display_author} ({$oThread->thread_type}) 
		{if $oThread->thread_author->address_banned} (BLOCKED) {/if}
		{if $oThread->thread_time_worked} ({$smarty.const.LANG_DISPLAY_TIME_WORKED}: {$oThread->thread_time_worked}){/if}
	</td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  </table>
  
  <table width="100%" border="0" cellspacing="0" cellpadding="0">

  {if $suppress_links === false}
  
  	{* Show Thread Action Links *}
	  <tr bgcolor="#888888">
		<td align="left">
			{if $o_ticket->writeable}
				{if !empty($o_ticket->queue_addresses_id)}
					&nbsp;[ <a href="{$oThread->url_reply}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_REPLY}</a> ]

					[ <a href="{$oThread->url_quote_reply}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_QUOTE_REPLY}</a> ]
					
					{if $o_ticket->properties->show_forward_thread === true}
						[ <a href="{$oThread->url_quote_forward}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_QUOTE_FORWARD}</a> ]
					{/if}
					
				{/if}
				
				{if empty($o_ticket->queue_addresses_id)}&nbsp;{/if}[ <a href="{$oThread->url_comment}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_COMMENT}</a> ]
			{/if}
			
			[ <a href="javascript:toggleThreadOptions({$oThread->thread_id});" class="cer_display_commentLink">More Options...</a> ]
		</td>
   	  </tr>
	  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
    </table>
   	    
	<div id="thread_{$oThread->thread_id}_options" style="display:none;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr bgcolor="#AFAFAF">
		<td align="left">
			&nbsp;<span class="cer_maintable_header">options: </span>
			{if $o_ticket->writeable}
			
				{if !empty($o_ticket->queue_addresses_id)}
					{if $o_ticket->properties->show_forward_thread === true}
						[ <a href="{$oThread->url_forward}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_FORWARD}</a> ]
						[ <a href="{$oThread->url_bounce}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_BOUNCE}</a> ]
					{/if}
				{/if}
				
				{if $oThread->url_add_req}
					[ <a href="{$oThread->url_add_req}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_ADD_TO_REQUESTERS}</a> ]
				{/if}
	
				{if $oThread->url_block_sender}
					[ <a href="{$oThread->url_block_sender}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_BLOCK_SENDER}</a> ]
				{/if}
	
				{if $oThread->url_unblock_sender}
					[ <a href="{$oThread->url_unblock_sender}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_UNBLOCK_SENDER}</a> ]
				{/if}
	
				[ <a href="{$oThread->url_strip_html}" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_STRIP_HTML}</a> ]
			{/if}
            
			[ <a href="javascript: printTicket('{$oThread->print_thread}');" class="cer_display_commentLink">{$smarty.const.LANG_THREAD_PRINT}</a> ]
			
			[ <a href="{$oThread->url_track_time_entry}" class="cer_display_commentLink">Add Time Worked</a> ]
		</td>
   	  </tr>
	  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
    </table>
	</div>
	
  {/if} {* end suppress links *}
  
  	{if !empty($thread_action) && isset($thread) && $oThread->thread_id == $thread}
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
  		{if $thread_action == "forward"}
  			{include file="display/actions/thread_action_forward.tpl.php"}
		{/if}
   		{if $thread_action == "bounce"}  {* [jxdemel] Bounce Feature *}
   			{include file="display/actions/thread_action_bounce.tpl.php"}
 		{/if}
		{if $thread_action == "strip_html"}
			{include file="display/actions/thread_action_strip_html.tpl.php"}
		{/if}
   	  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
    </table>
   	{/if}
   	
    {* Thread Content Block *}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bordercolor="#666666">
      <tr bgcolor="#E6E6E6">
        <td class="cer_display_emailText" style="padding-left: 2px;">
{if empty($oThread->thread_subject)}
{$smarty.const.LANG_WORD_SUBJECT}: {$o_ticket->ticket_subject|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}<br>
{else}
{$smarty.const.LANG_WORD_SUBJECT}: {$oThread->thread_subject|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}<br>
{/if}

{if !empty($oThread->thread_to)}
To: {$oThread->thread_to|short_escape}<br>
{/if}
{$smarty.const.LANG_WORD_FROM}: {$oThread->thread_display_author}<br>
{if !empty($oThread->thread_cc)}
Cc: {$oThread->thread_cc|short_escape}<br>
{/if}

{if !empty($oThread->thread_replyto)}
Reply-To: {$oThread->thread_replyto|short_escape}<br>
{/if}

Date: {$oThread->thread_date_rfc}<br>

<br>
{$oThread->thread_content|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}
		</td>
      </tr>


    {* Thread Errors *}
    {if count($oThread->thread_errors) }
	  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
    	<tr bgcolor="#CC1111">
    		<td class="cer_maintable_header">
	        	&nbsp;Message Errors:&nbsp;
	        	{if $suppress_links === false}
	        		<a href="{$oThread->url_clear_errors}" class="cer_display_commentLink">Clear</a>
	        	{/if}
    		</td>
    	</tr>
	  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
      <tr bgcolor="#D0D0D0">
    	<td class="cer_maintable_text">
		    {section name=error loop=$oThread->thread_errors->errors}
		      {$oThread->thread_errors->errors[error]}
		    {/section}
    	 	<br>
    	</td>
	  </tr>
    {/if}

    
    {* File Attachments *}
    {if count($oThread->file_attachments)}
		<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
    	<tr>
    		<td bgcolor="#B0B0B0">
    		<span class="cer_maintable_heading"><b>{$smarty.const.LANG_DISPLAY_ATTACHMENTS}</b>: </span>
    		{section name=file loop=$oThread->file_attachments}
    		{* if(preg_match("/MSIE/", $_SERVER["HTTP_USER_AGENT"])) *}		
		    	<a href="{$oThread->file_attachments[file]->file_url}" class="cer_display_file_link" target="_blank">
		    	{$oThread->file_attachments[file]->file_name} ({$oThread->file_attachments[file]->display_size})</a>&nbsp;&nbsp;
		    {/section}
    		</td>
    	</tr>
    {/if}
    
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr><td align="right"><a href="#top" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_BACK_TO_TOP|lower}</a></td></tr>
	</table>
	<br>

	</div>
