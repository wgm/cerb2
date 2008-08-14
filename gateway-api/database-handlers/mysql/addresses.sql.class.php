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
class addresses_sql
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
    * @return addresses_sql
    */
   function addresses_sql(&$db) {
      $this->db =& $db;
   }
   
   function get_address_id($params) {
   	extract($params);
      $sql = "SELECT a.address_id FROM address a WHERE a.address_address = %s";
      $address_id = $this->db->GetOne(sprintf($sql, $this->db->qstr($address)));
      
      if(empty($address_id)) {
      	$sql = "INSERT INTO address(address_address) VALUES(%s)";
      	$this->db->Execute(sprintf($sql, $this->db->qstr($address)));
      	$address_id = $this->db->Insert_ID();
      }
      
      return $address_id;
   }
   
}