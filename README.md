# Pattern Lab

## About

Brad had a crazy idea.

## Installation

After downloading Pattern Lab you can do the following to set it up:

### 1. Configure Apache

Most of the features in Pattern Lab require it to be running on a web server like Apache. The ideal set-up is to run Apache and Pattern Lab locally on your computer. If you don't know how to set-up Apache there are directions for Mac OS X in `extras/apache/`. At the very least, the `DocumentRoot` for the site should be set-to `/path/to/patternlab/public/`.

### 2. Configure & Pre-Build Pattern Lab

By default, a number of important pages, including the main page, *aren't* built when you first download Pattern Lab. Before you visit your install of Pattern Lab you'll need to make sure all of the necessary pages have been built. 

To generate your site do the following:

1. Open `scripts/php`
2. Double-click `generateSite.command`

The site should now be generated and available. Simply follow the "Regular Use" steps to finish the set-up process.

## Regular Use

Their are several options you can enable when using Pattern Lab on a regular basis.

### Watch for Changes & Auto-Regenerate Patterns

Once you've generated the site Pattern Lab can watch for changes to patterns or their related data. When they're being watched, Pattern Lab's public files will be automagically rendered when you save patterns or their related data. To see changes all you need to do is refresh your browser. 

To set-up the watch do the following:

1. Open `scripts/php`
2. Double-click `watchForChanges.command`

To make Pattern Lab stop watching your files just press`CTRL+C`. 

By default, Pattern Lab will monitor the `pattern.mustache` and `data.json` files in `source/patterns`. It will also watch `source/data/data.json` as well as any user-defined files listed in `config/config.ini`. For example, you might want to track a Sass-built `styles.css` file.

**Please note:** Pattern Lab will not watch for and automatically add completely brand-new patterns. To add a completely brand-new pattern simply stop the watch by pressing `CTRL+C` and then double-click `watchForChanges.command`. Your new files should now be added and should be tracked for changes.

### Auto-Reload the Browser Window When Content Updates

Rather than manually refreshing your browser you can have Pattern Lab auto-reload your browser window for you. To turn this feature on do the following:

1. Open `scripts/php`
2. Double-click `startContentSyncServer.command`
3. Refresh the Pattern Lab site

Your browser should now be listening for auto-reload events. The Pattern Lab toolbar should note that content sync is now "on."

