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
| File: config_queues.php
|
| Purpose: The configuration include for configuring and deleting queue 
| 		e-mail addresses and properties.
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

if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
if(!$priv->has_priv(ACL_QUEUE_EDIT,BITGROUP_1) && !$priv->has_priv(ACL_QUEUE_DELETE,BITGROUP_1))
	{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
	}

$u_qids = $queue_access->get_writeable_qid_list();

$sql = "SELECT q.queue_id, q.queue_name, count(qa.queue_addresses_id) as num_addresses, q.queue_mode, q.queue_default_schedule, q.queue_default_response_time ".
	"FROM queue q ".
  	"LEFT JOIN queue_addresses qa ON (q.queue_id = qa.queue_id) ".
	"WHERE q.queue_id IN ($u_qids) ".
  	"GROUP BY q.queue_id ".
	"ORDER BY q.queue_name ASC";
$result = $cerberus_db->query($sql);

$sched_handler = new cer_ScheduleHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="qid" value="<?php echo  @$queue_data["queue_id"]; ?>">
<input type="hidden" name="module" value="queues">
<input type="hidden" name="form_submit" value="queues_delete_confirm">
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td><?php echo  LANG_CONFIG_QUEUE_TITLE ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
			<?php if($priv->has_priv(ACL_QUEUE_CREATE,BITGROUP_1)) { ?><a href="<?php echo cer_href("configuration.php?module=queues&pqid=0"); ?>" class="cer_maintable_subjectLink"><?php echo  LANG_CONFIG_QUEUE_CREATE ?></a><br><br><?php } ?>
	
			<table border="0" cellpadding="3" cellspacing="1" width="100%" bgcolor="#FFFFFF">
			
				<tr bgcolor="#666666">
					<td align="center" valign="middle" class="cer_maintable_header">Delete</td>
					<td class="cer_maintable_header">Queue</td>
				</tr>
			
			<?php
			$i = 0;
			while($row = $cerberus_db->fetch_row($result))
				{
					$sql = "SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain ".
						"FROM queue_addresses qa ".
						"WHERE qa.queue_id = " . $row["queue_id"] . " " .
						"ORDER BY qa.queue_address, qa.queue_domain;";
					$queue_boxes_res = $cerberus_db->query($sql);
				?>
				
				<tr bgcolor="#DDDDDD">
					<td valign="middle" align="center"><?php if($priv->has_priv(ACL_QUEUE_DELETE,BITGROUP_1)) { echo "<input type=\"checkbox\" name=\"qids[" . $row["queue_id"] . "]\" value=\"" . $row["queue_id"] . "\"> "; } ?></td>
					<td>
						
						<table border="0" cellspacing="1" cellpadding="3" bgcolor="#000000" width="100%">
							<tr class="boxtitle_green_glass">
								<td colspan="3"><a href="<?php echo cer_href("configuration.php?module=queues&pqid=" . $row["queue_id"]); ?>" class="cer_white_link"><?php echo @htmlspecialchars(stripslashes($row["queue_name"])); ?></a></td>
							</tr>
							<tr bgcolor="#CCCCCC">
								<td width="10%" class="cer_maintable_headingSM">Mode</td>
								<td width="60%" class="cer_maintable_headingSM">Schedule (default)</td>
								<td width="30%" align="center" class="cer_maintable_headingSM">Response Target (default)</td>
							</tr>
							<tr bgcolor="#EEEEEE">
								<td class="cer_maintable_text"><?php echo (($row["queue_mode"]) ? "Gated" : "Open"); ?></td>
								<td class="cer_maintable_text"><?php echo (($row["queue_default_schedule"]) ? $sched_handler->schedules[$row["queue_default_schedule"]]->schedule_name : "not set" ); ?></td>
								<td align="center" class="cer_maintable_text"><?php echo (($row["queue_default_response_time"]) ? $row["queue_default_response_time"] . " hrs" : "not set"); ?></td>
							</tr>
							<tr bgcolor="#FFFFFF">
								<td colspan="3" class="cer_footer_text"><b>Address<?php echo (($row["num_addresses"] == 1) ? "" : "es") . " (" . $row["num_addresses"] . ")"; ?>:</b> 
								
									<?php
										  echo "<span class=\"cer_footer_text\">";
								          if($cerberus_db->num_rows($queue_boxes_res) > 0)
								          {
								            $x = 1;
								            while($queue_box = $cerberus_db->fetch_row($queue_boxes_res)) {
								            	echo $queue_box["queue_address"] . "@" . $queue_box["queue_domain"];
								              if($x != $cerberus_db->num_rows($queue_boxes_res)) echo ", ";
								              $x++;
								            }
								            echo "</span>";
								          }
									?>
								</td>
							</tr>
						</table>
						
					</td>
				</tr>
					
				<?php
					$i++;	
				}
				?>
				
			</table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
				<?php if($priv->has_priv(ACL_QUEUE_DELETE,BITGROUP_1)) {?><input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>"><?php } ?>&nbsp;
		</td>
	</tr>
</table>
<br>
