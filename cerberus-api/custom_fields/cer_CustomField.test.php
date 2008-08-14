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
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");

class cer_CustomField_Test extends cer_TestCase
{
	var $fCustomField = null;
	
	function cer_CustomField_Test($name)
	{
		$this->cer_TestCase($name); // [JAS]: Call the parent object constructor.
	}
	
	function test_CustomField()
	{
		global $cerberus_db;
		
//		$this->fCustomField = new cer_CustomFieldGroupHandler();
//		$this->fCustomField->load_entity_groups(ENTITY_REQUESTER,1);
		
//		$this->fCustomField = new cer_CustomFieldGroupHandler();
//		$this->fCustomField->load_entity_groups(ENTITY_TICKET,2);
		
		$this->assert(true);
	}	
	
};

?>