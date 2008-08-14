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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");

// [JAS]: This object holds the variables the proc functions can reference from each ticket row
class cer_TicketViewsProc
{
	var $view_ptr = null;
	var $col_ptr = null;
	var $address_address = null;
	var $ticket_id = 0;
	var $queue_id = 0;
	
	function cer_TicketViewsProc(&$view_obj)
	{
		$this->view_ptr = &$view_obj;
	}
	
};

function view_proc_checkbox($t_id,$proc_args)
{
	global $queue_access; // [JAS]: clean
	if($queue_access->has_write_access($proc_args->queue_id))
		return "<input type='checkbox' name='".$proc_args->col_ptr->element_name."' value='" . $proc_args->ticket_id . "'>";
	else return;
}

function view_proc_print_id($t_id,$proc_args)
{
	if(!empty($proc_args->ticket_mask))
		$id = $proc_args->ticket_mask;
	else
		$id = sprintf("%06.0d",$t_id);
	
	$str =  '<a href="' . cer_href("display.php?ticket=" . $proc_args->ticket_id) . '" class="cer_footer_text">';
	$str .= $id;
	$str .= '</a>';
	
	return $str;
}

function view_proc_print($str,$proc_args)
{
	return '<span class="cer_maintable_text">' . @htmlspecialchars(stripslashes($str), ENT_QUOTES, LANG_CHARSET_CODE) . '</span>';
}

function view_proc_print_small($str,$proc_args)
{
	return '<span class="cer_footer_text">' . @htmlspecialchars(stripslashes($str), ENT_QUOTES, LANG_CHARSET_CODE) . '</span>';
}

function view_proc_print_worked($str,$proc_args)
{
	$str = cer_DateTimeFormat::secsAsEnglishString($str*60,true,2);
	return '<span class="cer_footer_text">' . @htmlspecialchars(stripslashes($str), ENT_QUOTES, LANG_CHARSET_CODE) . '</span>';
}

function view_proc_print_custom_field($str,$proc_args)
{
	$gid = $proc_args->col_ptr->group_id;
	$fid = $proc_args->col_ptr->field_id;
	@$fld_ptr = &$proc_args->view_ptr->field_handler->group_templates[$gid]->fields[$fid];
	
	if($fld_ptr) {
		// [JAS]: If it's a Dropdown control.
		if($fld_ptr->field_type == 'D') {
			$str = $fld_ptr->field_options[$str];
		}
	}
	
	return '<span class="cer_footer_text">' . @htmlspecialchars(stripslashes($str), ENT_QUOTES, LANG_CHARSET_CODE) . '</span>';
}

function view_proc_print_email_address($str,$proc_args)
{
	global $cerberus_format; // clean up
	$is_banned = "";
	
	$requestor_address = display_email($str);
	if($proc_args->requester_banned) $is_banned = "*";
	
	return '<span class="cer_footer_text">' . $requestor_address . $is_banned . '</span>';
}

function view_proc_print_queue_link($queue,$proc_args)
{
	$str = '<a href="' . cer_href("ticket_list.php?queue_view=1&qid=" . $proc_args->queue_id) . '" class="cer_queue_link">';
	$str .= $queue . '</a>';
	return $str;
}

function view_proc_print_subject_link($subject,$proc_args)
{
	global $session; // clean up
	$from = array("<",
				  ">",
				  '"',
				  );
	$to = array("&lt;",
	            "&gt;",
	            "&quot;"
	            );
	
	$str = "";
	if($session->vars["login_handler"]->batch->in_batch($proc_args->ticket_id)!==false)
		$str .= '<span class="cer_configuration_updated">*</span> ';
	$str .=  '<a href="' . cer_href("display.php?ticket=" . $proc_args->ticket_id) . '" class="cer_maintable_subjectLink">';
	$str .= str_replace($from,$to,stripslashes($subject));
 	$str .= '</a>';	
	return $str;
}

