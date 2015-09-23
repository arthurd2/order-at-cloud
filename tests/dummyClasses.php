<?php
class Counter
{
    static public $scenarios = 0;
    static public $pareto = 0;
    static public $leaf = 0;
    static public $start = 1;
    static public $counters = [];
    
    static function reset() {
        Counter::$scenarios = 0;
        Counter::$pareto = 0;
        Counter::$leaf = 0;
        Counter::$start = time()-1;
        Counter::$counters = [];
    }
    static function stats($prefix = '') {
        $now = time();
        $delta = ($now - Counter::$start);
        if (!isset(Counter::$counters[$prefix])) Counter::$counters[$prefix] = 0;
        Counter::$counters[$prefix]++;
        $line = sprintf('%% %s - %s(%s)- Scenarios(%s) - ND(%s) - Leafs(%s) - Time(%s) - CVMP/sec(%s)', date(DATE_RFC2822), $prefix, Counter::$counters[$prefix], Counter::$scenarios, Counter::$pareto, Counter::$leaf, $delta, Counter::$scenarios / $delta);
        error_log($line);
    }
}
class CostTestMigrations extends Cost implements InterfaceCost
{
    static function getCost(&$cvmp) {
        $count = 1;
        foreach (Cache::$realCvmp['vmp'] as $vm => $pm) {
            if ($cvmp['vmp'][$vm] != $pm) {
                $count++;
            }
        }
        return $count;
    }
}
class COSText extends Cost
{
}
class COSTimp implements InterfaceCost
{
    static function getCost(&$cvmp) {
    }
}
class COSTextimp extends Cost implements InterfaceCost
{
    static function getCost(&$cvmp) {
        return 2;
    }
}

class QUAext extends Qualifier
{
}
class QUAimp implements InterfaceQualifier
{
    static function getWeight() {
    }
    static function evaluate(&$cvmp) {
    }
}
class QUAfake extends Qualifier implements InterfaceQualifier
{
    static function evaluate(&$cvmp) {
        return 2;
    }
}

class QUAincomplete extends Qualifier implements InterfaceQualifier
{
    static function load() {
        Cache::$cache->set('load_test', true);
    }
    static function evaluate(&$cvmp) {
        return ['v1' => 2, 'crap' => true];
    }
}

class QUAtest extends Qualifier implements InterfaceQualifier
{
    protected $weight = 2;
    static function evaluate(&$cvmp) {
        $return = [];
        foreach ($cvmp['vmp'] as $vm => $pm) {
            $return[$vm] = 2;
        }
        return $return;
    }
}

class QUAmirror extends Qualifier implements InterfaceQualifier
{
    
    static function evaluate(&$cvmp) {
        $return = [];
        foreach ($cvmp['vmp'] as $vm => $pm) {
            $return[$vm] = isset($cvmp['mirror']) ? $cvmp['mirror'] : 10;
        }
        return $return;
    }
}

class QUAconsolidate extends Qualifier implements InterfaceQualifier
{
    
    static function evaluate(&$cvmp) {
        $return = [];
        $count = 0;
        foreach ($cvmp['pmp'] as $pm) {
            $count+= count($pm) * count($pm);
        }
        foreach ($cvmp['vmp'] as $vm => $pm) {
            $return[$vm] = $count;
        }
        return $return;
    }
}

class RFCextendRule extends Rule
{
}
class RFCimplementRule implements RuleFreeOfContext
{
    static function isAllowed(&$vm, &$pm) {
    }
    static function isEnable() {
    }
    static function enable() {
    }
    static function disable() {
    }
}
class RFCextImpRules extends Rule implements RuleFreeOfContext
{
    static function isAllowed(&$vm, &$pm) {
        return ($vm === 1);
    }
}

class RSCextendRule extends Rule
{
}
class RSCimplementRule implements RuleSensitiveToTheContext
{
    static function isAllowed(&$vm) {
    }
    static function getWeight() {
    }
    static function isEnable() {
    }
    static function enable() {
    }
    static function disable() {
    }
}
class RSCextImpRules extends Rule implements RuleSensitiveToTheContext
{
    static function isAllowed(&$vm) {
        return ($vm === 1);
    }
}
