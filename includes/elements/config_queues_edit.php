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
| File: config_queues_edit.php
|
| Purpose: The configuration include that facilitates the queue creation and
|			modification of properties.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

$sched_handler = new cer_ScheduleHandler();

if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();

// Verify that the connecting user has access to modify configuration/queue values
if((!$priv->has_priv(ACL_QUEUE_CREATE,BITGROUP_1) && !$priv->has_priv(ACL_QUEUE_EDIT,BITGROUP_1)) || (!$priv->has_priv(ACL_QUEUE_CREATE,BITGROUP_1) && $pqid==0))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

if(!isset($qid))
{ echo LANG_CONFIG_QUEUE_EDIT_NOID; exit(); }	
	
if($qid != 0) { 
	$u_qids = $queue_access->get_readable_qid_list();
	
	$sql = "SELECT q.queue_id, q.queue_name, q.queue_email_display_name, q.queue_prefix, q.queue_response_open, q.queue_response_close,".
	" q.queue_response_gated, q.queue_send_open, q.queue_send_closed, q.queue_core_update, q.queue_mode, q.queue_default_response_time, q.queue_default_schedule, q.queue_addresses_inherit_qid " .
  	" FROM queue q ".
  	" WHERE q.queue_id IN ($u_qids) AND q.queue_id = $qid ".
  	" ORDER BY q.queue_name ASC";
  $result = $cerberus_db->query($sql);
  if($cerberus_db->num_rows($result)==0)
  	{	echo LANG_CERB_ERROR_ACCESS;
  	exit(); }
	$queue_data = $cerberus_db->fetch_row($result);
}

$queue_handler = new cer_QueueHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pqid" value="<?php echo  @$queue_data["queue_id"]; ?>">
<input type="hidden" name="module" value="queues">
<input type="hidden" name="form_submit" value="queues_edit">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="B5B5B5">
  <tr class="boxtitle_orange_glass"> 
