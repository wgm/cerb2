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

// ALTER TABLE `trigram_to_kb` DROP `weight` 
// ALTER TABLE `trigram_to_kb` ADD `good` BIGINT NOT NULL
// ALTER TABLE `trigram_to_kb` ADD `bad` BIGINT NOT NULL ;

//ALTER TABLE `trigram_to_ticket` ADD INDEX ( `ticket_id` ) 
//ALTER TABLE `trigram_to_ticket` ADD INDEX ( `trigram_id` ) 

define("CER_CERBY_PROB_CEILING",0.9999);
define("CER_CERBY_PROB_FLOOR",0.0001);
define("CER_CERBY_PROB_UNKNOWN",0.55);
define("CER_CERBY_PROB_MEDIAN",0.5);
define("CER_CERBY_MAX_INTERESTING_TRIGRAMS",15);
define("CER_CERBY_SUGGESTION_MINIMUM_PROBABILITY",0.1);

require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_Bayesian.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_Trigram.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");

class cer_BayesianTrigram
{
	var $trigram_id = 0;
	var $in_good = 0;
	var $in_bad = 0;
	var $probability = CER_CERBY_PROB_UNKNOWN;
	var $interest_rating = 0.0;
	
	function cer_BayesianTrigram($tg_id, $good, $bad) {
		$this->trigram_id = $tg_id;
		$this->in_good = $good;
		$this->in_bad = $bad;
	}
}

class cer_KbSuggestion extends cer_Bayesian {
	var $num_good = 0;
	var $num_bad  = 0;
	
	var $kb_id    = 0;
	var $trigrams = null;
	var $method = null;

	var $probability = 0;
	var $score       = 0;
	var $trained     = 0;

	var $url     = "";
	var $subject = "";

	function cer_KbSuggestion($kb_id, &$total, $subject, $method="trigram") {
		$this->kb_id = $kb_id;
		$this->trigrams=array();
		$this->method = $method;
		$this->subject = $subject;
	}
	
	function addTrigram($tg_id, $good, $bad) {
		$trigram = new cer_BayesianTrigram($tg_id, $good, $bad);
		$this->trigrams[$tg_id] = $trigram;
	}
	
	function addStats($good, $bad) {
		$this->num_good = $good;
		$this->num_bad  = $bad;
	}
	
	function calculateTrigramProbability() {
		foreach($this->trigrams as $tg_id => $tg) {
			if(0!=($tg->in_good + $tg->in_bad)) {
				$g1 = max($this->num_good,1);
				$b1 = max($this->num_bad,1);

				$g2 = $this->trigrams[$tg_id]->in_good * 2;
				$b2 = $this->trigrams[$tg_id]->in_bad;

				$g3 = min(($g2 / $g1),1);
				$b3 = min(($b2 / $b1),1);
				$this->trigrams[$tg_id]->probability = max(min(($g3 / ($b3 + $g3)),CER_CERBY_PROB_CEILING),CER_CERBY_PROB_FLOOR);		
			}
		}
	}
	
	function calculateTrigramInterest() {
		foreach($this->trigrams as $tg_id => $tg) {
			if(($this->trigrams[$tg_id]->in_good * 2) + $this->trigrams[$tg_id]->in_bad >= 5) {
				$this->trigrams[$tg_id]->interest_rating = $this->get_median_deviation($this->trigrams[$tg_id]->probability);
			}
			else {
				$this->trigrams[$tg_id]->interest_rating = 0.00;
			}
		}
	}
	
	function sortTrigramsByInterest() {
		if(!count($this->trigrams)) return false;
		
		$tmpArray = array();
		$new_trigrams = array();
		
		foreach($this->trigrams as $tg_id => $tg) {
			$tmpArray[$tg_id] = $tg->interest_rating;
		}
		
		arsort($tmpArray);
		
		foreach($tmpArray as $tg_id => $interest_rating) {
			$new_trigrams[$tg_id] = $this->trigrams[$tg_id];
		}
		
		unset($this->trigrams);
		unset($tmpArray);

		$this->trigrams = $new_trigrams;
	}
	