**Please note:** If you find that content sync is not working properly please make sure your browser [supports WebSockets](http://caniuse.com/websockets).

### Sync Pattern Browsing Across Multiple Tabs or Browsers (aka Page Follow)

If you want to test a pattern in multiple tabs or browsers without refreshing them all or having to navigate to new patterns in each simply use Pattern Lab's page follow feature. Any browser or tab should control all of the browsers or tabs. To turn this feature on do the following:

1. Open `scripts/php`
2. Double-click `startNavSyncServer.command`
3. Refresh the Pattern Lab site

Your browser should now be listening for page follow events. The Pattern Lab toolbar should note that page follow is now "on." Any other browser that visits the Pattern Lab site should now be redirected to the last visited pattern. When one browser views another pattern they should all be updated.

If you want to link patterns together (for a demo or to flip between "page" patterns) you can use the following format to have Pattern Lab put in the correct path:

    <a href="{{ link.full-pattern-name }}">Link Text</a>

For example, to link to the block hero molecule pattern you would use:

    <a href="{{ link.m-blocks-block-hero }}">Link Text</a>

If you want to view patterns on your mobile device simply do the following:

1. Make sure your mobile device and computer are on the same WiFi network
2. Note the IP address for your computer (found under System Preferences > Sharing)
3. Replace the star with your IP address in the following address: `patternlab.*.xip.io`
4. Enter that into the browser on your mobile device

The above assumes that your Apache VirtualHost has `patternlab.*.xip.io` as a `ServerAlias`. If it doesn't please add it.

**Please note:** If you find that nav sync is not working properly please make sure your browser [supports WebSockets](http://caniuse.com/websockets).

## All About Patterns

All you ever wanted to know about patterns.

### How Patterns Are Organized

Patterns are organized into atoms, molecules, organisms, and pages. The pattern directories have the following naming convention: 

    [patternComplexity]-[patternType]-[patternName]

This is what each means:

* `patternComplexity` denotes the overall type of pattern. _a_ is for atoms, _m_ is for molecules, _o_ is for organisms, and _p_  is for pages.
* `patternType` denotes the sub-type of pattern. This helps to organize patterns in the drop downs in Pattern Lab.
* `patternName` is obviously the actual name of the pattern.

In order for Pattern Lab to work you must follow the directory naming convention when creating new patterns. Also, a pattern **must** be named `pattern.mustache`.

### Modifying Individual Patterns

To modify an individual pattern simply open it up and edit it. Patterns can be found in `source/[pattern-name]/pattern.mustache`. Pattern Lab supports the [Mustache syntax](http://mustache.github.io/mustache.5.html).

### Including One Pattern Within Another

To use another pattern in another, for example to create a molecule from several atoms, just use the [Mustache](http://mustache.github.io/mustache.5.html) partials syntax. The name of the partial should be the name of the directory for the partial you want to include. For example, to include the logo image atom you'd use:

    {{> a-images-logo }}

### Adding/Modifying Static Assets for a Pattern

To add static assets like a special CSS file that's included by your pattern or an image that's referenced by a pattern simply put the asset in the appropriate directory in `public/`. For example, JavaScript should go in `public/js/` and CSS should go in `public/css/`. Pattern Lab comes with several standard libraries by default.

### Modifying Data for a Pattern

A pattern can reference variables, [via Mustache](http://mustache.github.io/mustache.5.html), to include "dynamic" content. Depending on your preferences you can define and/or modify data in three places:

1. Include a `data.json` file in the pattern directory itself. When referencing the data in the pattern you **must** scope the data to that pattern's name. For example, if you want to reference the data in the `data.json` in the `source/patterns/a-images-landscape-4x3` directory in your pattern you must reference `{{ a-images-landscape-4x3.src }}`.
2. Modify `source/data/data.json` and use a nested naming scheme. For example, the first entry in the default file nests by type and sub-type. To reference the nested landscape 4x3 image in your pattern you'd use `{{ atoms.images.landscape-4x3.src }}`.
3. Modify `source/data/data.json` and use a flat naming scheme. For example, the second entry in the default file doesn't nest the object at all. To reference the flat landscape 4x3 image in your pattern you'd use `{{ landscape-4x3.src }}`

All of these are supported "out-of-the-box." There's no need to settle on any particular format.

## Credits

The default install of Pattern Lab uses a number of PHP libraries. They are:

* [mustache.php](https://github.com/bobthecow/mustache.php)
* [Wrench](https://github.com/varspool/Wrench)

## IDEAS

Ok, so these are the things I want to work on cleaning up:

* <del>I've done a simple tweak where choosing an option updates the iframe instead of reloading the page. I want to get the accordion working properly and, more importantly, I want changes made to patterns, data & styles reflected in an auto-update to the existing window. So you could "update a pattern," system generates new mark-up, the viewer re-loads the iframe and updates the pattern nav w/ a helpful "we've been updated text/color change." Hopefully this would make for a clean, iterative process.</del>
* <del>add a way to easily reference other templates as part of a pattern. this would add the "click-through" feature.</del>
* not sure about the versioning... I can see a method for it but it makes PHP an absolute requirement in the short-term.
* <del>that said, i'd also love to make this multi-device capable (e.g. update pattern on a desktop and see it show up across multiple devices). not sure how ish works in that context but it'd be cool to play around with. this would also require a server-side language choice... i think.</del>
* this repo has to be cleaned & moved to a github organization. you have at least one image which shouldn't be here and, since it's under version control, it'll always be here even in a delete.

Crazy idea, a codepen like interface for modifying the pattern, related data, and styles in the browser. I don't think I could touch related JS.

