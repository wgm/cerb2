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
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file cer_DateTime.class.php
\brief Date Handling Tools

Classes and methods for handling various date related functionality.

\author Jeff Standen, WebGroup Media LLC. <jeff@webgroupmedia.com>
\date 2004
*/

/** @addtogroup util_datetime Utility::DateTime
 *
 * Date and Time Utilities
 *
 * @{
 */

 
/*! \brief
 *
 * Date/Time Handling Class
 *
 */
class cer_DateTime {
	
	var $mktime_datetime = null;		//!< Date/Time stored in mktime format.
	
	/** \brief
	 *
	 * Constructor
	 *
	 */
	 function cer_DateTime($date=null) {
	 	if($date != null) {
			$this->mktime_datetime = $this->parseDateTime($date);
	 	}
	 }

	function is_epoch($timestamp, $allow_really_old = false, $allow_future = true) {
		if(!is_numeric($timestamp) || $timestamp < 0) {
			return false;
		}
		if($timestamp < 100000000 && !$allow_really_old) {
			return false;
		}
		if($timestamp > time() && !$allow_future) {
			return false;
		}
		if($timestamp >  2147483648) {
			return false;
		}
		return true;
	}

	 
	 /** \brief
	  *
	  * Parse a DateTime stamp according to the format given.  Store it as
	  * an epoch timestamp so we can easily return it in whatever format
	  * the caller needs.
	  *
	  */
	 function parseDateTime($date) {

		// [BGH]: Check if it's an epoch date
		// on 64bit systems strtotime parses
		// the epoch as a date YYYYYYMMDD
		if(cer_DateTime::is_epoch($date)) {
			return $date;
		}
	 	
 		// [JAS]: Check known date formats.
 		$auto = strtotime($date);
 		if ($auto !== -1) {
 			return $auto;
 		}
	 		
		return false;
	 }
	 
	 /*! \brief
	  *
	  * Check if a passed number is a valid epoch timestamp according
	  * to mktime().
	  *
	  */
	 function isMktime($timestamp) {
		if (is_numeric($timestamp) 
				&& ($timestamp > 0) // [JAS]: range is above the epoch
				&& ($timestamp < 2144448000) // [JAS]: range is below 32-bit armageddon
				) {
			return true;
 		}
 		
 		return false;
	 }
	 
	 /*! \brief
	  *
	  * Checks if any of a variable number of arguments are truly empty.  
	  *	A value of '0' is not considered empty.
	  * \todo This should probably be moved into another utility class. [JAS]
	  *
	  */
	 function _noneAreEmpty() {
	 	foreach (func_get_args() as $idx => $arg) {
	 		if (empty($arg) && $arg !== 0) {
	 			return false;
	 		}
	 	}
	 	
	 	return true;
	 }
	 
	 // [JAS]: This function will always use the server timezone date
	 function getDate($format=LANG_DATE_FORMAT_STANDARD) {
		return strftime($format,$this->mktime_datetime);
	 }
	 
	 // [JAS]: This function will return datestamps in the current user's timezone
	 function getGMTDate($format = "m/d/y H:i:s") {
		return gmdate($format,$this->mktime_datetime);
	 }
	 
	 function getUserDate($format=LANG_DATE_FORMAT_STANDARD) {
		$cfg = CerConfiguration::getInstance();
		global $session;
		
		$offset = $cfg->settings["server_gmt_offset_hrs"] * 3600;
		$user_offset = $session->vars["login_handler"]->user_prefs->gmt_offset * 3600;
		
		$now = $this->mktime_datetime - $offset; // to GMT from server
		$now += $user_offset; // to user from server

		return strftime($format,$now);
	 }
	 
	 function changeGMTOffset($to_offset,$from_offset=null) {
	 	$cfg = CerConfiguration::getInstance();
	 	if(empty($from_offset)) 
	 		$from_offset = $cfg->settings["server_gmt_offset_hrs"];
	 		
	 	$from_offset *= 3600;
	 	$to_offset *= 3600;
	 	
	 	$this->mktime_datetime -= $from_offset; // to GMT from server
	 	$this->mktime_datetime += $to_offset; // to destination timezone
	 }
	 
	 // [JAS]: This will turn any date in the future into the current date & time.
	 // 	Mostly used by areas where you need time santity, such as incoming e-mail
	 //		times from RFC headers, etc.
	 function _trimFutureDateStamp() {
	 	if($this->mktime_datetime > mktime()) {
	 		$this->mktime_datetime = mktime();
	 	}
	 }
	 
	// [JAS]: Wrapper for handling RFC->Database dates in one function.
	// Argument: Wed, 11 Feb 2004 17:28:56 -0800
	function rfcDateAsDbDate($date) {
		// [JAS]: If no date was passed, use todays date.
		if(empty($date)) $date = date("Y-m-d H:i:s");
		
		$this->cer_DateTime($date);
		 
		// [JAS]: If the timestamp didn't parse right, make the date right now.
		if(empty($this->mktime_datetime)) $this->cer_DateTime(date("Y-m-d H:i:s"));
		 
		 $this->_trimFutureDateStamp();
		return $this->getDate("%Y-%m-%d %H:%M:%S");
	}
	
	function rfcDateToLocal($date) {
		// [JAS]: If no date was passed, use todays date.
		if(empty($date)) $date = date("Y-m-d H:i:s");
		
		$this->cer_DateTime($date);
	    $this->_trimFutureDateStamp();
		return date("r",$this->mktime_datetime);
	}
	
};

/** @} */

?>
