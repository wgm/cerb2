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
|
| File: knowledgebase.php
|
| Purpose: The knowledgebase system.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted (ben@webgroupmedia.com)		[BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/general.php");
require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");

require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramKB.class.php");
require_once (FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
//require_once(FILESYSTEM_PATH . "includes/cerberus-api/knowledgebase/knowledgebase.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseHandler.class.php");
require_once (FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramCerby.class.php");

log_user_who_action(WHO_KNOWLEDGEBASE);

if(!$priv->has_priv(ACL_KB_VIEW,BITGROUP_1)) { header("Location: " .cer_href("index.php")); }

//######
$root = ((@$_REQUEST["root"]) ? $_REQUEST["root"] : 0);
//######

//@$search_content = $_REQUEST["search_content"];
//@$search_category = $_REQUEST["search_category"];
//@$kbcat = $_REQUEST["kbcat"];

$kbid = isset($_REQUEST["kbid"]) ? $_REQUEST["kbid"] : "";
$kb_category_id = isset($_REQUEST["kb_category_id"]) ? $_REQUEST["kb_category_id"] : "";
$kb_keywords = isset($_REQUEST["kb_keywords"]) ? stripslashes($_REQUEST["kb_keywords"]) : "";
$kb_ask = isset($_REQUEST["kb_ask"]) ? stripslashes($_REQUEST["kb_ask"]) : "";
$kb_public = isset($_REQUEST["kb_public"]) ? $_REQUEST["kb_public"] : "";
$kb_problem_summary = isset($_REQUEST["kb_problem_summary"]) ? $_REQUEST["kb_problem_summary"] : "";
$kb_problem_text = isset($_REQUEST["kb_problem_text"]) ? $_REQUEST["kb_problem_text"] : "";
$kb_problem_text_type = isset($_REQUEST["kb_problem_text_type"]) ? $_REQUEST["kb_problem_text_type"] : 0;
$kb_solution_text = isset($_REQUEST["kb_solution_text"]) ? $_REQUEST["kb_solution_text"] : "";
$kb_solution_text_type = isset($_REQUEST["kb_solution_text_type"]) ? $_REQUEST["kb_solution_text_type"] : 0;
$search_id = isset($_REQUEST["search_id"]) ? trim($_REQUEST["search_id"]) : "";
$form_submit = isset($_REQUEST["form_submit"]) ? $_REQUEST["form_submit"] : "";
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "view";
$kb_rating = isset($_REQUEST["kb_rating"]) ? $_REQUEST["kb_rating"] : "";
$kb_comment = isset($_REQUEST["kb_comment"]) ? $_REQUEST["kb_comment"] : "";
$poster_email = isset($_REQUEST["poster_email"]) ? $_REQUEST["poster_email"] : "";
$poster_comment = isset($_REQUEST["poster_comment"]) ? $_REQUEST["poster_comment"] : "";
$kb_comment_id = isset($_REQUEST["kb_comment_id"]) ? $_REQUEST["kb_comment_id"] : "";
$kb_comment_edit = isset($_REQUEST["kb_comment_edit"]) ? $_REQUEST["kb_comment_edit"] : "";
$kb_comment_content = isset($_REQUEST["kb_comment_content"]) ? $_REQUEST["kb_comment_content"] : "";
$kb_clean_learning = isset($_REQUEST["kb_clean_learning"]) ? $_REQUEST["kb_clean_learning"] : "";

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_db = cer_Database::getInstance();

$kbase_format = new cer_formatting_obj; // ***

$cer_trigram = new cer_TrigramKB();
$cer_search = new cer_SearchIndexKB();

$cer_tpl->assign('kb_comment_edit',$kb_comment_edit);
$cer_tpl->assign('kb_comment',$kb_comment);

$kb = new cer_KnowledgebaseHandler();

//######
if(empty($root) && !empty($kbid)) { $root = $kb->getCategoryIdForArticleId($kbid); }
$cer_tpl->assign('root',$root);
$cer_tpl->assign('kb_ask',$kb_ask);
//######

$cer_tpl->assign('remote_addr',$_SERVER["REMOTE_ADDR"]);

if(isset($_REQUEST["form_submit"])) {
	switch($form_submit)
		{
		case "kb_rating":
		{
			$sql = sprintf("REPLACE INTO `knowledgebase_ratings` (ip_addr,kb_article_id,rating) VALUES ('%s',%d,%d);",
				$_SERVER['REMOTE_ADDR'],$kbid,$kb_rating);
			$cerberus_db->query($sql);
			
			$sql = sprintf("select kb_article_id, avg(rating) as rating_avg,count(rating_id) as rating_count from knowledgebase_ratings WHERE kb_article_id = %d GROUP BY kb_article_id",
					$kbid
				);
			$res = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($res)) {
				while($kbr = $cerberus_db->fetch_row($res)) {
					$sql = sprintf("UPDATE knowledgebase SET kb_avg_rating = %f, kb_rating_votes = %d WHERE kb_id = %d",
							sprintf("%0.1f",$kbr["rating_avg"]),
							$kbr["rating_count"],
							$kbr["kb_article_id"]
						);
					$cerberus_db->query($sql);
				}
			}
			
			header("Location: ".cer_href("knowledgebase.php?mode=view_entry&kbid=$kbid&root=$root"));
			break;
		}
			
		case "kb_comment":
			{
			$poster_email = @htmlspecialchars($poster_email, ENT_QUOTES, LANG_CHARSET_CODE);
			$poster_comment = @htmlspecialchars($poster_comment, ENT_QUOTES, LANG_CHARSET_CODE);
			if($cfg->settings["kb_editors_enabled"]) $article_approved = 0; else $article_approved = 1;
			
			$sql = sprintf("INSERT INTO knowledgebase_comments (kb_article_id,kb_comment_approved,kb_comment_date,poster_email,poster_comment,poster_ip) ".
				"VALUES (%d,%d,NOW(),%s,%s,'%s')",
				$kbid,$article_approved,$cerberus_db->escape($poster_email),$cerberus_db->escape($poster_comment),$_SERVER["REMOTE_ADDR"]);
			$cerberus_db->query($sql);
			header("Location: ".cer_href("knowledgebase.php?mode=view_entry&kbid=$kbid&root=$root"));
			break;
			}
			
		case "kb_comment_edit":
			{
			$kb_comment_content = @htmlspecialchars($kb_comment_content, ENT_QUOTES, LANG_CHARSET_CODE);
			$sql = sprintf("UPDATE knowledgebase_comments SET poster_comment = %s WHERE kb_comment_id = %d",
				$cerberus_db->escape($kb_comment_content),$kb_comment_id);
			$cerberus_db->query($sql);
			header("Location: ".cer_href("knowledgebase.php?mode=view_entry&kbid=$kbid&root=$root"));
			break;
			}
			
		case "kb_comment_delete":
			{
			if($priv->has_priv(ACL_KB_COMMENT_EDITOR,BITGROUP_2)) {

	  			$sql = "DELETE FROM knowledgebase_comments WHERE kb_comment_id = $kb_comment_id";
	  			$cerberus_db->query($sql);
			}
			header("Location: ".cer_href("knowledgebase.php?mode=view_entry&kbid=$kbid&root=$root"));
			break;
			}
			
		case "kb_create":
			{
			if($priv->has_priv(ACL_KB_ARTICLE_CREATE,BITGROUP_1)) { 
  			$sql = "INSERT INTO knowledgebase(kb_entry_date,kb_entry_user,kb_category_id,kb_keywords,kb_public) " . 
  				" VALUES(NOW()," . $session->vars["login_handler"]->user_id . ",$kb_category_id,'" . addslashes($kb_keywords) . "',$kb_public) ";
  			$cerberus_db->query($sql);
  			$last_kb_id = $cerberus_db->insert_id();
  			
  			$sql = "INSERT INTO knowledgebase_problem(kb_id,kb_problem_summary,kb_problem_text,kb_problem_text_is_html) " . 
  				sprintf(" VALUES(%d,'%s','%s',%d)",
  						$last_kb_id,
  						addslashes($kb_problem_summary),
  						addslashes($kb_problem_text),
  						$kb_problem_text_type
  					);
  			$cerberus_db->query($sql);
  
  			$sql = "INSERT INTO knowledgebase_solution(kb_id,kb_solution_text,kb_solution_text_is_html) " . 
  				sprintf(" VALUES(%d,'%s',%d) ",
  						$last_kb_id,
  						addslashes($kb_solution_text),
  						$kb_solution_text_type
  					);
  			$cerberus_db->query($sql);

			$cer_trigram->indexSingleArticle($last_kb_id);
			$cer_search->indexSingleArticle($last_kb_id);
			}
			header("Location: ".cer_href("knowledgebase.php?root=$root"));
			break;
			}
			
		case "kb_delete":
			{
			if($priv->has_priv(ACL_KB_ARTICLE_DELETE,BITGROUP_1)) {
	  			$sql = "DELETE FROM knowledgebase WHERE kb_id = $kbid";
	  			$cerberus_db->query($sql);
	  
	  			$sql = "DELETE FROM knowledgebase_problem WHERE kb_id = $kbid";
	  			$cerberus_db->query($sql);
	  
	  			$sql = "DELETE FROM knowledgebase_solution WHERE kb_id = $kbid";
	  			$cerberus_db->query($sql);
	  			
	  			$sql = "DELETE FROM search_index_kb WHERE kb_article_id = $kbid";
	  			$cerberus_db->query($sql);

	  			$sql = "DELETE FROM knowledgebase_ratings WHERE kb_article_id = $kbid";
	  			$cerberus_db->query($sql);

	  			$sql = "DELETE FROM knowledgebase_comments WHERE kb_article_id = $kbid";
	  			$cerberus_db->query($sql);
	  			
	  			// remove trigrams from article
	  			$cer_trigram->deleteFromArticle($kbid);
	  			$cer_search->deleteFromArticle($kbid);
			}
			header("Location: ".cer_href("knowledgebase.php?root=$root"));
			break;
			}
			
		case "kb_edit":
			{
			if($priv->has_priv(ACL_KB_ARTICLE_EDIT,BITGROUP_1)) {
	  			$sql = sprintf("UPDATE knowledgebase SET kb_category_id=%d,kb_keywords='%s',kb_public=%d WHERE kb_id = %d",
	  					$kb_category_id,
	  					addslashes($kb_keywords),
	  					$kb_public,
	  					$kbid
	  				); 
	  			$cerberus_db->query($sql);
	  			
	  			$sql = sprintf("UPDATE knowledgebase_problem SET kb_problem_summary='%s', kb_problem_text='%s', kb_problem_text_is_html=%d WHERE kb_id = %d",
	  					addslashes($kb_problem_summary),
	  					addslashes($kb_problem_text),
	  					addslashes($kb_problem_text_type),
	  					$kbid
	  				); 
	  			$cerberus_db->query($sql);
	  
	  			$sql = sprintf("UPDATE knowledgebase_solution SET kb_solution_text='%s', kb_solution_text_is_html=%d WHERE kb_id = %d",
	  					addslashes($kb_solution_text),
	  					addslashes($kb_solution_text_type),
	  					$kbid
	  				); 
	  			$cerberus_db->query($sql);
	  		if("1"==$kb_clean_learning) {
					$cer_trigram->deleteFromArticle($kbid);
	  		}
				$cer_trigram->indexSingleArticle($kbid);
				$cer_search->deleteFromArticle($kbid);
				$cer_search->indexSingleArticle($kbid);
			}
			header("Location: ".cer_href("knowledgebase.php?mode=view_entry&kbid=$kbid&root=$root"));
			break;
			}
			
		case "kb_search":
			{
				
			if(isset($search_id) && is_numeric($search_id) && !empty($search_id)) {
				$sql = "SELECT k.kb_id,k.kb_category_id FROM knowledgebase k WHERE k.kb_id = $search_id";
				$result = $cerberus_db->query($sql);
				
				//echo $sql;
				//print_r($result);
				//exit;
				
				if($row = $cerberus_db->grab_first_row($result))
				{
					header("Location: " . cer_href(sprintf("knowledgebase.php?mode=view_entry&kbid=%d&root=%d",
							$row["kb_id"],
							$row["kb_category_id"]
						)));
				}
			}
			
			elseif (!empty($kb_ask)) {
				
				$mode = "ask_results";
				
				$cerbyTrigram = new cer_TrigramCerby();
				$list = $cerbyTrigram->ask($kb_ask,10,0);
				
				if(!empty($list)) {
					$kb->search_articles = $kb->tree->buildAskList($list);
				}

				$keyword_string = $kb->getKeywordString($kb_ask);
				$cer_tpl->assign('kb_keyword_string',$keyword_string);
			}
			
			elseif (!empty($kb_keywords)) {
				
				$search = new cer_searchIndex();
				
				$search->indexWords($kb_keywords);
				$search->removeExcludedKeywords();
				$search->loadWordIDs(1);
				
				$sql = sprintf("SELECT k.kb_id, k.kb_category_id, kp.kb_problem_summary, count( si.kb_article_id )  AS matches ".
						"FROM  (`search_index_kb` si, `knowledgebase` k, `knowledgebase_problem` kp) ".
						"WHERE k.kb_id = kp.kb_id AND k.kb_id = si.kb_article_id ". // AND k.kb_public = 1
						"AND si.word_id IN ( %s )  ".
						"GROUP BY si.kb_article_id ".
						"ORDER BY matches DESC ".
						"LIMIT 0,25 ",
							implode(',',array_values($search->wordarray))
					); 
				$res = $cerberus_db->query($sql);
				
				$mode = "keyword_results";
				$kb->search_articles = $kb->tree->buildKeywordSearchList($res,$kb_keywords);
				
				$cer_tpl->assign('kb_keyword_string',$kb_keywords);
			}
				
			break;
			}		
		}
}
// ***************************************************************************************************************************

$cer_tpl->assign('mode',$mode);

// [JAS]: Header Functionality
$header_readwrite_queues = array();
$header_write_queues = array();

foreach($cer_hash->get_queue_hash(HASH_Q_READWRITE) as $queue)
{ $header_readwrite_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_readwrite_queues',$header_readwrite_queues);

foreach($cer_hash->get_queue_hash(HASH_Q_WRITE) as $queue)
{ $header_write_queues[$queue->queue_id] = $queue->queue_name; }
$cer_tpl->assign_by_ref('header_write_queues',$header_write_queues);

// ***************************************************************************************************************************
$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign_by_ref('priv',$priv);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php")
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "knowledgebase.php";
$cer_tpl->assign("page",$page);

// ***************************************************************************************************************************

$cer_tpl->assign_by_ref('kb',$kb);

$cer_tpl->display('knowledgebase.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
