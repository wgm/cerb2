<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_kbase_comments.php
|
| Purpose: For approval + rejection of knowledgebase article comments
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");

require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseTree.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

$kbase_tree = new cer_KnowledgebaseTree();

// [JAS]: Verify that the connecting user has access to modify configuration/kbase values
if(!$priv->has_priv(ACL_KB_COMMENT_EDITOR,BITGROUP_2))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}
	
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="kbase_comments">
<input type="hidden" name="form_submit" value="kbase_comments">
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td>Knowledgebase Article Comments Management</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
	<?php 
$sql = "SELECT kbp.kb_problem_summary, kc.kb_comment_id, kc.poster_email, kc.poster_comment, kc.kb_comment_date, kc.kb_comment_approved, kc.poster_ip, ".
	" kb.kb_category_id, kb.kb_id " .
	" FROM knowledgebase_comments kc LEFT JOIN knowledgebase kb ON (kb.kb_id = kc.kb_article_id) ".
	" LEFT JOIN knowledgebase_problem kbp USING (kb_id) ".
	" WHERE kc.kb_comment_approved = 0 ".
	" ORDER BY kb.kb_category_id,kc.kb_comment_date ASC";
$comment_res = $cerberus_db->query($sql);
if($cerberus_db->num_rows($comment_res))
{
	echo "<table width='100%' cellpadding='2' cellspacing='0'>";
	while($com_res = $cerberus_db->fetch_row($comment_res))
	{
	?>
	<tr>
		<td bgcolor="#D0D0D0" class="cer_maintable_text">
			<input type="checkbox" name="comment_ids[]" value="<?php echo $com_res["kb_comment_id"]; ?>">
			<b><?php echo str_replace("@"," at ",str_replace("."," dot ",$com_res["poster_email"])); ?></b> (IP: <?php echo $com_res["poster_ip"]; ?>)<br>
			<?php 
				$date = new cer_DateTime($com_res["kb_comment_date"]);
				echo $date->getUserDate();
			?>
			<?php
			if($cfg->settings["kb_editors_enabled"] && !$com_res["kb_comment_approved"]) { echo " (<span class='cer_configuration_updated'>Pending Approval</span>) "; }
			?>
			<br>
			<?php
			$cat_id = $com_res["kb_category_id"];
			echo $kbase_tree->printTrail($cat_id) . " : ";
			echo "<b><a href='knowledgebase.php?mode=view_entry&kbid=".$com_res["kb_id"]."&root=$cat_id&sid=".$session->session_id."' class='cer_maintable_heading'>" . $com_res["kb_problem_summary"] . "</a></b>";
			?>
	 	</td>
	</tr>
	<tr>
		<td bgcolor="#EEEEEE" class="cer_knowledgebase_comment">
			<?php
			$comment = preg_replace("/(http|https):\/\/(.\S+)()/si",
				"<a href='\$1://\$2'>\$1://\$2</a>",
				$com_res["poster_comment"]);
			$comment = str_replace("  "," &nbsp;",$comment);
			$comment = str_replace(chr(9),"  &nbsp; ",$comment); // Replace tabs with three spaces
			$comment = str_replace("\r\n","\n",$comment);
			$comment = str_replace("\n","<br>",$comment);
			echo stripslashes($comment);
			?>
	 	</td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td bgcolor="#B5B5B5">
		<select name="comment_action">
			<option value="approve" selected>Approve Checked Comments
			<option value="reject">Reject and Delete Checked Comments
		</select>
		<input type="submit" class="cer_button_face" value="Commit">
		</td>
	</tr>
</table>
	<?php
}
else
{
	echo "No user comments pending approval.";
}
?>
	</td>
  </tr>
</table>
</form>
<br>
