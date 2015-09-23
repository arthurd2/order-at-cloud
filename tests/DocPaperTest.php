<?php
class DocPaperTest extends PHPUnit_Framework_TestCase
{
    protected $sizes = [350, 400, 450, 500, 550, 600];

    public function setUp() {
        if (extension_loaded('Xdebug')) $this->markTestSkipped('Please disable Xdebug before executing these tests. Skipping tests.');
        foreach (Costs::getClasses() as $class) Costs::del($class);
        foreach (Qualifiers::getClasses() as $class) Qualifiers::del($class);
        foreach (RulesFreeOfContext::getClasses() as $class) RulesFreeOfContext::del($class);
        foreach (RulesSensitiveToTheContext::getClasses() as $class) RulesSensitiveToTheContext::del($class);
        
        Costs::add('CostMigrations');
        CostMigrations::$maxCost = 20;
        
        RulesFreeOfContext::add('RfcLiveMigration');
        RulesFreeOfContext::add('RfcClusterCoherence');
        RulesSensitiveToTheContext::add('RscMemoryAvailability');
        RulesSensitiveToTheContext::add('RscMaxCost');
        
        Qualifiers::add('QUAconsolidatePm');
        Qualifiers::add('QuaDistributeServices');
        Qualifiers::add('QuaDistributeStoragePool');
    }
    
    /**
     * @depends xxxx
     */
    public function testVaryingVmsAndMax() {
        
        $maxs = [5, 10, 20];
        $g = new HelperTexGraph("Varying Max Migrations");
        
        foreach ($maxs as $max) {
            CostMigrations::$maxCost = $max;
            foreach ($this->sizes as $size) {
                Counter::reset();
                $realCvmp = HelperVmData::getRealCvmp($size);
                $oc = new OrderCloudExp($realCvmp);
                $bestCvmp = $oc->organize($realCvmp);
                
                $g->addCvmp($bestCvmp,$size,"Max. $max Migrations");
                
                Counter::stats(__FUNCTION__);
            }
        }
        
        //TODO colocar o calculo automatico das porcentagens
        error_log($g->finish());
        $this->assertTrue(True);
    }
    
    /**
     * Measure CB with a fixed time os 120 seconds.
     * @depends xxx
     */
    public function testTempoFixo120() {
        
        $switchs = [false, true];
        $g = new HelperTexGraph("Comparison Varying Time and Cost Threshold");
        
        foreach ($switchs as $timeBased) {
            if ($timeBased) {
                RulesFreeOfContext::add('RfcTime');
                RfcTime::$maxTime = 120;
                RulesSensitiveToTheContext::del('RscMaxCost');
                $line = 'Time Based';
            } 
            else {
                RulesFreeOfContext::del('RfcTime');
                RulesSensitiveToTheContext::add('RscMaxCost');
                $line = 'Migration Based';
            }
            
            foreach ($this->sizes as $size) {
                Counter::reset();
                $realCvmp = HelperVmData::getRealCvmp($size);
                $oc = new OrderCloudExp($realCvmp);
                $bestCvmp = $oc->organize($realCvmp);

                $g->addCvmp($bestCvmp,$size,$line); 

                Counter::stats(__FUNCTION__);
            }
            
        }
        error_log($g->finish());
        $this->assertTrue(True);
    }
    
    /**
     * Testar com e sem o uso do Rank Placement
     * Testar de uma forma aleatoria a pescagem das VMs
     * Comparar CB e media das avaliações
     * @depends xxx
     */
    public function testSelectionMethods() {
        $rankSwitch = ['random','rank','serial'];
        $g = new HelperTexGraph("Comparison Varying the Rank Utilisation");
        foreach ($rankSwitch as $rank) {
            foreach ($this->sizes as $size) {
                Counter::reset();
                $realCvmp = HelperVmData::getRealCvmp($size);
                $oc = new OrderCloudExp($realCvmp);
                $oc->rankSelect = $rank;
                $bestCvmp = $oc->organize($realCvmp);

                $g->addCvmp($bestCvmp,$size,"$rank");                
                
                Counter::stats(__FUNCTION__);
            }
        }
        error_log($g->finish());
        $this->assertTrue(true);
    }
    
    /**
     * Varia Pareto com Maior Beneficio.
     * Comparar CB e numero de CVMP
     * @depends xxx
     */
    public function testNonDominantVsGreaterBenefit() {
        $paretoSwitch = [true, false];
        
        $sizes = [6,8,10,12,14];

        $g = new HelperTexGraph("Comparison Varying Non-Dominanted Filter Utilisation");
        foreach ($paretoSwitch as $pareto) {
            foreach ($sizes as $size) {
                Counter::reset();
                CostMigrations::$maxCost = $size;
                $realCvmp = HelperVmData::getRealCvmp(600);
                $oc = new OrderCloudExp($realCvmp);
                $oc->paretoFilter = $pareto;
                $bestCvmp = $oc->organize($realCvmp);

                $g->addCvmp($bestCvmp,$size,"Pareto Filter($pareto)");
                
                Counter::stats(__FUNCTION__);
            }
        }
        error_log($g->finish());
        $this->assertTrue(True);
    }
    
    /**
     * Testar sem usar recursão final.
     * Comparar CB e numero de CVMP
     * @depends xxx
     */
    public function testWithoutFinalRecursion() {
        $recursionSwitch = [true,false];
        $g = new HelperTexGraph("Comparison Without Using the Final Recursion");
        
        foreach ($recursionSwitch as $recursion) {        
            foreach ($this->sizes as $size) {
                Counter::reset();
                $realCvmp = HelperVmData::getRealCvmp($size);
                $il = [];
                $oc = new OrderCloudExp($realCvmp);
                $bestCvmp = $oc->organize($realCvmp, $il, $recursion);

                $g->addCvmp($bestCvmp,$size,"Recursion($recursion)");
                            
                Counter::stats(__FUNCTION__);
            }
        }
        error_log($g->finish());
        $this->assertTrue(True);
    }

}
