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
	protected $lastTimestamp = null;
	protected $currentAddress = null;
	
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

	public function onUpdate() {
		// not using for this application
	}

}
