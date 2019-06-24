<?php

/* Checks if the search have reach a preset TimeOut deadline. */

class RfcTime extends Rule implements RuleFreeOfContext
{
    static public $maxTime = 120;
    static function isAllowed(&$vm, &$pm) {
        return (time()-Counter::$start <= RfcTime::$maxTime);
    }
}
RulesFreeOfContext::add('RfcTime');
