<?php
class Approximation
{
    static function calcularSemRegras($scenario) {
        return pow($scenario['npms'], $scenario['nvms']);
    }
    
    static function calcularComRegras($scenario) {
        $retorno = 1;
        foreach ($scenario['placements'] as $vm) {
            $retorno*= count($vm);
        }
        return $retorno;
    }
    
    static function calcularComRegrasMaxVMProd($scenario, $maxVM) {
        $todas = 1;
        foreach ($scenario['rpm'] as $pmName => $vms) {
            $combinacao = Approximation::calcCombination($vms, $maxVM);
            $todas*= $combinacao;
        }
        return $todas;
    }
    
    static function calcularComRegrasMaxVMSum($scenario, $maxVM) {
        $todas = 0;
        foreach ($scenario['rpm'] as $pmName => $vms) {
            $combinacao = Approximation::calcCombination($vms, $maxVM);
            $todas+= $combinacao;
        }
        return $todas;
    }
    
    static function calcularComRegrasMaxVMSub($scenario, $maxVM) {
        
        $indesejado = 0;
        
        $todas = array_product($scenario['rvm']);
        foreach ($scenario['rpm'] as $pmName => $pm) {
            $localUnwanted = 0;
            if ($pm > $maxVM) {
                for ($i = $maxVM + 1; $i <= $pm; $i++) {
                    $tmp = Approximation::calcCombination($pm, $i);
                    $localUnwanted+= $tmp;
                }
            }
            $indesejado+= $localUnwanted;
        }
        return $todas - $indesejado;
    }
    
    static function fact($a) {
        if ($a <= 1) return 1;
        else return $a * Approximation::fact(($a - 1));
    }
    
    static function calcCombination($n, $s) {
        $resp = Approximation::fact($n) / (Approximation::fact($s) * Approximation::fact($n - $s));
        return $resp;
    }
    
    static function getOutsidersInsiders($pm, $scenario) {
        $outsider = 1;
        $insider = array();
        foreach ($scenario['placements'] as $vm => $places) {
            $flag = true;
            foreach ($places as $place) {
                $pmName = explode(':', $place) [1];
                
                //If the current VM can be host in the evaluated PM, do not multiply
                if ($pm == $pmName) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) $outsider*= count($places);
            else $insider[] = count($places) - 1;
        }
        return array($outsider, $insider);
    }
    
    static function calcularComRegrasMaxVMOutIn($scenario, $maxVM) {
        $unwanted = 0;
        $all = array_product($scenario['rvm']);
        
        foreach ($scenario['rpm'] as $pmName => $numOfVMsInPM) {
            $unwantedLocal = 0;
            if ($numOfVMsInPM > $maxVM) {
                list($out, $in) = Approximation::getOutsidersInsiders($pmName, $scenario);
                for ($i = $maxVM + 1; $i <= $numOfVMsInPM; $i++) {
                    $inConsolidated = Approximation::getInsidersCombinations($in, $numOfVMsInPM - $i);
                    $unwantedLocal+= $out * $inConsolidated;
                }
            }
            $unwanted+= $unwantedLocal;
        }
        return $all - $unwanted;
    }
    
    static function calcularComRegrasMaxVMSubProdOthers($scenario, $maxVM) {
        
        $indesejado = 0;
        
        $todas = array_product($scenario['rvm']);
        foreach ($scenario['rpm'] as $pmName => $pm) {
            $localUnwanted = 0;
            if ($pm > $maxVM) {
                $multi = Approximation::getOutsidersInsiders($pmName, $scenario) [0];
                for ($i = $maxVM + 1; $i <= $pm; $i++) {
                    $tmp = Approximation::calcCombination($pm, $i) * $multi;
                    $localUnwanted+= $tmp;
                }
            }
            $indesejado+= $localUnwanted;
        }
        return $todas - $indesejado;
    }
    static function getInsidersCombinations(&$insiders, $size) {
        if ($size <= 0) return 1;
        $subtrees = Approximation::_getInsidersCombinations($insiders, $size);
        return array_sum($subtrees);
    }
    
    static function _getInsidersCombinations(&$insiders, $size, $pos = 0) {
        if ($size <= 0) return array(1);
        
        $return = array();
        $end = count($insiders) - $size;
        
        for ($i = $pos; $i <= $end; $i++) {
            $subtree = Approximation::_getInsidersCombinations($insiders, $size - 1, $i + 1);
            foreach ($subtree as $value) $return[] = $insiders[$i] * $value;
        }
        return $return;
    }
    static function calculateAvgCombSplitterApproach($scenario, $maxVM) {
        $quantities = Approximation::getCombinatorialSliceQuantities($scenario, $maxVM);
        return array_sum($quantities) / count($quantities);
    }
    
