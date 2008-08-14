{literal}
<script>
	function toggleDisplayContact() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_contact").style.display=="block") {
				document.getElementById("ticket_display_contact").style.display="none";
				document.getElementById("ticket_display_contact_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_contact.value = 0;
			}
			else {
				document.getElementById("ticket_display_contact").style.display="block";
				document.getElementById("ticket_display_contact_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_contact.value = 1;
			}
		}
	}
</script>
{/literal}

<table cellpadding="2" cellspacing="0" border="0" width='100%'>
	<tr class="boxtitle_green_glass">
		<td width="99%">
			{$smarty.const.LANG_DISPLAY_COMPANYCONTACT}
		</td>
		<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_contact_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_contact}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayContact();" onmouseover="javascript:this.style.cursor='hand';"></td>
	</tr>
</table>

<div id="ticket_display_contact" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_contact}block{else}none{/if};">
<table border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF" width="100%">

{if $o_ticket->sla->pub_user}
	<tr> 
      <td bgcolor="DDDDDD" class="cer_display_custom_field" align="left" valign="middle" width="5%" nowrap>
      	<b>{$smarty.const.LANG_WORD_COMPANY}:</b>
      </td>
	      <td class="cer_maintable_heading" width="95%" valign="middle" bgcolor="#EEEEEE">
	      {if !empty($o_ticket->sla->pub_user->company_ptr) }
	      	{$o_ticket->sla->pub_user->company_ptr->company_name}
	      	<span class="cer_footer_text">(<a href="{$o_ticket->sla->pub_user->company_ptr->url_view}" class="cer_maintable_text">{$smarty.const.LANG_DISPLAY_SHOW_COMPANY_DETAILS}</a>)</span>
	      {else}
	      	<span class="cer_maintable_text">{$smarty.const.LANG_DISPLAY_NO_COMPANY}</span>
	      {/if}
	      </td>
    </tr>
 	
    <tr>
      <td bgcolor="DDDDDD" class="cer_display_custom_field" align="left" valign="middle" width="5%" nowrap>
      	<b>{$smarty.const.LANG_DISPLAY_SLA_COVERAGE}</b>
      </td>
      <td width="95%" valign="middle" bgcolor="#EEEEEE">
      {if !empty($o_ticket->sla->sla_plan) }
      	<span class="cer_maintable_text">{$o_ticket->sla->sla_plan->sla_name|short_escape}</span>
      {else}
      	<span class="cer_maintable_text">{$smarty.const.LANG_DISPLAY_NO_SLA_PLAN}</span>
      {/if}
      </td>
    </tr>
    
    {if !empty($o_ticket->sla->sla_plan) }
    <tr>
      <td bgcolor="DDDDDD" class="cer_display_custom_field" align="left" valign="middle" width="5%" nowrap>
      	<b>{$smarty.const.LANG_WORD_EXPIRES}</b>
      </td>
      <td width="95%" valign="middle" bgcolor="#EEEEEE">
      	<span class="cer_maintable_text">{if $o_ticket->sla->pub_user->company_ptr->sla_expire_date}{$o_ticket->sla->pub_user->company_ptr->sla_expire_date->getUserDate("%m/%d/%y")}{/if}</span>
      </td>
    </tr>
    {/if}
    
 	<tr> 
      <td bgcolor="DDDDDD" class="cer_display_custom_field" align="left" valign="middle" width="5%" nowrap>
      	<b>{$smarty.const.LANG_WORD_CONTACT}:</b>
      </td>
      <td width="95%" valign="middle" bgcolor="#EEEEEE">
      	<span class="cer_maintable_text">{$o_ticket->sla->pub_user->account_name_first|short_escape} {$o_ticket->sla->pub_user->account_name_last|short_escape}</cer_maintable_text>
      	<span class="cer_footer_text">(<a href="{$o_ticket->sla->pub_user->url_view}" class="cer_maintable_text">{$smarty.const.LANG_DISPLAY_SHOW_CONTACT_DETAILS}</a>)</span>
      </td>
    </tr>

    {else}

		<tr> 
	      <td class="cer_maintable_text" valign="middle" bgcolor="#DDDDDD">
	      	<b>{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED}</b><br>
	      </td>
	    </tr>
		<tr> 
	      <td valign="middle" bgcolor="#EEEEEE">
	      	<a href="{$urls.clients}" target="_blank" class="cer_maintable_text">{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_SEARCH}</a><br>
	      </td>
	    </tr>
	   
	   {if $priv->has_priv(ACL_CONTACTS_CONTACT_MANAGE, BITGROUP_3)}
	     
		<tr> 
	      <td valign="middle" bgcolor="#EEEEEE">
	      	<a href="{$urls.contact_add}" target="_blank" class="cer_maintable_text">{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_CREATE} {if $priv->has_restriction(ACL_HIDE_REQUESTOR_EMAILS,BITGROUP_1)}{$smarty.const.LANG_WORD_REQUESTER}{else}{$o_ticket->requestor_address->address}{/if}</a><br>
	      </td>
	    </tr>
	    
	   {/if}
    
	{/if}
 	
</table>

{if $o_ticket->sla->pub_user && !empty($o_ticket->sla->sla_plan) && !empty($o_ticket->sla->sla_queue_ptr) }
	
	<br>
	
	<table cellpadding="1" cellspacing="1" border="0" bgcolor="#009900" width='100%'>
		<tr>
			<td class="cer_display_company_sla_background">
		      	<span class="cer_maintable_header2">
					Queue SLA Coverage: {$o_ticket->sla->sla_plan->sla_name|short_escape}
		      	</span>
			</td>
		</tr>
	</table>
	
	{if !empty($o_ticket->sla->sla_plan) && !empty($o_ticket->sla->sla_queue_ptr)}
	
	<table border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF" width="100%">
	
		<tr bgcolor="#DDDDDD">
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_QUEUE}</td>
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_QUEUEMODE}</td>
			<td class="cer_maintable_headingSM">{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_SLASCHEDULE}</td>
			<td class="cer_maintable_headingSM" align="center">{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_TARGETRESPONSETIME}</td>
		</tr>
	
		{if !empty($o_ticket->sla->sla_queue_ptr) }
			<tr bgcolor="#EEEEEE">
				<td class="cer_maintable_text"><B>{$o_ticket->sla->sla_queue_ptr->queue_name|short_escape}</B></td>
				<td class="cer_maintable_text">{$o_ticket->sla->sla_queue_ptr->queue_mode}</td>
				<td class="cer_maintable_text">{$o_ticket->sla->sla_queue_ptr->queue_schedule_name}</a></td>
				<td class="cer_maintable_text" align="center">{$o_ticket->sla->sla_queue_ptr->queue_response_time}{$smarty.const.LANG_DATE_SHORT_HOURS_ABBR}</td>
			</tr>
		{/if}
		
	</table>		
	
	{/if}

{/if}

</div>

<br>

