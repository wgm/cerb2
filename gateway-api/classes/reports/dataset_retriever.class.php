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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

class dataset_retriever
{
	/**
	* DB abstraction layer handle
	*
	* @var object
	*/
	//var $db;
   
	function dataset_retriever() {
		//$this->db =& database_loader::get_instance();
		$this->create_xml();
		
	}
	
	function create_xml() {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$datasets =& $data->add_child("datasets", xml_object::create("datasets"));

		// OPP_ALL
		$dataset =& $datasets->add_child("dataset", xml_object::create("dataset"));
		$dataset->add_child("name", xml_object::create("name", "Opportunity"));
		$dataset->add_child("token", xml_object::create("token", "OPP_ALL"));
		
		$tables =& $dataset->add_child("tables", xml_object::create("tables", NULL));
		
		$this->create_opportunity_fields($tables);
		$this->create_company_fields($tables);
		$this->create_user_fields($tables);

		// TICKET_QUEUE
		$dataset =& $datasets->add_child("dataset", xml_object::create("dataset"));
		$dataset->add_child("name", xml_object::create("name", "Tickets w/ Queues"));
		$dataset->add_child("token", xml_object::create("token", "TICKET_QUEUE"));

		$tables =& $dataset->add_child("tables", xml_object::create("tables", NULL));
		
		$this->create_ticket_fields($tables);
		$this->create_queue_fields($tables);
		
		// TICKET_THREAD
		$dataset =& $datasets->add_child("dataset", xml_object::create("dataset"));
		$dataset->add_child("name", xml_object::create("name", "Threads w/ Tickets"));
		$dataset->add_child("token", xml_object::create("token", "TICKET_THREAD"));
		$tables =& $dataset->add_child("tables", xml_object::create("tables", NULL));
		
		$this->create_thread_fields($tables);
		$this->create_ticket_fields($tables);
		
		// ADDRESS_CONTACT_TICKET
		$dataset =& $datasets->add_child("dataset", xml_object::create("dataset"));
		$dataset->add_child("name", xml_object::create("name", "Address with Contacts, Ticket"));
		$dataset->add_child("token", xml_object::create("token", "ADDRESS_CONTACT_TICKET"));
		$tables =& $dataset->add_child("tables", xml_object::create("tables", NULL));

		$this->create_thread_fields($tables);
		$this->create_ticket_fields($tables);
		$this->create_company_fields($tables);
		$this->create_pgu_fields($tables);
		$this->create_address_fields($tables);

		
		//INPUT_TYPE  (checkbox, string, dropdown, multiple choice)
		//DATA_TYPE (string, date, number)

	}

	
	function create_field_xml(&$fields_tag, $token) {
		$field =& $fields_tag->add_child("field", xml_object::create("field"));
		
		$mappings = custom_report_mappings::get_col_info($token);
		$field->add_child("token", xml_object::create("token", $token));
		$field->add_child("name", xml_object::create("name", $mappings['friendly_name']));
		$field->add_child("data_type", xml_object::create("data_type", $mappings['simple_type']));
		$field->add_child("input_type", xml_object::create("input_type", $mappings['display_type']));
		
		if($mappings['display_type'] == "multiple") {
			$options_tag =& $field->add_child("options", xml_object::create("options"));
			for($i=0; $i < sizeof($mappings['multiple_vals']); $i++) {
				$options_tag->add_child("option", xml_object::create("option", $mappings['multiple_vals'][$i]));  ;
			}
		}
	}
	
	function create_ticket_fields(&$tables) {
		$table_tik =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_tik->add_child("name", xml_object::create("name", "Ticket"));
		$fields_tik =& $table_tik->add_child("fields", xml_object::create("fields"));
		
		$this->create_field_xml($fields_tik, "TIK_ID");
		$this->create_field_xml($fields_tik, "TIK_MASK");
		$this->create_field_xml($fields_tik, "TIK_SUBJECT");
		$this->create_field_xml($fields_tik, "TIK_STATUS");
		$this->create_field_xml($fields_tik, "TIK_PRIORITY");
		$this->create_field_xml($fields_tik, "TIK_CREATED_DATE");
		$this->create_field_xml($fields_tik, "TIK_UPDATE_DATE");
		$this->create_field_xml($fields_tik, "TIK_DUE_DATE");
		$this->create_field_xml($fields_tik, "TIK_SPAM_PROB");
	}
	
	function create_thread_fields(&$tables) {
		$table_thr =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_thr->add_child("name", xml_object::create("name", "Thread"));
		$fields_thr =& $table_thr->add_child("fields", xml_object::create("fields"));
		
		$this->create_field_xml($fields_thr, "THR_ID");
		$this->create_field_xml($fields_thr, "THR_THREAD_DATE");
		$this->create_field_xml($fields_thr, "THR_TIME_WORKED");
		$this->create_field_xml($fields_thr, "THR_SUBJECT");
		$this->create_field_xml($fields_thr, "THR_TO");
		$this->create_field_xml($fields_thr, "THR_CC");
		$this->create_field_xml($fields_thr, "THR_REPLYTO");
		$this->create_field_xml($fields_thr, "THR_IS_AGENT");
		$this->create_field_xml($fields_thr, "THR_THREAD_RECEIVED");		
	}
	
