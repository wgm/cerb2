<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/arrays.inc.php");

class email_dispatcher
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;

	function email_dispatcher() {
		$this->db =& database_loader::get_instance();
		$this->ticket_ids = array();

		// This builds a list of all class methods which start with 'grab_work'.
		// These will be cycled through in the order they are listed in this file to get work for a user
		$this->dispatcher_methods = array_filter(get_class_methods($this), create_function('$var', 'return stristr($var,"grab_work") !== FALSE;'));
		$this->max_score = $this->build_max_score();
	}

	function build_max_score() {
		$constants = get_defined_constants();
		$max_score = 0;
		foreach($constants as $name=>$value) {
			if(stristr($name, "DISPATCHER_WEIGHT_") !== FALSE) {
				if(stristr($name, "SKILL_MATCH") !== FALSE && stristr($name, "SKILL_MATCH_100") === FALSE) {
					continue;
				}
				elseif($value < 0) {
					continue;
				}
				else {
					$max_score += $value;
				}
			}
		}
		return $max_score;
	}

	//   function assign_work($num_tickets, $user_id, $teams_pulled) {
	function assign_work($user_id, $teams_pulled) {
		$this->set_teams_and_departments($teams_pulled);
		$this->save_team_ticket_pulls($user_id, $teams_pulled);
		$queues = $this->get_queues_from_teams($teams_pulled);
		//      $current_ticket_count = $this->db->Get("user", "get_assigned_ticket_count", array("user_id"=>$user_id));
		//      if($current_ticket_count > $num_tickets) {
		//         xml_output::error(0, 'You already have too many tickets assigned to you!');
		//      }

		// hard setting the number of tickets to grab to 25
		$num_tickets = 25;

		// Now that we know how many tickets we need, loop over the methods we have to grab work until we don't need anymore tickets
		foreach($this->dispatcher_methods as $method) {
			if(method_exists($this, $method)) {
				$this->$method($num_tickets, $user_id, $queues);
			}
		}
		$this->decrease_weight_other_agent_scheduled($user_id);
		$this->remove_rejected_tickets($user_id);
		return $this->build_xml($num_tickets*4);
	}

	function set_teams_and_departments($teams_pulled) {
		$this->teams_pulled = $teams_pulled;
		$this->departments_pulled = array();
		$departments_list = $this->db->Get("departments", "get_departments_from_teams_list", array("teams"=>$teams_pulled));
		if(is_array($departments_list)) {
			foreach($departments_list as $department_item) {
				$this->departments_pulled[] = $department_item['department_id'];
			}
		}
	}

	function get_queues_from_teams($teams_pulled) {
		$queue_list = $this->db->Get("teams", "get_queues_from_teams_get_workable", array("teams"=>$teams_pulled));
		$queues = array();
		if(is_array($queue_list)) {
			foreach($queue_list as $queue_item) {
				$queues[] = $queue_item['queue_id'];
			}
		}
		return $queues;
	}

	function save_team_ticket_pulls($user_id, $teams_pulled) {
		$this->db->Save("dispatcher", "clear_ticket_pulled_teams", array("user_id"=>$user_id));
		$this->db->Save("dispatcher", "save_pulled_teams", array("user_id"=>$user_id, "teams"=>$teams_pulled));
	}

	function add_ticket_to_pool($ticket_id, $weight, $method = NULL) {
		if(!is_array($this->ticket_ids[$ticket_id]['methods'])) {
			$this->ticket_ids[$ticket_id]['methods'] = array();
		}
		if(!is_null($method) && array_search($method, $this->ticket_ids[$ticket_id]['methods']) === FALSE) {
			$this->ticket_ids[$ticket_id]['methods'][] = $method;
		}
		if(!isset($this->ticket_ids[$ticket_id]['score'])) {
			$this->ticket_ids[$ticket_id]['score'] = 0;
		}
		$this->ticket_ids[$ticket_id]['score'] += $weight;
	}

	function decrease_weight_other_agent_scheduled($user_id) {
		$ticket_ids = array();
		if(is_array($this->ticket_ids)) {
			foreach($this->ticket_ids as $ticket_id=>$info) {
				$ticket_ids[] = $ticket_id;
			}
		}
		$scheduled_tickets = $this->db->Get("dispatcher", "get_tickets_scheduled_others", array("user_id"=>$user_id, "ticket_ids"=>$ticket_ids));
		$scheduled_ticket_ids = array();
		if(is_array($scheduled_tickets)) {
			foreach($scheduled_tickets as $row) {
				$this->add_ticket_to_pool($row['ticket_id'], DISPATCHER_WEIGHT_OTHER_AGENT_DELAY);
			}
		}
	}

	function remove_rejected_tickets($user_id) {
		$this->db->Save("dispatcher", "purge_ignored_tickets", array());
		$ticket_list = $this->db->Get("dispatcher", "get_tickets_ignored_or_rejected", array("user_id"=>$user_id));
		if(is_array($ticket_list)) {
			foreach($ticket_list as $row) {
				unset($this->ticket_ids[$row['ticket_id']]);
			}
		}
	}

	function score_sorter($a, $b) {
		return ($a["score"] > $b["score"]) ? -1 : 1;
	}

	function build_xml($num_tickets) {
		if(!is_array($this->ticket_ids) || count($this->ticket_ids) < 1) {
			xml_output::error(0, "No work can be found for you!");
		}
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$tickets =& $data->add_child("tickets", xml_object::create("tickets"));
		uasort($this->ticket_ids, array($this, "score_sorter"));
		$loop_count = 0;
		foreach($this->ticket_ids as $ticket_id=>$info) {
			if($loop_count > $num_tickets) {
				break;
			}
			$ticket =& $tickets->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id)));
			$ticket->add_child("score", xml_object::create("score", $info['score']));
			$ticket->add_child("percentile", xml_object::create("percentile", ($info['score']/$this->max_score)));
			$methods =& $ticket->add_child("methods", xml_object::create("methods"));
			if(is_array($info['methods'])) {
				foreach($info['methods'] as $method) {
					$methods->add_child("method", xml_object::create("method", $method));
				}
			}
			$loop_count++;
		}
		return TRUE;
	}

	function grab_work_by_date($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_date", array("max_tickets"=>$max_tickets, "user_id"=>$user_id, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				$this->add_ticket_to_pool($tickets_item['ticket_id'], DISPATCHER_WEIGHT_BY_DATE, 'scheduled');
				$this->db->Save("dispatcher", "clear_from_delay_queue", array("delay_id"=>$tickets_item['delay_id']));
			}
		}
	}

	function grab_work_by_customer_reply($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_customer_reply", array("max_tickets"=>$max_tickets, "user_id"=>$user_id, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				$this->add_ticket_to_pool($tickets_item['ticket_id'], DISPATCHER_WEIGHT_CUSTOMER_REPLY, 'customer_reply');
				$this->db->Save("dispatcher", "clear_from_delay_queue", array("delay_id"=>$tickets_item['delay_id']));
			}
		}
	}

	function grab_work_by_recommended_to_me($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_recommended_to_me", array("max_tickets"=>$max_tickets, "user_id"=>$user_id, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				$this->add_ticket_to_pool($tickets_item['ticket_id'], DISPATCHER_WEIGHT_RECOMMENDED_AGENT, 'recommended_to_you');
				$this->db->Save("dispatcher", "clear_from_suggestion_queue", array("suggestion_id"=>$tickets_item['suggestion_id']));
			}
		}
	}

	function grab_work_overdue($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_overdue", array("max_tickets"=>$max_tickets, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				$this->add_ticket_to_pool($tickets_item['ticket_id'], DISPATCHER_WEIGHT_OVERDUE_TICKET, 'overdue');
			}
		}
	}

	function grab_work_high_priority($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_high_priority", array("priority"=>DISPATCHER_HIGH_PRIORITY_TICKET, "max_tickets"=>$max_tickets, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				$this->add_ticket_to_pool($tickets_item['ticket_id'], DISPATCHER_WEIGHT_HIGH_PRIORITY_TICKET, 'priority');
			}
		}
	}

	function grab_work_skills_match($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_skills_match", array("user_id"=>$user_id, "max_tickets"=>$max_tickets, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				if($tickets_item['percentage_match'] > 0.75) {
					$weight = DISPATCHER_WEIGHT_SKILL_MATCH_100;
				}
				elseif($tickets_item['percentage_match'] > 0.5) {
					$weight = DISPATCHER_WEIGHT_SKILL_MATCH_75;
				}
				elseif($tickets_item['percentage_match'] > 0.25) {
					$weight = DISPATCHER_WEIGHT_SKILL_MATCH_50;
				}
				else {
					$weight = DISPATCHER_WEIGHT_SKILL_MATCH_25;
				}
				$this->add_ticket_to_pool($tickets_item['ticket_id'], $weight, 'skills');
			}
		}
	}

	function grab_work_from_anywhere($max_tickets, $user_id, $queues) {
		$tickets_list = $this->db->get("dispatcher", "get_tickets_anywhere", array("max_tickets"=>$max_tickets, "queues"=>$queues));
		if(is_array($tickets_list)) {
			foreach($tickets_list as $tickets_item) {
				$this->add_ticket_to_pool($tickets_item['ticket_id'], DISPATCHER_WEIGHT_GENERIC, 'generic');
			}
		}
	}

	function recommend_ticket($ticket_id, $member_ids) {
		if(!is_array($member_ids)) {
			xml_output::error(0, "Can't save recommendations without array of ids");
		}
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$data->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id)));
		$members =& $data->add_child("members", xml_object::create("members"));

		$query_params["ticket_id"] = $ticket_id;
		if($this->db->Save("dispatcher", "recommend_ticket", array("ticket_id"=>$ticket_id, "member_ids"=>$member_ids))) {
			foreach($member_ids as $id) {
				$members->add_child("member", xml_object::create("member", NULL, array("id"=>$id)));
			}
			return TRUE;
		}
		else {
			return FALSE;
		}
	}


	function accept_tickets($tickets_array, $user_id) {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$tickets_xml =& $data->add_child("tickets", xml_object::create("tickets"));
		if(is_array($tickets_array)) {
			foreach($tickets_array as $ticket_id=>$ticket_info) {
				if($this->db->Save("dispatcher", "accept_ticket", array("ticket_id"=>$ticket_id, "user_id"=>$user_id))) {
					$this->db->Save("ticket", "add_task", array("ticket_id"=>$ticket_id, "user_id"=>$user_id, "title"=>$ticket_info["title"], "estimate"=>$ticket_info["estimate"]));
					$tickets_xml->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id, "result"=>"success")));
				}
				else {
					$tickets_xml->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id, "result"=>"failure", "reason"=>"Already assigned to someone else")));
				}
			}
		}
		return TRUE;
	}

	function hide_tickets($tickets_array, $user_id) {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$tickets_xml =& $data->add_child("tickets", xml_object::create("tickets"));
		foreach($tickets_array as $ticket_id=>$ticket_info) {
			if($this->db->Save("dispatcher", "hide_ticket", array("ticket_id"=>$ticket_id, "user_id"=>$user_id, "reason"=>$ticket_info['reason']))) {
				$tickets_xml->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id, "result"=>"success")));
			}
			else {
				$tickets_xml->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id, "result"=>"failure", "reason"=>"Failed to hide ticket")));
			}
		}
		return TRUE;
	}

	function delay_tickets($tickets_array, $user_id) {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$tickets_xml =& $data->add_child("tickets", xml_object::create("tickets"));
		foreach($tickets_array as $ticket_id=>$ticket_info) {
			if($this->db->Save("dispatcher", "delay_ticket", array("ticket_id"=>$ticket_id, "user_id"=>$user_id, "reason"=>$ticket_info['reason'], "mins"=>$ticket_info['mins']))) {
				$tickets_xml->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id, "result"=>"success")));
			}
			else {
				$tickets_xml->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id, "result"=>"failure", "reason"=>"Already assigned to someone else")));
			}
		}
		return TRUE;
	}
	
	function get_suggestions_for_ticket($ticket_id) {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$data->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_id)));
		$suggestions_xml =& $data->add_child("suggestions", xml_object::create("suggestions"));
		$suggestions_array = $this->db->Get("dispatcher", "get_ticket_suggestions", array("ticket_id"=>$ticket_id));
		if(is_array($suggestions_array)) {
			foreach($suggestions_array as $suggestion_row) {
				$suggestion_xml =& $suggestions_xml->add_child("suggestion", xml_object::create("suggestion"));
				$suggestion_xml->add_child("agent", xml_object::create("agent", NULL, array("id"=>$suggestion_row['agent_id'])));
				$suggestion_xml->add_child("member", xml_object::create("member", NULL, array("id"=>$suggestion_row['member_id'])));
				$suggestion_xml->add_child("timestamp", xml_object::create("timestamp", $suggestion_row['timestamp']));
			}
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
}