<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");

class CerConfiguration
{
var $settings;

	function CerConfiguration()
	{
		$this->settings = array();
	}

	function reload() {
		$this->settings = array();
		$this->set_config_defaults();
		$this->import_config_db();
	}
	
	function getInstance() {
		static $instance = NULL;
		
		if($instance == NULL) {
			$instance = new CerConfiguration();
			$instance->reload();
		}
		
		return $instance;
	}
	
	/**
	 * @return void
	 * @desc Sets the default configuration options
	 */
	function set_config_defaults()
	{
		global $_SERVER;
		$zones = new cer_Timezone();
		
		// [JAS]: Allow host name autodetection override from site.config.php
		if(defined("HOST_NAME") && HOST_NAME) {
			$this->settings["http_server"] = HOST_NAME;
		}
		else {		
			$scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
			$this->settings["http_server"] = "$scheme://" . $_SERVER["HTTP_HOST"]; // auto detect host
		}
		
		$path = split("/",$_SERVER["PHP_SELF"]);
		if(count($path)>1) { unset($path[count($path)-1]); $path_url = implode("/",$path); } else { $path_url = ""; }
		$this->settings["cerberus_gui_path"] = $path_url;
		
		$this->settings["helpdesk_title"] = "";
		$this->settings["session_lifespan"] = 1440;
		$this->settings["session_ip_security"] = 0;
		$this->settings["warcheck_secs"] = 10;
		$this->settings["mail_delivery"] = "smtp";
		$this->settings["smtp_server"] = "localhost";
		$this->settings["mail_delay"] = 2;
		$this->settings["who_max_idle_mins"] = 10;
		$this->settings["auto_add_cc_reqs"] = false;
		$this->settings["enable_panel_stats"] = true;
		$this->settings["enable_customer_history"] = true;
		$this->settings["enable_audit_log"] = true;
		$this->settings["track_sid_url"] = true;
		$this->settings["satellite_enabled"] = false;
		$this->settings["xsp_url"] = "";
		$this->settings["xsp_login"] = "";
		$this->settings["xsp_password"] = "";
		$this->settings["overdue_hours"] = 12;
		$this->settings["customer_ticket_history_max"] = 10;
		$this->settings["sendmail"] = true;
		$this->settings["time_adjust"] = 0;
		$this->settings["enable_id_masking"] = 1;
		$this->settings["show_kb"] = true;
		$this->settings["show_kb_topic_totals"] = 1;
		$this->settings["default_language"] = "en";
		$this->settings["gui_version"] = GUI_VERSION;
		$this->settings["kb_editors_enabled"] = 0;
		$this->settings["ob_callback"] = "";
 		$this->settings["bcc_watchers"] = 0;
 		$this->settings["watcher_assigned_tech"] = 0;
 		$this->settings["not_to_self"] = 0;
  		$this->settings["watcher_no_system_attach"] = 0;
 		$this->settings["watcher_from_user"] = 0;
  		$this->settings["send_precedence_bulk"] = 0;	// [jxdemel] new feature
  		$this->settings["user_only_assign_own_queues"] = 0;
  		$this->settings["auto_delete_spam"] = 0;
  		$this->settings["purge_wait_hrs"] = 24;
 		$this->settings["parser_secure_enabled"] = 0;
 		$this->settings["parser_secure_user"] = "";
 		$this->settings["parser_secure_password"] = "";
 		$this->settings["search_index_numbers"] = 0;
 		$this->settings["parser_version"] = "";
 		$this->settings["save_message_xml"] = 1;
 		$this->settings["server_gmt_offset_hrs"] = $zones->getServerTimezoneOffset();
 		
	}

	/**
	 * @return void
	 * @desc Reads in the configuration values from the database
	 */
	function import_config_db()
	{
		$config_db = cer_Database::getInstance();
		
		$sql = "SELECT * from configuration LIMIT 0,1";
		$conf_res = $config_db->query($sql);
		
		if($config_db->num_rows($conf_res))
		{
			$cfg = $config_db->fetch_row($conf_res);
			foreach($cfg as $idx=>$val)
			{
				$cfg_fld = $idx;
				$cfg_val = $val;
				if($cfg_val != "") {
					$this->settings[$cfg_fld]=$cfg_val; // [JAS]: Replaced: eval("\$this->settings[\"" . $cfg_fld . "\"]=\"" . $cfg_val . "\";");
				}
			}
		}
	}
	
};

?>