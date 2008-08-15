<?php

require_once (FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

class cer_KnowledgebaseCategory {
	var $category_id = 0;
	var $category_name = null;
	var $parent_id = null;
	var $total_articles = 0;
	var $sorted_children = array();
	var $children = array();
	var $url_view = null;
	var $child_list = null;
	
	function cer_KnowledgebaseCategory($id,$parent_id=-1,$name="") {
		$this->category_id = $id;
		$this->parent_id = $parent_id;
		$this->category_name = $name;
		
		if(function_exists("cer_href"))
		$this->url_view = sprintf("%s",
				cer_href("knowledgebase.php?root=" . $this->category_id)
			);
	}

	function addChild(&$child_ptr) {
		$this->children[] = &$child_ptr;
	}
};

class cer_KnowledgebaseTree {
	var $db = null;
	var $categories = array();
	var $half = 0;
	var $topic_totals = array();
	var $category_dropdown = array();
	var $category_checked = array();
		
	function cer_KnowledgebaseTree() {
		$this->db = cer_Database::getInstance();
		$this->categories[0] = new cer_KnowledgebaseCategory(0,-1,"Top");
		
		$root = $_REQUEST["root"];
		if(empty($root)) $root = 0;

		$sql = "SELECT kbc.kb_category_id, kbc.kb_category_name, kbc.kb_category_parent_id, count(k.kb_id) as num_articles ".
			"FROM knowledgebase_categories kbc ".
			"LEFT JOIN knowledgebase k ON (k.kb_category_id = kbc.kb_category_id) ". //  AND k.kb_public = 1
			"GROUP BY kbc.kb_category_id ".
			"ORDER BY kbc.kb_category_id ASC";
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$parent_id = $row["kb_category_parent_id"];
				$cat_id = $row["kb_category_id"];
				$cat_name = stripslashes($row["kb_category_name"]);
				$parent_id = $row["kb_category_parent_id"];
				$num_articles = $row["num_articles"];
				
				if(!isset($this->categories[$cat_id])) {
					$this->categories[$cat_id] = new cer_KnowledgebaseCategory($cat_id,$parent_id,$cat_name);
				}
				else {
					$this->categories[$cat_id]->category_name = $cat_name;
					$this->categories[$cat_id]->parent_id = $parent_id;
				}
				
				$this->topic_totals[$cat_id] = $num_articles;
				
				if(!isset($this->categories[$parent_id])) {
					$this->categories[$parent_id] = new cer_KnowledgebaseCategory($parent_id);
				}
				
				$this->categories[$parent_id]->addChild($this->categories[$cat_id]);
			}
		}

		$this->half = ceil(count($this->categories[$root]->children)/2);
		
		$this->_sortChildren();
		$this->_fillArticleCounts();
//		$this->_clearEmptyCategories();
		$this->_buildChildText();
	}
	
	// [JAS]: After all children are loaded up we should sort them
	function _sortChildren() {
		foreach($this->categories as $idx => $c) {
			$this->categories[$idx]->sorted_children = 
				cer_PointerSort::pointerSortCollection($this->categories[$idx]->children,"category_name");
		}
	}
	
	function _fillArticleCounts() {
		$this->recurse_category(0);
		
		foreach($this->topic_totals as $i => $tot) {
			$this->categories[$i]->total_articles = $tot;
		}
	}
	
	function _clearEmptyCategories() {
		if(!empty($this->categories))
		foreach($this->categories as $i => $c) {
			
			if(!empty($c->children))
			foreach($c->children as $ii => $cc) {
				if(empty($this->categories[$i]->children[$ii]->total_articles)) {
					$this->categories[$i]->children[$ii] = null;
					unset($this->categories[$i]->children[$ii]);
				}
			}
		}
	}
	
	function _buildChildText() {
		
		foreach($this->categories as $c_idx => $cat) {
			$c_list = array();
			
			if(count($cat->sorted_children))
			foreach($cat->sorted_children as $c) {
				if(function_exists("cer_href"))
				$c_list[] = sprintf("<a href='%s' class='cer_knowledgebase_link'>%s</a>",
						cer_href("knowledgebase.php?root=" . $c->category_id),
						$c->category_name
					);
			}
			
			if(count($c_list))
				$this->categories[$c_idx]->child_list = 
					sprintf("%s<br>", 
						implode(", ", $c_list)
					);
		}
	}
	
	//! Add up the topics under a category and then recurse through its children
	/*!
	This function is used by calculate_topic_totals()
	
	\param $cat_id the category id to calculate totals for and then recurse
	\note This function is called by calculate_topic_totals() and should not be called directly.
	\return The total topics under \a $cat_id as an \c integer.
	*/
	function recurse_category($cat_id,$level=0)
	{
		$add_these = 0;
		$total = @$this->topic_totals[$cat_id];
		
		if(@$this->categories[$cat_id]->sorted_children) {
			foreach($this->categories[$cat_id]->sorted_children as $child) {
				
				// [JAS]: Take advantage of the recursion to build a structured dropdown of all categories
				$this->category_dropdown["" . $child->category_id] = 
					(($level-1>=0) ? str_repeat("--",$level) : "") . $child->category_name;
						
				$this->category_checked["" . $child->category_id] = 
						array($level,$child->category_name);
				
				$new_level = $level + 1;
				$add_these += $this->recurse_category($child->category_id,$new_level);
			}
		}
		
		@$this->topic_totals[$cat_id] += $add_these;
		
		return $total + $add_these;
	}
	
	// [JAS]: \todo When this moves to the API we should change it to return an assoc array of (cat id => cat name)
	// 		rather than HTML links.  Let the caller format the HTML in Smarty or directly.
	function printTrail($root) {
		global $base_url;
		$stack = array();
		$at = $root;
		
		while($at != -1) {
			if(function_exists("cer_href"))
			$stack[] = sprintf("<a href='%s' class='%s'>%s</a>",
					cer_href("knowledgebase.php?root=" . $this->categories[$at]->category_id),
					"cer_knowledgebase_link",
					$this->categories[$at]->category_name
				);
			$at = $this->categories[$at]->parent_id;
		} 
		
		$stack = array_reverse($stack);
		
		return implode(" : ", $stack);
	}
	
	function buildAskList(&$ask_results)
	{
		global $session;
		
		$articles = array();
		$root = $_REQUEST["root"];

		foreach($ask_results as $ask) {
			$article = new cer_KnowledgebaseArticleListing();
			
			$article->article_id = sprintf("%05.0f",$ask->kb_id);
			if(function_exists("cer_href")) $article->article_url = cer_href(sprintf("knowledgebase.php?mode=view_entry&kbid=%d&root=",$ask->kb_id,$root));
			$article->article_summary = $ask->subject;
			$article->article_rating = $ask->score;

			array_push($articles,$article);
		}
	
		return $articles;
	}	

	function buildKeywordSearchList(&$keyword_results,$keyword_string)
	{
		global $session;
		
		$articles = array();
		
		if($this->db->num_rows($keyword_results))
		while($row=$this->db->fetch_row($keyword_results))
		{
			$article = new cer_KnowledgebaseArticleListing();
			
			$article->article_id = sprintf("%05.0f",$row["kb_id"]);
			if(function_exists("cer_href")) $article->article_url = cer_href(sprintf("knowledgebase.php?mode=view_entry&kbid=%d&root=%d",$row["kb_id"],$row["kb_category_id"]));
			$article->article_summary = stripslashes($row["kb_problem_summary"]);

			$num_keywords = count(explode(' ',$keyword_string));

			$percent = ($row["matches"] / $num_keywords) * 100;
			$percent = sprintf("%0.2f%%",$percent);
			
			$article->article_matches = $percent;
			
			array_push($articles,$article);
		}
	
		return $articles;
	}	
	
	function buildArticleList($cat_id) // $kbc_id,$kbc=0
		{
			global $session;
			
			$articles = array();
			
			$sql = "SELECT kb.kb_id, kbp.kb_problem_summary,kbp.kb_problem_text,kb.kb_public,kb.kb_avg_rating,kb.kb_rating_votes ".
				"FROM (knowledgebase_problem kbp, knowledgebase kb) " . 
				"WHERE kb.kb_id = kbp.kb_id AND kb.kb_category_id = $cat_id";
			$result = $this->db->query($sql);

//			echo $sql;
			
			if($this->db->num_rows($result))
			while($row=$this->db->fetch_row($result))
			{
			    $article = new cer_KnowledgebaseArticleListing(); // CER_KNOWLEDGEBASE_ARTICLE_LISTING();
						
		        $article->article_id = sprintf("%05.0f",$row["kb_id"]);
		        if(function_exists("cer_href")) $article->article_url = cer_href(sprintf("knowledgebase.php?mode=view_entry&kbid=%d&root=%d",$row["kb_id"],$cat_id));
		        $article->article_summary = stripslashes($row["kb_problem_summary"]);
				$article->article_brief = stripslashes($row["kb_problem_text"]);
				$article->article_public = $row["kb_public"];
				$article->article_rating = sprintf("%0.1f",$row["kb_avg_rating"]);
				$article->article_votes = $row["kb_rating_votes"];
		        
				if($article->article_rating)
		  		{
			     	$article->rate_percent = round($article->article_rating * 20);
			     	$article->rate_percent_i = 100 - $article->rate_percent;
			     	$article->rate_width = round($article->article_rating * 10);
		  		}

				array_push($articles,$article);
			}
		
		return $articles;
		}
};

class cer_KnowledgebaseArticleListing
{
	var $article_id = 0;
	var $article_summary = null;
	var $article_url = null;
	var $article_brief = null;
	var $article_public = 0;
	var $article_rating = 0.0;
	var $article_matches = 0;
	var $article_votes = 0;
	var $rate_percent = 0;
	var $rate_percent_i = 0;
	var $rate_width = 0;
};


?>