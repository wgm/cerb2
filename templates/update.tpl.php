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
{include file="keyboard_shortcuts_jscript.tpl.php}

<script src="{$cfg->settings.http_server}{$cfg->settings.cerberus_gui_path}/includes/scripts/spellcheck.js"></script>
<script src="{$cfg->settings.http_server}{$cfg->settings.cerberus_gui_path}/includes/scripts/inserttext.js"></script>
<script>
<!--- 
{literal}
var clientPC = navigator.userAgent.toLowerCase();
var clientVer = parseInt(navigator.appVersion);

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav  = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));

var is_win   = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac    = (clientPC.indexOf("mac")!=-1);

function addAttachment(name)
{
	document.frmTicketUpdate.attachment_list.options[document.frmTicketUpdate.attachment_list.length] = new Option(name);
}

function doQuote()
	{
  if(document.frmTicketUpdate.btn_quote.value=="Quote") // we're opening a quote
  	{
      insertText(document.frmTicketUpdate.ticket_response, "[quote]");
      document.frmTicketUpdate.btn_quote.value="Quote*";
      document.frmTicketUpdate.ticket_response.focus();
    }
  else // we're closing a quote.
  	{
	  insertText(document.frmTicketUpdate.ticket_response, "[/quote]");
      document.frmTicketUpdate.btn_quote.value="Quote";
      document.frmTicketUpdate.ticket_response.focus();
    }
  }
  
{/literal}
// --->
</script>

</head>

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}
<br>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr> 
    <td colspan="2" valign="top" class="cer_display_header">{if !is_batch()} {$smarty.const.LANG_UPDATE_TICKET} #{$o_ticket->ticket_mask_id}: {$o_ticket->ticket_subject|short_escape} {else} Multiple Ticket Batch Response{/if}</td>
  </tr>
	{include file="display/display_ticket_navbar.tpl.php"}
</table>
<form name="frmTicketUpdate" action="update.php" method="post" enctype="multipart/form-data" onsubmit="return verifyInput();">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="initial_owner" value="{$o_ticket->ticket_assigned_to_id}">
<input type="hidden" name="initial_status" value="{$o_ticket->ticket_status}">
<input type="hidden" name="initial_queue" value="{$o_ticket->ticket_queue_id}">
<input type="hidden" name="initial_priority" value="{$o_ticket->ticket_priority}">
<input type="hidden" name="form_submit" value="update">
<input type="hidden" name="bids" value="{$o_ticket->batch->batch_id_string}">
<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
<input type="hidden" name="thread" value="{$thread}">
<input type="hidden" name="qid" value="{$o_ticket->ticket_queue_id}">
<input type="hidden" name="ticket_id" value="{$o_ticket->ticket_id}">
{if !empty($batch_action)}<input type="hidden" name="batch_action" value="{$batch_action}">{/if}

<a name="#update">
<table width="100%" border="0" cellspacing="0" cellpadding="2" bordercolor="#A0A0A0">
  <tr class="boxtitle_blue_glass">
    <td width="100%">
 {if !is_batch()}
 	{$smarty.const.LANG_UPDATE_TICKET} #{$o_ticket->ticket_mask_id}: 
	 {if empty($o_ticket->quote_thread->thread_subject)}
	  {$o_ticket->ticket_subject|short_escape} 
	 {else}
	  {$o_ticket->quote_thread->thread_subject|short_escape} 
	 {/if}
  {else} Multiple Ticket Batch Response
  {/if}</td>
  </tr>
