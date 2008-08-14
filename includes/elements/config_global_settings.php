<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_global_settings.php
|
| Purpose: This config include handles global configuration values
|	that used to be in the conifg.php file.  These can now be shared
|	with the parser. 
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/languages.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");

$cer_language = new cer_languages_obj();

// Verify that the connecting user has access to modify configuration values
if(!$priv->has_priv(ACL_GLOBAL_SETTINGS,BITGROUP_2))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="cfg_gui_version" value="<?php echo GUI_VERSION; ?>">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="settings">
<input type="hidden" name="form_submit" value="global_settings">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
  <tr class="cer_maintable_text"> 
    <td class="cer_maintable_text" bgcolor="#FFFFFF"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
		  <tr class="boxtitle_green_glass"> 
			<td colspan="2">Global Settings</td>
		  </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Helpdesk Title:</td>
            <td width="81%">
              <input type="text" name="cfg_helpdesk_title" size="64" maxlength="250" value="<?php echo $cfg->settings["helpdesk_title"]; ?>"><br>
              <span class="cer_footer_text"> Browser title for the helpdesk. Overrides settings in language files. Leave empty to keep language-driven titles.<br>
            </td>
          </tr>
	  		<tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Server Timezone:</td>
            <td width="81%">
				<select name="cfg_server_gmt_offset_hrs" class="cer_footer_text">
					<?php
					$zones = new cer_Timezone();
					foreach($zones->timezones as $offset => $zone) {
						echo sprintf("<option value='%s' %s>%s</option>",
								$offset,
								(($cfg->settings["server_gmt_offset_hrs"] == $offset) ? "SELECTED" : ""),
								$zone
							);
					}
					?>
				</select>
				<br>
              <span class="cer_footer_text">The webserver is reporting <?php echo date("T") . " " . date("O") . " " . ((date("I")) ? "(w/ Daylight Savings Time)" : ""); ?>.<br>
            </td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Session Lifespan:</td>
            <td width="81%">
              <input type="text" name="cfg_session_lifespan" size="5" maxlength="5" value="<?php echo $cfg->settings["session_lifespan"]; ?>"><span class="cer_footer_text"> (minutes)</span><br>
              <span class="cer_footer_text"> Time to keep idle sessions/logins in the system before flushing (in <b>minutes</b>). 1440 minutes = 24 hours = 1 day<br>
             </td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Track Session ID:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_track_sid_url" value="1" <?php echo (($cfg->settings["track_sid_url"])?"checked":""); ?>><br>
              <span class="cer_footer_text">If you're having trouble with cookies, make sure this is <B>checked</B> to track the  
              session id in the URL.  This is a good default to have on to accomodate the most users and firewalls.  If for 
              some reason this is causing problems, <B>uncheck</B> it.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Session IP Security:</td>
            <td width="81%">
              <select name="cfg_session_ip_security">
            <?php
            	$ip_secure = array( 0 => "Full IP Match (e.g, 12.34.56.78)",
            						1 => "Class C Mask (e.g., 12.34.56.xxx)",
            						2 => "Class B Mask (e.g., 12.34.xxx.xxx)",
            						3 => "Class A Mask (e.g., 12.xxx.xxx.xxx)",
            						4 => "Disabled"
            				);
            				
            	foreach($ip_secure as $idx => $ips)
            		echo sprintf("<option value='%d' %s>%s",
            					$idx,
            					(($cfg->settings["session_ip_security"]==$idx)?"selected":""),
            					$ips
            					);
            ?>
              </select>
              <br>
              <span class="cer_footer_text">This option will lock a session to the IP that started it.  You can require matching the full or a partial IP mask.  In the options 
              above the <i><B>xxx</B></i> section refers to a dynamic part of the mask and the options are in order from most to least secure.  A dialup may change IPs wildly
              between connections (dynamic IP), cable and DSL may not change at all (static IP).  If you have helpdesk users with dynamic IPs, <B>Class C Mask</B> is 
              recommended.  If you have mostly static IPs, <B>Full IP Match</B> is recommended.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Flood Protection Timer:</td>
            <td width="81%">
              <input type="text" name="cfg_warcheck_secs" size="5" maxlength="5" value="<?php echo $cfg->settings["warcheck_secs"]; ?>"><span class="cer_footer_text"> (seconds)</span><br>
              <span class="cer_footer_text">Protection from autoresponder wars.  If a new ticket with the 
              same sender address, subject and destination queue is received within this many <b>seconds</b> of an identical message
              then do not send another autoresponse.  (Default is <b>10</b> seconds)<br>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Max Idle Mins:</td>
            <td width="81%">
              <input type="text" name="cfg_who_max_idle_mins" size="3" maxlength="3" value="<?php echo $cfg->settings["who_max_idle_mins"]; ?>"><br>
              <span class="cer_footer_text">After this many <B>minutes</B>, a user will be considered idle and we'll assume they've 
              abandoned their session and exclude them from the who's online list.</span></td>
          </tr>
		  <tr bgcolor="#DDDDDD">
          <td width="19%" class="cer_maintable_heading" valign="top">Time Adjust:</td>
            <td width="81%">
              <input type="text" name="cfg_time_adjust" size="6" maxlength="6" value="<?php echo $cfg->settings["time_adjust"]; ?>"><br>
              <span class="cer_footer_text">Time adjustment between GUI + back-end database <B>(in seconds)</B>.  Usually this is 0, but in some
              cases you may have servers at two different providers.  NOTE: 10800 seconds is 3 hours (i.e., EST->PST).</span></td>
          </tr>
		  <tr bgcolor="#DDDDDD">
          <td width="19%" class="cer_maintable_heading" valign="top">Default Language:</td>
            <td width="81%">
              <select name="cfg_default_language">
              	<?php
              	foreach($cer_language->languages as $lang)
              	{
              		echo sprintf("<option value='%s' %s>%s",
              			$lang->lang_code,
              			(($cfg->settings["default_language"]==$lang->lang_code)?"selected":""),
              			$lang->lang_name . " (" . $lang->lang_code . ")"
              			);
              	}
              	?>
              </select>
              <span class="cer_footer_text">2-3 Digit Language code.  en = English, fr = French, es = Spanish, de = German, etc.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Purge Wait Time:</td>
            <td width="81%">
              <input type="text" name="cfg_purge_wait_hrs" size="4" maxlength="4" value="<?php echo sprintf("%d",$cfg->settings["purge_wait_hrs"]); ?>"><br>
              <span class="cer_footer_text">This many <B>hours</B> must pass before a 'dead' ticket may be completely purged from the system. 
              Raising this number gives you a longer window to recover a ticket accidentily marked 'dead'.  Setting this option to 0 lets you
              purge all dead tickets instantly from Configuration-&gt;Maintenance.  Default is <B>24</B> hours. </span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Output Buffering Callback:</td>
            <td width="81%">
              <input type="text" name="cfg_ob_callback" size="25" maxlength="64" value="<?php echo $cfg->settings["ob_callback"]; ?>"><br>
              <span class="cer_footer_text">The output buffering function to run on GUI output before displaying. Default is NULL for the
              widest compatibility, but most systems can set this to <b>ob_gzhandler</b> to pick up a peformance increase in the browser.</span>
              </td>
          </tr>
		  <tr class="boxtitle_blue_glass"> 
			<td colspan="2">Mail Settings</td>
		  </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Mail Enabled:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_sendmail" value="1" <?php echo (($cfg->settings["sendmail"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Allow the system to send mail.  This should always be <B>checked</B> on live systems. This
              can be set false for demo systems.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Mail Delivery:</td>
            <td width="81%" class="cer_maintable_text">
              <input type="radio" name="cfg_mail_delivery" value="smtp" <?php echo (($cfg->settings["mail_delivery"]=="smtp")?"checked":""); ?>> SMTP 
              <input type="radio" name="cfg_mail_delivery" value="mail" <?php echo (($cfg->settings["mail_delivery"]=="mail")?"checked":""); ?>> Mail
              <br>
              <span class="cer_footer_text"><B>smtp</B> is preferred if your system supports it.  If replies, comments or new tickets 
              from the GUI aren't working, set it to <B>mail</B>.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">SMTP Server:</td>
            <td width="81%">
              <input type="text" name="cfg_smtp_server" size="32" maxlength="64" value="<?php echo $cfg->settings["smtp_server"]; ?>"><br>
              <span class="cer_footer_text">The domain name of the mail server.  <b>localhost</b> should work if the mail server
              resides on the same machine.</span></td>
          </tr>
		  <tr class="boxtitle_gray_glass_dk"> 
			<td colspan="2">Ticket Settings</td>
		  </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Enable Ticket ID Masking:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_enable_id_masking" value="1" <?php echo (($cfg->settings["enable_id_masking"])?"checked":""); ?>><br>
              <span class="cer_footer_text">When enabled a ticket ID mask will be used on all correspondence, such as RTM-98754-321. When disabled the true ticket ID will be used, such as 1234.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Auto-Add Cc Requesters:</td>
            <td width="81%" class="cer_maintable_text">
              <input type="radio" name="cfg_auto_add_cc_reqs" value="1" <?php echo (($cfg->settings["auto_add_cc_reqs"])?"checked":""); ?>> true
              <input type="radio" name="cfg_auto_add_cc_reqs" value="0" <?php echo ((!$cfg->settings["auto_add_cc_reqs"])?"checked":""); ?>> false
              <br>
              <span class="cer_footer_text">Automatically add CC'd addresses to ticket requesters list for incoming mail.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Enable Panel Stats:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_enable_panel_stats" value="1" <?php echo (($cfg->settings["enable_panel_stats"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Show statistics on the home page.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Enable Customer History:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_enable_customer_history" value="1" <?php echo (($cfg->settings["enable_customer_history"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Show customer's past support history on ticket display.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Enable Audit Log:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_enable_audit_log" value="1" <?php echo (($cfg->settings["enable_audit_log"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Show all actions performed by all users when displaying a ticket.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Ticket Due Hours (default):</td>
            <td width="81%">
              <input type="text" name="cfg_overdue_hours" size="3" maxlength="3" value="<?php echo $cfg->settings["overdue_hours"]; ?>"><br>
              <span class="cer_footer_text">By default, a new ticket or reply is due after this many hours if there is no queue-specific default or Service Level Agreement (SLA) in place to manage the due date of a given ticket more accurately.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Customer Ticket History Max:</td>
            <td width="81%">
              <input type="text" name="cfg_customer_ticket_history_max" size="3" maxlength="3" value="<?php echo $cfg->settings["customer_ticket_history_max"]; ?>"><br>
              <span class="cer_footer_text">The number of customer support history entries to show per page.</span></td>
		 </tr>
		 <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Bcc Watchers:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_bcc_watchers" value="1" <?php echo (($cfg->settings["bcc_watchers"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Bcc emails to watchers. Hides the watcher's email addresses.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Enable Watcher Delivery only to Assigned Techs:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_watcher_assigned_tech" value="1" <?php echo (($cfg->settings["watcher_assigned_tech"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Send watcher emails only to watchers who are assigned to the ticket instead of to all watchers.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Save message_source.xml attachments:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_save_message_xml" value="1" <?php echo (($cfg->settings["save_message_xml"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Save message_source.xml attachments. Disable this option to decrease total data size saved to database.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Do not send system attachments to watchers:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_watcher_no_system_attach" value="1" <?php echo (($cfg->settings["watcher_no_system_attach"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Do not send system attachments ("message_source.xml" and/or "html_mime_part.html") to the watchers.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Don't send watcher emails to email sender:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_not_to_self" value="1" <?php echo (($cfg->settings["not_to_self"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Do not send watcher emails to the watcher who was the sender of the email.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Set Watcher From: address to user's email address:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_watcher_from_user" value="1" <?php echo (($cfg->settings["watcher_from_user"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Set watcher email's from: address to the address of the original email sender.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">&quot;Send Precedence: bulk&quot;:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_send_precedence_bulk" value="1" <?php echo (($cfg->settings["send_precedence_bulk"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Include &quot;Precedence: bulk&quot; in mail header for outgoing email.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Users Can Only Assign on Own Queues:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_user_only_assign_own_queues" value="1" <?php echo (($cfg->settings["user_only_assign_own_queues"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">When this option is checked users can only assign tickets to queues they have read or write access on.  When unchecked 
			  users can assign tickets to any queue, but still won't be able to open queues they don't have access to.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Automatically Delete Tickets &quot;Marked Spam&quot;:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_auto_delete_spam" value="1" <?php echo (($cfg->settings["auto_delete_spam"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Automatically set tickets 'dead' when manually marked as spam.</span></td>
          </tr>

          <tr class="boxtitle_blue_glass"> 
			<td colspan="2">Search Index Settings</td>
		  </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Index Numbers:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_search_index_numbers" size="2" maxlength="2" value="1" <?php echo (($cfg->settings["search_index_numbers"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Allows the text search engine to index purely numerical words (e.g., 911 or 234231).  If you need this functionality, check this box.  Enabling will use slightly more database space, disabling will conserve the space.  System may require a search re-index after changing this option.  The number must still be within your set minimum and maximum word length.</span></td>
          </tr>

		  <tr class="boxtitle_green_glass"> 
			<td colspan="2">Parser Settings</td>
		  </tr>
		  <input type="hidden" name="cfg_parser_version" value="<?php echo $cfg->settings["parser_version"]; ?>">
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Secure Mode:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_parser_secure_enabled" value="1" <?php echo (($cfg->settings["parser_secure_enabled"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Require a login and password to run the parser.  If this is enabled, you <b>*must*</b> have this login and password defined in your parser's <b>config.xml</b> file.  Default is disabled.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Parser User:</td>
            <td width="81%">
              <input type="text" name="cfg_parser_secure_user" size="20" maxlength="64" value="<?php echo $cfg->settings["parser_secure_user"]; ?>"><br>
              <span class="cer_footer_text">If Secure Mode is enabled, this login must match the one in your parser's <b>config.xml</b> file.  Otherwise leave blank.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Parser Password:</td>
            <td width="81%">
              <input type="text" name="cfg_parser_secure_password" size="20" maxlength="64" value="<?php echo $cfg->settings["parser_secure_password"]; ?>"><br>
              <span class="cer_footer_text">If Secure Mode is enabled, this password must match the one in your parser's <b>config.xml</b> file.  Otherwise leave blank.</span></td>
          </tr>
          
          <?php
          if(!HIDE_XSP_SETTINGS)
          {
          ?>
		  <tr class="boxtitle_red_glass"> 
			<td colspan="2">xSP Gateway Settings</td>
		  </tr>
		  <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Satellite Enabled:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_satellite_enabled" value="1" <?php echo (($cfg->settings["satellite_enabled"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Settings for Cerberus xSP Gateway.  If you don't know what 
              this does, leave all the xSP values blank.  For more info check the xSP Gateway docs.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">xSP Gateway URL:</td>
            <td width="81%">
              <input type="text" name="cfg_xsp_url" size="64" maxlength="255" value="<?php echo $cfg->settings["xsp_url"]; ?>"><br>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">xSP Login:</td>
            <td width="81%">
              <input type="text" name="cfg_xsp_login" size="20" maxlength="64" value="<?php echo $cfg->settings["xsp_login"]; ?>"><br>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">xSP Password:</td>
            <td width="81%">
              <input type="text" name="cfg_xsp_password" size="20" maxlength="64" value="<?php echo $cfg->settings["xsp_password"]; ?>"><br>
          </tr>
          <?php
          }
          ?>
		  
		  <tr class="boxtitle_gray_glass_dk"> 
			<td colspan="2">Knowledgebase Settings</td>
		  </tr>
		  <tr bgcolor="#DDDDDD">
          <td width="19%" class="cer_maintable_heading" valign="top">Enable Knowledgebase:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_show_kb" value="1" <?php echo (($cfg->settings["show_kb"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Show the Knowledgebase features in the GUI.</span></td>
          </tr>
		  <tr bgcolor="#DDDDDD">
          <td width="19%" class="cer_maintable_heading" valign="top">Show Knowledgebase Topic Totals:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_show_kb_topic_totals" value="1" <?php echo (($cfg->settings["show_kb_topic_totals"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Total up the number of articles per knowledgebase category.  If the knowledgebase runs slow try
              <B>unchecking</B> this, otherwise leave it <B>checked</B>.</span></td>
          </tr>
		  <tr bgcolor="#DDDDDD">
          <td width="19%" class="cer_maintable_heading" valign="top">Knowledgebase Comments Require Approval:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_kb_editors_enabled" value="1" <?php echo (($cfg->settings["kb_editors_enabled"])?"checked":""); ?>><br>
              <span class="cer_footer_text">When users provide comments on knowledgebase articles, this option requires that editors
              review and specifically approve comments before they are visible in the public knowledgebase.  This works much 
              like moderators in forums.  Leaving this option unchecked will allow posted comments to be immediately and publically
              visible without prior company review or approval.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td align="right" colspan="2">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT; ?>">
			</td>
		</tr>
        </table>
    </td>
  </tr>
</table>
</form>
<br>
