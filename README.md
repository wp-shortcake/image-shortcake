# Image-shortcake #
**Contributors:** fusionengineering  
**Donate link:** http://example.com/  
**Tags:** comments, spam  
**Requires at least:** 3.0.1  
**Tested up to:** 3.4  
**Stable tag:** 4.3  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Image Shortcake adds a shortcode for images, so that themes can template and
filter images displayed in posts. It is designed to work with the UI provided
by the [Shortcake (Shortcode UI)](https://github.com/fusioneng/Shortcake)
plugin,

## Description ##

When images are inserted into posts from the media library or media uploader,
only the html of the `<img>` tag and the link around it (if any) are preserved.
This means that themes which want to change the way images are marked up in
content don't have an easy way of doing this.

Image Shortcake is an attempt to solve this problem, by saving images in post
content as _shortcodes_ rather than HTML. The output of shortcodes can be
easily filtered in themes, plugins and templates, and since the original
attachment data is preseved as attributes on the shortcode, it becomes much
easier for modify the way images are marked up in themes.

For best results, use with [Shortcake (Shortcode UI)](https://github.com/fusioneng/Shortcake) plugin.

## Changelog ##

### 0.1 ###
Initial release (May 1, 2015)

