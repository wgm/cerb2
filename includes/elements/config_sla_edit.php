<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_sla_edit.php
|
| Purpose: The configuration include for creating and editing service
|		level agreement plans.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

if(!$priv->has_priv(ACL_SLA_PLANS,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$sched_handler = new cer_ScheduleHandler();
$sla_handler = new cer_SLA(array($slid));
$queue_handler = new cer_QueueHandler();

$sla_plan = new cer_SLAPlan();


// [JAS]: If the plan exists, use a pointer.  Otherwise the blank template above shows for new.
if(isset($sla_handler->plans[$slid])) {
	$sla_plan = &$sla_handler->plans[$slid];
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pslid" value="<?php echo $pslid; ?>">
<input type="hidden" name="module" value="sla">
<input type="hidden" name="form_submit" value="sla_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
  <tr class="boxtitle_orange_glass"> 
<?php
if($slid==0) {
    ?><td>Create Service Level Agreement Plan</td><?php
}
else {
    ?><td>Edit SLA Plan '<?php echo @htmlspecialchars($sla_plan->sla_name); ?>'</td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Plan Name:</td>
            <td width="81%">
              <input type="text" name="sla_name" size="32" maxlength="64" value="<?php echo @htmlspecialchars($sla_plan->sla_name); ?>">
              <span class="cer_footer_text">Such as: "Corporate Support Package"</span></td>
          </tr>
          <tr> 
            <td colspan="2">
            	
            	<table border="0" cellspacing="1" cellpadding="1" width="100%" bgcolor="#FFFFFF">
            		<tr class="boxtitle_green_glass">
            			<td colspan="5">Plan Includes:</td>
            		</tr>
            		
            		<tr class="boxtitle_gray_glass_dk">
            			<td align="center" nowrap>Include</td>
            			<td>Queue</td>
            			<td>Queue Mode</td>
            			<td>SLA Response Schedule</td>
            			<td align="center" nowrap>Response (hrs)*</td>
            		</tr>
            		
            		<?php
            		$row = 0;
            		
					foreach($queue_handler->queues as $queue)
            		{
            			$qid = $queue->queue_id;
            			
            			if($row % 2) $row_bgcolor = "#DFDFDF";
            			else $row_bgcolor = "#EAEAEA";
            		?>
            		
            		<tr bgcolor="<?php echo $row_bgcolor; ?>">
            			<td align="center" nowrap><input type="checkbox" name="qids[]" value="<?php echo $qid; ?>" <?php if(isset($sla_plan->queues[$qid])) echo "CHECKED"; ?>></td>
            			<td class="cer_maintable_heading"><?php echo $queue->queue_name; ?></td>
            			<td class="cer_maintable_text"><?php echo (($queue->queue_mode) ? "Gated" : "Open"); ?></td>
            			<td>
            				<select name="q<?php echo $qid; ?>_schedule">
            					<option value="0"> - none - 
            					<?php foreach($sched_handler->schedules as $sched) { ?>
            						<option value="<?php echo $sched->schedule_id; ?>" <?php if(@$sla_plan->queues[$qid]->queue_schedule_id == $sched->schedule_id) echo "SELECTED"; ?>><?php echo $sched->schedule_name; ?>
            					<?php } ?>
            				</select>
            			</td>
            			<td align="center" nowrap><input type="text" name="q<?php echo $qid; ?>_response_time" size="3" maxlength="3" value="<?php echo @$sla_plan->queues[$qid]->queue_response_time; ?>"></td>
            		</tr>

            		<?php
            			$row++;
            		}
            		?>
            		
            		<tr>
            			<td colspan="5" class="cer_maintable_header" bgcolor="#999999">* SLA Guaranteed Response Time (in business hours using Schedule).</td>
            		</tr>
            		
            		<tr>
            			<td colspan="5" class="cer_maintable_text" bgcolor="#DDDDDD">
            				<b>Note:</b> Queues not included in the plan will be handled by their default settings. <b>Open</b> queues are available for 
            				use by any requester, and <b>Gated</b> queues are restricted to SLA plans that enable them by company (e.g., Priority Support, 
            				Pager Access, etc.)
            			</td>
            		</tr>
            		
            	</table>
            	
			</td>
          </tr>
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
