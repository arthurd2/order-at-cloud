<?php
class QuaConsolidatePmTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        foreach (Costs::getClasses() as $class) Costs::del($class);
        foreach (Qualifiers::getClasses() as $class) Qualifiers::del($class);
        foreach (RulesFreeOfContext::getClasses() as $class) RulesFreeOfContext::del($class);
        foreach (RulesSensitiveToTheContext::getClasses() as $class) RulesSensitiveToTheContext::del($class);
    }
    /**
     * @depends xxx
     */
    public function testReturn() {


    	QuaConsolidatePm::evaluate($cvmp);


    }
}
