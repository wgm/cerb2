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

class cer_String {
	
	function strSplit($string,$len) {
		
		$len = abs((int)$len);
		if(empty($len)) return array($string);
		
		$chunks = array();
				
		$remainder = $string;

		while(!empty($remainder)) {
			$chunks[] = substr($remainder,0,$len);
			$remainder = substr($remainder,$len);
		}
		
		return $chunks;
	}
	
};


?>