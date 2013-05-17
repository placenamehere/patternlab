<?php

/*!
 * Content Update Broadcaster, v0.1
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

class contentUpdateBroadcasterApplication extends Application {
	protected $clients          = array();
	protected $lastTimestamp    = null;
	protected $currentAddress   = null;
	
	/**
	 * @see Wrench\Application.Application::onConnect()
	 */
	public function onConnect($client) {
		$id = $client->getId();
		$this->clients[$id] = $client;
	}
	
	public function onDisconnect($client) {
		$id = $client->getId();
		unset($this->clients[$id]);
	}
	
	public function onData($data, $client) {
		// function not in use
	}

	/**
	 * @see Wrench\Application.Application::onUpdate()
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
