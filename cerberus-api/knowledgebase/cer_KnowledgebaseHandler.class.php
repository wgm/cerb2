<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| File: cer_KnowledgebaseHandler.class.php
|
| Purpose: Knowledgebase object
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once (FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once (FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseTree.class.php");
require_once (FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

class cer_KnowledgebaseHandler
{
	var $db = null;
	var $tree = null;
	
	var $show_kb_search = false;
	var $show_article_create = false;
	var $show_article_edit = false;
	var $show_article_delete = false;
	var $show_topic_totals = false;
	var $show_comment_editor = false;
	
	var $options_kb_categories = array();
	var $cat_table_nodes = array();
	var $cat_articles = array();
	var $search_articles = array();
	
//	var $bread_crumb_trail = null;
//	var $num_per_col = 0;
	var $category_id = 0;
	var $category_name = null;
	
	var $focus_article = null;																		// Used for Article Create/Edit
	
	function cer_KnowledgebaseHandler()
	{
		$this->db = cer_Database::getInstance();

		$this->tree = new cer_KnowledgebaseTree();
		
		$this->_determine_links();
		$this->_populate_options();
		$this->_determine_actions();
	}
	
	function _determine_actions()
	{
		$mode = $_REQUEST["mode"];
		$root = $_REQUEST["root"];

//		$kbc_trueid = @$this->tree->tree_struct[$root]->node_id;
		$this->category_id = $root;
		$this->category_name = @$this->tree->categories[$root]->category_name;
		
		switch($mode)
		{
			default:
			case "view":
//				$this->cat_table_nodes = $this->tree->build_kb_table($root);
				$this->cat_articles = $this->tree->buildArticleList($root);
			break;
			
			case "create":
				$this->focus_article = new cer_KnowledgebaseArticle($this,0);
			break;
			
			case "edit_entry":
				global $kbid;
				$this->focus_article = new cer_KnowledgebaseArticle($this,$kbid);
			break;
			
			case "view_entry":
				global $kbid;
				$this->focus_article = new cer_KnowledgebaseArticle($this,$kbid);
			break;
			
			case "results":
				// results are populated from search action form_submit
			break;
				
		}
	}
	
	function _populate_options()
	{
//		$this->tree->fall_node(0,0,1,-1,$this->options_kb_categories);
	}
	
	function _determine_links()
	{
		global $priv;
		$cfg = CerConfiguration::getInstance();

		$root = $_REQUEST["root"];
		
		if(function_exists("cer_href")) {
			$this->category_create_url = cer_href("knowledgebase.php?mode=create&root=$root");
		}

		if($priv->has_priv(ACL_KB_SEARCH,BITGROUP_1))
			$this->show_kb_search = true;

		if($priv->has_priv(ACL_KB_ARTICLE_CREATE,BITGROUP_1))
			$this->show_article_create = true;
			
		if($priv->has_priv(ACL_KB_ARTICLE_EDIT,BITGROUP_1))
			$this->show_article_edit = true;
			
		if($priv->has_priv(ACL_KB_ARTICLE_DELETE,BITGROUP_1))
			$this->show_article_delete = true;
			
		if($priv->has_priv(ACL_KB_COMMENT_EDITOR,BITGROUP_2))
			$this->show_comment_editor = true;
		
		if($cfg->settings["show_kb_topic_totals"])
			$this->show_topic_totals = true;
		
	}
	
	function getKeywordString($kb_ask) {
		$search = new cer_searchIndex();
		
		if(!empty($kb_ask)) {
			$search->indexWords($kb_ask);
			$search->removeExcludedKeywords();
			$search->loadWordIDs(1);
		}
		
		$keywords = array();
		
		$sql = sprintf("SELECT si.word_id, w.word, count(si.word_id)  AS instances ".
				"FROM (`search_index_kb` si, `search_words` w) ".
				"WHERE si.word_id = w.word_id AND si.word_id IN ( %s )  ".
				"GROUP BY si.word_id ".
				"ORDER BY instances DESC ".
				"LIMIT 0,5", // keep the five top linked keywords
					implode(",", array_values($search->wordarray))
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($word_row = $this->db->fetch_row($res)) {
				$id = $word_row["word_id"];
				$word = $word_row["word"];
				$count = $word_row["instances"];
				$keywords[] = $word;
			}
		}
		
		$keyword_string = "";
		
		if(!empty($keywords)) {
			$keyword_string = implode(' ',$keywords);
		}
		else if (!empty($kb_keywords)) {
			$keyword_string = $kb_keywords;
		}
		
		return $keyword_string;
	}

	function getCategoryIdForArticleId($id) {
		$sql = "SELECT k.kb_id,k.kb_category_id FROM knowledgebase k WHERE k.kb_id = $id";
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res)) {
			return $row["kb_category_id"];
		}
		else {
			return "0";
		}
	}
	
};

class cer_KnowledgebaseArticle
{
	var $db = null;
	var $kb_ptr = null;
	
	var $article_id = 0;
	var $article_summary = null;
	var $article_entry_date = null;
	var $article_public = 0;
	var $article_keywords = null;
	var $article_problem_text = null;
	var $article_problem_text_is_html = 0;
	var $article_solution_text = null;
	var $article_solution_text_is_html = 0;
	var $article_entry_user = null;
	var $article_category_id = 0;
	var $article_category_name = null;
	var $article_mode = null;

	var $article_rating_avg = null;
	var $article_rating_count = 0;
	var $article_rating_width = 0;
	var $article_rating_percent = 0;
	var $article_rating_percent_i = 0;
	
	var $url_article_edit = null;
	var $url_article_delete = null;
	var $url_comment_delete = null;
	var $url_return = null;
	var $url_add_comment = null;
	
	var $ip_has_voted = false;
	
	var $comments = array();
	
	function cer_KnowledgebaseArticle(&$kb,$kbid=0)
	{
		$this->db = cer_Database::getInstance();
		$this->kb_ptr = &$kb;
		
		$this->_load_article_details($kbid);
		$this->_load_article_comments();
		$this->_check_voting();
		
	}
	
	function _check_voting()
	{
		global $_SERVER;
		
		$sql = sprintf("SELECT rating_id FROM knowledgebase_ratings WHERE ip_addr = '%s' AND kb_article_id = %d",
			$_SERVER['REMOTE_ADDR'],
			$this->article_id
			);
		$rate_res = $this->db->query($sql);
		
		if($this->db->num_rows($rate_res))
			$this->ip_has_voted = true;

	}
	
	function _load_article_details($kbid=0)
	{
		global $session; // clean
		
		$root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;
		$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";

		$this->article_mode = (($mode=="create") ? "create" : "edit");

		if($kbid==0) {
			$this->article_entry_date = date("D M d Y",mktime());
			$this->article_entry_user = $session->vars["login_handler"]->user_login;
			return true;
		}
		
		$sql = "SELECT k.kb_id, k.kb_entry_date, k.kb_public, k.kb_category_id, k.kb_keywords, kp.kb_problem_summary, kp.kb_problem_text, kp.kb_problem_text_is_html, " .
			" ks.kb_solution_text, ks.kb_solution_text_is_html, kc.kb_category_name, u.user_login As entry_user, k.kb_avg_rating, k.kb_rating_votes " . 
			" FROM knowledgebase k LEFT JOIN knowledgebase_problem kp ON (kp.kb_id=k.kb_id) LEFT JOIN knowledgebase_solution ks on (ks.kb_id=k.kb_id) ".
			" LEFT JOIN knowledgebase_categories kc ON (kc.kb_category_id=k.kb_category_id) LEFT JOIN user u ON (k.kb_entry_user=u.user_id) " . 
			" WHERE k.kb_id = " . $kbid;
	  $result = $this->db->query($sql);
	  if($this->db->num_rows($result))
	  {
		  $kb_data = $this->db->fetch_row($result);
	
			$this->article_id = $kb_data["kb_id"];
			$this->article_summary = stripslashes($kb_data["kb_problem_summary"]);
			$this->article_public = $kb_data["kb_public"];
			$this->article_keywords = stripslashes($kb_data["kb_keywords"]);
			$this->article_problem_text = stripslashes($kb_data["kb_problem_text"]);
			$this->article_problem_text_is_html = $kb_data["kb_problem_text_is_html"];
			$this->article_solution_text = stripslashes($kb_data["kb_solution_text"]);
			$this->article_solution_text_is_html = $kb_data["kb_solution_text_is_html"];
			$date = new cer_DateTime($kb_data["kb_entry_date"]);
			$this->article_entry_date = $date->getUserDate();
			$this->article_entry_user = $kb_data["entry_user"];
			$this->article_category_id = $kb_data["kb_category_id"];
			$this->article_category_name = $kb_data["kb_category_name"];
			$this->article_rating_avg = $kb_data["kb_avg_rating"];
			$this->article_rating_count = $kb_data["kb_rating_votes"];

			if($this->article_rating_avg)
			{
				$this->article_rating_percent = round($this->article_rating_avg * 20); // make a 5.0 max into 100%
				$this->article_rating_percent_i = 100 - $this->article_rating_percent;
				$this->article_rating_width = round($this->article_rating_avg * 10);
			}
			
			$this->url_article_delete = sprintf("knowledgebase.php?kbid=%s&root=%s&form_submit=kb_delete",
				$this->article_id,
				$root
				);
			
			if(function_exists("cer_href")) {
				$this->url_article_edit = cer_href(sprintf("knowledgebase.php?mode=edit_entry&kbid=%d&root=%d",
					$this->article_id,
					$root
					));
			}
				
			$this->url_comment_delete = sprintf("knowledgebase.php?kbid=%d&root=%s&kb_comment_id=\"+comment_id+\"&form_submit=kb_comment_delete",
				$this->article_id,
				$root
				);
				
			$this->url_return = sprintf("knowledgebase.php?root=%d",
					$this->article_category_id
				);
				
			if(function_exists("cer_href")) {
				$this->url_add_comment = cer_href(sprintf("knowledgebase.php?mode=view_entry&kb_comment=1&kbid=%d&root=%d",
					$this->article_id,
					$root
					),"comment");
			}
	  }
	}
	
	function _load_article_comments()
	{
		$root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;
		$kb_comment_edit = isset($_REQUEST["kb_comment_edit"]) ? $_REQUEST["kb_comment_edit"] : null;
		
		if(empty($this->article_id)) return true;
		
		$sql = "SELECT kb_comment_id, poster_email, poster_comment, kb_comment_date, kb_comment_approved ".
			"FROM knowledgebase_comments WHERE kb_article_id = " . $this->article_id . " " .
			"ORDER BY kb_comment_date ASC";
		$comment_res = $this->db->query($sql);
		
		if($this->db->num_rows($comment_res))
		{
			while($com_res = $this->db->fetch_row($comment_res))
			{
				$comment = new cer_KnowledgebaseArticleComment();
				
				$comment->comment_id = $com_res["kb_comment_id"];
				$comment->comment_poster = str_replace("@"," at ",str_replace("."," dot ",$com_res["poster_email"]));
				$date = new cer_DateTime($com_res["kb_comment_date"]);
				$comment->comment_date = $date->getUserDate();
				$comment->comment_approved = $com_res["kb_comment_approved"];
				
				if(!empty($kb_comment_edit) && $kb_comment_edit == $comment->comment_id)
				{
					$comment->comment_content = stripslashes($com_res["poster_comment"]);
				}
				else
				{
					$comment_str = preg_replace("/(http|https):\/\/(.\S+)()/si",
						"<a href='\$1://\$2'>\$1://\$2</a>",
						$com_res["poster_comment"]);
					$comment_str = str_replace("  "," &nbsp;",$comment_str);
					$comment_str = str_replace(chr(9),"  &nbsp; ",$comment_str); // Replace tabs with three spaces
					$comment_str = str_replace("\r\n","\n",$comment_str);
					$comment_str = str_replace("\n","<br>",$comment_str);
					$comment->comment_content = stripslashes($comment_str);
				}
				
				$comment->url_edit = cer_href(sprintf("knowledgebase.php?mode=view_entry&kb_comment_edit=%d&kbid=%d&root=%d",
					$comment->comment_id,
					$this->article_id,
					$root
					));
				
				array_push($this->comments,$comment);
			}			
		}
	}

};

class cer_KnowledgebaseArticleComment
{
	var $comment_id = 0;
	var $comment_poster = null;
	var $comment_date = null;
	var $comment_content = null;
	var $comment_approved = 0;
	var $url_edit = null;
};

?>