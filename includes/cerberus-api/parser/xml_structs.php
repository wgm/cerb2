<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
\file xml_structs.php
\brief Cerberus E-mail Parser/XML Classes

\author Jeff Standen, jeff@webgroupmedia.com
\date 2002-2005
*/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "includes/functions/RFC822.php");

class CERB_RAW_EMAIL_ATTACHMENT
{
	var $post_names;
	var $tmp_files;
	var $filename;
	var $filesize;
	var $content_type;
	
	function CERB_RAW_EMAIL_ATTACHMENT()
	{
		$this->post_names = array();
		$this->tmp_files = array();
		$this->filename = "";
		$this->filesize = 0;
		$this->content_type = "";
	}
	
	function add_postname()
	{
		$tmp_postname = "";
		array_push($this->post_names,$tmp_postname);
	}
};

class CERB_RAW_EMAIL_HEADERS
{
	var $cc;
	var $cc_raw;
	var $delivered_to;
	var $envelope_to;
	var $from;
	var $in_reply_to; // [BGH] added for proper proxying of watcher emails
 	var $message_id;
 	var $received;
	var $references; // [BGH] added for proper proxying of watcher emails
	var $reply_to; // [bgh] changed from return_path
	var $subject;
	var $to;
	var $to_raw;
	var $all = array(); // [JAS]: All email headers from the original ticket, assoc array
	
	function CERB_RAW_EMAIL_HEADERS()
	{
		$this->cc = array();
		$this->cc_raw = "";
		$this->delivered_to = "";
		$this->envelope_to = "";
		$this->from = "";
		$this->message_id = "";
		$this->received = "";
		$this->reply_to = ""; // [bgh] changed from return_path
		$this->subject = "";
		$this->to = array();
		$this->to_raw = "";
	}
};

class CERB_RAW_EMAIL
{
	var $db = null;
	var $headers;
	var $cerbmail_file;
	var $body;
	var $html_body;
	var $attachments;
	var $message_type;
	var $parser_version = null;
	
	function CERB_RAW_EMAIL()
	{
		$this->db = cer_Database::getInstance();
		$this->clear();
	}
	
	function add_attachment()
	{
		$tmp_attachment = new CERB_RAW_EMAIL_ATTACHMENT();
		array_push($this->attachments,$tmp_attachment);
	}
	
	function build_message()
	{
		$this->header_escape();
		$this->parse_addresses();
		$this->validate_headers();
		$this->writeParserVersion();
		$this->build_attachment_data();
	}

	function header_escape()
	{
		$this->headers->subject = addslashes($this->headers->subject);
	}
	
	function validate_headers()
	{
		// [JAS]: Make sure we have content in the subject line -- if not, set a default
		if(empty($this->headers->subject)) $this->headers->subject = "no subject";
	}
	
