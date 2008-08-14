<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_Trigram.class.php");

class cer_Trigram_Test extends cer_TestCase
{
	var $trigramClass = null;		//<! Fixture for cer_Trigram
	
	function cer_Trigram_Test($name) {
		$this->cer_TestCase($name); // [BGH]: Call the parent object constructor.
	}
	
	function setUp() {
		$this->trigramClass = new cer_Trigram();
	}
	
	function tearDown() {
		$this->trigramClass = null;
	}
	
	function test_cleanPunctuation() {
		$expected = array("WHAT?");
		$actual = cer_Trigram::cleanPunctuation(array("WHAT???"));
		$this->assertEquals($expected,$actual,"Failed to condense many identical special chars.");
		
		$expected = array("WHAT!!?");
		$actual = cer_Trigram::cleanPunctuation(array("WHAT!!?"));
		$this->assertEquals($expected,$actual,"Failed, Condensed special chars.");
		
		$expected = array("WHAT!!?");
		$actual = cer_Trigram::cleanPunctuation(array("WHAT!!?"));
		$this->assertEquals($expected,$actual,"Failed, did not strip whitespace from edges.");

		$expected = array("!WHAT");
		$actual = cer_Trigram::cleanPunctuation(array("!!WHAT"));
		$this->assertEquals($expected,$actual,"Failed, Did not condense prefixed special chars. (even number)");
		
		$expected = array("!WHAT");
		$actual = cer_Trigram::cleanPunctuation(array("!!!!!WHAT"));
		$this->assertEquals($expected,$actual,"Failed, Did not condense prefixed special chars. (odd number)");

		$expected = array("!%%WHAT");
		$actual = cer_Trigram::cleanPunctuation(array("!!!!!%%WHAT"));
		$this->assertEquals($expected,$actual,"Failed, Did not condense specials prefixing specials.");
		
		$expected = array("!WHAT","!happened!","here!?!?");
		$actual = cer_Trigram::cleanPunctuation(array("!!!!!WHAT","!!happened!","here!?!??"));
		$this->assertEquals($expected,$actual,"Failed substring condensing.");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 1)");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 2)");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","!!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 3)");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","!!!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 4)");

		$expected = array(0=>"happened",3=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","?","!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 1, length of 2)");

		$expected = array(0=>"happened",5=>"a",31=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","...","!!!","???","@@@","a",",,,","###","%%%","^^^","&&&","***","(((",")))","___","+++","===","{{{","}}}","[[[","]]]","\\\\\\","|||",";;;",":::","\"\"\"","<<<",">>>","///","~~~","---","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (odd number)");
		
		$expected = array(0=>"happened",31=>"here");
		$actual = cer_Trigram::cleanPunctuation(array("happened","!@#$%","..","!!","??","@@",",,","##","%%","^^","&&","**","((","))","__","++","==","{{","}}","[[","]]","\\\\","||",";;","::","\"\"","<<",">>","//","~~","--","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (even number)");		

		$expected = array(0=>"Hi!",1=>"What",2=>"happened",3=>"here?");
		$actual = cer_Trigram::cleanPunctuation(array("Hi!","What","happened","here?"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 1, length of 2)");
	}

	function test_indexWords() {
		$expected = array("a","b","c");
		$actual = $this->trigramClass->indexWords("a b c");
		$this->assertEquals($expected,$actual,"Simple a b c Test.");

		$expected = array("this","is","a","larger","test!");
		$actual = $this->trigramClass->indexWords("This is a larger test!");
		$this->assertEquals($expected,$actual,"Longer sentence test.");

		$expected = array("this","test!","is","a","!test!","of","punctuation!");
		$actual = $this->trigramClass->indexWords("This TEST!! Is a !!Test! of Punctuation!!");
		$this->assertEquals($expected,$actual,"Longer test with punctuation.");
		
		$expected = array("this","test!","is","a","!test!","of","punctuation!");
		$actual = $this->trigramClass->indexWords("This\tTEST!!\rIs a !!Test!\r\nof Punctuation!!");
		$this->assertEquals($expected,$actual,"Longer test with extra white characters.");	
	
		$expected = array("this","test!","is","a","!test!","of","punctuation!");
		$actual = $this->trigramClass->indexWords("This  \tTEST!!\r\r\rIs a  !!Test! \r\nof Punctuation!!");
		$this->assertEquals($expected,$actual,"Longer test with extra doubled up white characters.");	

		$expected = array("testing"=>-1,"a"=>-1,"100"=>-1,"char"=>-1,"length"=>-1,"word"=>-1,"aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeee"=>-1,"woo!"=>-1);		
		$this->trigramClass->indexWords("testing a 100 char length word aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeeeeeeeeffffffffffgggggggggghhhhhhhhhhiiiiiiiiiijjjjjjjjjj woo!");
		$actual = $this->trigramClass->wordarray;
		$this->assertEquals($expected,$actual,"Testing of a very long word in word array.");		
	}
	
	function _wordsToTrigrams_indexer(&$obj) {
		$x = 1;

		foreach ($obj as $key => $elem) {
			if(TRIGRAM_MAX_WORD < strlen($key)) {
				$obj[$key] = TRIGRAM_WORD_UNSET;
			}
			else {
				$obj[$key] = $x;
				$x++;
			}
		}
	}
	
	function test_wordsToTrigrams()
	{	
		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5));
		$this->trigramClass->indexWords("This is a larger test!");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Longer sentence test.");
		
		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5));
		$this->trigramClass->indexWords("This is a ## larger test!");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Longer sentence test.");		
		
		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5), new trigram(4,5,6), new trigram(5,6,7), new trigram(6,7,8));
		$this->trigramClass->indexWords("testing a 100 char length word aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeeeeeeeeffffffffffgggggggggghhhhhhhhhhiiiiiiiiiijjjjjjjjjj woo!");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Testing of a very long word in word array.");			

		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5), new trigram(1,2,3), new trigram(2,3,6), new trigram(3,6,5));
		$this->trigramClass->indexWords("This is the first sentence. This is the second sentence.");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Testing non-trigramming over sentence breaks. (period)");					
		
		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5), new trigram(1,2,3), new trigram(2,3,6), new trigram(3,6,5));
		$this->trigramClass->indexWords("This is the first sentence! This is the second sentence!");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Testing of a very long word in word array. (bang)");					

		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5), new trigram(1,2,3), new trigram(2,3,6), new trigram(3,6,5));
		$this->trigramClass->indexWords("This is the first sentence? This is the second sentence?");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Testing of a very long word in word array. (question)");					

		$expected = array(new trigram(1,2,3), new trigram(2,3,4), new trigram(3,4,5), new trigram(1,2,3), new trigram(2,3,6), new trigram(3,6,7), new trigram(1,2,3), new trigram(2,3,8), new trigram(3,8,9));
		$this->trigramClass->indexWords("This is the first sentence?? This is the second sentence!! This is the third sentence..");
		$this->_wordsToTrigrams_indexer($this->trigramClass->wordarray);
		$actual = $this->trigramClass->wordsToTrigrams();
		$this->assertEquals($expected,$actual,"Testing of a very long word in word array. (question)");					
	}
	
};

?>