<?php
define('OC_CACHE_HOST', '127.0.0.1');
define('OC_CACHE_PORT', '11211');

define('OC_TMP', 'TMP');
define('OC_LAST_ADD_VM','lastAddVM');
define('OC_LAST_ADD_PM','lastAddPM');
define('OC_LAST_REM_VM','lastRemVM');
define('OC_LAST_REM_PM','lastRemPM');
define('OC_ND_HIGH_CONVERGENCE',true);

define('OC_STORE','classes');

$phar = Phar::running();
$p = new Phar($phar);
$files = $p->getMetadata()['files'];

foreach ($files as $file) 
	require_once $phar."/".$file;



$cache = new Memcached;
$cache->addServer(OC_CACHE_HOST, OC_CACHE_PORT);
Cache::$cache = $cache;

require_once "tests/dummyClasses.php";
Counter::$start = time()-1;


