<?php

/*!
 * Nav Sync Broadcaster, v0.1
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

class navSyncBroadcasterApplication extends Application {
	
	protected $clients = array();
	protected $currentAddress = null;
	
	/**
	* When a client connects add it to the list of connected clients. Also send the client the current page to load in their iframe
	*/
	public function onConnect($client) {
		$id = $client->getId();
		$this->clients[$id] = $client;
		if ($this->currentAddress != null) {
			$client->send($this->currentAddress);
		}
	}
	
	/**
	* When a client disconnects remove it from the list of connected clients
	*/
	public function onDisconnect($client) {
		$id = $client->getId();
		unset($this->clients[$id]);
	}
	
	/**
	* When receiving a message from a client, strip it to avoid cross-domain issues and send it to all clients except the one who sent it
	* Also store the address as the current address for any new clients that attach
	*/
	public function onData($data, $client) {
		preg_match("/http:\/\/[A-z0-9\-\.]{1,}\/(.*)/i",$data,$matches);
		$data = "/".$matches[1];
		$testId = $client->getId();
		foreach ($this->clients as $sendto) {
			if ($testId != $sendto->getId()) {
				$sendto->send($data);
			}
		}
		$this->currentAddress = $data;
	}

	/**
	* Dead function in this instance
	*/
	public function onUpdate() {
		// not using for this application
	}

}
