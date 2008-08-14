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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/bitflags.php");

// BITGROUP 1
define("ACL_USER_CREATE",				BITFLAG_1);
define("ACL_USER_EDIT",					BITFLAG_2);
define("ACL_USER_DELETE",				BITFLAG_3);
define("ACL_QUEUE_CREATE",				BITFLAG_4);
define("ACL_QUEUE_EDIT",				BITFLAG_5);
define("ACL_QUEUE_DELETE",				BITFLAG_6);
define("ACL_KB_ARTICLE_CREATE",			BITFLAG_7);
define("ACL_KB_ARTICLE_EDIT",			BITFLAG_8);
define("ACL_KB_ARTICLE_DELETE",			BITFLAG_9);
define("ACL_KB_CATEGORY_CREATE",		BITFLAG_10);
define("ACL_KB_CATEGORY_EDIT",			BITFLAG_11);
define("ACL_KB_CATEGORY_DELETE",		BITFLAG_12);
define("ACL_UPLOAD_LOGO",				BITFLAG_13);
define("ACL_MAINT_PURGE_DEAD",			BITFLAG_14);
define("ACL_MAINT_OPTIMIZE",			BITFLAG_15);
define("ACL_EMAIL_BLOCK_SENDERS",		BITFLAG_16);
define("ACL_EMAIL_EXPORT",				BITFLAG_17);
//define("ACL_OPTIONS_UPLOAD_KEY",		BITFLAG_18);  // deprecated bit
define("ACL_OPTIONS_FEEDBACK",			BITFLAG_19);
define("ACL_OPTIONS_REPORT_BUG",		BITFLAG_20);
define("ACL_TICKET_CHOWNER",			BITFLAG_21);
define("ACL_TICKET_CHSTATUS",			BITFLAG_22);
define("ACL_TICKET_CHPRIORITY",			BITFLAG_23);
define("ACL_TICKET_CHQUEUE",			BITFLAG_24);
define("ACL_TICKET_CHSUBJECT",			BITFLAG_25);
define("ACL_TICKET_TAKE",				BITFLAG_26);
define("ACL_PREFS_USER",				BITFLAG_27);
define("ACL_KB_VIEW",					BITFLAG_28);
define("ACL_KB_SEARCH",					BITFLAG_29);
define("ACL_CONFIG_MENU",				BITFLAG_30);
define("ACL_HIDE_REQUESTOR_EMAILS",		BITFLAG_31);

// BITGROUP 2
define("ACL_GROUPS_CREATE",				BITFLAG_1);
define("ACL_GROUPS_EDIT",				BITFLAG_2);
define("ACL_GROUPS_DELETE",				BITFLAG_3);
define("ACL_VIEWS_CREATE",				BITFLAG_4);
define("ACL_VIEWS_EDIT",				BITFLAG_5);
define("ACL_VIEWS_DELETE",				BITFLAG_6);
//define("ACL_REQUESTOR_FLDS_EDIT",		BITFLAG_7); // deprecated bit
//define("ACL_REQUESTOR_FLDS_ADMIN",	BITFLAG_8); // deprecated bit
//define("ACL_TICKET_FLDS_EDIT",		BITFLAG_9); // deprecated bit
//define("ACL_TICKET_FLDS_ADMIN",		BITFLAG_10); // deprecated bit
define("ACL_TICKET_BATCH",				BITFLAG_11);
define("ACL_AUDIT_LOG",					BITFLAG_12);
define("ACL_MAILRULE_CREATE",			BITFLAG_13);
define("ACL_MAILRULE_EDIT",				BITFLAG_14);
define("ACL_MAILRULE_DELETE",			BITFLAG_15);
define("ACL_PARSER_LOG",				BITFLAG_16);
define("ACL_TICKET_CLONE",				BITFLAG_17);
define("ACL_GLOBAL_SETTINGS",			BITFLAG_18);
define("ACL_REINDEX_THREADS",			BITFLAG_19);
define("ACL_REINDEX_ARTICLES",			BITFLAG_20);
define("ACL_KB_COMMENT_EDITOR",			BITFLAG_21);
define("ACL_TICKET_KILL",				BITFLAG_22);
define("ACL_ADD_REQUESTER",				BITFLAG_23);
define("ACL_FORWARD_THREAD",			BITFLAG_24);
define("ACL_TICKET_MERGE",				BITFLAG_25);
define("ACL_PUBLIC_GUI",				BITFLAG_26);
define("ACL_SLA_PLANS",					BITFLAG_27);
define("ACL_SLA_SCHEDULES",				BITFLAG_28);
define("ACL_CUSTOM_FIELDS",				BITFLAG_29);
define("ACL_CUSTOM_FIELDS_ENTRY",		BITFLAG_30);
define("ACL_CONTACTS",					BITFLAG_31);

// BITGROUP 3
define("ACL_REPORTS",					BITFLAG_1);
define("ACL_CONTACTS_CONTACT_MANAGE",	BITFLAG_2);
define("ACL_CONTACTS_COMPANY_MANAGE",	BITFLAG_3);
define("ACL_TIME_TRACK_CREATE",			BITFLAG_4);
define("ACL_CONTACTS_COMPANY_DELETE",	BITFLAG_5);
define("ACL_CONTACTS_SLA_ASSIGN",		BITFLAG_6);
define("ACL_CONTACTS_CONTACT_ASSIGN",	BITFLAG_7);
define("ACL_CONTACTS_EMAIL_ASSIGN",		BITFLAG_8);
define("ACL_CREATE_TICKET",				BITFLAG_9);
define("ACL_TIME_TRACK_VIEW_OWN",		BITFLAG_10);
define("ACL_TIME_TRACK_VIEW_ALL",		BITFLAG_11);
define("ACL_TIME_TRACK_EDIT_OWN",		BITFLAG_12);
define("ACL_TIME_TRACK_EDIT_ALL",		BITFLAG_13);
define("ACL_TIME_TRACK_DELETE_OWN",		BITFLAG_14);
define("ACL_TIME_TRACK_DELETE_ALL",		BITFLAG_15);
define("ACL_QUEUE_CATCHALL",			BITFLAG_16);

?>