<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC 
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

require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
if(!$priv->has_priv(ACL_QUEUE_CATCHALL))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$queue_handler = new cer_QueueHandler();

$sql = "SELECT c.catchall_id, c.catchall_name, c.catchall_pattern, c.catchall_to_qid, c.catchall_order ".
	"FROM queue_catchall c ".
	"ORDER BY c.catchall_order";
$result = $cerberus_db->query($sql);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr class="boxtitle_orange_glass"> 
    <td>Queue Catch-All Rules</td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td align="left" bgcolor="#DDDDDD" class="cer_maintable_text">
    
    	<span class="cer_maintable_text">Queue Catch-All Rules will handle e-mail that reaches your helpdesk but isn't 
    	assigned to any particular queue.  Your rules can use powerful regular expressions to assign particular addresses or 
    	patterns to a specific queue.  This is very helpful if your customers are mistyping your e-mail addresses 
    	or you simply don't feel like adding every possible e-mail combination to your queues.  The 'order' field allows 
    	you to decide in what order catch-all rules are checked.  The parser stops as soon as the first match is found.<br>
    	<b>Note:</b> <i>Make sure you have a valid e-mail address (or shared queue addresses) assigned to your destination queue (dropbox).  
    	If you're only using shared addresses on your dropbox, the queue owning the e-mail address will pick up the message.</i><br>
    	<br>
    	Some examples you can use are given below:<br>
    	<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
    		<tr>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Pattern</td>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Effect</td>
    		</tr>
    		<tr bgcolor="#DDDDDD">
    			<td class="cer_maintable_heading">/^(.*)$/</td>
    			<td class="cer_maintable_text">matches anything (period)</td>
    		</tr>
    		<tr bgcolor="#DDDDDD">
    			<td class="cer_maintable_heading">/^(.*)(\@)(.*)$/</td>
    			<td class="cer_maintable_text">matches anything@anything</td>
    		</tr>
    		<tr bgcolor="#DDDDDD">
    			<td class="cer_maintable_heading">/^(.*)(\@webgroupmedia\.com)$/i</td>
    			<td class="cer_maintable_text">matches anything@webgroupmedia.com, case-insensitive</td>
    		</tr>
    		<tr bgcolor="#DDDDDD">
    			<td class="cer_maintable_heading">/^(sales\@)(.*)$/i</td>
    			<td class="cer_maintable_text">matches sales@anything, case-insensitive</td>
    		</tr>
    	</table>
    	
    	<br>
    	Read more about regular expression <a href="http://us2.php.net/preg_match" target="_blank" class="cer_maintable_heading">pattern syntax here</a>.
    	</span>
    </td>
   </tr>

