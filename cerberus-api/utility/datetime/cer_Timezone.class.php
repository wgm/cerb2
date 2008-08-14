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

class cer_Timezone {
	var $timezones = array(
		"-12.0" => "(GMT -12:00 hours) IDLW: Eniwetok, Kwajalein",
		"-11.0" => "(GMT -11:00 hours) NT: Midway Island, Samoa",
		"-10.0" => "(GMT -10:00 hours) AHST/CAT/HST: Hawaii",
		"-9.0" => "(GMT -9:00 hours) YST: Alaska",
		"-8.0" => "(GMT -8:00 hours) PST: Los Angeles, San Francisco, Seattle",
		"-7.0" => "(GMT -7:00 hours) MST: Denver",
		"-6.0" => "(GMT -6:00 hours) CST: Mexico City, Saskatchewan",
		"-5.0" => "(GMT -5:00 hours) EST: New York, Bogota, Lima, Quito",
		"-4.0" => "(GMT -4:00 hours) AST: Caracas, La Paz",
		"-3.5" => "(GMT -3:30 hours) Newfoundland",
		"-3.0" => "(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown",
		"-2.0" => "(GMT -2:00 hours) AT: Mid-Atlantic",
		"-1.0" => "(GMT -1:00 hours) WAT: Azores, Cape Verde Islands",
		"0.0"  => "(GMT) WET/UTC: London, Dublin, Lisbon, Casablanca, Edinburgh",
		"+1.0" => "(GMT +1:00 hours) CET: Paris, Berlin, Amsterdam, Oslo, Rome, Madrid",
		"+2.0" => "(GMT +2:00 hours) EET: Athens, Helsinki, Istanbul, Jerusalem, Harare",
		"+3.0" => "(GMT +3:00 hours) BT: Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Nairobiv",
		"+3.5" => "(GMT +3:30 hours) Tehran",
		"+4.0" => "(GMT +4:00 hours) Abu Dhabi, Muscat, Baku, Tbilisi",
		"+4.5" => "(GMT +4:30 hours) Afghanistan",
		"+5.0" => "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent",
		"+5.5" => "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi",
		"+6.0" => "(GMT +6:00 hours) Almaty, Dhaka, Colombo",
		"+7.0" => "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta",
		"+8.0" => "(GMT +8:00 hours) CCT: Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei",
		"+9.0" => "(GMT +9:00 hours) JST: Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
		"+9.5" => "(GMT +9:30 hours) Australia Central Standard: Adelaide, Darwin",
		"+10.0" => "(GMT +10:00 hours) EAST/GST: Guam, Papua New Guinea, Vladivostok",
		"+11.0" => "(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia",
		"+12.0" => "(GMT +12:00 hours) NZST/IDLE: Auckland, Wellington, Fiji, Kamchatka, Marshall Island",
		"+13.0" => "(GMT +13:00 hours) Rawaki Islands: Enderbury, Kiribati",
		"+14.0" => "(GMT +14:00 hours) Line Islands: Kiribati"
	);

	function getServerTimezoneOffset() {
		$offset = date("Z"); // find GMT offset
		if(date("I")) $offset += (($offset < 0) ? -3600 : 3600);
		$offset = $offset / 3600; // convert to hrs
		$offset = sprintf("%c%0.1f",
				(($offset < 0) ? "-" : (($offset > 0) ? "+" : "")), // sign
				$offset
			);
			
		return trim($offset);
	}
		
}

?>