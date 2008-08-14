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
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
\file cer_UnitTest.class.php
\brief Unit Testing Classes

Classes for performing unit tests on objects and their methods.

\author Jeff Standen, WebGroup Media LLC. <jeff@webgroupmedia.com>
\author Fred Yankowski, OntoSys, Inc. <fred@ontosys.com>
\date 2004
*/

/*
[JAS]: 
	Completely dissected and rewrote PHPunit (http://phpunit.sourceforge.net/) for this code.
	
	The original code was about 2 years old and didn't work with the later versions of PHP4,
	due to various issues with pass by reference, etc.
	
	We do use most of the same variables and concepts from PHPunit, which was based 
	originally on JUnit.
	
	The original code is BSD licensed:
	
	Written by Fred Yankowski <fred@ontosys.com>
	OntoSys, Inc  <http://www.OntoSys.com>
	
	Copyright (c) 2000 Fred Yankowski

	Permission is hereby granted, free of charge, to any person
	obtaining a copy of this software and associated documentation
	files (the "Software"), to deal in the Software without
	restriction, including without limitation the rights to use, copy,
	modify, merge, publish, distribute, sublicense, and/or sell copies
	of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be
	included in all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
	BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
	ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
	CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/

@error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE |
		E_CORE_ERROR | E_CORE_WARNING);

/** @addtogroup unit_tests Unit Testing
 *
 * Unit Testing Classes
 *
 * @{
 */
		
/**
 * Custom error handler for unit tests.  Logs any PHP errors as unit test 
 * exceptions rather than printing to the screen or terminating the script.
 * 
 * \param $errno (int)
 * \param $errstr (string)
 * \param $errfile (string)
 * \param $errline (int)
 * \return void
 */
function cerUnitTest_error_handler($errno, $errstr, $errfile, $errline) {
	global $cerUnitTest_testRunning;
	$cerUnitTest_testRunning[0]->fail("<B>PHP ERROR:</B> ".$errstr." <B>in</B> ".$errfile." <B>at line</B> ".$errline);
}
    
/*! \brief Assertion Class
 * 
 * Allows assertion checks for unit testing and throws exceptions if they fail.
 */
class cer_Assert {
	/*!
	Performs an assertion check.  Logs a failure if false.
	
	\param $boolean (boolean)
	\param $message (string)
	\return void
	*/
	function assert($boolean,$message=0) {
		if(!$boolean)
			$this->fail($message);			
	}
	
	/*!
	 * Compares two variables/objects/classes, throws exception if they're not equal.  If comparing objects/classes, 
	 * an equals() method will be used if it exists.
	 * 
	 * \param $expected (mixed)
	 * \param $actual (mixed)
	 * \param $message (string)
	 * \return void
	 */
	function assertEquals($expected, $actual, $message=0) {
		if(gettype($expected) != gettype($actual)) {
			$this->failNotEquals($expected,$actual,"expected",$message);
			return;
		}
		
		if (is_object($expected)) {
			if (strtolower(get_class($expected)) != strtolower(get_class($actual))) {
				$this->failNotEquals($expected, $actual, "expected", $message);
				return;
			}
			if (method_exists($expected, "equals")) {
				if (! $expected->equals($actual)) {
					$this->failNotEquals($expected, $actual, "expected", $message);
				}
				return;
			}
		}
		
		if (is_null($expected) != is_null($actual)) {
			$this->failNotEquals($expected, $actual, "expected", $message);
			return;
		}
		
		if ($expected != $actual) {
			$this->failNotEquals($expected, $actual, "expected", $message);
		}
	}
	

	/*!
	 * Compares a value to a regular expression.  Throws an exception if \c $actual doesn't match the regular expression \c $regexp.
	 * 
	 * \param $regexp (regexp)
	 * \param $actual (mixed)
	 * \param $message (string)
	 * \return void
	 */
	function assertRegexp($regexp, $actual, $message=false) {
		if (! preg_match($regexp, $actual)) {
		  $this->failNotEquals($regexp, $actual, "pattern", $message);
		}
	}
	
	/*!
	 * Compared multiple line strings.  Throws an exception if \c $string0 and \c $string1 don't match.
	 * 
	 * \param $string0 (string)
	 * \param $string1 (string)
	 * \param $message (string)
	 * \return void
	 */
	function assertEqualsMultilineStrings($string0, $string1, $message = "") {
		$lines0 = split("\n", $string0);
		$lines1 = split("\n", $string1);
		if (sizeof($lines0) != sizeof($lines1)) {
		  $this->failNotEquals(sizeof($lines0)." line(s)",
		                       sizeof($lines1)." line(s)", "expected", $message);
		}
		for($i=0; $i< sizeof($lines0); $i++) {
		  $this->assertEquals(trim($lines0[$i]),
		                      trim($lines1[$i]),
		                      "line ".($i+1)." of multiline strings differ. " . $message); 
		}
	}
	
