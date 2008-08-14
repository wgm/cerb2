
{* Custom Field Groups *}
{foreach from=$search_box->field_groups->group_templates item=group name=group}
	<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
		<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
		<tr> 
		  <td class="boxtitle_gray_glass">{$group->group_name}</td>
		</tr>
		<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	</table>
    
	<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
	<tr bgcolor="#DDDDDD"> 
    
	{foreach name=field from=$group->fields item=field}
      <td class="cer_maintable_headingSM" bgcolor="#DDDDDD" width="20%" nowrap>&nbsp;{$field->field_name}:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
      <td bgcolor="#EEEEEE" width="30%">
      	{assign var=id value=$field->field_id}
      	{assign var=val value=$search_box->field_values.$id}
      	
      	{if $field->field_type == 'T' || $field->field_type == 'S' }
      		<input type="text" size="15" name="search_field_{$field->field_id}" value="{$val}">
        {elseif $field->field_type == 'E' }
      		<input type="text" size="8" name="search_field_{$field->field_id}" value="{$val}">
      		<a href="javascript: calendarPopUp('document.search.search_field_{$field->field_id}');"><img src="includes/images/icon_calendar.gif" border="0" align="absmiddle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
      		<span class="cer_footer_text">(<b>mm/dd/yy</b>)</span>
        {elseif $field->field_type == 'D' }
        	<select name="search_field_{$field->field_id}">
        		<option value="">- any -
            	{html_options options=$field->field_options selected=$val}
            </select>
        {/if}
		</td>
		{if $smarty.foreach.field.iteration % 2 == 0 }
			  </tr>
	 		  {if !$smarty.foreach.field.last}<tr bgcolor="#DDDDDD">{/if}
		{/if}
		
	    {if $smarty.foreach.field.last && $smarty.foreach.field.iteration % 2 != 0 }
		  	<td class="cer_maintable_headingSM" bgcolor="#DDDDDD" width="20%" nowrap>&nbsp;</td>
	      	<td bgcolor="#EEEEEE" width="30%">&nbsp;</td>
	      	</tr>
	    {/if}
	  {/foreach}
	  
	  </table>
{/foreach}

<input type="hidden" name="search_field_ids" value="{$search_box->field_groups_field_ids}">

{* Date Range *}
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
	<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
	<tr> 
	  <td class="boxtitle_gray_glass">Dates</td>
	</tr>
	<tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
<tr bgcolor="#DDDDDD"> 
  <td class="cer_maintable_headingSM" width="1%" nowrap>&nbsp;Date Range:<img src="includes/images/spacer.gif" width="5" height="1" border="0"></td>
  <td bgcolor="#EEEEEE" class="cer_maintable_text" width="99%"> 
	<select name="search_date" class="cer_footer_text">
		<option value="1" {if $session->vars.psearch->params.search_date ==1 }SELECTED{/if}>Created Between
		<option value="2" {if $session->vars.psearch->params.search_date == 2}SELECTED{/if}>Latest Message Between
	</select>
	&nbsp;
	<input name="search_fdate" type="text" size="8" value="{$session->vars.psearch->params.search_fdate}">
	-<b>and</b>-
	<input name="search_tdate" type="text" size="8" value="{$session->vars.psearch->params.search_tdate}"> <span class="cer_footer_text">(mm/dd/yy)</span>
  </td>
</tr>
</table>
