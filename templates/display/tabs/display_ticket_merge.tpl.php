{literal}

<script>

function verifyMerge()

{

{/literal}

	if(confirm("{$smarty.const.LANG_ACTION_MERGE_SURE}"))

{literal}

	{

		return true;

	}

	else

		return false;

}

</script>

{/literal}<div align="right"></div>

<a name="merge">

<table width="100%" border="0" cellspacing="0" cellpadding="2">

<form action="display.php#merge" method="post" OnSubmit="javascript:return verifyMerge();">

<input type="hidden" name="sid" value="{$session_id}">

<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">

<input type="hidden" name="mode" value="properties">

<input type="hidden" name="qid" value="{$o_ticket->ticket_queue_id}">

<input type="hidden" name="form_submit" value="merge">

  <tr> 

		<td>

			{if !empty($merge_error)}

				<span class="cer_configuration_updated">ERROR: {$merge_error}</span>

			{/if}

				<table width="100%" border="0" cellspacing="0" cellpadding="0">

		  		<tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          <tr class="boxtitle_blue_glass_pale">

          	<td>&nbsp;{$smarty.const.LANG_ACTION_MERGE}</td>

          </tr>

          <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

		  		<tr bgcolor="#DDDDDD">

          	<td>

          	<table cellspacing="0" cellpadding="2" width="100%" border="0">

          		<tr>

          			<td><span class="cer_maintable_text">{$smarty.const.LANG_ACTION_MERGE_INSTRUCTIONS}</span></td>

          		</tr>

          		<tr>

          			<td><span class="cer_maintable_heading">{$smarty.const.LANG_ACTION_MERGE_PROMPT_BEFORE}{$o_ticket->ticket_mask_id}{$smarty.const.LANG_ACTION_MERGE_PROMPT_AFTER}</span><input type="text" size="15" name="merge_to">

          			<input type="submit" value="{$smarty.const.LANG_ACTION_MERGE_PROMPT_SUBMIT}"></td>

          		</tr>

						</table>

            </td>

         </tr>

				 <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

       </table>        

		</td>

  </tr>

</form>

</table>