<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr> 
    <td colspan="2" valign="top">
    	<span class="cer_display_header">Ticket #{$o_ticket->ticket_mask_id}: {$o_ticket->ticket_subject|short_escape}</span></a>
    	<br>
    	<a href="{$urls.tab_display}#latest" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_JUMP_TO_LATEST|lower}</a>
    	{if $priv->has_priv(ACL_PREFS_USER)}
   		  | <a href="my_cerberus.php?mode=layout#layout_display" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_CUSTOMIZE_LAYOUT|lower}</a>
    	{/if}
    </td>
  </tr>
  <tr> 
    <td valign="top">
    {include file="display/display_ticket_navbar.tpl.php"}
	</td>
  </tr>
</table>