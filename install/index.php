<?php
require_once getcwd() . "/../site.config.php";
?>
<html>
<head>
<title>Cerberus Helpdesk - Installation Checker</title>
<style type="text/css">
<!--
.install_option {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; color: #333333}
.install_value_ok { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #009900 }
.install_value_fail { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #CC0000}
-->
</style>
</head>
<body>

<span class="install_option">
<?php
$fatal = false;
$ivo = "<span class='install_value_ok'>";
$ivf = "<span class='install_value_fail'>";
$sc = "</span>";

echo "<b>Cerberus Helpdesk Installer</b><br><br>";

echo "Checking PHP Version... " . $ivo.PHP_VERSION.$sc . " ";
if(version_compare(PHP_VERSION,"4.2.0") >=0 ) echo $ivo."(ok)".$sc; else {echo $ivf."(4.2.0 or higher required)".$sc; $fatal = true;}
echo "<br>";

echo "Checking System Information... " .$ivo. PHP_OS .$sc. "<br>";

echo "Checking PHP Server API... " . $ivo. php_sapi_name().$sc . "<br>";

echo "Checking Path to 'php.ini'... " . $ivo.PHP_CONFIG_FILE_PATH.$sc . "<br>";

echo "<br>";

echo "Checking safe_mode... "; 
$val = ini_get("safe_mode");
echo $ivo . ((!empty($val) || $val==1) ? "On" : "Off") . $sc;
echo "<br>";

echo "Checking short_tags... "; 
$val = ini_get("short_open_tag");
echo $ivo . ((!empty($val) || $val==1) ? "On" : "Off") . $sc;
echo "<br>";

echo "Checking file_uploads...";
$val = ini_get("file_uploads");
echo ((!empty($val) || $val==1) ? $ivo . "On" : $ivf . "Off - Please turn on file_uplaods in the php.ini file<br>If this is not enabled Cerberus can not receive email properly.") . $sc;
if(empty($val)) $fatal=true;
echo "<br>";

echo "Checking upload_tmp_dir...";
$val = ini_get("upload_tmp_dir");
echo ((!empty($val) || $val==1) ? $ivo . "Set" : $ivf . "not set! (set in php.ini if attachments don't work)") . $sc;
//if(empty($val)) $fatal=true;  // [JAS]: This isn't fatal.
echo "<br>";

echo "Checking upload_max_filesize...";
$val = ini_get("upload_max_filesize");
echo ((!empty($val) || $val==1) ? $ivo . "Max. upload/attachment size: " . $val . " (increase in php.ini)" : $ivf . "empty!") . $sc;
if(empty($val)) $fatal=true;
echo "<br>";

echo "Checking post_max_size...";
$val = ini_get("post_max_size");
echo ((!empty($val) || $val==1) ? $ivo . "Max. e-mail (w/ attachments) size: " . $val . " (increase in php.ini)" : $ivf . "empty!") . $sc;
if(empty($val)) $fatal=true;
echo "<br>";

echo "<br>";

echo "Checking for MySQL... ";
$mysql_loaded = extension_loaded("mysql");
if($mysql_loaded) echo $ivo."Version " . mysql_get_client_info() .$sc; else { echo $ivf."not installed!".$sc; $fatal=true; }
echo "<br>";

if($mysql_loaded) {
	echo "Checking Database Setup...<br>";
	echo "&nbsp;&nbsp;Host: <b>" . DB_SERVER . "</b><br>";
	echo "&nbsp;&nbsp;Name: <b>" . DB_NAME . "</b><br>";  
	
	echo "Checking Database Connection... ";
	@$db = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
	$db_error = mysql_error();
	if(strlen($db_error)!=0) {
		echo $ivf.$db_error.$sc; $fatal=true; 
	}

	if(strlen($db_error)==0) {
		@mysql_select_db(DB_NAME);
		$db_error = mysql_error();
		if(strlen($db_error)!=0) {
			echo $ivf.$db_error.$sc; $fatal=true;
		}
	}

	if(strlen($db_error)==0) {
		@$result = mysql_query("SHOW TABLES");
		$db_error = mysql_error();
		if(strlen($db_error)!=0) {
			echo $ivf.$db_error.$sc; $fatal=true;
		}
	}
	
	if(!$fatal) {
		echo $ivo."yes".$sc; 
	}

} 

echo "<br><br>";

// [BGH]: added version check for pspell because we need the regexp offset in 4.3.0 or greater
echo "Checking for PSpell (spellchecker)... ";
$pspell_loaded = false;
if(extension_loaded("pspell")) { 
	$pspell_loaded=true;
	echo $ivo."found!".$sc; 
	if(-1!=version_compare( phpversion(), "4.3.0")) {
		echo $ivo." and PHP Version OK!".$sc; 
	}
	else { 
		echo $ivf." and PHP Version must be greater or equal to 4.3.0 to use Spell Checking".$sc;
	}	
} 
else { 
	echo $ivo."not installed!".$sc;
}

echo "<br><br>";

echo "Checking if FILESYSTEM_PATH (" . FILESYSTEM_PATH . ") exists... "; 
if(is_dir(FILESYSTEM_PATH)) echo $ivo."yes".$sc; else { $fatal=true; echo $ivf."no (check site.config.php)".$sc; }

echo "<br>";

echo "Checking FILESYSTEM_PATH trailing slash... "; 
$last_char = substr(FILESYSTEM_PATH,-1);
if($last_char == "\\" || $last_char == "/") echo $ivo."yes".$sc; else { $fatal=true; echo $ivf."no (check site.config.php)".$sc; }

echo "<br>";

echo "Checking for Cerberus GUI Files in FILESYSTEM_PATH (" . FILESYSTEM_PATH . ")... "; 
if(file_exists(FILESYSTEM_PATH . "site.config.php")) echo $ivo."yes".$sc; else { $fatal=true; echo $ivf."no (check site.config.php)".$sc; }

echo "<br><br>";

echo "Checking if 'logo.gif' is writeable... "; 
$logo_path = FILESYSTEM_PATH . "logo.gif";
if(is_writable($logo_path)) echo $ivo."yes".$sc; else echo $ivf."no".$sc; 

echo "<br>";

echo "Checking if 'templates_c' directory is writeable... "; 
$template_path = FILESYSTEM_PATH . "templates_c";
$fp = @fopen($template_path . "/test.deleteme.php", "w");
if(null==$fp) {
	$fatal=true; echo $ivf."no".$sc;
}
else {
	fclose($fp);
	@unlink($template_path . "/test.deleteme.php");
	echo $ivo."yes".$sc;
}
	
echo "<br>";

echo "Checking if 'tempdir' is writeable... "; 
$tmp_path = FILESYSTEM_PATH . "tempdir";
$fp = @fopen($tmp_path . "/test.deleteme.php", "w");
if(null==$fp) {
	$fatal=true; echo $ivf."no".$sc;
}
else {
	fclose($fp);
	@unlink($tmp_path . "/test.deleteme.php");
	echo $ivo."yes".$sc;
}


echo "<br><br>";

if(!$fatal)
	{ echo $ivo."No fatal errors detected.  Proceed with configuration by deleting the 'install' directory and running <a href='../upgrade.php' class='install_value_ok'>upgrade.php</a>".$sc; }
else
	{ echo $ivf."Fatal errors detected.  Please correct the above red items and reload.".$sc; }
	
?>
</span>
</body>
</html>