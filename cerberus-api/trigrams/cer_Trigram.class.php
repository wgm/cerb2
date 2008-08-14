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

/*!
\file cer_Trigram.class.php
\brief Trigram creation tools

Classes and methods for handling trigram related functionality

\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
\author Trent Ramseyer, WebGroup Media LLC. <trent@webgroupmedia.com>
\date 2004
*/

/** @addtogroup trigrams Trigrams
 *
 * Trigram Functionality
 *
 * @{
 */
if(!defined("TRIGRAM_MAX_WORD")) define("TRIGRAM_MAX_WORD",45);

/** Trigram structure to hold trigrams */
class trigram {
	var $trigram=null;
	
	/** Constructor
	 *
	 *	Constructer used to fill in the data in the structure.
	 */
	function trigram($aa=NULL, $bb=NULL, $cc=NULL) {
		$this->trigram = sprintf("%010d%010d%010d", $aa, $bb, $cc);
	}
	
}

/** Base Trigram class
 *
 * Base Trigram class that has trigram only specific functions. There are be no email/kb functions in this class.
 *
 */
class cer_Trigram extends cer_SearchWords {
		
	/** Words in sequential order as they would have been in the text */
	var $wordarray_tg = array();
	
	/** Array of trigram objects 
	 *
	 *	/see trigram
	 */
	var $trigrams = array();
	
	/** IDs (from database) for the trigrams in the $trigrams array */
	var $trigram_ids = array();
	
	/** Constructor
	 *
	 *	Default constructor, must provide DB connection to it.
	 *  \param $db Database connection object
	 */
	function cer_Trigram() {
		$this->cer_SearchWords();
	}

	/** Cleans up the punctuation in an email.
	 * 
	 *	Will condense punctuation at the beginning and ends of words, will also remove punctuation only words. This reduces the number of unique words when creating trigrams. For example, 'HELP!!' would be come 'HELP!' causing it to match 'HELP!'.
	 *	\param $string The string that you want to clean up
	 *	\return String that has been cleaned of punctuation
	 */
	function cleanPunctuation($words) {
		$search = array();
		$replace = array();

		foreach($words as $key => $word) {
			if(45<strlen($word)) {
				$words[$key] = 	substr($word, 0, 45);
			}
		}

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

		foreach ($chars as $char) {
			$search[] = "/(\\$char)+$/";
			$search[] = "/^(\\$char)+/";
			$replace[]= "\\1";
			$replace[]= "\\1";
		}

		// condense punctuation at the beginning and end of words, many become one
		$words = preg_replace($search, $replace, $words);		
		
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
		$replace = "\\3";

		// where we strip the stuff
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
	 *	\param $string The text you would like to index
	 *	\return The array of words as they would have been in the email.
	 *	\see cleanPunctuation
	 */
	function indexWords($string=NULL) {
		$this->wordarray = array();
		$this->sql_words = array();
		if(NULL!=$string) {
			$strip_html = new cer_StripHTML();
			
			stripslashes($string);
			$string = strtolower($string);
			$string = $strip_html->strip_html($string);
			$string = cer_Whitespace::mergeWhitespace($string);
			$this->wordarray_tg = explode(" ",$string); // split string on single spaces
			$this->wordarray_tg = $this->cleanPunctuation($this->wordarray_tg);
			
			// make the words safe for MySQL as we now index words like "don't"
			foreach($this->wordarray_tg as $id => $word) {
				$word = trim($word);
				if(empty($word)) {
					unset($this->wordarray_tg[$id]);
				}
				else {
					$this->wordarray[$word] = SEARCH_WORD_UNSET;
				}
			}
		}
		return $this->wordarray_tg;
	}
	/** Convert word array to trigram array
	 *
	 *	Convert the wordarray_tg array into trigram objects which get put into the internal trigram array. This function will only make trigrams out of the words inside the sentence. Trigrams will not span sentence delimiters "!?.". This function is usually followed by the saveTrigrams() function.
	 *	\return The array of trigram objects
	 *	\sa saveTrigrams
	 */
	function wordsToTrigrams()
	{
		// start building the trigrams
		$a = 0;
		$b = 0;
		$c = 0;
		
		$this->trigrams = array();
		
		$lastword = "";
		// loop through the words building the trigrams
		foreach($this->wordarray_tg as $word) {
			// rotate word ids
			$a = $b;
			$b = $c;
			
			// find next word id
			// \todo [JAS]: BUG HERE (notice error if no index)
			$c = @$this->wordarray[$word];

			// check for end of sentence, this should reduce the number of trigrams
			// and keep them more focused on the subjects in the sentences
			// if the word begins with a period, it's going to be the end of a sentence
			if(0<strlen($lastword) && 	(
				'.' == $lastword[strlen($lastword)-1] 
				|| '!' == $lastword[strlen($lastword)-1] 
				|| '?' == $lastword[strlen($lastword)-1]) 
				){
				$a = 0;
				$b = 0;
			}
			else {
				// save trigram
				if(0 != $a) {
					$unsetcount = 0;
					if(SEARCH_WORD_UNSET==$a) {
						$unsetcount++;
						$a=0;
					}
					if(SEARCH_WORD_UNSET==$b) { 
						$unsetcount++;
						$b=0;
					}
					if(SEARCH_WORD_UNSET==$c) {
						$unsetcount++;
						$c=0;
					}
					if(2>$unsetcount) {
						$tg = new trigram($a, $b, $c);
						array_push($this->trigrams, $tg);
					}
				}
			}
			// save lastword so we can check it next time through
			$lastword=$word;
		}
		
		return $this->trigrams;
	}

	/** Save trigram indexes to databsae
	 *
	 *	Save the trigram objects in the internal trigrams array to the database.
	 *	\return Nothing
	 *	\see wordsToTrigrams loadTrigramIDs
	 */
	function saveTrigrams()
	{
		$values = array();

		if(0<count($this->trigrams)) {
			// build the SQL
			
			foreach($this->trigrams as $key => $trigram) {
				// [JAS]: Don't save a trigram unless we've actually linked to something tangible.
				if(!empty($trigram->trigram)) {
					$values[] = "(0, '" . $trigram->trigram . "')";
				}
			}

			if(!empty($values)) {
				$sql = "INSERT IGNORE INTO `trigram` (trigram_id,trigram) VALUES " . implode(",",$values);
			
				// save the trigrams
				$this->db->query($sql);
			}
			unset($values);
		}
	}

	/** Load the IDs of the trigrams
	 *
	 *	Load the unique IDs of the trigrams we have in the internal array from the Database. This function is used when you need to link trigrams to a KB article or Email. It usually follows the saveTrigrams() function.
	 *	\return Nothing
	 *  \see saveTrigrams wordsToTrigrams
	 */
	function loadTrigramIDs()
	{
		$this->trigram_ids = array();
		// now we need to save the trigrams to a table that links them to the thread.
		if(0<count($this->trigrams)) {
			// get the ID of the trigrams
			$values=array();

			foreach($this->trigrams as $key => $trigram) {
				$values[]=$trigram->trigram;
			} // foreach

			arsort($values);
			
			if(null!=$trigram->trigram) {
				$sql = "SELECT `trigram_id` FROM `trigram` WHERE `trigram`.`trigram` IN ('" . implode("','", $values) . "')";
				$res = $this->db->query($sql);
				
				while($this->db->num_rows($res) && $row=$this->db->fetch_row($res)) {
					array_push(	$this->trigram_ids, $row["trigram_id"]);
				}
			}
			unset($values);
		} // if
	}
	
};

/** @} */

?>