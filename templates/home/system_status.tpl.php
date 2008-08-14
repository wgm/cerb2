<table border="0" cellpadding="1" cellspacing="0">

    <tr>

      <td valign="top"><img id="folders_tab" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_home_show_queues}folders_on.gif{else}folders_off.gif{/if}" width="21" height="80" onMouseOver="this.style.cursor='hand';" onclick="javascript:toggleSystemStatus();"><br>

	  <img id="search_tab" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_home_show_search}search_on.gif{else}search_off.gif{/if}" width="21" height="80" onMouseOver="this.style.cursor='hand';" onclick="javascript:toggleSearch();"></td>

      <td><div id="system_status" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_home_show_queues}block{else}none{/if};"><table width="200" border="0" cellpadding="0" cellspacing="0">

		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr> 
            <td align="center" class="boxtitle_blue">{$smarty.const.LANG_STATUS_TITLE}</td>
          </tr>
		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

        </table>

        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#999999">

          <tr> 
            <td align="center" bgcolor="#EEEEEE" class="cer_footer_text">{$time_now}</td>
          </tr>

        </table>

        <table width="200" border="0" cellpadding="0" cellspacing="0">

		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr> 
            <td align="center" class="boxtitle_blue">{$smarty.const.LANG_STATUS_QUEUE_LOAD}</td>
          </tr>
		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

        </table>



        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#999999">

          {foreach key=qid item=node from=$system_status->queue_tree}
          {assign var="queue" value=$system_status->queue_list.$qid}
          
          {if !empty($queue->queue_name)}
          	{assign var="queue_name" value=$queue->queue_name}
          {else}
          	{assign var="queue_name" value="(Not Visible)"}
          {/if}

          {if !empty($queue->queue_active_tickets)}
          	{assign var="queue_count" value=$queue->queue_active_tickets}
          {else}
          	{assign var="queue_count" value="0"}
          {/if}

          <tr> 
            <td width="90%" bgcolor="#EEEEEE"><a href="{$queue->queue_url}" class="cer_queue_link">{$queue_name}</a><span class="cer_footer_text"> ({$queue_count})</span></td>
            <td width="10%" nowrap bgcolor="#DDDDDD"><img src="includes/images/cerb_graph.gif" width="{$queue->queue_bar_width}" height="15"><img src="includes/images/cer_graph_cap.gif" width="1" height="15"></td>
          </tr>

	  		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          
	  			{if !empty($node)}
		       {foreach key=cqid item=cnode from=$node}
		       {assign var="child" value=$system_status->queue_list.$cqid}
		          <tr> 
		            <td width="90%" bgcolor="#EEEEEE"><span class="cer_maintable_text"> - </span><a href="{$child->queue_url}" class="cer_queue_link_sm">{$child->queue_name}</a><span class="cer_footer_text"> ({$child->queue_active_tickets})</span></td>
		            <td width="10%" nowrap bgcolor="#DDDDDD"><img src="includes/images/cerb_graph.gif" width="{$child->queue_bar_width}" height="15"><img src="includes/images/cer_graph_cap.gif" width="1" height="15"></td>
		          </tr>
		
			  		<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
			  		{/foreach}
	  			{/if}
	  		
          {/foreach}

        </table>

        

        <table width="200" border="0" cellpadding="0" cellspacing="0"">

		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr> 
            <td align="center" class="boxtitle_blue">Status Breakdown</td>
          </tr>
		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

        </table>

        

        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#999999">

          {foreach key=status item=status_item from=$system_status->statuses}

          <tr class="cer_footer_text"> 

            <td width="95%" bgcolor="#EEEEEE"><a href="{$status_item->status_url}" class="cer_queue_link">{$status}</a></td>

            <td width="5%" align="right" nowrap bgcolor="#DDDDDD"><span class="cer_footer_text">{$status_item->count}</span></td>

          </tr>
          
          <tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          {/foreach}

        </table>

        
        
        <table width="200" border="0" cellpadding="0" cellspacing="0"">

		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr> 
            <td align="center" class="boxtitle_blue">{$smarty.const.LANG_STATUS_LAST_TITLE}</td>
          </tr>
		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

        </table>

        

        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#999999">

          {foreach item=day from=$system_status->day_totals}

          <tr class="cer_footer_text"> 

            <td width="95%" bgcolor="#EEEEEE">{$day->mysql_timestamp|date_format:$smarty.const.LANG_DATE_FORMAT_DAY_TOTALS}</td>

            <td width="5%" align="right" nowrap bgcolor="#DDDDDD"><span class="cer_footer_text">{$day->day_total}</span></td>

          </tr>
          
          <tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          {/foreach}

        </table>

        

        {if $cfg->settings.enable_panel_stats }

        <table width="200" border="0" cellpadding="0" cellspacing="0">

		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
          <tr> 
            <td align="center" class="boxtitle_blue">{$smarty.const.LANG_STATUS_STATS_TITLE}</td>
          </tr>
		  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

        </table>

        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#999999">

          <tr class="cer_footer_text"> 

            <td width="99%" bgcolor="#EEEEEE">{$smarty.const.LANG_STATUS_STATS_TICKET_STORED}</td>

            <td width="1%" align="right" valign="bottom" nowrap bgcolor="#DDDDDD">{$system_status->total_tickets}</td>

          </tr>

		  <tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>          
          
          <tr class="cer_footer_text"> 

            <td width="99%" bgcolor="#EEEEEE">{$smarty.const.LANG_STATUS_STATS_EMAIL_STORED}</td>

            <td width="1%" align="right" valign="bottom" nowrap bgcolor="#DDDDDD">{$system_status->total_threads}</td>

          </tr>
          
          <tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          <tr class="cer_footer_text"> 

            <td width="99%" bgcolor="#EEEEEE">{$smarty.const.LANG_STATUS_STATS_ADDRESS_STORED}</td>

            <td width="1%" align="right" valign="bottom" nowrap bgcolor="#DDDDDD">{$system_status->total_addresses}</td>

          </tr>
          
          <tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

          <tr class="cer_footer_text"> 

            <td bgcolor="#EEEEEE">{$smarty.const.LANG_STATUS_STATS_KB_STORED}</td>

            <td align="right" valign="bottom" nowrap bgcolor="#DDDDDD">{$system_status->total_articles}</td>

          </tr>

        </table>

        {/if}

        

        {include file="home/refresh_box.tpl.php"}

        

        </div>

        

	  </td>

    </tr>

  </table>

