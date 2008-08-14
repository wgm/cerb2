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
| File: header.php
|
| Purpose: The global page header.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");

$cerberus_disp = new cer_display_obj;
?>

<script>
sid = "<?php echo "sid=" . $session->session_id; ?>";
show_sid = <?php echo ((@$cfg->settings["track_sid_url"]) ? "true" : "false"); ?>;

<?php if(isset($new_pm) && $new_pm !== false) { ?>
var new_pm = <?php echo $new_pm; ?>;
<?php } else { ?>
var new_pm = false;
<?php } ?>

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
	url = "create.php?qid=" + parseInt(qid);
	if(show_sid) { url = url + "&" + sid; } document.location = url;
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
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="2" valign="bottom" bgcolor="#FFFFFF"> 
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="99%"><img src="logo.gif"></td>
          <td width="1%" align="right" valign="bottom" nowrap><span class="cer_footer_text"><?php echo LANG_HEADER_LOGGED; ?>
              <b><?php echo $session->vars["login_handler"]->user_login; ?></b> [ <a href="<?php echo cer_href("logout.php"); ?>" class="cer_footer_text"><?php echo strtolower(LANG_WORD_LOGOUT); ?></a> ]</span>
              <?php if($unread_pm) { ?>
	            <br>
              	<a href="<?php echo cer_href("my_cerberus.php?mode=messages&pm_folder=ib"); ?>" class="cer_configuration_updated"><?php echo $unread_pm; ?> <?php echo LANG_HEADER_UNREAD_MESSAGES; ?>!</a>
              <?php } ?>
            
			<?php if(!empty($session->vars["login_handler"]->ticket_id)) { ?>
    	        <br>
				<span class="cer_footer_text"><B>[</B> <?php echo LANG_HEADER_LAST_VIEWED; ?>: <a href="<?php echo $session->vars["login_handler"]->ticket_url; ?>" class="cer_maintable_text"><?php echo substr($session->vars["login_handler"]->ticket_subject,0,45); ?></a> <B>]</B></span>
			<?php } ?>
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
          <td nowrap <?php if($page == "index.php") { ?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"><a href="<?php echo cer_href("index.php"); ?>" class="<?php if($page == "index.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo strtolower(LANG_WORD_HOME); ?></a><img src="includes/images/spacer.gif" width="15" height="8" align="absmiddle"></td>
          
          <?php if (isset($session->vars["psearch"])) { ?>
          	<td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          	<td nowrap <?php if($page == "ticket_list.php") { ?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("ticket_list.php"); ?>" class="<?php if($page == "ticket_list.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_RESULTS; ?></a><img src="includes/images/spacer.gif" width="15" height="8"></td>
          <?php } ?>
          
          <?php if($priv->has_priv(ACL_KB_VIEW,BITGROUP_1) && $cfg->settings.show_kb) { ?>
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "knowledgebase.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("knowledgebase.php"); ?>" class="<?php if($page == "knowledgebase.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_KB; ?></a><img src="includes/images/spacer.gif" width="15" height="1"></td>
          <?php } ?>
          
          <?php if($priv->has_priv(ACL_CONFIG_MENU,BITGROUP_1)) { ?>
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "configuration.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("configuration.php"); ?>" class="<?php if($page == "configuration.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_CONFIG; ?></a><img src="includes/images/spacer.gif" width="15" height="1"></td>
          <?php } ?>
          
          <?php if($priv->has_priv(ACL_CONTACTS,BITGROUP_2)) { ?>
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "clients.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="1"><a href="<?php echo cer_href("clients.php"); ?>" class="<?php if($page == "clients.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_CONTACTS; ?></a><img src="includes/images/spacer.gif" width="15" height="8"></td>
    	  <?php } ?>
          
          <?php if($priv->has_priv(ACL_REPORTS,BITGROUP_3)) { ?>
	    	  <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "reports.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="8"><a href="<?php echo cer_href("reports.php"); ?>" class="<?php if($page == "reports.php") {?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo LANG_HEADER_REPORTS; ?></a><img src="includes/images/spacer.gif" width="15" height="8"></td>
    	  <?php } ?>
          
          <?php if($priv->has_priv(ACL_PREFS_USER,BITGROUP_1)) { ?>
	          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
    	      <td nowrap <?php if($page == "my_cerberus.php") {?>bgcolor="#FF6600"<?php } else { ?>onMouseover="this.style.backgroundColor='#AAAAAA';" onMouseout="this.style.backgroundColor='#888888';"<?php } ?>><img src="includes/images/spacer.gif" width="15" height="8"><a href="<?php echo cer_href("my_cerberus.php"); ?>" class="<?php if($page == "my_cerberus.php") { ?>headerMenuActive<?php } else { ?>headerMenu<?php } ?>"><?php echo strtolower(LANG_MYCERBERUS); ?></a><img src="includes/images/spacer.gif" width="15" height="8"></td>
    	  <?php } ?>
          
          <td valign="bottom"><img src="includes/images/menuSep.gif" width="1" height="10" align="bottom"></td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </td>
    <td width="1%" nowrap bgcolor="#666666" valign="bottom" align="right">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr> 
          <td nowrap>&nbsp;</td>
          <td nowrap bgcolor="#444444"><img src="includes/images/spacer.gif" width="15" height="8"><a href="javascript:toggleHeaderAdvanced();" class="headerMenu"><?php echo strtolower(LANG_HEADER_ADVANCED); ?></a><img src="includes/images/spacer.gif" width="15" height="8"></td>

          <?php /*
          
          <?php if($urls.save_layout) { ?> 
          	<td nowrap><img src="includes/images/spacer.gif" width="15" height="8"><a href="{$urls.save_layout}" class="headerMenu"><?php echo strtolower(LANG_HEADER_SAVE_PAGE_LAYOUT); ?></a><img src="includes/images/spacer.gif" width="15" height="8"></td>
          <?php } ?>
          
          */ ?>
          
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
    <td bgcolor="#CCCCCC"><div id="header_advanced" style="display:<?php if($session->vars["login_handler"]->user_prefs->page_layouts["layout_home_header_advanced"]) {?>block<?php } else { ?>none<?php } ?>;"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#888888">
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
					<input type="hidden" name="search_owner" value="<?php echo $session->vars["login_handler"]->user_id; ?>">
                    <td>
                    	<?php if(!empty($session->vars["login_handler"]->user_prefs->assign_queues->queues)) { ?>
				  		<span class="cer_maintable_text">Assign me a max of <b><?php echo $session->vars["login_handler"]->user_prefs->assign_queues->max; ?></b> tickets</span>
				  		(<a href="javascript:jumpNav(4);" class="cer_maintable_heading">configure</a>)
				  		<span class="cer_maintable_text"> using </span>
				  		<select name="assign_type" class="cer_footer_text">
				  			<option value="newest" selected>newest
				  			<option value="oldest">oldest
				  		</select>
				  		
				  		<input type="submit" value="Quick Assign!" class="cer_button_face">
				  	<?php } else { ?>
				  		<a href="javascript:jumpNav(4);" class="cer_maintable_heading"><?php echo LANG_HEADER_CONFIGURE_QUICKASSIGN; ?></a>
				  	<?php } ?>
					</td>
					</form>
					<form name="header" action="display.php" method="post">
                    <td align="right" nowrap>
					    <?php if($priv->has_priv(ACL_CREATE_TICKET,BITGROUP_3)) {?>
					        <input type="button" class="cer_button_face" name="newTicket" value="<?php echo LANG_HEADER_NEW_TICKET; ?>" OnClick="javascript:createTicket(this.form.new_ticket_queue_id[this.form.new_ticket_queue_id.selectedIndex].value);"> 
					        <?php $cerberus_disp->draw_queue_select("new_ticket_queue_id",0,"cer_footer_text","","","write"); ?>
						<?php } ?>                    
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
						<span class="cer_header_loginBold"><?php echo LANG_HEADER_QUICKQUEUE; ?>:</span>
						<?php $cerberus_disp->draw_queue_select("jump_queue",((isset($qid))?"$qid":"0"),"cer_footer_text","","OnChange=\"javascript:jumpQueue();\"","read"); ?>
						<input type="button" value="<?php echo LANG_BUTTON_GO; ?>" class="cer_button_face" OnClick="javascript:jumpQueue();">
						</span>                    
                  </td>
                    <td align="right" nowrap> 
                      <input type="button" class="cer_button_face" name="gotoTicket" value="<?php echo LANG_HEADER_GOTO_TICKET; ?>" OnClick="javascript:loadTicket(this.form.ticket);"><input type="text" id="goto_input" name="ticket" size="5" value="" class="cer_footer_text">
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

<script>
	function toggleHeaderAdvanced() {
		if (document.getElementById) {
			if(document.getElementById("header_advanced").style.display=="block") {
				document.getElementById("header_advanced").style.display="none";
				<?php /*
				<?php if($urls.save_layout) { ?>
					document.formSaveLayout.layout_home_header_advanced.value = 0;
				<?php } ?>
				*/ ?>
			}
			else {
				document.getElementById("header_advanced").style.display="block";
				<? /*
				<?php if($urls.save_layout) { ?>
					document.formSaveLayout.layout_home_header_advanced.value = 1;
				<?php } ?>
				*/ ?>
			}
		}
	}
</script>