	function trimTrigramsByInterest() {
		$interesting_trigrams = array_splice($this->trigrams,0,CER_CERBY_MAX_INTERESTING_TRIGRAMS);
		$trigram_hash = array();
		foreach($interesting_trigrams as $tg) {
			$trigram_hash[$tg->trigram_id] = $tg;
		}
		
		unset($interesting_trigrams);
		unset($this->trigrams);
		
		$this->trigrams = $trigram_hash;
	}
	
	function calculateKbProbability() {
		// create array of probabilities
		$probabilities = array();
		
		foreach($this->trigrams as $tg_id => $tg) {
			$probabilities[] = $tg->probability;
		}
		
		$this->probability = $this->combine_p($probabilities);
		$this->score = number_format($this->probability*100, 2);
	}
}

class cer_TrigramCerby {
	var $db=null;
	var $good=0;
	var $bad =0;
	var $trigram_ids=null;
	var $kb_suggestions=null;
	
	function cer_TrigramCerby() {
		$this->db = cer_Database::getInstance();
	}
	
	
	function _load_ticket_trigrams($ticket_id=0)
	{
		if(!empty($ticket_id)) {
			// get the number of trigrams associated with a ticket
			$sql = sprintf("SELECT `trigram_to_ticket`.`trigram_id`  ".
							"FROM `trigram_to_ticket` ".
							"WHERE `trigram_to_ticket`.`ticket_id`=%d",
							$ticket_id);
	
			$result = $this->db->query($sql);
	
			$this->trigram_ids = array();
			
			while(null!=($row = $this->db->fetch_row($result))) {
				$this->trigram_ids[] = $row["trigram_id"];
			}
		}
	}
	
	function ask($question,$howmany=5, $public_only=0, $include_kb_catids=NULL) {
		$trigrams = new cer_Trigram();
		
		// get the number of trigrams in the question
		$trigrams->indexWords($question);
		$trigrams->loadWordIDs(0);

		$trigrams->wordsToTrigrams();
		$trigrams->loadTrigramIDs();
		
		// if there are any trigrams, try to find kb article suggestions
		if(!empty($trigrams->trigram_ids)) {
			$this->_getSuggestionData($trigrams->trigram_ids, $howmany, 0, $public_only, $include_kb_catids);
		}
		
		return $this->kb_suggestions;
	}
	
	function getSimilar($kb_id, $howmany=5, $public_only=0, $include_kb_catids = NULL) {
		// get the number of trigrams associated with a KB Article
		$sql = sprintf("SELECT `trigram_to_kb`.`trigram_id` ".
						"FROM `trigram_to_kb` ".
						"WHERE `trigram_to_kb`.`knowledgebase_id`=%d",
						$kb_id);

		$result = $this->db->query($sql);

		if($this->db->num_rows($result)) {
			$kb_trigrams = array();
			
			while(null!=($row = $this->db->fetch_row($result))) {
				$kb_trigrams[] = $row["trigram_id"];
			}
			
			$this->_getSuggestionData($kb_trigrams, $howmany, $kb_id, $public_only, $include_kb_catids);
		}
		
		return $this->kb_suggestions;		
	}
	
	function getSuggestion($ticket_id, $howmany=5, $public_only=0) {

		$this->_load_ticket_trigrams($ticket_id);

		// if there are no trigrams, try to index the ticket
		if(empty($this->trigram_ids)) {
			$certrigram = new cer_TrigramEmail();
			$certrigram->indexSingleTicket($ticket_id);
			$this->_load_ticket_trigrams($ticket_id);
		}
		
		if(!empty($this->trigram_ids)) {
			$this->_getSuggestionData($this->trigram_ids, $howmany, 0, $public_only);
		}

		if(!empty($this->kb_suggestions)) {
			$sql = sprintf("SELECT `kb_id` FROM `trigram_training` WHERE `kb_id` IN (%s) AND `ticket_id`=%d",
							implode(",",array_keys($this->kb_suggestions)),
							$ticket_id
						);
			
			$result = $this->db->query($sql);

			if($this->db->num_rows($result)) {
				while(null!=($row = $this->db->fetch_row($result))) {
					$this->kb_suggestions[$row["kb_id"]]->trained = 1;	
				}
			}
			
			// assign URL's to the KB Articles
			foreach($this->kb_suggestions as $kb_id => $kb) {
				$this->kb_suggestions[$kb_id]->url = cer_href("knowledgebase.php?mode=view_entry&kbid=".$kb_id);
			}
		}

		return $this->kb_suggestions;
	}
	
	
	
