<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
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
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_OB_CALLBACK",true); // [JAS]: Leave this true

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");

//session_name("cerberus_reindex");
//session_start();

@$from = (int)$_REQUEST["from"];
@$from_mktime = $_REQUEST["from_mktime"];
@$total = $_REQUEST["total"];
@$total_words = $_REQUEST["total_words"];
@$step = $_REQUEST["step"];

if(empty($step) || 1>$step) {
	$step=50;
}

if(empty($total)) {
	$sql = "SELECT MAX(t.ticket_id) FROM ticket t";
	$num_res = $cerberus_db->query($sql,false);
	$row = $cerberus_db->fetch_row($num_res);
	$total = $row[0];
}

if(empty($from_mktime)) {
	$from_mktime = mktime();
}

if(empty($from) || !isset($from)) {
	$from = $total - $step;
	if($from < 0) $from = 0;
	$to = $total;
}
else {
	$to = $from + $step;
}

// [BGH]: indexing backwards..
if($from+$step == $total)
{
	$sql = "DELETE FROM `search_index`";
	$cerberus_db->query($sql);
}


if($step<$to) {
	echo "<html><body Onload='nextSet();'>";
}
else {
	echo "<html><body>";
}


echo "<font color='#FF9204'><b>CERBERUS HELPDESK</b></font><br>";
echo "Indexing <b>" . ($total-$to) . "</b> to <B>" . ($total-(0<$from?$from:0)) . "</B> of <b>$total</b> tickets.&nbsp; &nbsp;<b>";
if(0 < $to) echo "<font color='red'>PLEASE WAIT... DO NOT ABORT!</font></b>";
echo "<br>";
echo "<table width='100%'>";
if(0 == $total) {
	$x = 100;
} else {
	$x = (int)(($total-$to+$step) / $total * 100);
}
if($x > 100) $x = 100;
$y = 100 - $x;
if($y < 0) $y = 0;
echo "<tr><td width='$x%' bgcolor='#00FF00' align='center'><font color='#FFFFFF'><B>$x%</B></font></td><td width='$y%' bgcolor='#c0c0c0'>&nbsp;</td></tr>";
echo "</table>";
echo "Time Elapsed: " . cer_DateTimeFormat::secsAsEnglishString(mktime() - $from_mktime) . " (as of last batch of $step)<BR>";
if(!empty($total_words) && 0<(mktime() - $from_mktime)) {
	echo "Words processed per second: " . number_format(($total_words / (mktime() - $from_mktime)),0) . "<br>";
}
flush();

$sql = "SELECT t.ticket_id from ticket t where t.ticket_id > $from AND t.ticket_id <= $to ORDER BY t.ticket_id DESC";
$rows = $cerberus_db->query($sql);

$thread_handler = new cer_ThreadContentHandler();
$cer_search = new cer_SearchIndexEmail();
//$cer_search->wordcache->loadCache($_SESSION["word_cache"]);

// get to work!
if($cerberus_db->num_rows($rows)) {
	while($row = $cerberus_db->fetch_row($rows)) {
		$ticket_id = $row["ticket_id"];
		$this_words = 0;
		
		$thread_handler->loadTicketContentDB($ticket_id);

		$cer_search->indexSingleTicketSubject($ticket_id); // [BGH]: Subject has to go first
		$this_words += count($cer_search->wordarray);

		$cer_search->indexSingleTicket($ticket_id, 1, $thread_handler->threads);
		$this_words += count($cer_search->wordarray);
		
		unset($thread_handler->threads);
		
		$total_words += $this_words;
	}
}

//$word_cache = $cer_search->wordcache->saveCache();
//$_SESSION["word_cache"] = $word_cache;

if(0 > $from || 0 == $total)
{
	echo "<script>alert('CERBERUS: Re-index done!');</script>";
	exit;
}

$cerberus_db->close();


$from = $from - $step;
echo "<script>function nextSet() { document.location='reindex_threads_popup.php?step=".$step."&what=".$what."&total=".$total."&from=".$from."&from_mktime=".$from_mktime."&total_words=".$total_words."'; }</script></body></html>";

?>