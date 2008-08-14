<?php
/***********************************************************************
| MajorCRM (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file:
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

echo "<html><head><title>XML Test - Step #2 - Review XML packet</title>";
echo "<script type='text/javascript' src='js_xml/xmlp.js'></script>";
echo "<script type='text/javascript'><!--\n\nfunction checkXMLDoc() {\nvar parser = new XMLParser();\nvar doc = null;\ntry {\nparser.parse(document.gateway_submit.xml.value);\n}\ncatch (e) {\nvar doanyway=confirm('XML document is not compliant, submit anyway?');\nif(doanyway)\nreturn true;\nelse\nreturn false;\n} return true;\n\n}\n\n//-->\n</script>";
echo "</head><body><h1>XML Test - Step #2 - Review XML packet</h1><br />";
echo "<form name='gateway_submit' action='../../gateway.php' method='post' target='result'>";
echo "<input type='hidden' name='debug' value=''>";
echo "<textarea name='xml' cols=80 rows=10>";

print('<?xml version="1.0" encoding="UTF-8"?>');
echo htmlentities($xml->to_string(TRUE));

echo "</textarea><br /><br />";
echo "<input type=button value='Submit to Gateway' onclick=\"javascript: document.gateway_submit.debug.value = 0; if(checkXMLDoc()) { document.gateway_submit.submit(); }\">&nbsp;&nbsp;&nbsp;";
echo "<input type=button value='Go back' onclick=\"javascript: window.location.href='builder.php';\">&nbsp;&nbsp;&nbsp;";
echo "<input type=button value='Submit to Gateway w/ query debug' onclick=\"javascript: document.gateway_submit.debug.value = 1; if(checkXMLDoc()) { document.gateway_submit.submit(); }\">&nbsp;&nbsp;&nbsp;";
echo "</form></body></html>";
