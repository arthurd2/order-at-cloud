<?php
class Qualifiers extends HandlerSingleton {
    protected $interfaceClass = 'InterfaceQualifier';
    protected $extendsClass = 'Qualifier';

    static function getBenefit(& $cvmp){
    	if (!isset($cvmp[OC_TMP]['benefit'])){
    		$evaluations = Qualifiers::getEvaluation($cvmp);
    		$cvmp[OC_TMP]['benefit'] = array_sum($evaluations);
    	}
  		return $cvmp[OC_TMP]['benefit'];
    }
    static function getEvaluation(& $cvmp){
        if (!isset($cvmp[OC_TMP]['values'])){
        	$qualifiers = Qualifiers::getClasses();
            foreach ($cvmp['vmp'] as $vm => $pm) 
                $cvmp[OC_TMP]['values'][$vm] =  1 ;
        	foreach ( $qualifiers as $class) {
        		$normEval = $class::_evaluate($cvmp);
        		$w = $class::getWeight();
        		foreach ($normEval as $vm => $eval) {
                    $cvmp[OC_TMP]['values'][$vm] *=  pow($eval, $w);
                }
        	}
        }
        return $cvmp[OC_TMP]['values'];
    }

    static function getCostBenefit(& $cvmp){
    	if (!isset($cvmp[OC_TMP]['cb'])){
    		$candBen = Qualifiers::getBenefit($cvmp);
    		$realBen = Qualifiers::getBenefit(Cache::$realCvmp);
            $cvmp[OC_TMP]['benefit'] = ($candBen-$realBen);
    		$cvmp[OC_TMP]['cb'] = ($candBen-$realBen)/Costs::getCost($cvmp);
    	}
       	return $cvmp[OC_TMP]['cb']  ;
    }
}