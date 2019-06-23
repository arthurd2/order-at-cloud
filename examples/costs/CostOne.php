<?php
/*
* 
* Dummy Cost Function
* It will return 1 independent of the Scenario.
*
*/
class CostOne extends Cost implements InterfaceCost{
	static function getCost(&$cvmp){
		return 1;
	}	
}
Costs::add('CostOne');
