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
| File: config_users_groups_edit.php
|
| Purpose: The configuration include for creating and editing user privilege
|		groups.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/acl_groups.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");

$queue_access = new CER_QUEUE_ACCESS();

// [JAS]: Verify that the connecting user has access to modify configuration/
//		user values
if(!$priv->has_priv(ACL_GROUPS_CREATE,BITGROUP_2) 
	AND !$priv->has_priv(ACL_GROUPS_EDIT,BITGROUP_2) 
  	AND !$priv->has_priv(ACL_GROUPS_DELETE,BITGROUP_2))
	{
		echo LANG_CERB_ERROR_ACCESS;
		exit();
	}

if(!isset($gid))
	{ echo LANG_CONFIG_GROUPS_NOID; exit(); }	

if($gid != 0) {	
	$sql = "SELECT `group_id`,`group_name`,`is_core_default`, `group_acl`, `group_acl2`, `group_acl3` ".
	" FROM user_access_levels WHERE `group_id` = $gid";
	$result = $cerberus_db->query($sql);
	$group_data = $cerberus_db->fetch_row($result);
}

function cer_draw_acl_checkbox($bit_flag,$bit_group=1)
{
	global $pgid;
	global $group_data;
	$do_checked = false;
	
	switch($bit_group)
	{
		case 1:
			$fieldname = "group_acl";
			break;
		default:
			$fieldname = "group_acl" . $bit_group;
			break;
	}
	
	// [JAS]: Autocheck privileges on new groups
	if(isset($pgid) && empty($pgid)) {
		// [JAS]: Don't autocheck restrictions
		if(!($bit_flag == ACL_HIDE_REQUESTOR_EMAILS && $bit_group == 1))
			$do_checked = true;
	}
	elseif(cer_bitflag_is_set($bit_flag,@$group_data[$fieldname])) {
		$do_checked = true;
	}
	
	echo "<input type=\"checkbox\" name=\"".$fieldname."[]\" value=\"$bit_flag\"".
  		(($do_checked) ? " CHECKED" : "").
	">";
}

?>
<form action="configuration.php" method="post" name="groupedit">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pgid" value="<?php echo  $gid ?>">
<input type="hidden" name="module" value="groups">
<input type="hidden" name="form_submit" value="groups_edit">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_GROUPS_SUCCESS . "</span><br>"; ?>

