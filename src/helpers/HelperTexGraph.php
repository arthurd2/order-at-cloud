<?php
class HelperTexGraph
{
    public function __construct($name) {
        $this->name = $name;
        $this->graphic = [];
        $e = &$this->graphic;

    }
    
    public function addCvmp($cvmp, $size, $line) {
        
        $time = time() - Counter::$start;
        $cb = $cvmp[OC_TMP]['cb'];
        $ben = $cvmp[OC_TMP]['benefit'];
        $avg = array_sum($cvmp[OC_TMP]['values']) / $cvmp['nvms'];
        $stdDev = stats_standard_deviation($cvmp[OC_TMP]['values']);
        $scenarios = Counter::$scenarios;
        $ndcvmp = Counter::$pareto;
        $top20 = $this->getDeltaTop20($cvmp);

        $e = &$this->graphic;
        $e['Time'][$line][]					= "($size,$time)";
        $e['CB'][$line][]					= "($size,$cb)";
        //$e['Benefit'][$line][]			= "($size,$ben)";
        $e['Top 20 Lowest'][$line][]		= "($size,$top20)";
        $e['Avg Benefit'][$line][]			= "($size,$avg)";
        $e['Std Dev'][$line][]				= "($size,$stdDev)";
        $e['CVMPs'][$line][]				= "($size,$scenarios)";
        $e['Non-Dominated CVMPs'][$line][]	= "($size,$ndcvmp)";
    }
    
    public function finish() {
    	$return = sprintf($this->fmt_header,$this->name);

    	foreach ($this->graphic as $name => $lines) {
    			$return .= sprintf($this->fmt_header_graphic,$name);
    	    	foreach ($lines as $lineName => $coord) {
    	    		$return .= sprintf($this->fmt_coord,"$name - $lineName",implode('', $coord));
    	    	}
    	    	$return .= sprintf($this->fmt_bottom_graphic,implode(',', array_keys($lines)));
    	}
    	$return .= sprintf($this->fmt_bottom,$this->name);
    	return $return;
    }
    
    protected $fmt_header = "\n%%TODO -------------------------- %s \n\\begin{figure*}";
    protected $fmt_bottom = "\n\\caption{%s}\n\\label{fig:results}\n\\end{figure*}";
    
    protected $fmt_coord = "\n\\addplot  plot coordinates { %% %s \n %s };\n";

    protected $fmt_header_graphic = "\n\\begin{tikzpicture}\n\\begin{axis}[
    %%title=,
    width=0.6\linewidth,
    x tick label style={/pgf/number format/1000 sep=},
    ylabel=%s,
    ylabel near ticks,
    %%symbolic x coords={350,400,450,500,550,600},
    legend style={anchor=center,at={(0.5,0.5)}},
    ]\n";

    protected $fmt_bottom_graphic = "%%\\legend{%s}\n\\end{axis}\n\\end{tikzpicture}";

    public function getDeltaTop20(&$cvmp) {
    	$realEval = Cache::$realCvmp[OC_TMP]['values'];
    	$bestEval = &$cvmp[OC_TMP]['values'];
    	asort($realEval,SORT_NUMERIC);
    	$ct = 0;
    	$sum = 0;
    	foreach ($realEval as $vm => $value) {
    		$ct++;
    		$sum += $bestEval[$vm] - $value;
    		if ($ct >= 20) break;
    	}
    	return $sum;
    }
}
