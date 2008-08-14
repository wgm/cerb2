<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: core.hash.php
|
| Purpose: Cache queries to reduce redundant database queries
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/queue_access/cer_queue_access.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/bitflags.php");

define("HASH_Q_NONE",BITFLAG_0); // no access
define("HASH_Q_READ",BITFLAG_1); // only read access
define("HASH_Q_WRITE",BITFLAG_2); // only write access
define("HASH_Q_READWRITE",HASH_Q_READ + HASH_Q_WRITE); // write + read access
define("HASH_Q_ALL",BITFLAG_3); // any access (none/read/write)

define("HASH_USER_ANY_Q",""); // return all users

define("HASH_COMPANY_ALL",""); // return all companies

define("HASH_GROUP_ALL",""); // return all groups


class CER_HASH_CONTAINER
{
	var $_db;
	var $_objects = array('queues' => null,
						 'companies' => null,
						 'groups' => null,
						 'statuses' => null,
						 'users' => null,
						 'views' => null
						 );
						 
	function CER_HASH_CONTAINER()
	{
		$this->_db = cer_Database::getInstance();
	}
	
	function get_group_hash($filter=HASH_GROUP_HASH) {

		if($this->_objects['groups'] == null)
			$this->_objects['groups'] = new CER_HASH_GROUPS($this);
			
		$groups = $this->_objects['groups']->groups;	
		return($groups);
	}
	
	function get_queue_hash($filter=HASH_Q_ALL)
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['queues'] == null)
			$this->_objects['queues'] = new CER_HASH_QUEUES($this);
			
		$queues = $this->_objects['queues']->queues;
			
		if($filter != HASH_Q_ALL)
		{
			foreach($queues as $idx => $queue)
			{
				switch($filter)
				{
					case HASH_Q_NONE:
						if($queue->queue_access != 'none') unset($queues[$idx]);
					break;

					case HASH_Q_READ:
						if($queue->queue_access != 'read') unset($queues[$idx]);
					break;

					case HASH_Q_READWRITE:
						if($queue->queue_access != 'read' && $queue->queue_access != 'write') unset($queues[$idx]);
					break;

					case HASH_Q_WRITE:
						if($queue->queue_access != 'write') unset($queues[$idx]);
					break;
				}
			}
		}
		
		return($queues); // [JAS]: return the post-filter queue list from hash
	}
	
	function get_user_hash($q_filter=HASH_USER_ANY_Q,$sorting="user_login")
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['users'] == null)
			$this->_objects['users'] = new CER_HASH_USERS($this,$sorting);
			
		$users = $this->_objects['users']->users;
			
		return($users);
	}

	function get_company_hash($q_filter=HASH_COMPANY_ALL)
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['companies'] == null)
			$this->_objects['companies'] = new CER_HASH_COMPANIES($this);
			
		$users = $this->_objects['companies']->companies;
			
		return($users);
	}

	function get_view_hash()
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['views'] == null)
			$this->_objects['views'] = new CER_HASH_VIEWS($this);
			
		$views = $this->_objects['views']->views;
			
		return($views);
	}

	function get_status_hash()
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['statuses'] == null)
			$this->_objects['statuses'] = new CER_HASH_STATUSES($this);
			
		$statuses = $this->_objects['statuses']->statuses;
			
		return($statuses);
	}

    // [JSJ]: Added function to get the string priority hash
    function get_priority_hash()
    {
          // [JSJ]: If we don't have a hash for this object yet, get one.
          if($this->_objects['priorities'] == null)
                  $this->_objects['priorities'] = new CER_HASH_PRIORITIES($this);

          $priorities = $this->_objects['priorities']->priorities;

          return($priorities);
    }

};


class CER_HASH_GROUPS
{
	var $_db = null;
	var $_parent = null;
	var $groups = array();
	
	function CER_HASH_GROUPS(&$parent)
	{
		global $session; // [JAS]: Clean up
		global $queue_access; // [JAS]: Hijack it if we have it built
		
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
		
		// [JAS]: First populate the hash with ALL queues.	
		$sql = "SELECT `group_id` , `group_name` , `is_core_default` , `group_acl` , `group_acl2` , `group_acl3`". 
			   "FROM `user_access_levels` ORDER BY `group_name`";
		$res = $this->_db->query($sql);
		
		if($this->_db->num_rows($res))
		{
			while($qr = $this->_db->fetch_row($res))
			{
				$group = new user_groups_obj("");
				$group->group_id = $qr["group_id"];
				$group->group_name = stripslashes($qr["group_name"]);
				$group->is_core_default = null;
				$group->group_acl = null;
				$group->group_acl2 = null;
				$group->group_acl3 = null;
				$this->groups[$group->group_id] = $group;
			}
		}
	}
	
};


class CER_HASH_QUEUES
{
	var $_db = null;
	var $_parent = null;
	var $queues = array();
	var $queue_access = null;
	
