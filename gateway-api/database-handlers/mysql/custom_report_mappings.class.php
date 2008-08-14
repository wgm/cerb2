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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Specifies custom report token to column name mappings.
 * Specifies join conditions between tables
 *
 */
class custom_report_mappings
{
	function get_col_info($token) {
  	$column = array();
  	switch($token) {
    case "OPP_ID":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "opportunity_id";
      $column['type'] = "int";
      $column['friendly_name'] = "Opportunity Id";
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";
    	break;
    case "OPP_NAME":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "opportunity_name";
      $column['type'] = "char";
      $column['friendly_name'] = "Opportunity Name";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";
      break;
    case "OPP_SOURCE":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "source";
      $column['type'] = "enum";
      $column['friendly_name'] = "Source";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "multiple";
		$column['multiple_vals'] = array('Cold Call', 'Existing Customer', 'Self Generated', 'Employee', 'Partner', 'Direct Mail', 'Conference', 'Trade Show', 'Web Site', 'Word of Mouth'); // [JAS]: This should be coming from DB schema      
      break;
    case "OPP_AMOUNT":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "amount";
      $column['type'] = "decimal";
      $column['friendly_name'] = "Amount";      
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";
      break;
    case "OPP_STAGE":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "stage";
      $column['type'] = "enum";
      $column['friendly_name'] = "Stage";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "multiple";
      $column['multiple_vals'] = array('Prospecting', 'Qualifications', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'); // [JAS]: This should be coming from DB schema
      break;
    case "OPP_PROBABILITY":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "probability";
      $column['type'] = "tinyint";
      $column['friendly_name'] = "Probability";      
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";
    	break;
    case "OPP_CLOSE_DATE":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "close_date";
      $column['type'] = "unixtimestamp";
      $column['friendly_name'] = "Close Date";
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";      
    	break;
    case "OPP_CREATED_DATE":
    	$column['table_name'] = "opportunity";
      $column['column_name'] = "created_date";
      $column['type'] = "unixtimestamp";
      $column['friendly_name'] = "Created Date";
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";      
    	break;
    case "COM_ID":
    	$column['table_name'] = "company";
      $column['column_name'] = "id";
      $column['type'] = "bigint";
      $column['friendly_name'] = "Company Id";      
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";      
    	break;
    case "COM_NAME":
    	$column['table_name'] = "company";
      $column['column_name'] = "name";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Company Name";      
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_MAILING_CITY":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_mailing_city";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Mailing City";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_MAILING_STATE":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_mailing_state";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Mailing State";      
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_MAILING_ZIP":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_mailing_zip";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Mailing Zip";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_PHONE":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_phone";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Phone";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_FAX":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_fax";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Fax";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_WEBSITE":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_website";
      $column['type'] = "varchar";
      $column['friendly_name'] = "Website";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_EMAIL":
    	$column['table_name'] = "company";
      $column['column_name'] = "company_email";
      $column['type'] = "varchar";
      $column['friendly_name'] = "E-mail";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "COM_CREATED_DATE":
    	$column['table_name'] = "company";
      $column['column_name'] = "created_date";
      $column['type'] = "unixtimestamp";
      $column['friendly_name'] = "Created Date";
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";      
    	break;
    case "USR_NAME":
    	$column['table_name'] = "user";
      $column['column_name'] = "user_name";
      $column['type'] = "char";
      $column['friendly_name'] = "User Name";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";        
    	break;
 
    case "TIK_ID":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_id";
      $column['type'] = "bigint";
      $column['friendly_name'] = "Ticket Id";      
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";      
    	break;
    case "TIK_MASK":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_mask";
      $column['type'] = "char";
      $column['friendly_name'] = "Ticket Mask";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;
    case "TIK_SUBJECT":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_subject";
      $column['type'] = "char";
      $column['friendly_name'] = "Subject";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";          
    	break;
    case "TIK_STATUS":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_status";
      $column['type'] = "enum";
      $column['friendly_name'] = "Status";
      $column['simple_type'] =  "string";
      $column['display_type'] =  "multiple";
      $column['multiple_vals'] = array('new', 'awaiting-reply', 'customer-reply', 'bounced', 'resolved', 'dead'); // [JAS]: This should be coming from DB schema
    	break;
    case "TIK_CREATED_DATE":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_date";
      $column['type'] = "datetime";
      $column['friendly_name'] = "Create Date";      
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";      
    	break;
    case "TIK_UPDATE_DATE":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "last_update_date";
      $column['type'] = "timestamp";
      $column['friendly_name'] = "Last Updated";      
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";      
    	break;
    case "TIK_DUE_DATE":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_due";
      $column['type'] = "datetime";
      $column['friendly_name'] = "Due Date";  
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";          
    	break;
    case "TIK_PRIORITY":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_priority";
      $column['type'] = "tinyint";
      $column['friendly_name'] = "Priority";     
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";       
    	break;
    case "TIK_SPAM_PROB":
    	$column['table_name'] = "ticket";
      $column['column_name'] = "ticket_spam_probability";
      $column['type'] = "float";
      $column['friendly_name'] = "Spam Score";      
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";      
    	break;
    case "QUE_ID":
    	$column['table_name'] = "queue";
      $column['column_name'] = "queue_id";
      $column['type'] = "bigint";
      $column['friendly_name'] = "Queue Id";      
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";      
    	break;
    case "QUE_NAME":
		$column['table_name'] = "queue";
      $column['column_name'] = "queue_name";
      $column['type'] = "char";
      $column['friendly_name'] = "Queue";      
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";      
    	break;    	
	case "THR_ID":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_id";
      $column['type'] = "int";
      $column['friendly_name'] = "Thread ID";
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";         	
      break;    
	case "THR_THREAD_DATE":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_date";
      $column['type'] = "datetime";
      $column['friendly_name'] = "Thread Date";    	
      $column['simple_type'] =  "date";
      $column['display_type'] =  "string";         
      break;    
	case "THR_TIME_WORKED":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_time_worked";
      $column['type'] = "smallint";
      $column['friendly_name'] = "Time Worked";    	
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string"; 
      break;           
	case "THR_SUBJECT":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_subject";
      $column['type'] = "char";
      $column['friendly_name'] = "Subject";    	
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";         
      break;    
	case "THR_TO":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_to";
      $column['type'] = "char";
      $column['friendly_name'] = "To";    
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";         	
	case "THR_CC":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_cc";
      $column['type'] = "char";
      $column['friendly_name'] = "Cc";    	
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";         
      break;    
	case "THR_REPLYTO":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_replyto";
      $column['type'] = "char";
      $column['friendly_name'] = "Reply To"; 
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";            	
      break;    
	case "THR_IS_AGENT":
    	$column['table_name'] = "thread";
      $column['column_name'] = "is_agent_message";
      $column['type'] = "tinyint";
      $column['friendly_name'] = "Is Agent Message";  
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";           	
      break;    
	case "THR_THREAD_RECEIVED":
    	$column['table_name'] = "thread";
      $column['column_name'] = "thread_received";
      $column['type'] = "datetime";
      $column['friendly_name'] = "Thread Received";   
      $column['simple_type'] =  "string";
      $column['display_type'] =  "string";         
      break;    
	case "PGU_ID":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "public_user_id";
		$column['type'] = "bigint";
		$column['friendly_name'] = "Public User Id";  
      $column['simple_type'] =  "number";
      $column['display_type'] =  "string";   		 
		break;
      case "PGU_MAILING_CITY":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "mailing_city";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Mailing City";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";   		
		break;
	case "PGU_MAILING_STATE":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "mailing_state";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Mailing State";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";
		break;	
	case "PGU_MAILING_ZIP":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "mailing_zip";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Mailing Zip";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";		
		break;
	case "PGU_MAILING_COUNTRY_OLD":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "mailing_country_old";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Old Country";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";		
		break;
	case "PGU_PHONE_HOME":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "phone_home";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Home Phone";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";		
		break;
	case "PGU_PHONE_MOBILE":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "phone_mobile";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Mobile Phone";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";		
		break;
	case "PGU_PHONE_FAX":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "phone_fax";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Fax";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";		
		break;
	case "PGU_PASSWORD":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "password";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Password";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";				
		break;
	case "PGU_PUBLIC_ACCESS_LEVEL":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "public_access_level";
		$column['type'] = "tinyint";
		$column['friendly_name'] = "Access Level";   
		$column['simple_type'] =  "tinyint";
		$column['display_type'] =  "string";				
		break;
	case "PGU_MAILING_ADDRESS":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "mailing_address";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Mailing Address";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";				
		break;
	case "PGU_NAME_FIRST":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "name_first";
		$column['type'] = "varchar";
		$column['friendly_name'] = "First Name";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";						
		break;
	case "PGU_NAME_LAST":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "name_last";
		$column['type'] = "varchar";
		$column['friendly_name'] = "Last Name";  
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";			 
		break;
	case "PGU_NAME_SALUTATION":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "name_salutation";
		$column['type'] = "enum";
		$column['friendly_name'] = "Salutation";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "multiple";
		$column['multiple_vals'] = array('', 'Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.');
		break;
	case "PGU_CREATED_DATE":
		$column['table_name'] = "public_gui_users";
		$column['column_name'] = "created_date";
		$column['type'] = "bigint";
		$column['friendly_name'] = "Created Date";   
		$column['simple_type'] =  "date";
		$column['display_type'] =  "string";				
		break;

	case "ADR_ADDRESS":
		$column['table_name'] = "address";
		$column['column_name'] = "address_address";
		$column['type'] = "char";
		$column['friendly_name'] = "Address";   
		$column['simple_type'] =  "string";
		$column['display_type'] =  "string";						
		break;
	case "ADR_BANNED":
		$column['table_name'] = "address";
		$column['column_name'] = "address_banned";
		$column['type'] = "tinyint";
		$column['friendly_name'] = "Banned";   
		$column['simple_type'] =  "number";
		$column['display_type'] =  "string";				
		break;
    }
    return $column;
  }
  
  function get_link_col_token($token) {
  	$id_token = "";
  	switch($token) {
    case "OPP_NAME":
    	$id_token = "OPP_ID";
    	break;
    case "COM_NAME":
    	$id_token = "COM_ID";
    	break;
    case "PGU_NAME_FIRST":
    case "PGU_NAME_LAST":
    	$id_token = "PGU_ID";
    	break;
//    case "TIK_SUBJECT":
//    	$id_token = "TIK_ID";
//    	break;
//    case "QUEUE_NAME":
//    	$id_token = "QUE_ID";
//    	break;
    }
    return $id_token;
  }
  
  /*
   * returns a mysql expression to be used for ordering the query results for
   * the specified range_type
   */
  function get_range_grouping_expression($range_type, $column_token, $firstDayOfWeek=1) {
    $column_mapping = custom_report_mappings::get_col_info($column_token);
    $column_string = $column_mapping['table_name'] . "." . $column_mapping['column_name'];  

    
    $casted_column_string = "FROM_UNIXTIME(" . $column_string . ")"; 
   	switch($range_type) {
    	case "DATE_DAY":
    		$expression = " DATE_FORMAT(" . $casted_column_string . ", '%%Y-%%m-%%d') ";
			break;
    	case "DATE_WEEK":
      	//TODO:if depending on start of week
			if($firstDayOfWeek == 1) {
    			$expression = " DATE_FORMAT(" . $casted_column_string . ", '%%X-%%V') ";
			}
			else {
        		$expression = " DATE_FORMAT(". $casted_column_string . ", '%%x-%%v') ";
			}
			break;    
    	case "DATE_MONTH":
    		$expression = " DATE_FORMAT(" . $casted_column_string . ", '%%Y%%m') ";
			break;    
    	case "DATE_QUARTER":
    		$expression = " QUARTER(" . $casted_column_string . ") ";
			break;
    	case "DATE_YEAR":
    		$expression = " YEAR(" . $casted_column_string . ") ";
			break;
		case "DATE_MONTH_IN_YEAR":
			$expression = " Month(" . $casted_column_string . ") ";
			break;
		case "DATE_DAY_IN_MONTH":
    		$expression = " DAYOFMONTH(" . $casted_column_string . ") ";
			break;
		default:
			$expression = $casted_column_string;
			break;
	}  	
    
    return $expression;
	}

  function get_join_sql_from_dataset_token($token) {
  	$sql = "";
  	switch($token) {
	  	case "OPP_ALL":
      	$sql=" FROM opportunity " .
        		"LEFT JOIN company ON opportunity.company_id = company.id ".
            "LEFT JOIN user ON opportunity.owner_id = user.user_id " ;
      break;
	  	case "TICKET_QUEUE":
      	$sql=" FROM ticket " .
        		"LEFT JOIN queue ON queue.queue_id = ticket.ticket_queue_id ";
      break;
	  	case "TICKET_THREAD":
      	$sql=" FROM thread " .
        		"LEFT JOIN ticket ON ticket.ticket_id = thread.ticket_id ";
      break;
	  	case "ADDRESS_CONTACT_TICKET":
      	$sql=" FROM address " .
        		"LEFT JOIN public_gui_users ON public_gui_users.public_user_id = address.public_user_id ".
        		"LEFT JOIN company ON company.id = public_gui_users.company_id ".
        		"LEFT JOIN thread ON thread.thread_address_id = address.address_id ".
        		"LEFT JOIN ticket ON ticket.ticket_id = thread.ticket_id ";
     	 break;
      
    }
    return $sql;
  }
  
 	function get_dataset($token) {
 		$dataset=NULL;
	  	switch($token) {
	  		case "OPP_ALL":
				$dataset = new dataset();
				$dataset->add_table("opportunity", NULL, NULL, NULL, NULL);
				$dataset->add_table("company", "opportunity", "company.id", "opportunity.id", "LEFT");
				$dataset->add_table("user", "opportunity", "user.user_id", "opportunity.owner_id", "LEFT");
				//$qTables = $test->get_query_tables(array("chat", "email"));
				break;
	  		case "TICKET_QUEUE":
				$dataset = new dataset();
				$dataset->add_table("ticket", NULL, NULL, NULL, NULL);
				$dataset->add_table("queue", "ticket", "queue.queue_id", "ticket.ticket_queue_id", "INNER");
//				$dataset->add_table("user", "opportunity", "user.user_id", "opportunity.owner_id", "LEFT");
				break;
			case "TICKET_THREAD":
				$dataset = new dataset();
				$dataset->add_table("thread", NULL, NULL, NULL, NULL);
				$dataset->add_table("ticket", "thread", "ticket.ticket_id", "thread.ticket_id", "LEFT");
	    	  break;				
			case "ADDRESS_CONTACT_TICKET":
				$dataset = new dataset();
				$dataset->add_table("address", NULL, NULL, NULL, NULL);
				$dataset->add_table("public_gui_users", "address", "public_gui_users.public_user_id", "address.public_user_id",  "LEFT");
				$dataset->add_table("company", "public_gui_users", "company.id", "public_gui_users.company_id", "LEFT");
				$dataset->add_table("thread", "address", "thread.thread_address_id", "address.address_id", "LEFT");
				$dataset->add_table("ticket", "thread", "ticket.ticket_id", "thread.ticket_id", "LEFT");
	    	  break;
	    }
	    return $dataset;
	}
  
  	function get_operator_friendly_name($operator_token) {
		$str = "";
		switch(strtoupper($operator_token)) {
	  	case "OPER_EQ":
			$str = "Equals";
      		break;
	  	case "OPER_LT":
      		$str = "Less Than";      
      		break;
	  	case "OPER_GT":
			$str = "Greater Than";
      		break;
	  	case "OPER_LTE":
      		$str = "Less Than or Equal";
      		break;
	  	case "OPER_GTE":
      		$str = "Greater Than or Equal";
      		break;
	  	case "OPER_NEQ":
			$str = "Not Equal";
			break;
		case "OPER_CONTAINS":
			$str = "Contains";
			break;
		case "OPER_NCONTAINS":
			$str = "Does Not Contain";
			break;
		case "OPER_STARTS":
			$str .= "Starts With";      
			break;
		case "OPER_INCL":
			$str="Includes";
			break;
		case "OPER_EXCL":
			$str="Does Not Include";				      
			break;
		}
		return $str;
	}
  
	function get_condition_mapping($column_token, $operator_token, $operand) {
		$str="";
		$column_mapping = custom_report_mappings::get_col_info($column_token);
		$column_string = $column_mapping['table_name'] . "." . $column_mapping['column_name'];
		$str .= " AND " . $column_string . " ";
		$db =& database_loader::get_instance();
		//print_r($db->direct);
		
		switch(strtoupper($operator_token)) {
	  	case "OPER_EQ":
			if($column_mapping['type'] == "unixtimestamp") {
				$operandAfter = $db->direct->qstr($operand+86399);
				$operand = $db->direct->qstr($operand);
				$str .= " BETWEEN ".$operand." AND ".$operandAfter." ";
			}
			else {
				$operand = $db->direct->qstr($operand);
				$str .= "= " . $operand . " ";
			}
      		break;
	  	case "OPER_LT":
			$operand = $db->direct->qstr($operand);  	
      		$str .= "< " . "" . $operand . " ";
      		break;
	  	case "OPER_GT":
			if($column_mapping['type'] == "unixtimestamp") {
				$operand = $db->direct->qstr($operand+86399);	
				$str .= " > " . $operand." ";
			}
			else {		
				$operand = $db->direct->qstr($operand);				
				$str .= "> " . $operand . " ";
			}
      		break;
	  	case "OPER_LTE":
	  		$operand = $db->direct->qstr($operand);
      		$str .= "<= " . $operand . " ";
      		break;
	  	case "OPER_GTE":
	  		$operand = $db->direct->qstr($operand);
      		$str .= ">= " . $operand . " ";
      		break;
	  	case "OPER_NEQ":
	  		$operand = $db->direct->qstr($operand);
			$str .= "<> " . $operand . " ";
			break;
		case "OPER_CONTAINS":
			$operand = $db->direct->qstr("%%". $operand . "%%");
			$str .= "LIKE " . $operand . " ";
			break;
		case "OPER_NCONTAINS":
			$operand = $db->direct->qstr("%%" . $operand . "%%" );
			$str .= "NOT LIKE " . $operand . " ";
			break;
		case "OPER_STARTS":
			$operand = $db->direct->qstr($operand . "%%");
			$str .= "LIKE " . $operand . " ";
			break;
		case "OPER_INCL":
			//$operand = $db->direct->qstr($operand);
			//$str .= "IN " . "(".$operand.") ";      
			$str="";
			break;
		case "OPER_EXCL":
			//$operand = $db->direct->qstr($operand);
			//$str .= "NOT IN " . "(".$operand.") ";
			$str="";				      
			break;
		}  
		return $str;
	}
  
}