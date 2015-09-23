<?php
class PHPUnitTestListener implements PHPUnit_Framework_TestListener
{
    private $time;
    private $timeLimit = 0.1;
    
    public function startTest(PHPUnit_Framework_Test $test) {
        $this->time = microtime();
    }
    public function endTest(PHPUnit_Framework_Test $test, $time) {
        //$fmt =  "\nTime: %s ms Name: %s  (from: %s, to: %s)";
        $fmt =  "\nTime: %s ms Name: %s ";
        $current = microtime();
        $took = $current - $this->time;
        
        if($took > $this->timeLimit ) {
            error_log(sprintf($fmt,$took,$test->getName(),$this->time,$current));
        }
        
    }
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
    }
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
    }
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    }
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    }
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
    }
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
    }
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    }
}
