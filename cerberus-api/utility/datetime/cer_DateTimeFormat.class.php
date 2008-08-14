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
\file cer_DateTimeFormat.class.php
\brief Date and Time Formatting Tools

Classes and methods for handling various forms date and time formatting.

\author Jeff Standen, WebGroup Media LLC. <jeff@webgroupmedia.com>
\date 2004
*/


/** @addtogroup util_datetime
 *
 * @{
 */


/*! \brief
 *
 * Date and Time Formatting Functionality
 *
 */
class cer_DateTimeFormat {

	/** \brief
	 *
	 * Prints an English string for any number of seconds (e.g.: 2 days, 3 hrs, 15 min, 2 sec)
	 *
	 * \code
	 * echo cer_DateTimeFormat::secsAsEnglishString(999999);
	 * \endcode
	 * Output: 11 days, 13 hrs, 46 min, 39 sec
	 *
	 * \param $secs (integer) seconds
	 * \return string
	 */
	function secsAsEnglishString($secs=0,$abbrev=false,$places=0)
	{
		$times = array(0,0,0,0);
		
		switch($secs)
		{
			case $secs >= 86400: // days
				while($secs>=86400)
				{ $secs -= 86400; $times[0]++; }
			case $secs >= 3600: // hours
				while($secs>=3600)
				{ $secs -= 3600; $times[1]++; }
			case $secs >= 60: // mins
				while($secs>=60)
				{ $secs -= 60; $times[2]++; }
			case $secs >= 0: // sec remainder
				$times[3] = floor($secs);
			break;
		}

		// [JAS]: Write the English readable part.
		if(!empty($times[0])) { $times[0] = $times[0] . cer_DateTimeFormat::getSuffix("d",$times[0],$abbrev); } else unset($times[0]);
		if(!empty($times[1])) { $times[1] = $times[1] . cer_DateTimeFormat::getSuffix("h",$times[1],$abbrev); } else unset($times[1]);
		if(!empty($times[2])) { $times[2] = $times[2] . cer_DateTimeFormat::getSuffix("m",$times[2],$abbrev); } else unset($times[2]);
		if(!empty($times[3])) { $times[3] = $times[3] . cer_DateTimeFormat::getSuffix("s",$times[3],$abbrev); } else unset($times[3]);
		
		// force number of places
		if($places && count($times) > $places) {
			$times = array_slice($times,0,$places);
		}

		$sep = ", ";

		$time_string = implode($sep,$times);

		// [JAS]: Make sure we're at least returning something for 0 seconds.
		if(empty($time_string)) {
			if($abbrev)
				$time_string = "0s";
			else
				$time_string = "0 sec";
		}
		
		return $time_string;
	}
	
	function getSuffix($type,$num,$abbrev=false) {
		$suffix = null;
		
		switch($type) {
			case "d":
				if($abbrev) {
					$suffix = LANG_DATE_SHORT_DAYS_ABBR;
				}
				else {
					$suffix = (($num>1)?" " . LANG_DATE_SHORT_DAYS :" " . LANG_DATE_SHORT_DAY);
				}
				break;
			case "h":
				if($abbrev) {
					$suffix = "h";
				}
				else {
					$suffix = (($num>1)?" " . LANG_DATE_SHORT_HOURS :" " . LANG_DATE_SHORT_HOUR);
				}
				break;
			case "m":
				if($abbrev) {
					$suffix = "m";
				}
				else {
					$suffix = (($num>1)?" " . LANG_DATE_SHORT_MINUTES :" " . LANG_DATE_SHORT_MINUTE);
				}
				break;
			case "s":
				if($abbrev) {
					$suffix = "s";
				}
				else {
					$suffix = (($num>1)?" " . LANG_DATE_SHORT_SECONDS :" " . LANG_DATE_SHORT_SECOND);
				}
				break;
		}
		
		return $suffix;
	}
};

/** @} */

?>