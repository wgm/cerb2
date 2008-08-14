    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td valign="top"> 
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
              <tr> 
                <td class="{$tabs->tab_thread_bg_css}" align="center">
                	<a href="{$urls.tab_display}" class="{$tabs->tab_thread_css}">{$smarty.const.LANG_DISPLAY_DISPLAY}</a>
                </td>
                <td>&nbsp;</td>

                {if $urls.tab_props}
                <td class="{$tabs->tab_props_bg_css}" align="center">
                	<a href="{$urls.tab_props}" class="{$tabs->tab_props_css}">{$smarty.const.LANG_DISPLAY_PROPS}</a>
                </td>
                <td>&nbsp;</td>
                {/if}
                
                {if $urls.tab_antispam}
                <td class="{$tabs->tab_antispam_bg_css}" align="center">
                	<a href="{$urls.tab_antispam}" class="{$tabs->tab_antispam_css}">{$smarty.const.LANG_DISPLAY_ANTISPAM}</a>
                </td>
                <td>&nbsp;</td>
                {/if}
                
                {if $urls.tab_batch}
                <td class="{$tabs->tab_batch_bg_css}" align="center">
                	<a href="{$urls.tab_batch}" class="{$tabs->tab_batch_css}">{$smarty.const.LANG_DISPLAY_BATCH}</a>
                </td>
                <td>&nbsp;</td>
                {/if}

                {if $urls.tab_log}
                <td class="{$tabs->tab_log_bg_css}" align="center">
                	<a href="{$urls.tab_log}" class="{$tabs->tab_log_css}">{$smarty.const.LANG_DISPLAY_LOG}</a>
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
