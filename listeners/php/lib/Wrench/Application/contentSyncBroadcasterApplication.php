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
	protected $lastTimestamp    = null;
	protected $currentAddress   = null;
	
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
		// limit updates to once per second
		if(time() > $this->lastTimestamp) {
			foreach ($this->clients as $sendto) {
				$sendto->send(file_get_contents(__DIR__."/../../../../../public/latest-change.txt"));
			}
			$this->lastTimestamp = time();
		}
	}

}
