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

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");

/** brief SearchIndex creation tools for emails
 *
 *	Classes and methods for search indexing email
 *
 *	\file cer_SearchIndexEmail.class.php
 *	\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
 *	\date 2004
 *
 */

/** @addtogroup search
 *
 * @{
 */

/** Class for indexing emails
 *
 *	This class is used for indexing the text in email threads.
 */
class cer_SearchIndexEmail extends cer_SearchIndex {

	var $thread_handler = null;

	/** Constructor
	 *
	 *	Constructer used to fill in the data in the structure.
	 */
	function cer_SearchIndexEmail() {
		$this->cer_searchIndex();
		$this->thread_handler = new cer_ThreadContentHandler();
	}

	/** Index a single ticket
	 *
	 *	Index the first email in a ticket. This will be called after each bit of content is added to the ticket so we only index the last bit of content.
	 *	This function will load the text from the last email of a ticket, index the words, it then save the word indexes to the ticket.
	 *	\param $ticket_id The ticket ID that you want to index
	 *	\param $all_threads Set to 1 if you want to index all the threads for the ticket, 0 if you want to only index the last one
	 *	\return true
	 */
	function indexSingleTicket($ticket_id=0, $all_threads=0, $threads=null)
	{
		$cfg = CerConfiguration::getInstance();
		$string = "";

		if($all_threads) {
			if(empty($threads)) {
				$this->thread_handler->loadTicketContentDB($ticket_id);
				$threads = $this->thread_handler->threads;
			}

			$firstthread = array_shift($threads);

			foreach($threads as $key => $thread) {
				$string = $thread->content;
				if(strlen($string) < 51200) {
					$this->indexWords($string,$cfg->settings["search_index_numbers"]);
					$this->removeExcludedKeywords();
					$this->saveWords();
					$this->loadWordIDs(0);
					$this->_saveToThread($ticket_id,0,0);
				}
				else {
					while(strlen($string) > 0) {
						if(strstr($string, " ") && strlen($string) > 51200) {
							$first_space_after_50k = strpos($string, " ", 51200);
							if($first_space_after_50k > 61440) {
								$first_space_after_50k = 51200;
							}
						}
						else {
							$first_space_after_50k = 51200;
						}
						$this->indexWords(substr($string, 0, $first_space_after_50k),$cfg->settings["search_index_numbers"]);
						$this->removeExcludedKeywords();
						$this->saveWords();
						$this->loadWordIDs(0);
						$this->_saveToThread($ticket_id,0,0);
						$string = substr($string, $first_space_after_50k+1);
					}
				}
			}

			$first_thread_length = strlen($firstthread->content);
			if(0<$first_thread_length) {
				if($first_thread_length < 51200) {
					$this->indexWords($firstthread->content,$cfg->settings["search_index_numbers"]);
					$this->removeExcludedKeywords();
					$this->saveWords();
					$this->loadWordIDs(0);
					$this->_saveToThread($ticket_id,0,1);
				}
				else {
					$string = $firstthread->content;
					while(strlen($string) > 0) {
						if(strstr($string, " ") && strlen($string) > 51200) {
							$first_space_after_50k = strpos($string, " ", 51200);
							if($first_space_after_50k > 61440) {
								$first_space_after_50k = 51200;
							}
						}
						else {
							$first_space_after_50k = 51200;
						}
						$this->indexWords(substr($string, 0, $first_space_after_50k),$cfg->settings["search_index_numbers"]);
						$this->removeExcludedKeywords();
						$this->saveWords();
						$this->loadWordIDs(0);
						$this->_saveToThread($ticket_id,0,1);
						$string = substr($string, $first_space_after_50k+1);
					}
				}
			}

			/*
			Disabling indexing of all threads at once, and replacing with a method which breaks up in chunks of 50kb

			// index the not-first threads first so we can 'replace into' later
			//			if(!empty($string)) {
			//				$this->indexWords($string,$cfg->settings["search_index_numbers"]);
			//				$this->removeExcludedKeywords();
			//				$this->saveWords();
			//				$this->loadWordIDs(0);
			//				$this->_saveToThread($ticket_id,0,0);
			//			}
			*/

			/* Disabling indexing of the first thread all at once if over 50k in size
			// index the first thread by itself
			// this should be done last so the 'replace into' will mark the search index properly
			//			if(0<strlen($firstthread->content)) {
			//				$this->indexWords($firstthread->content,$cfg->settings["search_index_numbers"]);
			//				$this->removeExcludedKeywords();
			//				$this->saveWords();
			//				$this->loadWordIDs(0);
			//				$this->_saveToThread($ticket_id,0,1);
			//			}
			*/

		}
		else { // only get the last thread_id on the ticket.

		// is the first thread?
		$is_firstthread = 0;

		$th_ids=array();
		$sql = sprintf("SELECT th.thread_id, t.min_thread_id ".
		"FROM ticket t, thread th ".
		"WHERE th.thread_id = t.max_thread_id ".
		"AND t.ticket_id = %d " .
		"ORDER BY t.ticket_id, th.thread_id ",
		$ticket_id
		);
		$res = $this->db->query($sql);

		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$th_ids[] = $row["thread_id"];
				if($row["thread_id"] == $row["min_thread_id"]) {
					$is_firstthread=1;
				}
			}
		}

