<?php

require_once(FILESYSTEM_PATH . 'cerberus-api/mail/mimeDecode.php');

class cer_Pop3Client {
	var $host;
	var $port;
	var $user;
	var $pass;
	var $state;
	var $tls;
	var $socket;
	var $error;
	var $lineBuffer;
	var $readBuffer;
	var $messageCount;
	var $debug;
	var $timeout;
	var $messageListArray;
	var $lineTerminator;
		
	function cer_Pop3Client($host, $port, $user, $pass, $timeout, $terminator="\r\n") {
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		$this->timeout = $timeout;
		
		$this->tls = $tls;
		$this->socket = null;
		$this->tls = false;
		$this->state = 0;
		$this->clearBuffers();
		$this->debug = false;
		$this->messageListArray = array();
		$this->lineTerminator = $terminator;
	}
	
	function setDebug($bool) {
		$this->debug = $bool;
	}
	
	function clearBuffers() {
		if($this->debug) { echo "Clearing Buffers... <br>"; flush(); }
		$this->lineBuffer = array();
		$this->readBuffer = "";
	}
	
	function socketError() {
		$this->error = "Could not create the socket, the error code is: " . socket_last_error() . ",error message is: " . socket_strerror(socket_last_error());
		if($this->debug) { echo "socketError() called... <br>"; echo $this->error . "<br>"; flush(); }
	}
	
	function splitBuffer() {
		if($this->debug) { echo "Trying to split the buffer of length " . strlen($this->readBuffer) . "... <br>"; flush(); }
		if(0<strlen($this->readBuffer)) {
			if($this->debug) { print_r($this->readBuffer); flush(); }
			$linearray = explode($this->lineTerminator,$this->readBuffer);
			if($this->debug) { print_r($linearray); flush(); }
			if(is_array($linearray)) {
				$this->readBuffer = array_pop($linearray);
				$this->lineBuffer = $this->lineBuffer + $linearray;
			}
		}
	}
	
	function socketRead() {
		if($this->debug) { echo "Inside socketRead()...<br>"; flush(); }
		$smbuff = "";
		$sockres;
		
		$readers = array($this->socket);
		
		$changeCount = socket_select($readers, $writers = NULL, $exceptions = NULL, $this->timeout);
		
		if(FALSE===$changeCount) {
			$this->socketError();
			return false;
		}
		else if (0<$changeCount) {
			if(FALSE!==($smbuff=socket_read($this->socket,1024,PHP_BINARY_READ))) {
				if($this->debug) { echo "socket_read (" . $sockres . "): " . $smbuff . "<br>"; flush(); }
				$this->readBuffer .= $smbuff;
			} else {
				$this->socketError();
				return false;
			}
		} else {
			// [bgh] timeout
			$this->error = "The socket read timed out.";
			return false;
		}
		
		return true;
	}
	
	
	function socketWrite($buffer) {
		
		$length = strlen($buffer);
		
		if($this->debug) { echo "Inside socketWrite()...<br>"; flush(); }
		if($this->debug) { echo "Writing '" . $buffer . "'...<br>"; flush(); }
		
		while(0<$length) {
			$sentCount = socket_write($this->socket, $buffer);
			
			if(FALSE === $sentCount) {
				$this->socketError();
				return false;
			}
			
			if($this->debug) { echo "  Wrote " . $sentCount . " bytes...<br>"; flush(); }
			$length-=$sentCount;
			$buffer = substr($buffer,$sentCount);
		}
		
		return true;
	}
	
	
	function commandResult() {
		if($this->debug) { echo "commandResult() Need a line to check... <br>"; flush(); }
		$line = $this->socketReadLine();
		if($this->isError($line)) {
			return false;
		}
		
		return true;
	}
	
	function socketReadLine() {
		if($this->debug) { echo "Inside socketReadLine()... " . count($this->lineBuffer) . " lines ready for reading.<br>"; flush(); }
		while(0==count($this->lineBuffer)) {
			if($this->debug) { echo "lineBuffer: "; print_r($this->lineBuffer); echo "<br>"; flush(); }
			if($this->debug) { echo "readBuffer: "; print_r($this->readBuffer); echo "<br>"; flush(); }
			if($this->debug) { echo "socketReadLine() Need to read more to make a line... <br>"; flush(); }
			if($this->socketRead()) {
				if($this->debug) { echo "socketReadLine() got more! Splitting... <br>"; flush(); }
				$this->splitBuffer();
			} else {
				if($this->debug) { echo "socketReadLine() had an error =( ... <br>"; flush(); }
				return null;
			}
		}
		
		return array_shift($this->lineBuffer);
	}
	
