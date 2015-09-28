<?php
class OrderCloud
{
    private $rules;
    private $qualifiers;
    private $costs;
    protected static $stop = false;
    
    public function __construct(&$currentCvmp) {
        Cache::$realCvmp = & $currentCvmp;
        $this->executeLoadClasses();
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
            $newLowVM = $this->selectLowerVm($sCvmp, $ignoreVMs);
            $ignoreVMs[$newLowVM] = $newLowVM;
            $sCvmp = $this->organize($sCvmp, $ignoreVMs, true);
        }
        return $sCvmp;
    }
    
    public function getCvmpWithMaxCostBenefit(&$pareto) {
        if(count($pareto) == 1) return  array_pop($pareto);

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
        $evalBase = Qualifiers::getEvaluation($cvmp);
        
        $ignore = array_flip($ignoreVMs);
        
        //TODO Valor max do int pode ser um problema pois vai estourar no
        $valueMin = PHP_INT_MAX;
        
        $vmMin = null;
        
        foreach ($evalBase as $vm => $value) {
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
            $count+= $evalCand[$vm] - $value;
            if ($value > $evalCand[$vm]) {
                return false;
            }
        }
        
        return (OC_ND_HIGH_CONVERGENCE) ? ($count > 0) : true;
    }
}
