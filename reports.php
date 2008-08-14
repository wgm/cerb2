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
| File: reports.php
|
| Purpose: Various reports handling
|
| Developers involved with this file:
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
|       Trent Ramseyer  (trent@webgroupmedia.com)  [TAR]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/user/user_prefs.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/calendar.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "cerberus-api/reports/cer_Report.class.php");

@$report = $_REQUEST["report"];
@$mo_offset = $_REQUEST["mo_offset"];
@$mo_m = $_REQUEST["mo_m"];
@$mo_d = $_REQUEST["mo_d"];
@$mo_y = $_REQUEST["mo_y"];
@$mt_m = $_REQUEST["mt_m"];
@$mt_d = $_REQUEST["mt_d"];
@$mt_y = $_REQUEST["mt_y"];
@$from_date = $_REQUEST["from_date"];
@$to_date = $_REQUEST["to_date"];

@$report_user_id = $_REQUEST["report_user_id"];
@$report_queue_id = $_REQUEST["report_queue_id"];
@$report_group_id = $_REQUEST["report_group_id"];

if(!empty($from_date) && (empty($mo_m) && empty($mo_d) && empty($mo_y)))
	list($mo_m, $mo_d, $mo_y) = sscanf($from_date,"%d/%d/%d");
	
if(!empty($to_date) && (empty($mt_m) && empty($mt_d) && empty($mt_y)))
	list($mt_m, $mt_d, $mt_y) = sscanf($to_date,"%d/%d/%d");

// [JAS]: Fix issue w/ Y2K turning into '0' rather than '00'.
$mo_y = sprintf("%02d",$mo_y);
$mt_y = sprintf("%02d",$mt_y);

if(empty($mo_offset)) $mo_offset = 0;

$cer_tpl = new CER_TEMPLATE_HANDLER();

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

$uid = $session->vars["login_handler"]->user_id;
$user_prefs = new CER_USER_PREFS($uid);

$cer_tpl->assign('report',$report);
$cer_tpl->assign_by_ref('user_prefs',$user_prefs);

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);
$cer_tpl->assign('qid',((isset($qid))?$qid:0));

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);


// ***************************************************************************************************************************
// [JAS]: Define our reports here
$report_list = new cer_ReportsHandler();
$report_list->build_report($report,$cer_tpl);

$cer_tpl->assign_by_ref('report_list',$report_list);
// ***************************************************************************************************************************

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
			  'reports' => cer_href("reports.php")
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "reports.php";
$cer_tpl->assign("page",$page);

// ***************************************************************************************************************************
// [JAS]: Choose the appropriate report template
$cer_tpl->display('reports.tpl.php');
// ***************************************************************************************************************************

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
