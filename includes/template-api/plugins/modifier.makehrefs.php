<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     makehrefs
 * Version:  1.0
 * Date:     Aug 30, 2003
 * Author:       Jeremy Johnstone <jeremy@scriptd.net>
 * Purpose:  Convert url's to clickable links
 * Input:    string = contents to replace
 *           sanitize = whether to pass through sanitizer
 *			 style = CSS style to use for links
 * Example:  {$text|makehrefs} or ($text|makehrefs:false} or {$text|makehrefs:true:"cer_black_link"}
 *
 * Modified by:
 *		Ben Halsted (ben@webgroupmedia.com) [BGH] 20031031
 *
 * -------------------------------------------------------------
 */
function smarty_modifier_makehrefs($string, $sanitize = true, $style="")
{
	$from = array("&gt;");
	$to = array(">");
	
	$string = str_replace($from,$to,$string);
	
   if($sanitize !== false)
      return preg_replace("/(^|[\r\n ])((http)+(s)?:\/\/([\.\?,\!\>]?([^\.\?,\!\>\s]))+)/ie", "'\\1<a href=\"goto.php?url='.htmlentities(urlencode('\\2')).'\" class=\"$style\" target=_blank>\\2</a>'", $string);
   else
      return preg_replace("/(^|[\r\n ])((http)+(s)?:\/\/([\.\?,\!\>]?([^\.\?,\!\>\s]))+)/i", "\\1<a href=\"\\2\" class=\"$style\" target=_blank>\\2</a>", $string);
}

/* vim: set expandtab: */
?>