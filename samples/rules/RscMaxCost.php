<?php

/* Check if the new scenario exceed the Max Cost preset */

class RscMaxCost extends Rule implements RuleSensitiveToTheContext
{
    static function isAllowed(&$cvmp) {
        return (Costs::getCost($cvmp) <= Costs::getMaxCost()) ;
    }
}
RulesSensitiveToTheContext::add('RscMaxCost');
