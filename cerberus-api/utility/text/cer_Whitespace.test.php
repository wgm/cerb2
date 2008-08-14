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

require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");

class cer_Whitespace_Test extends cer_TestCase
{
	function cer_Whitespace_Test($name) {
		$this->cer_TestCase($name); // [BGH]: Call the parent object constructor.
	}
	
//	function setUp() {
//	}
//	
//	function tearDown() {
//	}
	
	function test_mergeWhitespace() {
		$expected = " ";
		$actual = cer_Whitespace::mergeWhitespace(" ");
		$this->assertEquals($expected,$actual,"Testing that a single whitespace doesn't get removed by itself");

		$expected = " ";
		$actual = cer_Whitespace::mergeWhitespace("  ");
		$this->assertEquals($expected,$actual,"Many spaces become one.");

		$expected = " this is a whitespace test! ";
		$actual = cer_Whitespace::mergeWhitespace("  this  is  a  whitespace  test!  ");
		$this->assertEquals($expected,$actual,"Simple mergeWhitespace test.");

		$expected = " this is a whitespace test! ";
		$actual = cer_Whitespace::mergeWhitespace("  \tthis \r is \n a \t whitespace\r  test!\n  ");
		$this->assertEquals($expected,$actual,"Simple mergeWhitespace test with \\r\\n\\t .");

		$expected = " this is a whitespace test! ";
		$actual = cer_Whitespace::mergeWhitespace("\r\nthis\r\nis\r\na\r\nwhitespace\r\ntest!\r\n");
		$this->assertEquals($expected,$actual,"Simple mergeWhitespace test with \\r\\n whitespace characters.");
		
		$expected = " ";
		$actual = cer_Whitespace::mergeWhitespace("\r\n\r\n\t \r\n\r\n\r \n\r\n");
		$this->assertEquals($expected,$actual,"Simple mergeWhitespace test with only \\r\\n whitespace characters.");
	}
	
	
};

?>