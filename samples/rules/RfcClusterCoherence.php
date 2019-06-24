<?php

/* Check if a VM is not being migrate to a cluster diferentes from its own. */

class RfcClusterCoherence extends Rule implements RuleFreeOfContext
{
    protected static $cluster = [];

    public static $clusters2pms = [];
    
    static public function load() {

        $clusters['cluster1'] = ['PM1','PM2','PM3'];
        $clusters['cluster2'] = ['PM4','PM5','PM6'];
        
        foreach ($clusters as $name => $servers) {
            foreach ($servers as $pm) {
                RfcClusterCoherence::$clusters2pms[$name][] = $pm;
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
