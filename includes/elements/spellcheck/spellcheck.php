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
/*
Original Patch provided by Billy Cook (wrcook@clemson.edu)
Modified for use by Jeff Standen (jeff@webgroupmedia.com)
*/

// [BGH]: added version check for pspell because we need the regexp offset in 4.3.0 or greater
// [BGH]: this is in this file so this file will know if pspell is loaded if it is called from
// [BGH]: a remote server. IE: spellcheck via cerberusweb.com functionality
$pspell_loaded = false;
if(-1!=version_compare( phpversion(), "4.3.0")) {
	if(extension_loaded("pspell")) {
		$pspell_loaded = true;
	}
}

@$remote = $_REQUEST["remote"];

if($pspell_loaded) {
	$pspell_link = pspell_new ("en", "", "", "", PSPELL_FAST|PSPELL_RUN_TOGETHER);

	$mystr = stripslashes($_REQUEST['spellstring']);
	$js = "";

	// [wrcook] replace backslashes with their html entity
	$mystr = str_replace("\\", "&#092;", $mystr);

	// can't have newlines or carriage returns in javascript string
	$mystr = str_replace("\r", "", $mystr);
	$mystr = str_replace("\n", "_|_", $mystr);

	$mystr = trim($mystr);

	// original that doesn't work with html
	preg_match_all ( "/[[:alpha:]']+|<[^>]+>|&[^;\ ]+;/", $mystr, $alphas, PREG_OFFSET_CAPTURE|PREG_PATTERN_ORDER);

	// this has to be done _after_ the matching.  it messes up the
	// indexing otherwise.
	$mystr = str_replace("\"", "\\\"", $mystr);

	$js .= 'var mispstr = "'.$mystr.'";'."\n";

	$js .= 'var misps = Array(';
	$curindex = 0;
	$foundone = false;
	$sugs_found = 0;

	for($i = 0; $i < sizeof($alphas[0]); $i++) {
		// if the word is an html tag or entity then skip it
		if (preg_match("/<[^>]+>|&[^;\ ]+;/", $alphas[0][$i][0]))  {
			continue; // skip this one
		}
		if (!pspell_check ($pspell_link, $alphas[0][$i][0])) {
			$foundone = true;
			$js .= "new misp('" . str_replace("'", "\\'",$alphas[0][$i][0]) . "',". $alphas[0][$i][1] . "," . (strlen($alphas[0][$i][0]) + ($alphas[0][$i][1] - 1) ) . ",[";

			$suggestions = pspell_suggest ($pspell_link, $alphas[0][$i][0]);

			if(is_array($suggestions)) {
				foreach ($suggestions as $suggestion) {
					//echo $suggestion . "<br>";
					$sugs[] = "'".str_replace("'", "\\'", $suggestion)."'";
				}
			}

			if (isset($sugs) && sizeof($sugs)) {
				$js .= implode(",", $sugs);
			}
			unset($sugs);

			$js .= "]),\n";
			$sugs_found = 1;
		}
	}
	if ($sugs_found)
	$js = substr($js, 0, -2);
	$js .= ");";
}

if("1"==$remote) {
	echo $js;
}
else {
	require("spellwin.php");
}

?>