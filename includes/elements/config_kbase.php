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
| File: config_kbase.php
|
| Purpose: The configuration include for the knowledgebase categories.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseTree.class.php");

$kbase_tree = new cer_KnowledgebaseTree();

// [JAS]: Verify that the connecting user has access to modify configuration/kbase values
if(!$priv->has_priv(ACL_KB_CATEGORY_EDIT,BITGROUP_1) && !$priv->has_priv(ACL_KB_CATEGORY_DELETE,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post" onsubmit="return confirm('<?php echo  LANG_CONFIG_KBASECAT_CONFIRM ?>');">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="kbid" value="<?php echo  $kbid ?>">
<input type="hidden" name="module" value="kbase">
<input type="hidden" name="form_submit" value="kbase_delete">
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td><?php echo LANG_CONFIG_KBASECAT_TITLE; ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
      
				<?php if($priv->has_priv(ACL_KB_CATEGORY_CREATE,BITGROUP_1)) { ?><a href="<?php echo cer_href("configuration.php?module=kbase&pkbid=0"); ?>" class="cer_maintable_subjectLink"><?php echo  LANG_CONFIG_KBASECAT_CREATE; ?></a><br><?php } ?>
				
				<?php
					foreach($kbase_tree->category_checked as $c_idx => $cat) {
						list($level,$category_name) = $cat;
						
						if($priv->has_priv(ACL_KB_CATEGORY_DELETE,BITGROUP_1)) {
							echo "<input type=\"checkbox\" name=\"kbids[]\" value=\"$c_idx\"";
							if(@count($cat->children) != 0) {
								echo " OnClick=\"javascript:alert('" . LANG_CONFIG_KBASECAT_SUBCAT_NODEL . "');this.checked=false;\"";
							}
							echo ">";
						}
						
						if($level-1>=0) echo str_repeat("&nbsp;&nbsp;&nbsp;",$level);
						if($level) echo "- ";
						
						echo "<a href=\"" . cer_href(sprintf("configuration.php?module=kbase&pkbid=%d",$c_idx));
						echo "\" class=\"cer_maintable_subjectLink\">" .  $category_name . "</a><br>";
					}
				?>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
				<?php if($priv->has_priv(ACL_KB_CATEGORY_DELETE,BITGROUP_1)) { ?><input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>"><?php } ?>&nbsp;
		</td>
	</tr>
</table>
<br>