	function create_company_fields(&$tables) {
		$table_com =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_com->add_child("name", xml_object::create("name", "Company"));
		$fields_com =& $table_com->add_child("fields", xml_object::create("fields"));
		
		$this->create_field_xml($fields_com, "COM_NAME");
		$this->create_field_xml($fields_com, "COM_MAILING_CITY");
		$this->create_field_xml($fields_com, "COM_MAILING_STATE");
		$this->create_field_xml($fields_com, "COM_MAILING_ZIP");
		$this->create_field_xml($fields_com, "COM_PHONE");
		$this->create_field_xml($fields_com, "COM_FAX");
		$this->create_field_xml($fields_com, "COM_WEBSITE");
		$this->create_field_xml($fields_com, "COM_EMAIL");
		$this->create_field_xml($fields_com, "COM_CREATED_DATE");
		$this->create_field_xml($fields_com, "COM_ID");
				
	}
	
	function create_pgu_fields(&$tables) {
		$table_pgu =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_pgu->add_child("name", xml_object::create("name", "Public Gui Users"));
		$fields_pgu =& $table_pgu->add_child("fields", xml_object::create("fields"));

		$this->create_field_xml($fields_pgu, "PGU_ID");
		$this->create_field_xml($fields_pgu, "PGU_MAILING_CITY");
		$this->create_field_xml($fields_pgu, "PGU_MAILING_STATE");
		$this->create_field_xml($fields_pgu, "PGU_MAILING_ZIP");
		$this->create_field_xml($fields_pgu, "PGU_MAILING_COUNTRY_OLD");
		$this->create_field_xml($fields_pgu, "PGU_PHONE_HOME");
		$this->create_field_xml($fields_pgu, "PGU_PHONE_MOBILE");
		$this->create_field_xml($fields_pgu, "PGU_PHONE_FAX");
		$this->create_field_xml($fields_pgu, "PGU_PASSWORD");
		$this->create_field_xml($fields_pgu, "PGU_PUBLIC_ACCESS_LEVEL");
		$this->create_field_xml($fields_pgu, "PGU_MAILING_ADDRESS");
		$this->create_field_xml($fields_pgu, "PGU_NAME_FIRST");
		$this->create_field_xml($fields_pgu, "PGU_NAME_LAST");
		$this->create_field_xml($fields_pgu, "PGU_NAME_SALUTATION");
		$this->create_field_xml($fields_pgu, "PGU_CREATED_DATE");		
	}
	
	function create_address_fields(&$tables) {
		$table_adr =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_adr->add_child("name", xml_object::create("name", "Address"));
		$fields_adr =& $table_adr->add_child("fields", xml_object::create("fields"));		

		$this->create_field_xml($fields_adr, "ADR_ADDRESS");
		$this->create_field_xml($fields_adr, "ADR_BANNED");		
	}
	
	function create_queue_fields(&$tables) {
		$table_que =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_que->add_child("name", xml_object::create("name", "Queue"));
		$fields_que =& $table_que->add_child("fields", xml_object::create("fields"));
		
		$this->create_field_xml($fields_que, "QUE_ID");
		$this->create_field_xml($fields_que, "QUE_NAME");		
	}
	
	function create_opportunity_fields(&$tables) {
		$table_opp =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_opp->add_child("name", xml_object::create("name", "Opportunity"));
		$fields_opp =& $table_opp->add_child("fields", xml_object::create("fields"));
		
		$this->create_field_xml($fields_opp, "OPP_ID");
		$this->create_field_xml($fields_opp, "OPP_NAME");
		$this->create_field_xml($fields_opp, "OPP_SOURCE");
		$this->create_field_xml($fields_opp, "OPP_AMOUNT");
		$this->create_field_xml($fields_opp, "OPP_STAGE");
		$this->create_field_xml($fields_opp, "OPP_PROBABILITY");
		$this->create_field_xml($fields_opp, "OPP_CLOSE_DATE");
		$this->create_field_xml($fields_opp, "OPP_CREATED_DATE");		
	}
	
	function create_user_fields(&$tables) {
		$table_usr =& $tables->add_child("table", xml_object::create("table", NULL));
		$table_usr->add_child("name", xml_object::create("name", "User"));
		$fields_usr =& $table_usr->add_child("fields", xml_object::create("fields"));
				
		$this->create_field_xml($fields_usr, "USR_NAME", "User Name", "string", "string");		
	}
		
}
	
	
	
		


