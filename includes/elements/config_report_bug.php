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
| File: config_report_bug.php
|
| Purpose: The config include for reporting bugs to the Cerberus
|		development team.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//	kbase values
if(!$priv->has_priv(ACL_OPTIONS_REPORT_BUG,BITGROUP_1))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$sql = "select count(t.ticket_id) as ticket_count from ticket t";
$result = $cerberus_db->query($sql,false);
if($cerberus_db->num_rows($result) > 0) { $row = $cerberus_db->fetch_row($result); $ticket_count = $row[0]; }
$sql = "select count(th.thread_id) as thread_count from thread th";
$result = $cerberus_db->query($sql,false);
if($cerberus_db->num_rows($result) > 0) { $row = $cerberus_db->fetch_row($result); $thread_count = $row[0]; }
$sql = "select count(q.queue_id) as queue_count from queue q";
$result = $cerberus_db->query($sql,false);
if($cerberus_db->num_rows($result) > 0) { $row = $cerberus_db->fetch_row($result); $queue_count = $row[0]; }
$sql = "select count(u.user_id) as user_count from user u";
$result = $cerberus_db->query($sql,false);
if($cerberus_db->num_rows($result) > 0) { $row = $cerberus_db->fetch_row($result); $user_count = $row[0]; }

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_BUG_SUCCESS . "</span><br>"; ?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="bug">
<input type="hidden" name="form_submit" value="bug_send">
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
        <tr class="boxtitle_orange_glass"> 
          <td colspan="2"><?php echo  LANG_CONFIG_BUG_TITLE ?></td>
        </tr>
        <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
          <td colspan="2" valign="top" align="left"> 
              <table width="98%" border="0" cellspacing="1" cellpadding="2">
                <tr> 
                  <td width="120" class="cer_maintable_heading"><?php echo  LANG_CONFIG_BUG_SUMMARY ?>:</td>
                  <td> 
                    <input type="text" name="bug_subject" size="45" maxlength="64">
                  </td>
                </tr>
                <tr> 
                  <td width="120" class="cer_maintable_heading"><?php echo  LANG_CONFIG_BUG_NAME ?>:</td>
                  <td> 
                    <input type="text" name="bug_sender" size="45" maxlength="64" value="<?php echo  $session->vars["login_handler"]->user_name; ?>">
                  </td>
                </tr>
                <tr> 
                  <td width="120" class="cer_maintable_heading"><?php echo  LANG_CONFIG_BUG_EMAIL ?>:</td>
                  <td> 
                    <input type="text" name="bug_sender_email" size="45" maxlength="64" value="<?php echo  $session->vars["login_handler"]->user_email; ?>">
                  </td>
                </tr>
                <tr> 
                  <td width="120" class="cer_maintable_heading">&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td width="120" class="cer_maintable_heading" valign="top"><?php echo  LANG_CONFIG_BUG_DESC ?>:</td>
                  <td> 
                    <textarea name="bug_description" cols="55" rows="15"><?php
echo"\nDescription:\n\n";
echo "\n===[ DEBUG INFORMATION ]===\n";
echo "Cerberus GUI Version: " . GUI_VERSION . "\n";
echo "Cerberus Parser Version: " . @$cfg->settings["parser_version"] . "\n";
echo "Server Software: " . @$_SERVER["SERVER_SOFTWARE"] . "  MySQL/" . @mysql_get_client_info() . "\n";
echo	"Machine Type: " . @PHP_OS . "\n";
echo	"Client Browser: " . @$_SERVER["HTTP_USER_AGENT"] . "\n";
echo "Ticket Count: " . @$ticket_count . "\n";
echo "Thread Count: " . @$thread_count . "\n";
echo "Queue Count: " . @$queue_count . "\n";
echo "User Count: " . @$user_count . "\n";
?></textarea>
                  </td>
                </tr>
              </table>
          </td>
        </tr>
        <tr bgcolor="#999999" class="cer_maintable_text" align="right"> 
          <td colspan="2" class="cer_maintable_heading" valign="top"> 
            <input type="submit" class="cer_button_face" value="<?php echo  LANG_CONFIG_BUG_SUBMIT ?>">
          </td>
        </tr>
      </table>
</form>
