<table width="100%" border="0" cellspacing="0" cellpadding="1">
  <tr bgcolor="#FFFFFF"> 
    <td class="cer_display_header" colspan="2">
    	{$smarty.const.LANG_DISPLAY_THREAD}
    	
    	{* only if time tracking is enabled *}
    	<br>
    	<span class="cer_footer_text">
    	
    	{if $priv->has_priv(ACL_TIME_TRACK_CREATE,BITGROUP_3)}
    		<a href="javascript:toggleThreadTimeEntry();" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_THREAD_ADD_TIME}</a> 
    		 | 
    	{/if}
    	
    	<a href="javascript:toggleThreadsActivity();" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_THREAD_TOGGLE_ACTIVITY}</a>
    	 | 
    	<a href="javascript:toggleThreadsTime();" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_THREAD_TOGGLE_TIME}</a>
    	</span>
    	{* end enabled *}
    	
    </td>
  </tr>
</table>
<br>

{include file="display/display_ticket_add_track_time.tpl.php"}

{include file="display/display_ticket_threads_list.tpl.php" suppress_links=false}