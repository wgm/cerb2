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
// General purpose bit flags.
// You'll probably want to store these in the database in a BIGINT or as a 
// 	string of appropriate length (digit count of sum of all bitflags)
/*
\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2003
*/

define("BITFLAG_0",0); // 0
define("BITFLAG_1",1); // 2^0
define("BITFLAG_2",2); // 2^1
define("BITFLAG_3",4); // 2^2
define("BITFLAG_4",8); // 2^3
define("BITFLAG_5",16); // 2^4
define("BITFLAG_6",32); // 2^5
define("BITFLAG_7",64); // 2^6
define("BITFLAG_8",128); // 2^7
define("BITFLAG_9",256); // 2^8
define("BITFLAG_10",512); // 2^9
define("BITFLAG_11",1024); // 2^10
define("BITFLAG_12",2048); // 2^11
define("BITFLAG_13",4096); // 2^12
define("BITFLAG_14",8192); // 2^13
define("BITFLAG_15",16384); // 2^14
define("BITFLAG_16",32768); // 2^15
define("BITFLAG_17",65536);
define("BITFLAG_18",131072);
define("BITFLAG_19",262144);
define("BITFLAG_20",524288);
define("BITFLAG_21",1048576);
define("BITFLAG_22",2097152);
define("BITFLAG_23",4194304);
define("BITFLAG_24",8388608);
define("BITFLAG_25",16777216);
define("BITFLAG_26",33554432);
define("BITFLAG_27",67108864);
define("BITFLAG_28",134217728);
define("BITFLAG_29",268435456);
define("BITFLAG_30",536870912);
define("BITFLAG_31",1073741824); // 2^30

define("BITGROUP_1",1);
define("BITGROUP_2",2);
define("BITGROUP_3",3);

function cer_bitflag_is_set($bit_flag,$bit_field)
{
	if($bit_field & $bit_flag) return true;
  else return false;
}

?>