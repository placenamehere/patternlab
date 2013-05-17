#!/usr/bin/env php
<?php

/*!
 * Nav Sync Server, v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * The server that clients attach to to learn about page updates. See
 * lib/Wrench/Application/pageUpdateBroadcasterApplication.php for logic
 *
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require(__DIR__.'/lib/SplClassLoader.php');

$classLoader = new SplClassLoader('Wrench',__DIR__.'/lib');
$classLoader->register();

if (!($config = @parse_ini_file(__DIR__."/../../config/config.ini"))) {
	$config = @parse_ini_file(__DIR__."/../../config/config.ini");	
}

$address = ($config) ? $config['websocketAddress'] : '127.0.0.1';
$port    = ($config) ? $config['navSyncPort'] : '8000';

$server = new \Wrench\Server('ws://'.$address.':'.$port.'/', array(
    'allowed_origins'            => array(
		'127.0.0.1',
		$address
    )
));

$server->registerApplication('navsync', new \Wrench\Application\navSyncBroadcasterApplication());
$server->run();
