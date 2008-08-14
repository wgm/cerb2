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

require_once(FILESYSTEM_PATH . "includes/functions/structs.php");

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketViewProcs.func.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");

class cer_TicketView {
	var $db = null;

	var $view_id = 0; 											// view id from db
	var $view_name = null; 										// view name
	var $view_slot = null; 										// the variable to track the view prefs with (sorting/asc,desc/page/etc)
	var $view_private = 0;										// 0=no 1=yes

	var $view_title_style = null;

	var $view_exclusive = false;								// Require in_tids()
	var $view_only_assigned = 0; 								// 0 = show all  1 = only show owned tickets

	var $view_adv_2line=1;										// Show two line ticket listings, subject on its own.  1=yes 0=np
	var $view_adv_controls=1;									// Show ticket batch controls + checkboxes.  1=yes 0=np
	var $view_adv_exclusions=0;									// How many columns we're forcing (checkbox + subject, etc)

	var $view_colspan = 0; 										// stores colspan for display
	var $view_colspan_subject = 0; 								// stores colspan for first line display

	var $view_options = array(); 								// array of all views available for drop-down

	var $view_page = 0;
	var $view_next_url = null;
	var $view_prev_url = null;

	var $view_bind_page = null;									// page name: index.php, display.php, ticket_list.php, ...
	var $show_options = 0;

	var $filter_rows = 0;
	var $filter_responded = 0;

	var $hide_statuses = "awaiting-reply,resolved,dead";		// statuses to not show in view
	// statuses to show in view, opposite of hide_statuses
	var $show_statuses = array();								// automatically built

	var $queues = null; 										// queues string, comma-delimited; or '*' for all

	var $column_string = null; 									// raw column string, comma-delimited
	var $columns = array(); 									// view columns
	var $rows = array(); 										// view rows

	var $params = array();

	var $page_name = null;
	var $page_args = "x=";

	var $show_next = false;
	var $show_prev = false;
	var $show_from = 0;
	var $show_to = 0;
	var $show_of = 0;
	var $show_new_view = false;
	var $show_edit_view = false;

	var $show_modify = null;
	var $show_mass = null;
	var $show_batch_actions = null;
	var $show_chowner = null;
	var $show_chowner_options = null;
	var $show_chstatus = null;
	var $show_chstatus_options = null;
	var $show_chqueue = null;
	var $show_chqueue_options = null;
	var $show_chaction = null;

	var $field_handler = null;

	var $heap_name = null;
	
	var $tables = array();

	function cer_TicketView($vid="",$vslot="",$params=array()) {
		global $_SERVER; //* \todo clean
		global $session;
		global $status_options;

		if(!empty($status_options))
		foreach($status_options as $st) {
			$this->show_statuses[$st] = $st;
		}
			
		$this->db = cer_Database::getInstance();
		$this->heap_name = "temp_" . $session->vars["login_handler"]->user_id;
		
		$this->view_slot = $vslot;

		$this->params = $params;

		$this->field_handler = new cer_CustomFieldGroupHandler();
		$this->field_handler->loadGroupTemplates();

		$this->page_name = $_SERVER['PHP_SELF'];

		// [JAS]: Load information about the tables the view can use columns from.
		$this->_loadTables();

		$this->setPrefs();
		$this->_loadViewOptions();
		$this->_loadViewDetails($vid);
		$this->_computeViewColSpan();
		$this->_populateView();
		$this->_determinePageURLs();
	}

	function setPrefs() {
		// [JAS]: Expect Override
	}

	function setViewDefaults() {
		// [JAS]: Expect Override
	}

	function _loadTables() {
		global $session;

		$pref_vars = &$session->vars["login_handler"]->user_prefs->view_prefs->vars;
		$view_slot = $this->view_slot;
		$sort_by = $pref_vars[$view_slot."_sort_by"];

		// [JAS]: Do we need to LEFT JOIN some tables? (ugh)
		$use_company = false; // phew
		if($sort_by == "company_name" || $this->params["search_company"]) {
			$use_company = true; // ack
		}

		$base_sql = "SELECT t.ticket_id, thr.thread_address_id ".
		"FROM ticket t, thread thr, thread th, address a, address ad, queue q %s %s ".
		"%s ".
		(($use_company) ? "LEFT JOIN public_gui_users pu ON (a.public_user_id = pu.public_user_id) " : "") .
		(($use_company) ? "LEFT JOIN company c ON (pu.company_id = c.id) " : "") .
		"WHERE t.min_thread_id = thr.thread_id ".
		"AND t.max_thread_id = th.thread_id ".
		"AND a.address_id = thr.thread_address_id ".
		"AND ad.address_id = th.thread_address_id ".
		((!empty($this->params["search_sender"])) ? "AND r.ticket_id = t.ticket_id " : "").
		"AND t.ticket_queue_id = q.queue_id ";

		$this->tables = array (
		"address_first" => new cer_TicketViewColumnTable("address","a",$base_sql),
		"address_last" => new cer_TicketViewColumnTable("address","ad",$base_sql),
		"company" => new cer_TicketViewColumnTable("company","c",$base_sql),
		"queue" => new cer_TicketViewColumnTable("queue","q",$base_sql),
		"thread_first" => new cer_TicketViewColumnTable("thread","thr",$base_sql),
		"thread_last" => new cer_TicketViewColumnTable("thread","th",$base_sql),
		"ticket" => new cer_TicketViewColumnTable("ticket","t",$base_sql),
		);
	}

	function _loadViewOptions() {
		global $cer_hash; //* \todo clean

		// [JAS]: Store our view options for displaying the view select box
		$this->view_options = array('' => 'Default');
		foreach($cer_hash->get_view_hash() as $view)
		{
			$this->view_options[$view->view_id] = $view->view_name;
		}
	}

	function _loadViewDetails($vid) {
		global $status_options;
		global $priv;

		// [JAS]: Determine what links we'll be showing under the view
		if($priv->has_priv(ACL_VIEWS_CREATE,BITGROUP_2))
		$this->show_new_view = true;
		if(!empty($vid) && ($priv->has_priv(ACL_VIEWS_EDIT,BITGROUP_2) || $priv->has_priv(ACL_VIEWS_DELETE,BITGROUP_2)))
		$this->show_edit_view = true;

		// [JAS]: If a view ID was selected, load it
		if(!empty($vid))
		{
			$sql = "SELECT v.view_id,v.view_name,v.view_private,v.view_queues,v.view_columns,v.view_hide_statuses,v.view_only_assigned,v.view_adv_2line,v.view_adv_controls ".
			"FROM ticket_views v ".
			"WHERE v.view_id = $vid";
			$v_res = $this->db->query($sql,false);

			if($this->db->num_rows($v_res))
			{
				$v_row = $this->db->fetch_row($v_res);
				$this->view_id = $v_row[0];
				$this->view_name = trim(stripslashes($v_row[1]));
				$this->view_private = (int)$v_row[2];
				$this->queues = (string)$v_row[3];
				$this->column_string = (string)$v_row[4];
				$this->hide_statuses = $v_row[5];
				$this->view_only_assigned = $v_row[6];
				$this->view_adv_2line = $v_row[7];
				$this->view_adv_controls = $v_row[8];

				$cols = explode(",",$v_row[4]);

				array_push($this->columns,new cer_TicketViewColumn("checkbox",$this,"bids[]")); // [JAS]: Force checkbox
				array_push($this->columns,new cer_TicketViewColumn("ticket_subject",$this)); // [JAS]: Force subject

				// [JAS]: Are we showing two line subjects
				if($this->view_adv_2line)
				$this->view_adv_exclusions++;

				// [JAS]: Are we showing view advanced controls
				if($this->view_adv_controls)
				$this->view_adv_exclusions++;

				if(!empty($cols))
				foreach($cols as $c) {
					if($this->view_adv_2line && $c == "ticket_subject")
					continue;

					array_push($this->columns,new cer_TicketViewColumn($c,$this));
				}

			}
			else // no row returned (bad cookie or deleted)
			{
				$this->setViewDefaults();
			}
		}
		else // [JAS]: default view values
		{
			$this->setViewDefaults();
		}
	}

