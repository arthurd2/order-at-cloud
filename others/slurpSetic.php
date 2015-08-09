<?php
require_once 'src/model/Scenario.php';
require_once 'src/model/Approximation.php';
require_once "libs/vmwarephp/Bootstrap.php";
ini_set('xdebug.max_nesting_level', 2000);
$vmware = new \Vmwarephp\Vhost(getenv('VMHOST'), getenv('VMUSER'), getenv('VMPASS'));

define('FNVM', "SeTIC_VMs.json");
define('FNPM', "SeTIC_PMs.json");
define('FNPL', "SeTIC_Placements.json");
define('FNSC', "SeTIC_Scenario.json");

if(!file_exists(FNVM)) loadVMs($vmware); 
if(!file_exists(FNPM)) loadPMs($vmware); 
if(!file_exists(FNPL)) convertDataToPlacements(); 
if(!file_exists(FNSC)) convertPlacementToScenario(); 


function convertPlacementToScenario() {
	$placements = json_decode(file(FNPL)[0],true);
	$scenario = Scenario::buildScenarioByPlacements($placements);
	$scenario['vms'] = json_decode(file(FNVM)[0],true);
	$scenario['pms'] = json_decode(file(FNPM)[0],true);
    file_put_contents(FNSC, json_encode($scenario));
	return $scenario;
}

function convertDataToPlacements() {
	$vms = json_decode(file(FNVM)[0],true);
	$pms = json_decode(file(FNPM)[0],true);
	$placements = array();
	$valids = 0;
	$invalids = 0;
	foreach ($pms as  $pm) {
		foreach ($vms as  $vm) {
			if(placementIsValid($vm,$pm)){
				$vmName = str_replace(':', '', $vm['name']);
				$placements[$vmName][] = $vmName.':'.$pm['name'];
				echo '+';
				$valids++;
			}else{
				echo '-';
				$invalids++;
			}
		}
	}
	echo "\n\n Valids($valids) Invalids($invalids)\n\n";
	$json = json_encode($placements);
    
    //Salvar no arquivo
    file_put_contents(FNPL, $json);
    return $filename;

}

function placementIsValid($vm,$pm){
	//Verify networks
	foreach ($vm['networks'] as  $net) {
		$resp = array_search($net, $pm['networks']);
		if($resp === false) return false;
	}

	//Verify datastore
	foreach ($vm['datastores'] as  $ds) {
		$resp = array_search($ds, $pm['datastores']);
		if($resp === false ) return false;
	}

	return true;
}

function loadVMs($vmware) {
    //https://www.vmware.com/support/developer/vc-sdk/visdk41pubs/ApiReference/vim.VirtualMachine.html
    //http://pubs.vmware.com/vsphere-60/index.jsp?topic=/com.vmware.wssdk.apiref.doc/index.html&single=true&__utma=207178772.1811249502.1438681066.1438681066.1438681066.1&__utmb=207178772.0.10.1438681066&__utmc=207178772&__utmx=-&__utmz=207178772.1438681066.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)&__utmv=-&__utmk=104578819
    //'name', 'summary','network','datastore', 'config'
    $virtualMachines = array();
    $vms = array();
    echo date(DATE_RFC2822)." - Inicio \n";
    $virtualMachines = $vmware->findAllManagedObjects('VirtualMachine', array('name', 'network', 'runtime','datastore'));
    $num = count($virtualMachines);
    $count = 1;
    $start = time();
    foreach ($virtualMachines as $vm) {
    	$newVM = array();
        $newVM['name'] = $vm->name;
        $eta = intval((time()-$start)/$count)*($num-$count+1);
        $spend = time()-$start;
        echo date(DATE_RFC2822)." | $num/". $count++ ." | ETA: ". $eta .'s | Spend: '.$spend.'s | '.$newVM['name']."\n";
        
		if ($vm->runtime->powerState != 'poweredOn') continue;

        //$newVM->cpus = $vm->summary->config->numCpu
        $newVM['memory'] = $vm->config->hardware->memoryMB;
        //$newVM['status'] = $vm->runtime->onlineStandby;
        
        //$newVM['paused'] = $vm->runtime->paused;
        //$newVM->annotation = $vm->config->annotation
        $newVM['uuid'] = $vm->config->uuid;
        
        $newVM['networks'] = array();
        foreach ($vm->network as $network) $newVM['networks'][] = $network->name;
        
        $newVM['datastores'] = array();
        foreach ($vm->datastore as $datastore) $newVM['datastores'][] = $datastore->info->name;
        
        /*
                $newVM->disco = 0
                $devs = $vm->config->hardware->device
                foreach ($devs as $dev) {
                    if (isset($dev->capacityInKB)) $newVM->disco+= ($dev->capacityInKB / (1024 * 1024))
                }
        */
        $vms[$newVM['uuid']] = $newVM;
    }
    
    $json = json_encode($vms);
       
    //Salvar no arquivo
    file_put_contents(FNVM, $json);
    return $filename;
}

function loadPMs($vmware) {
    $physicalMachines = array();
    $pms = array();
    
    echo date(DATE_RFC2822)." - Inicio \n";
    $physicalMachines = $vmware->findAllManagedObjects('HostSystem', array('name', 'network', 'datastore', 'hardware'));
    
    foreach ($physicalMachines as $pm) {
        $newPM = array();
        $newPM['name'] = $pm->name;
        echo date(DATE_RFC2822).' - '.$newPM['name']."\n";
        //$newPM['uuid'] = $pm->config->uuid
        $newPM['networks'] = array();
        foreach ($pm->network as $network) {
            $newPM['networks'][] = $network->name;
        }
        $newPM['datastores'] = array();
        foreach ($pm->datastore as $datastore) {
            $newPM['datastores'][] = $datastore->info->name;
        }
        $newPM['memory'] = intval($pm->hardware->memorySize / (1024 * 1024));
        
        $pms[$newPM['name']] = $newPM;
    }
    $json = json_encode($pms);
    
    file_put_contents(FNPM, $json);
}
