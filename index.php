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
| File: index.php
|
| Purpose: The main page of the helpdesk system, providing navigation to
|   all functional areas of the system.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/home/system_status.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/search/ticket_search.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/log/whos_online.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/notification/cer_notification_class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");

log_user_who_action(WHO_ON_HOME);

// [JAS]: Create the Cerberus objects we'll need on the page and includes
$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_format = new cer_formatting_obj;
$cerberus_translate = new cer_translate;
$cerberus_disp = new cer_display_obj;
$cerberus_db = cer_Database::getInstance();

// [JAS]: Setup up the local variables from the scope objects
@$refresh_min = $_REQUEST["refresh_min"];
@$view_submit = $_REQUEST["view_submit"];

@$form_submit = $_REQUEST["form_submit"];
@$bids = $_REQUEST["bids"];
@$status_id = $_REQUEST["status_id"];
@$owner_id = $_REQUEST["owner_id"];
@$queue_id = $_REQUEST["queue_id"];
@$action_id = $_REQUEST["action_id"];

$errorcode = isset($_REQUEST["errorcode"]) ? strip_tags($_REQUEST["errorcode"]) : "";
$errorvalue = isset($_REQUEST["errorvalue"]) ? strip_tags($_REQUEST["errorvalue"]) : "";

if(!empty($form_submit))
{
	switch($form_submit) {
		case "save_layout":
			$layout_home_show_queues = (isset($_REQUEST["layout_home_show_queues"])) ? $_REQUEST["layout_home_show_queues"] : 0;
			$layout_home_show_search = (isset($_REQUEST["layout_home_show_search"])) ? $_REQUEST["layout_home_show_search"] : 0;
			$layout_home_header_advanced = (isset($_REQUEST["layout_home_header_advanced"])) ? $_REQUEST["layout_home_header_advanced"] : 0;
			$layout_view_options_av = (isset($_REQUEST["layout_view_options_av"])) ? $_REQUEST["layout_view_options_av"] : 0;
			$layout_view_options_uv = (isset($_REQUEST["layout_view_options_uv"])) ? $_REQUEST["layout_view_options_uv"] : 0;
		
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_show_queues"] = $layout_home_show_queues;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_show_search"] = $layout_home_show_search;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_home_header_advanced"] = $layout_home_header_advanced;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_av"] = $layout_view_options_av;
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_uv"] = $layout_view_options_uv;			
			
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			
			$errorcode = "Page layout saved!";
			
			break;
			
		default:
			include (FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView_modify.include.php");		
			break;
	}
}

// [JAS]: Handle dynamic view filter options on form submit
if(!empty($view_submit))
{
	if(isset($view_prefs)) unset($view_prefs);
	$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;
	
	@$filter_responded = $_REQUEST[$view_submit."_filter_responded"];
	if(empty($filter_responded)) $filter_responded = 0;
	$view_prefs->vars[$view_submit."_filter_responded"] = $filter_responded;

	@$filter_rows =  $_REQUEST[$view_submit."_filter_rows"];
	if(empty($filter_rows)) $filter_rows = 15;
	$view_prefs->vars[$view_submit."_filter_rows"] = $filter_rows;
	
	// [JAS]: Reset paging
	$view_prefs->vars[$view_submit."_p"] = 0;
}

// [JAS]: Check to see if the refresh option hasn't been selected, but we have a
// preference for default refresh in the user preference object
if(!isset($refresh_min)) {
	
	@$refresh_override = $session->vars["refresh_override"];
	
	if(!isset($refresh_override))
		{ $refresh_min = $session->vars["login_handler"]->user_prefs->user_refresh_rate; }
	else
		{ $refresh_min = $refresh_override;}
}

// [JAS]: If we have an override from the refresh select box, use it and ignore
// the user preference object.
if(!empty($refresh_min) && $refresh_min > 0) {
	$query = "?refresh_min=" . $refresh_min;
	$cer_tpl->assign('refresh_sec',$refresh_min * 60);
	$cer_tpl->assign('refresh_url',cer_href("index.php" . $query));
	$cer_tpl->assign('do_meta_refresh',true);
	
	$session->vars["refresh_override"] = $refresh_min;
}