	function _computeViewColSpan()
	{
		$col_span = count($this->columns);

		if(!$this->view_adv_controls)
		$col_span--;

		$this->view_colspan = $col_span - 1;

		$this->view_colspan_subject = $col_span - 1;

		if($this->view_adv_2line && $this->view_adv_controls)
		$this->view_colspan_subject--;
	}

	/* Allow Override */
	function _buildQueueList() {
		global $queue_access;

		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();

		// [JAS]: Replacement for the queue access linking, manipulate queue id lists.
		$user_queues = $queue_access->get_readable_qid_list();
		if($this->queues == "*") $this->queues = $user_queues;
		$qid_list = $queue_access->elements_in_both_lists($this->queues,$user_queues);

		$qid_sql = null;

		if(!empty($qid_list)) {
			$qid_sql = $qid_list;
		}
		else {
			$qid_sql = '-1';
		}

		return $qid_sql;
	}

	/* Allow Override */
	function _buildStatusList() {
		$show_status_sql = null;
		$status_list = $this->show_statuses;

		if (!empty($this->hide_statuses)) {
			$hide_list = explode(',', $this->hide_statuses);

			if(!empty($hide_list))
			foreach($hide_list as $status) {
				unset($status_list[$status]);
			}

			$show_status_sql = "'".implode("','",$status_list)."'";
		}

		return $show_status_sql;
	}

	/* Expect Override */
	function _buildSenderList() {
		return null;
	}

	/* Allow Override */
	function _buildAssignedList() {
		global $session;

		$assigned_sql = null;

		if($this->view_only_assigned == 1) {
			$assigned_sql = $session->vars["login_handler"]->user_id;
		}
		else if($this->view_only_assigned == 2) {
			$assigned_sql = "'0'";
		}

		return $assigned_sql;
	}

	/* Expect Override */
	function _buildCompanyList() {
		return null;
	}

	/* Expect Override */
	function _buildSearchWords() {
		return null;
	}

	/* Expect Override */
	function _buildContentSearchWords() {
		return null;
	}

	/* Expect Override */
	function _buildSubjectSearchWords() {
		return null;
	}

	/* Expect Override */
	function _buildDateSearch() {
		return null;
	}

	/* Expect Override */
	function _buildCustomFieldSearch() {
		return null;
	}

	/* Expect Override */
	function _buildBatchList() {
		return null;
	}

	function _buildCustomerRespondedSQL() {
		if(!empty($this->filter_responded)) {
			return " t.last_reply_by_agent = 0 ";
		}
		else {
			return null;
		}
	}