	/*!
	 * Formats a value for printing assertion failures. Protected.
 	 * 	
	 * \param $value (mixed)
	 * \param $class (class)
	 * \return string
	 */
	function _formatValue($value, $class="") {
		$translateValue = $value;
		
		if (is_object($value)) {
		  if (method_exists($value, "toString") ) {
			  $translateValue = $value->toString();
		  }
		  else {
			  $translateValue = serialize($value);
		  }
		}
		else if (is_array($value)) {
			$translateValue = serialize($value);
		}
		
		$htmlValue = "<code class=\"$class\">"
			. @htmlspecialchars($translateValue, ENT_COMPAT, LANG_CHARSET_CODE) . "</code>";
		
		if (is_bool($value)) {
		  $htmlValue = $value ? "<i>true</i>" : "<i>false</i>";
		}
		elseif (is_null($value)) {
		  $htmlValue = "<i>null</i>";
		}
		
		$htmlValue .= "&nbsp;&nbsp;&nbsp;<span class=\"typeinfo\">";
		$htmlValue .= "type:" . gettype($value);
		$htmlValue .= is_object($value) ? ", class:" . strtolower(get_class($value)) : "";
		$htmlValue .= "</span>";
		
		return $htmlValue;
	}

  	/*!
  	 * Prints an exception when an assertEquals() call fails.
  	 * 
  	 * \param $expected (mixed) The expected value being compared.
  	 * \param $actual (mixed) The actual value being compared.
  	 * \param $expected_label (string) A custom label for the failure report.
  	 * \param $message (string) A custom error message.
  	 * \return void
  	 */	
	function failNotEquals($expected,$actual,$expected_label,$message) {
		$str = $message ? ($message . ' ') : '';
		$str .= "<br>";
		$str .= sprintf("%s<br>%s",
			    $this->_formatValue($expected, "expected"),
			    $this->_formatValue($actual, "actual"));
		
		$this->fail($str);
	}
};


/*! \brief Exception Class
 * 
 * Handles thrown exceptions if unit test assertions fail.
 */
class cer_Exception {
	var $message;
	var $type;

	/*!
	 * \param $message (string) Exception message.
	 * \param $type (string) FAILURE or ERROR
	 * \return void
	 */
	function cer_Exception($message, $type = 'FAILURE') {
		$this->message = $message;
		$this->type = $type;
	}
	
	/*!
	 * Returns exception message.
	 * 
	 * \return string
	 */
	function getMessage() {
		return $this->message;
	}
	
	/*!
	 * Returns exception type.
	 * 
	 * \return string
	 */
	function getType() {
		return $this->type;
	}
};


/*! \brief Unit Test Case Class
 * 
 * A Unit Test Case is used to test the methods of an object.  A group of Test 
 * Cases (cer_TestCase) is called a Test Suite (cer_TestSuite).
 */
class cer_TestCase extends cer_Assert {
	var $fName = null;
	var $fClassName = null;
	var $fExceptions = array();
	var $fResult = null;
	
	/*!
	 * \param $name (string) The method name to invoke for unit testing.
	 * \return void
	 */
	function cer_TestCase($name) {
		$this->fName = $name;
	}
	
	/*!
	 * Returns the name of the method being invoked for unit testing.
 	 * 	
	 * \return string
	 */
	function name() {
		return $this->fName;
	}
	
	/*!
	 * Logs an exception.
	 * 
	 * \param $message (string)
	 * \return void	
	 */
	function fail($message=0) {
		$this->fExceptions[] = new cer_Exception($message,'FAILURE');
	}
	
	/*!
	 * Returns the name of the class performing the unit tests.
 	 * 	
	 * \return string
	 */
	function classname() {
	  if (isset($this->fClassName)) {
		return $this->fClassName;
	  } else {
		return strtolower(get_class($this));
	  }
	}
	
	/*!
	 * Runs the unit test case and logs the results to a cer_TestResult object.
	 * Passes control to the cer_TestResult object, which calls each test case
	 * on cer_TestCase::run_bare().
	 * 
	 * \param $testResult (&cer_TestResult)
	 * \return cer_TestResult
	 */
	function run(&$testResult) {
   		$this->fResult = $testResult;
   		$testResult->run($this);
   		$this->fResult = 0;
    
   		return $testResult;
	}
	