	function _getSuggestionData($tg_ids, $howmany=5, $exclude_kbid=0, $public_only=0, $include_kb_catids=NULL) {
		$tgcount = count($tg_ids);
		
		$from_sql = "FROM `trigram_to_kb` tk, `knowledgebase_problem` kp ";
			
		$public_sql = "";
		if(!empty($public_only) && 1==$public_only) {
			$from_sql .= ", `knowledgebase` k ";
			$public_sql = "`k`.`kb_public`=1 AND `k`.`kb_id`=`tk`.`knowledgebase_id` AND ";
			if(is_array($include_kb_catids)) {
				$public_sql .= sprintf("`k`.`kb_category_id` IN ( %s ) AND ", implode(',', $include_kb_catids));
			}
		}
		
		$exclude_sql = "";
		if(!empty($exclude_kbid) && 0<$exclude_kbid) {
			$exclude_sql = sprintf("`tk`.`knowledgebase_id`!=%d AND ", $exclude_kbid);
		}

		// get the trigram->kb suggestions
		$sql = sprintf("SELECT `tk`.`trigram_id`,`tk`.`knowledgebase_id`,`tk`.`good`,`tk`.`bad`,`kp`.`kb_problem_summary` ".
						$from_sql.
						"WHERE %s  %s `kp`.`kb_id`=`tk`.`knowledgebase_id` AND `tk`.`trigram_id` IN (%s) ",
							$public_sql,
							$exclude_sql,
							implode(",",$tg_ids)
						);
		$result = $this->db->query($sql);
		
		$this->kb_suggestions = array();
		
		if(0<($this->db->num_rows($result))) {
			while($row = $this->db->fetch_row($result)) {
				if(empty($this->kb_suggestions[$row["knowledgebase_id"]])) {
					$sug = new CER_KBSUGGESTION($row["knowledgebase_id"], $tgcount, stripslashes($row["kb_problem_summary"]), "trigram");
					$this->kb_suggestions[$row["knowledgebase_id"]]=$sug;
				}

				$this->kb_suggestions[$row["knowledgebase_id"]]->addTrigram($row["trigram_id"],$row["good"], $row["bad"]);
			}
		}
		
		// get the stats for each kb
		$sql = sprintf("SELECT `kb_id`, `num_good`, `num_bad` ".
						"FROM `trigram_stats` ".
						"WHERE `kb_id` IN (%s) ",
							implode(",",array_keys($this->kb_suggestions))
						);
		$result = $this->db->query($sql);
		
		if(0<($this->db->num_rows($result))) {
			while($row = $this->db->fetch_row($result)) {
				$this->kb_suggestions[$row["kb_id"]]->addStats($row["num_good"], $row["num_bad"]);
			}
		}

		// calculate the probabilities
		foreach($this->kb_suggestions as $kb_id => $kbsug) {
			$this->kb_suggestions[$kb_id]->calculateTrigramProbability();
			$this->kb_suggestions[$kb_id]->calculateTrigramInterest();
			$this->kb_suggestions[$kb_id]->sortTrigramsByInterest();
			$this->kb_suggestions[$kb_id]->trimTrigramsByInterest();
			$this->kb_suggestions[$kb_id]->calculateKbProbability();
		}
		
		// trim it down to $howmany suggestions
		// sorty by KB probability
		if(!empty($this->kb_suggestions)) {
			$tmpArray = array();
			$new_kb = array();
		
			foreach($this->kb_suggestions as $kb_id => $kb) {
				if(CER_CERBY_SUGGESTION_MINIMUM_PROBABILITY<$kb->probability) {
					$tmpArray[$kb_id] = $kb->probability;
				}
			}
			
			arsort($tmpArray);
			
			foreach($tmpArray as $kb_id => $probability) {
				if(empty($howmany)) {
					break;
				}
				$new_kb[$kb_id] = $this->kb_suggestions[$kb_id];
				
				// we need one less now, decriment howmany
				$howmany--;
			}
			
			unset($this->kb_suggestions);
			unset($tmpArray);
	
			$this->kb_suggestions = $new_kb;
		}
		
	}	
	
	
	
