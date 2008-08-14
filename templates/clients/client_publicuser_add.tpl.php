<span class="cer_display_header">{$smarty.const.LANG_CONTACTS_REGISTRED_ADD}</span><br>
<span class="cer_maintable_text">{$smarty.const.LANG_CONTACTS_REGISTRED_INSTRUCTIONS}
</span><br>
<a href="{$urls.clients}" class="cer_maintable_heading">&lt;&lt; {$smarty.const.LANG_CONTACTS_BACK_TO_LIST} </a><br>
<br>

<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">

	<tr>
		<td valign="top">
		
			{include file="clients/client_publicuser_details_editable.tpl.php" id=""}
			
		</td>
		
</table>

<br>