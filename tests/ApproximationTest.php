<?php
class ApproximationTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        
        foreach (Costs::getClasses() as $class) Costs::del($class);
        foreach (Qualifiers::getClasses() as $class) Qualifiers::del($class);
        foreach (RulesFreeOfContext::getClasses() as $class) RulesFreeOfContext::del($class);
        foreach (RulesSensitiveToTheContext::getClasses() as $class) RulesSensitiveToTheContext::del($class);
        
        RulesFreeOfContext::add('RfcLiveMigration');
        RulesFreeOfContext::add('RfcClusterCoherence');
    }
    public function testPossibilities() {
        $cvmp = HelperVmData::getRealCvmp(608);
        error_log(Approximation::getNumberOfPossibilitiesBasedOnRfcAndCvmp($cvmp));
    }
}
