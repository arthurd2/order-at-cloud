<?php
interface InterfaceQualifier {

	/*
	Return a array where keys are the VMs and the value is the evaluation
	 */
	//TODO consider to remove the reference, in case the Developer tries to break things up.
	static function evaluate(& $cvmp);
	/*
	Return the weight which will be elevate the value, i.e. 0.1 <= w <= 10
	 */
	static function getWeight();

}