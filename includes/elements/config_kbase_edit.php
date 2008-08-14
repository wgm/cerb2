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
| File: config_kbase_edit.php
|
| Purpose: This config include handles knowledgebase category create & 
|		edit functionality. 
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

// Verify that the connecting user has access to modify configuration/kbase values
if((!$priv->has_priv(ACL_KB_CATEGORY_CREATE,BITGROUP_1) && !$priv->has_priv(ACL_KB_CATEGORY_EDIT,BITGROUP_1)) || (!$priv->has_priv(ACL_KB_CATEGORY_CREATE,BITGROUP_1) && $kbid==0))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

if(!isset($kbid))
	{ echo LANG_KB_NO_CATID; exit(); }	

$sql = "SELECT kbc.kb_category_id, kbc.kb_category_name, kbc.kb_category_parent_id FROM knowledgebase_categories kbc WHERE kbc.kb_category_id = $kbid";
$result = $cerberus_db->query($sql);
$kbase_data = $cerberus_db->fetch_row($result);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pkbid" value="<?php echo  $kbase_data["kb_category_id"]; ?>">
<input type="hidden" name="module" value="kbase">
<input type="hidden" name="form_submit" value="kbase_edit">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="B5B5B5">
  <tr class="boxtitle_orange_glass"> 
<?php
if($kbid==0) {
    ?><td><?php echo  LANG_CONFIG_KBASECAT_ADD ?></td><?php
}
else {
    ?><td><?php echo  LANG_CONFIG_KBASECAT_EDIT ?> '<?php echo  $kbase_data["kb_category_name"] ?>'</td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
      <div align="right" class="cer_maintable_text"></div>
      <div align="left">
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_KBASECAT_CATNAME; ?>:</td>
            <td width="81%">
              <input type="text" name="kbase_cat_name" size="25" maxlength="32" value="<?php echo  stripslashes(str_replace("\"","&quot;",$kbase_data["kb_category_name"])); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_KBASECAT_EXAMPLE; ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_KBASECAT_PARENT; ?>:</td>
            <td width="81%">
            	<select name="kbase_cat_parent_id">
            		<option value="0"><?php echo LANG_CONFIG_KBASECAT_NONE; ?>
							<?php
								$kb_cat = $kbase_data["kb_category_id"];
							
								// [JAS]: Make sure we're not displaying the current category as a parent option
								unset($kbase_tree->category_dropdown[$kb_cat]);
								
								// [JAS]: Or any of its children
								if(!empty($kbase_tree->categories[$kb_cat]->children))
								foreach($kbase_tree->categories[$kb_cat]->children as $ch_ptr) {
									unset($kbase_tree->category_dropdown[$ch_ptr->category_id]);
								}
								
								if(!empty($kbase_tree->category_dropdown))
								foreach($kbase_tree->category_dropdown as $idx => $cat) {
									echo "<option value=\"$idx\" ";
									if($idx == $kbase_data["kb_category_parent_id"]) echo "SELECTED";
									echo ">$cat";
								}
								
							?>
							</select>
						</td>
	        </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<input type="submit" class="cer_button_face" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT; ?>">
		</td>
	</tr>
</table>
</form>
<br>
