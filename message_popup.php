<?php
require_once("site.config.php");

@$mid = $_REQUEST["mid"];

$from_user = "";

$sql = "UPDATE private_messages SET pm_notified = 1 WHERE pm_to_user_id = " . $session->vars["login_handler"]->user_id;
$cerberus_db->query($sql);

if(isset($mid) && !empty($mid))
{
	$sql = "SELECT pm.pm_id, u.user_login FROM private_messages pm, user u WHERE pm.pm_from_user_id = u.user_id AND pm.pm_id = " . $mid;
	$result = $cerberus_db->query($sql);
	
	if($row = $cerberus_db->grab_first_row($result))
	{
		$from_user = $row["user_login"];
	}
}

?>
<html>
<head>
<title>New Private Message!</title>
<style>
<?php require("cerberus.css"); ?>
</style>

<script>
sid = "sid=<?php echo @$sid; ?>";

function gotoInbox()
{
	url = "my_cerberus.php?mode=messages&pm_folder=ib&" + sid;
	window.opener.document.location = url;
	window.close();
}

function closeIgnore()
{
	window.close();
}
</script>

</head>

<body bgcolor="#BBBBBB">

<table border="1" cellpadding="3" cellspacing="0" bgcolor="#FFFFFF">
<tr>
	<td align="center">
		<span class="cer_maintable_heading">You have a new private message <?php if(!empty($from_user)) { echo "from $from_user!"; } ?></span><br>
		<br>
		<a href="javascript:gotoInbox();" class="cer_maintable_heading">View my Inbox</a><br>
		<span class="cer_footer_text"><br></span>
		<a href="javascript:closeIgnore();" class="cer_maintable_heading">Ignore this Message</a><br>
		<br>
	</td>
</tr>
</table>

</body>

</html>