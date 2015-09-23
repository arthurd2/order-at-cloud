<?php
class Approximation
{
    static function getNumberOfPossibilitiesBasedOnRfcAndCvmp($cvmp) {
        $matrix = Approximation::getPossibilityMatrixBasedOnRfcAndCvmp($cvmp);
        return getNumberOfPossibilitiesBasedOnMatrix($matrix['vms']);

    }
    static function getPossibilityMatrixBasedOnRfcAndCvmp($cvmp) {
        $vms = array_keys($cvmp['vmp']);
        $pms = array_keys($cvmp['pmp']);
        $allowenceMatrix = [];
        foreach ($vms as $vm) {
            foreach ($pms as $pm) {
                if(RulesFreeOfContext::isAllowed($vm,$pm)){
                    $allowenceMatrix['vms'][$vm][$pm] = true; 
                    $allowenceMatrix['pms'][$pm][$vm] = true; 
                }

            }
        }
        
        return $allowenceMatrix;
    }
    
    static function getNumberOfPossibilitiesBasedOnMatrix($matrix) {
        $retorno = 1;
        foreach ($matrix as $vms) {
            $retorno*= count($vms);
        }
        return $retorno;
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