	function generate_message_id() {
		global $_SERVER;
		srand((double)microtime()*10000000);
		return sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);	
	}
	
	function parse_addresses()
	{
  	$RFC822 = new Mail_RFC822();
	
		$address = $RFC822->parseAddressList(trim($this->headers->to_raw), 'localhost', TRUE);
		foreach($address as $addy)
			{ array_push($this->headers->to, $addy->mailbox . "@" . $addy->host); 
			}

		if(!empty($this->headers->cc_raw)) {
			$address = $RFC822->parseAddressList(trim($this->headers->cc_raw), 'localhost', TRUE);
			foreach($address as $addy)
				{ array_push($this->headers->cc, $addy->mailbox . "@" . $addy->host); }
		}
		
		if(!empty($this->headers->envelope_to)) {
			$address = $RFC822->parseAddressList(trim($this->headers->envelope_to), 'localhost', TRUE);
			$this->headers->envelope_to = $address[0]->mailbox . "@" . $address[0]->host;
		}

		if(!empty($this->headers->delivered_to)) {
			$address = $RFC822->parseAddressList(trim($this->headers->delivered_to), 'localhost', TRUE);
			$this->headers->delivered_to = $address[0]->mailbox . "@" . $address[0]->host;
		}

		if(!empty($this->headers->from)) {
			$address = $RFC822->parseAddressList(trim($this->headers->from), 'localhost', TRUE);
			$this->headers->from = $address[0]->mailbox . "@" . $address[0]->host;
		}

		if(!empty($this->headers->reply_to)) { // [bgh] changed from return_path
			$address = $RFC822->parseAddressList(trim($this->headers->reply_to), 'localhost', TRUE); // [bgh] changed from return_path
			$this->headers->reply_to = $address[0]->mailbox . "@" . $address[0]->host; // [bgh] changed from return_path
		}
	}
	
	function writeParserVersion() {
		if (!empty($this->parser_version)) {
			$sql = sprintf("UPDATE `configuration` SET `parser_version` = %s", $this->db->escape($this->parser_version));
			$this->db->query($sql);
		}
	}
	
	function build_attachment_data()
	{
		foreach($this->attachments as $idx => $file)
		{
			if(!count($file->post_names)) unset($this->attachments[$idx]);
			else
			foreach($file->post_names as $pn)
			{
				$post_name = str_replace(".","_",$pn);
				$file_name = null;
				
				if(isset($_FILES[$post_name])) {
					$file_name = $_FILES[$post_name]["tmp_name"];
				}
				
				if(!file_exists($file_name)) {
					echo "Cerberus [ERROR]: File attachment file `$file_name` doesn't exist in the filesystem!  Make sure upload_tmp_dir is set in your php.ini file.  Make sure your parser user can write to the tmp_dir path in the config.xml file.\r\n";
				}
				else {
				
					$fp = @fopen($file_name,"rb");
					$size = $_FILES[$post_name]["size"];
					if(0<$size) {
						if(@$fp) 
						$file_content = fread($fp,$size);
					}
					if( (empty($file->filename) ) 
						&& $this->message_type != 'c' // Not a HTML only message, not an attachment, append to body
						&& !stristr($file->content_type,"text/html")
					)
					{
						$this->body .= trim($file_content);
						unset($this->attachments[$idx]);
						@unlink($file_name);
					}
					else if (empty($filename) 
						&& ($this->message_type == 'c' 
							|| stristr($file->content_type,"text/html")) // HTML only message, and this isn't an attachment, append
					)
					{
						$this->html_body .= trim($file_content);
						unset($this->attachments[$idx]);
						@unlink($file_name);
					}
					else {
						array_push($this->attachments[$idx]->tmp_files,$file_name);
						$this->attachments[$idx]->filesize += $_FILES[$post_name]["size"];
						unset($this->attachments[$idx]->post_names);
					}
					
					fclose($fp);
				}
				// end file exists
			}
			// end foreach post names
		}
		// end foreach attachments

		// [JAS]: If we don't have a plaintext message body, use the HTML part (bad e-mail client!)
		if(empty($this->body) && !empty($this->html_body))
		{
			$strip_html = new cer_StripHTML();
			require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
			$this->headers->subject = $strip_html->strip_html($this->headers->subject);
			$this->body = $strip_html->strip_html($this->html_body);
		}

		$tmp_dir_path = FILESYSTEM_PATH . "tempdir";
		
		// [JAS]: Attach the XML packet for *DEBUG* purposes ************************************//debug
		global $xml_data;
		$cfg = CerConfiguration::getInstance();

		if(isset($xml_data) && !empty($xml_data) && "1" == $cfg->settings["save_message_xml"])
		{
			$xml_attachment = new CERB_RAW_EMAIL_ATTACHMENT();
			$xml_attachment->filename = "message_source.xml";
			$xml_attachment->filesize = strlen($xml_data);
			$xml_attachment->content_type = "text/plain";
			$tmp_name = tempnam($tmp_dir_path, "cerb");
			$fp = fopen($tmp_name,"w");
			if($fp)
			{
				fwrite($fp,$xml_data,strlen($xml_data));
				fclose($fp);
				array_push($xml_attachment->tmp_files,$tmp_name);
				array_push($this->attachments,$xml_attachment);
			}
		}
		// ********************************************************************************// end debug

		
		// [JAS]: Add on our html message part as another attachment if it exists and is under 512KB
		if(!empty($this->html_body) && strlen($this->html_body) < 512000)
		{
			$html_attachment = new CERB_RAW_EMAIL_ATTACHMENT();
			$html_attachment->filename = "html_mime_part.html";
			$html_attachment->filesize = strlen($this->html_body);
			$html_attachment->content_type = "text/html";
			$tmp_name = tempnam($tmp_dir_path, "cerb");
			$fp = fopen($tmp_name,"w");
			if($fp)
			{
				fwrite($fp,$this->html_body,strlen($this->html_body));
				fclose($fp);
				array_push($html_attachment->tmp_files,$tmp_name);
				array_push($this->attachments,$html_attachment);
			}
		}

	}
	
	function clear()
	{
		$this->cerbmail_file = null;
		$this->body = null;
		$this->html_body = null;
		$this->headers = new CERB_RAW_EMAIL_HEADERS();
		$this->message_type = null;
		$this->attachments = array();
	}

	function import_thread_attachments($thread)
	{
		$tmp_dir_path = FILESYSTEM_PATH . "tempdir";
		
		$sql = "SELECT f.file_id, f.file_name, f.file_size FROM thread_attachments f ".
			"LEFT JOIN thread th ON (f.thread_id = th.thread_id) ".
			"WHERE th.thread_id = $thread";
		$file_res = $this->db->query($sql);
		
		if($this->db->num_rows($file_res))
		while($file_row = $this->db->fetch_row($file_res))
		{
			if($file_row["file_name"] == "message_source.xml" || $file_row["file_name"] == "html_mime_part.html")
				continue;
				
			$new_attachment = new CERB_RAW_EMAIL_ATTACHMENT();
			$new_attachment->filename = $file_row["file_name"];
			$new_attachment->filesize = $file_row["file_size"];
			
			$sql = "SELECT p.part_id, p.file_id, p.part_content FROM thread_attachments_parts p ".
				sprintf("WHERE p.file_id = %s ",$file_row["file_id"]) . 
				"ORDER BY p.part_id ";
			$part_res = $this->db->query($sql);
			
			if($this->db->num_rows($part_res))
			while($part_row = $this->db->fetch_row($part_res))
			{
				$tmp_name = tempnam($tmp_dir_path, "cerb");
				$fp = fopen($tmp_name,"w");
				if($fp)
				{
					fwrite($fp,$part_row["part_content"],strlen($part_row["part_content"]));
					fclose($fp);
					array_push($new_attachment->tmp_files,$tmp_name);
				}
			}
			
			array_push($this->attachments,$new_attachment);
		}
		
		unset($file_res);
		unset($part_res);
	}
	
	function feed_xml_data($tag,$level,$stack,$data)
	{
		$data = trim($data);
		
		if(empty($data)) return true;

		// [JAS]: We want to store all the raw headers for mail rules
		if($level == 3 && $stack[1] == "HEADERS") { @$this->headers->all[strtolower($tag)] .= $data; }
		
		switch ($tag) {
			
			case "CC": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->cc_raw .= $data; }
				break;
			}
			case "CERBMAIL": {
				if($level == 2 && $stack[0] == "EMAIL") { $this->cerbmail_file .= $data; }
				break;
			}
			case "CONTENT-TYPE": {
				if($stack[$level-3] == "SUB" 
					&& $stack[$level-2] == "HEADERS" 
					&& count($this->attachments)) // [JAS]: We're not outside our first file (i.e. errant mime-parts)
					{ 
						$this->attachments[count($this->attachments)-1]->content_type .= $data; 
					}
				break;
			}
			case "DELIVERED-TO": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->delivered_to .= $data; }
				break;
			}
			case "ENVELOPE-TO": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->envelope_to .= $data; }
				break;
			}
			case "FROM": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->from .= $data; }
				break;
			}
			case "IN-REPLY-TO": { // [BGH] added to properly proxy watcher emails
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->in_reply_to .= $data; }
				break;
			}
			case "MESSAGE-ID": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->message_id .= $data; }
				break;
			}
			case "PARSER_VERSION": {
				if($level == 2 && $stack[0] == "EMAIL") { $this->parser_version .= $data; }
				break;
			}
			case "RECEIVED": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->received .= $data; }
				break;
			}
			case "REFERENCES": { // [BGH] added to properly proxy watcher emails
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->references .= $data; }
				break;
			}
			case "REPLY-TO": { // [bgh] changed from return_path
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->reply_to .= $data; } // [bgh] changed from return_path
				break;
			}
			case "SUBJECT": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->subject .= $data; }
				break;
			}
			case "TEMPNAME": {
				if($stack[$level-3] == "SUB" && $stack[$level-2] == "FILE") {
					$num_attach = count($this->attachments)-1;
					$num_posts = count($this->attachments[$num_attach]->post_names)-1;
					$this->attachments[$num_attach]->post_names[$num_posts] .= $data;
				}
				else if ($stack[$level-3] == "EMAIL" && $stack[$level-2] == "FILE") { 
					$num_attach = count($this->attachments)-1;
					$num_posts = count($this->attachments[$num_attach]->post_names)-1;
					$this->attachments[$num_attach]->post_names[$num_posts] .= $data;
				}
				break;
			}
			case "TO": {
				if($level == 3 && $stack[1] == "HEADERS") { $this->headers->to_raw .= $data; }
				break;
			}
		}
	}
};

class CERB_XSP_LOGIN_RESPONSE
{
	var $user_name=null;
	var $user_login=null;
	var $user_email=null;
	var $user_password=null;
	var $handshake=null;
	
	function CERB_XSP_LOGIN_RESPONSE()
	{
		$this->clear();
	}
	
	function clear()
	{
		$this->user_name=null;
		$this->user_login=null;
		$this->user_email=null;
		$this->user_password=null;
		$this->handshake=null;
	}
	
	function feed_xml_data($tag,$data)
	{
		$data = trim($data);
		
		switch ($tag) {
			case "USER_NAME":
			$this->user_name .= $data;
			break;
			case "USER_LOGIN":
			$this->user_login .= $data;
			break;
			case "USER_EMAIL":
			$this->user_email .= $data;
			break;
			case "USER_PASSWORD":
			$this->user_password .= $data;
			break;
			case "HANDSHAKE":
			$this->handshake .= $data;
			break;
		}
	}
};


?>
