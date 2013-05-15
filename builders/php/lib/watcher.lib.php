<?php

/*!
 * patternlab watcher class - php v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * Watches the source/patterns dir for any changes so they can be automagically
 * moved to the public/patterns dir.
 *
 * This is not the most efficient implementation of a directory watch but I hope
 * it's the most platform agnostic.
 *
 */

class Watcher extends Builder {
	
	/**
	* Start up the watcher
	*/
	public function __construct() {
		
		// construct the parent
		parent::__construct();
		
	}
	
	public function watch() {
		
		$c = false;          // have the files been added to the overall object?
		$t = false;          // was their a change found? re-render
		$o = new stdClass(); // create an object to hold the properties
		
		// generate all of the patterns
		$entries = scandir(__DIR__.$this->sp);
		
		// run forever
		while (true) {
			foreach($entries as $entry) {
				
				if (!in_array($entry,$this->if)) {
					
					// figure out how to watch for new directories and new files
					if (!$c) {
						$o->$entry = new stdClass();
					} else {
						/*if ($o->$entry == undefined) {
							$this->renderAndMove();
							$o->entry;
						}*/
					}
					
					$ph = $this->md5File(__DIR__.$this->sp.$entry."/pattern.mustache");
					$dh = $this->md5File(__DIR__.$this->sp.$entry."/data.json");
					
					if (!$c) {
						$o->$entry->ph = $ph;
						$o->$entry->dh = $dh;
					} else {
						if ($o->$entry->ph != $ph) {
							$t = true;
							$o->$entry->ph = $ph;
							print $entry."/pattern.mustache changed...\n";
						}
						if ($o->$entry->dh != $dh) {
							$t = true;
							$o->$entry->dh = $dh;
							print $entry."/data.json changed...\n";
						}
						if ($t) {
							$this->gatherData();
							$this->renderAndMove();
							$t = false;
						}
					}
					
				}
				
			}
			
			// check the user-supplied watch files (e.g. css)
			$i = 0;
			foreach($this->wf as $wf) {
				
				if (!$c) {
					$o->$wf = new stdClass();
				}
				
				$fh = $this->md5File(__DIR__."/../../../source".$wf);
				if (!$c) {
					$o->$wf->fh = $fh;
				} else {
					if ($o->$wf->fh != $fh) {
						$o->$wf->fh = $fh;
						$this->moveFile($wf,$this->mf[$i]);
						print $wf." changed...\n";
					};
					$i++;
				}
				
			}
			
			// check the main data.json file for changes
			$dh = $this->md5File(__DIR__."/../../../source/data/data.json");
			if (!$c) {
				$o->dh = $dh;
			} else {
				if ($o->dh != $dh) {
					$o->dh = $dh;
					$this->gatherData();
					$this->renderAndMove();
					print "data/data.json changed...\n";
				};
			}
			
			$c = true;
		}
		
	}
	
	private function md5File($f) {
		$r = file_exists($f) ? md5_file($f) : '';
		return $r;
	}
	
	private function moveFile($s,$p) {
		copy(__DIR__."/../../../source".$s,__DIR__."/../../../public".$p);
	}
	
}
