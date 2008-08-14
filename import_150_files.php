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
|
| File: import_150_files.php
|
| Purpose: This script imports the 1.5.0 file attachment structures to
|	the new, more flexible, 2.0.0+ database structures.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");

@$from = $_REQUEST["from"];
@$form_submit = $_REQUEST["form_submit"];
@$file_count = $_REQUEST["file_count"];

// ======[ WARNING: Do not edit below this line unless you know what you're doing ]=====

define("FILE_CHUNK_SIZE",512000);
define("FILES_PER_RUN",20);

if($session->vars["login_handler"]->user_superuser != 1) die("Cerberus [ERROR]: This script *must* be run as superuser.");

if($from == "")
{
?>
<html><body>
<form action="import_150_files.php" method="post">
<img src="logo.gif" border=0><br>
<br>
<span class="cer_maintable_text">You are running the Cerberus Helpdesk <b>1.5.0</b> to <b>2.0.0</b> file attachment conversion script. 
Clicking <b>Proceed</b> will begin the process.  
<font color="#FF0000"><b>This may take several minutes depending on the size of your database</b></font>.<br>
<br>
<b>NOTE:</b> Once this is done running, it is recommended you delete this script (import_150_files.php) and the old file attachment table from your
database (thread_file).<br></span>
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="import">
<input type="hidden" name="from" value="0">
<br>
<input type="submit" value="Proceed &gt;&gt;">
<input type="button" value="Cancel" OnClick="javascript:document.location='index.php?sid=<?php echo $session->session_id; ?>';">
</form>
</body></html>
<?php
	exit;
}
else
{
	if(empty($file_count)) {
		$sql = "SELECT count(f.file_id) As file_count " .
			"FROM thread_file f WHERE f.file_name != 'message_source.txt' ORDER BY f.file_id ASC";
		$file_res = $cerberus_db->query($sql);
		
		if(!$file_row = $cerberus_db->grab_first_row($file_res)) die("Cerberus [ERROR]: Pre 2.0.0 file attachment tables not found.  This script may have already been run.");
		
		$file_count = $file_row["file_count"];
	}
		
	if($file_count == 0) die("Cerberus [ERROR]: No Pre 2.0.0 file attachments found to import.  This script may already have run.");

	$next_from = $from + FILES_PER_RUN;
	
	if($next_from > $file_count-1)
	{
		?>
		<html>
		<body OnLoad="javascript:next_set();">
		<img src="logo.gif" border=0><br>
		<br>
		<span class="cer_maintable_text"><b>Import Complete!</b> You should delete this script (<b><i>import_150_files.php</i></b>) 
		and DROP the <b><i>thread_file</i></b> table in your database.<br><br>
		<b>NOTE:</b> <font color="#FF0000">Running this script again will result in <b>duplicate</b> attachments.</font></span>
		</body>
		</html>
		<?php
		exit;
	}
?>
<html>
<body OnLoad="javascript:next_set();">
<img src="logo.gif" border=0><br>
<br>
<span class="cer_maintable_text"><b>Importing Files...</b><br><br></span>
<?php
	$progress_bar_void = 100 - round(($from/$file_count)*100);
	$progress_bar = 100 - $progress_bar_void;
	?>
	<span class="cer_maintable_text"><b>Import Progress:</b></span><br>
	<table border="1" cellpadding="0" cellspacing="0" width="100%"  bordercolor="#000000">
		<tr>
			<td width="<?php echo $progress_bar; ?>%" bgcolor="#00FF00">&nbsp;</td>
			<td width="<?php echo $progress_bar_void; ?>%" bgcolor="#CCCCCC">&nbsp;</td>
		</tr>
	</table>
	<br>
	<?php
}

$sql = "SELECT f.thread_id, f.file_name, f.file_size, f.file_content " .
	"FROM thread_file f WHERE f.file_name != 'message_source.txt' ORDER BY f.file_id ASC " .
	"LIMIT $from," . FILES_PER_RUN;
$file_res = $cerberus_db->query($sql);

if($cerberus_db->num_rows($file_res))
{
	while($file_row = $cerberus_db->fetch_row($file_res))
	{
		$sql = "INSERT INTO thread_attachments (thread_id,file_name,file_size) ".
			sprintf("VALUES (%d,%s,%d)",
				$file_row["thread_id"],$cerberus_db->escape($file_row["file_name"]),$file_row["file_size"]);
		$cerberus_db->query($sql);
		
		$file_id = $cerberus_db->insert_id();
		echo "Created File ID  " . $file_id . " for <b>" . $file_row["file_name"] . "</b> in thread " . $file_row["thread_id"] . "<br>";
		
		$file_size = strlen($file_row["file_content"]);
		$file_parts = array();
		$file_pos = 0;
		if($file_size > FILE_CHUNK_SIZE)
		{
			while($file_size > 0)
			{
				array_push($file_parts,substr($file_row["file_content"],$file_pos,FILE_CHUNK_SIZE));
				$file_size -= FILE_CHUNK_SIZE;
				$file_pos += FILE_CHUNK_SIZE;
			}
		}
		else
		{
			array_push($file_parts,$file_row["file_content"]);
		}
		
		foreach($file_parts as $file_part)
		{
			$sql = "INSERT INTO thread_attachments_parts (file_id,part_content) ".
				sprintf("VALUES (%d,%s)",
					$file_id,$cerberus_db->escape($file_part));
			$cerberus_db->query($sql);
		}
	}
}

if(isset($from)) { 
?>
<script>
<!--
function next_set()
{
	url = 'import_150_files.php?sid=<?php echo $session->session_id; ?>&from=<?php echo $next_from; ?>&file_count=<?php echo $file_count; ?>';
	document.location = url;
}
-->
</script>
</body>
</html>
<?php
}
?>