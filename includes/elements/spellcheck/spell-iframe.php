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
<style>
   .cer_spellcheck_highlight { color: #ff0000; font-weight: bold; }
</style>
<script>
function assignSelf() {
   window.parent.iFrameBody = document.getElementById("theBody");
   window.parent.iFrameWin = this;
   window.parent.startsp();
}
</script>

<body onLoad="assignSelf();" id="theBody" bgcolor="#FFFFFF">
Cerberus [ERROR]: Can't assign text to IFRAME.<BR>
Common Causes of this error could be:<BR>
1. The text you were using to spellcheck with causes an escaping problem with javascript.<BR>
   If this happens please copy/paste the text you were using into an email and send it to<BR>
   support@webgroupmedia.com explaining what happened.<BR>
   <BR>
2. allow_url_fopen is disabled in your php.ini file.<BR>
   Please check the documentation <a href="http://www.php.net/manual/en/ref.filesystem.php#ini.allow-url-fopen">here</a>.<BR>
2. Trying to spellcheck large amounts of text with the "Spell Check via Cerberusweb.com" button.<BR>
3. Trying to spellcheck via Cerberusweb.com while running PHP on Windows with version less than 4.3.0.<BR>
</body>