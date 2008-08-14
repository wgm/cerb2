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

require_once(FILESYSTEM_PATH . 'includes/cerberus-api/queue_access/cer_queue_access.class.php');
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");

if(!$priv->has_priv(ACL_SLA_SCHEDULES,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

//if(!isset($pslid))
//	{ echo LANG_KB_NO_CATID; exit(); }	

$sched_handler = new cer_ScheduleHandler();

// [JAS]: either empty object for create or a pointer below for edit
$sched_ptr = new cer_Schedule();

if(!empty($pslid) && isset($sched_handler->schedules[$pslid])) {
	$sched_ptr = &$sched_handler->schedules[$pslid];
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pslid" value="<?php echo $pslid; ?>">
<input type="hidden" name="module" value="schedules">
<input type="hidden" name="form_submit" value="schedule_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
  <tr class="boxtitle_orange_glass"> 
<?php
if(empty($pslid)) {
    ?><td>Create SLA Schedule</td><?php
}
else {
    ?><td>Edit SLA Schedule '<?php echo @htmlspecialchars($sched_ptr->schedule_name) ?>'</td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Schedule Name:</td>
            <td width="81%">
              <input type="text" name="schedule_name" size="32" maxlength="64" value="<?php echo @htmlspecialchars($sched_ptr->schedule_name); ?>">
              <span class="cer_footer_text">Such as: "Standard Hours"</span></td>
          </tr>
          <tr> 
            <td colspan="2">
            	
            	<table border="0" cellspacing="1" cellpadding="1" width="100%" bgcolor="#FFFFFF">
            		<tr class="boxtitle_gray_glass_dk">
            			<td colspan="2">Hours:</td>
            		</tr>
            		
            		<tr bgcolor="#999999" class="cer_maintable_header">
            			<td>Day</td>
            			<td>SLA Hours</td>
            		</tr>
            		
            		<?php
            		$dno = 0;
            		foreach($sched_handler->days as $dn => $da) {
            		?>
            		
            		<tr bgcolor="#DDDDDD">
            			<td class="cer_maintable_heading"><?php echo $dn; ?></td>
            			<td class="cer_maintable_text">
            				<input type="radio" name="<?php echo $da; ?>_hrs" value="24hrs" <?php if($sched_ptr->weekday_hours[$dno]->hrs == "24hrs") echo "CHECKED"; ?>>24 Hours
            				<input type="radio" name="<?php echo $da; ?>_hrs" value="closed" <?php if($sched_ptr->weekday_hours[$dno]->hrs == "closed") echo "CHECKED"; ?>>Closed
            				<input type="radio" name="<?php echo $da; ?>_hrs" value="custom" <?php if($sched_ptr->weekday_hours[$dno]->hrs == "custom") echo "CHECKED"; ?>>Custom: 
            			
            				<select name="<?php echo $da; ?>_open">
            					<?php foreach($sched_handler->times_opt as $t24 => $t12) { ?>
            						<option value="<?php echo $t24; ?>" <?php if($t24==$sched_ptr->weekday_hours[$dno]->open) echo "SELECTED"; ?>><?php echo $t12; ?>
            					<?php } ?>
            				</select>&nbsp;- 
            				<select name="<?php echo $da; ?>_close">
            					<?php foreach($sched_handler->times_opt as $t24 => $t12) { ?>
            						<option value="<?php echo $t24; ?>" <?php if($t24==$sched_ptr->weekday_hours[$dno]->close) echo "SELECTED"; ?>><?php echo $t12; ?>
            					<?php } ?>
            				</select>
            			</td>
            		</tr>
            		
            		<?php 
            			$dno++;
            		}
            		?>
            		
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
