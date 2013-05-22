<?php

/*!
 * pattern lab generator class - v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * Compiles and moves all files in the source/patterns dir to public/patterns dir.
 *
 */

class Generator extends Builder {
	
	/**
	* Use the Builder __construct to gather the config variables
	*/
	public function __construct() {
		
		// construct the parent
		parent::__construct();
		
	}
	
	/**
	* Main logic. Gathers data, gets partials, and generates patterns
	* Also generates the main index file and styleguide
	*/
	public function generate() {
		
		// gather data
		$this->gatherData();
		
		// render out the patterns and move them to public/patterns
		$this->renderAndMove();
		
		// render out the main pages and move them to public
		$nd = $this->gatherNavItems();
		$nd['contentsyncport'] = $this->contentSyncPort;
		$nd['navsyncport'] = $this->navSyncPort;
		
		// grab the partials into a data object for the style guide
		$sd = $this->gatherPartials();
		
		// render the "view all" pages
		$this->generateViewAllPages();
		
		// render the index page and the style guide
		$e = new Mustache_Engine(array(
			'loader' => new Mustache_Loader_FilesystemLoader(__DIR__."/../../../source/templates/"),
			'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__."/../../../source/templates/partials/"),
		));
		$r = $e->render('index',$nd);
		file_put_contents(__DIR__."/../../../public/index.html",$r);
		
		$s = $e->render('styleguide',$sd);
		file_put_contents(__DIR__."/../../../public/styleguide.html",$s);
		//chmod('../../public/index.html',$this->fp);
		
	}
	
	/**
	* Renders the patterns in the source directory so they can be used in the default styleguide
	*
	* @return {Array}        an array of rendered partials
	*/
	private function gatherPartials() {
		
		$m = $this->mustacheInstance();
		$p = array("partials" => array());
		
		// scan the pattern source directory
		$entries = scandir(__DIR__."/".$this->sp);
		foreach($entries as $entry) {
			
			// decide which files in the source directory might need to be ignored
			if (!in_array($entry,$this->if) && ($entry[0] != "p")) {
				if (file_exists(__DIR__."/".$this->sp.$entry."/pattern.mustache")) {
					
					// render the partial and stick it in the array
					$p["partials"][] = $this->renderPattern($entry."/pattern.mustache",$m);
					
				}
			}
			
		}
		
		return $p;
		
	}
	

	
}