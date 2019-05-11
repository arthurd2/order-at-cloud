<?php
class OrderCloud
{
    private $rules;
    private $qualifiers;
    private $costs;
    private $debug = false;
    protected static $stop = false;
    
    public function __construct(&$currentCvmp,$debug = false) {
        $this->debug = $debug;
        Cache::$realCvmp = & $currentCvmp;
        $this->executeLoadClasses();
        if ($this->debug) {
            $this->handlerStatus();
        }
    }
    
    private function executeLoadClasses() {
        foreach (Costs::getClasses() as $class) $class::load();
        foreach (Qualifiers::getClasses() as $class) $class::load();
        foreach (RulesFreeOfContext::getClasses() as $class) $class::load();
        foreach (RulesSensitiveToTheContext::getClasses() as $class) $class::load();
    }
    
    public function organize(&$baseCvmp, &$ignoreVMs = [], $isMainInteration = true) {
        $class = get_called_class();
        $pareto = [];
        if (count($ignoreVMs) >= $baseCvmp['nvms'] or $class::$stop) return $baseCvmp;
        
        //Select Lower VM from the Rank
        $lowVM = $this->selectLowerVm($baseCvmp, $ignoreVMs);

        //generateCVMP
        $cvmps = $this->generateCVMP($baseCvmp, $lowVM);
        
        //foreach Possible CVMP
        $ignoreVMs[$lowVM] = $lowVM;
        foreach ($cvmps as $key => $cvmp) {
            Counter::$scenarios++;
            if (!$class::$stop and $this->isNonDominanted($baseCvmp, $cvmp)) {
                Counter::$pareto++;
                $pareto[] = $this->organize($cvmp, $ignoreVMs, false);
            }
        }
        //Taking the lowVM putted before
        array_pop($ignoreVMs);
        if (empty($pareto)) Counter::$leaf++;
        
        $pareto[] = $baseCvmp;
        $sCvmp = $this->getCvmpWithMaxCostBenefit($pareto);
        
        if ($isMainInteration) {
            $class::updateIgnoreVMs($ignoreVMs,$sCvmp,$lowVM);
            $sCvmp = $this->organize($sCvmp, $ignoreVMs, true);
        }
        return $sCvmp;
    }
    
    public function updateIgnoreVMs(&$ignoreVMs, &$sCvmp, $lowVM, &$extra = null) {
            $newLowVM = $this->selectLowerVm($sCvmp, $ignoreVMs);
            $ignoreVMs[$newLowVM] = $newLowVM;
    }
    public function getCvmpWithMaxCostBenefit(&$pareto) {
        //if(count($pareto) == 1) return  array_pop($pareto);

        $cvmpMax = array_pop($pareto);
        $cbMax = Qualifiers::getCostBenefit($cvmpMax);
        
        foreach ($pareto as $cvmp) {
            $cb = Qualifiers::getCostBenefit($cvmp);
            if ($cb > $cbMax) {
                $cbMax = $cb;
                $cvmpMax = $cvmp;
            }
        }
        return $cvmpMax;
    }
    public function generateCVMP($cvmp, $vm) {
        
        //TODO prettify this
        $newCvmps = [];
        $pm = $cvmp['vmp'][$vm];
        Cvmp::removeVm($cvmp, $vm);
        $pms = $cvmp['pmp'];
        unset($pms[$pm]);
        $pms = array_keys($pms);
        
        foreach ($pms as $pm) {
            if (RulesFreeOfContext::isAllowed($vm, $pm)) {
                $newCvmp = $cvmp;
                Cvmp::addVm($newCvmp, $vm, $pm);
                if (RulesSensitiveToTheContext::isAllowed($newCvmp)) {
                    $newCvmps[] = $newCvmp;
                }
            }
        }
        return $newCvmps;
    }
    
    public function selectLowerVm(&$cvmp, &$ignoreVMs) {

        if($cvmp['nvms'] <= count($ignoreVMs))
            throw new Exception("Number of IgnoredVMs is >= to te number of VMs", 1);

        $evalBase = Qualifiers::getEvaluation($cvmp);
        
        $ignore = array_flip($ignoreVMs);
        
        //TODO Valor max do int pode ser um problema pois vai estourar no
        $valueMin = PHP_INT_MAX;
        
        $vmMin = null;
        
        foreach ($evalBase as $vm => $value) {
            //if (!isset($ignore[$vm]) and (($value < $valueMin) or is_null($vmMin))) {
            if (!isset($ignore[$vm]) and ($value < $valueMin)) {
                $valueMin = $value;
                $vmMin = $vm;
            }
        }
        if (is_null($vmMin)) {
            throw new Exception("Couldnt find lower VM because the lower value is grater than the biggest INT", 1);
        }
        
        return $vmMin;
    }
    
    public function isNonDominanted(&$baseCvmp, &$candidateCvmp) {
        $evalBase = Qualifiers::getEvaluation($baseCvmp);
        $evalCand = Qualifiers::getEvaluation($candidateCvmp);
        $count = 0;
        foreach ($evalBase as $vm => $value) {
            $count += $evalCand[$vm] - $value;
            if ($value > $evalCand[$vm]) {
                return false;
            }
        }
        
        return (OC_ND_HIGH_CONVERGENCE) ? ($count > 0) : true;
    }

