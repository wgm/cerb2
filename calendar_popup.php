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
| File: calendar_popup.php
|
| Purpose: A general purpose calendar pop-up for simplified date
|	entry.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/languages.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/calendar.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");

$cer_tpl = new CER_TEMPLATE_HANDLER();

$cerberus_translate = new cer_translate;

// [JAS]: Set up the local variables from the scope objects
@$mo_d = $_REQUEST["mo_d"];
@$mo_m = $_REQUEST["mo_m"];
@$mo_y = $_REQUEST["mo_y"];
@$date_field = $_REQUEST["date_field"];
@$mo_offset = $_REQUEST["mo_offset"];

// [JAS]: Set up the calendar ==========================
class calendar_callback
{
	function calendar_draw_day_links(&$o_day,$month,$year)
	{
		global $mo_offset, $date_field; // clean
		
		if($o_day == null) return true;
		
		$o_day->day_url = cer_href(sprintf("%s?date_field=%s&mo_offset=%d&mo_d=%d&mo_m=%d&mo_y=%d",
			$_SERVER["PHP_SELF"],$date_field,$mo_offset,$o_day->day,$month,$year));
			
		return($o_day);
	}

	function calendar_draw_month_links($mo_offset=0,$prev_mo=-1,$next_mo=1)
	{
		global $date_field;
		
		$o_links = array();
		
		$o_links["prev_mo"] = cer_href($_SERVER["PHP_SELF"] . "?date_field=$date_field&mo_offset=$prev_mo");
		$o_links["next_mo"] = cer_href($_SERVER["PHP_SELF"] . "?date_field=$date_field&mo_offset=$next_mo");
		
		return($o_links);
	}
};


if(empty($mo_m) || empty($mo_d) || empty($mo_y))
{
	// ======================================================
	$cal_callbacks = new calendar_callback();
	$cal = new CER_CALENDAR($mo_offset);
	$cal->register_callback_day_links("calendar_draw_day_links",$cal_callbacks);
	$cal->register_callback_month_links("calendar_draw_month_links",$cal_callbacks);
	$cal->populate_calendar_matrix();
	$cer_tpl->assign_by_ref('cal',$cal);
	$cer_tpl->assign('date_field',$date_field);
	$cer_tpl->assign('date_chosen',false);
	// ======================================================
}
else {
	$cer_tpl->assign('mo_m',sprintf("%02d",$mo_m));
	$cer_tpl->assign('mo_d',sprintf("%02d",$mo_d));
	$cer_tpl->assign('mo_y',substr($mo_y,-2));
	$cer_tpl->assign('date_field',$date_field);
	$cer_tpl->assign('date_chosen',true);
}

$cer_tpl->display("calendar_popup.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************

?>