	function _populateView() {
		global $session;

		///////////////// =============================================================
		//  INITIALIZE SCOPE  [JAS]
		///////////////// =============================================================

		$pref_vars = &$session->vars["login_handler"]->user_prefs->view_prefs->vars;
		$view_slot = $this->view_slot;
		$sort_by = $pref_vars[$view_slot."_sort_by"];

		// [JAS]: If the session is sorting on an invalid column, look for ticket due, age or ticket ID, otherwise
		//	sort on the second column by default (first could be checkbox)
		if(!$this->columnExists($sort_by)) {
			if(!$col_id = $this->columnExists("ticket_due"))
			if(!$col_id = $this->columnExists("thread_date"))
			if(!$col_id = $this->columnExists("ticket_id"))
			if($col_id = $this->columns[1]->column_id) {}
			$sort_by = $this->columns[$col_id]->column_name;
		}
		$sort_asc = (($pref_vars[$view_slot."_asc"]==1)?"ASC":"DESC");

		$show_rows = $this->filter_rows;

		$p = @$pref_vars[$view_slot . "_p"];
		if(empty($p) || $p < 0) $p=0;

		// [JAS]: (LIMIT) Where to start in the resultset + how many rows to grab
		$row_from = ($p * $show_rows);
		$row_for = $show_rows;

		$qid_sql = $this->_buildQueueList();
		$show_status_sql = $this->_buildStatusList();
		$sender_sql = $this->_buildSenderList();
		$assigned_sql = $this->_buildAssignedList();
		$date_range_sql = $this->_buildDateSearch();
		$company_sql = $this->_buildCompanyList();
		$subject_words_rows = $this->_buildSubjectSearchWords(); // run second for subj bit
		$content_words_rows = $this->_buildContentSearchWords();
		$batch_id_sql = $this->_buildBatchList();
		$responded_sql = $this->_buildCustomerRespondedSQL();

		list($field_from_sql, $field_where_sql) = $this->_buildCustomFieldSearch();

		//          if(!count($this->in_tids) && $this->view_exclusive) array_push($this->in_tids,-1);

		///////////////// =============================================================
		//  INITIALIZE SORT VARIABLES  [JAS]
		///////////////// =============================================================

		$sort_id = $this->columnExists($sort_by);
		$sort["table_name"] = $this->columns[$sort_id]->table->table_name;
		$sort["table_prefix"] = $this->columns[$sort_id]->table->table_prefix;
		$sort["field_name"] = $this->columns[$sort_id]->table_field_name;
		$sort["sort_sql"] = $this->columns[$sort_id]->table->sort_sql;
		$sort["group_by"] = $this->columns[$sort_id]->table->group_by;

		///////////////// =============================================================
		//  PRE-SORT USING TICKET ID  [JAS]
		///////////////// =============================================================

		$t_ids = array();
		$r_ids = array();
		$search_join = null;

		if(!empty($subject_words_rows) && $subject_words_rows > 0) {
			$search_join .= sprintf(", %s_s si_s ",
				$this->heap_name
			);
		}
		if(!empty($content_words_rows) && $content_words_rows > 0) {
			$search_join .= sprintf(", %s_c si_c ",
				$this->heap_name	
			);
		}

		$sql = $sort["sort_sql"] .
		" %s ". // responded
		" %s ". // show status
		//		  	" %s ". // in status
		" %s ". // in requester list
		" %s ". // company
		" %s ". // queue
		" %s ". // batch
		" %s ". // assigned
		" %s ". // date range
		" %s ". // subject search words
		" %s ". // content search words
		" %s ". // custom field where
		" %s ". // group by
		"ORDER BY %s %s";

		// [JAS]: Fix needed for [#CERB-68] where MySQL 4.1 will not sort on table_name.aliased_field.
		if(substr($sort["table_prefix"],0,2) == "v_") {
			$sort_sql = $sort["field_name"];
		} else {
			$sort_sql = $sort["table_prefix"] . '.' . $sort["field_name"];
		}
		
		$sql = sprintf($sql,
		(($field_from_sql) ? ", " . $field_from_sql : ""),
		(($sender_sql) ? ", requestor r " : ""),
		((!empty($search_join)) ? $search_join : " "), // [JAS]: Are we adding the search_index table?
		((!empty($responded_sql)) ? " AND $responded_sql " : " "),
		((!empty($show_status_sql)) ? " AND t.ticket_status IN (".$show_status_sql.")" : " "),
		//		  		((!empty($status_sql)) ? " AND t.ticket_status IN (".$status_sql.")" : " "),
		((!empty($sender_sql)) ? " AND r.address_id IN (".$sender_sql.")" : " "),
		((!empty($company_sql)) ? " AND c.id = " . $company_sql . " " : " "),
		((!empty($qid_sql)) ? "AND t.ticket_queue_id IN ($qid_sql)" : ""),
		((!empty($batch_id_sql)) ? "AND t.ticket_id IN ($batch_id_sql)" : ""),
		((!empty($assigned_sql)) ? "AND t.ticket_assigned_to_id = " . $assigned_sql . "" : ""),
		((!empty($date_range_sql)) ? $date_range_sql : ""),
		((!empty($subject_words_rows)) ? " AND  t.ticket_id = si_s.ticket_id " : ""),
		((!empty($content_words_rows)) ? " AND  t.ticket_id = si_c.ticket_id " : ""),
		((!empty($field_where_sql)) ? " AND " . $field_where_sql : ""),
		((!empty($sort["group_by"])) ? (" GROUP BY " . $sort["group_by"] . " ") : " "),
		$sort_sql,
		$sort_asc
		);

//		echo "<HR>" . $sql . "<BR>";
		
		$count_result = $this->db->query($sql);

		$this->show_of = $this->db->num_rows($count_result);

		// [JAS]: If we're trying to go past the max number of pages, reset the view.
		if($row_from > $this->show_of) {
			$row_from = 0;
			$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;
			$view_prefs->vars[$this->view_slot."_p"] = 0;
		}

		$sql .= sprintf(" LIMIT %d,%d",
		$row_from,
		$row_for
		);

		$res = $this->db->query($sql);

		$this->show_to = $row_from+$row_for;
		$this->show_from = $row_from+1;
		if($this->show_of == 0) $this->show_from = 0;
		if($this->show_to > $this->show_of) $this->show_to = $this->show_of;

		//		  echo $sql . "<HR>";
		//		  echo "$sort_by, $sort_asc<br>";

		if($this->db->num_rows($res)) {
			while($row = $this->db->fetch_row($res)) {
				$t_ids[$row["ticket_id"]] = $row["thread_address_id"];
				$r_ids[$row["thread_address_id"]] = $row["thread_address_id"];
			}
		}


		///////////////// =============================================================
		//  HANDLE CUSTOM FIELDS [JAS]
		///////////////// =============================================================

		$c_fld_ids = array();
		$cust_select = array();
		$cust_join = array();

		if(!empty($this->columns))
		foreach($this->columns as $col) {
			if($col->column_type == "custom_field") {
				$cust_select[] = sprintf("v_%d.field_value as g_%d_custom_%d",
				$col->field_id,
				$col->group_id,
				$col->field_id
				);

				$cust_join[] = sprintf("LEFT JOIN field_group_values v_%d ON ".
				"(v_%d.entity_index = IF ( v_%d.entity_code =  'R', thr.thread_address_id, t.ticket_id ) ".
				"AND v_%d.field_id = %d)",
				$col->field_id,
				$col->field_id,
				$col->field_id,
				$col->field_id,
				$col->field_id
				);
			}
		}

		$use_custom_fields = false;
		if(!empty($cust_select) && !empty($cust_join))
		$use_custom_fields = true;


		///////////////// =============================================================
		//  FILL OUT THE RESULTS USING THE COLUMNS OF THE VIEW  [JAS]
		///////////////// =============================================================

		$use_company = false;
		if($this->columnExists("company_name") || $use_custom_fields)
		$use_company = true;

		$use_owner = false;
		if($this->columnExists("ticket_owner"))
		$use_owner = true;

		$sql = sprintf("SELECT t.ticket_id, t.ticket_subject, t.ticket_priority, t.ticket_spam_trained, t.last_reply_by_agent, ".
		"t.ticket_spam_probability, th.thread_date, thr.thread_received, t.ticket_status, t.ticket_due, ".
		"th.thread_address_id, t.min_thread_id, a.address_address, t.ticket_mask, t.ticket_time_worked as total_time_worked, ".
		"ad.address_address as requestor_address, ad.address_banned, q.queue_id, q.queue_name ".
		"%s ". // use company?
		"%s ". // use owner?
		"%s ". // custom field column?
		"FROM ticket t, thread th, thread thr, address a, address ad, queue q ".
		"%s ". // use company?
		"%s ". // use owner?
		"%s ". // custom field join?
		"WHERE 1 ".
		"AND t.max_thread_id = th.thread_id " .
		"AND t.min_thread_id = thr.thread_id ".
		"AND a.address_id = th.thread_address_id ".
		"AND ad.address_id = thr.thread_address_id ".
		"AND q.queue_id = t.ticket_queue_id ".
		"AND t.ticket_id IN (%s) ".
		"%s ".
		"ORDER BY %s %s",
			(($use_company) ? ", c.name as company_name" : ""),
			(($use_owner) ? ", u.user_login as ticket_owner" : ""),
			(($use_custom_fields) ? ("," . implode(",",$cust_select)) : ""),
			(($use_company) ? "LEFT JOIN public_gui_users pu ON (ad.public_user_id = pu.public_user_id) LEFT JOIN company c ON (pu.company_id = c.id)" : ""),
			(($use_owner) ? "LEFT JOIN user u ON (u.user_id = t.ticket_assigned_to_id)" : ""),
			(($use_custom_fields) ? (implode(" ",$cust_join)) : ""),
			implode(',',array_keys($t_ids)),
			(($use_custom_fields) ? "GROUP BY t.ticket_id" : ""),
			$sort_sql,
			$sort_asc
		);
		$result = $this->db->query($sql);

		if(!empty($search_join)) {
			$sql = sprintf("DROP TABLE IF EXISTS %s_s",
					$this->heap_name
				);
			$this->db->query($sql);
			$sql = sprintf("DROP TABLE IF EXISTS %s_c",
					$this->heap_name
				);
			$this->db->query($sql);
		}
		
		//          echo $sql . "<HR>";

		if($this->db->num_rows($result))
		while($ticket_row = $this->db->fetch_row($result))
		{
			$proc_args = new cer_TicketViewsProc($this);
			$proc_args->ticket_id = @$ticket_row["ticket_id"];
			$proc_args->ticket_status = @$ticket_row["ticket_status"];
			$proc_args->ticket_due = @$ticket_row["ticket_due"];
			$proc_args->ticket_mask = @$ticket_row["ticket_mask"];
			$proc_args->last_reply_by_agent = @$ticket_row["last_reply_by_agent"];
			$proc_args->queue_id = @$ticket_row["queue_id"];
			$proc_args->address_address = @$ticket_row["address_address"];
			$proc_args->requester_banned = @$ticket_row["address_banned"];
			$proc_args->ticket_spam_trained = @$ticket_row["ticket_spam_trained"];
			$proc_args->ticket_spam_probability = @$ticket_row["ticket_spam_probability"];
			$proc_args->min_thread_id = @$ticket_row["min_thread_id"];
			$proc_args->view_ptr = &$this;

			$row_data = array();

			if(!empty($this->columns))
			foreach($this->columns as $idx => $col)
			{
				$proc_args->col_ptr = &$this->columns[$idx];
				$row_data[$idx] = $col->execute_proc(@$ticket_row[$col->column_name],$proc_args);
			}
			array_push($this->rows,$row_data);
		}

	}