	/*!
	 * Runs the specific unit test method passed in the constructor of the cer_TestCase object.
	 * 
	 * \return void
	 */
	function runTest() {
		global $cerUnitTest_testRunning;
		
		// [JAS]: Log PHP Errors to the TestCase object itself.
		$cerUnitTest_testRunning[0] = &$this;
		
		$name = $this->name();
		$old_handler = set_error_handler("cerUnitTest_error_handler");

		//* \todo [JAS]: Do error checking for method exists.
	
		$this->$name();

		if($old_handler)		
			set_error_handler($old_handler);
			
		$cerUnitTest_testRunning = null;

	}
	
	/*!
	 * Sets up the unit test case object before the test is run.  This should be overriden in the child object.
	 */
	function setUp() {
		// [JAS]: Expect override.
	}
	
	/*!
	 * Cleans up after the unit test case has run.  This should be overriden in the child object.
	 */
	function tearDown() {
		// [JAS]: Expect override.
	}
	
	/*!
	 * Runs a bare unit test.  This is called by the cer_TestResult object for each cer_TestCase in a cer_TestSuite.
	 * 
	 * \return void
	 */
	function runBare() {
		$this->setUp();
		$this->runTest();
		$this->tearDown();		
	}

	/*!
	 * Creates a new cer_TestResult object for use by cer_TestCase.
	 * 
	 * \return cer_TestResult
	 */
	function _createResult() {
		return new cer_TestResult();
	}
	
	/*!
	 * Returns \c true if any exceptions of type 'FAILURE' occurred, otherwise returns \c false.
	 * 
	 * \return boolean
	 */
	function failed() {
		reset($this->fExceptions);
		
		foreach($this->fExceptions as $key => $exception) {
			if ($exception->type == 'FAILURE')
				return true;
		}
		
		return false;
	}
	
	/*!
	 * Returns an array of cer_Exception exceptions from the unit test case run.
	 * 
	 * \return cer_Exception[]
	 */
	function getExceptions() {
		return $this->fExceptions;
	}
	
};



/*! \brief Unit Test Suite Class
 * 
 * The cer_TestSuite class holds a collection of cer_TestCase objects to be run as a group (suite).
 */
class cer_TestSuite {
	
	//! Array of cer_TestCase objects.
	var $fTests = array(); 
	
	//! The name of the current cer_TestCase object running (for reporting progess).
	var $fClassName = null; 
	
	/*!
	 * Adds a new cer_TestCase to the test suite.
	 * 
	 * \param $test (cer_TestCase)
	 * \return void
	 */
	function addTest($test) {
		$this->fTests[] = $test;
	}
	
	/*!
	 * Runs all the unit test cases in the test suite.
	 * 
	 * \param $testResult (&cer_TestResult)
	 * \return void
	 */
	function run(&$testResult) {
		foreach ($this->fTests as $test) {
			$test->run($testResult);
		}
	}
};



/*! Unit Test Failure Class
 * 
 * Stores an exception from a cer_TestCase run.
 */
class cer_TestFailure {
	
	//! The name of the unit test method that failed.
	var $fFailedTestName = null;
	
	//! The exception message returned during the unit test failure.
	var $fException = null;
	
	/*!
	 * \param $test (&cer_TestCase)
	 * \param $exception (cer_Exception)
	 * \return void
	 */
	function cer_TestFailure(&$test, $exception) {
		$this->fFailedTestName = $test->name();
		$this->fException = $exception;
	}
	
	/*!
	 * Returns the name of the failed cer_TestCase unit test.
 	 * 	
	 * \return string
	 */
	function getTestName() {
		return $this->fFailedTestName;
	}
	
	/*!
	 * Returns the exception of the unit test failure.
	 * 
	 * \return cer_Exception
	 */
	function getException() {
		return $this->fException;
	}

	/*!
	 * Returns an array of exceptions for the unit test failure.
	 * 
	 * \return cer_Exception[]
	 */
	function getExceptions() {
		return array($this->fException);
	}

};


/*! \brief Unit Test Results Class
 * 
 * Initiates all unit tests (cer_TestCase) for a test suite (cer_TestSuite), 
 * stores the results of all tests and displays a summary report.
 */
class cer_TestResult {
	
