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
| File: ticket_list.php
|
| Purpose: List the tickets in a queue or in a search.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted		(ben@webgroupmedia.com)		[BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/search/ticket_search.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_translate = new cer_translate;
//$queue_access = new CER_QUEUE_ACCESS();
//$audit_log = new CER_AUDIT_LOG();
//$acl = new cer_admin_list_struct;
$cerberus_disp = new cer_display_obj;

@$qid = $_REQUEST["qid"];

@$search_status = $_REQUEST["search_status"];
@$search_subject = $_REQUEST["search_subject"];
@$search_sender = $_REQUEST["search_sender"];
@$search_owner = $_REQUEST["search_owner"];
@$search_content = $_REQUEST["search_content"];
@$search_company = $_REQUEST["search_company"];
@$search_date = $_REQUEST["search_date"];
@$search_fdate = $_REQUEST["search_fdate"];
@$search_tdate = $_REQUEST["search_tdate"];
@$search_field_ids = $_REQUEST["search_field_ids"];

@$form_submit = $_REQUEST["form_submit"];

@$queue_id = $_REQUEST["queue_id"];
@$owner_id = $_REQUEST["owner_id"];
@$status_id = $_REQUEST["status_id"];
@$action_id = $_REQUEST["action_id"];

@$queue_view = $_REQUEST["queue_view"];

@$last_author = $_REQUEST["last_author"];
@$bids = $_REQUEST["bids"];

@$view_submit = $_REQUEST["view_submit"];
@$search_submit = $_REQUEST["search_submit"];

@$assign_type = $_REQUEST["assign_type"];

// [JAS]: Handle dynamic view filter options on form submit
if(!empty($view_submit))
{
	@$filter_responded = $_REQUEST[$view_submit."_filter_responded"];
	if(empty($filter_responded)) $filter_responded = 0;
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_responded"] = $filter_responded;

	@$filter_rows =  $_REQUEST[$view_submit."_filter_rows"];
	if(empty($filter_rows)) $filter_rows = 15;
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_rows"] = $filter_rows;
	
	// [JAS]: Reset paging
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_p"] = 0;
}

//if(!isset($order_by) || empty($order_by)) $order_by = "";
//if(!isset($lastauthor)) $lastauthor = array();

$checkboxes_exist=0; // determines if we'll draw drop-down controls at the bottom of the page

$cerberus_format = new cer_formatting_obj;

$cer_tpl->assign('qid',((isset($qid))?$qid:0));

if(!empty($search_submit))
{
	$session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_p"] = 0;
//	$session->vars["login_handler"]->user_prefs->view_prefs->vars["sv"] = "";
}

$extra_where = "";
$extra_from_thread="";
$extra_select="";
$where_queue="";

$tids = array();
$in_tickets = "";

//=============================================================================
// [JAS]: FORM ACTIONS
//=============================================================================
if(!empty($form_submit))
{
	include (FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView_modify.include.php");		

	switch($form_submit)
	{
		case "save_layout":
			$layout_view_options_sv = (isset($_REQUEST["layout_view_options_sv"])) ? $_REQUEST["layout_view_options_sv"] : 0;
			$layout_home_header_advanced = (isset($_REQUEST["layout_home_header_advanced"])) ? $_REQUEST["layout_home_header_advanced"] : 0;
		
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_sv"] = $layout_view_options_sv;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_header_advanced"] = $layout_home_header_advanced;			
			
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			
			$errorcode = "Page layout saved!";
			
			break;
		
		case "quick_assign":
		{
			$uid = $session->vars["login_handler"]->user_id;
			$max = $session->vars["login_handler"]->user_prefs->assign_queues->max;
			$qs = @implode(",",$session->vars["login_handler"]->user_prefs->assign_queues->queues);
			
			$sql = sprintf("SELECT count(ticket_id) as assigned FROM ticket WHERE ticket_assigned_to_id=%d ".
				"AND ticket_status IN ('new', 'awaiting-reply', 'customer-reply','bounced')",
					$uid
				);
			$res = $cerberus_db->query($sql);
			
			
			if($row = $cerberus_db->grab_first_row($res)) {
				$assigned = $row["assigned"];
				if($assigned > $max) break;
				
				$max -= $assigned;
			}
			else
				break;
			
			$affected=0;
			$new_max = $max;
			$bailout=0;
				
			while($affected < $new_max && $bailout < 10)
			{
				$sql = sprintf("SELECT t.ticket_id FROM ticket t LEFT JOIN thread th ON (th.thread_id = t.max_thread_id) ".
					"WHERE t.ticket_status IN ('new', 'awaiting-reply', 'customer-reply','bounced') AND t.ticket_queue_id IN (%s) AND t.ticket_assigned_to_id = 0 ".
					"ORDER BY th.thread_date %s ".
					"LIMIT 0,%d",
						$qs,
						((@$assign_type=="newest") ? "DESC" : "ASC"),
						$new_max
					);
				$res = $cerberus_db->query($sql);
				
				$ids = array();
				if($cerberus_db->num_rows($res))
				{
					while($row=$cerberus_db->fetch_row($res))
						array_push($ids,$row["ticket_id"]);
											
					$sql = sprintf("UPDATE ticket SET ticket_assigned_to_id=%d ".
						"WHERE ticket_status IN ('new', 'awaiting-reply', 'customer-reply','bounced') AND ticket_assigned_to_id=0 AND ticket_id IN (%s) ".
						"LIMIT %d",
							$uid,
							implode(",",$ids),
							$new_max
						);
					$ures = $cerberus_db->query($sql);
					$af = $cerberus_db->affected_rows($ures);
					
					if($af) {
						$affected += $af;
						$new_max -= $af;
					}
					else
						break;
				}
				else 
					break;	
					
			$bailout++;
			} // end while
			
			break;
		}
		
	} // end switch
}
//=============================================================================
// [JAS]: END FORM ACTIONS
//=============================================================================


//=============================================================================
// [JAS]: SEARCH QUERY
//=============================================================================

$params = array();

// [JAS]: Quick Search Options
if(isset($qid)) $params["search_queue"] = $qid;
if(isset($search_status)) $params["search_status"] = $search_status;
if(isset($search_sender)) $params["search_sender"] = $search_sender;
if(isset($search_subject)) $params["search_subject"] = $search_subject;
if(isset($search_content)) $params["search_content"] = $search_content;
if(isset($search_owner)) $params["search_owner"] = $search_owner;
if(isset($search_company)) $params["search_company"] = $search_company;
if(isset($queue_view)) $params["queue_view"] = $queue_view;

// [JAS]: Advanced Search Options
$params["advsearch"] = $session->vars["psearch"]->params["advsearch"];
if(isset($search_date)) $params["search_date"] = $search_date;
if(isset($search_fdate)) $params["search_fdate"] = $search_fdate;
if(isset($search_tdate)) $params["search_tdate"] = $search_tdate;

if(!empty($search_field_ids)) {
	$params["search_field_ids"] = $search_field_ids;
	$ids = explode(',',$search_field_ids);
	
	// [JAS]: Populate filled in search fields in the persistent query.
	foreach($ids as $id) {
		$params["search_field_" . $id] = $_REQUEST["search_field_" . $id];
	}
}

// [JAS]: Check if we're doing a new search
if(isset($search_submit)) {
	unset($session->vars["psearch"]);
}

// [JAS]: Check if we're modifying a search
if(isset($session->vars["psearch"]) && !isset($queue_view)) {
	if(method_exists($session->vars["psearch"],"updateParams"))
		$session->vars["psearch"]->updateParams($params); // update
}
else { // [JAS]: Otherwise we're making a new search or queue list.
	$session->vars["psearch"] = new cer_TicketPersistentSearch();
	if(isset($queue_view)) {
		$session->vars["psearch"]->params["search_queue"] = $qid;
	}
	$session->vars["psearch"]->updateParams($params); // populate
}

//=============================================================================
// [JAS]: SEARCH VIEW CONTROL
//=============================================================================
@$sv = $_REQUEST["sv"]; // assigned view
@$sv_sort_by = $_REQUEST["sv_sort_by"];
@$sv_asc = $_REQUEST["sv_asc"];
@$sv_p = $_REQUEST["sv_p"];
if(isset($sv)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv"] = $sv; $sv_p = 0; }
if(isset($sv_sort_by)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_sort_by"] = $sv_sort_by; }
if(isset($sv_asc)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_asc"] = $sv_asc; }
if(isset($sv_p)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["sv_p"] = $sv_p; }
$s_view = new cer_TicketViewSearch($session->vars["login_handler"]->user_prefs->view_prefs->vars["sv"],$session->vars["psearch"]->params);
$cer_tpl->assign_by_ref('s_view',$s_view);

//if(!isset($select_all)) $select_all = 0;
//$search_results->select_all = $select_all;
//$cer_tpl->assign_by_ref('search_results',$search_results);

// [JAS]: Header Functionality
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);

// [JAS]: Search Box Functionality
$search_box = new CER_TICKET_SEARCH_BOX();
$cer_tpl->assign_by_ref('search_box',$search_box);

// [JAS]: We're giving the user a popup, remove the 'new' flag from messages so it doesn't keep popping up
if($session->vars["login_handler"]->has_new_pm)
{
	$cer_tpl->assign('new_pm',$session->vars["login_handler"]->has_new_pm);
}

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);
$cer_tpl->assign_by_ref('cerberus_disp',$cerberus_disp);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  'save_layout' => "javascript:savePageLayout();"
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "ticket_list.php";
$cer_tpl->assign("page",$page);

$cer_tpl->assign_by_ref('errorcode',$errorcode);

$cer_tpl->display("ticket_list.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************

?>