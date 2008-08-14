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
| File: attachment_send.php
|
| Purpose: Sends a file from the database to the users computer through 
|   the browser.  Uses browser to prompt for filename to "Save As".
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

define("NO_OB_CALLBACK",true); // [JAS]: Leave this true

require_once("site.config.php");

@$file_id = $_REQUEST["file_id"];
@$thread_id = $_REQUEST["thread_id"];

if(!isset($file_id) || !isset($thread_id)) exit;

$sql = sprintf("SELECT file_name, file_size FROM thread_attachments WHERE file_id = '%d' AND thread_id = '%d'", $file_id, $thread_id);
$file_dump = $cerberus_db->query($sql,false);

if($cerberus_db->num_rows($file_dump) > 0) {
   $file_info = $cerberus_db->fetch_row($file_dump);
}
else {
   echo "Attachment file ID is invalid!";
   exit();
}
// pkolmann: Att Mozilla to inline Browsers and make it RFC2183 right!
$attachment = ((strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) ||
 (strstr($_SERVER["HTTP_USER_AGENT"], "Mozilla"))) ?
 "inline" : "attachment"; 
$fexp=explode('.',$file_info[0]);
$ext=$fexp[sizeof($fexp)-1];
 
header("Expires: Mon, 26 Nov 1962 00:00:00 GMT\n");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT\n");
header("Cache-control: private\n");
header('Pragma: no-cache\n');

$mimetype = array( 
    'bmp'=>'image/bmp',
    'doc'=>'application/msword', 
    'gif'=>'image/gif',
    'gz'=>'application/x-gzip-compressed',
    'htm'=>'text/html', 
    'html'=>'text/html', 
    'jpg'=>'image/jpeg', 
    'mp3'=>'audio/x-mp3',
    'pdf'=>'application/pdf', 
    'php'=>'text/plain', 
    'swf'=>'application/x-shockwave-flash',
    'tar'=>'application/x-tar',
    'tgz'=>'application/x-gzip-compressed',
    'tif'=>'image/tiff',
    'tiff'=>'image/tiff',
    'txt'=>'text/plain', 
    'vsd'=>'application/vnd.visio',
    'vss'=>'application/vnd.visio',
    'vst'=>'application/vnd.visio',
    'vsw'=>'application/vnd.visio',
    'wav'=>'audio/x-wav',
    'xls'=>'application/vnd.ms-excel',
    'xml'=>'text/xml',
    'zip'=>'application/x-zip-compressed' 
    ); 
        
if(isset($mimetype[$ext]))
	header("Content-Type: " . $mimetype[$ext] . "\n");
else
	header("Content-Type: application/octet-stream\n");

// [JSJ]: Adding ORDER BY to the query as per suggestion from username 'Martin von Herm' on forums.
$sql = sprintf("SELECT part_content FROM thread_attachments_parts WHERE file_id = '%d' ORDER BY part_id", $file_id);
$file_parts_res = $cerberus_db->query($sql,false);

header("Content-transfer-encoding: binary\n"); 

$tmp_dir = FILESYSTEM_PATH . "tempdir";
$temp_dir = str_replace(chr(47),chr(92).chr(92),$tmp_dir);
$temp_file_name = tempnam($temp_dir,"cerbfile_"); 

$fp = fopen($temp_file_name,"wb");

while($file_part = $cerberus_db->fetch_row($file_parts_res))
{
	$chunk_len = strlen(str_replace(chr(0)," ",$file_part[0])); // [JAS]: Don't stop counting on a NULL
	fwrite($fp,$file_part[0],$chunk_len);
}

// Make sure all data is written to disk before getting the file size [gavin@eleventeenth.com]
fflush($fp);

//$temp_file_size = filesize($temp_file_name);
$fstat = fstat($fp);
$temp_file_size = $fstat["size"];
header("Content-Length: " . $temp_file_size . "\n");

$head = "Content-Disposition: $attachment; filename=\"".$file_info[0]."\"\n";
header($head);

if(@$fp) fclose($fp);
$fp = fopen($temp_file_name,"rb");

fpassthru($fp);

if(@$fp) @fclose($fp);
@unlink($temp_file_name);

unset($file_part);
unset($file_parts_res);

exit;
?>