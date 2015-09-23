<?php

class QualifiersTest extends PHPUnit_Framework_TestCase
{
    

    public function setUp(){
        foreach (Costs::getClasses() as $class) Costs::del($class);
        foreach (Qualifiers::getClasses() as $class) Qualifiers::del($class);
        foreach (RulesFreeOfContext::getClasses() as $class) RulesFreeOfContext::del($class);
        foreach (RulesSensitiveToTheContext::getClasses() as $class) RulesSensitiveToTheContext::del($class);
    }
    /**
     * @expectedException Exception
     */
    public function testExceptionUnkownClass() {
        $handler = Qualifiers::getInstance();
        $this->assertEquals('Qualifiers', get_class($handler), "Class Does not match");
        Qualifiers::add('xxx');
    }
    
    /**
     * @expectedException Exception
     */
    public function testExtendQua() {
        Qualifiers::add('QUAext');
        $this->assertTrue(False, 'Accept a non implemented InterfaceQualifier Class');
    }
    
    /**
     * @expectedException Exception
     */
    public function testImplementQua() {
        Qualifiers::add('QUAimp');
        $this->assertTrue(False, 'Accept a non Qualifier extended Class');
    }
    
    /**
     * @depends testImplementQua
     */
    public function testEvaluate() {
        Qualifiers::add('QUAtest');
        $this->assertEquals(1, count(Qualifiers::getClasses()), "Wrong number of Qualifiers");
        
        //Qualifiers::getClasses()
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $expected = ['v1' => 4, 'v2' => 4, 'v3' => 4, 'v4' => 4, 'v5' => 4, ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        
        $this->assertEquals($expected, Qualifiers::getEvaluation($cvmp), "Not expected evaluation");
    }
    
    /**
     * @depends testEvaluate
     */
    public function testEvaluate2() {
        Qualifiers::add('QUAtest');
        Qualifiers::add('QUAmirror');
        $this->assertEquals(2, count(Qualifiers::getClasses()), "Wrong number of Qualifiers");
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $expected = ['v1' => 40, 'v2' => 40, 'v3' => 40, 'v4' => 40, 'v5' => 40, ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        $this->assertEquals($expected, Qualifiers::getEvaluation($cvmp), "");
    }
    
    /**
     * @depends testEvaluate2
     */
    public function testBenefit() {
        Qualifiers::add('QUAtest');
        Qualifiers::add('QUAmirror');
        //Qualifiers::getClasses()
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        $this->assertEquals(200, Qualifiers::getBenefit($cvmp), "");
        unset($cvmp[OC_TMP]);
        $cvmp['mirror'] = 11;
        $this->assertEquals(220, Qualifiers::getBenefit($cvmp), "");
    }
    
    /**
     * @depends testBenefit
     */
    public function testCostBenefit() {
        Qualifiers::add('QUAtest');
        Qualifiers::add('QUAmirror');
        Costs::add('COSTextimp');
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        
        $realCvmp = Cvmp::buildCVmpByPlacements($places);
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        $cvmp['mirror'] = 11;
        
        // 220 - 200 / 2 (costTest = 2)
        $this->assertEquals(10, Qualifiers::getCostBenefit($cvmp), "");
    }
    
   
    /**
     * @depends testCostBenefit
     * @expectedException Exception
     */
    public function testIdentifyFakeEvaluate() {
        Qualifiers::add('QUAfake');
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        
        $handler = Qualifiers::getEvaluation($cvmp);
        $this->assertTrue(False, "Should Raise a Exception from Fake evaluation QUAextimp ");
    }


    /**
     * @depends testIdentifyFakeEvaluate
     * @expectedException Exception
     **/
    public function testIncompleteAndCrapEvaluations() {
        Qualifiers::add('QUAincomplete');
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        
        $evals = @Qualifiers::getEvaluation($cvmp);
        $this->assertEquals(2,$evals['v1'], "Value !match");
        unset($evals['v1']);
        foreach ($evals as $vm => $eval) {
            $this->assertEquals(1,$eval, "Value of '$vm' !match");
        }
    }




}

