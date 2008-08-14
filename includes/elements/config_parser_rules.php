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
| File: config_parser_rules.php
|
| Purpose: The configuration include for configuring parser message rules.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
if(!$priv->has_priv(ACL_MAILRULE_CREATE) && !$priv->has_priv(ACL_MAILRULE_EDIT) && !$priv->has_priv(ACL_MAILRULE_DELETE))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

$sql = "SELECT r.rule_id, r.rule_name, r.rule_pre_parse ".
	"FROM rule_entry r ".
	"ORDER BY r.rule_order";
$result = $cerberus_db->query($sql);

$pre_rules = array();
$post_rules = array();

// [JAS]: [TODO] This should be using the Rules API object rather than a custom SQL query.

if($cerberus_db->num_rows($result))
while($row = $cerberus_db->fetch_row($result))
{
	$rule = array();
	$rule["id"] = $row["rule_id"];
	$rule["name"] = stripslashes($row["rule_name"]);
	
	if($row["rule_pre_parse"]) {
		$pre_rules[$rule["id"]] = $rule;
	} else { 
		$post_rules[$rule["id"]] = $rule;
	}
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php?module=rules" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="rules_order">
<input type="hidden" name="pre_rules_ordered" value="<?php echo implode(",",array_keys($pre_rules)); ?>">
<input type="hidden" name="post_rules_ordered" value="<?php echo implode(",",array_keys($post_rules)); ?>">

<script>
	function mailRuleEdit(list) {
		var id = list.options[list.selectedIndex].value;
		
		if(id) {
			var url = "configuration.php?module=rules&prid=" + id + "&ck=" + getCacheKiller();
			document.location = url;
		}
	}
	
	function mailRuleDelete(list) {
		var id = list.options[list.selectedIndex].value;
		if(id && confirm('Are you sure you want to delete the selected mail rule?')) {
			var url = "configuration.php?module=rules&form_submit=rule_delete&prid=" + id + "&ck=" + getCacheKiller();
			document.location = url;
		}
	}
</script>

<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td>Parser Mail Rules</td>
  </tr>
  

  <tr class="boxtitle_gray_glass_dk"> 
    <td align="left"> 
  		Pre-Parse Mail Rules
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		Pre-Parse Mail Rules are run before an e-mail message enters the helpdesk.  These rules are best used to combat spam 
  		and other nuisances you may not want entering your helpdesk as a ticket.<br>
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
 		<a href="<?php echo cer_href("configuration.php?module=rules&prid=0&type=pre"); ?>" class="cer_maintable_subjectLink">Create a New Pre-Parse Rule</a><br>
    </td>
  </tr>
  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
    	
    	<table cellpadding="5" cellspacing="0">
    	<tr>
    		<td>
		    	<select name="pre_rule_id" size="10">
	    		<?php
	    		foreach($pre_rules as $rule) {
	    			echo sprintf("<option value='%s'>%s",
	    					$rule["id"],
	    					$rule["name"]
	    				);
	    		}
	    		?>
				</select>
			</td>
			<td valign="top">
				<input type="button" class="cer_button_face" value="Edit Rule" onclick="javascript:mailRuleEdit(this.form.pre_rule_id);"><br>
				<input type="button" class="cer_button_face" value="Delete Rule" onclick="javascript:mailRuleDelete(this.form.pre_rule_id);"><br>
				<br>
				<input type="button" class="cer_button_face" value="Move Up" onclick="javascript: moveUp(this.form.pre_rule_id); saveListState(this.form.pre_rule_id,this.form.pre_rules_ordered);"><br>
				<input type="button" class="cer_button_face" value="Move Down" onclick="javascript: moveDown(this.form.pre_rule_id); saveListState(this.form.pre_rule_id,this.form.pre_rules_ordered);"><br>
			</td>
		</tr>
		</table>
		
    </td>
  </tr>
  
  <tr class="boxtitle_gray_glass_dk"> 
    <td align="left"> 
  		Post-Parse Mail Rules
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		Post-Parse Mail Rules are run after an e-mail message has been transformed into a ticket.  These are best used to change the properties 
  		of a ticket (owner, status, priority, queue) based on your criteria.
    </td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		<a href="<?php echo cer_href("configuration.php?module=rules&prid=0&type=post"); ?>" class="cer_maintable_subjectLink">Create a New Post-Parse Rule</a><br>
    </td>
  </tr>
  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
    	
    	<table cellpadding="5" cellspacing="0">
    	<tr>
    		<td>
		    	<select name="post_rule_id" size="10">
	    		<?php
	    		foreach($post_rules as $rule) {
	    			echo sprintf("<option value='%s'>%s",
	    					$rule["id"],
	    					$rule["name"]
	    				);
	    		}
	    		?>
				</select>
			</td>
			<td valign="top">
				<input type="button" class="cer_button_face" value="Edit Rule" onclick="javascript:mailRuleEdit(this.form.post_rule_id);"><br>
				<input type="button" class="cer_button_face" value="Delete Rule" onclick="javascript:mailRuleDelete(this.form.post_rule_id);"><br>
				<br>
				<input type="button" class="cer_button_face" value="Move Up" onclick="javascript: moveUp(this.form.post_rule_id); saveListState(this.form.post_rule_id,this.form.post_rules_ordered);"><br>
				<input type="button" class="cer_button_face" value="Move Down" onclick="javascript: moveDown(this.form.post_rule_id); saveListState(this.form.post_rule_id,this.form.post_rules_ordered);"><br>
			</td>
		</tr>
		</table>
		
    </td>
  </tr>
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SAVE_CHANGES; ?>">
		</td>
	</tr>  
  
</table>
</form>
<br>
