<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: cer_BayesianAntiSpam.class.php
|
| Purpose: Bayesian Theorem and Probability Objects for Spam Handling,
|	Trigrams, etc.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

// ALTER TABLE `ticket` ADD `ticket_spam_probability` FLOAT UNSIGNED NOT NULL ;
// ALTER TABLE `ticket` ADD INDEX ( `ticket_spam_probability` ) 

require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_Bayesian.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");


class CER_BAYESIAN_ANTISPAM_WORD
{
	var $word = null;
	var $word_id = -1;
	var $in_spam = 0;
	var $in_nonspam = 0;
	var $probability = 0.40;
	var $interest_rating = 0.0;
	
	function CER_BAYESIAN_ANTISPAM_WORD($word,$id)
	{
		$this->word = $word;
		$this->word_id = $id;
	}
}

class cer_BayesianAntiSpam extends cer_Bayesian 
{
	var $db = null;
	var $thread_handler = null;
	var $search = null;
	var $num_spam = 0;
	var $num_nonspam = 0;
	var $word_probs = array();
	
	function cer_BayesianAntiSpam()
	{
		$this->db = cer_Database::getInstance();
		$this->_load_spam_stats();
	}

	function _load_spam_stats()
	{
		$sql = "SELECT num_spam, num_nonspam FROM spam_bayes_stats LIMIT 0,1";
		$res = $this->db->query($sql);
		
		if($row = $this->db->grab_first_row($res))
		{
			$this->num_spam = $row["num_spam"];
			$this->num_nonspam = $row["num_nonspam"];
		}
		else
		{
			$sql = "REPLACE INTO spam_bayes_stats VALUES (0,0)";
			$this->db->query($sql);
		}
	}
	
	function _analyze_raw_email($ticket_id,$with_words=0)
	{
		$this->search = new cer_SearchIndexEmail();
		
		// [BGH]: Create word index just like the search index does
		$this->search->loadTicketWordIDs($ticket_id,$with_words);
		$this->search->removeExcludedKeywords();
		$words = $this->search->wordarray;

		// [BGH]: Create word index just like the search index does
		$this->search->loadTicketWordIDs($ticket_id,$with_words);
		$this->search->removeExcludedKeywords();
		$words = $this->search->wordarray;

		return($this->_generate_word_probabilities($words));
	}
	
	function _generate_word_probabilities($words)
	{
		if(empty($words)) return array();
		
		// [JAS]: Give a spam word probability to each word using the bayesian index
		$word_probs = $this->_assign_probabilities($words);
		
		// [JAS]: Sort words based on their 'interesting' rating.  (e.g., deviation from median probability)
		$word_probs = $this->_sort_words_by_property($word_probs,"interest_rating");

		// [JAS]: Only keep the MAX_INTERESTING_WORDS words
		$word_probs = array_slice($word_probs,0,MAX_INTERESTING_WORDS);
		$word_hash = $this->_create_antispam_word_hash($word_probs);
		
		return ($word_probs);
	}
	
	function calculate_spam_probability_from_plaintext($body_text)
	{
		$this->search = new cer_SearchIndexEmail();
		$probability = 0;
		$prob = array();

		$this->search->indexWords($body_text);
		$this->search->removeExcludedKeywords();
		$this->search->loadWordIDs(1);
		$words = $this->search->wordarray;
		
		$word_probs = $this->_generate_word_probabilities($words);
		
		foreach($word_probs as $w) {
			$prob[] = $w->probability;
		}

		$probability = $this->combine_p($prob);

		return($probability);
	}
	
	function calculate_spam_probability($ticket_id=0, $prob_override=0, $with_words=0)
	{
		$probability = 0;
		$prob = array();
		
		if(!empty($prob_override)) return $prob_override;
		
 		// check to see if the spam probability is cached
		$sql = sprintf("SELECT `t`.`ticket_spam_probability` ".
						"FROM `ticket` t ".
						"WHERE `t`.`ticket_spam_probability`!=0 AND `t`.`ticket_id`=%d",
							$ticket_id
						);
						
		$result = $this->db->query($sql);

		if($row = $this->db->grab_first_row($result)) {
			$probability = $row["ticket_spam_probability"];
		}
		
		else {
			// calculate the spam probability
			$word_probs = $this->_analyze_raw_email($ticket_id, $with_words);
			
			if(empty($word_probs)) return false;
	
			foreach($word_probs as $w) {
				$prob[] = $w->probability;
			}

			$probability = $this->combine_p($prob);

			$sql = sprintf("UPDATE `ticket` SET `ticket_spam_probability`=%f WHERE `ticket_id`=%d",
							$probability,
							$ticket_id
							);

			$this->db->query($sql);
		}
		return $probability;
	}

	function mark_tickets_as_spam($tickets=array())
	{
		$this->_mark_tickets_as($tickets,"spam");
	}
	
	function mark_tickets_as_ham($tickets=array())
	{
		$this->_mark_tickets_as($tickets,"ham");
	}
	
