<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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

require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");

class cer_Bayesian_Test extends cer_TestCase {
	var $bayesClass = null;		//<! Fixture for cer_Bayesian
	
	function cer_Bayesian_Test($name) {
		$this->cer_TestCase($name); 
	}
	
	function setUp() {
		$this->bayesClass = new cer_Bayesian();
	}
	
	function tearDown() {
		$this->bayesClass = null;
	}
	
	function test_combine_p() {
		$expected = "0.9028";
		$actual = number_format(cer_Bayesian::combine_p(array(0.99,0.99,0.99,0.047225013,0.047225013,0.07347802,0.08221981,0.09019077,0.09019077,0.9075001,0.8921298,0.12454646,0.8568143,0.14758544,0.82347786)),4);
		$this->assertEquals($expected,$actual,"Failed to combine probabilities.");
	}

};

?>