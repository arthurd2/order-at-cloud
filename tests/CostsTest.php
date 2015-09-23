<?php

class CostsTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        foreach (Costs::getClasses() as $class) Costs::del($class);
        foreach (Qualifiers::getClasses() as $class) Qualifiers::del($class);
        foreach (RulesFreeOfContext::getClasses() as $class) RulesFreeOfContext::del($class);
        foreach (RulesSensitiveToTheContext::getClasses() as $class) RulesSensitiveToTheContext::del($class);
    }
    
    /**
     * @expectedException Exception
     */
    public function testExceptionRulesFreeOfContext() {
        $handler = Costs::getInstance();
        $this->assertEquals('Costs', get_class($handler), "Class Does not match");
        $handler->add('xxx');
    }
    
    /**
     * @expectedException Exception
     */
    public function testExtendRule() {
        Costs::add('COSText');
        $this->assertTrue(False, 'Accept a non implemented InterfaceCost Class');
    }
    
    /**
     * @expectedException Exception
     */
    public function testImplementRule() {
        Costs::add('COSTimp');
        $this->assertTrue(False, 'Accept a non Cost extended Class');
    }
    
    public function testGetCostEmptyClasses() {
        $x = [];
        $this->assertequals(1, Costs::getCost($x), "Cost should return 1 without classes");
    }
    
    /**
     * @depends testGetCostEmptyClasses
     */
    public function testCOSTextimp() {
        Costs::add('COSTextimp');
        $this->assertEquals(2, Costs::getCost($x), "");
    }
    
    //TODO testar del de Costs, ver se substitui mesmo.
    
}

