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
\file cer_WeightedAverage.class.php
\brief Weighted Averages

Class for performing an average where the various sample are weighted.

\author Jeff Standen, WebGroup Media LLC. <jeff@webgroupmedia.com>
\date 2004
*/

/** @addtogroup util_math_statistics Utility::Math::Statistics
 *
 * Statistics Functionality
 *
 * @{
 */


/** \brief
 *
 * Weighted Average Class
 *
 */ 
class cer_WeightedAverage
{
	var $samples = 0;				//!< Total number of samples on the stack
	var $total_weight = 0;			//!< The total of all weights applied
	var $sum = 0;					//!< The sum of all samples
	
	/** \brief
	 * 
	 * Adds a weighted sample to the stack.
	 * 
	 */
	function addSample($sample,$weight=1) {
		$this->samples++;
		$this->total_weight += $weight;
		$this->sum += $sample * $weight;
	}
	
	/** \brief
	 *
	 * Returns the weighted average.
	 *
	 */
	function getAverage() {
		if($this->samples == 0)
			return 0;
		
		if(!$this->total_weight)
			return $this->sum;
			
		$avg = ($this->sum / $this->total_weight);
		return $avg;
	}
};

/** @} */

?>