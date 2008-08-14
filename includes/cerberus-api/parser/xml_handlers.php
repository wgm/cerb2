<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file xml_handlers.php
\brief Cerberus E-mail Parser/XML Classes

\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2003
*/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/parser/xml_structs.php");

class CERB_XML_PARSER
{
	var $xml_parser;
	var $file;
	
	function CERB_XML_PARSER(&$scope)
	{
		$this->file = "";
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $scope);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
	}
	
	function set_xml_file($fname)
	{
		$this->file = $fname;
	}
	
	function free() {
		xml_parser_free($this->xml_parser);
	}
	
	function read_xml_string($xml_string) {
		if (!xml_parse($this->xml_parser, $xml_string, true)) {
			die(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($this->xml_parser)),
			xml_get_current_line_number($this->xml_parser)));
		}
	}
	
	function read_xml_file() {
		if (!($fp = fopen($this->file, "r"))) {
			die("could not open XML input");
		}
		
		while ($data = fread($fp, 8192)) {
			if (!xml_parse($this->xml_parser, $data, feof($fp))) {
				die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->xml_parser)),
				xml_get_current_line_number($this->xml_parser)));
			}
		}
	}
	
};

class CERB_XML_EMAIL_HANDLER
{
	var $o_raw_email;
	var $tag;
	var $level; // [JAS]: XML tree level.  EMAIL->HEADERS would be 2
	var $parser;
	var $stack; // [JAS]: push/pop stack for tracking the node (and all parent nodes) we're at in the XML tree
	var $unnamedFileNumber;
	var $inlineAttachment;
	
	function CERB_XML_EMAIL_HANDLER(&$o_raw_email) {
		$this->stack = array();
		$this->parser = new CERB_XML_PARSER($this);
		$this->o_raw_email = &$o_raw_email;
		$this->clear();
	}
	
	function startElement($parser, $tagName, $attrs) {
		array_push($this->stack,$tagName);
		$this->level++;
//		print_r($this->stack);
		
		if ($this->stack[0] == "EMAIL") {
			$this->tag = $tagName;

			if($tagName == "FILE")
			{
				$this->o_raw_email->add_attachment();
				if(!empty($attrs["NAME"]))
					$this->o_raw_email->attachments[count($this->o_raw_email->attachments)-1]->filename = $attrs["NAME"];
			}
			
			if($tagName == "TEMPNAME") $this->o_raw_email->attachments[count($this->o_raw_email->attachments)-1]->add_postname();
			
			if($tagName == "CONTENT-TYPE" && $attrs["CASE"]=="z" && 3<count($this->stack)) // Inline attachment possible
			{
				$this->inlineAttachment = true;
			}
			
			if($tagName == "CONTENT-TYPE" && $this->level == 3) // EMAIL->HEADERS->CONTENT-TYPE
			{
				$this->o_raw_email->message_type = $attrs["CASE"];
			}
		}
		
	}
	
	function endElement($parser, $tagName) {
		array_pop($this->stack);
		$this->level--;
		if($this->level > 0) $this->tag = $this->stack[$this->level-1]; else $this->tag = "";
		
		if ($tagName == "EMAIL") {
			$this->clear();
		}
	}
	
	function characterData($parser, $data) {
		// [bgh] since this function may get called many times for hte same content-type, only set inlineAttachment = false when it's no longer content-type
		if ("CONTENT-TYPE" == $this->stack[count($this->stack)-1] && $this->inlineAttachment) 
		{
			$contentType = trim($data);
			if(0<strlen($contentType)) {
				$parts = explode("/", $contentType);
				if(!empty($parts)) {
					$extension = $parts[count($parts)-1];
					$newName = "inline_attachment_" . $this->unnamedFileNumber . "." . $extension;
					$this->o_raw_email->attachments[count($this->o_raw_email->attachments)-1]->filename = $newName;
				}
			}
		} else if($this->inlineAttachment) {
			$this->inlineAttachment = false;
		}
		if ($this->stack[0] == "EMAIL") {
			$this->o_raw_email->feed_xml_data($this->tag,$this->level,$this->stack,$data);
		}
	}
	
	function clear() {
		$this->tag = "";
		$this->level = 0;
		$this->unnamedFileNumber = 0;
		$this->inlineAttachment = false;
	}
}

class CERB_XML_XSP_LOGIN_RESPONSE_HANDLER
{
	var $xsp_login_response_handler;
	var $tag;
	var $parser;
	var $level; // [JAS]: XML tree level.  EMAIL->HEADERS would be 2
	var $stack; // [JAS]: push/pop stack for tracking the node (and all parent nodes) we're at in the XML tree
	
	function CERB_XML_XSP_LOGIN_RESPONSE_HANDLER(&$xsp_login_response_handler) {
		$this->stack = array();
		$this->parser = new CERB_XML_PARSER($this);
		$this->xsp_login_response_handler = &$xsp_login_response_handler;
		$this->clear();
	}
	
	function startElement($parser, $tagName, $attrs) {
		array_push($this->stack,$tagName);
		$this->level++;
		
		if ($this->stack[0] == "XSP_LOGIN_RESPONSE") {
			$this->tag = $tagName;
		}
	}
	
	function endElement($parser, $tagName) {
		array_pop($this->stack);
		$this->level--;
		if($this->level > 0) $this->tag = $this->stack[$this->level-1]; else $this->tag = "";
	}
	
	function characterData($parser, $data) {
		if ($this->stack[0] == "XSP_LOGIN_RESPONSE") {
			$this->xsp_login_response_handler->feed_xml_data($this->tag,$data);
		}
	}
	
	function clear() {
		$this->tag = "";
		$this->level = 0;
	}
}

?>