	function columnExists($col_name)
	{
		if(!empty($this->columns))
		foreach($this->columns as $idx => $col)
		{
			if($col->column_name == $col_name)
			return $idx;
		}

		return false;
	}

	function _determinePageURLs() {
		global $session; // clean up

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->view_page = @$view_prefs->vars[$this->view_slot."_p"];
		if(empty($this->view_page)) $this->view_page = 0;

		$p = $this->view_page;

		if($p > 0) { // [JAS]: Can we show a previous page?
		$this->show_prev = true;
		$this->view_prev_url = cer_href(sprintf("%s?%s&%s_p=%d",
		$this->page_name,
		$this->page_args,
		$this->view_slot,
		($p-1)
		));
		}

		if($this->show_of > $this->show_to) {
			$this->show_next = true;
			$this->view_next_url = cer_href(sprintf("%s?%s&%s_p=%d",
			$this->page_name,
			$this->page_args,
			$this->view_slot,
			($p+1)
			));
		}
	}

	function enableSearchActions()
	{
		global $priv; // clean up
		$cfg = CerConfiguration::getInstance();
		global $cer_hash;

		if(($priv->has_priv(ACL_TICKET_CHOWNER) ||
		$priv->has_priv(ACL_TICKET_CHSTATUS) ||
		$priv->has_priv(ACL_TICKET_CHQUEUE) ||
		$priv->has_priv(ACL_TICKET_BATCH,BITGROUP_2)))
		{
			if($priv->has_priv(ACL_TICKET_CHOWNER)) {
				$this->show_chowner = true;
				$this->show_chowner_options = array('-1' => " - " . LANG_LIST_CHANGE_OWNER . "? - ",
				'' => LANG_WORD_NOBODY);
				foreach($cer_hash->get_user_hash() as $user)
				{ $this->show_chowner_options[$user->user_id] = $user->user_login;	}
			}

			if($priv->has_priv(ACL_TICKET_CHSTATUS)) {
				$this->show_chstatus = true;
				$this->show_chstatus_options = array('-1' => " - " . LANG_LIST_CHANGE_STATUS . "? - ");
				foreach($cer_hash->get_status_hash() as $st => $status)
				{
					if(!$priv->has_priv(ACL_TICKET_KILL,BITGROUP_2) // [JAS]: Restrict the dead status
					&& $status == LANG_STATUS_DEAD)		// [jxdemel] for non-english case
					{ }
					else
					$this->show_chstatus_options[$st] = $status;
				}
			}

			if($priv->has_priv(ACL_TICKET_CHQUEUE)) {
				$this->show_chqueue = true;
				$this->show_chqueue_options = array('-1' => " - " . LANG_LIST_CHANGE_QUEUE . "? - ");

				if($cfg->settings["user_only_assign_own_queues"])
				$hash_set = HASH_Q_READWRITE;
				else
				$hash_set = HASH_Q_ALL;

				foreach($cer_hash->get_queue_hash($hash_set) as $queue)
				{ $this->show_chqueue_options[$queue->queue_id] = $queue->queue_name;	}
			}

			if($priv->has_priv(ACL_TICKET_BATCH,BITGROUP_2)) {
				$this->show_chaction = true;
			}
		}
	}
};


class cer_TicketViewBatch extends cer_TicketView {
	var $slot_tag = "bv";
	var $view_bind_page = "display.php";

	function cer_TicketViewBatch($vid="",$params) {
		$this->cer_TicketView($vid,$this->slot_tag,$params);
		//		$this->setPrefs();
	}

	function setPrefs() {
		global $session;
		global $ticket; // fix

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->filter_rows = @$view_prefs->vars[$this->view_slot."_filter_rows"];
		$this->filter_responded = @$view_prefs->vars[$this->view_slot."_filter_responded"];

		$this->show_options = !empty($session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_" . $this->slot_tag]) ? 1 : 0;

		$this->view_title_style = "boxtitle_blue";

		if(empty($this->filter_rows)) $this->filter_rows = 20;
		if(empty($this->filter_responded)) $this->filter_responded = 0;

		$this->page_args = "ticket=" . $ticket . "&mode=batch";

		$this->view_exclusive = true;
		$this->show_batch_actions = true;
	}

	function setViewDefaults()
	{
		global $session;

		$this->view_name = "Batch";
		$this->queues = "*";
		$this->view_only_assigned = 0;
		$this->view_adv_2line = 0;
		$this->view_adv_controls = 1;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		if(empty($view_prefs->vars["bv_sort_by"])) $view_prefs->vars["bv_sort_by"] = "ticket_due";
		if(empty($view_prefs->vars["bv_asc"])) $view_prefs->vars["bv_asc"] = 0;

		$this->hide_statuses = "";
		$this->column_string = "ticket_id,requestor_address,ticket_status,queue_name,ticket_owner,ticket_date,ticket_priority";
		$this->columns[0] = new cer_TicketViewColumn("checkbox",$this,"bids[]");
		$this->columns[1] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[2] = new cer_TicketViewColumn("ticket_id",$this);
		$this->columns[3] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[4] = new cer_TicketViewColumn("requestor_address",$this);
		$this->columns[5] = new cer_TicketViewColumn("ticket_status",$this);
		$this->columns[6] = new cer_TicketViewColumn("queue_name",$this);
		$this->columns[7] = new cer_TicketViewColumn("ticket_priority",$this);
		$this->columns[8] = new cer_TicketViewColumn("ticket_owner",$this);
		$this->columns[9] = new cer_TicketViewColumn("ticket_due",$this);
		//		$this->columns[9] = new cer_TicketViewColumn("thread_date",$this);
	}

	function _buildBatchList() {
		if(isset($this->params["batch_ids"]) && !empty($this->params["batch_ids"])) {
			return implode(',',$this->params["batch_ids"]);
		}

		return "'0'";
	}

};


class cer_TicketViewUnassigned extends cer_TicketView {
	var $slot_tag = "uv";
	var $view_bind_page = "index.php";

	function cer_TicketViewUnassigned($vid="",$params) {
		$this->cer_TicketView($vid,$this->slot_tag,$params);
		//		$this->setPrefs();
	}

