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
class knowledgebase_sql
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
    * @return knowledgebase_sql
    */
   function knowledgebase_sql(&$db) {
      $this->db =& $db;
   }
   
   /**
    * Gets the knowledgebase categories from a specified root
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_categories_list($params) {
   	extract($params);
      $sql = "SELECT kbc.kb_category_id, kbc.kb_category_name, kbc.kb_category_parent_id FROM `knowledgebase_categories` kbc ORDER BY kbc.kb_category_id ASC";
      return $this->db->GetAll(sprintf($sql));
   }
   
   function get_articles_by_category($params) {
   	extract($params);
   	$sql = "SELECT kb.kb_id, kb.kb_category_id, kbp.kb_problem_summary, kb.kb_avg_rating, kb.kb_rating_votes, kb.kb_public_views FROM `knowledgebase` kb, `knowledgebase_problem` kbp WHERE kb.kb_id = kbp.kb_id AND kb.kb_category_id = '%d'";
   	return $this->db->GetAll(sprintf($sql, $category_id));
   }

   function get_article_by_id($params) {
   	extract($params);
   	$sql = "SELECT kb.kb_id, kb.kb_category_id, kbp.kb_problem_summary, kb.kb_avg_rating, kb.kb_rating_votes, kb.kb_public_views, kbp.kb_problem_text, kbp.kb_problem_text_is_html, kbs.kb_solution_text, kbs.kb_solution_text_is_html ".
   	"FROM `knowledgebase` kb, `knowledgebase_problem` kbp, `knowledgebase_solution` kbs ".
   	"WHERE kb.kb_id = kbp.kb_id ".
   	"AND kb.kb_id = kbs.kb_id ".
   	"AND kb.kb_id = '%d' ";
   	return $this->db->GetRow(sprintf($sql, $article_id));
   }

}