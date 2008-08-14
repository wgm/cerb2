<?php

session_name("config_xml_generator");
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
  for(e=0;e<document.configxmlgen_form.elements.length;e++) {
	if(document.configxmlgen_form.elements[e].type == "radio") {
	   document.configxmlgen_form.elements[e].checked = 1;
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
<span class="cer_display_header">Cerberus Helpdesk: config.xml Generator</span><br>
<br>
<table border="0" cellspacing="0" cellpadding="0">
<form action="index.php" method="POST" name="configxmlgen_form">
<input type="hidden" name="form_submit" value="go_next_step">
<?php

switch($step) {
   case '0': {
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; What mode are you using for the parser:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td align="center" valign="middle">
<input type="radio" name="parser_mode" value="pop3">
</td>
<td valign="top">
<span class="cer_maintable_heading">&nbsp;POP3 Mode</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Used via automated cron job to check a pop3 mailbox&nbsp;</span><br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td align="center" valign="middle">
<input type="radio" name="parser_mode" value="pipe">
</td>
<td valign="top">
<span class="cer_maintable_heading">&nbsp;Piping Mode</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Advanced Mode - Pipe email straight from mail server in real-time&nbsp;</span><br>
</td>
</tr>
<?php
     $next_step = 1;
     break;
   }
   case "1": {
      $_SESSION["PARSER_MODE"] = (isset($_REQUEST["parser_mode"]) && $_REQUEST["parser_mode"] == "pipe") ? "pipe" : "pop3";
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; What is your product license key:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td colspan=2>
&nbsp;<textarea cols="50" rows="5" name="product_key"></textarea>&nbsp;
</td>
</tr>
<?php
      $next_step = 2;
      break;
   }
   case "2": {
      $_SESSION["PRODUCT_KEY"] = isset($_REQUEST["product_key"]) ? $_REQUEST["product_key"] : "[** REPLACE THIS LINE WITH YOUR PRODUCT KEY **]";
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; Parser Configuration Information:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td colspan=2>
<span class="cer_maintable_heading">&nbsp;The defaults usually work for most installations</span>&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;Helpdesk URL:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>The URL to your Helpdesk Installation&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="parser_url" value="http://<?php echo $_SERVER["SERVER_NAME"]; echo 
str_replace("/install/configxml", 
"", dirname($_SERVER["PHP_SELF"])); ?>/" size="40">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;libcurl Path:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Set this to the location of your own 
libcurl compiled library&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="libcurl_path" value="./libcurl.so.2">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;Secure Mode Username:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Advanced Setting - Username from configuration area&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="parser_user" value="name">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;Secure Mode Password:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Advanced Setting - Password from configuration area&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="parser_pass" value="password">&nbsp;<br>
</td>
</tr>
<?php
      $next_step = 3;
      break;
   }
   case "3": {
      $_SESSION["PARSER_URL"] = isset($_REQUEST["parser_url"]) ? $_REQUEST["parser_url"] : "";
      $_SESSION["LIBCURL_PATH"] = isset($_REQUEST["libcurl_path"]) ? $_REQUEST["libcurl_path"] : "";
      $_SESSION["PARSER_USER"] = isset($_REQUEST["parser_user"]) ? $_REQUEST["parser_user"] : "";
      $_SESSION["PARSER_PASS"] = isset($_REQUEST["parser_pass"]) ? $_REQUEST["parser_pass"] : "";
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; Additional Parser Configuration Information:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;Temporary File Folder:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Usually /tmp/ on *nix or c:\temp\ on Windows&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="tmp_path" value="<?php echo (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "c:\\temp\\" : 
"/tmp/"; ?>">&nbsp;<br>
</td>
</tr>
<?php

if(isset($_SESSION["PARSER_MODE"]) && $_SESSION["PARSER_MODE"] == "pop3") {

?>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;Maximum emails to pull per pop3 session:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Set this to the maximum pop3 emails to pull per pop3 
session&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="max_pop3" value="10">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;POP3 Timeout:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Usage: </b>Maximum amount of time (in seconds) to wait for the POP3 
server.&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="pop3_timeout" value="15">&nbsp;<br>
</td>
</tr>
<?php
}

      if(isset($_SESSION["PARSER_MODE"]) && $_SESSION["PARSER_MODE"] == "pop3") {
         $next_step = 4;
      }
      else {
         $next_step = 6;
      }
      break;
   }
   case "4": {
      $_SESSION["TMP_PATH"] = isset($_REQUEST["tmp_path"]) ? $_REQUEST["tmp_path"] : "";
      $_SESSION["MAX_POP3"] = isset($_REQUEST["max_pop3"]) ? $_REQUEST["max_pop3"] : "10";
      $_SESSION["POP3_TIMEOUT"] = isset($_REQUEST["pop3_timeout"]) ? $_REQUEST["pop3_timeout"] : "15";
?>
<tr>
<td colspan="2" bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; POP3 Server Information:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;POP3 Server Address:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>mail.yourdomain.com&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="pop3_host" value="">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;POP3 Username:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>support@yourdomain.com&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="pop3_user" value="">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;POP3 Password:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>your_password&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="pop3_pass" value="">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;POP3 Port:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>Usually always 110&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<input type="text" name="pop3_port" value="110">&nbsp;<br>
</td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="../../includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle">
<span class="cer_maintable_heading">&nbsp;Delete Parsed Emails:</span>&nbsp;<br>
<span class="cer_footer_text">&nbsp;<b>Ex: </b>true/false&nbsp;</span><br>
</td>
<td valign="middle">
&nbsp;<select name="pop3_delete">
<option value="true">True</option>
<option value="false">False</option>
</select>
&nbsp;<br>
</td>
</tr>

<?php
         $next_step = 5;
         break;
      }
   case "5": {
      $_SESSION["POP3_HOST"] = isset($_REQUEST["pop3_host"]) ? $_REQUEST["pop3_host"] : "";
      $_SESSION["POP3_USER"] = isset($_REQUEST["pop3_user"]) ? $_REQUEST["pop3_user"] : "";
      $_SESSION["POP3_PASS"] = isset($_REQUEST["pop3_pass"]) ? $_REQUEST["pop3_pass"] : "";
      $_SESSION["POP3_PORT"] = isset($_REQUEST["pop3_port"]) ? $_REQUEST["pop3_port"] : "";
      $_SESSION["POP3_DELETE"] = isset($_REQUEST["pop3_delete"]) ? $_REQUEST["pop3_delete"] : "";
      $step = $next_step = "xml";
      break;
   } 
   case "6": {
      $_SESSION["TMP_PATH"] = isset($_REQUEST["tmp_path"]) ? $_REQUEST["tmp_path"] : "";
      $_SESSION["MAX_POP3"] = isset($_REQUEST["max_pop3"]) ? $_REQUEST["max_pop3"] : "10";
      $_SESSION["POP3_TIMEOUT"] = isset($_REQUEST["pop3_timeout"]) ? $_REQUEST["pop3_timeout"] : "15";
      $step = $next_step = "xml";
      break;
   }
}     

if($step !== "xml") {
?>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#FFFFFF"> 
<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="Continue to Next Step"></td>
</tr>
<?php
}
else {
if(isset($confirm) && $confirm == 1) {
?>
<tr> 
<td bgcolor="#0099FF">
<span class="cer_maintable_header">&nbsp; Cut and paste the below into your config.xml file:</span>
</td>
</tr>
<tr bgcolor="#d0d0d0"><td>
<textarea name="config" cols="80" rows="20">
<?php

$main = file_get_contents("./templates/main.template.txt");
$pop3 = file_get_contents("./templates/pop3.template.txt");

$replace = array();
$search = array();

if(is_array($_SESSION)) {
   foreach($_SESSION as $key=>$value) {
      $search[] = "###" . $key . "###";
      $replace[] = $value;
   }
}

$search[] = "###POP3###";
if(isset($_SESSION["PARSER_MODE"]) && $_SESSION["PARSER_MODE"] == "pop3") {
  $replace[] = str_replace($search, $replace, $pop3);
}
else {
  $replace[] = "";
}

echo str_replace($search, $replace, $main); 
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

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Parser Mode:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["PARSER_MODE"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Product Key:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["PRODUCT_KEY"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Helpdesk URL:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["PARSER_URL"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;libcurl Path:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["LIBCURL_PATH"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Secure Mode Username:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["PARSER_USER"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Secure Mode Password:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["PARSER_PASS"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Temp Folder:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["TMP_PATH"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<?php

if(isset($_SESSION["PARSER_MODE"]) && $_SESSION["PARSER_MODE"] == "pop3") {

?>
<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Maximum POP3's emails per session:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["MAX_POP3"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;POP3 Timeout:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["POP3_TIMEOUT"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>

<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;POP3 Server Address:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["POP3_HOST"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;POP3 Username:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["POP3_USER"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;POP3 Password:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["POP3_PASS"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;POP3 Port:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["POP3_PORT"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>

<tr bgcolor="#d0d0d0">
<td valign="middle"><span class="cer_maintable_heading">&nbsp;Delete Mail after Parsing:</span>&nbsp;<br></td>
<td valign="middle"><span class="cer_footer_text">&nbsp;<b><?php echo $_SESSION["POP3_DELETE"]; 
?></b>&nbsp;</span>&nbsp;<br></td>
</tr>
<tr><td colspan="2" bgcolor="#FFFFFF"><img src="includes/images/spacer.gif" width="1" height="1"></td></tr>
<?php

}

?>
<input type="hidden" name="confirm" value="1">
<tr bgcolor="#FFFFFF"> 
<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="Generate config.xml"></td>
</tr>
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
    <td valign="bottom" align="left" class="cer_footer_text">Cerberus Helpdesk is a trademark of WebGroup Media LLC (TM) - 
Version 2.5.1 Release<br>
    </td>
    <td valign="middle" align="right" class="cer_footer_text">powered by<img src="../../cer_inctr_logo_sm.gif" width="98" 
height="44" align="bottom"></td>
  </tr>
</table></body>
</html>

