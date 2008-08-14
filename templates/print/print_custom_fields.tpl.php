	{foreach from=$field_handler->group_instances item=group name=group}
        
	  	<tr>
          <td colspan="2">
          	<table cellspacing="0" cellpadding="0" border="0" width="100%">
          		<tr>
          			<td align="left"><b>{$group->group_name} (bound to {$group->entity_name})</b></td>
          		</tr>
          	</table>
          </td>
        </tr>
        <tr><td colspan=2><hr size=1 color=black></td></tr>
        {if count($group->fields) }
          
            {* Custom Fields Loop *}
            {foreach from=$group->fields item=field name=field}
            {if $field->field_value neq ''}
               <tr> 
                  <td align="left"><b>{$field->field_name|short_escape}:&nbsp;&nbsp;</b></td>
                    {assign var="field_value" value=$field->field_value}
                    
					{if $field->field_type == "D"}
                  		<td align="left">{$field->field_options.$field_value|short_escape}</td>	  
					{else}
            		    <td align="left">{$field_value|short_escape}</td>
					{/if}
               </tr>
            {/if}            
            {/foreach}
        {/if}         
  {/foreach}

