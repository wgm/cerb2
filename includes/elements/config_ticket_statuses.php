<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

if(!$priv->has_priv(ACL_GLOBAL_SETTINGS,BITGROUP_2))
{
	die(LANG_CERB_ERROR_ACCESS);
}

require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_TicketStatuses.class.php");
$ticket_status_handler = cer_TicketStatuses::getInstance();
$ticket_status_handler->reload();
$ticket_status_handler->computeTicketStatusCounts();
$status_counts = $ticket_status_handler->getTicketStatusCounts();
?>

<script>
	var nodelete_statuses = new Array();
	<?php 
	foreach($ticket_status_handler->getTicketStatuses() as $status) {
		if(isset($ticket_status_handler->permanent_statuses[$status]) || $ticket_status_handler->status_counts[$status]>0) { ?>
		nodelete_statuses['<?php echo addslashes($status); ?>'] = 1;
	<?php }	} ?>

	function focusStatus(status) {
		deleteBtn = document.getElementById("div_status_delete");
		
		if(null == nodelete_statuses[status])
			deleteBtn.style.display='block';
		else
			deleteBtn.style.display='none';
	}
	
	function validateStatusForm(f) {
		
		if(f.statuses_add.value.length > 0) {
			var regexp = new RegExp("^([a-zA-Z_0-9\-\_ ]+)$");
			var strr = f.statuses_add.value;
			var matches = strr.match(regexp);
			if(null == matches) {
				alert("Cerberus [ERROR]: Status names may only contain alphanumerics (a-z 0-9), spaces, dashes (-) and underscores (_).");
				return false;
			}
		}
		
		return true;
	}
</script>

<form action="configuration.php" method="post" onsubmit="return validateStatusForm(this);">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="statuses">
<input type="hidden" name="module" value="statuses">
<input type="hidden" name="statuses_initial" value="<?php echo addslashes(implode(",",array_keys($ticket_status_handler->getTicketStatuses()))); ?>">
<input type="hidden" name="statuses_ordered" value="<?php echo addslashes(implode(",",array_keys($ticket_status_handler->getTicketStatuses()))); ?>">

<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . strtoupper(LANG_WORD_SUCCESS) . ": Ticket Statuses Updated!</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass">  
    <td>Ticket Statuses</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td class="cer_maintable_heading" valign="top" align="left"> 
    	<table cellpadding="0" cellspacing="0" border="0" width="100%">
    		<tr>
    			<td width="40%" nowrap class="cer_maintable_text">
    				<select size="25" name="status_list" style="width:100%;" onchange="focusStatus(this.options[this.selectedIndex].value);">
    				<?php
    					foreach($ticket_status_handler->getTicketStatuses() as $status) {
    						$count = $ticket_status_handler->status_counts[$status];
    						echo sprintf("<option value=\"%s\">%s%s\r\n",
    								addslashes($status),
    								$status,
    								((isset($ticket_status_handler->permanent_statuses[$status])) ? "" : sprintf(" (%d ticket%s)",$count,($count<>1)?"s":""))
    							);
    					}
    				?>
    				</select><br>
    			</td>
    			<td width="1%" nowrap>
    				<img src="includes/images/spacer.gif" width="10" height="1">
    			</td>
    			<td valign="top" align="left" width="59%" class="cer_maintable_text">
    				<span class="cer_maintable_heading">Add Ticket Status:</span><br>
    				<input type="text" name="statuses_add" size="32"><br>
    				<br>
    				
    				<div id="div_status_delete" style="display:none;">
    					<input type="button" value="Remove Selected Status" class="cer_button_face" onclick="javascript: dropOptions(this.form.status_list); saveListState(this.form.status_list,this.form.statuses_ordered); document.getElementById('div_status_delete').style.display='none';"><br>
    					<br>
    				</div>
    				
    				<input type="button" value="Move Up" class="cer_button_face" onclick="javascript: moveUp(this.form.status_list); saveListState(this.form.status_list,this.form.statuses_ordered);"><br>
    				<input type="button" value="Move Down" class="cer_button_face" onclick="javascript: moveDown(this.form.status_list); saveListState(this.form.status_list,this.form.statuses_ordered);"><br>
    				<br>
    				<b>Instructions:</b> Here you can define custom ticket statuses that categorize tickets in your environment.<br>
    				<br>
    				Each custom status is listed with the number of tickets assigned to that status.  You may not remove 
    				any status that has tickets assigned to it.  First, move those tickets to a new status using a ticket search 
    				and mass status change.<br>
    				<br>
    				You may not remove the core statuses: new, awaiting-reply, customer-reply, bounced, resolved and dead.<br>
    				<br>
    				You may change the order the ticket statuses are shown in all status dropdowns by using the Move Up 
    				and Move Down buttons.
    				<br>
    			</td>
    		</tr>
    		
    		<tr bgcolor="#BBBBBB">
    			<td colspan="3" align="right">
    				<input type="submit" value="<?php echo LANG_BUTTON_SAVE_CHANGES; ?> &gt;&gt;" class="cer_button_face">
    			</td>
    		</tr>
    	</table>
    </td>
  </tr>
</table>
</form>
