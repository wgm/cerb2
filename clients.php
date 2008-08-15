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
| File: clients.php
|
| Purpose: Company/Address/Contact Management
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/user/user_prefs.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");

require_once(FILESYSTEM_PATH . "cerberus-api/company/cer_Company.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicUser.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

if(!$priv->has_priv(ACL_CONTACTS,BITGROUP_2))
{
	echo LANG_CERB_ERROR_ACCESS;
	exit();
}

$cer_tpl = new CER_TEMPLATE_HANDLER();

$cerberus_translate = new cer_translate;
$cerberus_format = new cer_formatting_obj();

@$form_submit = $_REQUEST["form_submit"];

@$mode = $_REQUEST["mode"];
@$id = $_REQUEST["id"];
@$action = $_REQUEST["action"];
@$sort_opt = $_REQUEST["sort_opt"];
@$up = max($_REQUEST["up"],0);
@$cp = max($_REQUEST["cp"],0);
@$add_to = $_REQUEST["add_to"];
@$add_email = $_REQUEST["add_email"];

@$puaids = $_REQUEST["puaids"];
@$puids = $_REQUEST["puids"];

@$company_name = $_REQUEST["company_name"];
@$company_account_number = $_REQUEST["company_account_number"];
$company_account_number_auto = ($_REQUEST["company_account_number_auto"]) ? $_REQUEST["company_account_number_auto"] : 0;
@$company_mailing_address = $_REQUEST["company_mailing_address"];
@$company_mailing_city = $_REQUEST["company_mailing_city"];
@$company_mailing_state = $_REQUEST["company_mailing_state"];
@$company_mailing_zip = $_REQUEST["company_mailing_zip"];
@$company_mailing_country_id = $_REQUEST["company_mailing_country_id"];
@$company_phone = $_REQUEST["company_phone"];
@$company_fax = $_REQUEST["company_fax"];
@$company_email = $_REQUEST["company_email"];
@$company_website = $_REQUEST["company_website"];

@$company_add_contact = $_REQUEST["company_add_contact"];
@$company_add_sla = $_REQUEST["company_add_sla"];
@$company_remove_sla = $_REQUEST["company_remove_sla"];
@$company_contact_action = $_REQUEST["company_contact_action"];
$company_sla_expire = isset($_REQUEST["company_sla_expire"]) ? $_REQUEST["company_sla_expire"] : "";

@$contact_search = $_REQUEST["contact_search"];

@$account_name_first = $_REQUEST["account_name_first"];
@$account_name_last = $_REQUEST["account_name_last"];
@$account_access_level = $_REQUEST["account_access_level"];
@$account_email_address= $_REQUEST["account_email_address"];
@$account_company_id= $_REQUEST["account_company_id"];
@$account_mailing_address = $_REQUEST["account_mailing_address"];
@$account_mailing_city = $_REQUEST["account_mailing_city"];
@$account_mailing_state = $_REQUEST["account_mailing_state"];
@$account_mailing_zip = $_REQUEST["account_mailing_zip"];
@$account_mailing_country = $_REQUEST["account_mailing_country"];
@$account_phone_work = $_REQUEST["account_phone_work"];
@$account_phone_home = $_REQUEST["account_phone_home"];
@$account_phone_mobile = $_REQUEST["account_phone_mobile"];
@$account_phone_fax = $_REQUEST["account_phone_fax"];
@$account_password = $_REQUEST["account_password"];

@$user_email_action = $_REQUEST["user_email_action"];
@$user_add_address = $_REQUEST["user_add_address"];

$company_custom_gid = ($_REQUEST["company_custom_gid"]) ? $_REQUEST["company_custom_gid"] : 0;
$company_custom_inst_id = ($_REQUEST["company_custom_inst_id"]) ? $_REQUEST["company_custom_inst_id"] : 0;

$contact_custom_gid = ($_REQUEST["contact_custom_gid"]) ? $_REQUEST["contact_custom_gid"] : 0;
$contact_custom_inst_id = ($_REQUEST["contact_custom_inst_id"]) ? $_REQUEST["contact_custom_inst_id"] : 0;

$company_entry_defaults = array();
$contact_entry_defaults = array();

$add_contact_fail_msg = null;
$add_contact_pass_msg = null;

$user_email_fail_msg = null;
$user_email_pass_msg = null;

$record_edit_pass_msg = null;

// ***************************************************************************************************************************
// [JAS]: Header Functionality
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);
// ***************************************************************************************************************************

