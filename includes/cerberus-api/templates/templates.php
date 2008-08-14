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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/template-api/Smarty.class.php");

class CER_TEMPLATE_HANDLER extends Smarty
{
	function CER_TEMPLATE_HANDLER()
	{
		$this->Smarty();
		
		$this->debugging = false;
		$this->compile_check = true;
		$this->caching = false;
		
		if(defined("FILESYSTEM_PATH") && FILESYSTEM_PATH != "")
		{
			$this->compile_dir = FILESYSTEM_PATH . 'templates_c';
			$this->template_dir = FILESYSTEM_PATH . 'templates';
			$this->config_dir = FILESYSTEM_PATH . 'configs';
			$this->plugins_dir = FILESYSTEM_PATH . 'includes/template-api/plugins';
		}
	}
};

?>