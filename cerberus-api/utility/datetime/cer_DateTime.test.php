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
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

class cer_Date_Test extends cer_TestCase
{
	var $fDateClass = null;			//!< Fixture for cer_DateTime
	
	function cer_Date_Test($name) {
		
		$this->cer_TestCase($name); // [JAS]: Call the parent object constructor.
	}
	
	function setUp() {
		$this->fDateClass = new cer_DateTime();
	}
	
	function tearDown() {
		$this->fDateClass = null;
	}
	
	function test_parseDate() {
		
		$expected = mktime(10,35,0,11,26,03); // [JAS]: 11/26/03 10:35:00am as a timestamp
		
		$actual = cer_DateTime::parseDateTime("11/26/03 10:35:00");
		$this->assertEquals($expected,$actual,"Failed parse date when autodetecting a string.");
		
		$actual = cer_DateTime::parseDateTime("2003-11-26 10:35:00");
		$this->assertEquals($expected,$actual,"Failed parse date when autodetecting a DB date.");
		
		$actual = cer_DateTime::parseDateTime("26-nov-03 10:35:00");
		$this->assertEquals($expected,$actual,"Failed parse date when autodetecting a Euro date.");
		
		$actual = cer_DateTime::parseDateTime($expected);
		$this->assertEquals($expected,$actual,"Failed parse date when autodetecting an epoch date.");

		$expected = mktime();
		$actual = $this->fDateClass->parseDateTime(date("r",$expected));
		$this->assertEquals($expected,$actual,"Failed parsing an RFC date.");

		$expected = 1124873660;
		$actual = $this->fDateClass->parseDateTime(1124873660);
		$this->assertEquals($expected,$actual,"Failed parsing a current epoch timestamp.");
		
		$expected = mktime(10,35,0,2,1,2000); // [JAS]: 02/01/00 10:35:00am as a timestamp
		
		$actual = cer_DateTime::parseDateTime("2/1/00 10:35:00");
		$this->assertEquals($expected,$actual,"Failed parse date on year 2000 string.");

	}
	
	function test__noneAreEmpty() {
		
		list($a, $b, $c) = array(null,'b','c');
		$actual = cer_DateTime::_noneAreEmpty($a,$b,$c);
		$this->assertEquals(false,$actual,"Failed testing with an expected empty argument.");
		
		list($a, $b, $c) = array(0,'b','c');
		$actual = cer_DateTime::_noneAreEmpty($a,$b,$c);
		$this->assertEquals(true,$actual,"Failed testing with a '0' but no empty arguments.");
		
		list($a, $b, $c) = array('a','b','c');
		$actual = cer_DateTime::_noneAreEmpty($a,$b,$c);
		$this->assertEquals(true,$actual,"Failed testing with no empty arguments.");
	}
	
	function test_getDate() {
		$expected = "11/26/03 10:35:00";
		$this->fDateClass = new cer_DateTime("2003-11-26 10:35AM");
		$actual = $this->fDateClass->getDate("%m/%d/%y %H:%M:%S");
		$this->assertEquals($expected,$actual,"Failed comparing getDate() default to string.");

		$not_expected = "01/26/04 20:12:15";
		$this->fDateClass = new cer_DateTime("2003-11-26 10:35AM");
		$actual = $this->fDateClass->getDate();
		$result = $not_expected == $actual;
		$this->assertEquals(false,$result,"Failed comparing getDate() to intentionally bad string.");
		
		$expected = "03/01/02 03:30:00";
		$this->fDateClass = new cer_DateTime("2002-02-28 19:30:00");
		$actual = $this->fDateClass->getGMTDate();
		$this->assertEquals($expected,$actual,"Failed comparing getGMTDate() over a leap year.");
	}
	
	function test_rfcDateAsDbDate() {
		// [JAS]: Should this return a date as GMT or Localized?
		$expected = mktime();
		$actual = $this->fDateClass->parseDateTime(date("r",$expected));
		
		$expected = "2004-02-11 17:28:56";
		$actual = $this->fDateClass->rfcDateAsDbDate("Wed, 11 Feb 2004 17:28:56 -0800");
		$this->assertEquals($expected,$actual,"Failed converting an RFC date to a database date.");
		
		$expected = date("Y-m-d H:i:s");
		$actual = $this->fDateClass->rfcDateAsDbDate("Mon, 03 May 04 12:17:43 ora legale Europa occidentale");
		$this->assertEquals($expected,$actual,"Failed converting an foreign date to a database date.");

		$expected = date("Y-m-d H:i:s");
		$actual = $this->fDateClass->rfcDateAsDbDate("");
		$this->assertEquals($expected,$actual,"Failed graceful handling of a blank RFC date.");
	}
	
	function test_changeGMTOffset() {
		$expected = mktime(10,35,00,11,26,2000);
		$this->fDateClass = new cer_DateTime("2000-11-26 13:35:00");
		$this->fDateClass->changeGMTOffset("0","+3.0");
		$actual = $this->fDateClass->mktime_datetime;
		$this->assertEquals((double)$expected,(double)$actual,"Failed changing a timestamp from GMT+3 to GMT.");
	}
};

?>