<form action="configuration.php?module=queue_catchall" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="queue_catchall">
<input type="hidden" name="form_submit" value="catchall_add">
  
  <tr class="boxtitle_gray_glass_dk">
  	<td colspan="1">
  		Add New Catch-All Rule:
  	</td>
  </tr>
  
	<tr bgcolor="#DDDDDD">
		<td>
		
	    	<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
	    		<tr>
	    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Rule Nickname</td>
	    			<td class="cer_maintable_header" bgcolor="#BBBBBB">When Destination Pattern Matches...</td>
	    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Deliver to Queue</td>
	    		</tr>
	    		<tr bgcolor="#DDDDDD">
	    			<td><input type="text" name="catchall_name" size="20" maxlength="64"></td>
	    			<td><input type="text" name="catchall_pattern" size="45" maxlength="128"></td>
	    			<td>
	    				<select name="catchall_to_qid">
							<?php 
							foreach($queue_handler->queues as $queue) { 
								echo sprintf("<option value='%d' %s>%s",
										$queue->queue_id,
										(0) ? "selected" : "",
										$queue->queue_name
									);
							}
	    					?>
	    				</select>
	    			</td>
    			</tr>
    			
				<tr bgcolor="#B0B0B0" class="cer_maintable_text">
					<td colspan="3" align="left">
						<input type="submit" class="cer_button_face" value="<?php echo LANG_WORD_CREATE ?>">&nbsp;
					</td>
				</tr>
				</form>				
				
			</table>
			
			<br>
			
	    	<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
				<form>
    			<tr class="boxtitle_green_glass">
	    			<td>Quick Regexp Pattern Tester</td>
    			</tr>
    			
    			<tr>
    				<td bgcolor="#DDDDDD">
    					 <span class="cer_maintable_heading">Pattern: </span>
    					 <input type="text" name="regexp_tester_pattern" size="45" value="" onfocus="javascript:clearRegexpResult(this.form);">
    					 <br>
    					 <span class="cer_maintable_heading">Destination E-mail Address: </span>
    					 <input type="text" name="regexp_tester_subject" size="35" value="" onfocus="javascript:clearRegexpResult(this.form);">
    					 <br>
    					 <input type="button" value="Test!" onclick="javascript:testRegexp(this.form);" class="cer_button_face">
    					 <span class="cer_maintable_heading">Result: </span>
    					 <input type="text" name="regexp_tester_result" size="5" value="">
    				</td>
    			</tr>
    			</form>

    		</table>

			<script>
			function clearRegexpResult(f) {
				f.regexp_tester_result.value = "";
			}
			
			function testRegexp(f) {
				// [JAS]: We want to strip the leading + trailing slashes (/) since JScript freaks.
				regexp_string = f.regexp_tester_pattern.value;
				regexp_string = regexp_string.substr(1,regexp_string.length);
				last_slash = regexp_string.indexOf("/");
				regexp_string = regexp_string.substr(0,last_slash);
				
				var regexp = new RegExp("" + regexp_string);
				var strr = f.regexp_tester_subject.value;
				
				var matches = strr.match(regexp);
				
				if(matches == null) {
					f.regexp_tester_result.value = "Fail!";
				}
				else {
					f.regexp_tester_result.value = "Pass!";
				}
			}
			</script>
    		
    		<br>
    		
		</td>
	</tr>
   
   <?php if($cerberus_db->num_rows($result)) { ?>
   
  <tr class="boxtitle_gray_glass_dk">
  	<td colspan="3">
  		Current Catch-All Rules:
  	</td>
  </tr>

	<form action="configuration.php?module=queue_catchall" method="post">
	<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
	<input type="hidden" name="module" value="queue_catchall">
	<input type="hidden" name="form_submit" value="catchall_edit">
  
   <tr bgcolor="#DDDDDD">
	<td>
    	<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
    	
    		<tr>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Rule Nickname</td>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">When Destination Pattern Matches...</td>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Deliver to Queue</td>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Order</td>
    			<td class="cer_maintable_header" bgcolor="#BBBBBB">Delete</td>
    		</tr>
    		
  		<?php
  		while($rule = $cerberus_db->fetch_row($result))
  		{
  			
  			?>
    		<tr bgcolor="#DDDDDD">
    			<input type="hidden" name="catchall_ids[]" value="<?php echo $rule["catchall_id"]; ?>">
    			<td class="cer_maintable_text"><?php echo stripslashes($rule["catchall_name"]); ?></td>
    			<td class="cer_maintable_heading"><?php echo stripslashes($rule["catchall_pattern"]); ?></td>
    			<td class="cer_maintable_text">
					<?php echo $queue_handler->queues[$rule["catchall_to_qid"]]->queue_name; ?>
    			</td>
    			<td>
    				<select name="catchall_order[]">
    					<?php for($x=1;$x<=$cerberus_db->num_rows($result);$x++) { ?>
    						<option value="<?php echo $x; ?>" <?php if($x == $rule["catchall_order"]) echo "selected"; ?>><?php echo $x; ?>
    					<?php } ?>
    				</select>
    			</td>
    			<td align="center" valign="center"><input type="checkbox" name="catchall_delete_ids[<?php echo $rule["catchall_id"]; ?>]" value="1"></td>
    		</tr>
  			<?php
  		}
  		?>
  		
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td colspan="5" align="left">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_WORD_UPDATE; ?>">&nbsp;
			</td>
		</tr>
  		
  		</table>
  		<br>
    </td>
  </tr>
 </form>
  
  <?php } /* end num rows */ ?>

</table>
<br>