if($mode == "search" && empty($contact_search)) unset($mode);

function get_country_list() {
	global $cerberus_db;
	$country_list = array();
	
	$sql = "SELECT cou.country_id, cou.country_name FROM country cou ORDER BY cou.country_name";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$country_list[$row["country_id"]] = $row["country_name"];
		}
	}
	
	return $country_list;
}

$country_list = get_country_list();

if(!empty($form_submit)) {

	switch($form_submit) {
		
		case "company_add": {
			
			if(!$priv->has_priv(ACL_CONTACTS_COMPANY_MANAGE,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			$sql = sprintf("INSERT INTO company (name,company_account_number,company_mailing_address,company_mailing_city,".
				"company_mailing_state,company_mailing_zip,company_mailing_country_id,company_phone,company_fax,company_email,".
				"company_website) ".
				"VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%s,%s,%s) ",
					$cerberus_db->escape($company_name),
					$cerberus_db->escape($company_account_number),
					$cerberus_db->escape($company_mailing_address),
					$cerberus_db->escape($company_mailing_city),
					$cerberus_db->escape($company_mailing_state),
					$cerberus_db->escape($company_mailing_zip),
					$company_mailing_country_id,
					$cerberus_db->escape($company_phone),
					$cerberus_db->escape($company_fax),
					$cerberus_db->escape($company_email),
					$cerberus_db->escape($company_website)
				);
			$cerberus_db->query($sql);
			
			$id = $cerberus_db->insert_id();
			
			// [JAS]: If the user wanted the company account number automatically assigned, 
			//	set it to the database ID
			if($company_account_number_auto) {
				$sql = "UPDATE company SET company_account_number = id WHERE id = $id";
				$cerberus_db->query($sql);
			}
			
			$mode = "c_view";
			
			// [JAS]: Are we adding a group of custom fields to this entry?
			if($id && !empty($company_custom_gid)) {
				
				$custom_handler = new cer_CustomFieldGroupHandler();
				$custom_handler->loadGroupTemplates();
				$inst_id = $custom_handler->addGroupInstance(ENTITY_COMPANY,$id,$company_custom_gid);
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$company_custom_gid]->fields))
				foreach($custom_handler->group_templates[$company_custom_gid]->fields as $idx => $fld) {
					$fld_idx = "company_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					if(!empty($val)) {
						$custom_handler->setFieldInstanceValue($fld->field_id,$inst_id,$val);
					}
				}
			}
			
			break;
		}
		
		case "company_edit": {
			if(empty($id)) break;
			
			if(!$priv->has_priv(ACL_CONTACTS_COMPANY_MANAGE,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			$sql = sprintf("UPDATE company SET ".
				"name=%s, ".
				"company_account_number=%s, ".
				"company_mailing_address=%s, ".
				"company_mailing_city=%s, ".
				"company_mailing_state=%s, ".
				"company_mailing_zip=%s, ".
				"company_mailing_country_id=%d, ".
				"company_phone=%s, ".
				"company_fax=%s, ".
				"company_email=%s, ".
				"company_website=%s ".
				"WHERE id = %d",
					$cerberus_db->escape($company_name),
					$cerberus_db->escape($company_account_number),
					$cerberus_db->escape($company_mailing_address),
					$cerberus_db->escape($company_mailing_city),
					$cerberus_db->escape($company_mailing_state),
					$cerberus_db->escape($company_mailing_zip),
					$company_mailing_country_id,
					$cerberus_db->escape($company_phone),
					$cerberus_db->escape($company_fax),
					$cerberus_db->escape($company_email),
					$cerberus_db->escape($company_website),
					$id
				);
			$cerberus_db->query($sql);
			
			// [JAS]: Do we have custom fields to update?
			// \todo This could probably also be moved into the bindings API for company/contact/time-entry edit
			if($id && !empty($company_custom_inst_id)) {
				
				$custom_handler = new cer_CustomFieldGroupHandler();
				$custom_handler->loadGroupTemplates();
				$custom_handler->loadSingleInstance($company_custom_inst_id);
				$gid = $custom_handler->group_instances[$company_custom_inst_id]->group_id;
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$gid]->fields))
				foreach($custom_handler->group_templates[$gid]->fields as $idx => $fld) {
					$fld_idx = "company_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					$custom_handler->setFieldInstanceValue($fld->field_id,$company_custom_inst_id,$val);
				}
			}
			
			$record_edit_pass_msg = LANG_CONTACTS_COMPANY_UPDATE_SUCCESS;
						
			break;
		}
		
		case "company_delete": {
			if(empty($id)) break;
			
			if(!$priv->has_priv(ACL_CONTACTS_COMPANY_DELETE,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			$sql = sprintf("DELETE FROM company WHERE id = %d",
					$id
				);
			$cerberus_db->query($sql);

			// [JAS]: If we had any custom field groups assigned to this company, time to delete them.
			$custom_handler = new cer_CustomFieldGroupHandler();
			$custom_handler->load_entity_groups(ENTITY_COMPANY,$id);
			
			if(!empty($custom_handler->group_instances))
			foreach($custom_handler->group_instances as $inst) {
				$custom_handler->deleteGroupInstances(array($inst->group_instance_id));	
			}
			
			unset($id);
			unset($mode);
			
			break;
		}
		
		case "company_update": {
			if(empty($puids) || !is_array($puids) || empty($company_contact_action)) break;
			
			if(!$priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			switch($company_contact_action) {
				case "unassign": {
					$sql = sprintf("UPDATE public_gui_users SET company_id = 0 WHERE public_user_id IN (%s)",
							implode(',',$puids)
						);
					$cerberus_db->query($sql);
					break;
				}
			}
			
			break;
		}
		
		case "user_add": {
			$user_add_error_msg = null;
			$add_address_id = null;
			
			if(!$priv->has_priv(ACL_CONTACTS_CONTACT_MANAGE,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			// check if they entered an email address
			if(empty($account_email_address)) {
				$user_add_error_msg = LANG_CONTACTS_ERROR_NO_ADDRESS_GIVEN;
			}
			else { // check the user isn't already assigned
				$sql = sprintf("SELECT a.address_id, a.public_user_id ".
					"FROM address a ".
					"WHERE a.address_address = %s",
						$cerberus_db->escape($account_email_address)
					);
				$res = $cerberus_db->query($sql);
				
				if($row = $cerberus_db->grab_first_row($res)) {
					$add_address_id = $row["address_id"];
					
					// If they're already a public user, complain
					if($row["public_user_id"]) {
						$user_add_error_msg = "ERROR: A user already exists for " . $account_email_address;
					}
				}
				else { // create address record
					$sql = sprintf("INSERT INTO address (address_address) VALUES (%s)",
							$cerberus_db->escape($account_email_address)
						);
					$cerberus_db->query($sql);
					
					$add_address_id = $cerberus_db->insert_id();
				}
			}
			
			if($user_add_error_msg) {
				$cer_tpl->assign_by_ref("user_add_error_msg",$user_add_error_msg);
				
				// [JAS]: Be nice and autofill in the failed user info for the agent
				$user = new cer_PublicUser();
					$user->account_name_first = $account_name_first;
					$user->account_name_last = $account_name_last;
					$user->account_access_level = $account_access_level;
					$user->account_address = $account_mailing_address;
					$user->account_city = $account_mailing_city;
					$user->account_state = $account_mailing_state;
					$user->account_zip = $account_mailing_zip;
					$user->account_country = $account_mailing_country;
					$user->account_phone_work = $account_phone_work;
					$user->account_phone_home = $account_phone_home;
					$user->account_phone_mobile = $account_phone_mobile;
					$user->account_phone_fax = $account_phone_fax;
				
				$cer_tpl->assign_by_ref("user",$user);
				break;
			}
								
			$sql = sprintf("INSERT INTO public_gui_users (name_first,name_last,public_access_level,mailing_address,mailing_city,mailing_state,".
				"mailing_zip,mailing_country_old,phone_work,phone_home,phone_mobile,phone_fax,`password`,company_id) ".
				"VALUES (%s,%s,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,'%s',%d) ",
					$cerberus_db->escape($account_name_first),
					$cerberus_db->escape($account_name_last),
					$account_access_level,
					$cerberus_db->escape($account_mailing_address),
					$cerberus_db->escape($account_mailing_city),
					$cerberus_db->escape($account_mailing_state),
					$cerberus_db->escape($account_mailing_zip),
					$cerberus_db->escape($account_mailing_country),
					$cerberus_db->escape($account_phone_work),
					$cerberus_db->escape($account_phone_home),
					$cerberus_db->escape($account_phone_mobile),
					$cerberus_db->escape($account_phone_fax),
					md5($account_password),
					max($account_company_id,0)
				);
			$cerberus_db->query($sql);
			
			$id = $cerberus_db->insert_id();
			
			$sql = sprintf("UPDATE address SET public_user_id = %d WHERE address_id = %d",
					$id,
					$add_address_id
				);
			$cerberus_db->query($sql);
			
			$mode = "u_view";
			
			// [JAS]: Are we adding a group of custom fields to this entry?
			if($id && !empty($contact_custom_gid)) {
				
				$custom_handler = new cer_CustomFieldGroupHandler();
				$custom_handler->loadGroupTemplates();
				$inst_id = $custom_handler->addGroupInstance(ENTITY_CONTACT,$id,$contact_custom_gid);
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$contact_custom_gid]->fields))
				foreach($custom_handler->group_templates[$contact_custom_gid]->fields as $idx => $fld) {
					$fld_idx = "contact_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					if(!empty($val)) {
						$custom_handler->setFieldInstanceValue($fld->field_id,$inst_id,$val);
					}
				}
			}
			
			break;
		}
		
		case "user_add_address": {
			if(empty($user_add_address) || empty($id)) break;
			$user_email_fail_msg = null;
			
			$addy_id = null;
			
			if(!$priv->has_priv(ACL_CONTACTS_EMAIL_ASSIGN,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			// check if the address exists
			$sql = sprintf("SELECT a.address_id, a.public_user_id ".
				"FROM address a ".
				"WHERE a.address_address = %s",
					$cerberus_db->escape($user_add_address)
				);
			$res = $cerberus_db->query($sql);
			
			if($row = $cerberus_db->grab_first_row($res)) { // if exists
				// check if it's assigned
				if($row["public_user_id"]) { // assigned
					$user_email_fail_msg = "ERROR: " . $user_add_address . " is already assigned to a contact.";
					$cer_tpl->assign_by_ref("user_email_fail_msg",$user_email_fail_msg);
					break;
				}
				else {
					$addy_id = $row["address_id"];
				}
			}
			else { // if not exists
				$sql = sprintf("INSERT INTO address (address_address) ".
					"VALUES (%s)",
						$cerberus_db->escape($user_add_address)
					);
				$cerberus_db->query($sql);
				$addy_id = $cerberus_db->insert_id();
			}
			
			// assign
			$sql = sprintf("UPDATE address SET public_user_id = %d WHERE address_id = %d",
					$id,
					$addy_id
				);
			$cerberus_db->query($sql);
			
			$user_email_pass_msg = LANG_CONTACTS_ADDRESS_ASSIGN_SUCCESS;
			$cer_tpl->assign_by_ref("user_email_pass_msg",$user_email_pass_msg);
			
			break;
		}
		
		case "user_edit": {
			if(empty($id)) break;
			
			if(!$priv->has_priv(ACL_CONTACTS_CONTACT_MANAGE,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			$sql = sprintf("UPDATE public_gui_users SET ".
				"name_first=%s, ".
				"name_last=%s, ".
				"public_access_level=%d, ".
				"mailing_address=%s, ".
				"mailing_city=%s, ".
				"mailing_state=%s, ".
				"mailing_zip=%s, ".
				"mailing_country_old=%s, ".
				"phone_work=%s, ".
				"phone_home=%s, ".
				"phone_mobile=%s, ".
				"phone_fax=%s ".
				((!empty($account_password)) ? ", `password`='%s' " : "%s") .
				"WHERE public_user_id = %d",
					$cerberus_db->escape($account_name_first),
					$cerberus_db->escape($account_name_last),
					$account_access_level,
					$cerberus_db->escape($account_mailing_address),
					$cerberus_db->escape($account_mailing_city),
					$cerberus_db->escape($account_mailing_state),
					$cerberus_db->escape($account_mailing_zip),
					$cerberus_db->escape($account_mailing_country),
					$cerberus_db->escape($account_phone_work),
					$cerberus_db->escape($account_phone_home),
					$cerberus_db->escape($account_phone_mobile),
					$cerberus_db->escape($account_phone_fax),
					((!empty($account_password)) ? md5($account_password) : ""),
					$id
				);
			$cerberus_db->query($sql);
			
			$record_edit_pass_msg = LANG_CONTACTS_REGISTRED_UPDATE_SUCCESS;
			
			// [JAS]: Do we have custom fields to update?
			// \todo This could probably also be moved into the bindings API for company/contact/time-entry edit
			if($id && !empty($contact_custom_inst_id)) {
				
				$custom_handler = new cer_CustomFieldGroupHandler();
				$custom_handler->loadGroupTemplates();
				$custom_handler->loadSingleInstance($contact_custom_inst_id);
				$gid = $custom_handler->group_instances[$contact_custom_inst_id]->group_id;
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$gid]->fields))
				foreach($custom_handler->group_templates[$gid]->fields as $idx => $fld) {
					$fld_idx = "contact_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					$custom_handler->setFieldInstanceValue($fld->field_id,$contact_custom_inst_id,$val);
				}
			}
			
			break;	
		}
		
		case "user_update": {
			if(empty($user_email_action) || empty($puaids) || !is_array($puaids) || empty($id)) break;
			
			if(!$priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			switch($user_email_action) {
				case "unassign": 
					// Unregister all the public users addresses that were selected on this user
					$sql = sprintf("UPDATE address SET public_user_id = 0 WHERE public_user_id = %d AND address_id IN (%s)",
							$id,
							implode(',',$puaids)
						);
					$cerberus_db->query($sql);
					
					// If that was all the users e-mail addresses, lets go ahead and nuke the public user --
					//  since a public user with no way to log in or edit in the GUI is fairly useless.
					$sql = sprintf("SELECT count(*) as assigned_addys from address WHERE public_user_id = %d",
							$id
						);		
					$res = $cerberus_db->query($sql);
					
					if($row = $cerberus_db->grab_first_row($res)) {
						
						// [JAS]: If no more addresses are assigned to this user, delete
						if(!$row["assigned_addys"]) {
							$sql = sprintf("DELETE FROM public_gui_users WHERE public_user_id = %d",
									$id
								);
							$cerberus_db->query($sql);
							
							$sql = sprintf("DELETE FROM public_gui_users_to_plugin WHERE public_user_id = %d",
									$id
								);
							$cerberus_db->query($sql);
							
							// [JAS]: If we had any custom field groups assigned to these contacts, time to delete them.
							$custom_handler = new cer_CustomFieldGroupHandler();
							$custom_handler->load_entity_groups(ENTITY_CONTACT,$id);
							
							if(!empty($custom_handler->group_instances))
							foreach($custom_handler->group_instances as $inst) {
								$custom_handler->deleteGroupInstances(array($inst->group_instance_id));	
							}
							
							unset($id);
							unset($mode);
						}
						
					} // end grab row
					
				break;
			}
						
			break;
		}
		
		case "company_add_contact": {
			if(empty($company_add_contact)) break;

			if(!$priv->has_priv(ACL_CONTACTS_CONTACT_ASSIGN,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			$sql = sprintf("SELECT a.address_id, a.public_user_id, pu.company_id ".
				"FROM (address a, public_gui_users pu) ".
				"WHERE pu.public_user_id = a.public_user_id AND a.address_address = %s ",
					$cerberus_db->escape($company_add_contact)
				);
			$res = $cerberus_db->query($sql);
			
			if($row = $cerberus_db->grab_first_row($res)) {
				if(!empty($row["company_id"])) {
					$add_contact_fail_msg = LANG_CONTACTS_COMPANY_ERROR_ALREADY_ASSIGNED;
					break;
				}
				
				$sql = sprintf("UPDATE public_gui_users SET company_id = %d WHERE public_user_id = %d",
						$id,
						$row["public_user_id"]
					);
				if($cerberus_db->query($sql)) {
					$add_contact_pass_msg = LANG_CONTACTS_COMPANY_ASSIGNED_SUCCESS;
				}
			}
			else {
				$add_contact_fail_msg = LANG_CONTACTS_COMPANY_ERROR_CONTACT_NOT_EXIST;
				break;
			}
			
			break;
		}
		
		case "company_add_sla": {
			if(empty($company_add_sla)) break;
			if(empty($company_sla_expire)) {
				$sla_expire = "0000-00-00 00:00:00";
			}
			else {
				$sla_expire_date = new cer_DateTime($company_sla_expire);
				$sla_expire_date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
				$sla_expire = $sla_expire_date->getDate("%Y-%m-%d 23:59:59");
			}
			
			if(!$priv->has_priv(ACL_CONTACTS_SLA_ASSIGN,BITGROUP_3)) die(LANG_CERB_ERROR_ACCESS);
			
			$sql = sprintf("UPDATE company SET sla_id = %d, sla_expire_date = '%s' WHERE id = %d",
					$company_add_sla,
					$sla_expire,
					$id
				);
			$cerberus_db->query($sql);
			
			break;
		}
		
		case "company_sla_update": {
			
			if(empty($company_remove_sla)) {
				if(empty($company_sla_expire)) {
					$sla_expire = "0000-00-00 00:00:00";
				}
				else {
					$sla_expire_date = new cer_DateTime($company_sla_expire);
					$sla_expire_date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
					$sla_expire = $sla_expire_date->getDate("%Y-%m-%d 23:59:59");
				}
				
				$sql = sprintf("UPDATE company SET sla_expire_date = '%s' WHERE id = %d",
						$sla_expire,
						$id
					);
				$cerberus_db->query($sql);
			}

			if(!empty($company_remove_sla)) {
				if(!$priv->has_priv(ACL_CONTACTS_SLA_ASSIGN,BITGROUP_3)) continue;
			
				$sql = sprintf("UPDATE company SET sla_id = 0, sla_expire_date = '0000-00-00 00:00:00' WHERE id = %d",
						$id
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
	}

	$cer_tpl->assign('add_contact_pass_msg',$add_contact_pass_msg);
	$cer_tpl->assign('add_contact_fail_msg',$add_contact_fail_msg);
	$cer_tpl->assign('record_edit_pass_msg',$record_edit_pass_msg);
}

$uid = $session->vars["login_handler"]->user_id;
$user_prefs = new CER_USER_PREFS($uid);

$cer_tpl->assign_by_ref('user_prefs',$user_prefs);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);
$cer_tpl->assign('qid',((isset($qid))?$qid:0));

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  
			  'company_add' => cer_href("clients.php?mode=c_add"),
			  'contact_add' => cer_href("clients.php?mode=u_add" . (($mode=="c_view") ? "&add_to=" . $id : "") )
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "clients.php";
$cer_tpl->assign("page",$page);

//$base_href = "clients.php?mode=$mode";

$params = array();
@$params['sort_opt'] = $sort_opt;
@$params['mode'] = $mode;
@$params['action'] = $action;
@$params['id'] = $id;
@$params['add_to'] = $add_to;
@$params['add_email'] = $add_email;
@$params['contact_search'] = stripslashes($contact_search);

$cer_tpl->assign_by_ref('params',$params);

$company_handler = new cer_CompanyHandler();
$user_handler = new cer_PublicUserHandler();

switch($params["mode"])
{
	case "u_view":
		$user_handler->loadUsersByIds(array($id));
		$user = &$user_handler->users[$id];
		$user->loadOpenTickets();
		
		$cer_tpl->assign_by_ref("user",$user);
		$cer_tpl->assign_by_ref("country_list",$country_list);
		break;
		
	case "c_view":
		$company_handler->_loadCompaniesByIds(array($id));
		$company = &$company_handler->companies[$id];
		$company->loadOpenTickets();
		
		$user_handler->loadUsersByCompany($id);
		$cer_tpl->assign_by_ref("company",$company);
		$cer_tpl->assign_by_ref("country_list",$country_list);
		break;
	
	case "search":
		if(empty($contact_search)) break;
		
		$company_handler->_loadCompaniesBySearch($contact_search);
		$user_handler->loadUsersBySearch($contact_search);
		break;
	
	case "c_add":
		$cer_tpl->assign_by_ref("country_list",$country_list);
		break;
		
	case "u_add":
		if($add_to) {
			$company_handler->_loadCompaniesByIds(array($add_to));
			$company = &$company_handler->companies[$add_to];
			$cer_tpl->assign_by_ref("company",$company);
		}
		$cer_tpl->assign_by_ref("country_list",$country_list);
		break;
		
	default:
		$company_handler->_loadCompaniesByPage($cp,15);
		$user_handler->loadUsersByPage($up,15);
		break;
}

// [JAS]: See if we need to attach a set of custom fields to companies
// [JAS]: If we do have custom fields, store the custom field group template + ID
if($mode == "c_add") {
	
	$field_binding = new cer_CustomFieldBindingHandler();
	$custom_handler = new cer_CustomFieldGroupHandler();
	$bind_gid = $field_binding->getEntityBinding(ENTITY_COMPANY);

	if(!empty($bind_gid)) {
		$custom_handler->loadGroupTemplates();
		$company_entry_defaults["custom_gid"] = $bind_gid;
		$company_entry_defaults["custom_fields"] = $custom_handler->group_templates[$bind_gid];
	}
	
	if(!empty($company_entry_defaults)) $cer_tpl->assign_by_ref('company_entry_defaults',$company_entry_defaults);
}

// [JAS]: See if we need to attach a set of custom fields to contacts
// [JAS]: If we do have custom fields, store the custom field group template + ID
if($mode == "u_add") {
	
	$field_binding = new cer_CustomFieldBindingHandler();
	$custom_handler = new cer_CustomFieldGroupHandler();
	$bind_gid = $field_binding->getEntityBinding(ENTITY_CONTACT);

	if(!empty($bind_gid)) {
		$custom_handler->loadGroupTemplates();
		$contact_entry_defaults["custom_gid"] = $bind_gid;
		$contact_entry_defaults["custom_fields"] = $custom_handler->group_templates[$bind_gid];
	}
	
	if(!empty($contact_entry_defaults)) $cer_tpl->assign_by_ref('contact_entry_defaults',$contact_entry_defaults);
}

$cer_tpl->assign_by_ref("company_handler",$company_handler);
$cer_tpl->assign_by_ref("user_handler",$user_handler);

$cer_tpl->display('clients.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
