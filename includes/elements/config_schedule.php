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
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/languages.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");

if(!$priv->has_priv(ACL_SLA_SCHEDULES,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$sched_handler = new cer_ScheduleHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<script>
	function verifyScheduleDelete()
	{
		if(confirm("Are you sure you want to permanently delete the selected schedules?"))
			return true;
		
		return false;
	}
</script>

<form action="configuration.php" method="post" onsubmit="javascript:return verifyScheduleDelete();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="schedules">
<input type="hidden" name="form_submit" value="schedule_delete">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
  <tr class="cer_maintable_text"> 
    <td class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
        
		  <tr class="boxtitle_orange_glass"> 
			<td colspan="2" colspan="2">SLA Schedules</td>
		  </tr>
		  
  <tr class="cer_maintable_text"> 
    <td colspan="2" align="left" bgcolor="#EEEEEE" class="cer_maintable_text"> 
  		<a href="<?php echo cer_href("configuration.php?module=schedules&pslid=0"); ?>" class="cer_maintable_subjectLink">Create New Schedule</a><br>
  		<br>
  	</td>
  </tr>
		  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="2" align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		<B>Explanation:</B> Schedules are used to determine the working hours of your queues.  SLA plans depend 
  		on the schedules defined here to set due dates based on business hours accordingly.  For example, in one queue you 
  		may want to provide replies 24/7, while in another you may field tickets from 9am to 5pm Monday through Friday.  The 
  		schedules created here are reusable throughout your SLA plans and queues.
  	</td>
  </tr>
  
  <tr bgcolor="#999999"> 
    <td width="1%" align="left" class="cer_maintable_header" nowrap>Delete</td>
    <td width="99%" align="left" class="cer_maintable_header" nowrap>Schedule</td>
  </tr>
	<?php
	
	foreach($sched_handler->schedules as $idx => $schedule)
		{
		?>
		<tr class="cer_maintable_text">
		    <td width="1%" align="center" valign="middle" bgcolor="#EAEAEA" class="cer_maintable_text" nowrap><input type="checkbox" name="sids[]" value="<?php echo $schedule->schedule_id; ?>"></td>
  			
		    <td width="99%" align="left" bgcolor="#EAEAEA" class="cer_maintable_text">
  				
  				<br>
  				
  				<table border="0" cellspacing="1" cellpadding="3" bgcolor="#000000">
  					<tr class="boxtitle_green_glass">
  						<td colspan="7"><a href="<?php echo cer_href("configuration.php?module=schedules&pslid=" . $schedule->schedule_id); ?>" class="cer_white_link"><?php echo @htmlspecialchars(stripslashes($schedule->schedule_name)); ?></a></td>
  					</tr>
  					<tr>
		  				<?php foreach($schedule->weekday_hours as $dn => $day) { 
		  					if($dn == 0 || $dn == 6) {
		  						$hdr_bgcolor = "#CCCCCC";
		  					}
		  					else {
		  						$hdr_bgcolor = "#DDDDDD";
		  					}
	  					?>
	  						<td class="cer_maintable_heading" bgcolor="<?php echo $hdr_bgcolor; ?>" align="center"><?php echo $day->day_abbrev; ?></td>
		  				<?php }	?>
  					</tr>
  					<tr>
		  				<?php foreach($schedule->weekday_hours as $dn => $day) { 
		  					if($dn == 0 || $dn == 6) {
		  						$day_bgcolor = "#DDDDDD";
		  					}
		  					else {
		  						$day_bgcolor = "#F5F5F5";
		  					}
		  				?>
	  						<td class="cer_footer_text" bgcolor="<?php echo $day_bgcolor; ?>" align="center">
	  							<?php
	  							switch($day->hrs) {
	  								case "custom":
										echo $sched_handler->times_opt[$day->open] . ' -<br>' . $sched_handler->times_opt[$day->close];
	  									break;
	  								case "closed":
	  									echo "closed";
	  									break;
	  								case "24hrs":
	  									echo "24hrs";
	  									break;
	  							}
	  							?>
	  						</td>
		  				<?php }	?>
  					</tr>
  				</table>
  				
  				<br>
  				
  			</td>
  			
		</tr>
  		<?php
		}
  		?>
  		
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td colspan="2" align="left">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>">
			</td>
		</tr>
		  
        </table>
    </td>
  </tr>
</table>
</form>
<br>
