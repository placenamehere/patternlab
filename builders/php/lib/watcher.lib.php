<?php

/*!
 * pattern lab watcher class - v0.1
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
	* Use the Builder __construct to gather the config variables
	*/
	public function __construct() {
		
		// construct the parent
		parent::__construct();
		
	}
	
	/**
	* Watch the source directory for any changes to existing files. Will run forever if given the chance
	*/
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
					
					// figure out the md5 hash of a file so we can track changes
					// runs well on a solid state drive. no idea if it thrashes regular disks
					$ph = $this->md5File(__DIR__.$this->sp.$entry."/pattern.mustache");
					$dh = $this->md5File(__DIR__.$this->sp.$entry."/data.json");
					
					// if the first time through just add the hash
					// after that check the hash to the recorded hash. if it has, render & move the *entire* project (shakes fist at partials)
					// update the change time so that content sync will work properly
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
							$this->generateViewAllPages();
							$this->updateChangeTime();
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
				
				// md5 hash the user-supplied filenames, if it's changed just move the single file
				// update the change time so that content sync will work properly
				$fh = $this->md5File(__DIR__."/../../../source".$wf);
				if (!$c) {
					$o->$wf->fh = $fh;
				} else {
					if ($o->$wf->fh != $fh) {
						$o->$wf->fh = $fh;
						$this->moveFile($wf,$this->mf[$i]);
						$this->updateChangeTime();
						print $wf." changed...\n";
					};
					$i++;
				}
				
			}
			
			// check the main data.json file for changes, if it's changed render & move the *entire* project
			// update the change time so that content sync will work properly
			$dh = $this->md5File(__DIR__."/../../../source/data/data.json");
			if (!$c) {
				$o->dh = $dh;
			} else {
				if ($o->dh != $dh) {
					$o->dh = $dh;
					$this->gatherData();
					$this->renderAndMove();
					$this->generateViewAllPages();
					$this->updateChangeTime();
					print "data/data.json changed...\n";
				};
			}
			
			$c = true;
		}
		
	}
	
	/**
	* Converts a given file into an md5 string
	* @param  {String}       file name to be hashed
	*
	* @return {String}       md5 string of the file or an empty string if the file wasn't found
	*/
	private function md5File($f) {
		$r = file_exists($f) ? md5_file($f) : '';
		return $r;
	}
	
	/**
	* Copies a file from the given source path to the given public path
	* @param  {String}       the source pattern name
	* @param  {String}       the public pattern name
	*
	* @return {String}       copied file
	*/
	private function moveFile($s,$p) {
		copy(__DIR__."/../../../source".$s,__DIR__."/../../../public".$p);
	}
	
	/**
	* Write out the time tracking file so the content sync service will work. A holdover
	* from how I put together the original AJAX polling set-up.
	*
	* @return {String}       file containing a timestamp
	*/
	private function updateChangeTime() {
		file_put_contents(__DIR__."/../../../public/latest-change.txt",time());
	}
	
}
