<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="preferences">
<input type="hidden" name="form_submit" value="preferences">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_glass"> 
    <td>&nbsp;{$smarty.const.LANG_PREF_TITLE}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr> 
          <td width="30%" class="cer_maintable_heading" valign="top">{$smarty.const.LANG_PREF_TIMEZONE}:</td>
					<td>
            <select name="gmt_offset" class="cer_footer_text">
            	{html_options options=$timezones->timezones selected=$user_prefs->user_gmt_offset}
            </select>
            <br>
            <span class="cer_footer_text">
            	Using GMT {$user_prefs->user_gmt_offset}.  The time is now {$time_now}. 
            </span>
					</td>
        </tr>
        <tr> 
          <td width="30%" class="cer_maintable_heading">{$smarty.const.LANG_REFRESH_TITLE}:</td>
					<td>
            <select name="refresh_rate">
              {html_options options=$user_prefs->options_refresh selected=$user_prefs->user_refresh_rate}
            </select>		
					</td>
        </tr>
	  		<tr> 
          <td class="cer_maintable_heading">{$smarty.const.LANG_PREF_MSG_TITLE}: </td>
          <td class="cer_footer_text">
            {html_radios name="ticket_order" options=$user_prefs->options_msg_order checked=$user_prefs->user_ticket_order}
          </td>
        </tr>
	  		<tr> 
          <td class="cer_maintable_heading">{$smarty.const.LANG_WORD_LANGUAGE}: </td>
          <td>
						<select name="prefs_user_language">
			  			{html_options options=$user_prefs->options_language selected=$user_prefs->user_language}
						</select>
            </td>
        </tr>
	  		<tr> 
          <td class="cer_maintable_heading">{$smarty.const.LANG_PREF_KEYBOARD_SHORTCUTS}: </td>
          <td class="cer_footer_text">
				<input type="checkbox" name="keyboard_shortcuts" value="1" {if $user_prefs->user_keyboard_shortcuts}checked{/if}> {$smarty.const.LANG_WORD_ENABLED}
            </td>
        </tr>
        
			</table>
    </td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>

{if isset($password_error)}
 <span class="cer_configuration_updated">{$smarty.const.LANG_WORD_ERROR}: {$password_error}</span><br>
{/if}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_orange_glass"> 
    <td>&nbsp;{$smarty.const.LANG_PREF_PW_CHANGE}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
          <td class="cer_maintable_heading" width="30%">{$smarty.const.LANG_PREF_PW_CURRENT}</td>
          <td>
				<input type="password" name="password_current" size="25" maxlength="25">
          </td>
        </tr>
	  		<tr> 
          <td class="cer_maintable_heading">{$smarty.const.LANG_PREF_PW_NEW}</td>
          <td>
		  		<input type="password" name="password_new" size="25" maxlength="25">
          </td>
        </tr>
	  		<tr> 
          <td class="cer_maintable_heading">{$smarty.const.LANG_PREF_PW_VERIFY}</td>
          <td>
				<input type="password" name="password_verify" size="25" maxlength="25">
          </td>
        </tr>
			</table>
	 	</td>
	</tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>
<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_green_glass"> 
    <td>&nbsp;{$smarty.const.LANG_PREF_AUTO_SIG}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text" align="left"> 
	    <textarea name="signatureBox" cols="80" rows="10">{$user_prefs->user_signature}</textarea><br>
	    <span class="cer_maintable_heading">{$smarty.const.LANG_PREF_AUTO_SIG_PLACEMENT} </span> 	
	    <span class="cer_footer_text"> 
	     {html_radios name="signature_pos" options=$user_prefs->options_sig_pos checked=$user_prefs->user_signature_pos}
	    </span>&nbsp;&nbsp;&nbsp;
	    <span class="cer_maintable_heading">{$smarty.const.LANG_PREF_AUTO_SIG_AUTO_INSERT} </span> 
	    <span class="cer_footer_text"> 
	     <input type=checkbox name="signature_autoinsert" {if $user_prefs->user_signature_autoinsert}checked{/if} value=1>
	    </span>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
		  <input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
		</td>
	</tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
</table>

</form>
