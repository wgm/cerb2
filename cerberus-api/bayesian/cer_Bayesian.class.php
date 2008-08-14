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
| File: cer_Bayesian.class.php
|
| Purpose: Bayesian Theorem and Probability Objects for Spam Handling,
|	Trigrams, etc.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once "site.config.php";
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");

define("PROBABILITY_CEILING",0.9999); // [JAS]: Don't give a higher probability than this
define("PROBABILITY_FLOOR",0.0001); // [JAS]: Don't give a lower probability than this
define("PROBABILITY_UNKNOWN",0.4); // [JAS]: A new probability, no previous training
define("PROBABILITY_MEDIAN",0.5); // [JAS]: The median we're deviating from to find 'interesting' words
define("MAX_INTERESTING_WORDS",15); // [JAS]: Maximum number of 'interesting' words used to score each email

class CER_BAYESIAN
{
	/**
	* @return probability (float)
	* @desc Combine a variable number of probabilities
	*/
	function combine_p($argv)
	{
		// [JAS]: Variable for all our probabilities multiplied, for Naive Bayes
		$AB = 1; // probabilities: A*B*C...
		$ZY = 1; // compliments: (1-A)*(1-B)*(1-C)...
		
		foreach($argv as $v) {
			$AB *= $v;
			$ZY *= (1-$v);
		}

		$combined_p = $AB / ($AB + $ZY);
		
		switch($combined_p)
		{
			case $combined_p > PROBABILITY_CEILING:
				return PROBABILITY_CEILING;
				break;
			case $combined_p < PROBABILITY_FLOOR:
				return PROBABILITY_FLOOR;
				break;
		}
		
		return $combined_p;
	}
	
	function get_median_deviation($p)
	{
		if($p > PROBABILITY_MEDIAN)
			return $p - PROBABILITY_MEDIAN;
		else
			return PROBABILITY_MEDIAN - $p;
	}
	
};




?>