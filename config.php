<?php

// Database connection information
// Type your information in between the single quotes ('s) below

define("DB_SERVER",'localhost');   
define("DB_NAME",'c2');
define("DB_USER",'c2');
define("DB_PASS",'c2');


// [JAS]: IPs that we'll allow to view upgrade.php.  
//   Add yours here at the end, or replace a 0.0.0.0
//   Partial IP masks are allowed.

$authorized_ips = array("127.0",
                                   "192.168.1",
                                   "0.0.0.0",
                                   "0.0.0.0",
                                   "0.0.0.0"
                                   );


// SMTP Authentication for outbound mail server
// Type your information in between the single quotes ('s) below
// Leave empty if you don't know what this is, or don't need it.

define('SMTP_AUTH_USERNAME', '');
define('SMTP_AUTH_PASSWORD', '');


