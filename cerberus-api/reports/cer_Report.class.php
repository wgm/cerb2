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
| File: cer_Report.class.php
|
| Purpose: Report-related objects
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|		Trent Ramseyer		(trent@webgroupmedia.com)		[TAR]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file cer_Report.class.php
\brief Reporting Classes

Classes for running reports.

\author Jeff Standen, WebGroup Media LLC. <jeff@webgroupmedia.com>
\author Trent Ramseyer, WebGroup Media LLC. <trent@webgroupmedia.com>
\date 2004
*/

require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/calendar.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

/** @addtogroup reports Reporting
 *
 * Reporting Classes
 *
 * @{
 */

define("SPACER_1PX",'<img src="includes/images/spacer.gif" width="1" height="1">');
define("SPACER_2PX",'<img src="includes/images/spacer.gif" width="1" height="2">');
define("SPACER_5PX",'<img src="includes/images/spacer.gif" width="1" height="5">');
define("REPORTS_DIR", FILESYSTEM_PATH . "includes/reports/");

/*! \brief Report Class
 * 
 * The report class stores meta-data about report modules in REPORTS_DIR.
 * cer_ReportsHandler creates a collection of cer_Report objects.
 * 
 * \sa cer_ReportsHandler
 */
class cer_Report
{
	var $report_name = null; 		//!< The current report name
	var $report_summary = null; 	//!< The current report description
	var $report_url = null; 		//!< The dynamically generated report URL
	var $report_tag = null;			//!< The report tag
	var $report_file = null;		//!< The report module in the file system
};


/*! \brief Reports Handler
 *
 * Reads in and stores information about all available reports.  Parses reports
 * to read the meta-data of each one.
 *
 */
class cer_ReportsHandler
{
	var $reports = array();		//!< An array of report groups for sorting
		
	function cer_ReportsHandler()
	{
		//* \todo [JAS]: Need to fix this sloppy global
		global $priv;		//<! Pointer of the privilege object
		
		$this->db = cer_Database::getInstance();
		
		if(!$priv->has_priv(ACL_REPORTS,BITGROUP_3)) return false;
		
		// [JAS]: Read in available reports from the reports directory.
		$this->import_report_dir();
	}
	
	/** \brief
	 *
	 * Add a new report to the reports handler.
	 * \return void
	 *
	 */
	function add_report($report_name=null,$report_summary=null,$report_tag=null,$file=null)
	{
		$new_report = new cer_Report();
		$new_report->report_name = $report_name;
		$new_report->report_summary = $report_summary;
		$new_report->report_url = cer_href("reports.php?report=" . $report_tag);
		$new_report->report_tag = $report_tag;
		$new_report->report_file = $file;
		$this->reports[$report_tag] = $new_report;
				
		asort($this->reports); // [JAS]: Sort reports alphabetically
		
		unset($new_report);
	}
	
	/** \brief
	 * 
	 * Scan for a meta-data tag within a report module.
	 *
	 */
	function _scan_for_tag($tag,$line)
	{
	    if(strstr($line,"define(\"$tag\"") !== false)
	    {
	      $line = str_replace("define(\"$tag\",\"","",$line);
	      // [JAS]: Find the semicolon line terminator, to exclude anything
	      //	after the line, such as comments.
	      $line_terminator = strpos($line,";");
	      if(!$line_terminator) $line_terminator = strlen($line)-1;
	      $script_tag = substr($line,0,$line_terminator-2);
	      return $script_tag;
	    }
	    else return false;
	}

	/** \brief
	 *
	 * Read in any report modules in the specified REPORTS_DIR.
	 *
	 */
	function import_report_dir()
	{
		$reports_path = REPORTS_DIR;
		if ($handle = opendir($reports_path))
		{
			while (false !== ($file = readdir($handle))) 
			{
				$found = 0;
				$report_name = "";
				$report_summary = "";      
				$report_tag = "";      
				
				// [JAS]: only pull report modules, exclude the ., the ..
				//	and CVS dirs, etc.
				if($file != "." 
					&& $file != ".." 
					&& $file !="CVS" 
					&& substr($file,-4) == ".php") 
				{
					// [JAS]: Probe the report module looking for report info.
					if($report_handle = fopen($reports_path . $file, "r"))
					{
						$found=0;
						while(!feof($report_handle) && $found < 4)
						{
							$line = fgets($report_handle,512);
							
							if(empty($report_name)) { if($report_name = $this->_scan_for_tag("REPORT_NAME",$line)) $found++; }
							if(empty($report_summary)) { if($report_summary = $this->_scan_for_tag("REPORT_SUMMARY",$line)) $found++; }
							if(empty($report_tag)) { if($report_tag = $this->_scan_for_tag("REPORT_TAG",$line)) $found++; }
						}
						
						if($found == 3)	{
							$this->add_report($report_name,$report_summary,$report_tag,$file); // [JAS]: add to reports array
						}
						else {
							$report_name = null;
							$report_summary = null;
							$report_tag = null;
						}
						
					}
					fclose($report_handle);
				}
			}
			closedir($handle);
		}
	}
	
