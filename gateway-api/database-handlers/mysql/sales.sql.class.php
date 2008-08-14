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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer example
 *
 */
class sales_sql
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
    * @return accounts_sql
    */
   function sales_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Gets the weekly totals of revenue from new sales based on the opportunity close date
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_weekly_revenue($params) {
   	extract($params);
    
    if($params['first_day_of_week'] == 1) {
    	$last_day_of_week = 7;
    }
    else {
    	$last_day_of_week = $params['first_day_of_week'] - 1;
    }
    
		$sql = "SELECT ". 
					"UNIX_TIMESTAMP(DATE_SUB(FROM_UNIXTIME(o.close_date),INTERVAL dayofweek(FROM_UNIXTIME(o.close_date))-%1\$d DAY)) start_date, ".
					"UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(o.close_date),INTERVAL %2\$d-dayofweek(FROM_UNIXTIME(o.close_date)) DAY)) end_date, ".
					"sum(o.amount) total_revenue,  ".
					"count(*) total_closed ".
					"FROM opportunity o ".
					"WHERE o.stage = 'Closed Won' ";
          
    if($date_start != "") {      
          $sql .= "AND o.close_date > '%3\$d' ";
    }
    if($date_end != "") {
          $sql .= "AND o.close_date < '%4\$d' ";
    }
    $sql .= "GROUP BY start_date ".
						"ORDER BY start_date ";

      return $this->db->GetAll(sprintf($sql, $first_day_of_week, $last_day_of_week, $date_start, $date_end));
   }

   function get_week_sales($params) {
   	extract($params);
    
		$sql = "SELECT ".
					"DATE_FORMAT(FROM_UNIXTIME(o.close_date),'%%m/%%d/%%y') as close_date, ".
					"u.user_name, ".
					"o.opportunity_name, ".
					"o.source, ".
					"o.amount, ".
					"o.stage, ".
					"c.name AS account_name, ".
          		"c.id AS account_id ".
					"FROM opportunity o ".
					"LEFT JOIN user u ON o.owner_id = u.user_id ".
					"LEFT JOIN company c ON o.company_id = c.id ".
					"WHERE o.stage = 'Closed Won' ";
					
				    if($date_start != "") {      
				          $sql .= "AND o.close_date >= $date_start ";
				    }
				    if($date_end != "") {
				          $sql .= "AND o.close_date <= $date_end ";
				    }
//					"AND FROM_UNIXTIME(close_date) >= '2004-01-19' ".
//					"AND FROM_UNIXTIME(close_date) <= '2005-07-25' ".

		$sql .=
					"ORDER BY o.close_date ";
    
     return $this->db->GetAll(sprintf($sql));
   }

   
   
}