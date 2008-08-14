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
| File: config_reindex_threads.php
|
| Purpose: The configuration include for reindexing the ticket thread 
| 		& word text indexes.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/*
require_once "includes/functions/privileges.php";

if(!$priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}
*/
?>
<script>
<!--
sid = "sid=<?php echo $session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

function formatURL(url)
{
  if(show_sid) { url = url + "&" + sid; }
  return(url);
}

function doReindexThreads() {
	step = document.reindex.step.value;
	window.open(formatURL('<?php echo $cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"]; ?>/reindex_threads_popup.php?step=' + step + '&x='),"reindex_t<?php echo mktime(); ?>","width=550,height=500"); 
} 
-->
</script>
<form name="reindex" action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="maintenance">
<input type="hidden" name="module" value="search_index">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": Reindex complete!</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td colspan="2">Reindex E-mail Message Threads</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="2" class="cer_maintable_heading" valign="top" align="left"> 
      <div class="cer_maintable_heading"> 
        <table width="98%" border="0" cellspacing="1" cellpadding="2">
          <tr> 
            <td width="21%" class="cer_maintable_heading">Reindex Threads:</span><br>
												<span class="cer_footer_text">Clicking the button will spawn a new window to proceed with
												reindexing the e-mail threads from the database.<br></span>
												</td>
            <td width="79%" valign="top">
              <span class="cer_maintable_heading">Tickets per pass:</span> <input type="text" name="step" value="50" size="4">&nbsp;
              <input type="button" value="<?php echo  LANG_CONFIG_PURGE_SUBMIT ?>" class="cer_button_face" class="cer_button_face" OnClick="javascript:doReindexThreads();">&nbsp;<br>
              <span class="cer_footer_text">Slower servers will want to use 10 to 25 tickets per pass and more powerful servers can get away with numbers in the 100 - 500+ range.</div>
              </td>
          </tr>
          <tr> 
            <td width="21%" class="cer_maintable_heading">&nbsp;</td>
            <td width="79%">&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
</table>
</form>