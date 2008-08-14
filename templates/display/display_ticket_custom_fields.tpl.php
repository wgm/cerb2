{literal}
<script>
	function toggleDisplayFields() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_fields").style.display=="block") {
				document.getElementById("ticket_display_fields").style.display="none";
				document.getElementById("ticket_display_fields_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_fields.value = 0;
			}
			else {
				document.getElementById("ticket_display_fields").style.display="block";
				document.getElementById("ticket_display_fields_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_fields.value = 1;
			}
		}
	}
</script>
{/literal}

<table cellpadding="2" cellspacing="0" border="0" width='100%'>
	<tr class="boxtitle_gray_glass_dk">
		<td width="99%">
      		{$smarty.const.LANG_CONFIG_CUSTOM_FIELDS}
		</td>
		<td width="1%" nowrap valign="middle" align="center"><img id="ticket_display_fields_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_fields}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayFields();" onmouseover="javascript:this.style.cursor='hand';"></td>
	</tr>
</table>

<div id="ticket_display_fields" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_fields}block{else}none{/if};">
<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
<form action="display.php" method="post" name="display_custom_fields">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
<input type="hidden" name="qid" value="{$o_ticket->ticket_queue_id}">
<input type="hidden" name="mode" value="edit_custom_fields">
<input type="hidden" name="form_submit" value="edit_custom_fields">

{include file="display/tabs/display_custom_fields.tpl.php" field_handler=$o_ticket->r_field_handler}
{include file="display/tabs/display_custom_fields.tpl.php" field_handler=$o_ticket->t_field_handler}
	  
	<tr>
		<td colspan="2">
			<table border=0 cellspacing=0 cellpadding=1 width="100%">
				{if $priv->has_priv(ACL_CUSTOM_FIELDS_ENTRY,BITGROUP_2) }
				<tr bgcolor="#EEEEEE">
					<td align="left" valign="top">
						{if !empty($o_ticket->r_field_handler->group_instances) ||
							!empty($o_ticket->t_field_handler->group_instances) }
								<input type="submit" class="cer_button_face" value="Update Fields">
								<span class="cer_maintable_heading"> -or- </span>
						{/if}
						
						<span class="cer_maintable_text">Add Field Group </span>
						
						<select name="instantiate_gid">
							<option value="">- none -
							{foreach from=$o_ticket->field_handler->group_templates item=group name=group}
								<option value="{$group->group_id}">{$group->group_name}
							{/foreach}
						</select>
						
						<span class="cer_maintable_text">&nbsp;to </span>
						
						<select name="instantiate_for">
							<option value="T_{$o_ticket->ticket_id}">this Ticket
							<option value="R_{$o_ticket->requestor_address->address_id}">this Requester
						</select>
						
						<input type="submit" class="cer_button_face" value="Add">
					</td>
				</tr>
				{/if}
			</table>
		</td>
	</tr>
</form>
</table>
</div>

<br>