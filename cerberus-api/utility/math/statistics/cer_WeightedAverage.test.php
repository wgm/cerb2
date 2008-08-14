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

require_once(FILESYSTEM_PATH . "cerberus-api/unit_test/cer_UnitTest.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");

class cer_WeightedAverage_Test extends cer_TestCase
{
	var $fWeightedAverage = null;			//!< Fixture for cer_WeightedAverage
	
	function cer_WeightedAverage_Test($name) {
		
		$this->cer_TestCase($name); // [JAS]: Call the parent object constructor.
	}
	
	function setUp() {
		$this->fWeightedAverage = new cer_WeightedAverage();
	}
	
	function tearDown() {
		$this->fWeightedAverage = null;
	}
	
	function test_addSample() {
		$this->fWeightedAverage->addSample(99,15);
		$expected = 1;
		$actual = $this->fWeightedAverage->samples;
		$this->assertEquals($expected,$actual,"Failed on adding a first sample of a set.");
		
		$this->fWeightedAverage->addSample(22,56);
		$expected = (99 * 15) + (22 * 56); // weighted sum
		$actual = $this->fWeightedAverage->sum;
		$this->assertEquals($expected,$actual,"Failed adding two samples.");
		
		$expected = 15 + 56; // weights
		$actual = $this->fWeightedAverage->total_weight;
		$this->assertEquals($expected,$actual,"Failed on adding total weights.");
	}
	
	function test_getAverage() {
		$expected = 62.55;
		$this->fWeightedAverage->addSample(56,22);
		$this->fWeightedAverage->addSample(20,1);
		$this->fWeightedAverage->addSample(75,15);
		$actual = (float) number_format($this->fWeightedAverage->getAverage(),2);
		$this->assertEquals($expected,$actual,"Failed on averaging 3 non-trivial weighted samples.");
		
		$expected = 0;
		$this->fWeightedAverage = new cer_WeightedAverage();
		$this->fWeightedAverage->addSample(20,0);
		$actual = (int) $this->fWeightedAverage->getAverage();
		$this->assertEquals($expected,$actual,"Failed on 0 value weight sample (div by zero).");
	}
	
};

?>