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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_Trigram.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");

/** brief Trigram creation tools for KB Problems
 *
 *	Classes and methods for creating trigrams from KB Article problem text
 *
 *	\file cer_TrigramKB.class.php
 *	\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
 *	\author Trent Ramseyer, WebGroup Media LLC. <trent@webgroupmedia.com>
 *	\date 2004
 *
 */

/** @addtogroup trigrams
 *
 * @{
 */
 
/** Class for indexing KB problems
 *
 *	This class is used for indexing the problem text of knowledgebase articles.
 */
class cer_TrigramKB extends cer_Trigram {
	
	/** Constructor
	 *
	 *	Constructer used to fill in the data in the structure.
	 */
	function cer_TrigramKB() {
		$this->cer_Trigram(cer_Database::getInstance());
	}
	
	/** Index a single kb article thread
	 *
	 *	Index the problem text of a kb article. Since we will match these trigrams with emails that open tickets.
	 *	This function will load the problem text from the KB article, index the words, index the trigrams, it then save the trigram indexes to the article.
	 *	\param $kbid The knowledgebase article ID we are going to index
	 *	\return true
	 */	
	function indexSingleArticle($kbid) {
		$sql = "SELECT `kb_problem_summary`,`kb_problem_text`, `kb_problem_text_is_html` FROM `knowledgebase_problem` WHERE `kb_id`=$kbid";
		$content = $this->db->query($sql);
		
		if($this->db->num_rows($content) && $text = $this->db->fetch_row($content)) {
			
			// get subject
			$string = stripslashes($text["kb_problem_summary"]);
			$string = trim($string);
			$lastchr = strlen($string)-1;

			// check for sentence terminator
			if(0<strlen($string) 
				&& '.' != $string[$lastchr] 
				&& '!' != $string[$lastchr] 
				&& '?' != $string[$lastchr]
				){
					// add terminator if one isn't there
					$string .= ". ";
			}
			else {
				// make sure the subject has a trailaing space
				// so it doesn't concat improperly with the body
				$string .= " ";
			}			
			
			$striphtml = new cer_StripHTML();
			
			// get the text
			$prob_string = $text["kb_problem_text"];
			
			if("1"==$text["kb_problem_text_is_html"]) {
				// remove the HTML
				$prob_string = $striphtml->strip_html($prob_string);
			}
			
			$string .= $prob_string;
			
			// standard trigram cleanups/indexing
			$this->indexWords($string);
			$this->saveWords();
			$this->loadWordIDs();
			$this->wordsToTrigrams();
			$this->saveTrigrams();
			$this->loadTrigramIDs();

			// save the trigrams to this KB article
			$this->_saveToKB($kbid);
		}
	
		return true;		
	}

	/** PRIVATE - Saves trigram IDs to a KB ID
	 *
	 *	Save the trigrams in the internal array to the database.
	 *	\param $kb_id The knowledgebase article ID you want to save the indexes to
	 *	\return Nothing
	 *  \see indexSingleArticle
	 */
	function _saveToKB($kb_id=0) {
		// now we need to save the trigrams to a table that links them to the knowledgebase.
		if(0 != $kb_id && 0<count($this->trigram_ids)) {
			$values="";
			foreach ($this->trigram_ids as $tgid) {
				if(""==$values) {
					$values .= "(" . $tgid . "," . $kb_id . ", 0, 0)";
				}
				else {
					$values .= ",(" . $tgid . "," . $kb_id . ", 0, 0)";
				}
			}
			$sql = "INSERT IGNORE INTO `trigram_to_kb` (trigram_id,knowledgebase_id,good,bad) VALUES " . $values;	
		
			// save the links
			mysql_query($sql) or die (mysql_error());
		}
	}
	
	/** Delete trigrams from KB article.
	 *
	 *	Deletes the trigrams associated with a knowledgebase article.
	 *	\param $kb_id The KB article ID you want to delete the trigrams from
	 *	\return Nothing
	 *  \see indexSingleArticle
	 */
	function deleteFromArticle($kb_id=0) {
		if(0 != $kb_id && 0<strlen($kb_id)) {
			$sql = "DELETE FROM `trigram_to_kb` WHERE `knowledgebase_id`=$kb_id";
			$this->db->query($sql);
		}
	}

	/** Index a range of Articles
	 *
	 *	\return true
	 */
	function reindexArticles($from=0,$count=0)
	{
		$to = $from+$count;
		$sql = "SELECT kb.kb_id from knowledgebase kb where kb.kb_id >= $from AND kb.kb_id < $to ORDER BY kb.kb_id ASC";
		$rows = $this->db->query($sql);
		while($this->db->num_rows($rows) && $row = $this->db->fetch_row($rows)) {
			$kb_id = $row["kb_id"];
			$this->indexSingleArticle($kb_id);
		}
		return true;
	}
	
	
};

/** @} */

?>