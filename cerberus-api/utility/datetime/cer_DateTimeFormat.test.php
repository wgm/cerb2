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
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");

class cer_DateTimeFormat_Test extends cer_TestCase
{
	var $fDateTimeFormatClass = null;			//!< Fixture for cer_DateTime
	
	function cer_Date_Test($name) {
		
		$this->cer_TestCase($name); // [JAS]: Call the parent object constructor.
	}
	
	function setUp() {
		$this->fDateTimeFormatClass = new cer_DateTimeFormat();
	}
	
	function tearDown() {
		$this->fDateTimeFormatClass = null;
	}
	
	function test_secsAsEnglishString() {
		$expected = "1 min, 33 secs";
		$actual = cer_DateTimeFormat::secsAsEnglishString(93);
		$this->assertEquals($expected,$actual,"Failed formatting 93 seconds as a string.");

		$expected = "1 day, 59 mins, 1 sec";
		$actual = cer_DateTimeFormat::secsAsEnglishString(89941);
		$this->assertEquals($expected,$actual,"Failed formatting 89941 seconds as a string.");
		
		$expected = "0 sec";
		$actual = cer_DateTimeFormat::secsAsEnglishString(0);
		$this->assertEquals($expected,$actual,"Failed formatting 0 seconds as a string.");
		
		$expected = "11 days, 13 hrs, 46 mins, 39 secs";
		$actual = cer_DateTimeFormat::secsAsEnglishString(999999);
		$this->assertEquals($expected,$actual,"Failed formatting 999999 seconds as a string.");
		
		$expected = "11d, 13h, 46m, 39s";
		$actual = cer_DateTimeFormat::secsAsEnglishString(999999,true);
		$this->assertEquals($expected,$actual,"Failed formatting 999999 seconds as an abbreviated string.");
		
		$expected = "11d, 13h";
		$actual = cer_DateTimeFormat::secsAsEnglishString(999999,true,2);
		$this->assertEquals($expected,$actual,"Failed formatting 999999 seconds as an abbreviated string.");
	}
	
};

?>