	function setPrefs() {
		global $session;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->filter_rows = @$view_prefs->vars[$this->view_slot."_filter_rows"];
		$this->filter_responded = @$view_prefs->vars[$this->view_slot."_filter_responded"];

		$this->show_options = !empty($session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_" . $this->slot_tag]) ? 1 : 0;

		$this->view_title_style = "boxtitle_green_glass";

		if(empty($this->filter_rows)) $this->filter_rows = 10;
		if(empty($this->filter_responded)) $this->filter_responded = 0;

		$this->show_mass=true;
		$this->enableSearchActions();
	}

	function setViewDefaults()
	{
		global $session;

		$this->view_name = LANG_UNASSIGNED_TITLE;
		$this->queues = "*";
		$this->view_only_assigned = 2;
		$this->view_adv_2line = 1;
		$this->view_adv_controls = 1;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		if(empty($view_prefs->vars["uv_sort_by"])) $view_prefs->vars["uv_sort_by"] = "ticket_due";
		if(empty($view_prefs->vars["uv_asc"])) $view_prefs->vars["uv_asc"] = 0;

		$this->column_string = "ticket_id,queue_name,address_address,ticket_status,ticket_date";
		$this->columns[0] = new cer_TicketViewColumn("checkbox",$this,"bids[]");
		$this->columns[1] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[2] = new cer_TicketViewColumn("ticket_id",$this);
		$this->columns[3] = new cer_TicketViewColumn("address_address",$this);
		$this->columns[4] = new cer_TicketViewColumn("queue_name",$this);
		$this->columns[5] = new cer_TicketViewColumn("ticket_status",$this);
		$this->columns[6] = new cer_TicketViewColumn("ticket_priority",$this);
		$this->columns[7] = new cer_TicketViewColumn("thread_date",$this);
		$this->columns[8] = new cer_TicketViewColumn("ticket_due",$this);
	}
};


class cer_TicketViewAssigned extends cer_TicketView {
	var $slot_tag = "av";
	var $view_bind_page = "index.php";

	function cer_TicketViewAssigned($vid="",$params) {
		$this->cer_TicketView($vid,$this->slot_tag,$params);
		//		$this->setPrefs();
	}

	function setPrefs() {
		global $session;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->filter_rows = @$view_prefs->vars[$this->view_slot."_filter_rows"];
		$this->filter_responded = @$view_prefs->vars[$this->view_slot."_filter_responded"];

		$this->show_options = !empty($session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_" . $this->slot_tag]) ? 1 : 0;

		$this->view_title_style = "boxtitle_orange_glass";

		if(empty($this->filter_rows)) $this->filter_rows = 5;
		if(empty($this->filter_responded)) $this->filter_responded = 0;

		$this->show_mass=true;
		$this->enableSearchActions();
	}

	function setViewDefaults()
	{
		global $session;

		$this->view_name = LANG_ASSIGNED_TITLE . " " . $session->vars["login_handler"]->user_login;
		$this->queues = "*";
		$this->view_only_assigned = 1;
		$this->view_adv_2line = 0;
		$this->view_adv_controls = 1;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		if(empty($view_prefs->vars["av_sort_by"])) $view_prefs->vars["av_sort_by"] = "ticket_due";
		if(empty($view_prefs->vars["av_asc"])) $user_prefs->view_prefs->vars["av_asc"] = 0;

		$this->column_string = "ticket_id,queue_name,address_address,ticket_status,ticket_date,ticket_priority";
		$this->columns[0] = new cer_TicketViewColumn("checkbox",$this,"bids[]");
		$this->columns[1] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[2] = new cer_TicketViewColumn("ticket_id",$this);
		$this->columns[3] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[4] = new cer_TicketViewColumn("address_address",$this);
		$this->columns[5] = new cer_TicketViewColumn("queue_name",$this);
		$this->columns[6] = new cer_TicketViewColumn("ticket_status",$this);
		$this->columns[7] = new cer_TicketViewColumn("ticket_priority",$this);
		//		$this->columns[8] = new cer_TicketViewColumn("thread_date",$this);
		$this->columns[8] = new cer_TicketViewColumn("ticket_due",$this);
	}
};


class cer_TicketViewSearch extends cer_TicketView {
	var $slot_tag = "sv";
	var $view_bind_page = "ticket_list.php";

	function cer_TicketViewSearch($vid="",$params) {
		$this->cer_TicketView($vid,$this->slot_tag,$params);
		//		$this->setPrefs();
	}

