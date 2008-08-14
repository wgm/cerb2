<script language="Javascript">
sid = "sid={$session_id}";
show_sid = {$track_sid};
error_nan = "{$smarty.const.LANG_CERB_ERROR_TICKET_NAN}";

sid = "sid={$session_id}";
show_sid = {$track_sid};

{if isset($new_pm) && $new_pm !== false}
new_pm = {$new_pm};
{else}
new_pm = false;
{/if}

{literal}
function loadTicket(tkt)
{
	tktval = tkt.value;
	
	url = "display.php?ticket=" + tktval;
	if(show_sid) { url = url + "&" + sid; } document.location = url;
}

function cer_upload_win()
{
	url = "upload.php";
	if(show_sid) {
		window.open( url + "?" + sid,"uploadWin","width=600,height=300,status=yes");
	}
	else {
		window.open( url,"uploadWin","width=600,height=300,status=yes");
	}
}

function getCacheKiller() {
	var date = new Date();
	return date.getTime();
}

function ticketCheck(tkt)
{
//	tktval = parseInt(tkt.value);
	tktval = tkt.value;
	if(tktval) return true; else return false;
}

function createTicket(qid)
{
	if(qid >= 0) {
		url = "create.php?qid=" + parseInt(qid);
		if(show_sid) { url = url + "&" + sid; } document.location = url;
	}
}

function getQueueId(frm) {
	idx = frm.new_ticket_queue_id.selectedIndex;
	if(idx >= 0) {
		return frm.new_ticket_queue_id[idx].value;
	}
	return -1;
}

function formatURL(url)
{
	if(show_sid) { url = url + "&" + sid; }
	return(url);
}

function printTicket(url)
{
	window.open(url,'print_ticket','width=700,height=500,scrollbars=yes');
}

function pmCheck()
{
	if(new_pm != false)
	{
		url = "message_popup.php?mid=" + new_pm;
		window.open(formatURL(url),"pm_notify_wdw","width=200,height=175");
	}
}

function load_init()
{
	pmCheck();
}

function jumpQueue(qid)
{
	qid = document.headerForm.jump_queue.value;
	url = "ticket_list.php?queue_view=1&qid=" + qid;
	if(show_sid) { url = url + "&" + sid; } document.location = url;
}

function jumpNav(link)
{
	if(link != null) {
		link_id = link;
	}
	else
		link_id = parseInt(document.headerForm.jump_nav.value);
		
	switch(link_id)
	{
		case 0:
		url = "my_cerberus.php?mode=dashboard";
		break;
		case 1:
		url = "my_cerberus.php?mode=tasks";
		break;
		case 2:
		url = "my_cerberus.php?mode=messages";
		break;
		case 3:
		url = "my_cerberus.php?mode=preferences";
		break;
		case 4:
		url = "my_cerberus.php?mode=assign";
		break;
		case 5:
		url = "my_cerberus.php?mode=notification";
		break;
	}

//	if(show_sid) { url = url + "&" + sid; } document.location = url;
	document.location = formatURL(url);
}

function findX(o)
{
	var left = 0;
	if (o.offsetParent)
	{
		while (o.offsetParent)
		{
			left += o.offsetLeft
			o = o.offsetParent;
		}
	}
	else if (o.x)
	{
		left += obj.x;
	}
	return left;
}

function findY(o)
{
	var top = 0;
	if (o.offsetParent)
	{
		while (o.offsetParent)
		{
			top += o.offsetTop
			o = o.offsetParent;
		}
	}
	else if (o.y) {
		top += o.y;
	}
	return top;
}	

{/literal}


</script>
{if (!empty($errorcode)) }
<font color="red"><center>{$errorcode}</center></font>
{/if}

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="2" valign="bottom" bgcolor="#FFFFFF"> 
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="99%"><img src="logo.gif"></td>
          <td width="1%" align="right" valign="bottom" nowrap><span class="cer_footer_text">{$smarty.const.LANG_HEADER_LOGGED}
              <b>{$user_login}</b> [ <a href="{$urls.logout}" class="cer_footer_text">{$smarty.const.LANG_WORD_LOGOUT|lower}</a> ]</span>
              {if $unread_pm}
	            <br>
              	<a href="{$urls.mycerb_pm}" class="cer_configuration_updated">{$unread_pm} {$smarty.const.LANG_HEADER_UNREAD_MESSAGES}!</a>
              {/if}
            
			{if !empty($session->vars.login_handler->ticket_id)}
    	        <br>
				<span class="cer_footer_text"><B>[</B> {$smarty.const.LANG_HEADER_LAST_VIEWED}: <a href="{$session->vars.login_handler->ticket_url}" class="cer_maintable_text">{$session->vars.login_handler->ticket_subject|truncate:45:"..."|short_escape}</a> <B>]</B></span>
			{/if}
            <br>
            <img src="includes/images/spacer.gif" height="3" width="1"></strong></span></td>
        </tr>
      </table>
