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
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_Trigram.class.php");

/** brief Trigram creation tools for emails
 *
 *	Classes and methods for creating trigrams from email tickets
 *
 *	\file cer_TrigramEmail.class.php
 *	\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
 *	\author Trent Ramseyer, WebGroup Media LLC. <trent@webgroupmedia.com>
 *	\date 2004
 *
 */

/** @addtogroup trigrams
 *
 * @{
 */
 
/** Class for indexing emails
 *
 *	This class is used for indexing the text of the first email on a ticket
 */
class cer_TrigramEmail extends cer_Trigram {
	
	var $thread_handler = null;
	
	/** Constructor
	 *
	 *	Constructer used to fill in the data in the structure.
	 */
	function cer_TrigramEmail() {
		$this->cer_Trigram();
		$this->thread_handler = new cer_ThreadContentHandler();
	}

	/** Index a single ticket
	 *
	 *	Index the first email in a ticket. Since we will match these trigrams with KB articles, we only do the first email.
	 *	This function will load the text from the first email of a thead, index the words, index the trigrams, it then save the trigram indexes to the ticket.
	 *	\param $ticket_id The ticket ID that you want to trigram index
	 *	\return true
	 */
	function indexSingleTicket($ticket_id="", $threads=null)
	{
		$cfg = CerConfiguration::getInstance();
		
		$sql = "SELECT `t`.`ticket_id`,`t`.`ticket_subject` as subject, th.thread_id ".
				"FROM (thread th, ticket t) ".
				"WHERE t.min_thread_id=th.thread_id AND t.ticket_id = $ticket_id ";
		$content = $this->db->query($sql);
		if($this->db->num_rows($content) && $text = $this->db->fetch_row($content)) {
			// get subject
			$string = stripslashes($text["subject"]);
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

			// append content
			if(empty($threads)) {
				$this->thread_handler->loadThreadContent($text["thread_id"]);
				$thread_content = &$this->thread_handler->threads[$text["thread_id"]]->content;
			}
			else {
				$thread = array_shift($threads);
				$thread_content = $thread->content;
			}
			$string .= $thread_content;

			$this->indexWords($string);
			$this->saveWords();
			$this->loadWordIDs(0, null);

			$this->wordsToTrigrams();
			$this->saveTrigrams();
			$this->loadTrigramIDs();
			$this->_saveToTicket($ticket_id);
		}
		
		return true;
	}

	/** Index a range of Tickets
	 *
	 *	\return true
	 */
	function reindexTickets($from=0,$to=0)
	{
		$sql = "SELECT t.ticket_id from ticket t where t.ticket_id > $from AND t.ticket_id <= $to ORDER BY t.ticket_id DESC";
		$rows = $this->db->query($sql);
		if($this->db->num_rows($rows) && $row = $this->db->fetch_row($rows)) {
			$ticket_id = $row["ticket_id"];
			$this->indexSingleTicket($ticket_id);
		}
		return true;
	}
	
	
	/** Removes the indexes for a specified ticket
	 *
	 *	Delete all assigned trigrams for a ticket
	 *	\param $ticket_id The ticket ID that you want to unindex
	 *	\return true
	 */
	function unindexTicket($ticket_id="")
	{
		$cfg = CerConfiguration::getInstance();
		if(0<$ticket_id) {
			$sql = "DELETE FROM `trigram_to_ticket` WHERE `ticket_id` = $ticket_id ";
			$this->db->query($sql);
		}
		return true;
	}	
	
	
	/** PRIVATE - Saves trigram IDs to a ticket ID
	 *
	 *	Save the trigrams in the internal array to the database.
	 *	\param $ticket_id The ticket ID you want to save the indexes to
	 *	\return Nothing
	 *  \see indexSingleTicket
	 */
	function _saveToTicket($ticket_id=0) {
		// now we need to save the trigrams to a table that links them to the ticket.
		if(0 != $ticket_id && 0<count($this->trigram_ids)) {
			$values="";
			foreach ($this->trigram_ids as $tgid) {
				if(""==$values) {
					$values .= "(" . $tgid . "," . $ticket_id . ")";
				}
				else {
					$values .= ",(" . $tgid . "," . $ticket_id . ")";
				}
			}
			$sql = "INSERT IGNORE INTO `trigram_to_ticket` (trigram_id,ticket_id) VALUES " . $values;	
			// save the links
			mysql_query($sql) or die (mysql_error());
		}
	}
};

/** @} */

?>