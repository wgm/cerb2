    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td valign="top"> 
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr> 
                {if $urls.tab_dashboard}
                <td class="{$tabs->tab_dashboard_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_dashboard}" class="{$tabs->tab_dashboard_css}">{$smarty.const.LANG_MYCERBERUS_HEADERS_DASHBOARD}</a>{*$smarty.const.LANG_WORD_PREFERENCES|lower*}
                </td>
                <td>&nbsp;</td>
                {/if}

                {if $urls.tab_preferences}
                <td class="{$tabs->tab_prefs_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_preferences}" class="{$tabs->tab_prefs_css}">{$smarty.const.LANG_MYCERBERUS_HEADERS_PREFERENCES}</a>{*$smarty.const.LANG_WORD_PREFERENCES|lower*}
                </td>
                <td>&nbsp;</td>
                {/if}
                
                {if $urls.tab_layout}
                <td class="{$tabs->tab_layout_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_layout}" class="{$tabs->tab_layout_css}">{$smarty.const.LANG_WORD_LAYOUT}</a>
                </td>
                <td>&nbsp;</td>
                {/if}

                {if $urls.tab_notification}
                <td class="{$tabs->tab_notify_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_notification}" class="{$tabs->tab_notify_css}">{$smarty.const.LANG_MYCERBERUS_HEADERS_NOTIFICATION}</a>
                </td>
                <td>&nbsp;</td>
                {/if}
                
                {if $urls.tab_assign}
                <td class="{$tabs->tab_assign_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_assign}" class="{$tabs->tab_assign_css}">{$smarty.const.LANG_MYCERBERUS_HEADERS_QUICKASSIGN}</a>
                </td>
                <td>&nbsp;</td>
                {/if}

                {if $urls.tab_tasks}
                <td class="{$tabs->tab_tasks_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_tasks}" class="{$tabs->tab_tasks_css}">{$smarty.const.LANG_MYCERBERUS_HEADERS_PROJECTS}</a>
                </td>
                <td>&nbsp;</td>
                {/if}
                
                {if $urls.tab_messages}
                <td class="{$tabs->tab_msgs_bg_css}" align="center" nowrap>
                	<a href="{$urls.tab_messages}" class="{$tabs->tab_msgs_css}">{$smarty.const.LANG_MYCERBERUS_HEADERS_PMS}</a>
                </td>
                <td>&nbsp;</td>
                {/if}

              </tr>
            </table>
          </td>
        </tr>
        <tr bgcolor="#858585"> 
          <td valign="top"><img src="includes/images/spacer.gif" width="1" height="2"></td>
        </tr>
        <tr> 
          <td valign="top"><img src="includes/images/spacer.gif" width="1" height="5"></td>
        </tr>
      </table>
