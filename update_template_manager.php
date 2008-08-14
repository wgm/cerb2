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
| File: update_template_manager.php
|
| Purpose: Add/Edit/Delete E-mail Templates
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

@$form_submit = $_REQUEST["form_submit"];
@$action = $_REQUEST["action"];
@$ticket_id = $_REQUEST["ticket_id"];
@$tid = $_REQUEST["tid"];
@$template_name = addslashes($_REQUEST["template_name"]);
@$template_desc = addslashes($_REQUEST["template_desc"]);
@$template_text = addslashes($_REQUEST["template_text"]);

$templ = new cer_template_struct;

if(!empty($form_submit))
{
	if(empty($tid)) // inserting
  	{
    	$sql = "INSERT INTO email_templates(template_name,template_description,template_text) " .
      	"VALUES	('$template_name','$template_desc','$template_text')";
			$cerberus_db->query($sql);
    }
  else // updating
		{
    	$sql = "UPDATE email_templates SET template_name = '$template_name', template_description='$template_desc', template_text='$template_text' WHERE template_id = $tid";
			$cerberus_db->query($sql);
    }

meta_redirect(cer_href("update_show_templates.php?ticket_id=$ticket_id"),2);
echo "Redirecting...";
exit;
}

if(!empty($tid))
{
	$sql = "SELECT t.template_id,t.template_name,t.template_description,t.template_text FROM email_templates t WHERE t.template_id = $tid";
	$t_res = $cerberus_db->query($sql);
	if($cerberus_db->num_rows($t_res))
  	{
    	$t_row = $cerberus_db->fetch_row($t_res);
      $templ->template_id = $t_row["template_id"];
      $templ->template_name = $t_row["template_name"];
      $templ->template_description = $t_row["template_description"];
      $templ->template_text = $t_row["template_text"];
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
<body topmargin=5 leftmargin=5 marginheight=5 marginwidth=5>

<form action="update_template_manager.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">

<img src="logo.gif"><br>

<table border=0 cellspacing=2 cellpadding=2>
	<tr>
  	<td class="cer_maintable_heading">Template Name:</td>
  	<td><input type="text" name="template_name" maxchars="64" size="35" value="<?php echo cer_dbc($templ->template_name); ?>"></td>
  </tr>
  <tr>
  	<td class="cer_maintable_heading">Template Description:</td>
  	<td><input type="text" name="template_desc" maxchars="200" size="45" value="<?php echo cer_dbc($templ->template_description); ?>"></td>
  </tr>
</table>

<textarea name="template_text" cols="70" rows="10"><?php echo cer_dbc($templ->template_text); ?></textarea>
<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>"><br>
<input type="hidden" name="tid" value="<?php echo @$tid; ?>">
<input type="hidden" name="form_submit" value="true">
<input type="submit" value="Submit" class="cer_button_face">
<input type="button" value="Cancel" class="cer_button_face" OnClick="javascript:window.close();">

<br><br><?php include "includes/elements/config_email_template_field_matrix.php"; ?>

</form>

</body>
</html>
