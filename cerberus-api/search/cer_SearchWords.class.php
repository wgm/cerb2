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

require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");

define("NOT_SUBJECT_WORD",0);
define("IS_SUBJECT_WORD",1);

define("SEARCH_WORD_UNSET",-1);

if(!defined("SEARCH_MIN_WORD")) define("SEARCH_MIN_WORD",3);
if(!defined("SEARCH_MAX_WORD")) define("SEARCH_MAX_WORD",25);

/*!
\file cer_searchIndex.class.php
\brief search word indexing

Classes and methods for handling searching and indexing of words

\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
\date 2004
*/

class cer_CachedWord {
//	var $hits = 0;
	var $word_id = 0;
//	var $word = "";
	
	function cer_CachedWord($id, $word) {
		$this->word_id = $id;
//		$this->word = $word;
	}
	
//	function hit() {
//		$this->hits++;
//	}
};

class cer_WordCacheHandler {
	var $cache = array();
	
	function cer_WordCacheHandler() {
		
	}
	
	function isCached($word) {
		// check if we have the word
		if(isset($this->cache[$word])) {
			
			// increment word hits
//			$this->cache[$word]->hits++;
			
			// return the cached word id
			return $this->cache[$word]->word_id;
		}
		
		// return failure
		return 0;
	}
	
	function addCachedWords($wordarray) {
		foreach($wordarray as $word => $word_id) {
		 	if(!$this->isCached($word)) {
		 		$this->cache[$word] = new cer_CachedWord($word_id, $word);
		 	}
		 }
	}
/*	
	function saveCache() {
		$best_hits = array();

		// sort it
		$best_hits = cer_PointerSort::pointerSortCollection($this->cache, "hits");
		$best_hits = array_reverse($best_hits);
		$best_hits = array_slice($best_hits, 0, 50);
		
		// save to session if exists
		return $best_hits;
	}
	
	function loadCache($word_cache) {
		if(!empty($word_cache) && empty($this->cache)) {
			$tmpCache = @$word_cache;
			if(!empty($tmpCache)) {
				foreach($tmpCache as $wc) {
					$this->cache[$wc->word] = $wc;	
				}
echo "Loaded " . count($this->cache) . " cached words.<br>";				
			}
		}
	}
*/
};

/** @addtogroup search Search
 *
 * Search Word Indexing and Searching Functionality
 *
 * @{
 */
class cer_searchWords {
	/** Database Object */
	var $db;
	
	/** Array of unique words */
	var $wordarray = array();
	
	/** Word cache */
	var $wordcache = null;
	
	/** SQL Safe copy of $wordarray */
	var $sql_words = array();
	
	/** Constructor
	 *
	 *	Default constructor, must provide DB connection to it.
	 *  \param $db Database connection object
	 */
	function cer_searchWords() {
		$this->db = cer_Database::getInstance();
		if(empty($this->wordcache)) {
			$this->wordcache = new cer_WordCacheHandler();
		}
	}

	
	/** Save indexed words to DB
	 *
	 *	After indexing words to the internal array using indexWords(), calling this function will save them to the Database
	 *	\return Nothing
	 *	\see indexWords
	 */
	function saveWords() {
		if(is_array($this->wordarray) && count($this->wordarray)) {
			if(!empty($this->wordarray)) {
				
				// if we didn't import the words, build the SQL safe word array
				$this->sql_words = array();
				foreach($this->wordarray as $word => $word_id) {
					if($cached_id=$this->wordcache->isCached($word)) {
						$this->wordarray[$word] = $cached_id;
					}
					else {
						// if the word is unset, insert it
						$this->sql_words[] = mysql_escape_string($word);
					}
				}
				
//				echo "Missed " . count($this->sql_words) . " of " . count($this->wordarray) . "<br>";
				// run the query for the words
				if(!empty($this->sql_words)) {
					$word_list = "('" . implode("'),('", $this->sql_words) . "')";
					$sql = "INSERT IGNORE INTO search_words(word) VALUES " . $word_list;
					$this->db->query($sql);
				}
			}
		}
	}

	/** Load word IDs from Database
	 *
	 *	Load all of the word IDs from database for the words in the internal array. This function is normally called just after saveWords().
	 *	\returns Array of IDs, the key in the array is the actual words
	 *	\see saveWords
	 */
	function loadWordIDs($is_search=0) {
		if(!$is_search) {
			// if we called loadWordIDs without calling saveWords first we have to populate the sql_words array
			if(empty($this->sql_words) && !empty($this->wordarray)) {
				foreach($this->wordarray as $word => $word_id) {
					$this->sql_words[] = mysql_escape_string($word);
				}				
			}
			if(!empty($this->sql_words)) {
				$sql = "SELECT w.word_id,w.word FROM search_words w WHERE w.word IN ('" .
					implode("','",$this->sql_words) .
					"')";
				$word_res = $this->db->query($sql);
	
				if($this->db->num_rows($word_res)) {
					while($word_row = $this->db->fetch_row($word_res))
					{
						$this->wordarray[$word_row["word"]] = $word_row["word_id"];
					}
				}
				
				$this->wordcache->addCachedWords($this->wordarray);
			}
		}
		else {
			foreach($this->wordarray as $word => $val)
			{
				$word = str_replace("*","%",$word);
				$sql = "SELECT w.word_id,w.word FROM search_words w WHERE w.word LIKE '$word'";
				$result = $this->db->query($sql);

				if($this->db->num_rows($result))
				{
					while($word_row = $this->db->fetch_row($result))
					{
						$this->wordarray[$word_row["word"]] = $word_row["word_id"];
					}
				}
			}
			
			// remove all unset words
			foreach($this->wordarray as $word => $value) {
				if(1>$value) {
					unset($this->wordarray[$word]);
				}
			}			
		}
		return $this->wordarray;
	}

};
	
?>