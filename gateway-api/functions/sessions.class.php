<?php

require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

if(!defined('VALID_INCLUDE')) exit();

class gateway_session
{
   function gateway_session() {
      $db =& database_loader::get_instance();
      $this->db =& $db->direct;
      $this->lifetime = AGENT_SESSION_EXPIRATION_SECS;
   }
   
   /**
    * Singleton Function
    *
    * @return database_loader
    */
   function &get_instance() {
		static $instance = NULL;
		
		if($instance == NULL) {
			$instance =& new gateway_session();
		}
		
		return $instance;
	}
      
   function update_login($user_id) {
      $sql = "UPDATE gateway_session SET user_id = '%d', login_timestamp = UNIX_TIMESTAMP() WHERE php_sid_cookie = %s";
      return $this->db->Execute(sprintf($sql, $user_id, $this->db->qstr(session_id())));
   }
   
   function _open($path, $name) {
      return TRUE;
   }

   function _close() {
      $this->_gc(0);
      return TRUE;
   }
   
   function _read($session_cookie_name) {
      $debug = $this->db->debug;
      $this->db->debug = "";
      $sql = "SELECT session_id, session_data FROM gateway_session WHERE php_sid_cookie = %s AND ip_address = '%d'";
      $recordSet = $this->db->Execute(sprintf($sql, $this->db->qstr($session_cookie_name), ip2long($_SERVER['REMOTE_ADDR'])));
      $this->db->debug = $debug;
      if(FALSE === $recordSet) {
         return '';
      }
      elseif($recordSet->RecordCount() > 0) {
         $this->session_id = $recordSet->fields['session_id'];
         return $recordSet->fields['session_data'];
      }
      else {
         return '';
      }
   }

   function _write($session_cookie_name, $session_data) {
      $sql = "UPDATE gateway_session SET last_timestamp = UNIX_TIMESTAMP(), session_data = %s, requests = requests + 1                        
                        WHERE php_sid_cookie = %s AND ip_address = '%d'";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($session_data),
         $this->db->qstr($session_cookie_name), ip2long($_SERVER['REMOTE_ADDR'])))) {
         return FALSE;
      }
      if($this->db->Affected_Rows() > 0) {
         return TRUE;
      }

      $sql = "INSERT INTO gateway_session (php_sid_cookie, ip_address, creation_timestamp,
                   last_timestamp, requests, session_data) VALUES (%s, '%d', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 1, %s)";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($session_cookie_name), ip2long($_SERVER['REMOTE_ADDR']), $this->db->qstr($session_data)))) {
         return FALSE;
      }
      if($this->db->Insert_ID()) {
         return TRUE;
      }
      else {
         return FALSE;
      }
   }
   
   function _destroy($session_cookie_name) {
      $sql = "DELETE FROM gateway_session WHERE php_sid_cookie = %s AND ip_address = '%d'";
      if(!$this->db->Execute(sprintf($sql, $this->db->qstr($session_cookie_name), ip2long($_SERVER['REMOTE_ADDR'])))) {
         return FALSE;
      }

      if($this->db->Affected_Rows() != 0) {
         return TRUE;
      }
      else {
         return FALSE;
      }
   }

   function _gc($maxlifetime) {
      $sql = "DELETE FROM gateway_session WHERE last_timestamp < (UNIX_TIMESTAMP() - '%d')";
      if(!$this->db->Execute(sprintf($sql, $this->lifetime))) {
         return FALSE;
      }
      return TRUE;
   }
}

/* Create new object of class */
$session_obj =& gateway_session::get_instance();

/* Change the save_handler to use the class functions */
session_set_save_handler(array(&$session_obj, '_open'),
                         array(&$session_obj, '_close'),
                         array(&$session_obj, '_read'),
                         array(&$session_obj, '_write'),
                         array(&$session_obj, '_destroy'),
                         array(&$session_obj, '_gc'));

/* Start the session */
session_name("cerberus_xml_gateway");
session_cache_limiter('private');
session_start();

$_SESSION['session_id'] = $session_obj->session_id;