	//! Array of cer_TestFailure objects (cer_Exception::type == 'FAILURE').
	var $fFailures = array();
	
	//! Array of cer_TestFailure objects (cer_Exception::type == 'ERROR').
	var $fErrors = array();
	
	//! The total number of tests run.
	var $fRunTests = 0;
	
	/*!
	 * Constructor
	 * 
	 * The constructor begins printing the test suite report when it's called.
	 */
	function cer_TestResult() {
		echo "<h2>Tests</h2>";
		
		echo "<TABLE CELLSPACING=\"1\" CELLPADDING=\"1\" BORDER=\"0\" WIDTH=\"90%\" ALIGN=\"CENTER\" class=\"details\">";
		echo "<TR><TH>Class</TH><TH>Function</TH><TH>Success?</TH></TR>";
	}
	
	/*!
	 * Prints the unit test report to the browser.
	 * 
	 * \return void
	 */
	function report() {
		echo "</TABLE>";
		
		$nRun = $this->countTests();
		$nFailures = $this->failureCount();
		echo "<h2>Summary</h2>";

		printf("<p>%s test%s run<br>", $nRun, ($nRun == 1) ? '' : 's');
		printf("%s failure%s.<br>\n", $nFailures, ($nFailures == 1) ? '' : 's');
		if ($nFailures == 0)
			return;

		echo "<h2>Failure Details</h2>";
		print("<ol>\n");
		$failures = $this->getFailures();

		foreach($failures as $i => $failure) {
			$failedTestName = $failure->getTestName();
			printf("<li>%s\n", $failedTestName);
			
			$exceptions = $failure->getExceptions();
			print("<ul>");
			foreach($exceptions as $na => $exception)
				printf("<li>%s\n", $exception->getMessage());
			print("</ul>");
		}

		print("</ol>\n");
	}
	
	/*!
	 * Runs the unit test (cer_TestCase) passed as argument 1.
	 * 
	 * \param $test (cer_TestCase)
	 * \return void
	 */
	function run(&$test) {
		$this->_startTest($test);
		$this->fRunTests++;
		
		$test->runBare();
		
		$exceptions = $test->getExceptions();		
		
		foreach($exceptions as $key => $exception) {
			
			if ($exception->type == 'ERROR') {
			    $this->addError($test, $exception);
			}
			else if ($exception->type == 'FAILURE') {
			    $this->addFailure($test, $exception);
			}
		}
		
		$this->_endTest($test);
	}
	
	/*!
	 * Logs a unit test error.  An error is unexpected behavior in the script.
	 * 
	 * \return void
	 */
	function addError(&$test, $exception) {
		$this->fErrors[] = new cer_TestFailure($test, $exception);
	}
	
	/*!
	 * Logs a unit test failure.  A failure may or may not be expected depending on the assertion.
	 * 
	 * \return void
	 */
	function addFailure(&$test, $exception) {
		$this->fFailures[] = new cer_TestFailure($test, $exception);
	}

	/*!
	 * Returns an array of unit test (cer_TestCase) failures (cer_TestFailure) from the test suite (cer_TestSuite).
	 * 
	 * \return cer_TestFailure[]
	 */
	function getFailures() {
		return $this->fFailures;
	}

	/*!
	 * Returns the number of tests (cer_TestCase) run during the test suite (cer_TestSuite).
	 * 
	 * \return int
	 */
	function countTests() {
		return $this->fRunTests;
	}
	
	/*!
	Returns the number of errors encountered during the test suite (cer_TestSuite) run.
	
	\return int
	*/
	function errorCount() {
		return count($this->fErrors);
	}
	
	/*!
	 * Returns the number of failures encountered during the test suite (cer_TestSuite) run.
	 * 
	 * \return int
	 */
	function failureCount() {
		return count($this->fFailures);
	}
	
	/*!
	 * Protected method called at the beginning of each test.  Sets up progress report HTML table rows.
	 * 
	 * \return void
	 */
	function _startTest($test) {
		echo sprintf("<TR><TD>%s</TD><TD>%s</TD>",
				$test->classname(),
				$test->name()
			);
		flush();
	}
	
	/*!
	 * Protected method called at the end of each test.  Prints status report and closes report HTML table rows.
	 * 
	 * \return void
	 */
	function _endTest($test) {
	    $outcome = $test->failed()
	       ? " class=\"Failure\">FAIL"
	       : " class=\"Pass\">OK";
	    printf("<TD$outcome</TD></TR>");
	    flush();
	}
	
};

/** @} */

?>