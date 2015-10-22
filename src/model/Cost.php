<?php

class Cost extends Singleton{
	public static $maxCost = PHP_INT_MAX;
	static public function load(){}
	static public function getMaxCost(){
		$class = get_called_class();
		return $class::$maxCost;
	}
}