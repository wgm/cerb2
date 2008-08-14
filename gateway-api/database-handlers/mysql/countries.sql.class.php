<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|
| Developers involved with this file:
|		Jeff Standen    (jeff@webgroupmedia.com)   [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer example
 *
 */
class countries_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var db
    */
   var $db;
   
   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return countries_sql
    */
   function countries_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Gets the countries list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_countries_list($params) {
   	extract($params);
   	
		$sql = "SELECT c.country_id, c.country_name ".
      	"FROM `country` c ";
      return $this->db->GetAll(sprintf($sql));
   }

}