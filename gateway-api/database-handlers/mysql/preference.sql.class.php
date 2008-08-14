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
|		Ben Halsted    (ben@webgroupmedia.com)   [BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer for visitor data
 *
 */
class preference_sql
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
    * @return visitor_sql
    */
   function preference_sql(&$db) {
      $this->db =& $db;
   }
   
   function set_preference($params) {
      extract($params);

      $sql = "DELETE FROM `user_prefs_xml` WHERE `user_id`=%s AND `workspace_id`=%s AND `pref_id`=%s";
      $this->db->Execute(sprintf($sql, $this->db->qstr($user_id), $this->db->qstr($workspace_id), $this->db->qstr($pref_id)));

      if(0<strlen($pref_xml)) {
         $sql = "INSERT INTO `user_prefs_xml` (`user_id`,`workspace_id`,`pref_id`,`pref_xml`) VALUES (%s,%s,%s,%s)";
         $this->db->Execute(sprintf($sql, $this->db->qstr($user_id), $this->db->qstr($workspace_id), $this->db->qstr($pref_id), $this->db->qstr($pref_xml)));
         return $this->db->Insert_ID();
      }

      return 0;
   }


   function get_preference($params) {
      extract($params);

      $sql = "SELECT `pref_xml` FROM `user_prefs_xml` WHERE `user_id`=%s AND `workspace_id`=%s AND `pref_id`=%s";
      return $this->db->GetOne(sprintf($sql, $this->db->qstr($user_id), $this->db->qstr($workspace_id), $this->db->qstr($pref_id)));
   }
   
}
