<?php
          
          if(!isset($module)) $module = "";
          switch ($module)
          {
          	case "addresses":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_addresses.php");
          		break;
          	}
          	case "settings":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_global_settings.php");
          		break;
          	}
          	case "statuses":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_ticket_statuses.php");
          		break;
          	}
          	case "log":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_parser_log.php");
          		break;
          	}
          	case "plugins":
          	{
          		if(isset($pgid) && $pgid!="")
              		require(FILESYSTEM_PATH . "includes/elements/config_plugins_edit.php");
              	else
              		require(FILESYSTEM_PATH . "includes/elements/config_plugins.php");
              	
          		break;
          	}
          	case "rules":
          	{
          		if(isset($prid) && $prid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_parser_rules_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_parser_rules.php"); }
          		break;
          	}
          	case "queues":
          	{
          		if(isset($pqid) && $pqid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_queues_edit.php"); }
          		else if(isset($qids) && !isset($destination_queue))
          		{ require(FILESYSTEM_PATH . "includes/elements/config_queues_delete.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_queues.php"); }
          		break;
          	}
          	case "queue_catchall":
          	{
       			require(FILESYSTEM_PATH . "includes/elements/config_queue_catchall.php");
          		break;
          	}
          	case "search_index":
          	{
          		switch($action)
          		{
          		case "threads":
          			require(FILESYSTEM_PATH . "includes/elements/config_reindex_threads.php");
          			break;
          		case "articles":
          			require(FILESYSTEM_PATH . "includes/elements/config_reindex_articles.php");
          			break;
          		}
          		break;
          	}
          	case "users":
          	{
          		if(isset($puid) && $puid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_users_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_users.php"); }
          		break;
          	}
          	case "sla":
          	{
          		if(isset($pslid) && $pslid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_sla_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_sla.php"); }
          		break;
          	}
          	case "schedules":
          	{
          		if(isset($pslid) && $pslid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_schedule_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_schedule.php"); }
          		break;
          	}
			case "custom_fields":                  	
          	{
          		if(isset($pgid) && $pgid != "")
          			require(FILESYSTEM_PATH . "includes/elements/config_custom_field_groups_edit.php");
          		else
          			require(FILESYSTEM_PATH . "includes/elements/config_custom_field_groups.php");
          		
          		break;
          	}
			case "custom_field_bindings":                  	
          	{
      			require(FILESYSTEM_PATH . "includes/elements/config_custom_field_bindings.php");
          		break;
          	}
          	case "groups":
          	{
          		if(isset($pgid) && $pgid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_user_groups_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_user_groups.php"); }
          		break;
          	}
          	case "kbase":
          	{
          		if(isset($pkbid) && $pkbid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_kbase_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_kbase.php"); }
          		break;
          	}
          	case "kbase_comments":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_kbase_comments.php");
          		break;
          	}
          	case "branding":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_branding.php");
          		break;
          	}
          	case "key":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_key.php");
          		break;
          	}
          	case "maintenance":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_maintenance.php");
          		break;
          	}
          	case "maintenance_optimize":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_maintenance_optimize.php");
          		break;
          	}
          	case "maintenance_repair":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_maintenance_repair.php");
          		break;
          	}
          	case "maintenance_tempdir":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_maintenance_tempdir.php");
          		break;
          	}
          	case "maintenance_attachments":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_maintenance_attachments.php");
          		break;
          	}
          	case "bug":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_report_bug.php");
          		break;
          	}
          	case "feedback":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_give_feedback.php");
          		break;
          	}
          	case "export":
          	{
          		require(FILESYSTEM_PATH . "includes/elements/config_address_export.php");
          		break;
          	}
          	case "public_gui_profiles":
          	{
          		if(isset($pfid) && $pfid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_public_gui_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_public_gui.php"); }
          		break;
          	}
          	case "public_gui_fields":
          	{
          		if(isset($pfid) && $pfid!="")
          		{ require(FILESYSTEM_PATH . "includes/elements/config_public_gui_fields_edit.php"); }
          		else
          		{ require(FILESYSTEM_PATH . "includes/elements/config_public_gui_fields.php"); }
          		break;
          	}
          	default:
          	{
          		$sql = "SELECT count(*) as comment_count FROM knowledgebase_comments WHERE kb_comment_approved = 0";
          		$com_result = $cerberus_db->query($sql);
          		$com_data = $cerberus_db->fetch_row($com_result);
          		
          		$sql = "SELECT count(*) as ticket_count FROM ticket WHERE ticket_status = 'dead'";
          		$tik_result = $cerberus_db->query($sql);
          		$tik_data = $cerberus_db->fetch_row($tik_result);
          		
				require_once(FILESYSTEM_PATH . "cerberus-api/utility/tempdir/cer_Tempdir.class.php");
          		$cer_tempdir = new cer_Tempdir();
          		
				require_once(FILESYSTEM_PATH . "cerberus-api/attachments/cer_AttachmentManager.class.php");
				$cer_attachments = new cer_AttachmentManager();
          		
          		if(!isset($MACHTYPE)) $MACHTYPE = "";
          		echo "<span class=\"cer_display_header\">" . LANG_CONFIG_GROUPS_EDIT_CONFIG . "</span><br>" .
          		"<span class=\"cer_maintable_text\">" . LANG_CONFIG_MENU_NOTE . "</span><br><br>" .
          		"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"550\" bgcolor=\"BABABA\">" .
          		"<tr class=\"boxtitle_gray_glass\"><td>&nbsp;Helpdesk Environment</td></tr>" .
          		"<tr><td bgcolor=\"#ECECEC\">" .

				// [PK]: Philipp Kolmann (kolmann@zid.tuwien.ac.at)
				// Make purge infos a href if user has permission to clear tickets/tempfiles
				"<span class=\"cer_maintable_text\">&nbsp;<img src=\"includes/images/crystal/16x16/icon_trashcan.gif\" align=\"absmiddle\">&nbsp;" . "There are <b>".((@$tik_data["ticket_count"])?$tik_data["ticket_count"]:"0")."</b> dead tickets pending ".
				(($priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))?"<a href=\"".cer_href("configuration.php?module=maintenance")."\" class=\"cer_maintable_text\">purge</a>":"purge") . ".</span><br>" .
           		"<span class=\"cer_maintable_text\">&nbsp;<img src=\"includes/images/crystal/16x16/icon_file.gif\" align=\"absmiddle\">&nbsp;" . "There are <b>".number_format($cer_tempdir->total_files,0,"",",")."</b> temporary files (" . display_bytes_size($cer_tempdir->total_sizes) . ") pending ".
				(($priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))?"<a href=\"".cer_href("configuration.php?module=maintenance_tempdir")."\" class=\"cer_maintable_text\">purge</a>":"purge") . ".</span><br>" .
           		"<span class=\"cer_maintable_text\">&nbsp;<img src=\"includes/images/crystal/16x16/icon_attachment_tar.gif\" align=\"absmiddle\">&nbsp;" . "There are <b>".number_format($cer_attachments->getTotalAttachments(),0,"",",")."</b> attachments (" . display_bytes_size($cer_attachments->getTotalAttachmentsSize()) . ") pending ".
				(($priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1))?"<a href=\"".cer_href("configuration.php?module=maintenance_attachments")."\" class=\"cer_maintable_text\">clean-up</a>":"clean-up") . ".</span><br>" .
           		"<span class=\"cer_maintable_text\">&nbsp;<img src=\"includes/images/crystal/16x16/icon_new_comment.gif\" align=\"absmiddle\">&nbsp;" . "There are <b>".((@$com_data["comment_count"])?$com_data["comment_count"]:"0")."</b> knowledgebase comments pending ".
				(($priv->has_priv(ACL_KB_COMMENT_EDITOR,BITGROUP_2) && $cfg->settings["show_kb"])?"<a href=\"".cer_href("configuration.php?module=kbase_comments")."\" class=\"cer_maintable_text\">review</a>":"review") . ".</span><br>" .
				
          		"</td></tr>" .
          		"</table><br>" .
          		"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"550\" bgcolor=\"BABABA\">" .
          		"<tr class=\"boxtitle_gray_glass\"><td>&nbsp;Client/Server Environment</td></tr>" .
          		"<tr><td bgcolor=\"#ECECEC\">" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . LANG_CONFIG_GUI_VERSION . ":</span><span class=\"cer_maintable_text\"> " . GUI_VERSION . " </span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Cerberus Parser Version" . ":</span><span class=\"cer_maintable_text\"> " . @$cfg->settings["parser_version"] . " </span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . LANG_CONFIG_SERVER_SOFTWARE . ":</span><span class=\"cer_maintable_text\"> ". @$_SERVER["SERVER_SOFTWARE"] . "  MySQL/" . @mysql_get_client_info() ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . LANG_CONFIG_MACHINE_TYPE . ":</span><span class=\"cer_maintable_text\"> ". @PHP_OS ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . LANG_CONFIG_CLIENT_BROWSER . ":</span><span class=\"cer_maintable_text\"> ". @$_SERVER["HTTP_USER_AGENT"] ."</span><br>" .
          		"</td></tr>" .
          		"</table><br>" .
          		"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"550\" bgcolor=\"BABABA\">" .
          		"<tr class=\"boxtitle_gray_glass\"><td>&nbsp;Developers</td></tr>" .
          		"<tr><td bgcolor=\"#ECECEC\">" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Jeff Standen" . ":</span><span class=\"cer_maintable_text\"> ". "Project Manager, Lead GUI/PHP Programmer" ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Ben Halsted" . ":</span><span class=\"cer_maintable_text\"> ". "Lead C/Parser Programmer" ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Jeremy Johnstone" . ":</span><span class=\"cer_maintable_text\"> ". "Developer, XML/Framework" ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Mike Fogg" . ":</span><span class=\"cer_maintable_text\"> ". "PHP/Java Developer" ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Trent Ramseyer" . ":</span><span class=\"cer_maintable_text\"> ". "PHP Developer, Designer" ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Darren Sugita" . ":</span><span class=\"cer_maintable_text\"> ". "Q/A Tester, Support, Docs" ."</span><br>" .
          		"<span class=\"cer_maintable_heading\">&nbsp;" . "Jerry Kanoholani" . ":</span><span class=\"cer_maintable_text\"> ". "Q/A Tester, Sales, Feedback" ."</span><br>" .
          		"</td></tr>" .
          		"</table><br>" .
          		"<span class=\"cer_maintable_heading\">Useful Links</span><br>" .
          		"<a href=\"http://www.cerberusweb.com/\" target=\"_blank\" class=\"cer_maintable_text\">Cerberus Helpdesk Website</a><br>" .
          		"<a href=\"http://forum.cerberusweb.com/\" target=\"_blank\" class=\"cer_maintable_text\">Cerberus Helpdesk Forums</a><br>" .
          		"<a href=\"http://www.cerberusweb.com/manual/\" target=\"_blank\" class=\"cer_maintable_text\">Cerberus Helpdesk Online Manual</a><br>" .
          		"<a href=\"http://www.webgroupmedia.com/\" target=\"_blank\" class=\"cer_maintable_text\">WebGroup Media, LLC. Website</a><br>" .
          		"<a href=\"http://www.php.net/\" target=\"_blank\" class=\"cer_maintable_text\">PHP Website</a><br>" .
          		"<a href=\"http://www.mysql.com/\" target=\"_blank\" class=\"cer_maintable_text\">MySQL Website</a><br>" .
          		"<a href=\"http://smarty.php.net/\" target=\"_blank\" class=\"cer_maintable_text\">Smarty Templates Website</a><br>" .
          		"<a href=\"http://php.weblogs.com/adodb/\" target=\"_blank\" class=\"cer_maintable_text\">ADODB Website</a><br>" .
          		"<a href=\"http://www.everaldo.com/crystal.html\" target=\"_blank\" class=\"cer_maintable_text\">Everaldo.com (Graphic Artist)</a><br>" .
          		"<br><span class=\"cer_maintable_heading\">Copyright (c) 2005, WebGroup Media LLC.  All rights reserved.</span><br><br>";
          		if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
          		break;
          	}
          }
?>
