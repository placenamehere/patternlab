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
	* Gathers the partials for the nav drop down in Pattern Lab
	*
	* @return {Array}        the nav items organized by type
	*/
	private function gatherNavItems() {
		
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
		
		return $b;
		
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