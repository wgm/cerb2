<?php

// Miscellaneous constants

define("XML_DATE_FORMAT", "d-M-y h:ia");
define("XML_DATE_FORMAT2", "%c");


// Conditionally defined constants

if(!defined('CHAT_REQUEST_TIMEOUT')) {
   // After this many seconds with no response a visitor is forwarded to e-mail
   define('CHAT_REQUEST_TIMEOUT', 180);
}

if(!defined('SESSION_EXPIRE_SECS')) {
   // An visitor is idle after this many seconds w/o a page hit
   define('SESSION_EXPIRE_SECS', 300);
}

if(!defined('CHAT_JOIN_HISTORY_HRS')) {
   define('CHAT_JOIN_HISTORY_HRS', 3);
}

if(!defined('AGENT_SESSION_EXPIRATION_SECS')) {
   define('AGENT_SESSION_EXPIRATION_SECS', 300);
}

if(!defined('DEFAULT_NUM_OWNED_TICKETS')) {
   define('DEFAULT_NUM_OWNED_TICKETS', 25);
}

if(!defined('NO_AGENT_IN_ROOM_VISITOR_INACTIVITY_TIMEOUT')) {
   define('NO_AGENT_IN_ROOM_VISITOR_INACTIVITY_TIMEOUT', 300);
}

if(!defined('DISPATCHER_HIGH_PRIORITY_TICKET')) {
   define('DISPATCHER_HIGH_PRIORITY_TICKET', 75);
}



// Dispatcher methods ticket weighting (conditionally defined to allow to be overridden in config)
if(!defined('DISPATCHER_WEIGHT_BY_DATE')) {
   define('DISPATCHER_WEIGHT_BY_DATE', 10);
}
if(!defined('DISPATCHER_WEIGHT_CUSTOMER_REPLY')) {
   define('DISPATCHER_WEIGHT_CUSTOMER_REPLY', 5);
}
if(!defined('DISPATCHER_WEIGHT_RECOMMENDED_AGENT')) {
   define('DISPATCHER_WEIGHT_RECOMMENDED_AGENT', 10);
}
if(!defined('DISPATCHER_WEIGHT_RECOMMENDED_TEAM')) {
   define('DISPATCHER_WEIGHT_RECOMMENDED_TEAM', 5);
}
if(!defined('DISPATCHER_WEIGHT_RECOMMENDED_DEPARTMENT')) {
   define('DISPATCHER_WEIGHT_RECOMMENDED_DEPARTMENT', 2);
}
if(!defined('DISPATCHER_WEIGHT_HIGH_PRIORITY_TICKET')) {
   define('DISPATCHER_WEIGHT_HIGH_PRIORITY_TICKET', 4);
}
if(!defined('DISPATCHER_WEIGHT_OVERDUE_TICKET')) {
   define('DISPATCHER_WEIGHT_OVERDUE_TICKET', 4);
}
if(!defined('DISPATCHER_WEIGHT_SKILL_MATCH_25')) {
   define('DISPATCHER_WEIGHT_SKILL_MATCH_25', 3);
}
if(!defined('DISPATCHER_WEIGHT_SKILL_MATCH_50')) {
   define('DISPATCHER_WEIGHT_SKILL_MATCH_50', 5);
}
if(!defined('DISPATCHER_WEIGHT_SKILL_MATCH_75')) {
   define('DISPATCHER_WEIGHT_SKILL_MATCH_75', 10);
}
if(!defined('DISPATCHER_WEIGHT_SKILL_MATCH_100')) {
   define('DISPATCHER_WEIGHT_SKILL_MATCH_100', 15);
}
if(!defined('DISPATCHER_WEIGHT_GENERIC')) {
   define('DISPATCHER_WEIGHT_GENERIC', 1);
}
if(!defined('DISPATCHER_WEIGHT_OTHER_AGENT_DELAY')) {
   // This is when someone else said give it back to me at XYZ time, 
   // it's less likely to go to someone else, but still can go to someone else
   // if I'm not around and thus the ticket doesn't fall through the cracks.
   define('DISPATCHER_WEIGHT_OTHER_AGENT_DELAY', -3);
}



// Bit constants

// Agents
define("AGENT_STATUS_OFFLINE", 0);
define("AGENT_STATUS_AWAY", 1);
define("AGENT_STATUS_ONLINE", 2);

// Messages
define("MESSAGE_CODE_VISITOR", 0);
define("MESSAGE_CODE_AGENT", 1);
define("MESSAGE_CODE_BROADCAST", 2);

// Rooms
define("JOIN_FLAG_SILENT", 1); // bit flag (2^0)
define("JOIN_FLAG_TYPING", 2); // bit flag (2^0)

// Visitors
define("VISITOR_STATUS_BROWSING",0);
define("VISITOR_STATUS_CHAT_REQUEST",1);
define("VISITOR_STATUS_CHATTING",2);
define("VISITOR_STATUS_INVITED",3);
define("VISITOR_STATUS_REJECT_INVITE",4);

// Notifications
define("EVENT_TYPE_CANCEL_EVENT", 1);
define("EVENT_TYPE_CHAT_REQUEST", 2);
define("EVENT_TYPE_CHAT_REQUEST_HANDLED", 4);
define("EVENT_TYPE_CHAT_INVITE_ACCEPTED", 8);
define("EVENT_TYPE_CHAT_INVITE_REJECTED", 16);
define("EVENT_TYPE_USER_LOGIN", 32);
define("EVENT_TYPE_USER_LOGOFF", 64);

// Dispatcher delays
define("DISPATCHER_DELAY_DATE", 1);
define("DISPATCHER_DELAY_CUSTOMER_REPLY", 2);

