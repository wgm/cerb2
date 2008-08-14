<?php


class cer_StripHTML
{
	var $ent_replace=array();
	var $ent_with=array();
	
	// [JAS]: HTML 2.0 Entity Replacement
	function cer_StripHTML()
	{
		$this->ent_replace = array(
		'&lt;',
		'&gt;',
		'&amp;',
		'&quot;',
		'&nbsp;',
		'&iexcl;',
		'&cent;',
		'&pound;',
		'&curren;',
		'&yen;',
		'&brvbar;',
		'&sect;',
		'&uml;',
		'&copy;',
		'&ordf;',
		'&laquo;',
		'&not;',
		'&shy;',
		'&reg;',
		'&macr;',
		'&deg;',
		'&plusmn;',
		'&sup2;',
		'&sup3;',
		'&acute;',
		'&micro;',
		'&para;',
		'&middot;',
		'&cedil;',
		'&sup1;',
		'&ordm;',
		'&raquo;',
		'&frac14;',
		'&frac12;',
		'&frac34;',
		'&iquest;',
		'&Agrave;',
		'&Acute;',
		'&Acirc;',
		'&Atilde;',
		'&Auml;',
		'&Aring;',
		'&AElig;',
		'&Ccedil;',
		'&Egrave;',
		'&Eacute;',
		'&Ecirc;',
		'&Euml;',
		'&Igrave;',
		'&Iacute;',
		'&Icirc;',
		'&Iuml;',
		'&ETH;',
		'&Ntilde;',
		'&Ograve;',
		'&Oacute;',
		'&Ocirc;',
		'&Otilde;',
		'&Ouml;',
		'&times;',
		'&Oslash;',
		'&Ugrave;',
		'&Uacute;',
		'&Ucirc;',
		'&Uuml;',
		'&Yacute;',
		'&THORN;',
		'&szlig;',
		'&agrave;',
		'&aacute;',
		'&acirc;',
		'&atilde;',
		'&auml;',
		'&aring;',
		'&aelig;',
		'&ccedil;',
		'&egrave;',
		'&eacute;',
		'&ecirc;',
		'&euml;',
		'&igrave;',
		'&iacute;',
		'&icirc;',
		'&iuml;',
		'&eth;',
		'&ntilde;',
		'&ograve;',
		'&oacute;',
		'&ocirc;',
		'&otilde;',
		'&ouml;',
		'&divide;',
		'&oslash;',
		'&ugrave;',
		'&uacute;',
		'&ucirc;',
		'&uuml;',
		'&yacute;',
		'&thorn;',
		'&yuml'
		);
		
		$this->ent_with = array(
		'<;',
		'>;',
		'&;',
		'";',
		chr(160),
		chr(161),
		chr(162),
		chr(163),
		chr(164),
		chr(165),
		chr(166),
		chr(167),
		chr(168),
		chr(169),
		chr(170),
		chr(171),
		chr(172),
		chr(173),
		chr(174),
		chr(175),
		chr(176),
		chr(177),
		chr(178),
		chr(179),
		chr(180),
		chr(181),
		chr(182),
		chr(183),
		chr(184),
		chr(185),
		chr(186),
		chr(187),
		chr(188),
		chr(189),
		chr(190),
		chr(191),
		chr(192),
		chr(193),
		chr(194),
		chr(195),
		chr(196),
		chr(197),
		chr(198),
		chr(199),
		chr(200),
		chr(201),
		chr(202),
		chr(203),
		chr(204),
		chr(205),
		chr(206),
		chr(207),
		chr(208),
		chr(209),
		chr(210),
		chr(211),
		chr(212),
		chr(213),
		chr(214),
		chr(215),
		chr(216),
		chr(217),
		chr(218),
		chr(219),
		chr(220),
		chr(221),
		chr(222),
		chr(223),
		chr(224),
		chr(225),
		chr(226),
		chr(227),
		chr(228),
		chr(229),
		chr(230),
		chr(231),
		chr(232),
		chr(233),
		chr(234),
		chr(235),
		chr(236),
		chr(237),
		chr(238),
		chr(239),
		chr(240),
		chr(241),
		chr(242),
		chr(243),
		chr(244),
		chr(245),
		chr(246),
		chr(247),
		chr(248),
		chr(249),
		chr(250),
		chr(251),
		chr(252),
		chr(253),
		chr(254),
		chr(255)
		);

		for($c=1;$c<256;$c++) { 
		    array_push($this->ent_replace,'&#' . $c . ';');
		    array_push($this->ent_with,chr($c));
		}
	}
	
	function strip_html($str) {
		$prev_str = "";
		
		// [JAS]: Remove carriage returns and linefeeds only after HTML tags
		// 		Otherwise manually stripping plaintext would corrupt it.
		$str = preg_replace("'>(. ?|)(\n|\r)'si", ">", $str);
		$str = preg_replace("'>(\n|\r)'si", ">", $str);
		
		while($str != $prev_str)
		{
			$prev_str = $str;
			
			$str = str_replace(
				array('<BR>','<br>','<P>','<p>','</P>','</p>','<hr>','<HR>','</TR>','</tr>'),
				array("\n","\n","\n","\n","\n","\n","\n","\n","\n","\n"),
				$str
			);
			
			// [JAS]: Get rid of comment tags
			$str = preg_replace("'<!--(.*?)-->'si", "", $str);
			// [JSJ]: Handle processing instructions separately from comments
			$str = preg_replace("'<![^>]*?>'si", "", $str);  // fixing overly greedy tag to separate handling of comments and processing instructions (ie <!DOCTYPE ... >
			
			// [JAS]: Get rid of everything inside script and head
			$str = preg_replace("'<script[^>]*?>.*?</script>'si", "", $str);
			$str = preg_replace("'<head[^>]*?>.*?</head>'si", "", $str);
			
			// [JAS]: Clean up any HTML tags that are left.
			$str = preg_replace("'<(.*?)>'si", "", $str);

			$str = str_replace($this->ent_replace,$this->ent_with,$str);
		}

		return $str;
	}

};
?>