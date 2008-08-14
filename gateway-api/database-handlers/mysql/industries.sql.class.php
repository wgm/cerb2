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
class industries_sql
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
    * @return industries_sql
    */
   function industries_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Gets the industries list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_industry_list($params) {
   	extract($params);
   	
		$sql = "SELECT i.industry_id, i.industry_name, i.industry_sector ".
      	"FROM `industry` i ".
      	"ORDER BY i.industry_sector, i.industry_name ";
      return $this->db->GetAll(sprintf($sql));
   }
   
   function get_sector_enum($params) {
   	extract($params);
   	$sql = "DESCRIBE `industry` `industry_sector`";
   	return $this->db->GetRow(sprintf($sql));
   }

}