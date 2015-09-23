<?php


class RulesFreeOfContextTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @expectedException Exception
     */
    public function testExceptionRulesFreeOfContext() {
        $handler = RulesFreeOfContext::getInstance();
        $this->assertEquals('RulesFreeOfContext', get_class($handler), "Class Does not match");
        $handler->add('xxx');
    }
    
    /**
     * @expectedException Exception
     */
    public function testExtendRule() {
        RulesFreeOfContext::add('RFCextendRule');
        $this->assertTrue(False,'Accpet an non extended Rule Class');
    }

    /**
     * @expectedException Exception
     */
    public function testImplementRule() {
        RulesFreeOfContext::add('RFCimplementRule');
        $this->assertTrue(False,'Accpet an non implemented RuleFreeOfContext Class');
    }

     public function testIsAllowedEmptyClasses() {
        $this->assertTrue(RulesFreeOfContext::isAllowed(1,2), "Class Does not match");
    }   
	/**
     * @depends testIsAllowedEmptyClasses
     */
    public function testExtImpRules() {
        RulesFreeOfContext::add('RFCextImpRules');
        $this->assertTrue(True, "Should accept the other class");
    }
    /**
     * @depends testExtImpRules
     */
    public function testIsAllowed() {
        $this->assertTrue(RulesFreeOfContext::isAllowed(1,2), "Class Does not match");
        $this->assertFalse(RulesFreeOfContext::isAllowed(2,2), "Class Does not match");
    }   
}


