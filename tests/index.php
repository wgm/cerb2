<?php
$title = 'Cerberus Helpdesk - Complete Unit Test Suite';

define("NO_SESSION",true);

require_once("../site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/unit_test/cer_UnitTest.class.php");
?>
<html>
<head>
	<title><?php echo $title; ?></title>
    <STYLE TYPE="text/css">
    	<?php include ("stylesheet.css"); ?>
    </STYLE>
</head>
<body>

<h1><?php echo $title; ?></h1>

<?php 

$test_suite = new cer_TestSuite();

// [JAS]: cerberus-api/custom_fields/cer_CustomField.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.test.php");
$test_suite->addTest( new cer_CustomField_Test("test_CustomField") );

// [JAS]: cerberus-api/unit_test/cer_UnitTest.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/unit_test/cer_UnitTest.test.php");
$test_suite->addTest( new cer_Assert_Test("testAssert") );
$test_suite->addTest( new cer_Assert_Test("testAssertEquals") );
$test_suite->addTest( new cer_Assert_Test("testAssertRegExp") );

// [JAS]: cerberus-api/bayesian/cer_Bayesian.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_Bayesian.test.php");
$test_suite->addTest( new cer_Bayesian_Test("test_combine_p") );

// [JAS]: cerberus-api/utility/date/cer_DateTime.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.test.php");
$test_suite->addTest( new cer_Date_Test("test_parseDate") );
$test_suite->addTest( new cer_Date_Test("test_getDate") );
$test_suite->addTest( new cer_Date_Test("test__noneAreEmpty") );
$test_suite->addTest( new cer_Date_Test("test_rfcDateAsDbDate") );
$test_suite->addTest( new cer_Date_Test("test_changeGMTOffset") );

// [JAS]: cerberus-api/utility/date/cer_DateTimeFormat.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.test.php");
$test_suite->addTest( new cer_DateTimeFormat_Test("test_secsAsEnglishString") );

// [JAS]: cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.test.php");
$test_suite->addTest( new cer_WeightedAverage_Test("test_addSample") );
$test_suite->addTest( new cer_WeightedAverage_Test("test_getAverage") );

// [BGH]: cerberus-api/utility/text/cer_Whitespace.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.test.php");
$test_suite->addTest( new cer_Whitespace_Test("test_mergeWhitespace") );

// [JAS]: cerberus-api/utility/text/cer_String.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_String.test.php");
$test_suite->addTest( new cer_String_Test("test_strSplit") );

// [JAS]: cerberus-api/ticket/cer_ThreadContentHandler.class.php
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.test.php");
$test_suite->addTest( new cer_ThreadContentHandler_Test("test_cer_ThreadContentHandler") );

// [BGH]: cerberus-api/trigrams/cer_Trigram.test.php
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_Trigram.test.php");
$test_suite->addTest( new cer_Trigram_Test("test_cleanPunctuation") );
$test_suite->addTest( new cer_Trigram_Test("test_indexWords") );
$test_suite->addTest( new cer_Trigram_Test("test_wordsToTrigrams") );

// [BGH]: cerberus-api/trigrams/cer_TrigramCerby.test.php
require_once(FILESYSTEM_PATH . "cerberus-api/trigrams/cer_TrigramCerby.test.php");
$test_suite->addTest( new cer_TrigramCerby_Test("test_getSuggestion") );
//$test_suite->addTest( new cer_TrigramCerby_Test("test_goodSuggestion") );
//$test_suite->addTest( new cer_TrigramCerby_Test("test_badSuggestion") );
$test_suite->addTest( new cer_TrigramCerby_Test("test_ask") );
$test_suite->addTest( new cer_TrigramCerby_Test("test_getSimilar") );

// [BGH]: cerberus-api/search/cer_SearchIndex.test.php
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.test.php");
$test_suite->addTest( new cer_SearchIndex_Test("test_cleanPunctuation") );
$test_suite->addTest( new cer_SearchIndex_Test("test_indexWords") );
$test_suite->addTest( new cer_SearchIndex_Test("test_removeExcludedKeywords") );

$result = new cer_TestResult();
$test_suite->run($result);
$result->report();

?>

</body>

</html>

