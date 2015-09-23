<?php
class RfcClusterCoherence extends Rule implements RuleFreeOfContext
{
    private static $cluster = [];
    
    static public function load() {
        $clusters['labtrans'] = ['150.162.2.175', '150.162.2.176'];
        $clusters['corporativo'] = ['150.162.2.205', '150.162.2.206', '150.162.2.217', '150.162.2.218', '150.162.2.219', '150.162.2.220', ];
        $clusters['R720'] = ['150.162.2.210', '150.162.2.211', '150.162.2.212', '150.162.2.213'];
        $clusters['R920'] = ['150.162.2.227', '150.162.2.228', ];
        $clusters['INE'] = ['150.162.2.216', '150.162.2.225'];
        $clusters['Unasus'] = ['150.162.2.214', '150.162.2.215', ];
        
        foreach ($clusters as $name => $servers) {
            foreach ($servers as $pm) {
                RfcClusterCoherence::$cluster[$pm] = $name;
            }
        }
    }
    static function isAllowed(&$vm, &$pm) {
        $pmOrig = Cache::$realCvmp['vmp'][$vm];
        return (RfcClusterCoherence::$cluster[$pmOrig] == RfcClusterCoherence::$cluster[$pm]);
    }
}
RulesFreeOfContext::add('RfcClusterCoherence');