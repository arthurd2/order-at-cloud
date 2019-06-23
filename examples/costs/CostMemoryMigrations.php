<?php
/*
* Memory Transfer Cost
* 
* This example considers Cost as the total amount of memory that will be tranfer to deploy a scenario.
* It compares the original scenario with the proposed one.
* If a VM will be migrated, it sums its memory to the final cost.
*/
class CostMemoryMigrations extends Cost implements InterfaceCost{
	private static $vmsData = [];

	static public function load(){
		$this->vmsData = HelperVmData::getVmsData();
	}

	static function getCost(&$cvmp){

		$count = 1;
		foreach (Cache::$realCvmp['vmp'] as $vm => $pm) {
			//If the PM of a VM differs from the origina scenario, it means that it will be migrate.
			if ($cvmp['vmp'][$vm] != $pm) {
				$count += $this->vmsData['vms'][$vm]['used_memory'];
			}
		}
		return $count;
	}
}
Costs::add('CostMemoryMigrations');