</table>
<table width="100%" border="1" cellspacing="0" cellpadding="2" bordercolor="#666666">
  <tr bgcolor="#C8C8C8">
	<td>
		<table width="100%" border="1" cellspacing="0" cellpadding="2" bordercolor="#666666">
    		<tr class="boxtitle_gray_glass_dk">
    			<td>{$smarty.const.LANG_UPDATE_RECIPIENTS}</td>
			</tr>
    		<tr bgcolor="#C8C8C8">
      			<td>
					<table width="100%" border="0" cellspacing="0" cellpadding="2" align="left">
					
					{* Show Queue Address List if Not Batched *}
					{if $o_ticket->is_batch == false}
                        <tr>
							<td class="cer_maintable_heading" align="right" nowrap width="10%" nobr>From Address: </td>
							<td class="cer_maintable_text" align="left" width="90%">
              	<select name="use_queue_address">
              	  {html_options options=$o_ticket->queues selected=$o_ticket->queue_addresses_id}
              	</select>
              	
              	{* or from {$smarty.const.LANG_UPDATE_REQUESTOR}  [JXD]: 
			   <input type="checkbox" name="from_ticket_requester">*}
              </td>
						</tr>
					{/if}
				
 				{if $type == "forward"}
 					{* forward address *}
 					<tr>
 						<td class="cer_maintable_heading" align="right" valign="middle" nowrap>Forward to: </td>
 						<td class="cer_maintable_text" align="left">
 							<input type="text" name="ticket_forward" size="50" maxlength="128"> <span class="cer_footer_text">(comma-delimited)</span><br>
 						</td>
 					</tr>
 				{else}

					{* List Requester Addresses that are Not Suppressed  *}
					<tr>
						<td class="cer_maintable_heading" align="right" nowrap>{$smarty.const.LANG_UPDATE_REQUESTOR}: </td>
						<td class="cer_maintable_text" align="left">
						{if $o_ticket->is_batch == false}
							{$o_ticket->requesters_reply_string}
						{else}
			            	Batch Response
			            {/if}
			            </td>
					</tr>
			
					{* Add to Requesters List Checkbox *}
					{if $type != "comment"}
					<tr>
						<td class="cer_maintable_heading" align="right" valign="middle" nowrap>{$smarty.const.LANG_UPDATE_CC}: </td>
						<td class="cer_maintable_text" align="left">
 							<input type="text" name="ticket_cc" size="50" maxlength="128">	
 							<span class="cer_footer_text">(comma-delimited)</span><br>
							<input type="checkbox" name="ticket_cc_add_reqs"> Add Cc:'d E-mail Addresses to Ticket Requesters.
						</td>
					</tr>
					{/if}
 				
				{/if}
					
					<tr>
						<td class="cer_maintable_heading" align="right" valign="top" nowrap>Watchers: </td>
						<td class="cer_footer_text" align="left">
						{if $o_ticket->watchers_string != ""}
							{$o_ticket->watchers_string}
						{else}
							{$smarty.const.LANG_WORD_NONE}
						{/if}
			            </td>
					</tr>
				</table>
			</td>
		</tr>

		{* Reply Screen Actions Javascript (escaped) *}
		<script>
		<!--
		{literal}
		function doTemplate()
		{ 
			window.open("{/literal}{$urls.show_templates}{literal}", "wdwTemplate", "fullscreen=no,toolbar=no,status=no,menubar=no,scrollbars=yes,resizable=yes,directories=no,location=no,width=620,height=500");
		}
		
		function verifyInput()
		{
			{/literal}{if $type == "forward"}{literal}
			if(document.frmTicketUpdate.ticket_forward.value == "") {
				alert("Cerberus [ERROR]: Forward address can not be blank.");
				return false;
			}
			{/literal}{/if}{literal}
			
			return true;
		}
		
		function quickAwaitingReply()
		{
		  	for(i=0;i<document.frmTicketUpdate.ticket_status.length;i++)
		    	{
		      	if(document.frmTicketUpdate.ticket_status.options[i].value == "awaiting-reply")
				  {
		          	document.frmTicketUpdate.ticket_status.selectedIndex = i;
		            break;
		          }
		      }
		}
		  
		function quickResolved()
		{
		  	for(i=0;i<document.frmTicketUpdate.ticket_status.length;i++)
		    	{
		      	if(document.frmTicketUpdate.ticket_status.options[i].value == "resolved")
				  {
		          	document.frmTicketUpdate.ticket_status.selectedIndex = i;
		            break;
		          }
		      }
		}
		  
		function quickTake()
		{
			for(i=0;i<document.frmTicketUpdate.ticket_owner.length;i++)
			{
				if(document.frmTicketUpdate.ticket_owner.options[i].value == "{/literal}{$session->vars.login_handler->user_id}{literal}")
				{
					document.frmTicketUpdate.ticket_owner.selectedIndex = i;
					break;
				}
			}
		}
		
		function doSignature()
		{ 
		signature = "{/literal}{$o_ticket->user_signature_js}{literal}\r\n";
		
		  insertText(document.frmTicketUpdate.ticket_response,signature);
		  document.frmTicketUpdate.ticket_response.focus();
		}
		
		function doSpellCheck()
		{
			document.spellform.spellstring.value = document.frmTicketUpdate.ticket_response.value;
			window.open("", "spellWindow", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=640,height=480');
			document.spellform.submit();
		}
		 {/literal}
		//-->
		</script>

		{* Draw Reply Actions *}
		<tr>
			<td>
				{if $ticket_glance_status_options}<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_STATUS}: </span><select name="ticket_status">{html_options options=$ticket_glance_status_options selected=$o_ticket->ticket_status}</select>&nbsp;{else}<input type="hidden" name="ticket_status" value="{$o_ticket->ticket_status}">{/if}
				{if $ticket_glance_owner_options}<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_OWNER}: </span><select name="ticket_owner">{html_options options=$ticket_glance_owner_options selected=$o_ticket->ticket_assigned_to_id}</select>&nbsp;{else}<input type="hidden" name="ticket_owner" value="{$o_ticket->ticket_assigned_to_id}">{/if}
				{if $ticket_glance_queue_options}<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_QUEUE}: </span><select name="ticket_queue">{html_options options=$ticket_glance_queue_options selected=$o_ticket->ticket_queue_id}</select>&nbsp;{else}<input type="hidden" name="ticket_queue" value="{$o_ticket->ticket_queue_id}">{/if}
				{if $ticket_glance_priority_options}<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_PRIORITY}: </span><select name="ticket_priority">{html_options options=$ticket_glance_priority_options selected=$o_ticket->ticket_priority}</select>&nbsp;{else}<input type="hidden" name="ticket_priority" value="{$o_ticket->ticket_priority}">{/if}
         	</td>
		</tr>
		
		<tr>
			<td>
				<span class="cer_maintable_heading">{$smarty.const.LANG_UPDATE_TYPE}:</span>
				<span class="cer_maintable_text">
					{if $type=="reply"}{$smarty.const.LANG_UPDATE_RESPONSE}<input type="hidden" name="thread_type" value="email">{/if}
					{if $type=="comment"}{$smarty.const.LANG_UPDATE_COMMENT}<input type="hidden" name="thread_type" value="comment">{/if}
		 			{if $type=="forward"}Forward to 3rd Party<input type="hidden" name="thread_type" value="forward">{/if}
		 		</span>
				&nbsp; 
				<span class="cer_maintable_heading">{$smarty.const.LANG_UPDATE_TIME}: </span><input type="text" size="5" name="ticket_time_worked">&nbsp;<span class="cer_footer_text">({$smarty.const.LANG_WORD_MINUTES|lower})</span> 
         	</td>
		</tr>

			
		<tr>
			<td>
				{if count($o_ticket->ticket_users)}
			    	{* Show Ticket Users, If Any *}
					<span class="cer_maintable_heading">{$smarty.const.LANG_WORD_TICKET_USERS}: </span>
					<span class="cer_maintable_text">
			    	{section name=user loop=$o_ticket->ticket_users}
			          	<b>{$o_ticket->ticket_users[user]->user_login}</b> {$o_ticket->ticket_users[user]->user_what}{if !%user.last%}, {/if}
			          {sectionelse}
			          	<b>{$smarty.const.LANG_WORD_NONE}</b>
			          {/section}
			        </span>
				     <br>
			    {/if}
         	</td>
		</tr>
			
	</table>		
   </td>
 </tr>  

 <tr bgcolor="#C9C9C9"> 
 	<td style="padding-top: 5px;">
				
		<input type="button" name="btn_quote" value="{$smarty.const.LANG_UPDATE_QUOTE}" class="cer_button_face" OnClick="javascript:doQuote();">
		<input type="button" name="btn_sig" value="{$smarty.const.LANG_UPDATE_INSERT_SIGNATURE}" class="cer_button_face" OnClick="javascript:doSignature();">
		<input type="button" name="btn_template" value="{$smarty.const.LANG_UPDATE_USE_TEMPLATE}" class="cer_button_face" OnClick="javascript:doTemplate();">
		{if $pspell_loaded === true}
			<input type="button" value="{$smarty.const.LANG_UPDATE_SPELLCHECK}" OnClick="javascript:doSpellCheck();" class="cer_button_face">
		{else}
			<input type="button" value="{$smarty.const.LANG_UPDATE_SPELLCHECK_ONLINE}" OnClick="javascript:doSpellCheck();" class="cer_button_face">
		{/if}
 		<input type="button" value="{$smarty.const.LANG_UPDATE_TAKE_TICKET}" OnClick="javascript:quickTake();" class="cer_button_face">
 		<input type="button" value="Set Awaiting-Reply" OnClick="javascript:quickAwaitingReply();" class="cer_button_face">
 		<input type="button" value="{$smarty.const.LANG_UPDATE_RESOLVED}" OnClick="javascript:quickResolved();" class="cer_button_face">
		<br>

