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
|		Ben Halsted    (ben@webgroupmedia.com)   [BGH]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class preference
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function preference() {
      $this->db =& database_loader::get_instance();
   }

   function set_preference($user_id, $workspace_id, $pref_id, $pref_xml) {
      $retval = $this->db->Save("preference", "set_preference", array("user_id"=>$user_id, "workspace_id"=>$workspace_id, "pref_id"=>$pref_id, "pref_xml"=>$pref_xml));
      if($retval === FALSE) {
         return FALSE;
      }

      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $preference =& $data->add_child("preference", xml_object::create("preference", NULL, array("user_id"=>$user_id, "workspace_id"=>$workspace_id, "pref_id"=>$pref_id)));
      return TRUE;
   }

   function get_preference($user_id, $workspace_id, $pref_id) {
      $preference_data = $this->db->Get("preference", "get_preference", array("user_id"=>$user_id, "workspace_id"=>$workspace_id, "pref_id"=>$pref_id));

      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $preference =& $data->add_child("preference", xml_object::create("preference", $preference_data, array("user_id"=>$user_id, "workspace_id"=>$workspace_id, "pref_id"=>$pref_id)));
      return TRUE;
   }
}