		$this->thread_handler->loadThreadContent($th_ids);
		$threads = &$this->thread_handler->threads;

		foreach($threads as $key => $thread) {
			$string .= $thread->content . " ";
		}

		// index the not-first threads first so we can 'replace into' later
		if(!empty($string)) {
			$this->indexWords($thread->content,$cfg->settings["search_index_numbers"]);
			$this->removeExcludedKeywords();
			$this->saveWords();
			$this->loadWordIDs(0);
			$this->_saveToThread($ticket_id,0,$is_firstthread);
		}
		}

		return true;
	}

	/** Index a single ticket's subject line
	 *
	 *	\param $ticket_id The ticket ID that you want to index
	 *	\return true
	 */
	function indexSingleTicketSubject($ticket_id=0)
	{
		$cfg = CerConfiguration::getInstance();

		$sql = "SELECT t.ticket_id,t.ticket_subject ".
		"FROM ticket t ".
		"WHERE t.ticket_id = $ticket_id ";
		$content = $this->db->query($sql);

		if($this->db->num_rows($content) && $text = $this->db->fetch_row($content)) {
			$string = stripslashes($text["ticket_subject"]);
			$this->indexWords($string,$cfg->settings["search_index_numbers"]);
			$this->removeExcludedKeywords();
			$this->saveWords();
			$this->loadWordIDs(0);
			$this->_saveToThread($text["ticket_id"],1,1);
		}
		return true;
	}



	function loadTicketWordIDs($ticket_id=0,$with_words=0) {
		$this->wordarray = null;
		if(empty($with_words)) {
			$sql = sprintf("SELECT `si`.`word_id` ".
			"FROM `search_index` si ".
			"WHERE `si`.`in_first_thread`=1 AND `si`.`ticket_id`=%d",
			$ticket_id);
			$result = $this->db->query($sql);

			if($this->db->num_rows($result)) {
				while(null!=($word_row = $this->db->fetch_row($result))) {
					$this->wordarray[$word_row["word_id"]] = $word_row["word_id"];
				}
			}
		}
		else {
			$sql = sprintf("SELECT `si`.`word_id`, `sw`.`word` ".
			"FROM `search_index` si, `search_words` sw ".
			"WHERE `si`.`word_id`=`sw`.`word_id` AND `si`.`in_first_thread`=1 AND `si`.`ticket_id`=%d",
			$ticket_id);
			$result = $this->db->query($sql);

			if($this->db->num_rows($result)) {
				while(null!=($word_row = $this->db->fetch_row($result))) {
					$this->wordarray[$word_row["word"]] = $word_row["word_id"];
				}
			}
		}

		return true;
	}


	/** PRIVATE - Saves IDs to a ticket ID
	 *
	 *	Save the IDs in the internal array to the database.
	 *	\param $ticket_id The ticket ID you want to save the indexes to
	 *	\return Nothing
	 *  \see indexSingleTicket
	 */
	function _saveToThread($ticket_id=0, $in_subject=0, $in_first_thread=0) {
		if($ticket_id && is_array($this->wordarray) && 0<count($this->wordarray)) {
			$word_ids = "(" .
			implode(",$ticket_id,$in_subject,0),(",array_values($this->wordarray)) .
			",$ticket_id,$in_subject,0)";
			$sql = "INSERT IGNORE INTO search_index (word_id,ticket_id,in_subject,in_first_thread) VALUES $word_ids";
			$this->db->query($sql);

			if($in_first_thread) {
				$sql = sprintf("UPDATE `search_index` SET `in_first_thread`=1 WHERE `word_id` IN (%s) AND `ticket_id`=%d",
				implode(",",$this->wordarray),
				$ticket_id
				);
				$this->db->query($sql);
			}
		}
	}

};

/** @} */

?>