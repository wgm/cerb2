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
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_SESSION", true);

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseHandler.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/cer_KnowledgebaseTree.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramCerby.class.php");

$cerberus_db = cer_Database::getInstance();

// [JAS]: The remote IPs you're allowing to use the Fetch & Retrieve(tm) Gateway
$fnr_authorized_ips = array("127.0",
				   "192.168.1",
				   "0.0.0.0",
				   "0.0.0.0"
				   );

// DO NOT EDIT BELOW THIS LINE ------------------------------------------------------
				   
// [JAS]: Authorize IPs or close page
$pass = false;
foreach ($fnr_authorized_ips as $ip)
{
 	// [JAS]: Rewrite this to explode on the above into arrays and check as many elements as exist (2...4, etc)
	if(substr($ip,0,strlen($ip)) == substr($_SERVER['REMOTE_ADDR'],0,strlen($ip)))
 	{ $pass=true; break; }
}
if(!$pass) { echo "Cerberus [ERROR]: You are not authorized to use this tool. (Logged IP: " . $_SERVER['REMOTE_ADDR'] .")  If you're an authorized user, ask your admin to edit the script and add your IP."; exit(); }


$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";
$source = isset($_REQUEST["source"]) ? $_REQUEST["source"] : 0;
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : 0;
$question = isset($_REQUEST["question"]) ? $_REQUEST["question"] : "";
$limit = isset($_REQUEST["limit"]) ? $_REQUEST["limit"] : 10;

// [JAS]: Unescape variables (w/ magic quotes compensation)
$question = ini_get("magic_quotes_gpc") ? stripslashes(stripslashes($question)) : $question; // [JAS]: Why twice?  Ask Zend.

switch ($mode) {
	
	// [JAS]: Find matching resources from knowledge silos.
	case "fetch": {
		
		$cer_fnr = new cer_TrigramCerby();
		
		if(empty($question)) {
			return;
		}
		
		// [JAS]: Get back the F&R suggestions for the text we've been sent.
		$suggestions = $cer_fnr->ask($question, $limit, 0);
		
		// [JAS]: Send the answers, if any, as XML.
		echo "<cer_fnr_fetch>\n";
		
		echo sprintf("<question>\n".
					 "<![CDATA[%s]]>\n",
				$question
			);
		
		$fnr_suggested_kb_ids = array();
				
		if(!empty($suggestions)) {
			foreach($suggestions as $answer) {
				echo sprintf(
						 	"<answer source='%d' id='%d' relevance='%s' method='trigram'>\n".
						 		"<![CDATA[%s]]>\n".
						 	"</answer>\n",
						0,
						$answer->kb_id,
						sprintf("%0.1f",$answer->probability * 100) . "%",
						$answer->subject
					);
					
				$fnr_suggested_kb_ids[$answer->kb_id] = $answer->kb_id;
			}
		}
		
		$num_fnr_matches = count($suggestions);
		
		// [JAS]: If we didn't get back enough suggestions using F&R, try
		//	a keyword search using the most indexed words of the question.
		if($num_fnr_matches < $limit) {
			$search = new cer_searchIndex();
			
			$search->indexWords($question);
			$search->removeExcludedKeywords();
			$search->loadWordIDs(1);
			
			$sql = sprintf("SELECT k.kb_id, k.kb_category_id, kp.kb_problem_summary, count( si.kb_article_id )  AS matches ".
					"FROM  (`search_index_kb` si, `knowledgebase` k, `knowledgebase_problem` kp) ".
					"WHERE k.kb_id = kp.kb_id AND k.kb_id = si.kb_article_id ". // AND k.kb_public = 1
					"AND si.word_id IN ( %s )  ".
					"%s ".
					"GROUP BY si.kb_article_id ".
					"ORDER BY matches DESC ".
					"LIMIT 0,%d ",
						implode(',', array_values($search->wordarray)),
						!empty($fnr_suggested_kb_ids) 
							? sprintf("AND k.kb_id NOT IN ( %s )", implode(',', $fnr_suggested_kb_ids))
							: "",
						($limit - $num_fnr_matches)
				); 
			$res = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($res)) {
				$articles = array();
				
				while($row = $cerberus_db->fetch_row($res)) {
					$article = new cer_KnowledgebaseArticleListing();
					
					$article->article_id = sprintf("%05.0f",$row["kb_id"]);
					$article->article_summary = stripslashes($row["kb_problem_summary"]);
		
					$num_keywords = count($search->wordarray);
		
					$percent = ($row["matches"] / $num_keywords) * 100;
					$percent = sprintf("%0.1f%%",$percent);
					
					$article->article_matches = $percent;
					
					echo sprintf(
							 	"<answer source='%d' id='%d' relevance='%s' method='keyword'>\n".
							 		"<![CDATA[%s]]>\n".
							 	"</answer>\n",
							0,
							$article->article_id,
							$article->article_matches,
							$article->article_summary
						);
					
					$fnr_suggested_kb_ids[$article->article_id] = $article->article_id;
				}
			}
		}
		
		echo "</question>\n";
		
		echo "</cer_fnr_fetch>\n";
		
		break;
	}
	
	// [JAS]: Return detailed info about a specific resource match
	case "retrieve": {
		// [JAS]: \todo We need to be able to handle other knowledge silos later,
		//	outside Cerb KB forums.
		
		if(empty($id))
			return;
		
		echo "<cer_fnr_retrieve>\n";
			
		switch($source) {
			// KB Article
			default: {
				$null = null;
				$resource_content = null;
				
				$kb_article = new cer_KnowledgebaseArticle($null, $id);
				
				$resource_content .= "<html><head><style>".
					"body { color: 333333; font-size: 11px; font-family: Tahoma, Verdana, Helvetica, sans-serif;}\n".
					"h6 { color: 333333; font-size: 9px; font-family: Tahoma, Verdana, Helvetica, sans-serif;}\n".
					"</head><body>";
				
				// [JAS]: Build the KB Article Problem Section
				$resource_content .= "<h2>Problem:</h2>\n" .
					(($kb_article->article_problem_text_is_html) ? 
						$kb_article->article_problem_text
						:
						str_replace("\n","<br>",str_replace("\n\n","\n",str_replace("\r","\n",$kb_article->article_problem_text)))
						) . 
						"<br>\n";
				
				// [JAS]: Build the KB Article Solution Section
				$resource_content .= "<h2>Solution:</h2>\n" .
					(($kb_article->article_solution_text_is_html) ? 
						$kb_article->article_solution_text
						:
						str_replace("\n","<br>",str_replace("\n\n","\n",str_replace("\r","\n",$kb_article->article_solution_text)))
						) . 
						"<br>\n";
						
				$resource_content .= "".
						"<h6>Article ID: " . sprintf("%05.0f",$kb_article->article_id) . "</h6>\n".
						"<br>\n";

				$resource_content .= "</body></html>";
						
				echo sprintf("<resource source='%d' id='%d'>\n".
							 	"<![CDATA[%s]]>\n".
							 "</resource>\n",
							$source,
							$id,
							$resource_content
						);
				
				break;
			}
		}
		
		echo "</cer_fnr_retrieve>\n";
		
		break;
	}
	
}

?>