    static function getSequencialSliceQuantities($scenario, $maxVM) {
        $sliceSize = ($maxVM * 2) + 1;
        $quatities = array();
        
        $p = 0;
        while ($p < $scenario['nvms']) {
            $slice = array_slice($scenario['placements'], $p, $sliceSize);
            $sliceScenario = Scenario::buildScenarioByPlacements($slice);
            $quatities[] = Approximation::calcularComRegrasMaxVMOutIn($sliceScenario, $maxVM);
            $p+= $sliceSize;
        }
        
        return $quatities;
    }
    static function getCombinatorialSliceQuantities($scenario, $maxVM) {
        require_once 'libs/Combinatorics.php';
        $combinatorics = new Math_Combinatorics;
        $sliceSize = ($maxVM * 2) + 1;
        
        $input = array_keys($scenario['placements']);
        $combinations = $combinatorics->combinations($input, $sliceSize);
        
        $quatities = array();
        
        foreach ($combinations as $combination) {
            $slice = array();
            foreach ($combination as $index) {
                $slice[] = $scenario['placements'][$index];
            }
            $sliceScenario = Scenario::buildScenarioByPlacements($slice);
            $quatities[] = Approximation::calcularComRegrasMaxVMOutIn($sliceScenario, $maxVM);
        }
        
        return $quatities;
    }
    
    //TODO Arrumar casos de VMs sem places!!!
    static function treeSearchApproach(&$scenario, &$maxVM) {
        $placements = array_values($scenario['placements']);
        $usageVector = array();
        $level = 0;
        foreach ($scenario['rpm'] as $key => $value) $usageVector[$key] = 0;
        $nvms = $scenario['nvms'];
        return Approximation::treeSearchApproachBackEnd($placements, $nvms, $maxVM, $level, $usageVector);
    }
    static function treeSearchApproachBackEnd(&$placements, &$nvms, &$maxVM, &$level = 0, &$usageVector = array(), &$stateCounter = 0) {
        
        //Interects foreach possible placements of that VM
        foreach ($placements[$level] as $p) {
            $pmName = explode(':', $p) [1];
            
            //Checks if the PM is not full
            if ($usageVector[$pmName] < $maxVM) {
                
                //Check if the last VM to host
                if ($level >= $nvms - 1) {
                    
                    //Just count one state
                    $stateCounter++;
                } 
                else {
                    
                    //Prepare to drilldown
                    $level++;
                    $usageVector[$pmName]++;
                    Approximation::treeSearchApproachBackEnd($placements, $nvms, $maxVM, $level, $usageVector, $stateCounter);
                    $level--;
                    $usageVector[$pmName]--;
                }
            }
        }
        return $stateCounter;
    }
    
    /**
     * @codeCoverageIgnore
     * Someday i will return here.
     * Farewell my friend.
     */
    static function realTreeSearchApproach($scenario) {
        global $ctd;
        $placements = array_values($scenario['placements']);
        usort($placements, 'Approximation::cmp');
        $usageVector = array();
        $level = 0;
        foreach ($scenario['pms'] as $pm) $usageVector[$pm['name']] = intval($pm['memory']);
        $nvms = $scenario['nvms'];
        $vms = $scenario['vms'];
        
        return Approximation::realTreeSearchApproachBackEnd($placements, $nvms, $vms, $level, $usageVector, $ctd);
    }
    
    /**
     * @codeCoverageIgnore
     * Someday i will return here.
     * Farewell my friend.
     */
    static function realTreeSearchApproachBackEnd(&$placements, &$nvms, &$vms, &$level = 0, &$usageVector = array(), &$stateCounter = 0) {
        
        //Interects foreach possible placements of that VM
        
        foreach ($placements[$level] as $p) {
            list($vmName, $pmName) = explode(':', $p);
            if($level == 0 ) echo "$vmName, $pmName";
            //Checks if the PM is not full
            if ($usageVector[$pmName] > $vms[$vmName]['used_memory']) {
                
                //Check if the last VM to host
                if ($level >= $nvms - 1) {
                    
                    //Just count one state
                    $stateCounter++;
                } 
                else {
                    //Prepare to drilldown
                    $level++;
                    $usageVector[$pmName]-= $vms[$vmName]['used_memory'];
                    Approximation::realTreeSearchApproachBackEnd($placements, $nvms, $vms, $level, $usageVector, $stateCounter);
                    $level--;
                    $usageVector[$pmName]+= $vms[$vmName]['used_memory'];
                }
            }
        }
        return $stateCounter;
    }
    static function cmp($a, $b) {
        if (count($a) == count($b)) return 0;
        return (count($a) < count($b)) ? -1 : 1;
    }
}