</td>
  </tr>
  <tr> 
    <td colspan="2" valign="bottom" bgcolor="#FFFFFF" class="headerMenu"><img src="includes/images/spacer.gif" width="1" height="5"></td>
  </tr>
  <tr> 
    <td width="99%" valign="bottom" bgcolor="#888888"> 
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>
          
          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          <td nowrap {if $page == "index.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"><a href="{$urls.home}" class="{if $page == "index.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_WORD_HOME|lower}</a><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>
          
          {if (isset($session->vars.psearch)) }
          	<td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          	<td nowrap {if $page == "ticket_list.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.search_results}" class="{if $page == "ticket_list.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_RESULTS}</a><img src="includes/images/spacer.gif" width="15" height="8"></td>
          {/if}
          
          {if $priv->has_priv(ACL_KB_VIEW,BITGROUP_1) && $cfg->settings.show_kb }
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap {if $page == "knowledgebase.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.knowledgebase}" class="{if $page == "knowledgebase.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_KB}</a><img src="includes/images/spacer.gif" width="15" height="1"></td>
          {/if}
          
          {if $priv->has_priv(ACL_CONFIG_MENU,BITGROUP_1) }
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap {if $page == "configuration.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.configuration}" class="{if $page == "configuration.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_CONFIG}</a><img src="includes/images/spacer.gif" width="15" height="1"></td>
          {/if}
          
          {if $priv->has_priv(ACL_CONTACTS,BITGROUP_2) }
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap {if $page == "clients.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="1"><a href="{$urls.clients}" class="{if $page == "clients.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_CONTACTS}</a><img src="includes/images/spacer.gif" width="15" height="8"></td>
    	  {/if}
          
          {if $priv->has_priv(ACL_REPORTS,BITGROUP_3) }
	    	  <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap {if $page == "reports.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.reports}" class="{if $page == "reports.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_HEADER_REPORTS}</a><img src="includes/images/spacer.gif" width="15" height="8"></td>
    	  {/if}
          
          {if $priv->has_priv(ACL_PREFS_USER,BITGROUP_1) }
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap {if $page == "my_cerberus.php"}bgcolor="#FF6600"{else}onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"{/if}><img src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.preferences}" class="{if $page == "my_cerberus.php"}headerMenuActive{else}headerMenu{/if}">{$smarty.const.LANG_MYCERBERUS|lower}</a><img src="includes/images/spacer.gif" width="15" height="8"></td>
    	  {/if}
          
          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </td>
    <td width="1%" nowrap bgcolor="#666666" valign="bottom" align="right">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr> 
          <td nowrap>&nbsp;</td>
          <td nowrap bgcolor="#444444"><img src="includes/images/spacer.gif" width="15" height="8"><a href="javascript:toggleHeaderAdvanced();" class="headerMenu" id="headerAdvanced">{$smarty.const.LANG_HEADER_ADVANCED_OFF|lower}</a><img src="includes/images/spacer.gif" width="15" height="8"></td>

          {if $urls.save_layout}  
          	<td nowrap><img src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.save_layout}" class="headerMenu">{$smarty.const.LANG_HEADER_SAVE_PAGE_LAYOUT|lower}</a><img src="includes/images/spacer.gif" width="15" height="8"></td>
          {/if}
          
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td colspan="2" bgcolor="#003399" class="headerMenu"><table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td bgcolor="#FF6600"><img src="includes/images/spacer.gif" width="1" height="5"></td>
        </tr>
      </table></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#CCCCCC"><div id="header_advanced" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_home_header_advanced}block{else}none{/if};"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#888888">
        <tr> 
          <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="2">
                  <tr bgcolor="#DDDDDD"> 
			        <form name="headerAssignForm" action="ticket_list.php" method="post">
					<input type="hidden" name="sid" value="{$session_id}">
					<input type="hidden" name="form_submit" value="quick_assign">
					<input type="hidden" name="search_submit" value="1">
					<input type="hidden" name="qid" value="">
					<input type="hidden" name="search_status" value="-1">
					<input type="hidden" name="search_subject" value="">
					<input type="hidden" name="search_content" value="">
					<input type="hidden" name="search_company" value="">
					<input type="hidden" name="search_owner" value="{$session->vars.login_handler->user_id}">
                    <td>
                    	{if !empty($session->vars.login_handler->user_prefs->assign_queues->queues)}
				  		<span class="cer_maintable_text">Assign me a max of <b>{$session->vars.login_handler->user_prefs->assign_queues->max}</b> tickets</span>
				  		(<a href="javascript:jumpNav(4);" class="cer_maintable_heading">configure</a>)
				  		<span class="cer_maintable_text"> using </span>
				  		<select name="assign_type" class="cer_footer_text">
				  			<option value="newest" selected>newest
				  			<option value="oldest">oldest
				  		</select>
				  		
				  		<input type="submit" value="Quick Assign!" class="cer_button_face">
				  	{else}
				  		<a href="javascript:jumpNav(4);" class="cer_maintable_heading">{$smarty.const.LANG_HEADER_CONFIGURE_QUICKASSIGN}</a>
				  	{/if}
					</td>
					</form>
					<form name="header" action="display.php" method="post">
                    <td align="right" nowrap>
					    {if $priv->has_priv(ACL_CREATE_TICKET,BITGROUP_3) }
					        <input type="button" class="cer_button_face" name="newTicket" value="{$smarty.const.LANG_HEADER_NEW_TICKET}" OnClick="javascript:createTicket(getQueueId(this.form));"> 
					        <select name="new_ticket_queue_id" class="cer_footer_text">
					    	  {html_options options=$header_write_queues selected=$qid}
							</select>
						{/if}                    
					</td>
					</form>
              </tr>
            </table></td>
        </tr>
        </form>
        <tr> 
          <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
                  <tr bgcolor="#DDDDDD"> 
                    <td>
						<form name="headerForm" action="display.php" method="post" OnSubmit="javascript:return ticketCheck(this.ticket);">
						<span class="cer_header_loginBold">{$smarty.const.LANG_HEADER_QUICKQUEUE}:</span>
						<select name="jump_queue" class="cer_footer_text" OnChange="javascript:jumpQueue();">
							{html_options options=$header_readwrite_queues selected=$qid}
						</select>
						<input type="button" value="{$smarty.const.LANG_BUTTON_GO}" class="cer_button_face" OnClick="javascript:jumpQueue();">
						</span>                    
                  </td>
                    <td align="right" nowrap> 
                      <input type="button" class="cer_button_face" name="gotoTicket" value="{$smarty.const.LANG_HEADER_GOTO_TICKET}" OnClick="javascript:loadTicket(this.form.ticket);"><input type="text" id="goto_input" name="ticket" size="5" value="" class="cer_footer_text">
                	</td>
				</form>                	
              </tr>
            </table></td>
        </tr>
        <tr bgcolor="#888888"> 
          <td><img src="includes/images/spacer.gif" width="1" height="5"></td>
        </tr>
      </table></div></td>
  </tr>
</table>

{literal}
<script>
	function toggleHeaderAdvanced() {
		if (document.getElementById) {
			if(document.getElementById("header_advanced").style.display=="block") {
				document.getElementById("header_advanced").style.display="none";

				{/literal}

				document.getElementById("headerAdvanced").innerHTML="{$smarty.const.LANG_HEADER_ADVANCED_OFF|lower}";
				
				{if $urls.save_layout}
					document.formSaveLayout.layout_home_header_advanced.value = 0;
				{/if}
				{literal}
			}
			else {
				document.getElementById("header_advanced").style.display="block";

				{/literal}

				document.getElementById("headerAdvanced").innerHTML="{$smarty.const.LANG_HEADER_ADVANCED_ON|lower}";
				
				{if $urls.save_layout}
					document.formSaveLayout.layout_home_header_advanced.value = 1;
				{/if}
				{literal}
			}
		}
	}
</script>
{/literal}
