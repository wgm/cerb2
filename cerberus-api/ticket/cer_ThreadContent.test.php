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

require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");

class cer_ThreadContentHandler_Test extends cer_TestCase
{
	function cer_ThreadContentHandler_Test($name) {
		$this->cer_TestCase($name); // [BGH]: Call the parent object constructor.
	}
	
	function setUp() {
	}
	
	function tearDown() {
	}
	
	function test_cer_ThreadContentHandler() {
		$handler = new cer_ThreadContentHandler();
//		$handler->loadTicketContentDB(31085);
	}
};

?>