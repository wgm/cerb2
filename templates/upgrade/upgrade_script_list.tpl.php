{foreach item=script name=scripts from=$script_list}
	<tr bgcolor="#{if $smarty.foreach.scripts.iteration % 2 == 0}dedede{else}d0d0d0{/if}">
		<td align="center" valign="middle">
			{if $script->precursor_ran}
				<input type="radio" name="upgrade_script_name" value="{$script->script_ident}">
			{/if}
		</td>
		<td valign="top">
			<span class="cer_maintable_heading">&nbsp;{$script->script_name}</span>&nbsp;<br>
			<span class="cer_footer_text">&nbsp;<b>Author: </b>{$script->script_author}&nbsp;
			{if !$script->precursor_ran} -- <b>*required precursor scripts have not run*</b>{/if}
			</span><br>
		</td>
	</tr>
	<tr><td colspan="{$col_span}" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
{/foreach}
