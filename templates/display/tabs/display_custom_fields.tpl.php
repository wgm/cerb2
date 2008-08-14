
	{foreach from=$field_handler->group_instances item=group name=group}
        
	  	<tr>
          <td colspan="2">
          	<table cellspacing="0" cellpadding="0" border="0" width="100%">
          		<tr class="boxtitle_blue">
          			<td align="left">{$group->group_name} (bound to {$group->entity_name})</td>
          			<td align="right">
          				Delete:
          				<input type="checkbox" name="instance_ids[]" value="{$group->group_instance_id}">
          			</td>
          		</tr>
          	</table>
          </td>
        </tr>
        
        {if count($group->fields) }
          
            {* Custom Fields Loop *}
            {foreach from=$group->fields item=field name=field}
                <input type="hidden" name="g_{$group->group_instance_id}_field_ids[]" value="{$field->field_id}">
                <tr> 
                  <td width="10%" bgcolor="#DDDDDD" class="cer_custom_field_heading" valign="top">{$field->field_name|short_escape}:</td>
                  
                  <td width="80%" bgcolor="#EEEEEE">
                  {if $o_ticket->writeable !== false}
                  
                  	{if $field->field_type == "S"}
                    	<input type="text" name="g_{$group->group_instance_id}_field_{$field->field_id}" size="65" value="{$field->field_value|short_escape}" class="cer_custom_field_text">
                    {/if}

                  	{if $field->field_type == "E"}
						<input type="text" name="g_{$group->group_instance_id}_field_{$field->field_id}" maxlength="8" size="8" value="{$field->field_value}">
						<a href="javascript: calendarPopUp('document.display_custom_fields.g_{$group->group_instance_id}_field_{$field->field_id}');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
			          	<span class="cer_footer_text">
							(use calendar -or- enter <b><i>mm/dd/yy</i></b>)
			          	</span>
                    {/if}

                  	{if $field->field_type == "T"}
                    	<textarea cols="65" rows="3" name="g_{$group->group_instance_id}_field_{$field->field_id}" wrap="virtual" class="cer_custom_field_text">{$field->field_value|short_escape}</textarea><br>
                    	<span class="cer_footer_text">(maximum 255 characters)</span>
                    {/if}
                    
                  	{if $field->field_type == "D"}
                    	<select name="g_{$group->group_instance_id}_field_{$field->field_id}" class="cer_custom_field_text">
	                      <option value="">
	                      {html_options options=$field->field_options selected=$field->field_value}
                        </select>
                    {/if}
                    
                  {/if}
                  </td>
                </tr>
            {/foreach}
            
            <input type="hidden" name="group_instances[]" value="{$group->group_instance_id}">
            <input type="hidden" name="entity_codes[]" value="{$group->entity_code}">
            <input type="hidden" name="entity_indexes[]" value="{$group->entity_index}">
        {/if}
          
	  <tr bgcolor="#AAAAAA"> 
	  	<td colspan="2"><img src="images/spacer.gif" width="1" height="4"></td>
	  </tr>
          
	  <tr bgcolor="#FFFFFF"> 
	  	<td colspan="2"><img src="images/spacer.gif" width="1" height="2"></td>
	  </tr>
          
  {/foreach}

