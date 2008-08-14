<html>
<head>
<title>{$smarty.const.LANG_HTML_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma-directive" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Directive" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">
{include file="cerberus.css.tpl.php"}
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css" type="text/css">
</head>

<body bgcolor="#FFFFFF">

<table width="100%" border="0" cellspacing="0" cellpadding="1">
	<tr>
		<td align="center"><img src="logo.gif"></td>
	</tr>
</table>
<br>

{if !empty($pemail)}<span class="cer_configuration_updated">{$smarty.const.LANG_LOGIN_PW_SENT}</span><br>{/if}
{if !empty($pemail_verify)}<span class="cer_configuration_updated">{$smarty.const.LANG_LOGIN_PW_VERIFY}</span><br>{/if}
<table width="400" border="0" cellspacing="0" cellpadding="2" align="center" style="border: 1px solid #BBBBBB;">
  <form name="loginForm_lost1" method="post" action="do_login.php">
  <input type="hidden" name="form_submit" value="email">
  <tr class="boxtitle_green_glass"> 
  	<td colspan="2">{$smarty.const.LANG_LOGIN_PW_LOST1}</td>
  </tr>
  <tr>
    <td colspan="2" valign="top" align="left" bgcolor="#DDDDDD"> 
        <table width="100%" border="0" cellspacing="2" cellpadding="0">
          <tr> 
            <td width="1%" nowrap class="cer_maintable_heading">E-mail Address:</td>
            <td width="99%"><input type="text" name="email_address" size="30" style="width:100%;" maxlength="128"></td>
          </tr>
          <tr>
          	<td colspan="2" align="right"><input type="submit" class="cer_button_face" value="{$smarty.const.LANG_LOGIN_PW_LOST_SEND}"></td>
          </tr>
        </table>
    </td>
  </form>
  </tr>
</table>
<br>

<table width="400" border="0" cellspacing="0" cellpadding="2" align="center" style="border: 1px solid #BBBBBB;">
  <form name="loginForm_lost2" method="post" action="do_login.php">
  <input type="hidden" name="form_submit" value="email_verify">
  <tr class="boxtitle_blue_glass"> 
  	<td colspan="2">{$smarty.const.LANG_LOGIN_PW_LOST2}</td>
  </tr>
  <tr>
    <td width="1%" nowrap class="cer_maintable_heading" bgcolor="#DDDDDD">{$smarty.const.LANG_LOGIN_PW_LOST2_IE}:</td>
    <td width="99%" nowrap align="left" bgcolor="#DDDDDD">
    	<input type="text" name="email_verify" size="9" maxlength="12">
    	<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_BUTTON_SUBMIT}">
    </td>
  </tr>
  </form>
</table>
<br>

<div align="center"><a href="login.php" class="cer_footer_text">Return to Login Form</a></div>

{include file="footer.tpl.php"}
</body>
</html>