{if empty($company->num_public_users) }
{literal}
<script language="javascript1.2">
	function verifyCompanyDelete(f)
	 {
		if(confirm("[Cerberus]: Are you sure you want to permanently remove this company record?"))
			return true;
		else
			return false;
	}
</script>
{/literal}

<table border="0" cellpadding="3" cellspacing="1" bgcolor="#000000" width="100%">
	<form action="clients.php" onsubmit="javascript:return verifyCompanyDelete(this);">
	<input type="hidden" name="form_submit" value="company_delete">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="mode" value="{$params.mode}">
	<input type="hidden" name="id" value="{$company->company_id}">

	<tr class="boxtitle_gray_glass">
		<td>
			Remove Company Record
		</td>
	</tr>

	<tr bgcolor="#EEEEEE">
		<td class="cer_maintable_text">
			<input type="submit" value="Delete Company Record" class="cer_button_face">
		</td>
	</tr>
	
	</form>
</table>	

<br>
{/if}
