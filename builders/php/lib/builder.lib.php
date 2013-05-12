<?php

/*!
 * patternlab builder class - php v0.1
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
	
	/**
	* Start up the builder
	*/
	public function __construct() {
		
		// set-up the configuration options for patternlab
		if (!($config = @parse_ini_file("../../config/config.ini"))) {
			// config.ini didn't exist so attempt to create it using the default file
			if (!@copy("../../config/config.ini.default", "../../config/config.ini")) {
				print "Please make sure config.ini.default exists before trying to have PatternLab build the config.ini file automagically.";
				exit;
			} else {
				$config = @parse_ini_file("../../config/config.ini");	
			}
		}
		
		// populate some standard variables out of the config
		foreach ($config as $key => $value) {
			$this->$key = ($key == "if") ? explode(",",$value) : $value;
		}
		
		require 'lib/mustache/Autoloader.php';
		Mustache_Autoloader::register();
		
		$this->m = new Mustache_Engine(array(
			'loader' => new Mustache_Loader_PatternLoader($this->sp),
			'partials_loader' => new Mustache_Loader_PatternLoader($this->sp)
		));
		
	}
	
	/**
	 * Gathers data in all of the data.json files
	 */
	protected function gatherData() {
		$entries = scandir($this->sp);
		foreach($entries as $entry) {
			if (!in_array($entry,$this->if)) {
				if (file_exists($this->sp.$entry."/data.json")) {
					$d = new stdClass();
					$d->$entry = json_decode(file_get_contents($this->sp.$entry."/data.json"));
					$this->d = (object) array_merge((array) $this->d, (array) $d);
				}
			}
		}
	}
	
	private function renderFile($f) {
		$h  = file_get_contents($this->sp."d-wrapper/header.html");
		$rf = $this->m->render($f,$this->d);
		$f  = file_get_contents($this->sp."d-wrapper/footer.html");
		return $h."\n".$rf."\n".$f;
	}
	
	protected function renderAndMove() {
		$entries = scandir($this->sp);
		foreach($entries as $entry) {
			if (!in_array($entry,$this->if)) {
				if (file_exists($this->sp.$entry."/pattern.mustache")) {
					$r = $this->renderFile($this->sp.$entry."/pattern.mustache");
					if (!is_dir($this->pp.$entry)) {
						mkdir($this->pp.$entry);
						chmod($this->pp.$entry,$this->dp);
						file_put_contents($this->pp.$entry."/pattern.html",$r);
						chmod($this->pp.$entry."/pattern.html",$this->fp);
					} else {
						file_put_contents($this->pp.$entry."/pattern.html",$r);
					}
				}
			}
		}
	}
	
	public function printData() {
		print_r($this->d);
	}

}
