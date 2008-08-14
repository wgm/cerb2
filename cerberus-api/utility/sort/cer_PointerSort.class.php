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
\file cer_PointerSort.class.php
\brief Pointer Sorting

Class for performing direct or indirect sorts using pointers.

\author Jeff Standen, WebGroup Media LLC. <jeff@webgroupmedia.com>
\date 2004
*/

/** @addtogroup util_sort Utility::Sorting
 *
 * Sorting Functionality
 *
 * @{
 */

/** \brief
 *
 * Pointer Sorting Class
 *
 */
class cer_PointerSort {
 
	/** \brief
	 *
	 * Indirectly sorts an array by first creating a pointer array that maps out
	 * the $source_array collection.  The pointer array then references the 
	 * collection's objects and sorts itself on $sort_property using a bubble
	 * sort.  This functionality is perfect for cases where you can't or don't
	 * want to actually change the order of elements in an array (associative 
	 * indexing, etc.)
	 *
	 * \param $source_array (array) - The array to pointer sort
	 * \param $sort_property (string) - The property of the objects inside $source_array to sort on
	 * \return sorted pointer array
	 *
	 */
	function pointerSortCollection($source_array,$sort_property) {
		$i = 0;
		$ptr_array = array();
		
		if(!empty($source_array))
		foreach($source_array as $idx => $q) {
			$next = $i++;
			$ptr_array[$next] = &$source_array[$idx];
		}
		
		for($x = 0; $x < count($ptr_array); $x++) {
			for($y = count($ptr_array) - 1; $y > $x; $y--) {
				if($ptr_array[$x]->$sort_property > $ptr_array[$y]->$sort_property) {
					$tmp = &$ptr_array[$x];
					$ptr_array[$x] = &$ptr_array[$y];
					$ptr_array[$y] = &$tmp;
				}
			}
		}
		
//		echo "<pre>"; print_r($ptr_array); echo "</pre>";
		return $ptr_array;
	}
	
};

/** @} */
	
?>