	function setPrefs() {
		global $session;

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		$this->filter_rows = @$view_prefs->vars[$this->view_slot."_filter_rows"];
		$this->filter_responded = @$view_prefs->vars[$this->view_slot."_filter_responded"];

		$this->show_options = !empty($session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_" . $this->slot_tag]) ? 1 : 0;

		$this->view_title_style = "boxtitle_blue_glass";

		if(empty($this->filter_rows)) $this->filter_rows = 25;
		if(empty($this->filter_responded)) $this->filter_responded = 0;

		$this->show_modify = true;
		$this->enableSearchActions();
		$this->view_exclusive = true;
	}

	function setViewDefaults()
	{
		global $session;

		$this->view_name = "Tickets";
		$this->queues = "*";
		$this->view_only_assigned = 0;
		$this->view_adv_2line = 1;
		$this->view_adv_controls = 1;
		$this->hide_statuses = "";

		if(isset($view_prefs)) unset($view_prefs);
		$view_prefs = &$session->vars["login_handler"]->user_prefs->view_prefs;

		if(empty($view_prefs->vars["sv_sort_by"])) $view_prefs->vars["sv_sort_by"] = "ticket_due";
		if(empty($view_prefs->vars["sv_asc"])) $view_prefs->vars["sv_asc"] = 0;

		$this->column_string = "ticket_id,requestor_address,ticket_status,queue_name,ticket_owner,ticket_date,ticket_priority";
		$this->columns[0] = new cer_TicketViewColumn("checkbox",$this,"bids[]");
		$this->columns[1] = new cer_TicketViewColumn("ticket_subject",$this);
		$this->columns[2] = new cer_TicketViewColumn("ticket_id",$this);
		$this->columns[3] = new cer_TicketViewColumn("address_address",$this);
		$this->columns[4] = new cer_TicketViewColumn("ticket_status",$this);
		$this->columns[5] = new cer_TicketViewColumn("queue_name",$this);
		$this->columns[6] = new cer_TicketViewColumn("ticket_priority",$this);
		$this->columns[7] = new cer_TicketViewColumn("ticket_owner",$this);
		$this->columns[8] = new cer_TicketViewColumn("ticket_due",$this);
		//		$this->columns[8] = new cer_TicketViewColumn("thread_date",$this);
	}


	// [JAS]: Merge in any search filters on queue with the view preferences
	function _buildQueueList() {
		global $queue_access;
		$qid_sql = null;

		if(empty($queue_access)) $queue_access = new CER_QUEUE_ACCESS();

		// [JAS]: Replacement for the queue access linking, manipulate queue id lists.
		$user_queues = $queue_access->get_readable_qid_list();
		if($this->queues == "*") $this->queues = $user_queues;
		$qid_list = $queue_access->elements_in_both_lists($this->queues,$user_queues);

		$qid_sql = null;

		if(isset($this->params["search_queue"])) { // override
			// [JAS]: Make sure we're only letting people list the tickets in queues they can see
			$search_qids = (isset($this->params["search_queue"]) && !empty($this->params["search_queue"]))
			? $this->params["search_queue"] : $qid_list;
			$qid_sql = $queue_access->elements_in_both_lists($search_qids, $qid_list);

			// [mdf]: search_qids and qid_list are mutually exclusive, 
			//The user is searching in a queue that is not in this view... so no search results should display
			if(empty($qid_sql)) {
				$qid_sql = -1;
			}		
		}
		else { // view default
		if(@$this->queues != "*" && !empty($this->queues)) {
			$qid_sql = $qid_list;
		}
		else if (empty($this->queues)) {
			$qid_sql = '-1';
		}
		}

		return $qid_sql;
	}

	// [JAS]: Merge in any search filters on status with the view preferences
	// Merge any view exclusions with the desired search status value
	function _buildStatusList() {
		$show_status_sql = null;
		$status_list = $this->show_statuses;
		$hide_list = array();

		// [JAS]: Make sure we're setting the view defaults first.
		foreach (explode(",",$this->hide_statuses) as $st) {
			$hide_list[$st] = $st;
		}

		if (isset($this->params["search_status"])) {

			switch($this->params["search_status"]) {
				// Any
				case "":
				case "0":
				// defaults work
				break;

				// Any Active
				case "-1":
				$hide_list["awaiting-reply"] = "awaiting-reply";
				$hide_list["resolved"] = "resolved";
				$hide_list["dead"] = "dead";
				break;

				// A specific status
				default:
				foreach($this->show_statuses as $st) {
					if($st == $this->params["search_status"])
					continue;

					$hide_list[$st] = $st;
				}
				break;
			}

		}

		if (!empty($hide_list)) {
			foreach($hide_list as $hide) {
				unset($status_list[$hide]);
			}

			$show_status_sql = "'" . implode("','",$status_list) . "'";
		}


		return $show_status_sql;
	}

	// [JAS]: Now we don't only find the original sender, but we allow searches for any of the
	//	ticket requesters as well.  We have to do a pre-search query for requesters that match
	//  the sender substring (if given).
	function _buildSenderList() {
		$requester_sql = null;

		if($this->params["search_sender"]) { // override
		$sql = sprintf("SELECT r.address_id ".
		"FROM requestor r, address a ".
		"WHERE r.address_id = a.address_id ".
		"AND a.address_address LIKE '%%%s%%' ".
		"GROUP BY a.address_id",
		$this->params["search_sender"]
		);
		$req_res = $this->db->query($sql);

		if($this->db->num_rows($req_res)) {
			$req_ary = array();

			while($row = $this->db->fetch_row($req_res)) {
				$req_ary[] = $row["address_id"];
			}

			$requester_sql = implode(",",$req_ary);
		}
		}

		return $requester_sql;
	}

	function _buildAssignedList() {
		global $session;

		$assigned_sql = null;

		if($this->params["search_owner"] != -1 && $this->params["search_owner"] != 0) {
			$assigned_sql = $this->params["search_owner"];
		}
		if($this->params["search_owner"] == 0) {
			$assigned_sql = "'0'";
		}
		else {
			if($this->view_only_assigned == 1) {
				$assigned_sql = $session->vars["login_handler"]->user_id;
			}
			else if($this->view_only_assigned == 2) {
				$assigned_sql = "'0'";
			}
		}

		return $assigned_sql;
	}

	function _buildCompanyList() {
		$company_sql = null;

		if($this->params["search_company"]) {
			$company_sql = $this->params["search_company"];
		}

		return $company_sql;
	}

	function _buildSubjectSearchWords() {
		$rows = 0;
		
		if($this->params["search_subject"]) {
			$rows = $this->_parseSearchString($this->params["search_subject"],1);
		}

		return $rows;
	}

	function _buildContentSearchWords() {
		$rows = 0;
		
		if($this->params["search_content"]) {
			$rows = $this->_parseSearchString($this->params["search_content"],0);
		}

		return $rows;
	}

	function _parseSearchString($str,$is_subject=0) {
		global $cerberus_db;
		global $session;
		$cfg = CerConfiguration::getInstance();
		
		$cer_search = new cer_SearchIndex();
		$sql = "";
		
		$content_string = strtolower($str);
		$content_string = cer_Whitespace::mergeWhitespace($content_string);
		
		$search_terms = explode(" ",$content_string);
		$terms_required = array();
		$terms_optional = array();
		$terms_excluded = array();
		$terms_required_str = "";
		$terms_optional_str = "";
		$terms_excluded_str = "";
		
		if(!empty($search_terms))
		foreach($search_terms as $w) {
			$oper = substr($w,0,1);
			$word = substr($w,1);
			switch($oper) {
				case "+":
					$terms_required[] = $word;
					break;
				case "-":
					$terms_excluded[] = $word;
					break;
				default:
					$terms_optional[] = $oper . $word;
					break;
			}
		}
		
		if(!empty($terms_required)) {
			$terms_pre = count($terms_required);
			$terms_required_str = implode(" ",$terms_required);
			$cer_search->indexWords($terms_required_str, $cfg->settings["search_index_numbers"], 1);
			$terms_required = $cer_search->loadWordIDs(1);
			if(count($terms_required) < $terms_pre) $terms_required = array(-99);
//			echo "REQ: "; print_r($terms_required);echo "<BR>";
		}
		
		if(!empty($terms_optional)) {
			$terms_optional_str = implode(" ",$terms_optional);
			$cer_search->indexWords($terms_optional_str, $cfg->settings["search_index_numbers"], 1);
			$terms_optional = $cer_search->loadWordIDs(1);
//			echo "OPT: ";print_r($terms_optional);echo "<BR>";
		}
		
		if(!empty($terms_excluded)) {
			$terms_excluded_str = implode(" ",$terms_excluded);
			$cer_search->indexWords($terms_excluded_str, $cfg->settings["search_index_numbers"], 1);
			$terms_excluded = $cer_search->loadWordIDs(1);
//			echo "NEQ: ";print_r($terms_excluded);echo "<BR>";
		}

		$req_count = count($terms_required);
		$req_add = 0;
		$nuke_count = 0;
		
		if(!empty($terms_required)) {
			$req_list = implode(",",$terms_required);
			if($terms_required[0] != -99) $nuke_count += $req_count;
			if($terms_required[0] != -99) $req_add = $req_count;
		}
		
		$heap_table = $this->heap_name . (($is_subject) ? "_s" : "_c");
		
		$sql = "DROP TABLE IF EXISTS `". $heap_table ."`";
		$cerberus_db->query($sql);
			
		$sql = "CREATE TABLE `".$heap_table."` TYPE=HEAP SELECT ticket_id, count( word_id )  AS hit_count, " .
                        " 0 AS optional_count FROM  `search_index` %s GROUP BY ticket_id ".
                        (!empty($req_list) ? " HAVING hit_count = $req_count " : "");
                $where = "WHERE 1 " . (!empty($req_list) ? " AND word_id IN ($req_list) " : "") .
                        ((!empty($req_list) && !empty($is_subject)) ? " AND in_subject = 1 " : "");
                $where = ($where == "WHERE 1 " && !count($terms_excluded)) ? ' WHERE 0 ' : $where;
//		echo sprintf($sql, $where) . "<BR>";		
		$cerberus_db->query(sprintf($sql, $where));
		
		$sql = "ALTER TABLE `".$heap_table."` ADD PRIMARY KEY (ticket_id);";
		$cerberus_db->query($sql);
		
		if(!empty($terms_optional)) {
			$opt_list = implode(",",$terms_optional);
			$sql = "REPLACE INTO `".$heap_table."` SELECT w.ticket_id, count(w.word_id) + $req_add AS hit_count, count(w.word_id) AS optional_count FROM `search_index` w WHERE word_id IN ($opt_list) " . (!empty($is_subject) ? " AND in_subject = 1 " : "") . " GROUP BY ticket_id;";			
			$cerberus_db->query($sql);
			// [JSJ]: Clean out optionals if we didn't hit on our requireds
			if($req_add > 0) {
				$sql = "DELETE FROM `".$heap_table."` WHERE hit_count = optional_count";
				$cerberus_db->query($sql);
			}
		}
		
		if(!empty($terms_excluded)) {
			$exc_list = implode(",",$terms_excluded);			
			$sql = "REPLACE INTO `".$heap_table."` SELECT w.ticket_id, 0, 0 FROM search_index w WHERE w.word_id in ($exc_list) " . (!empty($is_subject) ? " AND in_subject = 1 " : "");
			$cerberus_db->query($sql);
		}

		// [JAS]: This needs to be a bit more complex later so it can tell that we're deleting optional 
		// 		matches.  Right now n optionals that pass req_count will stick.
//		if($req_add > 0) {
			$sql = "DELETE FROM `".$heap_table."` WHERE hit_count < " . (($nuke_count) ? $nuke_count : 1);
			$cerberus_db->query($sql);
//			echo $sql . "<BR>";		
//		}
		
		$sql = "SELECT ticket_id FROM `".$heap_table."`";
		$res = $cerberus_db->query($sql);
		$rows = $cerberus_db->num_rows($res);
		
		$rows = empty($rows) ? -1 : $rows;
		
		return $rows;
	}
	
	function _buildDateSearch() {
		$date_sql = null;

		// [JAS]: If we're not in advanced mode, bail out.
		if(!$this->params["advsearch"])
		return $date_sql;

		if($this->params["search_date"]
		&& $this->params["search_fdate"]
		&& $this->params["search_tdate"]) {

			$from_date = new cer_DateTime($this->params["search_fdate"]);
			$to_date = new cer_DateTime($this->params["search_tdate"]);
			$f_string = $from_date->getDate("%Y-%m-%d %H:%M:%S");
			$t_string = $to_date->getDate("%Y-%m-%d %H:%M:%S");

			if($this->params["search_date"] == 1)
			$date_sql = " AND thr.thread_date BETWEEN '$f_string' AND DATE_ADD('$t_string',INTERVAL \"1\" DAY)";
			elseif($this->params["search_date"] == 2)
			$date_sql = " AND th.thread_date BETWEEN '$f_string' AND DATE_ADD('$t_string',INTERVAL \"1\" DAY)";
		}

		return $date_sql;
	}

	function _buildCustomFieldSearch() {
		$field_from_sql = null;
		$field_where_sql = null;

		// [JAS]: If we're not in advanced mode, bail out.
		if(!$this->params["advsearch"])
		return $date_sql;

		if($this->params["search_field_ids"]) {
			$field_from = array();
			$field_where = array();
			$ids = explode(',',$this->params["search_field_ids"]);

			if(!empty($ids))
			foreach($ids as $id) {
				$val = @$this->params["search_field_" . $id];
				$type = @$this->field_handler->field_to_template[$id]->fields[$id]->field_type;

				if(!empty($val)) {

					$field_from[] = sprintf("`field_group_values` v%d",
					$id
					);

					// [JAS]: [TODO] Later we can include contacts and companies in the search if we know we have company linked.
//					$field_where[] = sprintf("(v%d.field_id = %d AND v%d.entity_index = IF (v%d.entity_code = 'R', thr.thread_address_id,IF (v%d.entity_code = 'C', ad.public_user_id, IF (v%d.entity_code = 'T', t.ticket_id,pu.company_id ) ) ) AND v%d.field_value %s %s)",
					$field_where[] = sprintf("(v%d.field_id = %d AND v%d.entity_code IN ('T','R') AND v%d.entity_index = IF (v%d.entity_code = 'R', thr.thread_address_id,t.ticket_id) AND v%d.field_value %s %s)",
					$id,
					$id,
					$id,
					$id,
					$id,

					$id,
					(($type == 'D') ? "=" : "LIKE"),
					(($type == 'D') ? $val : "'%" . $val . "%'")
					);
				}
			} // foreach $id
		}

		if(!empty($field_from))
		$field_from_sql = implode(',', $field_from);

		if(!empty($field_where))
		$field_where_sql = implode(" AND ", $field_where);

		return array($field_from_sql, $field_where_sql);
	}

};

class cer_TicketViewColumnTable
{
	var $table_name = null;
	var $table_prefix = null;
	var $sort_sql = null;
	var $group_by = null;
	var $selects = 0;

