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
| File: footer.php
|
| Purpose: The global page footer.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
?>
<br>
<table width="100%" border="0" cellspacing="0" align="left" cellpadding="0">
  <tr> 
    <td valign="bottom" align="left" class="cer_footer_text"><?php echo LANG_FOOTER_TM ?> - Version <?php echo GUI_VERSION; ?><br>
    </td>
    <td valign="middle" align="right" class="cer_footer_text"><?php echo LANG_FOOTER_POWERED ?><img src="cer_inctr_logo_sm.gif" width="98" height="44" align="bottom"></td>
  </tr>
</table>
<?php

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************

?>
