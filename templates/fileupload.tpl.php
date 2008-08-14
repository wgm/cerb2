<html>
<head>
<title>{$smarty.const.LANG_FILE_UPLOAD_WINDOW_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma-directive" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Directive" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">
{include file="cerberus.css.tpl.php"}

<script language=javascript>
//opener.location.reload();

{literal}

isNS4 = (document.layers) ? true : false;
isIE4 = (document.all && !document.getElementById) ? true : false;
isIE5 = (document.all && document.getElementById) ? true : false;
isNS6 = (!document.all && document.getElementById) ? true : false;
if(!isIE4 && !isIE5) isSCB = (document.getElementById && document.body.style) ? true : false;

function showUpProgress(){

  //identify the element based on browser type
  if (isNS4) {
    objElement = document.layers["UpProgress"];
  } 
  else if (isIE4) {
    objElement = document.all["UpProgress"];
  } 
  else if (isIE5 || isNS6 || isSCB) {
    objElement = document.getElementById("UpProgress");
  }

  if(isNS4){
      objElement.visibility ="visible";
  }
  else{
      objElement.style.visibility = "visible";
  }
  return true;
}

// [JAS] Clear our options box out first
function clearBox( box ) {
  var length = box.length;
  var i = 0;

  for (i = 0; i < length; i++) {
     box.options[0] = null;
  }
}

function populateParent()
{
	wo = window.opener;
	clearBox(wo.document.frmTicketUpdate.attachment_list);
{/literal}
{section name=filelist loop=$file_name_list_array}
	wo.addAttachment("{$file_name_list_array[filelist]}");
{sectionelse}
	wo.addAttachment("No Attachments");
{/section}
{literal}
	window.close();
}
{/literal}

</script>

</head>
<body bgcolor="#D3D3D3" {if $update_and_close == 1}OnLoad="javascript:populateParent();"{/if}>

<span class="cer_maintable_heading"><center>{$smarty.const.LANG_FILE_UPLOAD_INSTRUCTIONS}</center></span>
<form name="uploadAttachments" action="upload.php" method="post" enctype="multipart/form-data">
<table width="100%">
	<tr valign="middle">
		<td align="left" width="33%">
			<input type="hidden" name="ticket_id" value="{$ticket_id}">
			<input type="hidden" name="form_upload">
			<input type="hidden" name="form_delete">
			<input type="hidden" name="form_submit">
			<input type="file" name="reply_attachment">
			<input type="hidden" name="sid" value="{$session_id}">
		</td>
		<td align="center" width="33%">
		    <input type=button onclick="javascript: showUpProgress(); document.uploadAttachments.form_upload.value = 1; document.uploadAttachments.submit();" value="{$smarty.const.LANG_FILE_UPLOAD_ATTACH}"<br>
		    <input type=button onclick="javascript: document.uploadAttachments.form_delete.value = 1; document.uploadAttachments.submit();" value="{$smarty.const.LANG_FILE_UPLOAD_REMOVE}"><br><br><br>
		    <input type=button onclick="javascript: document.uploadAttachments.form_submit.value = 1; document.uploadAttachments.submit();" value="{$smarty.const.LANG_FILE_UPLOAD_DONE}">
		</td>
		<td align="center" width="33%">
			<select name="filelist[]" multiple size="8">
			   {html_options options=$file_name_array}
			</select>
			<br>
			<span class="cer_maintable_text"><B>{$smarty.const.LANG_FILE_UPLOAD_TOTAL_SIZE}:</B> {$total_attachment_size}</span></center>
		</td>
	</tr>
</table>
		
<div id="UpProgress" style="visibility:hidden"><h2><center>{$smarty.const.LANG_FILE_UPLOAD_INPROGRESS}<img src="includes/images/ani_periods.gif"></center></h2></div>
		 