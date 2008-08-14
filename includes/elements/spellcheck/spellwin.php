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
?>
<html>
<head>

<style>
.cer_maintable_header { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none; }
.cer_spellchecker_background { background: #33cc00; }
.cer_table_row_line { background: #333333; }
.cer_maintable_heading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #333333 }
.cer_maintable_text { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333 }
</style>

<script src="../../scripts/spellcheck.js"></script>

<script>
var iFrameBody;
var iFrameWin;
</script>
<script>
<?php
if($pspell_loaded) {
	echo $js;
}
else {
	@include("http://www.cerberusweb.com/spellcheck/includes/elements/spellcheck/spellcheck.php?remote=1&spellstring=".urlencode(stripslashes($_REQUEST['spellstring'])));
}
?>
</script>
</head>

<body bgcolor="#cccccc">
<form name="fm1">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td class="cer_table_row_line"><img src="../../images/spacer.gif" width="1" height="1"></td></tr>
	<tr class="cer_spellchecker_background">
		<td style="padding-left: 2px;" class="cer_maintable_header">Spellchecker</td>
	</tr>
	<tr><td class="cer_table_row_line"><img src="../../images/spacer.gif" width="1" height="1"></td></tr>
</table>
<iframe style="width: 100%; height: 250px" src="spell-iframe.php" frameborder="0"></iframe>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
   <tr bgcolor="#BBBBBB">
     <td valign="top">
        <span class="cer_maintable_heading">Change to:</span><br>
        <input type="text" name="changeto">
     </td>
     <td>
       <span class="cer_maintable_heading">Suggestions:</span><br>
       <select name="suggestions" size="5" style="width: 200px; height: 100px;" onClick="this.form.changeto.value = this.options[ this.selectedIndex ].text">
       </select>
     </td>
   </tr>
   <tr bgcolor="#888888">
     <td colspan="2">
        <input type="button" name="change" value="Change" onClick="replaceWord()">
        <input type="button" name="changeall" value="Change All" onClick="replaceAll()">
        <input type="button" name="ignore" value="Ignore" onClick="nextWord(false)">
        <input type="button" name="ignoreall" value="Ignore All" onClick="nextWord(true)">
        <input type="button" name="cancel" value="Cancel" onClick="window.close();">
     </td>
   </tr>
</table>
</form>
</body>
</html>