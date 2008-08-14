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

/**
 * Database abstraction layer example
 *
 */
class example_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;
   
   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return example_sql
    */
   function example_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Example function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function example($params) {
   	extract($params);
      $sql = "SELECT * FROM table WHERE var1 = '%d' AND var2 = %s";
      return $this->db->GetAll(sprintf($sql, $var1, $this->db->qstr($var2)));
   }

}