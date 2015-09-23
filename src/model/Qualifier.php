<?php
class Qualifier extends Singleton
{
    
    protected $weight = 1;
    
    public static function getWeight() {
        $class = get_called_class();
        $instance = $class::getInstance();
        return $instance->weight;
    }
    
    final static function _evaluate(&$cvmp) {
        $class = get_called_class();
        $evaluation = $class::evaluate($cvmp);
        //TODO test values > 0 & <=2
        
        //Testing is Value is an array
        if (!is_array($evaluation)){
            Qualifiers::del($class);
            throw new Exception("Evaluations sent from class '$class' is not an Array. Class Removed.", 1);
        }

        $return = [];

        //Selecting just the vms evaluations, cutting possible garbage
        foreach ($cvmp['vmp'] as $vm => $pm) {
            $return[$vm] = (isset($evaluation[$vm]))?$evaluation[$vm]:1;
            unset($evaluation[$vm]);
        }

        //Informing about the garbage found
        if (count($evaluation) > 0) {
            Qualifiers::del($class);
            throw new Exception("Class '$class' is putting crap in the evaluation result: \n".print_r($evaluation,true), 1);
        }

        return $return;
    }

    static function load(){}
}