<?php
if($qid==0) {
    ?><td><?php echo  LANG_CONFIG_QUEUE_ADD ?></td><?php
}
else {
    ?><td><?php echo  LANG_CONFIG_QUEUE_EDIT ?> '<?php echo  $queue_data["queue_name"] ?>'</td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td  bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_QUEUE_EDIT_NAME ?>:</td>
            <td width="81%">
              <input type="text" name="queue_name" size="15" maxlength="32" value="<?php echo cer_dbc(@$queue_data["queue_name"]); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_QUEUE_EDIT_NAME_IE ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">E-mail Friendly From: <?php /* echo LANG_CONFIG_QUEUE_EMAIL_DISPLAY_NAME */ ?></td>
            <td width="81%">
              <input type="text" name="queue_email_display_name" size="55" maxlength="64" value="<?php echo cer_dbc(@$queue_data["queue_email_display_name"]); ?>"><br>
              <span class="cer_footer_text">(for example: &quot;XYZ, Inc. Support&quot; or &quot;XYZ, LLC. Sales&quot;.  Leave blank for normal email address display)<?php /* echo  LANG_CONFIG_QUEUE_EMAIL_DISPLAY_NAME_IE */ ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_QUEUE_EDIT_PREFIX ?>:</td>
            <td width="81%">
              <input type="text" name="queue_prefix" size="20" maxlength="32" value="<?php echo  cer_dbc(@$queue_data["queue_prefix"]); ?>">
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_QUEUE_EDIT_PREFIX_IE ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading">&nbsp;</td>
            <td width="81%">&nbsp;</td>
          </tr>
          
          <tr class="boxtitle_gray_glass_dk"> 
            <td colspan="2">Autoresponse Template Tokens:</td>
          </tr>
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_text">
			              You can use the following tokens in both the auto-open and auto-close response templates:<br>
			              <B>##ticket_id##</B> - Ticket ID or Mask<br>
			              <B>##ticket_subject##</B> - Ticket Subject<br>
			              <B>##ticket_status##</B> - Ticket Status (new, resolved, etc.)<br>
			              <B>##ticket_owner##</B> - Ticket Owner User Name<br>
			              <B>##ticket_due##</B> - Initial Ticket Due Date according to SLA plan/Queue &amp; Schedule<br>
			              <B>##ticket_time_worked##</B> - The Amount of Agent/Technician Time Spent on this Ticket<br>
			              <B>##ticket_email##</B> - The Email Body of the Original Ticket Message<br>
			              <br>
			              <B>##queue_name##</B> - Ticket Queue Name (Support, etc.)<br>
			              <B>##queue_hours##</B> - Weekly Business Hours this Queue is Staffed (default or SLA override) <br>
			              <B>##queue_response_time##</B> - Queue's Target Response Time in Business Hours (default or SLA override) <br>
			              <br>
			              <B>##sla_name##</B> - The Sender's SLA plan (if any, otherwise returns "none").<br>
			              <B>##contact_name##</B> - The Sender's Full Name (if stored in Contacts, otherwise blank).<br>
			              <B>##requester_address##</B> - The Sender's E-mail Address that Opened the Ticket.<br>
			              <B>##company_name##</B> - The Sender's Company Name (if stored in Contacts, otherwise blank).<br>
			              <B>##company_acct_num##</B> - Company Account Number (if stored in Contacts, otherwise blank).<br>
			            </td>
					</tr>
				</table>
            </td>
          </tr>
          </tr>
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	<?php echo  LANG_CONFIG_QUEUE_EDIT_NEW ?>: <!--- (queue default, sender as no SLA coverage): --->
			            </td>
			        </tr>
			    </table>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
              <input type="checkbox" name="queue_send_open" value="1"<?php if(@$queue_data["queue_send_open"]) { echo " checked"; } ?>>
              <span class="cer_maintable_heading"> Enable New Ticket Auto Response</span>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
              <textarea name="queue_response_open" cols="80" rows="10"><?php echo  cer_dbc(@$queue_data["queue_response_open"]); ?></textarea><br>
            </td>
          </tr>
          
          </tr>
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	<?php echo  LANG_CONFIG_QUEUE_EDIT_CLOSED ?>:
			            </td>
			        </tr>
			    </table>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
              <input type="checkbox" name="queue_send_closed" value="1"<?php if(@$queue_data["queue_send_closed"]) { echo " checked"; } ?>>
              <span class="cer_maintable_heading"> Enable Ticket Resolved Auto Response</span>
            </td>
          </tr>
          <tr> 
            <td colspan="2" >
              <textarea name="queue_response_close" cols="80" rows="10"><?php echo cer_dbc(@$queue_data["queue_response_close"]); ?></textarea><br>
            </td>
            
          </tr>
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr class="boxtitle_blue_glass"> 
            <td colspan="2">Public Queue Access:</td>
          </tr>
          
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		        	<tr>
			        	<td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	Queue Mode:
			            </td>
			        </tr>
			    </table>
	            <table border="0" cellspacing="0" cellpadding="2" width="100%">
		            <tr>
			            <td bgcolor="#DDDDDD">
			              <select name="queue_mode">
			              	<option value="0" <?php if (@$queue_data["queue_mode"]==0) echo "SELECTED"; ?>>Open (Open to all Clients)
			              	<option value="1" <?php if (@$queue_data["queue_mode"]==1) echo "SELECTED"; ?>>Gated (Restricted by SLA)
			              </select>
			              <br>
			              <span class="cer_footer_text">
			              <b>Open</b> queues are available for use by any requester.<br>
			              <b>Gated</b> queues are restricted by Service Level Agreement (SLA) plans that enable access by company (e.g., Priority/Paid Support, etc.)</span><br>
			              <br>
			            </td>
					</tr>
				</table>
				
            </td>
          </tr>

          <tr>
          	<td colspan="2" bgcolor="#DDDDDD">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	Queue Service Level Defaults:
			            </td>
			        </tr>
			    </table>
	            <table border="0" cellspacing="0" cellpadding="2">
		            <tr>
			            <td bgcolor="#DDDDDD" class="cer_maintable_text">
			            	If a client (requester) does not have a Service Level Agreement (SLA) plan covering this queue 
			            	with a guaranteed response time and defined hours, the following defaults will be used to 
			            	automatically manage due dates on new tickets and replies.
			            </td>
			        </tr>
		            <tr>
			            <td bgcolor="#DDDDDD">
    		            	<span class="cer_maintable_heading">Default Schedule:</span>
	        				<select name="queue_default_schedule">
            					<option value="0"> - none - 
            					<?php foreach($sched_handler->schedules as $sched) { ?>
            						<option value="<?php echo $sched->schedule_id; ?>" <?php if(@$queue_data["queue_default_schedule"] == $sched->schedule_id) echo "SELECTED"; ?>><?php echo $sched->schedule_name; ?>
            					<?php } ?>
            				</select>
			            </td>
			         </tr>
			         <tr>
			            <td bgcolor="#DDDDDD">
			            	<span class="cer_maintable_heading">Default Response Time Target:</span>
			            	<input type="text" name="queue_default_response_time" value="<?php echo @$queue_data["queue_default_response_time"]; ?>" size="2" maxlength="3"> business hours
			            </td>
			        </tr>
			    </table>
          	</td>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr>
          	<td colspan="2" bgcolor="#DDDDDD">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	E-mail Template (if 'Gated'):
			            </td>
			        </tr>
			    </table>
          	</td>
          </tr>
          
          <tr> 
            <td colspan="2" class="cer_maintable_text" bgcolor="#DDDDDD">The following template is only used if the Queue Mode is 'Gated'.  If Cerberus receives 
            e-mail from a sender who isn't authorized by a service-level agreement (SLA) plan to use this queue, this e-mail template 
            will be sent back to the sender and no ticket will be created.  This template should include details on how to enable 
            (purchase/renew/etc.) an appropriate SLA plan.<br>
            <br>
            </td>
          </tr>
          
          <tr> 
            <td colspan="2" bgcolor="#DDDDDD">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_text">
			              You can use the following tokens in your Access Denied response:<br>
			              <B>##email_subject##</B> - Subject of the Unauthorized E-mail<br>
			              <B>##email_to##</B> - Destination Queue E-mail Address of the Unauthorized E-mail<br>
			              <B>##email_sender##</B> - Sender E-mail Address of the Unauthorized E-mail<br>
			              <B>##email_date##</B> - Date of the Unauthorized E-mail<br>
			              <B>##email_body##</B> - Content of the Unauthorized E-mail<br>
			            </td>
					</tr>
				</table>
            </td>
          </tr>
          
          <tr> 
            <td colspan="2">
              <textarea name="queue_response_gated" cols="80" rows="10"><?php echo  cer_dbc(@$queue_data["queue_response_gated"]); ?></textarea><br>
            </td>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          
        </table>
    </td>
  </tr>
  
	<tr class="boxtitle_gray_glass_dk"> 
		<td colspan="2">Agent Group Access to this Queue:</td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td colspan="2">
		<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
			<tr>
				<td bgcolor="#888888" class="cer_maintable_header">Group</td>
				<td bgcolor="#888888" class="cer_maintable_header">Queue Access</td>
			</tr>
			<?php
			$sql = "SELECT g.group_id, g.group_name, qa.queue_access ".
				"FROM user_access_levels g ".
				"LEFT JOIN queue_group_access qa ON (g.group_id = qa.group_id AND qa.queue_id = $qid) ".
				"ORDER BY g.group_name";
			$res = $cerberus_db->query($sql);

			if($cerberus_db->num_rows($res))					
			while($row = $cerberus_db->fetch_row($res))
			{
				$access = $row["queue_access"];
				$group_name = stripslashes($row["group_name"]);
				$gid = $row["group_id"];
			?>					
          <tr> 
            <td nowrap class="cer_maintable_heading" bgcolor="#CCCCCC"><?php echo @htmlspecialchars($group_name, ENT_QUOTES, LANG_CHARSET_CODE); ?>:</td>
            <td class="cer_maintable_text" bgcolor="#DDDDDD">						
  				<input type="radio" name="gaccess_<?php echo $gid; ?>" value="read"<?php if($access=="read") {echo " checked";} ?>><?php echo LANG_WORD_READ ?>
				<input type="radio" name="gaccess_<?php echo $gid; ?>" value="write"<?php if($access=="write") {echo " checked";} ?>><?php echo LANG_WORD_WRITE ?>
				<input type="radio" name="gaccess_<?php echo $gid; ?>" value="none"<?php if($access=="none" || empty($access)) {echo " checked";} ?>><?php echo LANG_WORD_NONE ?>
				<input type="hidden" name="glist[]" value="<?php echo $gid; ?>">
			</td>
          </tr>
          
			<?php
			}
			?>
		</table>
		<br>
	</td>
	</tr>
	
	<tr class="boxtitle_green_glass">
  	<td>
    	Unique E-mail Addresses Assigned to this Queue:
    </td>
  </tr>
	<?php
	// [JAS]: \todo This should really be using cer_Queue.
  if(!empty($queue_data["queue_id"]))
  	{
  	$sql = "SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain ".
    	"FROM queue_addresses qa ".
      "WHERE qa.queue_id = " . $queue_data["queue_id"] . " " .
      "ORDER BY qa.queue_domain, qa.queue_address;";
    $queue_boxes_res = $cerberus_db->query($sql);
    }
  ?>
  <tr bgcolor="#DDDDDD">
  	<td>
    	<table cellpadding="2" cellspacing="1" border="0">
      	<tr bgcolor="#888888" class="cer_maintable_header">
        	<td>Address</td>
        	<td align="center">Delete</td>
        </tr>
      	<?php
        if(!empty($queue_data["queue_id"]) && $cerberus_db->num_rows($queue_boxes_res) > 0)
          {
          while($queue_box = $cerberus_db->fetch_row($queue_boxes_res))
          	{
            ?>
            <tr>
            	<td class="cer_maintable_heading"><?php echo $queue_box["queue_address"]; ?>@<?php echo $queue_box["queue_domain"]; ?></td>
            	<td align="center"><input type="checkbox" name="queue_addresses[]" value="<?php echo $queue_box["queue_addresses_id"]; ?>"></td>
            </tr>
            <?php
	        }
          }
        ?>
      	<tr>
        	<td>
          <span class="cer_maintable_heading">Add: </span><input type="text" name="queue_address" size="15" maxlength="128"><span class="cer_maintable_heading">@</span><input type="text" name="queue_domain" size="25" maxlength="128">
          <span class="cer_footer_text"> <?php echo  LANG_CONFIG_QUEUE_EDIT_ADDRESS_IE ?></span>
          <br>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  
	<tr class="boxtitle_blue_glass_pale">
  	<td>
    	Subqueues:
    </td>
  </tr>
  <tr bgcolor="#DDDDDD">
  	<td>
  		<span class="cer_maintable_text"><i>Optional.</i>  This feature allows you to use this queue as a 
  		folder without assigning unique e-mail addresseses.  This option will share the queue address list 
  		from the specified queue when replying, while still keeping tickets separate.
  		For example: if you want to sort sales leads into multiple queues for workflow, but want all replies to come from 
  		sales@yourcompany.com, simply set all your sales queues to share the main 'Sales' queue addresses.</span>
  		<br>
  		<span class="cer_maintable_heading">Parent Queue: </span>
  		<select name="queue_addresses_inherit_qid">
  		<option value="0">- none -
  		<?php
  		foreach($queue_handler->queues as $q) {
  			if(0 == $q->queue_addresses_inherit_qid) {
	  			echo sprintf("<option value='%s' %s>%s",
	  					$q->queue_id,
	  					($queue_data["queue_addresses_inherit_qid"] == $q->queue_id) ? "SELECTED" : "",
	  					$q->queue_name
	  				);
  			}
  		}
  		?>
  		</select>
  	</td>
  </tr>
  
  <?php
  if($session->vars["login_handler"]->is_xsp_user)
  {
  ?>
  <tr>
  	<td bgcolor="#E93700" class="cer_maintable_header">
    	XSP Settings (xsp user only)
    </td>
  </tr>
  <tr> 
  	<td bgcolor="#DDDDDD">
	   	<table cellpadding="2" cellspacing="1" border="0">
			<tr>
			    <td width="19%" class="cer_maintable_heading">Send XSP Ticket Summaries:</td>
			    <td width="81%">
			      <input type="checkbox" name="queue_core_update" value="1"<?php if(@$queue_data["queue_core_update"]) { echo " checked"; } ?>>
			      <br>
			      <span class="cer_footer_text">Checking this box allows the Cerberus XSP GUI (if enabled) to receive 
			      updates about the tickets in this queue.</span></td>
			</tr>
		</table>
	</td>
  <?php
  }
  else 
  	echo "<input type=\"hidden\" name=\"queue_core_update\" value=\"".((@$queue_data["queue_core_update"])?"1":"0")."\">";
  ?>
  
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
