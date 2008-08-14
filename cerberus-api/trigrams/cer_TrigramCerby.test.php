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

require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramCerby.class.php");

class cer_TrigramCerby_Test extends cer_TestCase
{
	var $cerby = null;		//<! Fixture 
	
	function cer_TrigramCerby_Test($name) {
		$this->cer_TestCase($name); // [BGH]: Call the parent object constructor.
	}
	
	function setUp() {
		$this->cerby = new cer_TrigramCerby();
	}
	
	function tearDown() {
		$this->cerby = null;
	}
	
	function test_getSuggestion() {
//		$result=$this->cerby->getSuggestion(31291);
//		print_r($result);
	}

	function test_goodSuggestion() {
//		$this->cerby->goodSuggestion(31285, 88);
//		$this->cerby->goodSuggestion(31289, 88);

//		$this->cerby->goodSuggestion(31288, 2);
//		$this->cerby->goodSuggestion(31288, 56);
//		$this->cerby->goodSuggestion(30944, 7);
	}

	function test_badSuggestion() {
//		$this->cerby->badSuggestion(31288, 7);
//		$this->cerby->badSuggestion(31288, 7);
	}

	function test_ask() {
		$results = $this->cerby->ask("How do I land something on the moon?",100,0);	
//		print_r($results);
		$results = $this->cerby->ask("Can I send in a check?",100,0);	
		$results = $this->cerby->ask("Can I pay with a check?",100,0);	
		
	}
	
	function test_getSimilar() {
		$results = $this->cerby->getSimilar(81,10,0);
//		print_r($results);
	}	
};
	
?>