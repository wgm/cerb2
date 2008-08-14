<script>



function doReload_{$view->view_slot}(value)

{literal} { {/literal}

	url = formatURL('{$view->page_name}?{$view->view_slot}=' + value);

	{if !empty($ticket)} url = url + '&ticket={$o_ticket->ticket_id}';{/if}

	{if !empty($mode)} url = url + '&mode={$mode}';{/if}

	document.location =  url;

{literal} } {/literal}



function doViewEdit_{$view->view_slot}(slot)

{literal} { {/literal}

	url = formatURL('ticket_view_edit.php?{$view->page_args}&vid={$view->view_id}&slot=' + slot + '&page={$view->page_name}');

	window.open(url, "wdwViewEdit", "fullscreen=no,toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,directories=no,location=no,width=750,height=500");

{literal} } {/literal}



function doViewNew_{$view->view_slot}(slot)

{literal} { {/literal}

	url = formatURL('ticket_view_edit.php?{$view->page_args}&slot=' + slot + '&page={$view->page_name}&x=');

	window.open(url, "wdwViewNew", "fullscreen=no,toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,directories=no,location=no,width=750,height=500");

{literal} } {/literal}



var {$view->view_slot}_toggleCheck = 0;

	

function checkAllToggle_{$view->view_slot}()

{literal}

{

	{/literal}

	{$view->view_slot}_toggleCheck = ({$view->view_slot}_toggleCheck) ? 0 : 1;

	{literal}



	for(e = 0;e < document.viewform_{/literal}{$view->view_slot}{literal}.elements.length; e++) {

		if(document.viewform_{/literal}{$view->view_slot}{literal}.elements[e].type == 'checkbox') {

			document.viewform_{/literal}{$view->view_slot}{literal}.elements[e].checked = {/literal}{$view->view_slot}{literal}_toggleCheck;

		}

	}

}

{/literal}



function doViewOptions_{$view->view_slot}() {literal}

{

	if(document.getElementById)

	{

			{/literal}if(document.getElementById("view_{$view->view_slot}_options").style.display=="block"){literal}

			{

				{/literal}

				document.getElementById("view_{$view->view_slot}_options").style.display="none";

				{if $urls.save_layout && $page == $view->view_bind_page}

						document.formSaveLayout.layout_view_options_{$view->view_slot}.value = 0;

				{/if}

				{literal}

			}

			else

			{

				{/literal}

				document.getElementById("view_{$view->view_slot}_options").style.display="block";

				{if $urls.save_layout && $page == $view->view_bind_page}

						document.formSaveLayout.layout_view_options_{$view->view_slot}.value = 1;

				{/if}

				{literal}

			}

	}

}

{/literal}



</script>



<table width="100%" border="0" cellspacing="0" cellpadding="0">

	<tr><td colspan="2" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	<tr> 

  		<td width="99%" class="{$view->view_title_style}">{$view->view_name|short_escape}</td>

  		<td width="1%" bgcolor="#555555" nowrap valign="middle" align="center"><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"><a href="javascript:doViewOptions_{$view->view_slot}();" class="headerMenu">options</a><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>

	</tr>

	<tr><td colspan="2" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

</table>



{* [JAS]: Draw View select Box *}

<div id="view_{$view->view_slot}_options" style="display:{if $view->show_options}block{else}none{/if};">

<table width="100%" border="0" cellspacing="0" cellpadding="1">

<form action="{$view->page_name}" method="post" name="dropdown_{$view->view_slot}">

<input type="hidden" name="sid" value="{$session_id}">

{if !empty($ticket)}<input type="hidden" name="ticket" value="{$ticket}">{/if}

{if !empty($mode)}<input type="hidden" name="mode" value="{$mode}">{/if}

	<tr>

		<td align="left" bgcolor="#DDDDDD">

        	<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_TICKET_VIEW}: </span>

            <select name="{$view->view_slot}" class="cer_footer_text" OnChange="javascript:doReload_{$view->view_slot}(this[this.selectedIndex].value);">

              {html_options options=$view->view_options selected=$view->view_id}

            </select> 

            <input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_GO|lower}">&nbsp;&nbsp;

            {if $view->show_edit_view}

            	<a href="javascript:doViewEdit_{$view->view_slot}('{$view->view_slot}');" class="cer_footer_text">{$smarty.const.LANG_LIST_EDIT_VIEW|lower}</a> | 

            {/if}

            {if $view->show_new_view}

            	<a href="javascript:doViewNew_{$view->view_slot}('{$view->view_slot}');" class="cer_footer_text">{$smarty.const.LANG_LIST_NEW_VIEW|lower}</a>

            {/if}

        </td>

</form>

<form name="view_slot_{$view->view_slot}" action="{$view->page_name}" method="post">

<input type="hidden" name="sid" value="{$session_id}">

{if !empty($ticket)}<input type="hidden" name="ticket" value="{$ticket}">{/if}

{if !empty($mode)}<input type="hidden" name="mode" value="{$mode}">{/if}

