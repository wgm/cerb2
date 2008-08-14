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

{literal}
<script>
function focusLogin() {
	document.loginForm.CER_AUTH_USER.focus();
}
</script>
{/literal}

</head>

<body bgcolor="#FFFFFF" onload="javascript:focusLogin();">

<table width="100%" border="0" cellspacing="0" cellpadding="1">
	<tr>
		<td align="center"><img src="logo.gif"></td>
	</tr>
</table>
<br>


{if !empty($failed)}
<table width="400" border="0" cellspacing="0" cellpadding="2" align="center">
	<tr>
		<td><span class="cer_configuration_updated">{$smarty.const.LANG_LOGIN_FAILED}</span></td>
	</tr>
</table>
{/if}

<table width="400" border="0" cellspacing="0" cellpadding="2" align="center" style="border: 1px solid #BBBBBB;">
  <form name="loginForm" method="post" action="do_login.php">
  <input type="hidden" name="form_submit" value="login">
  <tr class="boxtitle_orange_glass"> 
  	<td colspan="2">{$smarty.const.LANG_LOGIN_LOGIN}</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
  	<td width="1%" nowrap align="right"  class="cer_maintable_heading">{$smarty.const.LANG_LOGIN_LOGIN}: </td>
  	<td width="99%" align="left" nowrap><input type="text" name="CER_AUTH_USER" size="30" maxlength="64" style="width: 100%;"></td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
  	<td width="1%" nowrap align="right" class="cer_maintable_heading">{$smarty.const.LANG_LOGIN_PW}: </td>
  	<td width="99%" align="left" nowrap><input type="password" name="CER_AUTH_PASS" size="30" maxlength="64" style="width: 100%;"></td>
  </tr>
  {*
  <tr bgcolor="#DDDDDD"> 
  	<td width="1%" nowrap align="left"></td>
  	<td width="99%" align="left" class="cer_footer_text"><input type="checkbox" name="CER_AUTH_AUTOLOGIN" value="1">
  	Automatically log me in from this computer
  	</td>
  </tr>
  *}
  <tr bgcolor="#DDDDDD">
  	<td colspan="2">
  	 	<table width="100%" cellpadding="0" cellspacing="0">
  	 		<tr>
				<td width="1%" nowrap align="left"><a href="forgot_pw.php" class="cer_footer_text">{$smarty.const.LANG_LOGIN_PW_LOST1}</a></td>
		  		<td width="99%" align="right" nowrap><input type="submit" class="cer_button_face" value="{$smarty.const.LANG_LOGIN_SUBMIT}"></td>
		  	</tr>
		</table>
  	</td>
  </tr>
  </form>
</table>  

{include file="footer.tpl.php"}
</body>
</html>