	function socketUnReadLine($line) {
		if(is_string($line)) {
			array_unshift($this->lineBuffer, $line);	
		}
	}
	
	/**
	 * @return true or false
	 */
	function connect() {
		if($this->debug) { echo "About to create the socket... <br>"; flush(); }
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if(FALSE === $this->socket) {
			$this->socketError();
			return false;
		}
		
		if(!socket_connect($this->socket, $this->host, $this->port)) {
			$this->socketError();
			return false;
		}
		
		return $this->commandResult();
	}
	
	function disconnect() {
		if($this->debug) echo "About to disconnect... <br>";
		socket_shutdown($this->socket, 2);
		socket_close($this->socket);
	}
	
	function pop3_dele() {
		
	}
	
	/**
	 * @return true or false if it is an error
	 */
	function isError($msg) {
		
		if(0==strncasecmp($msg,"-err",4)) {
			return true;
		}
		
		return false;
	}
	
	function pop3_pass() {
		$buf = "PASS " . $this->pass . "\r\n";
		$this->socketWrite($buf);
		
		return $this->commandResult();
	}
	
	function pop3_quit() {
		$buf = "QUIT\r\n";
		$this->socketWrite($buf);
		
		return $this->commandResult();
	}
	
	function pop3_retr($messId) {
		$buf = "RETR " . $messId . "\r\n";
		$this->socketWrite($buf);
		
		$this->clearBuffers();
		
		$line = $this->socketReadLine();
		if(!$this->isError($line)) {
			$email = "";
			while(true) {
				$line = $this->socketReadLine();
				flush();
				if(0==strcmp(".",$line)) {
					return $email;
				}
				$email .= $line . $this->lineTerminator;
			}
		}
		return FALSE;
	}
	
	/**
	 * @return returns true for has messages and 0 for no messages
	 */
	function pop3_stat() {
		$buf = "STAT\r\n";
		$this->socketWrite($buf);
		
		$line = $this->socketReadLine();
		
		list($status, $count, $bytes) = sscanf($line, "%s %d %d");
		
		if($this->isError($status)) {
			return false;
		}
		
		if(0<$count) {
			$buf = "LIST\r\n";
			$this->socketWrite($buf);
			if($this->commandResult()) {
				for($x=0; $x<$count; $x++) {
					$line = $this->socketReadLine();
					list($id, $bytes) = sscanf($line, "%d %d");
					$this->messageListArray[$id] = $bytes;
					if($this->debug) { echo "Message " . $id . " is " . $bytes . " bytes... <br>"; flush(); }
				}
			}
		}
		
		return true;
	}
	
	function pop3_user() {
		if($this->debug) { echo "Sending user name... <br>"; flush(); }
		$buf = "USER " . $this->user . "\r\n";
		
		if(!$this->socketWrite($buf)) {
			return false;
		} 
		
		return $this->commandResult();
	}
}


class cer_Pop3Parser {
	var $clients;
	
	function cer_Pop3 () {
		 $this->clients = array();
	}

	function canTLS() {
		// [bgh] TODO: implement
	}

	function addClient($clientInfo) {
		$this->clients[] = $clientInfo;
	}
	
	function run() {
		print_r($clients);
		foreach ($this->clients as $client) {
			
			// [bgh] connect
			if($client->connect()) {
				if($client->debug) echo "Connected...<br>";
				if($client->pop3_user()) {
					if($client->debug) echo "Sent User...<br>";
					if($client->pop3_pass()) {
						if($client->debug) echo "Sent Password...<br>";
						if($client->pop3_stat()) {
							if($client->debug) echo "Stat... " . $client->messageCount . " messages on the server.<br>";
							foreach($client->messageListArray as $id => $size) {
								$email = $client->pop3_retr($id);
								$params = array('include_bodies' => true, 'decode_bodies' => true, 'decode_headers' => true);
								
								if(FALSE!==$email) {
									$decoder = new Mail_mimeDecode($email);
									$structure = $decoder->decode($params);	
									echo $decoder->getXML($structure);
								}
							}
						}
					}	
				}
				
				$client->pop3_quit();
			}
			
			// [bgh] disconnect from the server
			$client->disconnect();
		}	
	}
}
