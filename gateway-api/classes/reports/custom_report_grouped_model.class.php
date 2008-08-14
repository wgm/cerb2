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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

class reports_custom_grouped_model extends reports_custom_model {
	var $groupings;

	var $summaries;

	function reports_custom_model($xml_input) {
		$this->summaries = array();
		$this->format_type = "grouped";
		parent::reports_custom_model();
	}

	function set_grouping_data($xml_input) {
		$groupings_elm = $xml_input->get_child("groupings", 0); //matrix/ group define own, list needs to opt out		
		
		$groupings_children =& $groupings_elm->get_child("group");
		
		if(is_array($groupings_children))
			foreach($groupings_children as $group_elm) {
				//print_r($group_elm);
				$order_attribute = $group_elm->get_attribute("order", FALSE);
				$this->groupings[$order_attribute] = $this->process_group($group_elm);
			}				
	}

	function process_summaries(&$disp_child_tag, $disp_col_name) {
		if(is_array($disp_child_tag))
		foreach($disp_child_tag as $summaries_elm) {
			//print_r($summaries_elm); echo ".....";
			$summaries_children =& $summaries_elm->get_children();
			if(is_array($summaries_children))
			foreach($summaries_children AS $summary_elms) {
				if(is_array($summary_elms))
				foreach($summary_elms AS $summary_elm) {
					//print_r($summary_elm);
					$summary = array();
					$summary['type'] = $summary_elm->get_attribute("type", FALSE);
					$summary['value'] = "";
					$this->summaries[$disp_col_name][] = $summary;
				}
			}
		}
	}

}
