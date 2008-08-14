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
| File: config_address_export.php
|
| Purpose: The configuration include for exporting addresses.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/functions/privileges.php");
?>
<form name="addressFrm" action="addresses_export.php" target="_blank" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td colspan="5"><?php echo  LANG_CONFIG_ADDRESS_TITLE ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
      <div align="left">
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="150" class="cer_maintable_heading" valign="top"><?php echo  LANG_CONFIG_ADDRESS_QUEUES ?></td>
            <td class="cer_maintable_text">
            <?php
						// Load up current user preferences
						$sql = "SELECT queue_id,queue_name FROM queue";
						$result = $cerberus_db->query($sql,false);
						if($cerberus_db->num_rows($result) > 0){
						     while($queuerow = $cerberus_db->fetch_row($result)){
								 echo "<input type='checkbox' name='queues[]' value='$queuerow[0]'>$queuerow[1]<br>";
								 }
						}
						?>
						</td>
          </tr>
					<tr> 
            <td class="cer_maintable_heading"><?php echo  LANG_CONFIG_ADDRESS_DELIMITER ?></td>
            <td>
              <span class="cer_footer_text">
							<input type="radio" name="delimiter" value="comma" checked><?php echo  LANG_CONFIG_ADDRESS_COMMA ?>   
							<input type="radio" name="delimiter" value="line"><?php echo  LANG_CONFIG_ADDRESS_LINE ?>
							</span> 
              </td>
          </tr>
					<tr> 
            <td class="cer_maintable_heading"><?php echo  LANG_CONFIG_ADDRESS_FILE ?></td>
            <td>
              <span class="cer_footer_text">
							<input type="radio" name="file_type" value="screen" checked><?php echo  LANG_CONFIG_ADDRESS_FILE_SCREEN ?>   
							<input type="radio" name="file_type" value="file"><?php echo  LANG_CONFIG_ADDRESS_FILE_FILE ?>
							</span> 
              </td>
          </tr>
								<tr>
											<td colspan="2" class="cer_footer_text"><?php echo  LANG_CONFIG_ADDRESS_NOTE ?></td>
								</tr>					
								<tr>
											<td>&nbsp;</td>
											<input type="hidden" name="form_submit" value="x">
                      <td><input type="submit" class="cer_button_face" value="Export Addresses"></td>
								</tr>					
					</table>
				
      </div>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
		&nbsp;
		</td>
	</tr>
</table>
</form>
