<?php
class HandlerSingleton {
	protected $classes;
	protected static $instance = [];
	protected $interfaceClass = 'PHPUnit_Framework_Test';
	protected $extendsClass = 'PHPUnit_Framework_TestCase';


	protected function __construct(){
		$this->classes = [];
	}

	public static function getInstance()
	{
		$called = get_called_class();

		if ( !isset($called::$instance[$called])) $called::$instance[$called] = new $called();

		return $called::$instance[$called];
	}

	public static function add($class){
		$called = get_called_class();
		$instance = $called::getInstance();
		$interfaceClass = $instance->interfaceClass;
		$extendsClass = $instance->extendsClass;

		$implements = @class_implements($class);
		if (class_exists($class) and in_array($interfaceClass, $implements) and is_subclass_of($class, $extendsClass)){
			$instance->classes[$class] = $class;
		}else{
			throw new Exception("Class '$class' does not: implements '$interfaceClass' or extends '$extendsClass'", 1);
			return false;
		}
		return true;
	}

	public static function del($class){
		$called = get_called_class();
		$instance = $called::getInstance();
		unset($instance->classes[$class]);
	}

	public static function getClasses(){
		$called = get_called_class();
		$instance = $called::getInstance();
		return $instance->classes;
	}
}
