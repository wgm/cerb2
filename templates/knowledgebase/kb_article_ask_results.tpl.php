<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr>
		<td bgcolor='#ECECEC'>
			<span class="cer_display_header">{$smarty.const.LANG_WORD_QUESTION}:</span><br>
		</td>
	</tr>
	<tr>
		<td bgcolor='#FFFFFF'>
			<span class="cer_maintable_text">{$kb_ask|escape:"htmlall"}</span>
		</td>
	</tr>
</table>
<br>

<span class="cer_display_header">Cerberus Fetch & Retrieve&trade; - {$smarty.const.LANG_KB_RESULTS}</span><br>

{if count($articles) > 0}

	<table width="100%" border="0" cellspacing="1" cellpadding="1">
    	<tr bgcolor="#999999"> 
	        <td width="1%" class="cer_maintable_header" bgcolor="#33cc00" align="center" nowrap>&nbsp;{$smarty.const.LANG_KB_ARTICLE_ID}&nbsp;</td>
	        <td width="98%" class="cer_maintable_header" bgcolor="#666666">&nbsp;{$smarty.const.LANG_KB_SUMMARY}&nbsp;</td>
	        <td width="1%" class="cer_maintable_header" bgcolor="#0099FF" align="center" nowrap>&nbsp;Score&nbsp;</td>
      	</tr>
	    	
 		{section name=article loop=$articles}
	        <tr class="{if %article.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
	          <td width="1%" align="right" nowrap><a href="{$articles[article]->article_url}" class="cer_maintable_text">{$articles[article]->article_id}</a>&nbsp;</td>
	    	    <td width="98%"> 
					<img src="includes/images/spacer.gif" width="4" height="1" align="absmiddle"><img src="includes/images/icon_article.gif" align="absmiddle"><img src="includes/images/spacer.gif" width="6" height="1" align="absmiddle"><a href="{$articles[article]->article_url}" class="cer_knowledgebase_article">{$articles[article]->article_summary}</a><br>
	          		<!--<span class="cer_maintable_text">{$articles[article]->article_brief|strip_tags|short_escape|truncate:150}</span>-->
				</td>
				<td width="1%" valign="middle" align="center" nowrap>
					&nbsp;{$articles[article]->article_rating}%&nbsp;
				</td>
	        </tr>
		{/section}
			
	</table>

{else}
	<i><span class="cer_maintable_text">{$smarty.const.LANG_KB_NO_RESULTS}.</span></i>
	<br>
{/if}

<br>

{include file="knowledgebase/kb_search_keywords.tpl.php"}