<?php

session_name("config_generator");
session_start();

$step = isset($_REQUEST["step"]) ? $_REQUEST["step"] : 0;
$confirm = (isset($_REQUEST["confirm"]) && $_REQUEST["confirm"] == 1) ? 1 : 0;
$next_step = $step;

error_reporting(error_reporting() & ~E_NOTICE); 

require_once(dirname(__FILE__) . '/../../includes/functions/compatibility.php');

?>
<html>
<head>
<title>Cerberus Helpdesk :: E-mail Management // Customer Relationship Management // Trouble Ticket System</title>
<style>
<!--
.cer_display_header { font-family: Arial, Helvetica, sans-serif; font-size: 14pt; font-weight: bold; color: #000000}
.cer_maintable_header { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none}
.cer_maintable_header2 { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none}
.cer_maintable_heading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #333333 }
.cer_footer_text { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: normal; color: #333333; }
.install_value_ok { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #009900 }
.install_value_fail { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #CC0000}
.cer_button_face { 
	font-family: 'Verdana', 'Arial', 'Helvetica', 'sans-serif'; 
	font-size: 8pt; 
	font-weight: normal; 
}

-->
</style>
<script>
<!--
function selectTopRadio() {
  for(e=0;e<document.configgen_form.elements.length;e++) {
	if(document.configgen_form.elements[e].type == "radio") {
	   document.configgen_form.elements[e].checked = 1;
	   break;
	}
  }
}
//-->
</script>
</head>
<body onload="javascript: selectTopRadio();">
<img src="../../logo.gif"><br>
<br>
<span class="cer_display_header">Cerberus Helpdesk: config.php Generator</span><br>
<br>
<table border="0" cellspacing="0" cellpadding="0">
<form action="index.php" method="POST" name="configgen_form">
<input type="hidden" name="form_submit" value="go_next_step">
<?php

switch($step) {
   case '0': {
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; Database Connection Information:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;DB Server Address:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>localhost&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="db_server" value="<?php echo $_SESSION["DB_SERVER"]; ?>">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;DB Username:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>cerberus&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="db_user" value="<?php echo $_SESSION["DB_USER"]; ?>">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;DB Password:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>your_db_password&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="db_pass" value="<?php echo $_SESSION["DB_PASS"]; ?>">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;DB Name:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>wgm_cerberus&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="db_name" value="<?php echo $_SESSION["DB_NAME"]; ?>">&nbsp;<br>
</td>
</tr>
<?php
     $next_step = 1;
     break;
   }
   case "1": {
      $_SESSION["DB_SERVER"] = isset($_REQUEST["db_server"]) ? $_REQUEST["db_server"] : "localhost";
      $_SESSION["DB_USER"] = isset($_REQUEST["db_user"]) ? $_REQUEST["db_user"] : "cerberus";
      $_SESSION["DB_PASS"] = isset($_REQUEST["db_pass"]) ? $_REQUEST["db_pass"] : "cerberus";
      $_SESSION["DB_NAME"] = isset($_REQUEST["db_name"]) ? $_REQUEST["db_name"] : "wgm_cerberus";
      $_SESSION["IP_1"] = (isset($_SESSION["IP_1"]) && !empty($_SESSION["IP_1"])) ? $_SESSION["IP_1"] : "0.0.0.0";
      $_SESSION["IP_2"] = (isset($_SESSION["IP_2"]) && !empty($_SESSION["IP_2"])) ? $_SESSION["IP_2"] : "0.0.0.0";
      $_SESSION["IP_3"] = (isset($_SESSION["IP_3"]) && !empty($_SESSION["IP_3"])) ? $_SESSION["IP_3"] : "0.0.0.0";
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; IP's allowed to access upgrade.php:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td colspan="2">&nbsp;<span class="cer_maintable_heading">Partial IP's are allowed</span></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td>&nbsp;Allowed IP #1:&nbsp;&nbsp;<input type="text" name="ip_1" value="<?php echo $_SESSION["IP_1"]; ?>"></td>
<td><input type="button" value="Use Your IP" onclick="javascript: document.configgen_form.ip_1.value = '<?php echo 
$_SERVER["REMOTE_ADDR"]; ?>';">
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td>&nbsp;Allowed IP #2:&nbsp;&nbsp;<input type="text" name="ip_2" value="<?php echo $_SESSION["IP_2"]; ?>"></td>
<td><input type="button" value="Use Your IP" onclick="javascript: document.configgen_form.ip_2.value = '<?php echo 
$_SERVER["REMOTE_ADDR"]; ?>';">
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td>&nbsp;Allowed IP #3:&nbsp;&nbsp;<input type="text" name="ip_3" value="<?php echo $_SESSION["IP_3"]; ?>"></td>
<td><input type="button" value="Use Your IP" onclick="javascript: document.configgen_form.ip_3.value = '<?php echo 
$_SERVER["REMOTE_ADDR"]; ?>';">
</td>
</tr>
<?php
      $next_step = 2;
      break;
   }
   case "2": {
      $_SESSION["IP_1"] = isset($_REQUEST["ip_1"]) ? $_REQUEST["ip_1"] : "0.0.0.0";
      $_SESSION["IP_2"] = isset($_REQUEST["ip_2"]) ? $_REQUEST["ip_2"] : "0.0.0.0";
      $_SESSION["IP_3"] = isset($_REQUEST["ip_3"]) ? $_REQUEST["ip_3"] : "0.0.0.0";
      $step = $next_step = "confirm";
      break;
   }
}     

if($step !== "confirm") {
?>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#FFFFFF"> 
<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="Continue to Next Step"></td>
</tr>
<?php
}
else {
if($confirm == 1) {
?>
<tr> 
<td bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; Cut and paste the below into your config.php file:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0"><td>
<textarea name="config" cols="80" rows="20">
<?php

$main = file_get_contents("./templates/siteconfig.template.txt");

$replace = array();
$search = array();

foreach($_SESSION as $key=>$value) {
   $search[] = "###" . $key . "###";
   $replace[] = $value;
}

echo htmlentities(str_replace($search, $replace, $main)); 
session_destroy();

?>
</textarea>
</td></tr>

<?php
}
else {
?>
<tr> 
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; Confirm Configuration:</span>
</td>
</tr>

<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;DB Server Address:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["DB_SERVER"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;DB Username:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["DB_USER"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;DB Password:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["DB_PASS"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;DB Name:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["DB_NAME"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Authorize IP #1:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["IP_1"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Authorize IP #2:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["IP_2"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Authorize IP #3:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["IP_3"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>


<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Checking Database Connection... :</span>&nbsp;<br></td>
<td valign="middle"><b>
<?php
$fatal = false;

$ivo = "<span class='install_value_ok'>";
$ivf = "<span class='install_value_fail'>";
$sc = "</span>";

@$db = mysql_connect($_SESSION["DB_SERVER"], $_SESSION["DB_USER"], $_SESSION["DB_PASS"]);
$db_error = mysql_error();
if(strlen($db_error)!=0) {
   echo $ivf.$db_error.$sc; $fatal=true;
}
if(strlen($db_error)==0) {
   @mysql_select_db($_SESSION["DB_NAME"]);
   $db_error = mysql_error();
   if(strlen($db_error)!=0) {
      echo $ivf.$db_error.$sc; $fatal=true;
   }
}

if(strlen($db_error)==0) {
   @$result = mysql_query("SHOW TABLES");
   $db_error = mysql_error();
   if(strlen($db_error)!=0) {
      echo $ivf.$db_error.$sc; $fatal=true;
   }
}

if($fatal) {
  $next_step = 0;
}
else {
  echo $ivo."Connection Successful.".$sc;
}

?>
</b>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<input type="hidden" name="confirm" value="1">
<tr bgcolor="#FFFFFF"> 
<?php
  if($fatal) {
?>
<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="Return to DB connection Page"></td>
</tr>
<?php
  } 
  else {
?>
<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="Generate config.php"></td>
</tr>
<?php
  }
?>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<?php
  }
}
?>
<input type="hidden" name="step" value="<?php echo $next_step;?>">
</form>
</table>

<br>
<a href="../index.php" class="cer_maintable_text">Return to Cerberus Helpdesk</a>

<br>
<table width="100%" border="0" cellspacing="0" align="left" cellpadding="0">
  <tr> 
    <td valign="bottom" align="left" class="cer_footer_text">Cerberus Helpdesk is a trademark of WebGroup Media LLC (TM)<br>
    </td>
    <td valign="middle" align="right" class="cer_footer_text">powered by<img src="../../cer_inctr_logo_sm.gif" width="98" 
height="44" align="bottom"></td>
  </tr>
</table></body>
</html>