	function _mark_tickets_as($tickets=array(),$spam_ham)
	{
		if(!is_array($tickets) || empty($tickets)) return false;
		
		$this->thread_handler = new cer_ThreadContentHandler();
		
		$sql = sprintf("SELECT t.ticket_id, t.ticket_spam_trained, th.thread_id, th.thread_subject ".
			"FROM ticket t ".
			"LEFT JOIN thread th ON (t.min_thread_id = th.thread_id) ".
			"WHERE t.ticket_id IN (%s)",
				implode(",",$tickets)
			);
		$res = $this->db->query($sql);
		
		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				$spam_code = 0;
				
				if($row["ticket_spam_trained"] == 0)
				{
					$this->thread_handler->loadThreadContent($row["thread_id"]);
					$thread_content = &$this->thread_handler->threads[$row["thread_id"]]->content;
					
					$text = stripslashes($row["thread_subject"]) . "\r\n" . $thread_content;
					
					switch($spam_ham)
					{
						case "spam":
							$this->mark_message_as_spam($row["ticket_id"]);
							$spam_code = 2;
							break;
						case "ham":
							$this->mark_message_as_nonspam($row["ticket_id"]);
							$spam_code = 1;
							break;
					}
		
					$sql = sprintf("UPDATE ticket SET ticket_spam_trained = %d WHERE ticket_id = %d",
							$spam_code,
							$row["ticket_id"]	
						);
					$this->db->query($sql);
				}
			}
		}
		
		$sql = "UPDATE `ticket` SET `ticket_spam_probability`=0";
		$this->db->query($sql);
	}
	
	function mark_message_as_spam($tid=0)
	{
		$cfg = CerConfiguration::getInstance();
		
		$word_probs = $this->_analyze_raw_email($tid);
		
		if(empty($word_probs)) return false;
		
		foreach($word_probs as $w) {
			$id = $w->word_id;
			$spam = $w->in_spam + 1;
			$ham = $w->in_nonspam;
		
			$sql = "REPLACE INTO spam_bayes_index (word_id,in_spam,in_nonspam) VALUES($id,$spam,$ham)";
			$this->db->query($sql);
		}
		
		$sql = "UPDATE spam_bayes_stats SET num_spam = num_spam + 1";
		$this->db->query($sql);
		
		if(!empty($tid) && isset($cfg) && $cfg->settings["auto_delete_spam"])
		{
			$sql = "UPDATE ticket SET ticket_status='dead' WHERE ticket_id = $tid";
			$this->db->query($sql);
		}

		$sql = "UPDATE `ticket` SET `ticket_spam_probability`=0 WHERE `ticket_spam_probability`!=0";
		$this->db->query($sql);
	}
	
	function mark_message_as_nonspam($ticket_id)
	{
		$word_probs = $this->_analyze_raw_email($ticket_id);

		if(empty($word_probs)) return false;

		foreach($word_probs as $w) {
			$id = $w->word_id;
			$spam = $w->in_spam;
			$ham = $w->in_nonspam + 1;
		
			$sql = "REPLACE INTO spam_bayes_index (word_id,in_spam,in_nonspam) VALUES($id,$spam,$ham)";
			$this->db->query($sql);
		}
		
		$sql = "UPDATE spam_bayes_stats SET num_nonspam = num_nonspam + 1";
		$this->db->query($sql);
		
		$sql = "UPDATE `ticket` SET `ticket_spam_probability`=0 WHERE `ticket_spam_probability`!=0";
		$this->db->query($sql);
	}

	function _create_antispam_word_hash(&$words)
	{
		$word_hash = array();
		
		foreach($words as $idx => $w)
			$word_hash[$w->word] = &$words[$idx];
		
		return $word_hash;
	}
	
	function _assign_probabilities(&$words)
	{
		$word_hash = array();
		
		// [JAS]: Set default probability on all words before lookup
		foreach($words as $w => $i)
		{
			$word = new CER_BAYESIAN_ANTISPAM_WORD($w,$i);
			$words_lookup[] = $word;
			
			if($i != -1) { // [JAS]: Only hash indexed words.
				$element = count($words_lookup)-1;
				$word_hash[$i] = &$words_lookup[$element];
			}
		}
		
		$word_id_list = implode(",",$words);

		// [JAS]: Look up spam word probabilities from our index		
		$sql = "SELECT b.word_id, b.in_spam, b.in_nonspam ".
			"FROM spam_bayes_index b ".
			"WHERE b.word_id IN ($word_id_list)";
		$res = $this->db->query($sql);
		
		// [JAS]: Assign spam word probabilities from the index
		if($this->db->num_rows($res))
		{
			while($row = $this->db->fetch_row($res))
			{
				$word = &$word_hash[$row["word_id"]];
				if(is_object($word)) { // Added check to make sure that $word is an object
					$word->in_spam = $row["in_spam"];
					$word->in_nonspam = $row["in_nonspam"];

					$word->probability = $this->_calculate_word_probability($word);

					// [JAS]: If a word appears more than 5 times (counting weight) in the corpus, use it.  Otherwise discard.
					if(($word->in_nonspam * 2) + $word->in_spam >= 5)
						$word->interest_rating = $this->get_median_deviation($word->probability);
					else
						$word->interest_rating = 0.00;
                                }
			}			
		}
		
		return $words_lookup;
	}
	
	function _calculate_word_probability($word)
	{
		$non_spam = max($this->num_nonspam,1);
		$spam = max($this->num_spam,1);

		$num_good = $word->in_nonspam * 2;
		$num_bad = $word->in_spam;
		
		$ngood = min(($num_good / $non_spam),1);
		$nbad = min(($num_bad / $spam),1);
		
		$prob = max(min(($nbad / ($ngood + $nbad)),PROBABILITY_CEILING),PROBABILITY_FLOOR);

		return ($prob);
	}
	
	function _sort_words_by_property($words,$property)
	{
		if(!count($words)) return false;
		
		foreach ($words as $key => $w)
			$tmpArray[$key] = $w->{$property};
			
		arsort($tmpArray);
		
		$new_words = array();
		
		foreach($tmpArray as $key => $val)
			$new_words[] = $words[$key];
		
		unset($words);
		unset($tmpArray);
		
		return ($new_words);
	}
};



?>