	/** \brief
	 *
	 * Runs the selected report by invoking its module constructor.
	 *
	 */
	function build_report($report=null,&$cer_tpl)
	{
		if(empty($report)) return false;

		require (REPORTS_DIR . $this->reports[$report]->report_file);
		$this->report = init_report($this->db,$cer_tpl);

	}
};


/*! \brief
 *
 * Container for holding various formats of the report dates and date ranges.
 *
 */
class cer_ReportDates
{
	var $range_from = null; 		//!< The starting date of the date range (if any) as a cer_DateTime
	var $range_to = null; 			//!< The ending date of the date range (if any) as a cer_DateTime
	var $from_date = null; 			//!< The beginning date in database format Y-m-d H:i:s
	var $to_date = null; 			//!< The ending date in database format Y-m-d H:i:s
	var $from_date_calender = null; //!< The beginning date as a string (m/d/y)
	var $to_date_calender = null; 	//!< The ending date as a string (m/d/y)
	var $date_range_str = null;		//!< A string of the date range of the report
	
	/** \brief Private
	 *
	 * Private method to set the from date in a report date range.
	 *
	 */
	function _setFromDate($date) {
		$this->range_from = new cer_DateTime($date);
		$this->from_date = $this->range_from->getDate("%Y-%m-%d");
		$this->from_date_calender = $this->range_from->getDate("%m/%d/%y");
	}
	
	/** \brief Private
	 *
	 * Private method to set the to date in a report date range.
	 *
	 */
	function _setToDate($date) {
		$this->range_to = new cer_DateTime($date);
		$this->to_date = $this->range_to->getDate("%Y-%m-%d");
		$this->to_date_calender = $this->range_to->getDate("%m/%d/%y");
	}
	
	/** \brief Sets the report date range.
	 *
	 * Stores dates in various formats for the displayed report to utilize.
	 *
	 * \param $from (date)
	 * \param $to (date)
	 * \return void
	 *
	 * \code
	 * \\ Example 1
	 * $from = mktime(0,0,0,01,01,2004);
	 * $to = mktime(0,0,0,12,31,2004);
	 * $report->report_dates->setDateRange($from,$to);
	 *
	 * \\ Example 2
	 * $report->report_dates->setDateRange("01/01/04","12/31/04"); 
	 * \endcode
	 *
	 */
	function setDateRange($from,$to) {
		$this->_setFromDate($from);
		$this->_setToDate($to);
		
		$this->date_range_str = sprintf("%s to %s",
			$this->range_from->getDate("%B %d, %Y"),
			$this->range_to->getDate("%B %d, %Y")
			);
	}
};


/*! \brief Report Data Class
 *
 *  Contains the report output for display.
 */
class cer_ReportData
{
	var $cal = null;			//!< Pointer to an instance of the cer_Calendar class
	var $quick_links = array(); //!< An array of quick report links (current month, current year, all users, etc.)
	var $rows = array(); 		//!< An array of report rows (made up of columns) for display
	var $user_data = null;		//!< A pointer to the user data object for user dropdowns
	var $queue_data = null;		//!< A pointer to the queue data object for queue dropdowns
	var $group_data = null;		//!< A pointer to the group data object for group dropdowns
	
	function cer_ReportData() {
		$this->user_data = new cer_ReportUserData();
		$this->queue_data = new cer_ReportQueueData();
		$this->group_data = new cer_ReportGroupData();
	}
};


/*! \brief
 * 
 * Report Row
 *
 */
class cer_ReportDataRow
{
	var $style = "cer_maintable_text";	//!< CSS style to use row a table row
	var $bgcolor = "#FFFFFF";			//!< Background color to use for a table row
	var $cols = array();				//!< Array of cer_ReportDataCol columns making up the row
};


/*! \brief
 * 
 * Report Column
 *
 */
