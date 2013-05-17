# Pattern Lab

## About

Brad had a crazy idea.

## Installation

After downloading Pattern Lab you can do the following to set it up:

### 1. Set-up Your Web Server

Make sure the web server is pointed at the `public` directory as the `DocumentRoot` for the Pattern Lab site. Not sure what that means? There are directions in `extras/apache/` to help you.

### 2. Configure & Pre-load Pattern Lab

By default, a number of important pages like `public/index.html` *aren't* built when you first download Pattern Lab. So before you visit the site you'll need to make sure it is built. To do so open Terminal, get to the Pattern Lab directory and type:

    php builders/php/builder.php -g

The site should now be generated and available. Simply follow the Regular Use steps to finish the set-up process.

## Regular Use

Each time you used Pattern Lab you'd want to follow these steps.

### 1. Watching for Changes To Your Patterns & Data

Once you've generated the site you can set-up the Pattern Lab builder to watch for changes to patterns or their related data. This way the public files will be automagically rendered when you save patterns or their data. All you'll need to do to see changes you make is to refresh the browser. To set-up the watch type:

    php builders/php/builder.php -w

To stop the `watch` just press`CTRL+C`. By default, `watch` will monitor the `pattern.mustache` and `data.json` files in `source/patterns`, `source/data/data.json`, as well as any user-defined files listed in `config/config.ini` like a Sass-built `styles.css` file.

**Please note:** Watch doesn't currently add completely brand-new patterns. To add a completely brand-new pattern simply stop `watch` by pressing `CTRL+C`, `generate` the site again, and then `watch` again. Your new files should now be added and should be tracked for changes.

### 2. Auto-reload The Browser Window When Content Updates

Rather than manually refreshing your browser you can have Pattern Lab auto-reload your browser window for you. To do so simply do the following:

1. Open a new tab or new window in Terminal
2. Make sure you're in the Pattern Lab directory
3. Type `php listeners/php/contentUpdateBroadcasterServer.php` and hit return

Reload the Pattern Lab website and your browser should now be listening for auto-reload events. To test simply modify a pattern and see if the window updates.

**Please note:** If you find that it's not working properly make sure your browser [supports WebSockets](http://caniuse.com/websockets) and that the address you're visiting in your browser matches the websocketAddress in `config/config.ini`.

### 3. Test in Multiple Tabs or Multiple Browsers

If you want to test a pattern in multiple tabs or browsers without refreshing them all or having to navigate to new patterns in each simply set-up the Page Update Broadcaster. Any browser or tab should control all of the browsers or tabs. To do so simply do the following:

1. Open a new tab or new window in Terminal
2. Make sure you're in the Pattern Lab directory
3. Type `php listeners/php/pageUpdateBroadcasterServer.php` and hit return

Reload the Pattern Lab website in multiple tabs or multiple browsers and they should now be listening for auto-page events. To test simply visit a different pattern in one browser and see if it shows up in the other tabs.

**Please note:** If you find that it's not working properly make sure your browser [supports WebSockets](http://caniuse.com/websockets) and that the address you're visiting in your browser matches the websocketAddress in `config/config.ini`.

## Modifying Patterns

Modifying patterns is fairly straightforward.

### Modifying Patterns Themselves

Modify the pattern itself by hacking at `[pattern-name]/pattern.mustache`. If you're using `watch` the changes will be automatically tracked. Pattern Lab supports the [Mustache syntax](http://mustache.github.io/mustache.5.html).

### Referencing Another Pattern

To use another pattern in the pattern your editing simply use the [Mustache](http://mustache.github.io/mustache.5.html) partials syntax. The name of the partial is simply the directory holding the other pattern.

    {{> a-images-logo }}

### Organizing Patterns

Patterns are organized into atoms, molecules, organisms, and pages. In order to have all of the build and partial support work automatically simply follow this directory naming convention:

    [patternComplexity]-[patternType]-[patternName]

This is what each means:

* The first letter of pattern directory should start with: a (*for atoms*), m (*for molecules*), o (*for organisms*), or p (*for pages*).
* The second part of the name is the type of pattern it is. e.g. how to organize it in the drop-down in Pattern Lab.
* The third part is the name of the pattern itself.

A pattern **must** be named `pattern.mustache`.

### Adding/Modifying Static Assets for a Pattern

To add static assets like a special CSS file that's included by your pattern or an image that's referenced by a pattern simply put the asset in the appropriate directory in `public/`.

### Modifying Data for a Pattern

A pattern can reference variables, [via Mustache](http://mustache.github.io/mustache.5.html), to include dynamic content. You can define the data in three places:

1. Include a `data.json` file in the pattern directory itself. When referencing the data in the pattern you must scope the data to that pattern name. For example, if you want to use reference the image src in `data.json` in the `a-images-landscape` directory in your pattern you must reference `{{ a-images-landscape.landscape-4x3.src }}`.
2. Modify the global `data.json` file in the `source` dir and use a nested naming scheme. If you look at the `data.json` file you'll see the first example nests the landscape 4x3 path in `atoms > images` just as if they were in a real directory. You'd reference the var in your pattern as `{{ atoms.images.landscape-4x3.src }}`
3. Modify the global `data.json` file in the `source` dir and use a flat naming scheme. If you look at the `data.json` file you'll see the first example just has the landscape 4x3 path at the top level. You'd reference the var in your pattern as `{{ landscape-4x3.src }}`

All of these are supported "out-of-the-box." There's no need to settle on any particular format.

## IDEAS

Ok, so these are the things I want to work on cleaning up:

* <del>I've done a simple tweak where choosing an option updates the iframe instead of reloading the page. I want to get the accordion working properly and, more importantly, I want changes made to patterns, data & styles reflected in an auto-update to the existing window. So you could "update a pattern," system generates new mark-up, the viewer re-loads the iframe and updates the pattern nav w/ a helpful "we've been updated text/color change." Hopefully this would make for a clean, iterative process.</del>
* add a way to easily reference other templates as part of a pattern. this would add the "click-through" feature.
* not sure about the versioning... I can see a method for it but it makes PHP an absolute requirement in the short-term.
* <del>that said, i'd also love to make this multi-device capable (e.g. update pattern on a desktop and see it show up across multiple devices). not sure how ish works in that context but it'd be cool to play around with. this would also require a server-side language choice... i think.</del>
* this repo has to be cleaned & moved to a github organization. you have at least one image which shouldn't be here and, since it's under version control, it'll always be here even in a delete.

Crazy idea, a codepen like interface for modifying the pattern, related data, and styles in the browser. I don't think I could touch related JS.

