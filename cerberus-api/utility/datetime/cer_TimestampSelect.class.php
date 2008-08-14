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
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class cer_TimestampSelect
{
	var $hrs_opts = array(
								"1" => "1",
								"2" => "2",
								"3" => "3",
								"4" => "4",
								"5" => "5",
								"6" => "6",
								"7" => "7",
								"8" => "8",
								"9" => "9",
								"10" => "10",
								"11" => "11",
								"12" => "12"
								);
								
	var $mins_opts = array(
								"00" => "00",
								"05" => "05",
								"10" => "10",
								"15" => "15",
								"20" => "20",
								"25" => "25",
								"30" => "30",
								"35" => "35",
								"40" => "40",
								"45" => "45",
								"50" => "50",
								"55" => "55"
								);
								
	var $ampm_opts = array(
								"am" => "am",
								"pm" => "pm"
								);
	
};

?>