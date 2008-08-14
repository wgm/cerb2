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

require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.class.php");

class cer_String_Test extends cer_TestCase
{
	function cer_Whitespace_Test($name) {
		$this->cer_TestCase($name); // [BGH]: Call the parent object constructor.
	}
	
	function setUp() {
	}
	
	function tearDown() {
	}
	
	function test_strSplit() {
		$expected = array("ma","ma","ma","ma","ma");
		$actual = cer_String::strSplit("mamamamama",2);
		$this->assertEquals($expected,$actual,"Failed splitting an even length string with an even chunk length.");
		
		$expected = array("ma","ma","ma","ma","ma","m");
		$actual = cer_String::strSplit("mamamamamam",2);
		$this->assertEquals($expected,$actual,"Failed splitting a odd length string with an even chunk length.");
		
		$expected = array("testing");
		$actual = cer_String::strSplit("testing",100);
		$this->assertEquals($expected,$actual,"Failed splitting a short string with a long chunk length.");
		
		$expected = array("testing");
		$actual = cer_String::strSplit("testing",0);
		$this->assertEquals($expected,$actual,"Failed splitting a short string with an empty chunk length.");
		
		$expected = array("testing");
		$actual = cer_String::strSplit("testing","");
		$this->assertEquals($expected,$actual,"Failed splitting a short string with a null string chunk length.");
		
		$expected = array("t","e","s","t","i","n","g");
		$actual = cer_String::strSplit("testing",-1);
		$this->assertEquals($expected,$actual,"Failed splitting a short string with a negativeg chunk length.");
		
		$expected = array();
		$actual = cer_String::strSplit(null,3);
		$this->assertEquals($expected,$actual,"Failed splitting a null string with a short chunk length.");
	}
}

?>