    public function handlerStatus()
    {
        $fmt = 'Class[%s] %s[%s]';
        error_log("\nCosts:");
        error_log("Main Cost Class[".Costs::getMainCost().']');
        foreach (Costs::getClasses() as $class) error_log(sprintf($fmt,$class,'Cost',$class::getMaxCost()));
        error_log("\nQualifiers:");
        foreach (Qualifiers::getClasses() as $class) error_log(sprintf($fmt,$class,'Weight',$class::getWeight()));
        error_log("\nRules Free Of Context:");
        foreach (RulesFreeOfContext::getClasses() as $class) error_log(sprintf($fmt,$class,'isValid',$class::isEnable()));
        error_log("\nRules Sensitive To The Context:");
        foreach (RulesSensitiveToTheContext::getClasses() as $class) error_log(sprintf($fmt,$class,'isValid',$class::isEnable()));
        echo PHP_EOL;
    }

    public function validate(&$scenario){
        $vms = array_keys($scenario['vmp']);
        $pms = array_keys($scenario['pmp']);

        foreach ($vms as $vm) {
            foreach ($pms as $pm) {
                if (!RulesFreeOfContext::isAllowed($vm,$pm)) return false;
            }
        }
        return RulesSensitiveToTheContext::isAllowed($scenario);
    }

    public function adapt(){
        $scenario  = &Cache::$realCvmp;

        //Get VMs which are not allowed by RFC
        $InvalidVMsRFC = $this->getVMsInvalidByRFC($scenario);

        //Take Invalid VMs out
        foreach ($InvalidVMsRFC as $vm) Cvmp::removeVm($scenario,$vm); 

        //Get VMs which are not allowed by RSC
        $InvalidVMsRSC = RulesSensitiveToTheContext::isAllowed($scenario)? [] : $this->getVMsInvalidByRSC($scenario);

        //Take Invalid VMs out
        foreach ($InvalidVMsRSC as $vm) Cvmp::removeVm($scenario,$vm); 

        //TODO Order VMs to input
        
        //Re-Insert VMs in the Cloud
        $unsupported = [];
        $invalidVms = array_merge($InvalidVMsRFC,$InvalidVMsRSC);
        foreach ($invalidVms as $vm) {
            try {
                $this->provisioning($vm);    
            } catch (Exception $e) {
                $unsupported[] = $vm;
            }
        }
        return $unsupported;
    }

    public function getVMsInvalidByRFC(&$scenario)
    {
        $invalidVms = [];
        //Validate each placement
        foreach ($scenario['vmp'] as $vm => $pm){
            //Save invalid VMs
            if(!RulesFreeOfContext::isAllowed($vm,$pm)) $invalidVms[] = $vm;
        }
        return $invalidVms;
    }

    public function getVMsInvalidByRSC(&$scenario)
    {
        $invalidVms = [];
        $validScenario = [];
        //Validate each placement
        foreach ($scenario['vmp'] as $vm => $pm){
            Cvmp::addVm($validScenario,$vm,$pm);
            //Save invalid VMs
            if(!RulesSensibleToTheContext::isAllowed($validScenario)){
                Cvmp::removeVm($validScenario,$vm,$pm);
                $invalidVms[] = $vm;
            }
        }
        return $invalidVms;
    }



    public function provisioning($vm){
        //Saving state of the Cloud
        $originalScenario = Cache::$realCvmp;

        //Get all current VMs to use as IgnoreSet later
        $vms = array_keys(Cache::$realCvmp['vmp']);

        //Try to host the VM in the first PM
        $pms = array_keys(Cache::$realCvmp['pmp']);
        foreach ($pms as $pm) {
            //Test RFC
            if(RulesFreeOfContext::isAllowed($vm,$pm))
                $scenario = Cache::$realCvmp;
                Cvmp::addVm( $scenario, $vm, $pm );
                //Se achar uma valida, testa as RSC
                if(RulesSensitiveToTheContext::isAllowed($scenario)){
                    Cache::$realCvmp = $scenario;
                    break;
                }
        }

        if(!isset($scenario['vmp'][$vm])) throw new Exception("Non initial host was identified to VM '$vm'. Lack of space? Strict Rules? ", 1);
        
        //Saving Costs
        $costs = Costs::getClasses();
        $mainCost = Costs::getMainCost();

        //Removing Costs
        foreach (Costs::getClasses() as $class) Costs::del($class);
       
        //Organize Cloud focusing on the new VM by ignoring the other VMs ($vms)
        $newScenario = $this->organize($scenario, $vms, false);

        //Save the host PM of the new VM
        $pm = $newScenario['vmp'][$vm];

        //Recover Costs
        foreach ($costs as $class) Costs::add($class);
        Costs::setMainCost($mainCost);

        //Recover Scenario
        Cache::$realCvmp = $originalScenario;
    
        return $pm;
    }
}
