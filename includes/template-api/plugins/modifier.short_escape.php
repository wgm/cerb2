<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     short_escape
 * Version:  1.0
 * Date:     Aug 30, 2003
 * Author:   Ben Halsted and Jeff Standen
 * Purpose:  Minimalistic safe escape (for foreign charsets, etc)
 * Input:    string = contents to replace
 * Example:  {$text|short_escape}
 *
 * -------------------------------------------------------------
 */
function smarty_modifier_short_escape($string)
{
	$from = array("<",
				  ">",
				  '"',
				  );
	$to = array("&lt;",
	            "&gt;",
	            "&quot;"
	            );
	
	$string = str_replace($from,$to,$string);
	
    return $string;
}

/* vim: set expandtab: */
?>