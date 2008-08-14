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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseTree.class.php");

class general_knowledgebase
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function general_knowledgebase() {
      $this->db =& database_loader::get_instance();
   }

   function get_categories() {
		$tree = new cer_KnowledgebaseTree();

      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $categories =& $data->add_child("categories", xml_object::create("categories", NULL));

      $this->recurse_category($xml, $categories, $tree, 0);     
      
      return TRUE;
   }
   
   function get_articles_by_category($category_id) {
   	$article_list = $this->db->Get("knowledgebase","get_articles_by_category",array("category_id"=>$category_id));   	
   	
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

		$articles =& $data->add_child("articles", xml_object::create("articles", NULL));

		if(is_array($article_list)) {
			foreach($article_list as $article) {
				$article_item =& $articles->add_child("article", xml_object::create("article", NULL, array("id"=>$article["kb_id"])));
            $article_item->add_child("title", xml_object::create("title", stripslashes($article["kb_problem_summary"])));
            $article_item->add_child("category_id", xml_object::create("category_id", $article["kb_category_id"]));
            $article_item->add_child("avg_rating", xml_object::create("avg_rating", sprintf("%0.1f",$article["kb_avg_rating"])));
            $article_item->add_child("votes", xml_object::create("votes", $article["kb_rating_votes"]));
            $article_item->add_child("views", xml_object::create("views", $article["kb_public_views"]));
			}
		}
		
		return TRUE;   	
   }
   
   function get_article_by_id($article_id) {
   	$article_info = $this->db->Get("knowledgebase","get_article_by_id",array("article_id"=>$article_id));

      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);

      if(!empty($article_info)) {
      	$article =& $data->add_child("article",xml_object::create("article", NULL, array("id"=>$article_info["kb_id"])));
         $article->add_child("title", xml_object::create("title", stripslashes($article_info["kb_problem_summary"])));
         $article->add_child("category_id", xml_object::create("category_id", $article_info["kb_category_id"]));
         $article->add_child("avg_rating", xml_object::create("avg_rating", sprintf("%0.1f",$article_info["kb_avg_rating"])));
         $article->add_child("votes", xml_object::create("votes", $article_info["kb_rating_votes"]));
         $article->add_child("views", xml_object::create("views", $article_info["kb_public_views"]));
         $article->add_child("problem_text", xml_object::create("problem_text", stripslashes($article_info["kb_problem_text"])));
         $article->add_child("solution_text", xml_object::create("solution_text", stripslashes($article_info["kb_solution_text"])));
      }
   	
   	return TRUE;
   }
   
   function recurse_category($xml, &$parent, $tree, $cat_id, $level=0) {
   	
		if(@$tree->categories[$cat_id]->sorted_children) {
			foreach($tree->categories[$cat_id]->sorted_children as $child) {

            $category =& $parent->add_child("category", xml_object::create("category", NULL, array("id"=>$child->category_id)));
            $category->add_child("name", xml_object::create("name", $child->category_name));
            $category->add_child("total_articles", xml_object::create("total_articles", $child->total_articles));
				
				$new_level = $level + 1;
				$this->recurse_category($xml, $category, $tree, $child->category_id, $new_level);
			}
		}
   }
   
}