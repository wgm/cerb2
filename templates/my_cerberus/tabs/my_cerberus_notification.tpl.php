<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="notification">
<input type="hidden" name="form_submit" value="notification">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_dk"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD"> 
    <td bgcolor="#DDDDDD">
    
    	<table width="100%" cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
    	
    		<tr bgcolor="#DDDDDD">
	    		<td colspan="2" class="cer_maintable_text">
	    			{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_INSTRUCTIONS}</span><br>
					<br>
					{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_TOKENS_INSTRUCTIONS}
				</td>
	    	</tr>
	    	
    		<tr class="boxtitle_blue_glass">
	    		<td colspan="2">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_HEADER}</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">
	    			{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_QUEUES}<br>
	    			<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_QUEUES_INFO}</span>
	    		</td>
	    		<td width="99%">
	    		
	    			<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
	    				<tr bgcolor="#666666">
	    					<td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_ENABLED}</td>
	    					<td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_QUEUE}</td>
	    					<td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO} {$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED}</td>
	    				</tr>
	    				
	    				{foreach key=q_id from=$header_readwrite_queues item=q name=queues}
	    				<tr bgcolor="#DDDDDD">
	    					<td align="center"><input type="checkbox" name="notify_new_enabled[]" value="{$q_id}" {if isset($notification->active_user->n_new_ticket->queues_send_to.$q_id)}checked{/if}></td>
	    					<td class="cer_maintable_heading">{$q}</td>
	    					<td>
	    						<input type="text" name="notify_new_emails[]" size="45" maxlength="255" value="{$notification->active_user->n_new_ticket->queues_send_to.$q_id}">
	    						<input type="hidden" name="notify_new_qlist[]" value="{$q_id}">
	    					</td>
	    				</tr>
	    				{/foreach}
	    				
	    			</table>
	    		</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE}</td>
	    		<td width="99%"><textarea name="notify_new_template" rows="10" cols="100%">{$notification->active_user->n_new_ticket->template}</textarea></td>
	    	</tr>
	    	
	    	
    		<tr class="boxtitle_blue_glass">
	    		<td colspan="2">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_ASSIGNMENT_HEADER}</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_ENABLED}</td>
	    		<td width="99%"><input name="notify_assigned_enabled" type="checkbox" value="1" {if $notification->active_user->n_assignment->enabled}checked{/if}></td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO}</td>
	    		<td width="99%">
	    			<input name="notify_assigned_emails" type="text" size="64" maxlength="255" value="{$notification->active_user->n_assignment->send_to}">
	    			<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED}</span>
	    		</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE}</td>
	    		<td width="99%"><textarea name="notify_assigned_template" rows="10" cols="100%">{$notification->active_user->n_assignment->template}</textarea></td>
	    	</tr>

    		<tr class="boxtitle_blue_glass">
	    		<td colspan="2">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_CLIENTREPLY_HEADER}</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_ENABLED}</td>
	    		<td width="99%"><input name="notify_client_reply_enabled" type="checkbox" value="1" {if $notification->active_user->n_client_reply->enabled}checked{/if}></td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO}</td>
	    		<td width="99%">
	    			<input name="notify_client_reply_emails" type="text" size="64" maxlength="255" value="{$notification->active_user->n_client_reply->send_to}">
	    			<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED}</span>
	    		</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE}:</td>
	    		<td width="99%"><textarea name="notify_client_reply_template" rows="10" cols="100%">{$notification->active_user->n_client_reply->template}</textarea></td>
	    	</tr>

    	</table>

	</td>
  </tr>
  
  <tr>
  	<td style="padding-right:2px;padding-top:2px;padding-bottom:2px" bgcolor="#BBBBBB" align="right"><input type="submit" value="{$smarty.const.LANG_BUTTON_SUBMIT}" class="cer_button_face"></td>
  </tr>
  
</form>
  
</table>

<br>