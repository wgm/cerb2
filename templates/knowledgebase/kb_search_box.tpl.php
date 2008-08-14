{assign var="col_span" value="2"}

<form action="knowledgebase.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="form_submit" value="kb_search">

<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA">
	<tr class="boxtitle_blue_glass">
		<td>Cerberus Fetch &amp; Retrieve&trade; {$smarty.const.LANG_KB_SEARCH_TITLE}</td>
	</tr>
	
	<tr>
		<td bgcolor="#EEEEEE">
			<table border="0" cellspacing="1" cellpadding="3">
			
			  <tr> 
			    <td valign="top">
			    	<table width="300">
			    		<tr>
			    			<td>
						    	<span class="cer_maintable_heading">{$smarty.const.LANG_KB_SEARCH_KEYWORD_INTRO}:</span><br>
								<input type="text" name="kb_keywords" value="" size="45"><br>

								-{$smarty.const.LANG_WORD_OR}-<br>
						    	<span class="cer_maintable_heading">{$smarty.const.LANG_KB_ASK_QUESTION}</span><br>
			    				<textarea name="kb_ask" rows="3" cols="45">{$kb_ask|short_escape}</textarea><br>
			    				<span class="cer_footer_text"><i>{$smarty.const.LANG_KB_INCLUDE_INFORMATION}</i></span><br>
			    				
						    	-{$smarty.const.LANG_WORD_OR}-<br>
						    	<span class="cer_maintable_heading">{$smarty.const.LANG_KB_GOTO_ID}:&nbsp;</span><br>
						    	<input type="text" name="search_id" size="8" value=""><br>
			    			</td>
			    		</tr>
			    	</table>
			    </td>
			    <td valign="top">
			    </td>
			  </tr>
 
			</table>
		</td>
	</tr>
	
	  <tr>
	  	<td align="right" bgcolor="#BBBBBB">
	    	<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_WORD_SEARCH}">
	  	</td>
	  </tr>
	
</table>

</form>