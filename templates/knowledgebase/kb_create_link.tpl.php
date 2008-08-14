{if $kb->show_article_create !== false}
<br>
<form>
<input type="button" 
	class="cer_button_face" 
	value="{$smarty.const.LANG_KB_ARTICLE_CREATE} '{$kb->category_name|escape:"quotes"}'" 
	OnClick="javascript:document.location='{$kb->category_create_url}'">
</form>
{/if}		