function view_proc_print_translated_status($status,$proc_args)
{
	global $cerberus_translate;
	$acl = new cer_admin_list_struct();
	
	$status = $cerberus_translate->translate_status(addslashes($status));
	$status = str_replace(" ","&nbsp;",str_pad($status,7," "));
	
	if($proc_args->last_reply_by_agent) { $last_style = "cer_footer_text"; } else { $last_style = "cer_footer_red"; }
	return "<span class=\"$last_style\">" . $status . '</span>';
}

// PROC: Turn a thread date into a human-readable age (secs,mins,hours,days)
function view_proc_date_to_age($t_date,$proc_args)
{
	global $cerberus_format;
	$cfg = CerConfiguration::getInstance();
	
	$due_date = new cer_DateTime($proc_args->ticket_due);
	$age_date = new cer_DateTime($t_date);
	$now = mktime();

	$time_left = $due_date->mktime_datetime - $now;

	$ticket_secs = $cerberus_format->date_diff_epoch($now,$age_date->mktime_datetime);
	
	if($time_left < 0) {
		$overdue = "cer_footer_red";
	}
	else {
		$overdue = "cer_footer_text";
	}
	
	$ticket_age = cer_DateTimeFormat::secsAsEnglishString($ticket_secs,true,1);
	
//	$ticket_age = $cerberus_format->format_seconds($ticket_secs);
	$ticket_age = "<span class=\"$overdue\">" . $ticket_age . "</span>";
	
	return $ticket_age;
}


function view_proc_due_to_age($t_date,$proc_args)
{
	// only show for non resolved/dead tickets?
	
	$cfg = CerConfiguration::getInstance();
	global $cerberus_format;
	
	$due_date = new cer_DateTime($t_date);
	$now = mktime();
	
	$time_left = $due_date->mktime_datetime - $now;
		
	if ($proc_args->ticket_status == "resolved" || $proc_args->ticket_status == "dead" || $t_date == "0000-00-00 00:00:00") {
		$overdue = "cer_footer_text";
		$ticket_due = "-";
	}
	elseif($time_left < 0) {
		$overdue = "cer_footer_red";
		$ticket_due = strtolower(LANG_WORD_OVERDUE);
	}
	else {
		$overdue = "cer_footer_text";
//		$ticket_due = $cerberus_format->format_seconds($time_left);
		$ticket_due = cer_DateTimeFormat::secsAsEnglishString($time_left,true,1);
	}

	$ticket_age = "<span class=\"$overdue\">" . $ticket_due . "</span>";
	return $ticket_age;
}

// [JSJ]: Added function for string display of ticket priority.
function view_proc_print_priority_as_string($str,$proc_args)
{
    global $cer_hash;
    $priority_hash = $cer_hash->get_priority_hash();
     
     //[RJN] Adding in check for Priority and reference correct style class
	if($priority_hash[$str] != "Unassigned")
		$priority_class = "priority_".$priority_hash[$str];
	else
		$priority_class = "cer_footer_text";
		
    return '<span class="'.$priority_class.'">' . ($priority_hash[$str] ? $priority_hash[$str] : "unknown") . '</span>';
}

function view_proc_print_spam_probability($str,$proc_args)
{
	global $cerberus_db;
	static $bayes;
	
	if(empty($bayes)) $bayes = new cer_BayesianAntiSpam();
	
	$prob = sprintf("%0.2f%%",100 * $bayes->calculate_spam_probability($proc_args->ticket_id,$proc_args->ticket_spam_probability));
	
	$style = "cer_footer_text";
	if($prob >= 90.00) $style = "cer_footer_red";
		
	return '<span class="'.$style.'">'.$prob.'</span>';
}

function view_proc_print_spam_trained($str,$proc_args)
{
	switch($proc_args->ticket_spam_trained)
	{
		case 1:
			$rating = "Not Spam";
			break;
		case 2:
			$rating = "Spam";
			break;
		default:
			$rating = "";
			break;
	}
	
	return '<span class="cer_footer_text">'.$rating.'</span>';
}

?>