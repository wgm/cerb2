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

<script src="{$cfg->settings.http_server}{$cfg->settings.cerberus_gui_path}/includes/scripts/spellcheck.js"></script>
<script>
{literal}
function addAttachment(name)
{
	document.frmTicketUpdate.attachment_list.options[document.frmTicketUpdate.attachment_list.length] = new Option(name);
}

// [TAR]: Requester and Subject Line Required.
function checkRequester(){
	if (document.frmTicketUpdate.nt_requestor.value == "") {
		alert('Ticket Requester is Required');
		document.frmTicketUpdate.nt_requestor.focus();
		return false;
	} 
	else {
		return checkSubject();
	}	
}
function checkSubject(){
	if (document.frmTicketUpdate.nt_subject.value == "") {
		alert('Ticket Subject is Required');
		document.frmTicketUpdate.nt_subject.focus();
		return false;
	} 
	else {
		return true;
	}	
}

{/literal}
</script>
{* Reply Screen Actions Javascript (escaped) *}
<script>
<!--
{literal}
function doSpellCheck()
{
	document.spellform.spellstring.value = document.frmTicketUpdate.ticket_response.value;
	window.open("", "spellWindow", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=640,height=480');
	document.spellform.submit();
}
 {/literal}
//-->
</script>

{include file="keyboard_shortcuts_jscript.tpl.php}

</head>

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}
<br>
<form name="frmTicketUpdate" method="post" enctype="multipart/form-data"  onSubmit="return checkRequester();">
<input type="hidden" name="form_submit" value="x">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="qid" value="{$qid}">

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr> 
    <td colspan="2" valign="top" class="cer_display_header">{$smarty.const.LANG_CREATE_IN} {$header_readwrite_queues.$qid}</td>
  </tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="2" bordercolor="#A0A0A0">
  <tr class="boxtitle_blue_glass"> 
    <td width="100%">{$smarty.const.LANG_CREATE_TICKET}</td>
  </tr>
</table>

<table width="100%" border="1" cellspacing="0" cellpadding="2" bordercolor="#666666">
  <tr bgcolor="#C9C9C9"> 
    <td>
        <table cellpadding="2" cellspacing="0" border="0">
	    			<tr>
								<td width="25%">
										<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_QUEUE}:</span>
								</td>
								<td class="cer_maintable_text" width="75%">
									{$header_readwrite_queues.$qid}
								</td>
						</tr>
	    			<tr>
							<td width="25%">
								<span class="cer_maintable_heading">Queue Address:</span>
							</td>
							<td class="cer_maintable_text" width="75%">
								<select name="nt_to">
								  {html_options options=$queue_addresses}
								</select>
							</td>
						</tr>
	    			<tr>
							<td valign="top">
  		          <span class="cer_maintable_heading">{$smarty.const.LANG_DISPLAY_REQUESTOR}:</span>
							</td>
									<td class="cer_maintable_text">
											<input type="input" size="55" name="nt_requestor" value="{$session->vars.login_handler->user_email}"><br>
											<span class="cer_footer_text">{$smarty.const.LANG_CREATE_SENDER_INSTRUCTIONS}</span>
									</td>
							</tr>
	    			<tr>
									<td>
            <span class="cer_maintable_heading">{$smarty.const.LANG_UPDATE_CC}:</span>
									</td>
									<td class="cer_maintable_text">
											<input type="input" size="55" name="nt_cc"> <span class="cer_footer_text">{$smarty.const.LANG_CREATE_COMMA_DELIMITED}</span>
									</td>
							</tr>
	    			<tr>
									<td>
            <span class="cer_maintable_heading">{$smarty.const.LANG_WORD_SUBJECT}:</span>
									</td>
									<td class="cer_maintable_text">
											<input type="input" size="65" name="nt_subject">
									</td>
						</tr>
						</table>
        <span class="cer_maintable_heading">{$smarty.const.LANG_CREATE_DESCRIBE}: </span>&nbsp;&nbsp;
		{if $pspell_loaded === true}
			<input type="button" value="{$smarty.const.LANG_CREATE_SPELLCHECK}" OnClick="javascript:doSpellCheck();" class="cer_button_face">
		{else}
			<input type="button" value="{$smarty.const.LANG_CREATE_SPELLCHECK} via cerberusweb.com" OnClick="javascript:doSpellCheck();" class="cer_button_face">
		{/if}
		<br>

<textarea name="ticket_response" cols="80" rows="20" class="cer_display_emailText">{$user_prefs->user_signature}</textarea>
			<br>
			<span class="cer_maintable_text">
				<input type="checkbox" name="nt_suppress_autoreply" value="1">Don't send '{$header_readwrite_queues.$qid}' queue autoresponse to requester.<br>
				<input type="checkbox" name="nt_suppress_email" value="1">{$smarty.const.LANG_CREATE_OPTIONS_NOCOPYTOREQUESTER}<br>
			</span><br>
		<table>
		<tr>
			<td valign="top"> 		
	    		<span class="cer_maintable_heading">{$smarty.const.LANG_UPDATE_ATTACHMENT}: </span>
	    	</td>
	    	<td valign="top">
			    <select name="attachment_list" size="3">
				    {if count($file_name_array) == 0}<option value="">{$smarty.const.LANG_CREATE_ATTACHMENTS_NO}{/if}
			    	{html_options options=$file_name_array}
			    </select><br>
			    <input type=button value="{$smarty.const.LANG_CREATE_ATTACHMENTS_ADD}" class="cer_button_face" onclick='javascript: cer_upload_win();'>
	    	</td>
	    </tr> 
		</table>
			
		</td>
  </tr>
  <tr bgcolor="#A0A0A0">
    <td>
	<table width="100%">
	    <tr><td align="left">&nbsp;</td>
  		<td align="right">
				<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_CREATE_SUBMIT}">
		</td>
	 </tr></table></td>
	</tr>
</table>
</form>

<!-- Spellchecker Form -->
<form name="spellform" method="POST" target="spellWindow" action="includes/elements/spellcheck/spellcheck.php">
<input type="hidden" name="spellstring" value="">
</form>
<!-- End Spellchecker Form -->

{include file="footer.tpl.php"}
</body>
</html>
