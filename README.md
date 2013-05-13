# Pattern Lab

## About

Brad had a crazy idea.

## To Use

This has a couple of steps so bear with me. Sorry.

### Web Server

Make sure the web server is pointed at the `public` directory as the `document root` for the pattern lab site.

### Loading the content

Before you visit the site you'll need to have the main page and styleguide populated. So open Terminal, get to the Pattern Lab directory and type:

    php builders/php/builder.php -g

This will generate the site. I need to figure out a better way to do this though I'm not sure it's possible.

### Watching for changes

Once you've generated the site you can set-up the Pattern Lab builder to watch for changes to patterns or their related data. The files will be automagically rendered when saved and all you need to do is refresh the browser. To set-up the watch type:

    php builders/php/builder.php -w

To stop the `watch` just press`CTRL+c`. *Note:* the main `data.json` file isn't currently being monitored for changes. I'll update that later.

### Modifying patterns

This is a little limited at this point. You can only modify existing patterns when using `watch`. Just stop the `watch`, add a pattern, `generate` again, and then `watch`. I can and will clean this process up. That said...

Modify the pattern itself by hacking at `[patter-name]/pattern.mustache`.

### Modifying the data in a pattern

Their are currently three ways to set this up.

1. Include a `data.json` file in the pattern directory itself. When referencing the data in the pattern you must scope the data to that pattern name. For example, if you want to use reference the image src in `data.json` in the `a-images-landscape` directory in your pattern you must reference `{{ a-images-landscape.landscape-4x3 }}`.
2. Modify the global `data.json` file in the `source` dir and use a nested naming scheme. If you look at the `data.json` file you'll see the first example nests the landscape 4x3 path in `atoms > images` just as if they were in a real directory. You'd reference the var in your pattern as `{{ atoms.images.landscape-4x3 }}`
3. Modify the global `data.json` file in the `source` dir and use a flat naming scheme. If you look at the `data.json` file you'll see the first example just has the landscape 4x3 path at the top level. You'd reference the var in your pattern as `{{ landscape-4x3 }}`

All of these are supported "out-of-the-box." There's no need to settle on any particular format.

## The Style Guide

Is not styled properly. I'll get to it.

## IDEAS

Ok, so these are the things I want to work on cleaning up:

* I've done a simple tweak where choosing an option updates the iframe instead of reloading the page. I want to get the accordion working properly and, more importantly, I want changes made to patterns, data & styles reflected in an auto-update to the existing window. So you could "update a pattern," system generates new mark-up, the viewer re-loads the iframe and updates the pattern nav w/ a helpful "we've been updated text/color change." Hopefully this would make for a clean, iterative process.
* add a way to easily reference other templates as part of a pattern. this would add the "click-through" feature.
* not sure about the versioning... I can see a method for it but it makes PHP an absolute requirement in the short-term.
* that said, i'd also love to make this multi-device capable (e.g. update pattern on a desktop and see it show up across multiple devices). not sure how ish works in that context but it'd be cool to play around with. this would also require a server-side language choice... i think.
* this repo has to be cleaned & moved to a github organization. you have at least one image which shouldn't be here and, since it's under version control, it'll always be here even in a delete.

