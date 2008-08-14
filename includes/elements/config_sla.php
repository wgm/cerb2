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
| File: config_sla.php
|
| Purpose: This config include handles service level agreement
|	plans.
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
require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

if(!$priv->has_priv(ACL_SLA_PLANS,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$sched_handler = new cer_ScheduleHandler();
$sla_handler = new cer_SLA();
$queue_handler = new cer_QueueHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<script>
	function verifySLADelete()
	{
		if(confirm("Are you sure you want to permanently delete the selected SLA plans?"))
			return true;
		
		return false;
	}
</script>

<form action="configuration.php" method="post" onsubmit="javascript:return verifySLADelete();">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="sla">
<input type="hidden" name="form_submit" value="sla_delete">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
  <tr class="cer_maintable_text"> 
    <td class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
        
		  <tr class="boxtitle_orange_glass"> 
			<td colspan="2" colspan="2">Service Level Agreement Plans</td>
		  </tr>
		  
  <tr class="cer_maintable_text"> 
    <td colspan="2" align="left" bgcolor="#EEEEEE" class="cer_maintable_text"> 
  		<a href="<?php echo cer_href("configuration.php?module=sla&pslid=0"); ?>" class="cer_maintable_subjectLink">Create SLA Plan</a><br>
  		<br>
  	</td>
  </tr>
		  
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td colspan="2" align="left" bgcolor="#DDDDDD" class="cer_maintable_text"> 
  		<B>Explanation:</B> Service Level Agreement (SLA) plans document an enhanced level of support given to a particular company as a contract, 
  		usually providing quicker, guaranteed response times for a fee or to meet the customer's requirements.  SLAs are used in service-related 
  		fields such as Customer Service and Technical Support.  SLA Plans in Cerberus are used to give precedence to tickets from certain 
  		companies w/ SLA plans. Cerberus helps to enforce guaranteed response times by automatically managing ticket due dates. You may also restrict 
  		certain queues (called 'Gated' queues) to customers who have an appropriate SLA plan (e.g., for paid support). If providing SLA options 
  		to your customers isn't relevant for your business, you can simply ignore this section.
  	</td>
  </tr>
  
  <tr class="boxtitle_gray_glass_dk"> 
    <td width="1%" align="left" nowrap>Delete</td>
    <td width="99%" align="left" nowrap>SLA Plan Name</td>
  </tr>
	<?php
	
	foreach($sla_handler->plans as $plan_id => $plan)
		{
		?>
		  <tr class="cer_maintable_text">
		  
		    <td width="1%" align="center" valign="middle" bgcolor="#EAEAEA" class="cer_maintable_text" nowrap><input type="checkbox" name="sids[]" value="<?php echo $plan_id; ?>"></td>
  			
		    <td width="99%" align="left" bgcolor="#EAEAEA" class="cer_maintable_text">

		    	<br>
		    
  				<table border="0" cellspacing="1" cellpadding="3" bgcolor="#000000">

  					<tr class="boxtitle_green_glass">
  						<td colspan="4"><a href="<?php echo cer_href("configuration.php?module=sla&pslid=" . $plan_id); ?>" class="cer_white_link"><?php echo @htmlspecialchars(stripslashes($plan->sla_name)); ?></a></td>
  					</tr>
  				
  					<tr bgcolor="#CCCCCC">
	  					<td class="cer_maintable_headingSM">Queue</td>
	  					<td class="cer_maintable_headingSM">Queue Mode</td>
	  					<td class="cer_maintable_headingSM">SLA Schedule</td>
	  					<td class="cer_maintable_headingSM">Target Response Time</td>
	  				</tr>
  				
  				<?php foreach($plan->queues as $qid => $q) { ?>
  				
  					<tr bgcolor="#F5F5F5">
  						<td class="cer_maintable_heading"><?php echo $q->queue_name; ?></td>
  						<td class="cer_maintable_text"><?php echo $q->queue_mode; ?></td>
  						<td class="cer_maintable_text"><a href="<?php echo cer_href("configuration.php?module=schedules"); ?>" class="cer_maintable_text"><?php echo @htmlspecialchars($q->queue_schedule_name); ?></a></td>
  						<td class="cer_maintable_text" align="center"><?php echo $q->queue_response_time; ?> hrs</td>
  					</tr>
  				
  				<?php } ?>
  				</table>
  				
  				<br>
  				
  			</td>
  			
  			</tr>
  			
  		<?php } ?>
  		
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
