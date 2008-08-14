<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
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
/*!
	\file privileges.php
	\brief Privilege & Rights System; functions for checking user privs

	\author Jeff Standen, jeff@webgroupmedia.com
	\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/acl_groups.php");

//! Cerberus Privileges Handler
/*
	Container for functions that check a user's rights and access levels.  These rights
	are generally set in User Groups in GUI:Configuration->Users->Groups.
*/
class privileges_obj { 
  var $acl_list;
  var $acl2_list;
  var $acl3_list;
  var $is_superuser;
  
  function privileges_obj()
  {
  	global $session; // clean up
  	$this->acl_list = @$session->vars["login_handler"]->user_access->group_acl;
  	$this->acl2_list = @$session->vars["login_handler"]->user_access->group_acl2;
  	$this->acl3_list = @$session->vars["login_handler"]->user_access->group_acl3;
    $this->is_superuser = @$session->vars["login_handler"]->user_superuser;
  }
  
  function has_restriction($bitfield,$bitgroup=BITGROUP_1)
  {
  	if($this->is_superuser==1) return false;
  	return $this->check_bit($bitfield, $bitgroup);
  }
  
  function has_priv($bitfield,$bitgroup=BITGROUP_1)
  {
  	if($this->is_superuser==1) return true;
  	return $this->check_bit($bitfield, $bitgroup);
  }
  
  function check_bit($bitfield,$bitgroup=BITGROUP_1) {
    switch($bitgroup)
    {
    	case BITGROUP_1:
      		return cer_bitflag_is_set($bitfield,$this->acl_list);
        break;
    	case BITGROUP_2:
      		return cer_bitflag_is_set($bitfield,$this->acl2_list);
        break;
    	case BITGROUP_3:
      		return cer_bitflag_is_set($bitfield,$this->acl3_list);
        break;
    }
  }

};

$priv = new privileges_obj(); //!< A Cerberus privileges object instance
?>