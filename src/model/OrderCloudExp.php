<?php
class OrderCloudExp extends OrderCloud
{
    public $rankSelect;
    public $paretoFilter;
    
    public function __construct(&$currentCvmp) {
        $this->rankSelect = 'rank';
        $this->paretoFilter = true;
        parent::__construct($currentCvmp);
    }
    
    public function selectLowerVm(&$cvmp, &$ignoreVMs) {
        
        switch ($this->rankSelect) {
            case 'rank':
                return parent::selectLowerVm($cvmp, $ignoreVMs);
                break;

            case 'serial':
                foreach ($cvmp['vmp'] as $vm => $pm) if (!isset($ignoreVMs[$vm])) return $vm;
                break;

            case 'random':
                $vms = $cvmp['vmp'];
                while (!empty($vms)) {
                    $vm = array_rand($vms);
                    if (!isset($ignoreVMs[$vm])) return $vm;
                    unset($vms[$vm]);
                }
                break;
            default:
                throw new Exception("I do not know this selection method");
                break;
            }
            
            throw new Exception("Selecting VM when ignoreVMs set is full", 1);
        }
        
        public function isNonDominanted(&$baseCvmp, &$candidateCvmp) {
            $evalBase = Qualifiers::getEvaluation($baseCvmp);
            $evalCand = Qualifiers::getEvaluation($candidateCvmp);
            return ($this->paretoFilter) ? parent::isNonDominanted($baseCvmp, $candidateCvmp) : (array_sum($evalCand) > array_sum($evalBase));
        }
    }
    