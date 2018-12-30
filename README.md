# YouTube oEmbed Lazy Load
This WordPress plugin replaces the default YouTube embed with a still (image) from the video and a play button instead of embedding the whole iframe. Only the image will be downloaded instead of the entire YouTube iframe (around 600kB). This makes your page render a lot faster which is especially important on mobile devices. When the play button is clicked the iframe will be loaded and the video wil start auto-playing (for mobile, see [known issues](#known-issues)).

## Requirements
* PHP >= 5.6 with php-xml module.
* WordPress with the classic-editor plugin installed and activated.

## Installation
Using composer simply execute:
```composer require 'roy-bongers/youtube-oembed-lazy-load:*'```

## Known issues
### Gutenberg support
The plugin only works with the [Classic editor](https://wordpress.org/plugins/classic-editor/) and not with the new Gutenberg editor. I'm not planning to add support for Gutenberg. If you want to add support for Gutenberg please fork the project.

### Autoplay on mobile
Google Chrome and other browsers are blocking autoplay on mobile devices unless they are muted. The plugin tries to play the video with audio. If this fails the video will start muted.
