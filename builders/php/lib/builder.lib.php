<?php

/*!
 * pattern lab builder class - v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

class Builder {

	protected $m;  // mustache instance
	protected $d;  // data from data.json files
	protected $sp; // source patterns dir
	protected $pp; // public patterns dir
	protected $dp; // permissions for the public pattern dirs
	protected $fp; // permissions for the public pattern files
	protected $if; // directories/files to be ignored in source/patterns
	protected $wf; // files to be watched to see if they should be moved
	protected $mf; // where the files should be moved too
	protected $websocketAddress; // for populating the websockets template partial
	protected $contentSyncPort; // for populating the websockets template partial
	protected $navSyncPort; // for populating the websockets template partial
	
	/**
	* Start up the builder
	*/
	public function __construct() {
		
		// set-up the configuration options for patternlab
		if (!($config = @parse_ini_file(__DIR__."/../../../config/config.ini"))) {
			// config.ini didn't exist so attempt to create it using the default file
			if (!@copy(__DIR__."/../../../config/config.ini.default", __DIR__."/../../../config/config.ini")) {
				print "Please make sure config.ini.default exists before trying to have PatternLab build the config.ini file automagically.";
				exit;
			} else {
				$config = @parse_ini_file(__DIR__."/../../../config/config.ini");	
			}
		}
		
		// populate some standard variables out of the config
		foreach ($config as $key => $value) {
			if (($key == "if") || ($key == "wf") || ($key == "mf")) {
				$values = explode(",",$value);
				array_walk($values,'Builder::trim');
				$this->$key = $values;
			} else {
				$this->$key = $value;
			}
		}
		
	}
	
	protected function mustacheInstance() {
		return new Mustache_Engine(array(
			'loader' => new Mustache_Loader_PatternLoader(__DIR__.$this->sp),
			"partials_loader" => new Mustache_Loader_PatternLoader(__DIR__.$this->sp)
		));
	}
	
	/**
	 * Gathers data in all of the data.json files
	 */
	protected function gatherData() {
		
		// gather the data from the main source data.json
		if (file_exists(__DIR__."/../../../source/data/data.json")) {
			$this->d = (object) array_merge((array) $this->d, (array) json_decode(file_get_contents(__DIR__."/../../../source/data/data.json")));
		}
		
		// gather data from pattern/data.json
		$entries = scandir(__DIR__.$this->sp);
		foreach($entries as $entry) {
			if (!in_array($entry,$this->if)) {
				if (file_exists(__DIR__.$this->sp.$entry."/data.json")) {
					$d = new stdClass();
					$d->$entry = json_decode(file_get_contents(__DIR__.$this->sp.$entry."/data.json"));
					$this->d = (object) array_merge((array) $this->d, (array) $d);
				}
			}
		}
		
	}
	
	protected function renderPattern($f,$m) {
		return $m->render($f,$this->d);
	}
	
	private function renderFile($f,$m) {
		$h  = file_get_contents(__DIR__.$this->sp."d-wrapper/header.html");
		$rf = $this->renderPattern($f,$m);
		$f  = file_get_contents(__DIR__.$this->sp."d-wrapper/footer.html");
		return $h."\n".$rf."\n".$f;
	}
	
	protected function renderAndMove() {
		
		$m = $this->mustacheInstance();
		
		$entries = scandir(__DIR__.$this->sp);
		foreach($entries as $entry) {
			if (!in_array($entry,$this->if)) {
				if (file_exists(__DIR__.$this->sp.$entry."/pattern.mustache")) {
					$r = $this->renderFile($entry."/pattern.mustache",$m);
					if (!is_dir(__DIR__.$this->pp.$entry)) {
						mkdir(__DIR__.$this->pp.$entry);
						//chmod($this->pp.$entry,$this->dp);
						file_put_contents(__DIR__.$this->pp.$entry."/pattern.html",$r);
						//chmod($this->pp.$entry."/pattern.html",$this->fp);
					} else {
						file_put_contents(__DIR__.$this->pp.$entry."/pattern.html",$r);
					}
				}
			}
		}
	}
	
	public function printData() {
		print_r($this->d);
	}
	
	public function trim(&$v) {
		$v = trim($v);
	}

}