// [JAS]: Header Functionality
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);

// [JAS]: System Status Functionality
$system_status = new CER_SYSTEM_STATUS();
$system_status->load_queue_data_by_uid($session->vars["login_handler"]->user_id);
$cer_tpl->assign_by_ref('system_status',$system_status);

// [JAS]: Create a view for unassigned tickets (default) or custom
$blank_array = array();

@$uv = $_REQUEST["uv"]; // unassigned view
@$uv_sort_by = $_REQUEST["uv_sort_by"];
@$uv_asc = $_REQUEST["uv_asc"];
@$uv_p = $_REQUEST["uv_p"]; // unassigned page number

if(isset($uv)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["uv"] = $uv; $uv_p = 0; }
if(isset($uv_sort_by)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["uv_sort_by"] = $uv_sort_by; }
if(isset($uv_asc)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["uv_asc"] = $uv_asc; }
if(isset($uv_p)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["uv_p"] = $uv_p; }

$u_view = new cer_TicketViewUnassigned($session->vars["login_handler"]->user_prefs->view_prefs->vars["uv"],$blank_array);
$cer_tpl->assign_by_ref('u_view',$u_view);

// [JAS]: Create a view for assigned tickets (default) or custom
@$av = $_REQUEST["av"]; // assigned view
@$av_sort_by = $_REQUEST["av_sort_by"];
@$av_asc = $_REQUEST["av_asc"];
@$av_p = $_REQUEST["av_p"];

if(isset($av)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["av"] = $av; $av_p = 0; }
if(isset($av_sort_by)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["av_sort_by"] = $av_sort_by; }
if(isset($av_asc)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["av_asc"] = $av_asc; }
if(isset($av_p)) { $session->vars["login_handler"]->user_prefs->view_prefs->vars["av_p"] = $av_p; }

$a_view = new cer_TicketViewAssigned(@$session->vars["login_handler"]->user_prefs->view_prefs->vars["av"],$blank_array);
$cer_tpl->assign_by_ref('a_view',$a_view);

// [JAS]: We're giving the user a popup, remove the 'new' flag from messages so it doesn't keep popping up
if($session->vars["login_handler"]->has_new_pm)
	$cer_tpl->assign('new_pm',$session->vars["login_handler"]->has_new_pm);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

// [JAS]: Refresh Box Functionality
$cer_tpl->assign('refresh_times',array(0 => LANG_REFRESH_DONT,
										 1 => '1 ' . LANG_WORD_MINUTES,
									   2 => '2 ' . LANG_WORD_MINUTES,
									   3 => '3 ' . LANG_WORD_MINUTES,
									   4 => '4 ' . LANG_WORD_MINUTES,
									   5 => '5 ' . LANG_WORD_MINUTES,
									   10 => '10 ' . LANG_WORD_MINUTES,
									   15 => '15 ' . LANG_WORD_MINUTES,
									   20 => '20 ' . LANG_WORD_MINUTES
									   ));
$cer_tpl->assign('refresh_rate',$refresh_min);

// [JAS]: Search Box Functionality
$search_box = new CER_TICKET_SEARCH_BOX();
$cer_tpl->assign_by_ref('search_box',$search_box);

// [JAS]: Who's Online Functionality
$cer_who = new CER_WHOS_ONLINE();
$cer_tpl->assign_by_ref('cer_who',$cer_who);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign('qid',((isset($qid))?$qid:0));

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
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  'save_layout' => "javascript:savePageLayout();",
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib")
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "index.php";
$cer_tpl->assign("page",$page);

switch($errorcode) {
	case "NOACCESS":
		$errorcode = "You do not have access to the requested ticket: " . $errorvalue;
		break;
	default:
		break;
}
$cer_tpl->assign_by_ref('errorcode',$errorcode);

$time_now = new cer_DateTime(date("Y-m-d H:i:s"));
$cer_tpl->assign("time_now",$time_now->getUserDate());

$cer_tpl->display('home.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>