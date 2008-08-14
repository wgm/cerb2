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

require_once(FILESYSTEM_PATH . "includes/functions/languages.php"); // define cerb language classes

class CER_USER_PREFS
{
	var $db = null;											// database pointer
	var $user_id = 0;
	var $user_refresh_rate = 0;
	var $user_ticket_order = 0;
	var $user_language = "en";
	var $user_signature_pos = 0;
	var $user_signature_autoinsert = 1;
	var $user_signature = null;
	var $user_page_layouts = array();
	var $user_keyboard_shortcuts = 0;
	var $user_gmt_offset = 0;
	
	var $gui_languages = null;
	var $options_refresh = array();							// options for the auto refresh box
	var $options_msg_order = array();
	var $options_language = array();
	var $options_sig_pos = array();
	
	function CER_USER_PREFS($uid="")
	{
		$this->db = cer_Database::getInstance();
		$this->user_id = $uid;
		
		$this->_load_user_prefs($uid);
		$this->_populate_options();
	}
	
	function _load_user_prefs($uid="")
	{
		if(empty($uid)) return false;
		
		// Load up current user preferences
		$sql = "SELECT prefs.refresh_rate, prefs.ticket_order, prefs.user_language, prefs.signature_pos, prefs.signature_autoinsert, sig.sig_content, prefs.page_layouts, prefs.keyboard_shortcuts, prefs.gmt_offset ".
			"FROM user_prefs prefs ".
			"LEFT JOIN user_sig sig USING (user_id) ".
			"WHERE prefs.user_id = " . $uid;
		$result = $this->db->query($sql);
		
		if($this->db->num_rows($result))
		 {
			$prefsrow = $this->db->fetch_row($result);
			$this->user_refresh_rate  = $prefsrow["refresh_rate"];
			$this->user_ticket_order  = $prefsrow["ticket_order"];
			$this->user_language = $prefsrow["user_language"];
			$this->user_signature_pos = $prefsrow["signature_pos"];
			$this->user_signature_autoinsert = $prefsrow["signature_autoinsert"];
			$this->user_signature = stripslashes($prefsrow["sig_content"]);
			$this->user_page_layouts = unserialize(stripslashes($prefsrow["sig_content"]));
			$this->user_keyboard_shortcuts = $prefsrow["keyboard_shortcuts"];
			$this->user_gmt_offset = $prefsrow["gmt_offset"];
		 }
			
		$this->gui_languages = new cer_languages_obj($this->user_language);
		//$gui_languages->get_default_language();
	}
	
	function _populate_options()
	{
		// $user_refresh_rate
		$mins = strtolower(LANG_WORD_MINUTES);
		$this->options_refresh = array(0 => LANG_REFRESH_DONT,
									   1 => "1 $mins",
									   2 => "2 $mins",
									   3 => "3 $mins",
									   4 => "4 $mins",
									   5 => "5 $mins",
									   10 => "10 $mins",
									   15 => "15 $mins",
									   20 => "20 $mins"
									   );
									   
		$this->options_msg_order = array(0 => LANG_PREF_MSG_OLDEST,
										 1 => LANG_PREF_MSG_NEWEST
										 );

		foreach($this->gui_languages->languages as $l)
			{ $this->options_language[$l->lang_code] = $l->lang_name; }
		

		$this->options_sig_pos = array(0 => LANG_PREF_AUTO_SIG_AFTER_QUOTE,
																	 1 => LANG_PREF_AUTO_SIG_BEFORE_QUOTE
																	);
	}
	
};


?>