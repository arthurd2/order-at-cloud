<?php


class OrderCloudTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @ depends testQUAextimp
     * @ expectedException Exception
     */
    public  function setUp(){
        foreach (Costs::getClasses() as $class) Costs::del($class);
        foreach (Qualifiers::getClasses() as $class) Qualifiers::del($class);
        foreach (RulesFreeOfContext::getClasses() as $class) RulesFreeOfContext::del($class);
        foreach (RulesSensitiveToTheContext::getClasses() as $class) RulesSensitiveToTheContext::del($class);
    }


    public function testIsNonDominant() {
        Qualifiers::add('QUAmirror');
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp1 = Cvmp::buildCVmpByPlacements($places);
        $cvmp2 = Cvmp::buildCVmpByPlacements($places);

        $cvmp1['mirror'] = 11;
        $cvmp2['mirror'] = 12;
        $x = null;
        $oc = new OrderCloud($x);

        $this->assertTrue($oc->isNonDominanted($cvmp1,$cvmp2));
        $this->assertFalse($oc->isNonDominanted($cvmp1,$cvmp1));
        $this->assertFalse($oc->isNonDominanted($cvmp2,$cvmp1));

    }
    public function testSelectLoweVM() {
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        $x=null;
        $oc = new OrderCloud($x);
        $ivm = [];
        $this->assertEquals('v1',$oc->selectLowerVm($cvmp,$ivm));
        $ivm[] = 'v1';
        $this->assertEquals('v2',$oc->selectLowerVm($cvmp,$ivm));
    }
    /**
     * @depends testSelectLoweVM
     * @expectedException Exception
     */
    public function testSelectLoweVMWithIgnoreVmFull() {
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        
        $oc = new OrderCloud($x=null);
        $ivm = ['v1','v2','v3','v4','v5'];
        $oc->selectLowerVm($cvmp,$ivm);
    }
    public function testGenCVMPs() {
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp = Cvmp::buildCVmpByPlacements($places);
        
        $x=null;
        $oc = new OrderCloud($x);
        RFCextImpRules::disable();
        RSCextImpRules::disable();

        $cvmps = $oc->generateCVMP($cvmp,'v1');
        $this->assertEquals(2,count($cvmps));

        RFCextImpRules::enable();
        RSCextImpRules::enable();
    }

    public function testgetCvmpWithMaxCostBenefit() {
        Qualifiers::add('QUAmirror');
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp0 = Cvmp::buildCVmpByPlacements($places);
        $cvmp1 = Cvmp::buildCVmpByPlacements($places);
        $cvmp2 = Cvmp::buildCVmpByPlacements($places);

        $cvmp1['mirror'] = 11;
        $cvmp2['mirror'] = 12;
        
        $oc = new OrderCloud($cvmp0);
        $pareto = [$cvmp1,$cvmp2];
        $best = $oc->getCvmpWithMaxCostBenefit($pareto);
        unset($best[OC_TMP]);
        $this->assertEquals($cvmp2,$best);
    }

    public function testOrganizeDummy() {
        Costs::add('CostTestMigrations');

        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp0 = Cvmp::buildCVmpByPlacements($places);

        $oc = new OrderCloud($cvmp0);
        
        $best = $oc->organize($cvmp0);

        unset($best[OC_TMP]);
        unset($cvmp0[OC_TMP]);
        unset($best[OC_LAST_ADD_VM]);
        unset($best[OC_LAST_REM_VM]);
        unset($best[OC_LAST_ADD_PM]);
        unset($best[OC_LAST_REM_PM]);
             
   
        $this->assertEquals($cvmp0,$best);
    }

    public function testOrganize() {
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp0 = Cvmp::buildCVmpByPlacements($places);
        $cvmp1 = Cvmp::buildCVmpByPlacements($places);
        $cvmp1['mirror'] = 11;
        $oc = new OrderCloud($cvmp0);

        Qualifiers::add('QUAconsolidate');
        $best = $oc->organize($cvmp1);
        unset($best[OC_TMP]);
        $x = array_search(5, $best['rpm']);
        $this->assertEquals(5,$best['rpm'][$x]);
        Qualifiers::del('QUAconsolidate');
    }

    public function testLoadClasses(){
        Qualifiers::add('QUAincomplete');

        Cache::$cache->delete('load_test');
        $places = ['v1' => 'p1', 'v2' => 'p1', 'v3' => 'p2', 'v4' => 'p2', 'v5' => 'p3', ];
        $cvmp0 = Cvmp::buildCVmpByPlacements($places);


        $this->assertFalse(Cache::$cache->get('load_test'));
        $oc = new OrderCloud($cvmp0);
        $this->assertTrue(Cache::$cache->get('load_test'));
    }

}

