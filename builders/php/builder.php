<?php

/*!
 * Pattern Lab Builder CLI v0.1
 *
 * Copyright (c) 2013 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

require __DIR__."/lib/builder.lib.php";
require __DIR__."/lib/generator.lib.php";
require __DIR__."/lib/watcher.lib.php";

require __DIR__."/lib/mustache/Autoloader.php";
Mustache_Autoloader::register();

// make sure this script is being accessed from the command line
if (php_sapi_name() == 'cli') {
	
	$args = getopt("gw");
	
	if (isset($args["g"])) {
		
		// iterate over the source directory and generate the site
		$g = new Generator();
		$g->generate();
		print "your site has been generated...\n";
		
	} elseif (isset($args["w"])) {
		
		// watch the source directory and regenerate any changed files
		$w = new Watcher();
		print "watching your site for changes...\n";
		$w->watch();
		
	} else {
		
		// when in doubt write out the usage
		print "\n";
		print "Usage:\n\n";
		print "  php builder.php -g\n";
		print "    Iterates over the 'source' directory and generates the entire site a single time.\n\n";
		print "  php builder.php -w\n";
		print "    Watches for changes in the 'source' directory and generates any new files or regenerates files if they've changed.\n\n";
		
	}

} else {

	print "The builder script can only be run from the command line.";

}
