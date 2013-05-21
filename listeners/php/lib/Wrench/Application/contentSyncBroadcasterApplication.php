<?php

/*!
 * Content Sync Broadcaster, v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * Continuously pushes data from latest-change.txt to attached browsers.
 * latest-change.txt is modified by the watch feature of Pattern Lab. Attached
 * browsers should refresh when they see the data change.
 *
 */

namespace Wrench\Application;

use Wrench\Application\Application;
use Wrench\Application\NamedApplication;

class contentSyncBroadcasterApplication extends Application {
	
	protected $clients          = array();
	protected $savedTimestamp   = null;
	protected $c                = false;
	
	/**
	* Set the saved timestamp
	*/
	public function __construct() {
		$this->savedTimestamp = file_get_contents(__DIR__."/../../../../../public/latest-change.txt");
	}
	
	/**
	* When a client connects add it to the list of connected clients
	*/
	public function onConnect($client) {
		$id = $client->getId();
		$this->clients[$id] = $client;
	}
	
	/**
	* When a client disconnects remove it from the list of connected clients
	*/
	public function onDisconnect($client) {
		$id = $client->getId();
		unset($this->clients[$id]);
	}
	
	/**
	* Dead function in this instance
	*/
	public function onData($data, $client) {
		// function not in use
	}
	
	/**
	* Sends out a message once a second to all connected clients containing the contents of latest-change.txt
	*/
	public function onUpdate() {
		
		$readTimestamp = file_get_contents(__DIR__."/../../../../../public/latest-change.txt");
		if ($readTimestamp != $this->savedTimestamp) {
			foreach ($this->clients as $sendto) {
				$sendto->send($readTimestamp);
			}
			$this->savedTimestamp = $readTimestamp;
		}
		
	}

}
