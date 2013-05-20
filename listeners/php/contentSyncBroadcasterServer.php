<?php

/*!
 * Content Sync Server, v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * The server that clients attach to to learn about content updates. See
 * lib/Wrench/Application/contentUpdateBroadcasterApplication.php for logic
 *
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require(__DIR__.'/lib/SplClassLoader.php');

$classLoader = new SplClassLoader('Wrench',__DIR__.'/lib');
$classLoader->register();

if (!($config = @parse_ini_file(__DIR__."/../../config/config.ini"))) {
	$config = @parse_ini_file(__DIR__."/../../config/config.ini");	
}

$port = ($config) ? trim($config['contentSyncPort']) : '8002';

$server = new \Wrench\Server('ws://0.0.0.0:'.$port.'/', array());

$server->registerApplication('contentsync', new \Wrench\Application\contentSyncBroadcasterApplication());
$server->run();
