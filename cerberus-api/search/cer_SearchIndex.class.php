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
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchWords.class.php");

$allow_numbers = (isset($cfg->settings) && $cfg->settings["search_index_numbers"]) ? 1 : 0;

if(!defined("SEARCH_MIN_WORD")) define("SEARCH_MIN_WORD",3);
if(!defined("SEARCH_MAX_WORD")) define("SEARCH_MAX_WORD",25);
if(!defined("SEARCH_ALLOW_NUMBERS")) define("SEARCH_ALLOW_NUMBERS", $allow_numbers);

/*!
\file cer_searchIndex.class.php
\brief search word indexing

Classes and methods for handling searching and indexing of words

\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
\date 2004
*/

/** @addtogroup search Search
 *
 * Search Word Indexing and Searching Functionality
 *
 * @{
 */
class cer_searchIndex extends cer_searchWords {

	/** Array of words not to link to the tickets
	 */
	var $excludearray = array();
		
	/** Constructor
	 *
	 *	Default constructor, must provide DB connection to it.
	 *  \param $db Database connection object
	 */
	function cer_SearchIndex() {
		$this->cer_searchWords();
	}
	
	/** Cleans up the punctuation in an email.
	 * 
	 *	Will remove punctuation at the beginning and ends of words, will also remove punctuation only words. This reduces the number of unique words. For example, 'HELP!!' would be come 'HELP' causing it to match 'HELP'.
	 *	\param $string The string that you want to clean up
	 *	\return String that has been cleaned of punctuation
	 */
	function cleanPunctuation($words,$is_search=0) {
		$chars = array();
		
		foreach($words as $key => $word) {
			if(45<strlen($word)) {
				$words[$key] = 	substr($word, 0, 45);
			}
		}

		if(!$is_search) {
			$chars = array(
				"$",
				".",
				"!",
				"?",
				"@",
				",",
				"#",
				"%",
				"^",
				"&",
				"*",
				"(",
				")",
				"_",
				"+",
				"=",
				"{",
				"}",
				"[",
				"]",
				"\\",
				"|",
				";",
				":",
				"\"",
				"<",
				">",
				"/",
				"~",
				"-"		
			);

			// add regexp to remove punctuation only words, #$%^!
			$search = "/(^(\\" . implode("|\\", $chars) . ")+)+$/";
			$replace = "";

			// where we strip the stuff
			$words = preg_replace($search, $replace, $words);		
		}

		// if it's a strip for search words we do not stip all punctuation words
		if($is_search) {
			$chars = array(
				"$",
				".",
				"!",
				"?",
				"@",
				",",
				"#",
				"^",
				"&",
				"(",
				")",
				"_",
				"+",
				"=",
				"{",
				"}",
				"[",
				"]",
				"\\",
				"|",
				";",
				":",
				"\"",
				"<",
				">",
				"/",
				"~",
				"-",
				"'"	
			);
		}
		else {
			$chars = array(
				"$",
				".",
				"!",
				"?",
				"@",
				",",
				"#",
				"%",
				"^",
				"&",
				"*",
				"(",
				")",
				"_",
				"+",
				"=",
				"{",
				"}",
				"[",
				"]",
				"\\",
				"|",
				";",
				":",
				"\"",
				"<",
				">",
				"/",
				"~",
				"-",
				"'"	
			);
		}			
		
		$search = array();
		$replace = array();		
		$search[] = "/([" . implode("\\", $chars) . "]+)$/";
		$search[] = "/^([" . implode("\\", $chars) . "]+)/";
		$replace[]= "\\2";
		$replace[]= "\\2";

		// to keep memory usage low we check and make sure we're not doing
		// regexp on HUGE emails. limit to 5000 words
		if(5000<count($words)) {
			$words = array_splice($words, 0, 5000);
		}
		
		// strip punctuation at the beginning and end of words
		$words = preg_replace($search, $replace, $words);
		
		// remove zero length words in the array
		foreach($words as $key => $word) {
			if(0==strlen($word)) {
				unset($words[$key]);
			}
		}
		
		return $words;
	}


	/** Indexes words into an array
	 *
	 *	Splits the text up into an array then indexes them. You will normally want to use the cleanPunctuation() function before calling this function on text.
	 *	\param $string (string) The text you would like to index
	 *  \param $numbers (int) Set to non-zero to index numbers also
	 *	\return The array of words as they would have been in the email.
	 *	\see cleanPunctuation
	 */
	function indexWords($string=NULL,$numbers=0,$is_search=0) {
		$words = array();
		$this->wordarray = array();
		$this->sql_words = array();
		if(NULL!=$string) {
			$strip_html = new cer_StripHTML();
			
			stripslashes($string);
			$string = strtolower($string);
			$string = $strip_html->strip_html($string);
			$string = cer_Whitespace::mergeWhitespace($string);
			$words = explode(" ",$string); // split string on single spaces
			
			$words = $this->cleanPunctuation($words,$is_search);
			
			// make the words safe for MySQL as we now index words like "don't"
			foreach($words as $id => $word) {
				$word = trim($word);
				
				// [JAS]: If we're doing a search and the word contains wildcard tokens
				if ($is_search && (strstr($word, "*") !== false || strstr($word, "%") !== false))  {
					$this->loadWildcardWordMatches($word);
				}
				// [JAS]: \bug This was probably where numeric search terms
				//	were getting blitzed.  This should read in from $cfg, but
				//	not break other tools if $cfg->settings doesn't exist.
				elseif(!$this->isAcceptableWord($word)) {
					unset($words[$id]);
					continue;
				}							
				// [JAS]: Otherwise an acceptable whole word
				else {
					$this->wordarray[$word] = SEARCH_WORD_UNSET;
				}
			}
		}
		return $words;
	}
	
	function isAcceptableWord($word) {
		$word = trim($word);
		$length = strlen($word);
				
		if(!SEARCH_ALLOW_NUMBERS && is_numeric($word)) {
			return false;
		}							
		if(SEARCH_MIN_WORD > $length || SEARCH_MAX_WORD < $length) {
			return false;
		}
		
		return true;
	}
	
	function loadWildcardWordMatches($wild_word) {
		
		$find = str_replace("*","%",$wild_word);
		
		$sql = sprintf("SELECT w.word_id, w.word FROM search_words w WHERE w.word LIKE '%s'",
				$find
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$word = stripslashes($row["word"]);
				
				if(!$this->isAcceptableWord($word)) {
					continue;
				}
				else {
					$this->wordarray[$word] = SEARCH_WORD_UNSET;
				}
			}
		}
		
		return;
	}
	
	/** Removes excluded words from the internal wordarray
	 *	
	 *	This function will trim down the wordarray by removing words that we do not want to index against threads.
	 *	\return Nothing
	 */
	function removeExcludedKeywords()
	{
		// [JAS]: If we haven't populated the exclude array yet, read in the values now.
		if(empty($this->excludearray))
		{
			$sql = "SELECT exclude_word FROM search_index_exclude";
			$result = $this->db->query($sql);
			if($this->db->num_rows($result))
			{
				while($exclude_word = $this->db->fetch_row($result))
				{
					array_push($this->excludearray,stripslashes($exclude_word["exclude_word"]));
				}
			}
		}
		
		// [JAS]: Remove the excluded words from the index
		foreach($this->excludearray as $word)
		{
			unset($this->wordarray[$word]);
		}
	}	
};
 
/** @} */

?>