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
| File: ticket_view_edit.php
|
| Purpose: Pop-up to create, edit and delete ticket views for use on the
| 	Home page ticket lists.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

define("MAX_VIEW_COLUMNS",15);

log_user_who_action(WHO_CONFIG_VIEWS);

@$vid = $_REQUEST["vid"];
@$form_submit = $_REQUEST["form_submit"];
@$action = $_REQUEST["action"];
@$view_name = $_REQUEST["view_name"];
@$qids = $_REQUEST["qids"];
@$column = $_REQUEST["column"];
@$hide_statuses = $_REQUEST["hide_statuses"];
@$view_only_assigned = $_REQUEST["view_only_assigned"];
@$view_adv_2line = $_REQUEST["view_adv_2line"];
@$view_adv_controls = $_REQUEST["view_adv_controls"];
@$slot = $_REQUEST["slot"];
@$view = $_REQUEST["view"];
@$page = $_REQUEST["page"];

// [JAS]: Misc Page Args
@$ticket = $_REQUEST["ticket"];
@$mode = $_REQUEST["mode"];

$cer_trans = new cer_translate;

if(empty($page)) $page = "index.php";
if(empty($slot)) $slot = "uv";

if(!empty($action))
{
	switch($action)
	{
		case "delete":
		{
			if($priv->has_priv(ACL_VIEWS_DELETE,BITGROUP_2) && !empty($vid))
			{
				$sql = "DELETE FROM ticket_views WHERE view_id = $vid";
				$cerberus_db->query($sql);
				echo "<script>window.opener.document.location='index.php?".$slot."=&sid=".$session->session_id."'; window.close();</script>";
			}
			exit;
			break;
		}
	}
}

if(!empty($form_submit))
{
	if(!empty($qids)) $qids = implode(",",$qids); else $qids = "";
	if(empty($view_only_assigned)) $view_only_assigned = 0;
	if(empty($view_adv_2line)) $view_adv_2line = 0;
	if(empty($view_adv_controls)) $view_adv_controls = 0;
	
	$c_column = array();
	foreach($column as $col)
	{
		if(!empty($col))
		$c_column[count($c_column)] = $col;
	}
	
	$columns = implode(",",$c_column);
	
	$c_hide_statuses = array();
	if(count($hide_statuses))
	{
		foreach($hide_statuses as $status)
		{
			if(!empty($status))
			$c_hide_statuses[count($c_hide_statuses)] = $status;
		}
		$hide_statuses = implode(",",$c_hide_statuses);
	}
	else { $hide_statuses = ""; }
	
	if(empty($vid)) // insert
	{
		$sql = "INSERT INTO ticket_views (view_name,view_queues,view_columns,view_hide_statuses,view_created_by_id,view_only_assigned,view_adv_2line,view_adv_controls) " .
		"VALUES ('".addslashes($view_name)."','$qids','$columns','$hide_statuses',".$session->vars["login_handler"]->user_id.",$view_only_assigned,$view_adv_2line,$view_adv_controls);";
		$cerberus_db->query($sql);
		$vid = $cerberus_db->insert_id();
	}
	else // update
	{
		$sql = "UPDATE ticket_views SET view_name = '" . addslashes($view_name) . "',view_queues='".
		$qids."',view_columns='".$columns."',view_hide_statuses='".$hide_statuses."',view_only_assigned=$view_only_assigned,".
		"view_adv_2line=".$view_adv_2line.",view_adv_controls=".$view_adv_controls.
		" WHERE view_id = $vid";
		$cerberus_db->query($sql);
	}
	
 			?>
      <html>
      <head>
      <script>
      function doLoadup()
      {
      	url = "<?php
      	echo sprintf("%s?%s=%d%s%s&sid=%s&ck=%d",
      	$page,
      	$slot,
      	$vid,
      	((!empty($ticket))?"&ticket=$ticket":""),
      	((!empty($mode))?"&mode=$mode":""),
      	$session->session_id,
      	rand(100,999)
      	);
      	?>";
      	window.opener.document.location=url;
      	window.close();
      }
      </script>
      </head>
      <body OnLoad="doLoadup();"></body>
      </html>
      <?php
      exit;
}

