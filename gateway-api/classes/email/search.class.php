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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");

class email_search
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function email_search() {
      $this->db =& database_loader::get_instance();
   }

   function do_search($search_type, $fields, $filters) {
      $method = "do_" . $search_type . "_search";
      if(!method_exists($this, $method)) {
         return FALSE;
      }
      else {
         return $this->$method($fields, $filters);
      }
   }

   function do_keyword_search($fields, $filters) {
      $keywords = explode(" ", cer_Whitespace::mergeWhitespace($fields["keywords"]));
      $searchwords = array();
      foreach($keywords as $word) {
         $word = preg_replace("/[^\w\*\%\.@]/", "", $word);
         if(strstr($word, "*") !== FALSE) {
            $word = str_replace("*", "%", $word);
            $searchwords = array_merge($searchwords, $this->db->Get("search", "get_words_from_partial", array("word"=>$word)));
         }
         else {
            $searchwords[] = $word;
         }
      }
      $keywords = "'" . implode("','", $searchwords) . "'";
      $dbSet = $this->db->Get("search", "keyword_search", array("keywords"=>$keywords, "filters"=>$filters));
      $ticket_data = $dbSet['results'];
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $search_info =& $data->add_child("search_info", xml_object::create("search_info"));
      $search_info->add_child("page", xml_object::create("page", $dbSet['page']));
      $search_info->add_child("total_pages", xml_object::create("total_pages", $dbSet['total_pages']));
      $search_info->add_child("prefilter_count", xml_object::create("prefilter_count", $dbSet['prefilter_count']));
      $search_info->add_child("postfilter_count", xml_object::create("postfilter_count", $dbSet['postfilter_count']));
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      if(is_array($ticket_data)) {
         foreach($ticket_data as $ticket) {
            $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'])));
         }
      }
      return TRUE;
   }

   function do_requester_search($fields, $filters) {
      $requester = cer_Whitespace::mergeWhitespace($fields["requester"]);
      $dbSet = $this->db->Get("search", "requester_search", array("requester"=>$requester, "filters"=>$filters));
      $ticket_data = $dbSet['results'];
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $search_info =& $data->add_child("search_info", xml_object::create("search_info"));
      $search_info->add_child("page", xml_object::create("page", $dbSet['page']));
      $search_info->add_child("total_pages", xml_object::create("total_pages", $dbSet['total_pages']));
      $search_info->add_child("prefilter_count", xml_object::create("prefilter_count", $dbSet['prefilter_count']));
      $search_info->add_child("postfilter_count", xml_object::create("postfilter_count", $dbSet['postfilter_count']));
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      if(is_array($ticket_data)) {
         foreach($ticket_data as $ticket) {
            $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'])));
         }
      }
      return TRUE;
   }
   
   function do_partial_ticket_id_search($fields, $filters) {
   	$ticket_search = $fields["partial_ticket_id"];
   	$dbSet = $this->db->Get("search", "partial_ticket_id_search", array("ticket_search"=>$ticket_search, "filters"=>$filters));
   	$ticket_data = $dbSet['results'];
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $search_info =& $data->add_child("search_info", xml_object::create("search_info"));
      $search_info->add_child("page", xml_object::create("page", $dbSet['page']));
      $search_info->add_child("total_pages", xml_object::create("total_pages", $dbSet['total_pages']));
      $search_info->add_child("prefilter_count", xml_object::create("prefilter_count", $dbSet['prefilter_count']));
      $search_info->add_child("postfilter_count", xml_object::create("postfilter_count", $dbSet['postfilter_count']));
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      if(is_array($ticket_data)) {
         foreach($ticket_data as $ticket) {
            $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'])));
         }
      }
      return TRUE;
   }

   function do_assigned_search($fields, $filters) {
      $assigned_id = $fields["agent_id"];
      $dbSet = $this->db->Get("search", "assigned_search", array("assigned_id"=>$assigned_id, "filters"=>$filters));
      $ticket_data = $dbSet['results'];
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $search_info =& $data->add_child("search_info", xml_object::create("search_info"));
      $search_info->add_child("page", xml_object::create("page", $dbSet['page']));
      $search_info->add_child("total_pages", xml_object::create("total_pages", $dbSet['total_pages']));
      $search_info->add_child("prefilter_count", xml_object::create("prefilter_count", $dbSet['prefilter_count']));
      $search_info->add_child("postfilter_count", xml_object::create("postfilter_count", $dbSet['postfilter_count']));
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      if(is_array($ticket_data)) {
         foreach($ticket_data as $ticket) {
            $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'])));
         }
      }
      return TRUE;
   }

   function do_filter_search($fields, $filters) {
      $dbSet = $this->db->Get("search", "filter_search", array("filters"=>$filters));
      $ticket_data = $dbSet['results'];
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $search_info =& $data->add_child("search_info", xml_object::create("search_info"));
      $search_info->add_child("page", xml_object::create("page", $dbSet['page']));
      $search_info->add_child("total_pages", xml_object::create("total_pages", $dbSet['total_pages']));
      $search_info->add_child("prefilter_count", xml_object::create("prefilter_count", $dbSet['prefilter_count']));
      $search_info->add_child("postfilter_count", xml_object::create("postfilter_count", $dbSet['postfilter_count']));
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      if(is_array($ticket_data)) {
         foreach($ticket_data as $ticket) {
            $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'])));
         }
      }
      return TRUE;
   }

   function do_advanced_search($fields, $filters) {
      if(array_key_exists("keywords", $fields) && strlen(cer_Whitespace::mergeWhitespace($fields["keywords"])) > 0) {
         $keywords = explode(" ", cer_Whitespace::mergeWhitespace($fields["keywords"]));
         $searchwords = array();
         foreach($keywords as $word) {
            $word = preg_replace("/[^\w\*\%\.@]/", "", $word);
            if(strstr($word, "*") !== FALSE) {
               $word = str_replace("*", "%", $word);
               $searchwords = array_merge($searchwords, $this->db->Get("search", "get_words_from_partial", array("word"=>$word)));
            }
            else {
               $searchwords[] = $word;
            }
         }
         $keywords = "'" . implode("','", $searchwords) . "'";
         $dbSet = $this->db->Get("search", "keyword_search", array("keywords"=>$keywords, "filters"=>$filters));
      }
      else {
         $dbSet = $this->db->Get("search", "filter_search", array("filters"=>$filters));
      }
      $ticket_data = $dbSet['results'];
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $search_info =& $data->add_child("search_info", xml_object::create("search_info"));
      $search_info->add_child("page", xml_object::create("page", $dbSet['page']));
      $search_info->add_child("total_pages", xml_object::create("total_pages", $dbSet['total_pages']));
      $search_info->add_child("prefilter_count", xml_object::create("prefilter_count", $dbSet['prefilter_count']));
      $search_info->add_child("postfilter_count", xml_object::create("postfilter_count", $dbSet['postfilter_count']));
      $tickets =& $data->add_child("tickets", xml_object::create("tickets"));
      if(is_array($ticket_data)) {
         foreach($ticket_data as $ticket) {
            $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket['ticket_id'])));
         }
      }
      return TRUE;
   }
}
