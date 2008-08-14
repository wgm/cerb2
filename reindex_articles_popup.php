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
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramKB.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");

$from = $_REQUEST["from"];

$sql = "SELECT MAX(kb_id) FROM knowledgebase k";
$num_res = $cerberus_db->query($sql,false);
$row = $cerberus_db->fetch_row($num_res);
$total = $row[0];

$step = 20;

if(empty($from) || !isset($from)) $from=0;
if($from == 0)
{
	$sql = "DELETE FROM search_index_kb";
	$cerberus_db->query($sql);
	$sql = "DELETE FROM `trigram_to_kb`";
	$cerberus_db->query($sql);	
}

$to = $from + $step;
if($to > $total) $to = $total;

if($from >= $total || $total == 0) {
	echo "<html><body>";
}
else {
	echo "<html><body Onload='nextSet();'>";
}
echo "Indexing <b>$from</b> to <b>$to</b> of <b>$total</b> knowledgebase articles.&nbsp; &nbsp;<b>";
if($to < $total) echo "<font color='red'>PLEASE WAIT... DO NOT ABORT!</font></b>";
echo "<br>";
echo "<table width='100%'>";
$x = (0==$total ? 0 : (int)(($from / $total)*100));
$y = 100 - $x; 
echo "<tr><td width='$x%' bgcolor='#00FF00'>&nbsp;</td><td width='$y%' bgcolor='#c0c0c0'>&nbsp;</td></tr>";
echo "</table>";

flush();

$cer_trigram = new cer_TrigramKB();
$cer_trigram->reindexArticles($from,$step);
$cer_search = new cer_SearchIndexKB();
$cer_search->reindexArticles($from,$step);

if($from >= $total || $total == 0)
{
	echo "<script>alert('CERBERUS: Re-index done!');</script>";
	exit;
}

$from = $to;

echo "<script>function nextSet() { document.location='reindex_articles_popup.php?from=$from&sid=".$session->session_id."'; }</script></body></html>";

exit;
?>