<input type="hidden" name="view_submit" value="{$view->view_slot}">

		<td align="right" bgcolor="#DDDDDD">

			<div id="view_slot_{$view->view_slot}_options" style="display:block;">

		        <span class="cer_maintable_heading">[ {$smarty.const.LANG_WORD_FILTERS}: </span>

				<input type="checkbox" name="{$view->view_slot}_filter_responded" value="1" {if !empty($view->filter_responded)}CHECKED{/if}> 

				<span class="cer_footer_text">{$smarty.const.LANG_FILTER_RESP_WITHOUT}</span> |

				<span class="cer_footer_text">{$smarty.const.LANG_WORD_SHOW}</span> 

				<input type="text" name="{$view->view_slot}_filter_rows" value="{$view->filter_rows}" size="2" maxlength="3" class="cer_footer_text"> 

				<span class="cer_footer_text">{$smarty.const.LANG_WORD_RESULTS}</span> 

				<span class="cer_maintable_heading"> ]</span> <input type="submit" value="{$smarty.const.LANG_WORD_FILTER|lower}" class="cer_button_face">

			</div>

		</td>

</form>

    </tr>

</table>

</div>



<table width="100%" border="0" cellspacing="0" cellpadding="0">

	{* [JAS]: Draw Column Headings *}

	<tr bgcolor="#C0C0C0"> 

	{if $view->view_adv_controls}

		<td align="{$view->columns[0]->column_align}" style="padding-left: 2px;">

			<a href="{$view->columns[0]->column_url}" class="cer_maintable_heading">{$view->columns[0]->column_heading}</a>

		</td>

	{/if}

	

	{section name=col loop=$view->columns start=2}

	{* foreach from=$view->columns item=col name=col start=2 *}

		<td align="{$view->columns[col]->column_align}" style="padding-left: 2px;">

			{if $view->columns[col]->column_sortable}

				<a href="{$view->columns[col]->column_url}" class="cer_maintable_heading">{$view->columns[col]->column_heading|short_escape}</a>

			{else}

			 	<span class="cer_maintable_heading">{$view->columns[col]->column_heading|short_escape}</span>

			{/if}

		</td>

	{/section}

    </tr>

    

    {* [JAS]: Draw View Rows *}

	

    {if $view->show_modify}

		<form action="ticket_list.php" method="post" name="viewform_{$view->view_slot}">

		<input type="hidden" name="sid" value="{$session_id}">

		<input type="hidden" name="form_submit" value="tickets_modify">

	{/if}

	

    {if $view->show_mass && $view->view_adv_controls}

		<form action="index.php" method="post" name="viewform_{$view->view_slot}">

		<input type="hidden" name="sid" value="{$session_id}">

		<input type="hidden" name="mass_slot" value="$view->view_slot">

		<input type="hidden" name="form_submit" value="tickets_modify">

	{/if}



	{if $view->show_batch_actions && count($session->vars.login_handler->batch->tickets)}

		<form action="update.php" method="post" name="viewform_{$view->view_slot}">

		<input type="hidden" name="sid" value="{$session_id}">

		<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">

		<input type="hidden" name="mode" value="batch">

		<input type="hidden" name="form_submitx" value="batch">

	{/if}

	

	<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	{section name=row loop=$view->rows}

		

		{if $view->view_adv_2line}

		<tr class="{if %row.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">

		

			{if $view->view_adv_controls}

			<td rowspan="{if $view->view_adv_2line}2{else}1{/if}" align="center">

				{$view->rows[row][0]}

			</td>

			{/if}

			

			{if $view->view_adv_2line}

				<td colspan="{$view->view_colspan_subject}">{$view->rows[row][1]}</td>

			{/if}

			

        </tr>

        {/if}

        

		<tr class="{if %row.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}" title="">

		

		{* [JAS]: If we are not showing subjects on two lines but are showing checkboxes, draw now *}

		{if !$view->view_adv_2line && $view->view_adv_controls}

          <td style="padding-left: 2px; padding-right: 2px;" align="{$view->columns[0]->column_align}">

          	{$view->rows[row][0]}

          </td>

		{/if}

		

		{section name=col loop=$view->rows[row] start=2}

          <td style="padding-left: 2px; padding-right: 2px;" align="{$view->columns[col]->column_align}" {$view->columns[col]->column_extras}>

          	{$view->rows[row][col]}

          </td>

        {/section}

        </tr>

	    <tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

	{/section}

	

	{if $view->show_modify}

	  {include file="search/search_actions.tpl.php" col_span=$col_span}

	  </form>

	{/if}

	

	{if $view->show_mass && $view->view_adv_controls}

	  {include file="views/mass_actions.tpl.php" col_span=$col_span}

	  </form>

	{/if}

	

	{if $view->show_batch_actions && count($session->vars.login_handler->batch->tickets)}

	  {include file="display/tabs/display_ticket_batch_actions.tpl.php" col_span=$col_span}

	  </form>

	{/if}

	

</table>



<table width="100%" border="0" cellspacing="1" cellpadding="2">

	<tr>

		<td width="100%" align="right" class="cer_footer_text">

			{if $view->show_prev}<a href="{$view->view_prev_url}" class="cer_header_loginLink">&lt;&lt;{$smarty.const.LANG_WORD_PREV}</a>{/if}

			({$smarty.const.LANG_WORD_SHOWING} {$view->show_from}-{$view->show_to} {$smarty.const.LANG_WORD_OF} {$view->show_of}) 

			{if $view->show_next}<a href="{$view->view_next_url}" class="cer_header_loginLink">{$smarty.const.LANG_WORD_NEXT}&gt;&gt;</a>{/if}	

		</td>

	</tr>

</table>

