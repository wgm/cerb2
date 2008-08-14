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
| File: addresses_export.php
|
| Purpose: Exports the stored e-mail addresses from the database in the 
|   format and with the options selected by the user. 
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

// [JAS]: Set up the local variable scope from the scope objects.
@$form_submit = $_REQUEST["form_submit"];
@$queues = implode(",",$_REQUEST["queues"]);
@$delimiter = $_REQUEST["delimiter"];
@$file_type = $_REQUEST["file_type"];

if(count($queues)==0) { echo "CERBERUS [ERROR]: No queues selected."; exit; }

// [JAS]: If we're exporting to a file, and not screen, kill cache.
if($file_type=="file") session_cache_limiter('public');

// [JAS]: Includes
require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

if(!empty($form_submit)) // [JAS]: process incoming form
	{
	if(DEMO_MODE) exit;
	if(!$priv->has_priv(ACL_EMAIL_EXPORT,BITGROUP_1)) exit;

	if($file_type == "screen") echo "<PRE>";
	 // [JAS]: Load up current user preferences

		if ($file_type == "file"){
			header("Content-Type: application/download\n");
      header("Content-Disposition: inline; filename=\"" . "cerberus_address_dump.txt" . "\"");
		} 
  
	 	$sql = "SELECT DISTINCT a.address_address FROM ticket t LEFT JOIN thread th ON (t.min_thread_id=th.thread_id) LEFT JOIN address a ON (th.thread_address_id=a.address_id) WHERE t.ticket_queue_id IN ($queues) ORDER BY a.address_address ASC;";
		$result_tickets = $cerberus_db->query($sql,false);																
		   $rows=0;
					if($cerberus_db->num_rows($result_tickets) > 0){
     		   while($ticketrow = $cerberus_db->fetch_row($result_tickets)){
								$rows++;
						 if ($delimiter == "comma"){
						  if(!empty($ticketrow[0])) {
              		echo $ticketrow[0];
									if($rows<$cerberus_db->num_rows($result_tickets)) echo ",";
                }
						 } else {
						  echo $ticketrow[0]; echo ($file_type=="screen") ? "\r\n" : "\r\n";
						 }
					}
			}
	if($file_type == "screen") echo "</PRE>";
exit;	
}	
?>
