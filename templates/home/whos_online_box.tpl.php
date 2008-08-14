<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr> 
    <td class="boxtitle_blue_glass">{$smarty.const.LANG_FOOTER_WHOS_ONLINE}: ({$cer_who->who_user_count_string})</td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  {section name=who_id loop=$cer_who->who_users}
  <tr class="cer_maintable_text_{if %who_id.rownum% % 2 == 0}2{else}1{/if}"> 
    <td style="padding-left: 2px;">
    	<span class="cer_whos_online_text">
    	{if !empty($cer_who->who_users[who_id]->user_name)}
    		<b>{$cer_who->who_users[who_id]->user_name}</b>
    	{/if}
    	{if !empty($cer_who->who_users[who_id]->user_login)}
    		({$cer_who->who_users[who_id]->user_login})
    	{/if}
    	{$cer_who->who_users[who_id]->user_action_string} 
    	(ip: {$cer_who->who_users[who_id]->user_ip} 
    	idle: {$cer_who->who_users[who_id]->user_idle_secs}) 
    	{if $cer_who->who_users[who_id]->user_pm_url != ""}
    		(<a href="{$cer_who->who_users[who_id]->user_pm_url}" class="cer_whos_online_text">{$smarty.const.LANG_FOOTER_SEND_PM}</a>)
    	{/if}
    	</span>
    </td>
  </tr>
  <tr><td bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  {/section}
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#666666"> 
    <td><img src="includes/images/spacer.gif" width="1" height="5"></td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>