<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
        <tr class="boxtitle_orange_glass"> 
          <td colspan="2"><?php echo  (@$gid==0)? LANG_CONFIG_GROUPS_EDIT_CREATE : LANG_CONFIG_GROUPS_EDIT_EDIT; ?></td>
        </tr>
        <tr bgcolor="#DDDDDD"> 
          <td colspan="2" valign="top" align="left"> 
              <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
                <tr bgcolor="#DDDDDD"> 
                  <td width="20%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_GROUPS_EDIT_GROUP ?>:</td>
                  <td width="80%">
                    <input type="text" name="group_name" size="40" maxlength="40" value="<?php echo cer_dbc(@$group_data["group_name"]); ?>">
                  </td>
                </tr>

			 	<?php
			 	// [JAS]: Functionality commented out for XSP 1.0
			 	if($session->vars["login_handler"]->is_xsp_user)
			 	{
			 	?>
                <tr bgcolor="#DDDDDD"> 
                  <td colspan="2"><input type="checkbox" name="is_core_default" value="1" <?php echo ((@$group_data["is_core_default"]==1)?"CHECKED":""); ?>> 
                  <span class="cer_maintable_heading">Make this Group the XSP User Default</span><br>
                  <span class="cer_footer_text">If this helpdesk is a satellite that reports to a Cerberus xSP Master GUI  
                  this group, if checked, will function as the default group for new users created by the Master GUI.<br>
                  <b>NOTE:</b> 
                  This will remove the XSP default flag from any other groups.  If this is not checked for any groups, new
                  XSP users will be superusers by default.</span></td>
                </tr>
				<?php
			 	}
			 	?>
			 	
                <tr class="boxtitle_gray_glass_dk"> 
                  <td colspan="2"><?php echo  LANG_CONFIG_GROUPS_EDIT_PRIV ?>:</td>
                </tr>
                <tr bgcolor="#DDDDDD"> 
                  <td colspan="2" class="cer_maintable_heading">
                    <table width="100%" border="0" cellspacing="1" cellpadding="1">
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading">Contacts</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can View Contacts/Company Menu</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS_CONTACT_MANAGE,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Manage Contacts (Add/Edit)</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS_COMPANY_MANAGE,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Manage Companies (Add/Edit)</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS_COMPANY_DELETE,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Delete Companies</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS_SLA_ASSIGN,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Assign/Unassign SLA Plans on Companies</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Assign/Unassign Contacts on Companies</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONTACTS_EMAIL_ASSIGN,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Assign/Unassign E-mail Addresses on Contacts</td>
                      </tr>
					  <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading"><?php echo  LANG_WORD_TICKETS ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CREATE_TICKET,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Create Tickets in GUI</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_CHOWNER); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_OWNER ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_CHPRIORITY); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_PRIOR ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_CHQUEUE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_QUEUE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_CHSTATUS); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_STATUS ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_CHSUBJECT); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_SUBJECT ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_TAKE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_TAKE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_KILL,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Set Tickets 'Dead'</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_BATCH,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Batch Tickets</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_CLONE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Clone Tickets</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CUSTOM_FIELDS_ENTRY,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Enter/Update Custom Fields on Tickets</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_AUDIT_LOG,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">View Ticket Audit Log</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_ADD_REQUESTER,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Add Ticket Requesters</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_FORWARD_THREAD,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Forward Ticket Messages to Email Address</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TICKET_MERGE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Merge Tickets</td>
                      </tr>
					  <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading">Ticket Time Tracking</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_CREATE,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Create Time Tracking Entries</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_VIEW_OWN,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can View Own Time Tracking Entries</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_VIEW_ALL,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can View All User Time Tracking Entries</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_EDIT_OWN,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Modify Own Time Tracking Entries</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_EDIT_ALL,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Modify All User Time Tracking Entries</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_DELETE_OWN,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Delete Own Time Tracking Entries</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_TIME_TRACK_DELETE_ALL,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Delete All User Time Tracking Entries</td>
                      </tr>
                      
                      
					  <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading">Ticket Views</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_VIEWS_CREATE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Ticket Views Create</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_VIEWS_EDIT,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Ticket Views Edit</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_VIEWS_DELETE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Ticket Views Delete</td>
                      </tr>
											<tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_GROUPS_EDIT_KB ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_VIEW); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_KB_VIEW ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_SEARCH); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_KB_SEARCH ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_ARTICLE_CREATE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_ARTICLE_CREATE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_ARTICLE_EDIT); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_ARTICLE_EDIT ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_ARTICLE_DELETE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_ARTICLE_DEL ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_COMMENT_EDITOR,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Article Comments: Approve, Edit, Remove (Editor)</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      <tr>
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading">My Cerberus</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">
                          <?php cer_draw_acl_checkbox(ACL_PREFS_USER); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_PREF ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      <tr>
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading">Reports</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">
                          <?php cer_draw_acl_checkbox(ACL_REPORTS,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can View Reports Menu</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_text">&nbsp;</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center">&nbsp;</td>
                        <td width="92%" class="cer_maintable_heading"><?php echo  LANG_CONFIG_GROUPS_EDIT_CONFIG ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CONFIG_MENU); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_CONFIG_MENU ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_GLOBAL_SETTINGS,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Edit Global Settings</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_USER_CREATE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_USER_CREATE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_USER_EDIT); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_USER_EDIT ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_USER_DELETE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_USER_DEL ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_GROUPS_CREATE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Group Create</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_GROUPS_EDIT,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Group Edit</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_GROUPS_DELETE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Group Delete</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_SLA_PLANS,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">SLA Plan Management (Create/Edit/Delete)</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_SLA_SCHEDULES,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">SLA Schedule Management (Create/Edit/Delete)</td>
                      </tr>
                      
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_CUSTOM_FIELDS,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Custom Field Groups Management (Create/Edit/Delete)</td>
                      </tr>
                      
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_PUBLIC_GUI,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Public GUI Profiles Management (Create/Edit/Delete)</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_MAILRULE_CREATE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Parser Mail Rule Create</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_MAILRULE_EDIT,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Parser Mail Rule Edit</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_MAILRULE_DELETE,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Parser Mail Rule Delete</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_PARSER_LOG,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">View Parser/GUI Error Log</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_REINDEX_ARTICLES,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Reindex Knowledgebase Articles</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_REINDEX_THREADS,BITGROUP_2); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Reindex E-mail Message Threads</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_QUEUE_CREATE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_QUEUE_CREATE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_QUEUE_EDIT); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_QUEUE_EDIT ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_QUEUE_DELETE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_QUEUE_DELETE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_QUEUE_CATCHALL,BITGROUP_3); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Can Setup Queue Catch-All Rules</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_CATEGORY_CREATE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_KB_CREATE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_CATEGORY_EDIT); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_KB_EDIT ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_KB_CATEGORY_DELETE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_KB_DEL ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_UPLOAD_LOGO); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_LOGO ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_MAINT_PURGE_DEAD); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_PURGE ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_MAINT_OPTIMIZE); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text">Maintenance Optimize Tables</td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_EMAIL_BLOCK_SENDERS); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_BLOCK ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_EMAIL_EXPORT); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_EMAIL ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_OPTIONS_REPORT_BUG); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_BUG ?></td>
                      </tr>
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_OPTIONS_FEEDBACK); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_FEEDBACK ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                
                <tr class="boxtitle_gray_glass_dk"> 
                  <td colspan="2">Restrictions:</td>
                </tr>
                <tr bgcolor="#DDDDDD"> 
                  <td colspan="2" class="cer_maintable_heading">
                    <table width="100%" border="0" cellspacing="1" cellpadding="1">
                      <tr> 
                        <td width="8%" valign="top" align="center"> 
                          <?php cer_draw_acl_checkbox(ACL_HIDE_REQUESTOR_EMAILS); ?>
                        </td>
                        <td width="92%" class="cer_maintable_text"><?php echo  LANG_CONFIG_GROUPS_EDIT_REQ ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                
                <tr class="boxtitle_green_glass"> 
                  <td colspan="2">Group Queue Access (defaults for users of this group):</td>
                </tr>
                <tr bgcolor="#DDDDDD"> 
                  <td colspan="2" class="cer_maintable_heading">
                  
                    <table border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
                    	<tr bgcolor="#999999">
                    		<td nowrap class="cer_maintable_header">Queue</td>
                    		<td nowrap class="cer_maintable_header">Access</td>
                    	</tr>
					<?php
					// [JAS]: Find the current group permissions using the queues this user is allowed to see.
					$group_qids = "-1";
					$q_list = $queue_access->queue_access_list;
					
					if(!empty($q_list))
						$group_qids = implode(",",array_keys($q_list));
					else
						$group_qids = "-1";
					
					$sql = "SELECT q.queue_id, q.queue_name, qa.queue_access ".
						"FROM queue q ".
						"LEFT JOIN queue_group_access qa ON (qa.queue_id = q.queue_id AND qa.group_id = $gid) ".
						"WHERE q.queue_id IN ($group_qids) ".
						"ORDER BY q.queue_name";
					$res = $cerberus_db->query($sql);

					if($cerberus_db->num_rows($res))					
					while($row = $cerberus_db->fetch_row($res))
					{
						$access = $row["queue_access"];
						$queue_name = $row["queue_name"];
						$qid = $row["queue_id"];
					?>					
		          <tr> 
		            <td nowrap class="cer_maintable_heading" bgcolor="#CCCCCC"><?php echo $queue_name; ?>:</td>
		            <td class="cer_maintable_text" bgcolor="#DDDDDD">						
		  				<input type="radio" name="qaccess_<?php echo $qid; ?>" value="read"<?php if($access=="read") {echo " checked";} ?>><?php echo LANG_WORD_READ ?>
						<input type="radio" name="qaccess_<?php echo $qid; ?>" value="write"<?php if($access=="write") {echo " checked";} ?>><?php echo LANG_WORD_WRITE ?>
						<input type="radio" name="qaccess_<?php echo $qid; ?>" value="none"<?php if($access=="none" || empty($access)) {echo " checked";} ?>><?php echo LANG_WORD_NONE ?>
						<input type="hidden" name="qlist[]" value="<?php echo $qid; ?>">
					</td>
		          </tr>
		          
					<?php
					}
					?>
                    	
                    	
                    </table>
                    
                  </td>
                </tr>
                  
              </table>
              
          </td>
        </tr>
        <tr bgcolor="#999999" class="cer_maintable_text" align="right"> 
          <td colspan="2" class="cer_maintable_heading" valign="top"> 
            <input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT ?>">
          </td>
        </tr>
      </table>
</form>