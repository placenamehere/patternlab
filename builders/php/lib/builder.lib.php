<?php

/*!
 * pattern lab builder class - v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 */

class Builder {

	// i was lazy when i started this project & kept (mainly) to two letter vars. sorry.
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
	* When initializing the Builder class or the sub-classes make sure the base properties are configured
	* Also, create the config if it doesn't already exist
	*/
	public function __construct() {
		
		// set-up the configuration options for patternlab
		if (!($config = @parse_ini_file(__DIR__."/../../../config/config.ini"))) {
			// config.ini didn't exist so attempt to create it using the default file
			if (!@copy(__DIR__."/../../../config/config.ini.default", __DIR__."/../../../config/config.ini")) {
				print "Please make sure config.ini.default exists before trying to have Pattern Lab build the config.ini file automagically.";
				exit;
			} else {
				$config = @parse_ini_file(__DIR__."/../../../config/config.ini");	
			}
		}
		
		// populate some standard variables out of the config
		foreach ($config as $key => $value) {
			
			// if the variables are array-like make sure the properties are validated/trimmed before saving
			if (($key == "if") || ($key == "wf") || ($key == "mf")) {
				$values = explode(",",$value);
				array_walk($values,'Builder::trim');
				$this->$key = $values;
			} else {
				$this->$key = $value;
			}
		}
		
	}
	
	/**
	* Simply returns a new Mustache instance
	*
	* @return {Object}       an instance of the Mustache engine
	*/
	protected function mustacheInstance() {
		return new Mustache_Engine(array(
			'loader' => new Mustache_Loader_PatternLoader(__DIR__.$this->sp),
			"partials_loader" => new Mustache_Loader_PatternLoader(__DIR__.$this->sp)
		));
	}
	
	/**
	* Gather data from source/data/data.json and data.json files in pattern directories
	* Throws all the data into the Builder class scoped d var
	*/
	protected function gatherData() {
		
		// gather the data from the main source data.json
		if (file_exists(__DIR__."/../../../source/data/data.json")) {
			$this->d = (object) array_merge(array(), (array) json_decode(file_get_contents(__DIR__."/../../../source/data/data.json")));
		}
		
		// this makes link a reserved word but oh well...
		$this->d->link = new stdClass();
		
		// gather data from pattern/data.json
		$entries = scandir(__DIR__.$this->sp);
		foreach($entries as $entry) {
			if (!in_array($entry,$this->if)) {
				$this->d->link->$entry = "/patterns/".$entry."/pattern.html";
				if (file_exists(__DIR__.$this->sp.$entry."/data.json")) {
					$d = new stdClass();
					$d->$entry = json_decode(file_get_contents(__DIR__.$this->sp.$entry."/data.json"));
					$this->d = (object) array_merge((array) $this->d, (array) $d);
				}
			}
		}
		
	}
	
	/**
	* Renders a given pattern file using Mustache and incorporating the provided data
	* @param  {String}       the filename of the file to be rendered
	* @param  {Object}       the instance of mustache to be used in the rendering
	*
	* @return {String}       the mark-up as rendered by Mustache
	*/
	protected function renderPattern($f,$m) {
		return $m->render($f,$this->d);
	}
	
	/**
	* Renders a pattern within the context of spitting out a finished pattern w/ header & footer
	* @param  {String}       the filename of the file to be rendered
	* @param  {Object}       the instance of mustache to be used in the rendering
	*
	* @return {String}       the final rendered pattern including the standard header and footer for a pattern
	*/
	private function renderFile($f,$m) {
		$h  = file_get_contents(__DIR__.$this->sp."d-wrapper/header.html");
		$rf = $this->renderPattern($f,$m);
		$f  = file_get_contents(__DIR__.$this->sp."d-wrapper/footer.html");
		return $h."\n".$rf."\n".$f;
	}
	
	/**
	* Initiates a mustache instance, renders out a full pattern file and places it in the public directory
	*
	* @return {String}       the mark-up placed in it's appropriate location in the public directory
	*/
	protected function renderAndMove() {
		
		// initiate a mustache instance
		$m = $this->mustacheInstance();
		
		// scan the pattern source directory
		$entries = scandir(__DIR__.$this->sp);
		foreach($entries as $entry) {
			
			// decide which files in the source directory might need to be ignored
			if (!in_array($entry,$this->if)) {
				
				if (file_exists(__DIR__.$this->sp.$entry."/pattern.mustache")) {
					
					// render the file
					$r = $this->renderFile($entry."/pattern.mustache",$m);
					
					// if the pattern directory doesn't exist create it
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
	
	/**
	* Print out the data var. For debugging purposes
	*
	* @return {String}       the formatted version of the d object
	*/
	public function printData() {
		print_r($this->d);
	}
	
	/**
	* Trim a given string. Used in the array_walk() function in __construct as a sanity check
	* @param  {String}       an entry from one of the list-based config entries
	*
	* @return {String}       trimmed version of the given $v var
	*/
	public function trim(&$v) {
		$v = trim($v);
	}

}
