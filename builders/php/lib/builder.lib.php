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
	* Renders the view all pages
	*
	* @return {String}        writes out each view all page
	*/
	protected function generateViewAllPages() {
		
		// silly to do this again but makes sense in light of the fact that watcher needs to use this function too
		$nd = $this->gatherNavItems();
		
		// add view all to each list
		$i = 0; $k = 0;
		foreach ($nd['buckets'] as $bucket) {
			
			foreach ($bucket["navItems"] as $navItem) {
				
				foreach ($navItem["navSubItems"] as $subItem) {
					if ($subItem["patternName"] == "View All") {
						
						// get all the rendered partials that match
						$sid = $this->gatherPartialsByMatch($subItem["patternPath"]);
						
						// render the viewall template
						$e = new Mustache_Engine(array(
							'loader' => new Mustache_Loader_FilesystemLoader(__DIR__."/../../../source/templates/"),
							'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__."/../../../source/templates/partials/"),
						));
						$v = $e->render('viewall',$sid);
						
						// if the pattern directory doesn't exist create it
						if (!is_dir(__DIR__.$this->pp.$subItem["patternPath"])) {
							mkdir(__DIR__.$this->pp.$subItem["patternPath"]);
							//chmod($this->pp.$entry,$this->dp);
							file_put_contents(__DIR__.$this->pp.$subItem["patternPath"]."/pattern.html",$v);
							//chmod($this->pp.$entry."/pattern.html",$this->fp);
						} else {
							file_put_contents(__DIR__.$this->pp.$subItem["patternPath"]."/pattern.html",$v);
						}
					}
				}
				
			}
			
			$i++;
			$k = 0;
			
		}
		
	}
	
	/**
	* Gathers the partials for the nav drop down in Pattern Lab
	*
	* @return {Array}        the nav items organized by type
	*/
	protected function gatherNavItems() {
		
		$b  = array(); // the array that will contain the items
		$t  = array(); // the array that will contain the english names for the types of buckets
		$cc = "";      // current class of the object we're looking at (e.g. atom)
		$cn = 0;       // track the number for the array
		$sc = "";      // current sub-class of the object we're looking at (e.g. block)
		$sn = 0;       // track the number for the array
		$n  = "";      // the name of the final object
		
		$b["buckets"] = array();
		$t   = array("a" => "Atoms", "m" => "Molecules", "o" => "Organisms", "p" => "Pages");
		$cco = $cc;    // prepopulate the "old" check of the previous current class
		$cno = $cn;    // prepopulate the "old" check of the previous current class
		$sco = $sc;    // prepopulate the "old" check of the previous current class
		$sno = $sn;
		
		// scan the pattern source directory
		$entries = scandir(__DIR__."/".$this->sp);
		foreach($entries as $entry) {
			
			// decide which files in the source directory might need to be ignored
			if (!in_array($entry,$this->if)) {
				$els = explode("-",$entry,3);
				$cc  = $els[0];
				$sc  = $els[1];
				$n   = ucwords(str_replace("-"," ",$els[2]));
				
				// place items in their buckets. i'm already confused looking back at this. it works tho...
				if ($cc == $cco) {
					if ($sc == $sco) {
						$b["buckets"][$cno]["navItems"][$sno]["navSubItems"][] = array(
																				"patternPath" => $entry,
																				"patternName"  => $n
																			   );
					} else {
						$sn++;
						$b["buckets"][$cno]["navItems"][$sn] = array(
																"sectionNameLC" => $sc,
																"sectionNameUC" => ucwords($sc),
																"navSubItems" => array(
																	array(
																		"patternPath" => $entry,
																		"patternName"  => $n
															  )));
						$sco = $sc;
						$sno = $sn;
					}
				} else {
					$b["buckets"][$cn] = array(
											   "bucketNameLC" => strtolower($t[$cc]),
											   "bucketNameUC" => $t[$cc], 
											   "navItems" => array( 
														array(
														"sectionNameLC" => $sc,
														"sectionNameUC" => ucwords($sc),
														"navSubItems" => array(
															array(
																"patternPath" => $entry,
																"patternName"  => $n
											    )))));
					$cco = $cc;
					$sco = $sc;
					$cno = $cn;
					$cn++;
					$sn = 0;
				}
			}
		}
		
		// add view all to each list
		$i = 0; $k = 0;
		foreach ($b['buckets'] as $bucket) {
			
			if ($bucket["bucketNameLC"] != "pages") {
				foreach ($bucket["navItems"] as $navItem) {
					
					$subItemsCount = count($navItem["navSubItems"]);
					$pathItems = explode("-",$navItem["navSubItems"][0]["patternPath"]);
					$viewAll = array("patternPath" => $pathItems[0]."-".$pathItems[1], "patternName" => "View All");
					
					$b['buckets'][$i]["navItems"][$k]["navSubItems"][$subItemsCount] = $viewAll;
					
					$k++;
				}
				
			}
			
			$i++;
			$k = 0;
		}
		
		return $b;
		
	}
	
	/**
	* Renders the patterns that match a given string so they can be used in the view all styleguides
	* It's duplicative but I'm tired
	*
	* @return {Array}        an array of rendered partials that match the given path
	*/
	protected function gatherPartialsByMatch($pathMatch) {
		
		$m = $this->mustacheInstance();
		$p = array("partials" => array());
		
		// scan the pattern source directory
		$entries = scandir(__DIR__."/".$this->sp);
		foreach($entries as $entry) {
			
			// decide which files in the source directory might need to be ignored
			if (!in_array($entry,$this->if) && ($entry[0] != "p") && strstr($entry,$pathMatch)) {
				if (file_exists(__DIR__."/".$this->sp.$entry."/pattern.mustache")) {
					
					// render the partial and stick it in the array
					$p["partials"][] = $this->renderPattern($entry."/pattern.mustache",$m);
					
				}
			}
			
		}
		
		return $p;
		
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