	function cer_TicketViewColumnTable($name,$prefix,$sql,$group_by="") {
		$this->table_name = $name;
		$this->table_prefix = $prefix;
		$this->sort_sql = $sql;
		$this->group_by = $group_by;
	}
};

class cer_TicketViewColumn
{
	var $column_name = null; // db column name
	var $column_heading = null; // column display name
	var $column_align = "left"; // column alignment (center,right,left)
	var $column_type = "normal";
	var $column_url = null; // column url for sorting
	var $column_extras = null; // extras such as nowrap, etc.
	var $column_sortable = true;
	var $column_proc = null; // column function (date to age, queue link, etc)
	var $table = null;
	var $table_field_name = null;

	var $field_id = 0;
	var $group_id = 0;

	var $parent_view = null; // parent view pointer
	var $element_name = null; // if we're drawing a <FORM> element, name it this

	function cer_TicketViewColumn($c_name,&$view_obj,$name="")
	{
		$this->column_name = $c_name;
		$this->parent_view = &$view_obj;
		$this->element_name = $name;

		$this->assign_default_proc($c_name);
		$this->set_column_url();
	}

	function setTable($table) {
		$ptr = &$this->parent_view->tables[$table];
		$ptr->selects++;
		return $ptr;
	}

	// [JAS]: Handle Column Specific Procedures
	function assign_default_proc($c_name)
	{
		switch($c_name)
		{
			case "checkbox":
			$this->column_proc = "view_proc_checkbox";
			$this->column_heading = strtolower(LANG_WORD_ALL);
			$this->column_align = "center";
			break;

			case "ticket_id":
			$this->column_proc = "view_proc_print_id";
			$this->column_heading = "#";
			$this->column_extras = "nowrap";
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_id";
			break;

			case "ticket_subject":
			$this->column_proc = "view_proc_print_subject_link";
			$this->column_heading = LANG_WORD_SUBJECT;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_subject";
			break;

			case "queue_name":
			$this->column_proc = "view_proc_print_queue_link";
			$this->column_heading = LANG_WORD_QUEUE;
			$this->table = $this->setTable("queue");
			$this->table_field_name = "queue_name";
			break;

			case "ticket_status":
			$this->column_proc = "view_proc_print_translated_status";
			$this->column_heading = LANG_WORD_STATUS;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_status";
			break;

			case "thread_received":
			$this->column_proc = "view_proc_date_to_age";
			$this->column_heading = "Created";
			$this->table = $this->setTable("thread_first");
			$this->table_field_name = "thread_received";
			break;

			case "thread_date":
			$this->column_proc = "view_proc_date_to_age";
			$this->column_heading = LANG_WORD_AGE;
			$this->table = $this->setTable("thread_last");
			$this->table_field_name = "thread_date";
			break;

			case "ticket_due":
			$this->column_proc = "view_proc_due_to_age";
			$this->column_heading = LANG_WORD_DUE;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_due";
			break;

			case "ticket_priority":
			$this->column_proc = "view_proc_print_priority_as_string"; // [JSJ: Print priority string instead of numerical value
			$this->column_heading = LANG_WORD_PRIORITY;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_priority";
			break;

			case "ticket_owner":
			$this->column_proc = "view_proc_print_small";
			$this->column_heading = LANG_WORD_OWNER;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_assigned_to_id";
			break;

			case "address_address":
			$this->column_proc = "view_proc_print_email_address";
			$this->column_heading = LANG_WORD_WROTE_LAST;
			$this->table = $this->setTable("address_last");
			$this->table_field_name = "address_address";
			break;

			case "requestor_address":
			$this->column_proc = "view_proc_print_email_address";
			$this->column_heading = LANG_WORD_REQUESTER;
			$this->table = $this->setTable("address_first");
			$this->table_field_name = "address_address";
			break;

			case "company_name":
			$this->column_proc = "view_proc_print_small";
			$this->column_heading = LANG_WORD_COMPANY;
			$this->table = $this->setTable("company");
			$this->table_field_name = "name";
			break;

			case "total_time_worked":
			$this->column_proc = "view_proc_print_worked";
			$this->column_heading = LANG_WORD_WORKED;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_time_worked";
			break;

			case "spam_probability":
			$this->column_proc = "view_proc_print_spam_probability";
			$this->column_heading = LANG_WORD_SPAM;
			$this->column_sortable = false;
			break;

			case "ticket_spam_trained":
			$this->column_proc = "view_proc_print_spam_trained";
			$this->column_heading = LANG_WORD_TRAINING;
			$this->table = $this->setTable("ticket");
			$this->table_field_name = "ticket_spam_trained";
			break;

			default:
			{
				// [JAS]: If we're looking at a group custom field column.
				if(substr($c_name,0,2) == "g_") {
					list($group_id,$fld_id) = sscanf($c_name,"g_%d_custom_%d");
					$this->column_type = "custom_field";
					$this->column_heading = $this->parent_view->field_handler->group_templates[$group_id]->fields[$fld_id]->field_name;
					$this->field_id = $fld_id;
					$this->group_id = $group_id;
					$this->column_proc = "view_proc_print_custom_field";

					$sort_sql = sprintf("SELECT t.ticket_id, thr.thread_address_id, v_%d.field_value AS g_%d_custom_%d ".
					"FROM ticket t, thread thr, thread th, address a, address ad, queue q %%s %%s %%s ". // [JAS]: leave the double %%, we're injecting vals later
					"LEFT JOIN public_gui_users pu ON (a.public_user_id = pu.public_user_id) ".
					"LEFT JOIN company c ON (pu.company_id = c.id) ".
					"LEFT JOIN field_group_values v_%d ".
					"ON ( v_%d.entity_index =  IF ( v_%d.entity_code =  'R', thr.thread_address_id, t.ticket_id ) ".
					"AND v_%d.field_id = %d ) ".
					"WHERE t.min_thread_id = thr.thread_id ".
					"AND t.max_thread_id = th.thread_id ".
					"AND a.address_id = thr.thread_address_id ".
					"AND ad.address_id = th.thread_address_id ".
					"AND t.ticket_queue_id = q.queue_id ",
					$fld_id,
					$group_id,
					$fld_id,
					$fld_id,
					$fld_id,
					$fld_id,
					$fld_id,
					$fld_id
					);

					$table_name = "v_" . $fld_id;

					$this->parent_view->tables[$table_name] = new cer_TicketViewColumnTable("field_group_values",$table_name,$sort_sql,"t.ticket_id");
					$this->table = $this->setTable($table_name);
					$this->table_field_name = $c_name;
				}
				break;
			}
		}
	}

