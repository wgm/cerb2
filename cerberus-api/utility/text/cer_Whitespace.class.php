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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/*!
\file cer_Whitespace.class.php
\brief Whitespace handling functions

Classes and methods for handling whitespace related functionality

\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
\date 2004
*/

/** @addtogroup util_text_whitespace Utility::Text::Whitespace
 *
 * Functions having to do with whitespace
 *
 * @{
 */
class cer_Whitespace {
 
	/** Used to merge whitespace
	 *
	 *	Merge many sequential whitespace characters into a single space.
	 *	\param $string - The string you want to modify.
	 *	\return A string with condensed whitespace.
	 */
	function mergeWhiteSpace($string)
	{
		// remove the whitespace from this text
		$string = str_replace("\n", " ", $string);
		$string = str_replace("\t", " ", $string);
		$string = str_replace("\r", " ", $string);
		$string = preg_replace("/([\ ])+/", "\\1", $string);

		return $string;
	}
	
};

/** @} */

?>