	function CER_HASH_QUEUES(&$parent)
	{
		global $session; // [JAS]: Clean up
		global $queue_access; // [JAS]: Hijack it if we have it built
		
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
		
		// [JAS]: Set up queue access object.
		if(empty($queue_access))
			$this->queue_access = new CER_QUEUE_ACCESS();
		else
			$this->queue_access = $queue_access;

		// [JAS]: First populate the hash with ALL queues.	
		$sql = "SELECT q.queue_id, q.queue_name FROM queue q ORDER BY q.queue_name";
		$res = $this->_db->query($sql);
		
		if($this->_db->num_rows($res))
		{
			while($qr = $this->_db->fetch_row($res))
			{
				$queue_item = new CER_QUEUE_ACCESS_OBJECT();
				$queue_item->queue_id = $qr["queue_id"];
				$queue_item->queue_name = stripslashes($qr["queue_name"]);
				$queue_item->queue_access = "none";
				$queue_item->queue_watcher = 0;
				$this->queues[$queue_item->queue_id] = $queue_item;
			}
		}

		// [JAS]: Then apply our permissions for the current user.		
		foreach($this->queue_access->queue_access_list as $idx => $q)
		{
			$this->queues[$idx] = &$this->queue_access->queues[$idx];
		}
			
//				$queue_item->queue_watcher = $qr["queue_watch"];

	}
};


class CER_HASH_VIEWS
{
	var $_db;
	var $_parent;
	var $views = array();

	function CER_HASH_VIEWS(&$parent)
	{
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
     	
		$sql = "SELECT v.view_id,v.view_name FROM ticket_views v ORDER BY v.view_name";
      	$result = $this->_db->query($sql,false);

      	if($this->_db->num_rows($result))
    	{
    		while($vr = $this->_db->fetch_row($result))
    		{
    			$view_item = new CER_HASH_VIEWS_ITEM;
    			$view_item->view_id = $vr[0];
    			$view_item->view_name = stripslashes($vr[1]);
    			$this->views[$view_item->view_id] = $view_item;
    		}
    	}		
	}
	
};


class CER_HASH_VIEWS_ITEM
{
	var $view_id;
	var $view_name;
};


class CER_HASH_COMPANIES
{
	var $_db;
	var $_parent;
	var $companies = array();
	
	function CER_HASH_COMPANIES(&$parent)
	{
    	$this->_db = cer_Database::getInstance();
    	$this->_parent = &$parent;
		
		$sql = "SELECT c.id As company_id, c.name As company_name ".
    		"FROM company c ORDER BY c.company_name";
    	$result = $this->_db->query($sql);
    	if($this->_db->num_rows($result))
    	{
    		while($cr = $this->_db->fetch_row($result))
    		{
    			$company_item = new CER_HASH_COMPANIES_ITEM;
    			$company_item->company_id = $cr["company_id"];
    			$company_item->company_name = stripslashes($cr["company_name"]);
    			$this->companies[$company_item->company_id] = $company_item;
    		}
    	}		
	}
	
};


class CER_HASH_COMPANIES_ITEM
{
	var $company_id;
	var $company_name;
};


class CER_HASH_STATUSES
{
	var $_db;
	var $_parent;
	var $statuses = array();
	
	function CER_HASH_STATUSES(&$parent)
	{
		global $session; // [JAS]: Clean up
		global $status_options; // clean
		global $cerberus_translate;
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
		
		foreach($status_options as $status)
		{
			$this->statuses[$status] = $cerberus_translate->translate_status($status);
		}
	}
	
};


// [JSJ]: Added hash for priority strings
class CER_HASH_PRIORITIES
{
      var $_db;
      var $_parent;
      var $priorities = array();

      function CER_HASH_PRIORITIES(&$parent)
      {
          global $session; // [JSJ]: Clean up
          global $priority_options; // clean
          global $cerberus_translate;
          $this->_db = cer_Database::getInstance();
          $this->_parent = &$parent;

          foreach($priority_options as $priority_id => $priority_name)
          {
                  $this->priorities[$priority_id] = $cerberus_translate->translate_priority($priority_name);
          }
      }
};


class CER_HASH_USERS
{
	var $_db;
	var $_parent;
	var $users = array();
	
	function CER_HASH_USERS(&$parent,$sorting="user_login")
	{
		global $session;
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
		$user_cache = array();
		
		$sql = "SELECT u.user_id, u.user_login, u.user_name FROM  `user` u ORDER BY u.$sorting";
		$u_res = $this->_db->query($sql);
		
		if($this->_db->num_rows($u_res))
		{
			while($u_row = $this->_db->fetch_row($u_res))
			{
				$user_item = new CER_HASH_USERS_ITEM;
				$user_item->user_id = $u_row["user_id"];
				$user_item->user_name = stripslashes($u_row["user_name"]);
				$user_item->user_login = $u_row["user_login"];
				$user_cache[$user_item->user_id] = $user_item;
			}
		}
		
//		$sql = "SELECT qa.user_id, qa.queue_id, qa.queue_access FROM queue_access qa";
//		$u_res = $this->_db->query($sql);
//		
//		if($this->_db->num_rows($u_res))
//		{
//			while($u_row = $this->_db->fetch_row($u_res))
//			{
//				$uid = $u_row["user_id"];
//				$qid = $u_row["queue_id"];
//				$qacc = $u_row["queue_access"];
//				
//				if(isset($user_cache[$uid])) 
//					$user_cache[$uid]->queue_access[$qid] = $qacc;
//			}
//		}
		
		foreach($user_cache as $idx => $u)
		{
			array_push($this->users,$u);
			unset($user_cache[$idx]);
		}
	}
};


class CER_HASH_USERS_ITEM
{
	var $user_id;
	var $user_name;
	var $user_login;
	var $queue_access; // this user's access rights		
};


?>