	function set_column_url()
	{
		global $session; // clean up

		$slot = $this->parent_view->view_slot;

		switch($slot)
		{
			case "uv": // unassigned or assigned homepage view
			case "av":
			if($this->column_name != "checkbox")
			{
				$this->column_url = cer_href(sprintf("%s?%s_p=%d&%s_asc=%d&%s_sort_by=%s",
				@$this->parent_view->page_name,
				$slot,
				0,
				$slot,
				((($session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"]==$this->column_name) && $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"]==1)?"0":"1"),
				$slot,
				$this->column_name
				));
			}
			else if($this->column_name == "checkbox")
			{
				$this->column_url = sprintf("javascript:checkAllToggle_%s();",
				$this->parent_view->view_slot
				);
			}
			break;

			case "sv": // search results / queue view
			if($this->column_name != "checkbox")
			{
				$this->column_url = cer_href(sprintf("%s?%s_p=%d&%s_asc=%d&%s_sort_by=%s",
				$this->parent_view->page_name,
				$slot,
				0,
				$slot,
				((($session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"]==$this->column_name) && $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"]==1)?"0":"1"),
				$slot,
				$this->column_name
				));
			}
			else if($this->column_name == "checkbox")
			{
				$this->column_url = sprintf("javascript:checkAllToggle_%s();",
				$this->parent_view->view_slot
				);
			}
			break;

			case "bv": // batch view
			//			global $mode; // fix
			//			global $ticket; // fix

			if($this->column_name != "checkbox")
			{
				$this->column_url = cer_href(sprintf("%s?%s&%s_p=%d&%s_asc=%d&%s_sort_by=%s",
				$this->parent_view->page_name,
				$this->parent_view->page_args,
				$slot,
				0,
				$slot,
				((($session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_sort_by"]==$this->column_name) && $session->vars["login_handler"]->user_prefs->view_prefs->vars[$slot."_asc"]==1)?"0":"1"),
				$slot,
				$this->column_name
				));
			}
			else if($this->column_name == "checkbox")
			{
				$this->column_url = sprintf("javascript:checkAllToggle_%s();",
				$this->parent_view->view_slot
				);

			}
			break;

			default:
			$this->column_url = "#";
			break;
		}
	}

	// [JAS]: $arg = value of row's field, $proc_args = object of row's variables
	//		that are usable in the procedure
	function execute_proc($arg="",$proc_args="")
	{
		if(!empty($this->column_proc))
		{ return call_user_func($this->column_proc,$arg,$proc_args); }
		else
		{ return ""; }
	}
};

?>
