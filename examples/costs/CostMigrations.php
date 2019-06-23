<?php
/*
* Migration Cost
* 
* This example counts how many migrations are necessary to reach the proposed scenario.
* It also adds a maxCost variable, which allows the Cost Function  to be used as a constrain of Rules Sensitive to Context. 
*/
class CostMigrations extends Cost implements InterfaceCost{
	static $maxCost = 20;
	static function getCost(&$cvmp){
		$count = 1;
		foreach (Cache::$realCvmp['vmp'] as $vm => $pm) {
			if ($cvmp['vmp'][$vm] != $pm) {
				$count++;
			}
		}
		return $count;
	}
}
Costs::add('CostMigrations');
