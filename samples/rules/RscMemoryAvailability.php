<?php

/*  Checks if the new PM has enought memory to host the VM */

class RscMemoryAvailability extends Rule implements RuleSensitiveToTheContext
{
    private static $vmsData = [];
    
    static public function load() {
        RscMemoryAvailability::$vmsData = HelperVmData::getVmsData();
    }
    static function isAllowed(&$cvmp) {
        if (!isset($cvmp[OC_LAST_ADD_PM])) return true;

        $pm = $cvmp[OC_LAST_ADD_PM];
        $pmMemory = RscMemoryAvailability::$vmsData['pms'][$pm]['memory'];
        $vms = $cvmp['pmp'][$pm];

        $sumMemory = 0;
        foreach ($vms as $vm) {
            $sumMemory += RscMemoryAvailability::$vmsData['vms'][$vm]['used_memory'];
        }
        return $pmMemory > $sumMemory;
    }
}
RulesSensitiveToTheContext::add('RscMemoryAvailability');
