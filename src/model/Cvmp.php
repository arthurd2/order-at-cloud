<?php
class Cvmp{
    static function buildCVmpByPlacements(&$placements) {
        $cvmp['rpm'] = [];
        $cvmp['pmp'] = [];
        $cvmp['vmp'] = [];
        
        foreach ($placements as $vm => $pm) {
                $cvmp['rpm'][$pm] = isset($cvmp['rpm'][$pm]) ? $cvmp['rpm'][$pm] + 1 : 1;
                $cvmp['vmp'][$vm] = $pm;
                $cvmp['pmp'][$pm][$vm] = $vm;
        }

        $cvmp['nvms'] = count($cvmp['vmp']);
        $cvmp['npms'] = count($cvmp['pmp']);
        return $cvmp;
    }
    static function addVm( &$cvmp, &$vm, &$pm ){

        if(isset($cvmp['pmp'][$pm][$vm]))
            return;
    	$cvmp['nvms'] = isset($cvmp['nvms'])? $cvmp['nvms']+1 : 1;
    	$cvmp['rpm'][$pm] = isset($cvmp['rpm'][$pm])? $cvmp['rpm'][$pm]+1 : 1;
        $cvmp['npms'] = count($cvmp['rpm']);
    	$cvmp['pmp'][$pm][$vm] = $vm;
    	$cvmp['vmp'][$vm] = $pm;
    	unset($cvmp[OC_TMP]);
        $cvmp[OC_LAST_ADD_VM] = $vm;
        $cvmp[OC_LAST_ADD_PM] = $pm;

    }
    static function removeVm( &$cvmp, &$vm ){
        if(!isset($cvmp['vmp'][$vm]))
            return; 
    	$pm = $cvmp['vmp'][$vm];
    	$cvmp['nvms']--;
    	$cvmp['rpm'][$pm]--;
    	unset($cvmp['pmp'][$pm][$vm]);
    	unset($cvmp['vmp'][$vm]);
    	unset($cvmp[OC_TMP]);
        $cvmp[OC_LAST_REM_VM] = $vm;
        $cvmp[OC_LAST_REM_PM] = $pm;
        if( isset($cvmp[OC_LAST_ADD_VM]) && $vm == $cvmp[OC_LAST_ADD_VM]){
            unset($cvmp[OC_LAST_ADD_PM]);
            unset($cvmp[OC_LAST_ADD_VM]);
        }
    }
}