	function _markTrained($ticket_id, $kb_id, $user_id) {
		// mark the ticket<-->kb as trained
		$sql = sprintf("INSERT IGNORE INTO `trigram_training` (ticket_id,kb_id,user_id)".
						"VALUES (%d,%d,%d)",
						$ticket_id,
						$kb_id,
						$user_id
					  );
					  
		$this->db->query($sql);	
	}
	
	
	function goodSuggestion($ticket_id, $kb_id, $user_id=0) {
		
		$sql = sprintf("SELECT `trigram_id` FROM `trigram_to_ticket` WHERE `ticket_id`=%d",$ticket_id);
		$result = $this->db->query($sql);
		
		$tg_ids = array();
		if($this->db->num_rows($result)) {
			while(null!=($row = $this->db->fetch_row($result))) {
				$tg_ids[] = $row["trigram_id"];
			}
		}

		$sql = sprintf("INSERT IGNORE INTO `trigram_to_kb` ".
						"VALUES (%s,%d,0,0)",
						implode(",$kb_id,0,0),(",$tg_ids),
						$kb_id
					  );
					  
		$this->db->query($sql);
		
		$sql = sprintf("UPDATE `trigram_to_kb` SET `good`=`good`+1 WHERE `trigram_id` IN (%s) AND `knowledgebase_id`=%d",
						implode(",", $tg_ids), $kb_id);

		$result = $this->db->query($sql);

		$data = array();
		if($this->db->num_rows($result)) {
			while(null!=($row = $this->db->fetch_row($result))) {
				$data[] = $row;
			}
		}

		$this->_markTrained($ticket_id,$kb_id,$user_id);

		$sql = sprintf("SELECT `kb_id` FROM `trigram_stats` WHERE `kb_id`=%d",
							$kb_id
						);
		
		$result = $this->db->query($sql);
		
		if($this->db->grab_first_row($result)) {
			// it is in there, just do an update
			$sql = sprintf("UPDATE trigram_stats SET num_good = num_good + 1 WHERE kb_id=%d",
							$kb_id
						);
			$this->db->query($sql);
		}
		else {
			// it is not in there, do an insert
			$sql = sprintf("INSERT INTO `trigram_stats` (`kb_id`,`num_good`) values (%d,1)",
								$kb_id
							);
							
			$this->db->query($sql);
		}
						

		return;			
	}


	
	function badSuggestion($ticket_id, $kb_id, $user_id=0) {
		
		$sql = sprintf("SELECT `trigram_id` FROM `trigram_to_ticket` WHERE `ticket_id`=%d",$ticket_id);
		$result = $this->db->query($sql);
		
		$tg_ids = array();
		if($this->db->num_rows($result)) {
			while(null!=($row = $this->db->fetch_row($result))) {
				$tg_ids[] = $row["trigram_id"];
			}
		}

		$sql = sprintf("INSERT IGNORE INTO `trigram_to_kb` ".
						"VALUES (%s,%d,0,0)",
						implode(",$kb_id,0,0),(",$tg_ids),
						$kb_id
					  );
		$this->db->query($sql);
		
		$sql = sprintf("UPDATE `trigram_to_kb` SET `bad`=`bad`+1 WHERE `trigram_id` IN (%s) AND `knowledgebase_id`=%d",
						implode(",", $tg_ids), $kb_id);
		
		$result = $this->db->query($sql);
		
		$data = array();
		if($this->db->num_rows($result)) {
			while(null!=($row = $this->db->fetch_row($result))) {
				$data[] = $row;
			}
		}
		
		$this->_markTrained($ticket_id,$kb_id,$user_id);

		$sql = sprintf("SELECT `kb_id` FROM `trigram_stats` WHERE `kb_id`=%d",
							$kb_id
						);
		
		$result = $this->db->query($sql);
		
		if($this->db->grab_first_row($result)) {
			// it is in there, just do an update
			$sql = sprintf("UPDATE trigram_stats SET num_bad = num_bad + 1 WHERE kb_id=%d",
							$kb_id
						);
			$this->db->query($sql);
		}
		else {
			// it is not in there, do an insert
			$sql = sprintf("INSERT INTO `trigram_stats` (`kb_id`,`num_bad`) values (%d,1)",
								$kb_id
							);
							
			$this->db->query($sql);
		}
		
		return;			
	}
	
};

?>
