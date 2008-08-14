{literal}
<script>
	function toggle_{/literal}{$tip_name}{literal}() {
		if(document.getElementById) {
			tooltip = document.getElementById("{/literal}{$tip_name}{literal}");
			if(null != tooltip) {
				if(tooltip.style.display == "block")
					tooltip.style.display="none";
				else
					tooltip.style.display="block";
			}
		}
	}
</script>
{/literal}

<div id="{$tip_name}" style="display:none;" class="context_tooltip">
	<table border="0" cellpadding="2" cellspacing="0" width="100%">
		<tr class="boxtitle_green_glass">
			<td colspan="2">
				Search Syntax
			</td>
		</tr>
		<tr>
			<td class="searchSyntaxText">
				<B>Must have:</B>
			</td>
			<td class="searchSyntaxText" nowrap>
				<i>+help +phone</i>
			</td>
		</tr>
		<tr>
			<td class="searchSyntaxText">
				<B>Can't have:</B>
			</td>
			<td class="searchSyntaxText" nowrap>
				<i>-spam</i>
			</td>
		</tr>
		<tr>
			<td class="searchSyntaxText">
				<B>Partial:</B>
			</td>
			<td class="searchSyntaxText" nowrap>
				<i>web*</i>
			</td>
		</tr>
		<tr>
			<td class="searchSyntaxText">
				<B>Any word:</B>
			</td>
			<td class="searchSyntaxText" nowrap>
				<i>help support assistance</i>
			</td>
		</tr>
		<tr>
			<td class="searchSyntaxText">
				<B>Mixed:</B>
			</td>
			<td class="searchSyntaxText" nowrap>
				<i>+help problem issue -phone</i>
			</td>
		</tr>
	</table>
</div>