{* Reply Text Box -- not indented so spaces aren't leading in textbox*}
<textarea name="ticket_response" cols="80" rows="20" wrap="virtual" class="cer_display_emailText" 
ONSELECT="storeCaret(this);"
ONCLICK="storeCaret(this);"
ONKEYUP="storeCaret(this);">{$o_ticket->quote_thread->thread_content}</textarea>
<br>
    	
<table><tr><td valign="top">
	    <span class="cer_maintable_heading">{$smarty.const.LANG_UPDATE_ATTACHMENT}: </span></td>
    	<td valign="top">
		    <select name="attachment_list" size="3">
		    {if count($file_name_array) == 0}<option value="">{$smarty.const.LANG_UPDATE_NO_ATTACHMENTS}{/if}
		    	{html_options options=$file_name_array}
		    </select><br>
			<input type="button" class="cer_button_face" value="{$smarty.const.LANG_UPDATE_EDIT_ATTACHMENTS}" onclick='javascript: cer_upload_win();'>		    
    	</td>
	    
	    </tr></table>
	</td>
  </tr>
	<tr bgcolor="#A0A0A0">
		<td align="right">
			<span class="cer_maintable_header">&nbsp;{$smarty.const.LANG_UPDATE_NEXT_STEP}: </span>
			<select name="next_action" class="cer_maintable_text">
				<option value="details">{$next_ticket_details}
				<option value="queue">{$next_ticket_queue}
				<option value="search">{$smarty.const.LANG_UPDATE_NEXT_LAST_SEARCH}
				<option value="batch">{$smarty.const.LANG_UPDATE_NEXT_BATCHED_TICKETS}
				<option value="home">{$smarty.const.LANG_WORD_HOME}
			</select>
			<input type="submit" class="cer_button_face" value="{$smarty.const.LANG_UPDATE_SUBMIT}">
		</td>
	</tr>
</table>
</form>
<br>

<!-- Spellchecker Form -->
<form name="spellform" method="POST" target="spellWindow" action="includes/elements/spellcheck/spellcheck.php">
<input type="hidden" name="spellstring" value="">
</form>
<!-- End Spellchecker Form -->

{if $o_ticket->is_batch == false}
<table width="100%" border="1" cellspacing="0" cellpadding="2" bordercolor="#666666">
  <tr class="boxtitle_gray_glass_dk"> 
    <td width="100%">Thread Review
    </td>
  </tr>
</table>

<iframe height="300" width="100%" src="{$urls.iframe_threads}#thread_.{$o_ticket->quoted_thread->thread_id}" name="review">
  {include file='display/iframe/display_ticket_threads_iframe.tpl.php' iframe="no"}
</iframe>
{/if}

{include file="footer.tpl.php"}
</body>
</html>