if(!empty($vid))
{
	$sql = "SELECT v.view_id,v.view_name,v.view_private,v.view_queues,v.view_columns,v.view_hide_statuses,v.view_only_assigned,v.view_adv_2line,v.view_adv_controls FROM ticket_views v WHERE v.view_id = $vid";
	$v_res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($v_res)) {
		$v_row = $cerberus_db->fetch_row($v_res);
		$q_ary = explode(",",$v_row["view_queues"]);
		$c_ary = explode(",",$v_row["view_columns"]);
		$s_ary = explode(",",$v_row["view_hide_statuses"]);
	}
}
?>
<html>
<head>
<title><?php echo LANG_HTML_TITLE; ?></title>
<style>
<?php require("cerberus.css"); ?>
</style>
</head>
<body>
<img src="logo.gif"><br><br>
<span class="cer_display_header">Ticket Views</span><br>
<form name="view_manager" method="post" onSubmit="return checkViewName();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">

<input type="hidden" name="vid" value="<?php echo $vid; ?>">
<input type="hidden" name="slot" value="<?php echo $slot; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="ticket" value="<?php echo $ticket; ?>">
<input type="hidden" name="form_submit" value="x">

<span class="cer_maintable_heading">View Name:</span><br>
<input type="text" name="view_name" maxchars="64" size="35" value="<?php echo cer_dbc(@$v_row["view_name"]); ?>"><br>
<br>
<span class="cer_maintable_text">
<input type="radio" name="view_only_assigned" value="0" <?php if(@$v_row["view_only_assigned"]==0) echo "CHECKED"; ?>>Show Tickets Assigned to Anyone in View<br>
<input type="radio" name="view_only_assigned" value="1" <?php if(@$v_row["view_only_assigned"]==1) echo "CHECKED"; ?>>Only Show Tickets Assigned to Me in View<br>
<input type="radio" name="view_only_assigned" value="2" <?php if(@$v_row["view_only_assigned"]==2) echo "CHECKED"; ?>>Only Show Unassigned Tickets in View<br>
</span>
<br>
<span class="cer_maintable_heading">View Advanced Options:</span><br>
<span class="cer_maintable_text">
<input type="checkbox" name="view_adv_2line" value="1" <?php if(@$v_row["view_adv_2line"]==1 || empty($vid)) echo "CHECKED"; ?>> Display ticket subject on its own line <span class="cer_footer_text">(space for additional columns. <B>recommended</B>)</span><br>
<input type="checkbox" name="view_adv_controls" value="1 "<?php if(@$v_row["view_adv_controls"]==1 || empty($vid)) echo "CHECKED"; ?>> Show batch action controls <span class="cer_footer_text">(checkboxes for mass change owner, status, etc. <b>recommended</b>)</span><br>
</span>
<br>

<span class="cer_maintable_heading">Select Queues to Display in View:</span><br>
<?php
$u_queues = &$queue_access->queue_access_list;

if(!empty($u_queues))
{
	echo "<table>";
	$rows=0;
	foreach($u_queues as $idx => $q)
	{
		if($rows % 2 == 0) echo "</tr><tr>";
		echo "<td class=\"cer_maintable_text\"><input type=\"checkbox\" name=\"qids[]\" value=\"" . @$q->queue_id . "\"";
		if(!empty($vid) && (count($q_ary) > 0)) if(is_numeric(array_search($q->queue_id,$q_ary))) { echo " CHECKED"; }
		echo ">" . $q->queue_name . "</td>";
		$rows++;
	}
	if($rows % 2 != 0) echo "<td>&nbsp;</td></tr>"; else echo "</tr>";
	echo "</table>";
}
?>
<br>
<span class="cer_maintable_heading">HIDE These Statuses (will not display in view):</span><br>
<?php
echo "<table>";
$rows=0;

foreach($status_options as $status)
{
	if($rows % 2 == 0) echo "</tr><tr>";
	echo "<td class=\"cer_maintable_text\"><input type=\"checkbox\" name=\"hide_statuses[]\" value=\"" . $status . "\"";
	if(!empty($vid) && (count($s_ary) > 0)) { if(is_numeric(array_search($status,$s_ary))) { echo " CHECKED"; } }
	else if(empty($vid) && ($status=="resolved" || $status=="dead")) { echo " CHECKED"; }
	echo ">" . $cer_trans->translate_status($status) . "</td>";
	$rows++;
}

if($rows % 2 != 0) echo "<td>&nbsp;</td></tr>"; else echo "</tr>";
echo "</table>";
?>
<br>
<span class="cer_maintable_heading">Select Columns (in order of display):</span><br>
<br>
<?php
// [JAS]: Custom Field Groups
$handler = new cer_CustomFieldGroupHandler();
$handler->loadGroupTemplates();

