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
| File: update_show_templates.php
|
| Purpose: Inside a pop-up window the e-mail templates that have been defined
|   for the system are shown.  When one is chosen it's fields are parsed and 
|		the post-parsed text is sent to the calling window's email textbox.
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

require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/email_templates/cer_email_templates.class.php");

@$ticket_id = $_REQUEST["ticket_id"];
@$form_submit = $_REQUEST["form_submit"];
@$action = $_REQUEST["action"];
@$tid = $_REQUEST["tid"];
@$tid_mode = $_REQUEST["tid_mode"];

if(!empty($action))
{
	switch($action)
  	{
    case "delete":
    	{
		$sql = "DELETE FROM email_templates WHERE template_id = $tid";
		$cerberus_db->query($sql);      
		meta_redirect(cer_href("update_show_templates.php?ticket_id=$ticket_id"),2);
		echo "Redirecting...";
			exit;
		break;
      }
    }
}

if(!empty($form_submit))
{
	// [JAS]: Form was submitted.  We need to send select the template from the 
	//	database, parse it and send it to the window.opener textbox.

	$email_templates = new CER_EMAIL_TEMPLATES();
    $email = $email_templates->parse_canned_template($tid,$ticket_id);
    ?>
      <html>
      <head>
      <script>
      	function doLoadup()
        	{
            <?php
            $email = addslashes($email);
            $email = str_replace("\r\n","\\r\\n",$email);
            
            // [JAS]: If we have a '<' in the content, break it into a substring to prevent the browser parsing it.
            ?>
            template = '<?php echo str_replace("<","' + '<' + '",$email); ?>';
            
			<?php
			switch($tid_mode)
			{
				
				case "replace":
					echo "window.opener.document.frmTicketUpdate.ticket_response.value = template + '\\r\\n';";
					break;
				default:
				case "prepend":
					echo "window.opener.document.frmTicketUpdate.ticket_response.value = '\\r\\n' + template + '\\r\\n' + window.opener.document.frmTicketUpdate.ticket_response.value;";
					break;
				case "append":
					echo "window.opener.document.frmTicketUpdate.ticket_response.value = window.opener.document.frmTicketUpdate.ticket_response.value + '\\r\\n' + template + '\\r\\n';";
					break;
			}
			?>
 
			window.close();   
          }
      </script>
      </head>
      <body OnLoad="doLoadup();"></body>
      </html>
      <?php
  
  exit;
}

$sql = "SELECT t.template_id, t.template_name, t.template_description from email_templates t ORDER BY t.template_name;";
$t_res = $cerberus_db->query($sql);
?>
<html>
<head>
<title><?php echo LANG_HTML_TITLE; ?></title>
<style>
<?php require("cerberus.css"); ?>
</style>
<script>
sid = "sid=<?php echo $session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

	function formatURL(url)
  	{
	  if(show_sid) { url = url + "&" + sid; }
      return(url);
    }
  
  function templateEdit(tids)
  	{
      if(tids.length) {
      for(x=0;x<tids.length;x++)
      	{
        	if(tids[x].checked) {
          	tid = tids[x].value;
            }
        }
      }
      else
      	{ tid = tids.value; } 
    
    	document.location = formatURL('update_template_manager.php?ticket_id=<?php echo $ticket_id; ?>&tid=' + tid);
    }
  
  function templateDelete(tids)
  	{
      if(tids.length) { 
      for(x=0;x<tids.length;x++)
      	{
        	if(tids[x].checked) {
          		tid = tids[x].value;
            }
        }
      }
      else
      	{ tid = tids.value; }
        
      if(confirm("Are you sure you want to delete this template?"))
      	{
			document.location = formatURL('update_show_templates.php?ticket_id=<?php echo $ticket_id; ?>&action=delete&tid=' + tid);
        }
    }
</script>
</head>
<body>
<img src="logo.gif"><br><br>
<span class="cer_display_header">E-mail Templates</span><br>
<span class="cer_maintable_text">
The template you select will be automatically appended to the end of the e-mail you 
are currently sending.
</span>
<br>

<form name="template_manager" method="post">
<input type="hidden" name="form_submit" value="use">
<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">

<table width="100%" border="0" cellpadding="2" cellspacing="1">
	<tr class="cer_home_templates_background">
  	<td class="cer_maintable_header" width="5%"></td>
    <td class="cer_maintable_header" width="45%">Template Name</td>
    <td class="cer_maintable_header" width="50%">Description</td>
  </tr>
<?php
if($cerberus_db->num_rows($t_res))
	{
  $row=0;
  while($t_row = $cerberus_db->fetch_row($t_res))
  	{
  	if($row % 2 == 0) $bgc = "cer_maintable_text_1"; else $bgc = "cer_maintable_text_2";
    ?>
	<tr class="<?php echo $bgc; ?>">
  	<td align="center"><input type="radio" name="tid" value="<?php echo $t_row["template_id"]; ?>" <?php if($row==0) echo "CHECKED"; ?>></td>
    <td><span class="cer_maintable_headingSM"><?php echo cer_dbc($t_row["template_name"]) ; ?></span></td>
    <td><span class="cer_footer_text"><?php echo cer_dbc($t_row["template_description"]) ; ?></span></td>
  </tr>
		<?php
  	$row++;
    }
  }
else
	{
  	echo "<tr><td colspan=3 class='cer_maintable_text'>No e-mail templates are defined.</td></tr>";
  }
?>  
</table>

<span class="cer_maintable_heading">Use Template:</span> 
<span class="cer_footer_text">
<input type="radio" name="tid_mode" value="replace"> Replacing Existing Email 
<input type="radio" name="tid_mode" value="append" checked> Append to Email
<input type="radio" name="tid_mode" value="prepend"> Prepend to Email
</span>
<br>
<input type="submit" value="Use Selected Template" class="cer_button_face">
<br>
<br>
<span class="cer_maintable_heading">Template Actions:</span><br>
<input type="button" value="New Template" class="cer_button_face" OnClick="document.location=formatURL('update_template_manager.php?ticket_id=<?php echo $ticket_id; ?>');"><input type="button" value="Edit Template" class="cer_button_face" OnClick="javascript:templateEdit(this.form.tid);"><?php if($session->vars["login_handler"]->user_superuser != 0) { ?><input type="button" value="Delete Template" class="cer_button_face" OnClick="javascript:templateDelete(this.form.tid);"><?php } ?><input type="button" value="Cancel" class="cer_button_face" OnClick="javascript:window.close();">
</form>
</body>
</html>