class cer_ReportDataCol
{
	var $data = null;		//!< Cell data
	var $style = null;		//!< Cell style
	var $align = "left";	//!< Cell alignment
	var $valign = "middle";	//!< Cell vertical alignment
	var $width = "";		//!< Cell width (% or pixel size)
	var $col_span = 1;		//!< Cell column span (width in columns)
	
	function cer_ReportDataCol($data=null)
	{
		$this->data = $data;
	}
};


/*! \brief
 * 
 * A Report Quick Link
 *
 */
class cer_ReportLink
{
	var $link_name;		//!< Quick Link Name
	var $link_url;		//!< Quick Link URL
};


/*! \brief
 * 
 * User data container
 *
 */
class cer_ReportUserData
{
	var $report_user_id = null;		//!< Selected User ID
	var $user_options = array();	//!< Array of users for drawing dropdown
};


/*! \brief
 * 
 * Queue data container
 *
 */
class cer_ReportQueueData
{
	var $report_queue_id = null;	//!< Selected Queue ID
	var $queue_list = array();		//!< Array of queues from drawing dropdown
};


/*! \brief
 * 
 * Group data container
 *
 */
class cer_ReportGroupData
{
	var $report_group_id = null;	//!< Selected Group ID
	var $group_list = array();		//!< Array of groups from drawing dropdown
};


/*! \brief Report Module
 *
 * cer_ReportModule is the superclass of all Report Modules in the REPORTS_DIR directory.
 *
 */
class cer_ReportModule
{
	var $db = null;					//!< Pointer to the database object
	var $report_name = null;		//!< The current report module name
	var $report_summary = null;		//!< The current report module description
	var $report_tag = null;			//!< The current report module tag
	var $report_data = null;		//!< A pointer to an instance of the Cer_ReportData class for holding report output
	var $report_dates = null;		//!< A pointer to the cer_ReportDates container

	function cer_ReportModule()
	{
		$this->db = cer_Database::getInstance();
		$this->report_data = new cer_ReportData();
		$this->report_dates = new cer_ReportDates();
	}
	
	/** \brief
	 *
	 * Callback function for handling calendar days in cer_Calendar
	 *
	 */
	function void_day_function(&$o_day,$month,$year)
	{
		global $mo_offset; // clean
		global $report;
		
		if($o_day == null) return true;
		
		$o_day->day_url = cer_href(sprintf("reports.php?report=$report&mo_offset=%d&mo_d=%d&mo_m=%d&mo_y=%d",
			$mo_offset,$o_day->day,$month,$year));
			
		return($o_day);
	}

	/** \brief
	 *
	 * Callback function for handling calendar months in cer_Calendar
	 *
	 */
	function void_month_function($mo_offset=0,$prev_mo=-1,$next_mo=1)
	{
		global $report;
		
		$o_links = array();
		
		$o_links["prev_mo"] = cer_href($_SERVER["PHP_SELF"] . "?report=$report&mo_offset=$prev_mo");
		$o_links["next_mo"] = cer_href($_SERVER["PHP_SELF"] . "?report=$report&mo_offset=$next_mo");
		
		return($o_links);
	}
	

	/** \brief
	 *
	 * Initializes the cer_Calendar object and registers the local callbacks.
	 *
	 */
	function _init_calendar()
	{
		//* \todo We should clean these globals up if at all possible.  Perhaps pass a container object from the parent.
		global $mo_offset, $mo_m, $mo_d, $mo_y, $mt_m, $mt_d, $mt_y;
		
		$cal = new cer_Calendar($mo_offset);
		$cal->register_callback_day_links("void_day_function",$this);
		$cal->register_callback_month_links("void_month_function",$this);
		$cal->populate_calendar_matrix();
		$this->report_data->cal = $cal;
		
		$this->report_data->quick_links[0] = new cer_ReportLink();
		$this->report_data->quick_links[0]->link_name = sprintf("Show Current Month (%s)",date("F Y"));
		$this->report_data->quick_links[0]->link_url = cer_href(sprintf("reports.php?report=%s",$this->report_tag));
		
		$this->report_data->quick_links[1] = new cer_ReportLink();
		$this->report_data->quick_links[1]->link_name = sprintf("Show Current Year (%s)",date("Y"));
		$this->report_data->quick_links[1]->link_url = cer_href(sprintf("reports.php?report=%s&from_date=01/01/%02d&to_date=12/31/%02d",$this->report_tag,date("y"),date("y")));
		
		$num_days = $cal->get_days_in_month($cal->cal_month,$cal->cal_year);
		
		if(!empty($mo_m) && !empty($mo_d) && !empty($mo_y))
		{
			$range_from = mktime(0,0,0,$mo_m,$mo_d,$mo_y);
						
			if(!empty($mt_m) && !empty($mt_d) && !empty($mt_y))
				$range_to = mktime(0,0,0,$mt_m,$mt_d,$mt_y);
			else
				$range_to = mktime(0,0,0,$mo_m,$mo_d,$mo_y);
		}
		else
		{
			$range_from = mktime(0,0,0,$cal->cal_month,1,$cal->cal_year);
			$range_to = mktime(0,0,0,$cal->cal_month,$num_days,$cal->cal_year);
		}
		
		$this->report_dates->setDateRange($range_from,$range_to);
	}
	
