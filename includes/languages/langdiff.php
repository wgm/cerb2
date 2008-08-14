<?php
// [JAS]: Find out what defines we haven't updated in the language resources 
//	using the English file as the benchmark.

function parseLine($line) {
	
	preg_match("/^define\(\"(.*?)\",(.*?)\);(.*?)$/i",$line, $matches);
	
	if(count($matches) == 4) {
		$def = $matches[1];
		$val = $matches[2];
	}
	
	if(!empty($def) && !empty($val)) {
		return array($def,$val);
	}
	else {
		return array();
	}
}

function readFileDefines($fp) {
	$defines = array();
	$line_num = 0;
	$hits = 0;
	
	while(!feof($fp)) {
		$def = null;
		$val = null;
		
		$line = fgets($fp,8192);
		$args = parseLine($line);
		
		++$line_num;
		
		if(!empty($args)) {
			$defines[$args[0]] = array($args[1],$line_num);
		}
	}
	
	return $defines;
}

$lang1 = "en/strings.php";
$fp1 = fopen($lang1,"rb");
$fp1_defines = readFileDefines($fp1);
@fclose($fp1);

$foreign_langs = array("bp","ch","cz","de","es","fr","it","nl","no","sk","ru");

echo "<h3>Comparing to English Resource File</h3>";

foreach($foreign_langs as $lang) {
	echo "<b>Lang: $lang</b> (languages/$lang/strings.php)<ul>";
	
	$lang2 = "$lang/strings.php";
	$fp2 = fopen($lang2,"rb");
	$fp2_defines = readFileDefines($fp2);
	@fclose($fp2);
	
	$diff_defines = array();
	$last_def = null;
	
	// Missing defines (compared to English)
	foreach($fp1_defines as $def => $para) {
		list($val,$lnum) = $para;
		if(!isset($fp2_defines[$def])) {
			echo "<li>+ <b>$def</b>";
			echo "<br>define(&quot;$def&quot;," . htmlspecialchars($val) . ");";
			if(!empty($last_def)) echo "<br><i>after <b>$last_def</b> (line ~$lnum)</i>";
			echo "</li>";
		}
		$last_def = $def;
	}
	
	// Extra defines (compared to English)
	foreach($fp2_defines as $def => $para) {
		list($val,$lnum) = $para;
		if(!isset($fp1_defines[$def])) {
			echo "<li>- <b>$def</b>";
			if(!empty($last_def)) echo "<br><i>after <b>$last_def</b> (line ~$lnum)</i>";
			echo "</li>";
		}
	}
	
	echo "</ul>";
}

?>