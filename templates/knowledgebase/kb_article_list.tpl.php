{if count($articles) > 0}
	<br>
	<table width="100%" border="0" cellspacing="1" cellpadding="1">
    		<tr bgcolor="#999999"> 
	        <td width="1%" class="cer_maintable_header" bgcolor="#33cc00" align="center" nowrap>&nbsp;{$smarty.const.LANG_KB_ARTICLE_ID}&nbsp;</td>
	        <td width="97%" class="cer_maintable_header" bgcolor="#666666">&nbsp;{$smarty.const.LANG_KB_SUMMARY}&nbsp;</td>
	        <td width="1%" class="cer_maintable_header" bgcolor="#888888" align="center" nowrap>&nbsp;Public&nbsp;</td>
	        <td width="1%" class="cer_maintable_header" bgcolor="#0099FF" align="center" nowrap>&nbsp;User Rating&nbsp;</td>
       	</tr>
	    	
 			{section name=article loop=$articles}
        <tr class="{if %article.rownum% % 2 == 0}cer_maintable_text_1{else}cer_maintable_text_2{/if}">
          <td width="1%" align="right" nowrap><a href="{$articles[article]->article_url}" class="cer_maintable_text">{$articles[article]->article_id}</a>&nbsp;</td>
          	<td width="97%"> 
          		<table cellpadding="0" cellspacing="0" border="0">
          			<tr>
          				<td valign="middle" align="center"><img src="includes/images/spacer.gif" width="4" height="1"><img src="includes/images/icon_article.gif" align="absmiddle"><img src="includes/images/spacer.gif" width="6" height="1"></td>
          				<td valign="top">
							<a href="{$articles[article]->article_url}" class="cer_knowledgebase_article">{$articles[article]->article_summary}</a><br>
			          		<span class="cer_maintable_text">{$articles[article]->article_brief|strip_tags|short_escape|truncate:150}</span>
          				</td>
          			</tr>
          		</table>
			</td>
			<td width="1%" valign="middle" align="center" class="cer_maintable_heading" nowrap>
					{if $articles[article]->article_public}X{/if}
			</td>
			<td width="1%" valign="middle" align="center" nowrap>
				{if $articles[article]->article_rating != 0}
					{$articles[article]->article_rating} / 5.0
	            	<table cellpadding="0" cellspacing="0" width="50">
	            		<tr>
	            			<td width="{$articles[article]->rate_percent}%" bgcolor="#EE0000"><img src="includes/images/spacer.gif" height="3" width="{$articles[article]->rate_width}"></td>
	            			<td width="{$articles[article]->rate_percent_i}%" bgcolor="#AEAEAE"></td>
	            		</tr>
	            	</table>
	          	{else}
    	        	N/A
          		{/if}
          	</td>
        </tr>
			{/section}
			
	</table>

{else}
		<br>
		<i><span class="cer_maintable_text">{$smarty.const.LANG_KB_ARTICLE_NO_ARTICLES}</span></i>
		<br>
{/if}
