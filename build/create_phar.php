<?php
ini_set('phar.readonly', 0);
$build = __DIR__;
$src = __DIR__ . '/../src/';

$phar = new Phar($build . "/OrderAtCloud.phar", FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "OrderAtCloud.phar");
$phar['Bootstrap.phar.php'] = file_get_contents($src.'Bootstrap.phar.php');

$folders = ['basics', 'interfaces','model'];
$files = [];
foreach ($folders as $folder) {
	foreach (glob("$src/$folder/*.php") as $filename){
		$file = str_replace($src.'/', '',$filename);
		$files[]=$file;
		$phar[$file] = file_get_contents($filename);
	}
}
$phar->setMetadata(['files'=>$files]);
$phar->setStub($phar->createDefaultStub('Bootstrap.phar.php'));
//copy($src . "/config.ini", $build . "/config.ini");
