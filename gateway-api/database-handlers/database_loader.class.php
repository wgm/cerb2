<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/third_party/adodb/adodb.inc.php");

$ADODB_FETCH_MODE = ADODB_FETCH_BOTH;

/**
 * Database abstraction loader which handles loading the proper DB layer
 *
 */
class database_loader
{
   /**
    * Actual ADOdb connection to DB.
    *
    * @var object
    */
   var $direct;
   /**
    * Array of DB objects to avoid multiple instances of same class.
    *
    * @var array
    */
   var $section_objs = array();
   
   /**
    * Singleton Function
    *
    * @return database_loader
    */
   function &get_instance() {
		static $instance = NULL;
		
		if($instance == NULL) {
			$instance =& new database_loader();
		}
		
		return $instance;
	}
   
   /**
    * Class constructor
    *
    * @return database_loader
    */
   function database_loader() {
      $this->direct =& ADONewConnection(DB_PLATFORM);
      $this->direct->PConnect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
      if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 1) {
         $this->direct->debug = 'true';
      }
   }
   
   /**
    * Get's data from the DB.
    *
    * @param string $class Which class layer to load
    * @param string $method Which method of that class to call
    * @param array $params Array of parameters to the method
    * @return mixed DB data
    */
   function get($class, $method, $params = array()) {
      if(!array_key_exists($class, $this->section_objs) || !is_object($this->section_objs[$class])) {
         if(!file_exists(FILESYSTEM_PATH . "gateway-api/database-handlers/" . DB_PLATFORM . "/" . $class . ".sql.class.php")) {
            return FALSE;
         }
         require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/" . DB_PLATFORM . "/" . $class . ".sql.class.php");
         $class_name = $class . "_sql";
         $this->section_objs[$class] =& new $class_name($this->direct);
      }
      if(!method_exists($this->section_objs[$class], $method)) {
         return FALSE;
      }
      return $this->section_objs[$class]->$method($params);
   }
   
   /**
    * Alias of get() method. Semantically used to not confuse save's with get's from DB
    *
    * @param string $class Which class layer to load
    * @param string $method Which method of that class to call
    * @param array $params Array of parameters to the method
    * @return mixed DB data
    */
   function save($class, $method, $params = array()) {
      return $this->get($class, $method, $params);
   }
}       
      
      
