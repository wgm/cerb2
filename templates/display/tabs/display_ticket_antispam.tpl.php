<input type="hidden" name="sid" value="{$session_id}">

<table width="100%" border="0" cellspacing="0" cellpadding="2">

<form>

  <tr> 

		<td>

	      <table width="100%" border="0" cellspacing="0" cellpadding="0">

	      

		  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          <tr class="boxtitle_blue_glass">

          	<td>&nbsp;{$smarty.const.LANG_DISPLAY_ANTISPAM_HEADING}</td>

          </tr>

          <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          

		  <tr bgcolor="#DDDDDD">

		  	<td>

	 	      	<table cellspacing="1" cellpadding="2" width="100%" border="0" bgcolor="#FFFFFF">

		 	      	<tr>

						<td align="center" bgcolor="#{if $o_ticket->ticket_spam_rating > 90}FF0000{else}00BB00{/if}">

							<span class="cer_maintable_header" valign="top">{$o_ticket->ticket_spam_rating|string_format:"%0.2f"}%</span>

						</td>

						<td bgcolor="#DDDDDD">

							<span class="cer_maintable_text" valign="top"><b>{$smarty.const.LANG_WORD_SPAM_PROBABILITY}</b></span>

						</td>

						

						<td bgcolor="#CCCCCC" valign="middle">

							<span class="cer_maintable_heading">Training:</span>

						</td>

						

						<td bgcolor="#DDDDDD" class="cer_footer_text" valign="middle">

							{if $o_ticket->ticket_spam_trained == 1}{$smarty.const.LANG_TICKET_IS_HAM}

							{elseif $o_ticket->ticket_spam_trained == 2}{$smarty.const.LANG_TICKET_IS_SPAM}

							{else}{$smarty.const.LANG_DISPLAY_ANTISPAM_NOT_TRAINED}

							{/if}

						</td>

					</tr>

					<tr>

						<td colspan="4" bgcolor="#DDDDDD">

						

			          		{if count($o_ticket->ticket_spam_words)}

			          		<br>

				          	<table cellspacing="1" cellpadding="2" border="0" bgcolor="#FFFFFF">

				          		<tr>

				          			<td colspan="5" bgcolor="#666666" class="cer_maintable_header">{$smarty.const.LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_HEADER}</td>

				          		</tr>

				          		<tr>

				          			<td bgcolor="#999999" class="cer_maintable_header">{$smarty.const.LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_TOKEN}</td>

				          			<td bgcolor="#999999" class="cer_maintable_header">{$smarty.const.LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_IN_SPAM}</td>

				          			<td bgcolor="#999999" class="cer_maintable_header">{$smarty.const.LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_IN_NON_SPAM}</td>

				          			<td bgcolor="#999999" class="cer_maintable_header">{$smarty.const.LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_PROBABILITY}</td>

				          			<td bgcolor="#999999" class="cer_maintable_header">{$smarty.const.LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_INTEREST_FACTOR}</td>

				          		</tr>

				          		

				          		{foreach from=$o_ticket->ticket_spam_words item=word name=words}

				          		<tr>

				          			<td bgcolor="#CCCCCC" class="cer_maintable_heading">{$word->word}</td>

				          			<td bgcolor="#DDDDDD" class="cer_maintable_text" align="center">{$word->in_spam}</td>

				          			<td bgcolor="#DDDDDD" class="cer_maintable_text" align="center">{$word->in_nonspam}</td>

				          			<td bgcolor="#DDDDDD" class="cer_maintable_text" align="center">{$word->probability|string_format:"%0.4f"}</td>

				          			<td bgcolor="#DDDDDD" class="cer_maintable_text" align="center">{$word->interest_rating|string_format:"%0.4f"}</td>

				          		</tr>

				          		{/foreach}

				          		

						 	</table>

						 	

						 	<br>

						 	<span class="cer_maintable_text">

							{$smarty.const.LANG_DISPLAY_ANTISPAM_TEXT}

						 	</span>

						 	

			          		{/if}

			          		

						</td>

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