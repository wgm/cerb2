<span class="cer_knowledgebase_heading">{$smarty.const.LANG_KB_BROWSE}:</span><br>

<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
	<tr class="boxtitle_gray_glass">
		<td>{$smarty.const.LANG_KB_CATEGORIES}:</td>
	</tr>

	<tr>
		<td bgcolor="#FFFFFF">
		
			{ $kb->tree->printTrail($root) }<BR><BR>
			
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>

					{if !count($kb->tree->categories[$root]->sorted_children) }
				
						<td class="cer_maintable_text">{$smarty.const.LANG_KB_NO_SUB_CATEGORIES}.</td>
						
					{else}
				
						<td width='50%' valign='top'>
					
						{foreach from=$kb->tree->categories[$root]->sorted_children item=cat name=cat}
							<img src="includes/images/icon_folder_closed.gif"><img src="includes/spacer.gif" width="4" height="1"><a href="{$cat->url_view}" class="cer_knowledgebase_link_lg">{$cat->category_name}</a> <span class="cer_footer_text">({$cat->total_articles})</span>
							<br>
							
							{* child list is drawn here *}
							{$cat->child_list}
														
							<br>
							
							{if $smarty.foreach.cat.iteration == $kb->tree->half}
								</td><td width='50%' valign='top'>
							{/if}
							
						{/foreach}
						
					{/if}
				
					</td>
				</tr>
			</table>		
		
		</td>
	</tr>	
	
</table>