	/** \brief
	 *
	 * Builds the user container.
	 *
	 */
	function _init_user_list()
	{
		//* \todo We need to clean these globals up.
		global $cer_hash, $report_user_id;
		
		$this->report_data->user_data->report_user_id = $report_user_id;
		
		// [TAR]: Create User Array
		$user_information = $cer_hash->get_user_hash(HASH_USER_ANY_Q,"user_name");
			
		// [TAR]: Return Variables to Parent
		foreach ($user_information as $idx => $user)
		{
			$this->report_data->user_data->user_options[$user->user_id] = sprintf("%s (%s)",
						$user->user_name,
						$user->user_login
					);
		}
			
	}
	
	/** \brief
	 *
	 * Builds the queue container.
	 *
	 */
	function _init_queue_list()
	{
		//* \todo We need to clean these globals up.
		global $cer_hash, $report_queue_id;
		
		$this->report_data->queue_data->report_queue_id = $report_queue_id; // [JAS]: Breaking in PHP5?
		// [TAR]: Create Queue Array
		$queue_information = $cer_hash->get_queue_hash($filter=HASH_Q_ALL);
			
		// [TAR]: Return Variables to Parent
		foreach ($queue_information as $idx => $queue)
		{
			$this->report_data->queue_data->queue_list[$queue->queue_id] = sprintf("%s",
						$queue->queue_name
					);
		}
			
	}
	
	/** \brief
	 *
	 * Builds the group container.
	 *
	 */
	function _init_group_list()
	{
		//* \todo We need to clean these globals up.
		global $cer_hash, $report_group_id;
		
		$this->report_data->group_data->report_group_id = $report_group_id;
		// [TAR]: Create Group Array
		$group_information = $cer_hash->get_group_hash($filter=HASH_GROUP_HASH);
		
		// [TAR]: Return Variables to Parent
		if (!empty($group_information)){
			foreach ($group_information as $idx => $group)
			{
				$this->report_data->group_data->group_list[$group->group_id] = sprintf("%s",
							$group->group_name
						);
			}
		}
	}
	
	
	/** \brief
	 *
	 * Reports an action name from the audit log.
	 * \todo [JAS]: 1. This really belongs in the audit log class, not the report class 2. we should use the language system 3. We should use the proper audit log constants not integers.
	 *
	 */
	function get_action_name($action_id)
	{
		switch($action_id)
		{
			case 1:
				return "Opened";
			break;
			case 2:
				return "Assigned";
			break;
			case 3:
				return "Changed Status";
			break;
			case 4:
				return "Replied";
			break;
			case 5:
				return "Commented";
			break;
			case 6:
				return "Changed Queue";
			break;
			case 7:
				return "Changed Priority";
			break;
			case 8:
				return "Requester Response";
			break;
			case 9:
				return "Ticket Reopened";
			break;
			case 10:
				return "Custom Fields Requester";
			break;
			case 11:
				return "Custom Fields Ticket";
			break;
			case 12:
				return "Ticket Cloned From";
			break;
			case 13:
				return "Rule Changed Owner";
			break;
			case 14:
				return "Rule Changed Status";
			break;
			case 15:
				return "Rule Changed Queue";
			break;
			case 16:
				return "Ticket Cloned";
 			break;
			case 17:
 				return "Thread Forwarded";
			break;
			case 18:
				return "Add Requester";
			break;
			case 19:
				return "Merge Ticket";
			break;
			case 20:
				return "Changed Priority";
 			break;
 			case 21:
 				return "Thread Bounce";
 			break;
			default:
				return "";
			break;
		}
	}	
};

/** @} */

?>