for($x=0;$x<MAX_VIEW_COLUMNS;$x++)
{
	$col = $x+1; // [JAS]: Turn array elements into base 10
	echo '<span class="cer_maintable_heading">'.sprintf("%02d",$col).'</span>: <select name="column[]">';
	echo ''.
	'<option value=""'.((@$c_ary[$x]=="")?" SELECTED":"").'>-none-'.
	'<option value="ticket_id"'.((@$c_ary[$x]=="ticket_id")?" SELECTED":"").'>Ticket ID'.
	'<option value="ticket_subject"'.((@$c_ary[$x]=="ticket_subject")?" SELECTED":"").'>Ticket Subject'.
	'<option value="queue_name"'.((@$c_ary[$x]=="queue_name")?" SELECTED":"").'>Queue Name'.
	'<option value="ticket_status"'.((@$c_ary[$x]=="ticket_status")?" SELECTED":"").'>Ticket Status'.
	'<option value="ticket_due"'.((@$c_ary[$x]=="ticket_due")?" SELECTED":"").'>Ticket Due Date'.
	'<option value="thread_date"'.((@$c_ary[$x]=="thread_date")?" SELECTED":"").'>Ticket Last Activity Date'.
	'<option value="thread_received"'.((@$c_ary[$x]=="thread_received")?" SELECTED":"").'>Ticket Created Date'.
	'<option value="ticket_owner"'.((@$c_ary[$x]=="ticket_owner")?" SELECTED":"").'>Ticket Owner'.
	'<option value="ticket_priority"'.((@$c_ary[$x]=="ticket_priority")?" SELECTED":"").'>Ticket Priority'.
	'<option value="address_address"'.((@$c_ary[$x]=="address_address")?" SELECTED":"").'>Last Wrote Address'.
	'<option value="requestor_address"'.((@$c_ary[$x]=="requestor_address")?" SELECTED":"").'>Requester Address'.
	'<option value="company_name"'.((@$c_ary[$x]=="company_name")?" SELECTED":"").'>Company Name'.
	'<option value="total_time_worked"'.((@$c_ary[$x]=="total_time_worked")?" SELECTED":"").'>Ticket Total Time Worked'.
	'<option value="spam_probability"'.((@$c_ary[$x]=="spam_probability")?" SELECTED":"").'>Spam Probabilty'.
	'<option value="ticket_spam_trained"'.((@$c_ary[$x]=="ticket_spam_trained")?" SELECTED":"").'>Spam Trained (Spam/Not)';
	
	// [JAS]: List Requester Custom Fields
	if(!empty($handler->group_templates))
	foreach($handler->group_templates as $group)
	{
		if(!empty($group->fields))
		foreach($group->fields as $field) {
			$field_id = $field->field_id;
			$field_name = $field->field_name;
			echo sprintf("<option value=\"g_%d_custom_%d\" %s> %s: %s",
					$group->group_id,
					$field_id,
					((@$c_ary[$x]=='g_' . $group->group_id . '_custom_'.$field_id) ? ' SELECTED' : ''),
					@htmlspecialchars($group->group_name, ENT_QUOTES, LANG_CHARSET_CODE),
					@htmlspecialchars($field_name, ENT_QUOTES, LANG_CHARSET_CODE)
				);
		}
	}
	
	echo '</select><br>';
	echo "<br>";
}
?>
<br>
<br>
<script>
function doViewDelete()
{
	if(confirm("Are you sure you want to delete this ticket view?"))
	{ document.location='ticket_view_edit.php?action=delete&vid=<?php echo $vid ."&sid=".$session->session_id; ?>'; }
}


// [TAR]: View Name Required onSubmit.
function checkViewName(){
	if (document.view_manager.view_name.value == "") {
		alert('View Name is Required');
		document.view_manager.view_name.focus();
		return false;
	} 
	else {
		return true;
	}	
}

</script>
<?php if($priv->has_priv(ACL_VIEWS_EDIT,BITGROUP_2)) { ?><input type="submit" value="Submit" class="cer_button_face"><?php } if($priv->has_priv(ACL_VIEWS_DELETE,BITGROUP_2) && !empty($vid)) { ?><input type="button" value="Delete View" class="cer_button_face" OnClick="javascript:doViewDelete();"><?php } ?><input type="button" value="Cancel" class="cer_button_face" OnClick="javascript:window.close();">
</form>
</body>
</html>
