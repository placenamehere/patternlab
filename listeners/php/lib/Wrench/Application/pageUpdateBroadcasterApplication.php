<?php

/*!
 * Page Update Broadcaster, v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * Listens for when a page changes in one of the attached browsers. Sends the
 * change to all listening browsers so they can also update.
 *
 */

namespace Wrench\Application;

use Wrench\Application\Application;
use Wrench\Application\NamedApplication;

class pageUpdateBroadcasterApplication extends Application {
	protected $clients = array();
	protected $lastTimestamp = null;
	protected $currentAddress = null;
	
	/**
	 * @see Wrench\Application.Application::onConnect()
	 */
	public function onConnect($client) {
		$id = $client->getId();
		$this->clients[$id] = $client;
		if ($this->currentAddress != null) {
			$client->send($this->currentAddress);
		}
	}
	
	public function onDisconnect($client) {
		$id = $client->getId();
		unset($this->clients[$id]);
	}
	
	public function onData($data, $client) {
		foreach ($this->clients as $sendto) {
			$sendto->send($data);
		}
		$this->currentAddress = $data;
	}

	/**
	 * @see Wrench\Application.Application::onUpdate()
	 */

	public function onUpdate() {
		// not using for this application
	}

}
