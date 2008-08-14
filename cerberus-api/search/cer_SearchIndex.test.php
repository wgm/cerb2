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

require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/configuration/CerConfiguration.class.php");

class cer_SearchIndex_Test extends cer_TestCase {
	var $searchClass = null;		//<! Fixture for cer_SearchIndex
	
	function cer_SearchIndex_Test($name) {
		$this->cer_TestCase($name); // [BGH]: Call the parent object constructor.
	}
	
	function setUp() {
		$this->searchClass = new cer_SearchIndex();
	}
	
	function tearDown() {
		$this->searchClass = null;
	}
	
	function test_cleanPunctuation() {
		$expected = array("WHAT");
		$actual = cer_SearchIndex::cleanPunctuation(array("WHAT???"));
		$this->assertEquals($expected,$actual,"Failed to condense many identical special chars.");

		$expected = array("WHAT");
		$actual = cer_SearchIndex::cleanPunctuation(array("WHAT!!?"));
		$this->assertEquals($expected,$actual,"Failed, Condensed special chars.");
		
		$expected = array("WHAT");
		$actual = cer_SearchIndex::cleanPunctuation(array("WHAT!!?"));
		$this->assertEquals($expected,$actual,"Failed, did not strip whitespace from edges.");

		$expected = array("WHAT");
		$actual = cer_SearchIndex::cleanPunctuation(array("!!WHAT"));
		$this->assertEquals($expected,$actual,"Failed, Did not condense prefixed special chars. (even number)");
		
		$expected = array("WHAT");
		$actual = cer_SearchIndex::cleanPunctuation(array("!!!!!WHAT"));
		$this->assertEquals($expected,$actual,"Failed, Did not condense prefixed special chars. (odd number)");

		$expected = array("WHAT");
		$actual = cer_SearchIndex::cleanPunctuation(array("!!!!!%%WHAT"));
		$this->assertEquals($expected,$actual,"Failed, Did not condense specials prefixing specials.");
		
		$expected = array("WHAT","happened","here");
		$actual = cer_SearchIndex::cleanPunctuation(array("!!!!!WHAT","!!happened!","here!?!??"));
		$this->assertEquals($expected,$actual,"Failed substring condensing.");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 1)");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 2)");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","!!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 3)");

		$expected = array(0=>"happened",2=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","!!!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 4)");

		$expected = array(0=>"happened",3=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","?","!!","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (length of 1, length of 2)");

		$expected = array(0=>"happened",5=>"a",31=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","...","!!!","???","@@@","a",",,,","###","%%%","^^^","&&&","***","(((",")))","___","+++","===","{{{","}}}","[[[","]]]","\\\\\\","|||",";;;",":::","\"\"\"","<<<",">>>","///","~~~","---","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (odd number)");
		
		$expected = array(0=>"happened",31=>"here");
		$actual = cer_SearchIndex::cleanPunctuation(array("happened","!@#$%","..","!!","??","@@",",,","##","%%","^^","&&","**","((","))","__","++","==","{{","}}","[[","]]","\\\\","||",";;","::","\"\"","<<",">>","//","~~","--","here"));
		$this->assertEquals($expected,$actual,"Failed removal of special character only words. (even number)");		
		
		$expected = array("WHAT%","happened*","here");
		$actual = cer_SearchIndex::cleanPunctuation(array("WHAT%","!!happened*","here!?!??"), 1);
		$this->assertEquals($expected,$actual,"Failed to build search string");
	}

	
	function test_indexWords() {
		$cfg = CerConfiguration::getInstance();
		$index_numbers = @$cfg->settings["search_index_numbers"];
		
		$expected = array();
		$actual = $this->searchClass->indexWords("a b c");
		$this->assertEquals($expected,$actual,"Simple a b c Test.");

		$expected = array(0=>"this",3=>"larger",4=>"test");
		$actual = $this->searchClass->indexWords("This is a larger test!");
		$this->assertEquals($expected,$actual,"Longer sentence test.");

		$expected = array(0=>"this",1=>"test",4=>"test",6=>"punctuation");
		$actual = $this->searchClass->indexWords("This TEST!! Is a !!Test! of Punctuation!!");
		$this->assertEquals($expected,$actual,"Longer test with punctuation.");
		
		$expected = array(0=>"this",1=>"test",4=>"test",6=>"punctuation");
		$actual = $this->searchClass->indexWords("This\tTEST!!\rIs a !!Test!\r\nof Punctuation!!");
		$this->assertEquals($expected,$actual,"Longer test with extra white characters.");	
	
		$expected = array(0=>"this",1=>"test",4=>"test",6=>"punctuation");
		$actual = $this->searchClass->indexWords("This  \tTEST!!\r\r\rIs a  !!Test! \r\nof Punctuation!!");
		$this->assertEquals($expected,$actual,"Longer test with extra doubled up white characters.");	

//		$expected = array("this"=>0,"mysql"=>0,"escaping"=>0,"test"=>0);
//		$this->searchClass->indexWords("This is a mysql escaping test!");
//		$actual = $this->searchClass->wordarray_sql;
//		$this->assertEquals($expected,$actual,"Escaping test for mysql word array. (without specials)");
		
//		$expected = array("thi\\'s"=>0,"my\\0sql"=>0,"escaping"=>0,"test"=>0);
//		$this->searchClass->indexWords("Thi's is a my\0sql\n \\escaping \"test!");
//		$actual = $this->searchClass->wordarray_sql;
//		$this->assertEquals($expected,$actual,"Escaping test for mysql word array. (with specials)");		

//		$expected = array("testing"=>0,"100"=>0,"char"=>0,"length"=>0,"word"=>0,"woo"=>0);
//		$this->searchClass->indexWords("testing a 100 char length word aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeeeeeeeeffffffffffgggggggggghhhhhhhhhhiiiiiiiiiijjjjjjjjjj woo!", 1);
//		$actual = $this->searchClass->wordarray_sql;
//		$this->assertEquals($expected,$actual,"Testing of a very long word in sql array.");		

		// [JAS]: This test needs to check the index words setting
		if(!$index_numbers) {
			$expected = array("testing"=>-1,"char"=>-1,"length"=>-1,"word"=>-1,"woo"=>-1);		
		} else {
			$expected = array("testing"=>-1,"100"=>-1,"char"=>-1,"length"=>-1,"word"=>-1,"woo"=>-1);
		}
		$this->searchClass->indexWords("testing a 100 char length word aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeeeeeeeeffffffffffgggggggggghhhhhhhhhhiiiiiiiiiijjjjjjjjjj woo!", 1);
		$actual = $this->searchClass->wordarray;
		$this->assertEquals($expected,$actual,"Testing of a very long word in word array.");		

		if(!$index_numbers) {
			$expected = array();		
		} else {
			$expected = array("testing"=>-1,"100"=>-1,"char"=>-1,"length"=>-1,"word"=>-1,"woo"=>-1);
		}
		$expected = array();
		$actual = $this->searchClass->indexWords("1 2 3 4 5 6 7 8 9 10");
		$this->assertEquals($expected,$actual,"Simple index excluding numbers.");
	
		if(!$index_numbers) {
			$expected = array();		
		} else {
			$expected = array("testing"=>-1,"100"=>-1,"char"=>-1,"length"=>-1,"word"=>-1,"woo"=>-1);
		}
		$actual = $this->searchClass->indexWords("1 2 3 4 5 6 7 8 9 10");
		$this->assertEquals($expected,$actual,"Simple index including numbers.", 1);

		if(!$index_numbers) {
			$expected = array();		
		} else {
			$expected = array("100","200","300","400","500","600","700","800","900","1000");
		}
		$actual = $this->searchClass->indexWords("100 200 300 400 500 600 700 800 900 1000", 1);
		$this->assertEquals($expected,$actual,"Simple index including numbers.");
	}

	function test_removeExcludedKeywords() {
		$this->searchClass->wordarray['the']=1;
		$this->searchClass->wordarray['for']=2;
		$this->searchClass->wordarray['him']=3;
		$this->searchClass->wordarray['llc']=4;
		$this->searchClass->wordarray['job']=5;
		$this->searchClass->wordarray['key']=6;
		$this->searchClass->removeExcludedKeywords(); 
		$expected = array('him' => 3, 'llc' => 4, 'key' => 6, 'job' => 5);
		$this->assertEquals($expected,$this->searchClass->wordarray,"Failed to remove excluded keywords.");
	}	
	
};

?>