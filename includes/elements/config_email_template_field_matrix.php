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
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

?>
<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
  <tr bgcolor="#666666"> 
    <td colspan="4" class="cer_maintable_header">Ticket Fields</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td width="20%" class="cer_maintable_text"><b>Ticket ID</b></td>
    <td width="30%" class="cer_footer_text">##ticket_id##</td>
    <td width="20%" class="cer_maintable_text"><b>Ticket Status</b></td>
    <td width="30%" class="cer_footer_text">##ticket_status##</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b>Ticket Subject</b></td>
    <td class="cer_footer_text">##ticket_subject##</td>
    <td class="cer_maintable_text"><b>Ticket Owner</b></td>
    <td class="cer_footer_text">##ticket_owner##</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b>Original Email Body</b></td>
    <td class="cer_footer_text">##ticket_email##</td>
    <td class="cer_maintable_text"><B>Ticket Time Worked</B></td>
    <td class="cer_footer_text">##ticket_time_worked##</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b>Queue ID</b></td>
    <td class="cer_footer_text">##queue_id##</td>
    <td class="cer_maintable_text"><b>Queue Name</b></td>
    <td class="cer_footer_text">##queue_name##</td>
  </tr>
  
<?php
/*
// [JAS]: Display Ticket Custom Fields and their Merge Field Names
$sql = "select f.field_id, f.field_name from ticket_fields f ORDER BY field_name";
$t_flds_res = $cerberus_db->query($sql);
if($cerberus_db->num_rows($t_flds_res))
	{
	echo "<tr bgcolor=\"#DDDDDD\">";

  $rows = 0;
  while($row_c = $cerberus_db->fetch_row($t_flds_res))
  	{
      if($rows % 2 == 0 && $rows) echo "</tr><tr bgcolor=\"#DDDDDD\">";
      echo "<td width=\"25%\" class=\"cer_maintable_text\"><b>" . stripslashes($row_c["field_name"]) . "</b></td>";
      echo "<td width=\"25%\" class=\"cer_footer_text\">##ticket_custom_" . $row_c["field_id"] . "##</td>";
    	$rows++;
    }
	if(($rows) % 2 != 0) echo "<td>&nbsp;</td><td>&nbsp;</td>";
	echo "</tr>";
	}
	*/
?>

  <tr> 
    <td colspan="4">&nbsp;</td>
  </tr>
  
  <tr bgcolor="#666666"> 
    <td colspan="4" class="cer_maintable_header">Contact & Company Fields</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b>Contact Name</b></td>
    <td class="cer_footer_text">##contact_name##</td>
    <td class="cer_maintable_text"><b>Company Name</b></td>
    <td class="cer_footer_text">##company_name##</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b>Contact E-mail Address</b></td>
    <td class="cer_footer_text">##requester_address##</td>
    <td class="cer_maintable_text"><b>Company Account Num.</b></td>
    <td class="cer_footer_text">##company_acct_num##</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b></b></td>
    <td class="cer_footer_text"></td>
    <td class="cer_maintable_text"><b>SLA Plan Name</b></td>
    <td class="cer_footer_text">##sla_name##</td>
  </tr>
  
<?php
/*
// [JAS]: Display Requestor Custom Fields and their Merge Field Names
$sql = "select f.field_id, f.field_name from address_fields f ORDER BY field_name";
$r_flds_res = $cerberus_db->query($sql);
if($cerberus_db->num_rows($r_flds_res))
	{
	echo "<tr bgcolor=\"#DDDDDD\">";

  $rows = 0;
  while($row_c = $cerberus_db->fetch_row($r_flds_res))
  	{
      if($rows % 2 == 0 && $rows) echo "</tr><tr bgcolor=\"#DDDDDD\">";
      echo "<td width=\"25%\" class=\"cer_maintable_text\"><b>" . stripslashes($row_c["field_name"]) . "</b></td>";
      echo "<td width=\"25%\" class=\"cer_footer_text\">##req_custom_" . $row_c["field_id"] . "##</td>";
    	$rows++;
    }
	if(($rows) % 2 != 0) echo "<td>&nbsp;</td><td>&nbsp;</td>";
	echo "</tr>";
	}
*/
?>

  <tr> 
    <td colspan="4">&nbsp;</td>
  </tr>
  
  <tr bgcolor="#666666"> 
    <td colspan="4" class="cer_maintable_header">Agent Fields</td>
  </tr>
  <tr bgcolor="#DDDDDD"> 
    <td class="cer_maintable_text"><b>User ID</b></td>
    <td class="cer_footer_text">##user_id##</td>
    <td class="cer_maintable_text"><b>User Name</b></td>
    <td class="cer_footer_text">##user_name##</td>
  </tr>
  <tr bgcolor="#DDDDDD">
    <td class="cer_maintable_text"><b>User Login</b></td>
    <td class="cer_footer_text">##user_login##</td>
    <td class="cer_maintable_text"><b>User E-mail</b></td>
    <td class="cer_footer_text">##user_address##</td>
  </tr>
  <tr bgcolor="#DDDDDD">
    <td class="cer_maintable_text"><b>User Signature</b></td>
    <td class="cer_footer_text">##user_signature##</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
