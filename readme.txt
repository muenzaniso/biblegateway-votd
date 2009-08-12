=== Plugin Name ===
Contributors: Dave Zaikos
Donate link: http://www.zaikos.com/blog/#support-this-site
Tags: bible, biblegateway, verse of the day, votd, niv, kjv
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 2.2

Adds BibleGateway.com's verse of the day as a sidebar widget, or on page or post.

== Description ==

Adds [BibleGateway.com](http://www.biblegateway.com/)'s verse of the day as a sidebar widget.  This plugin will also allow you to include the verse of the day in a page or post using a shortcode: `[bible-votd]`. Different Bible translations can be selected from the widget's page or as options in the shortcode.

The plugin defaults to using the NIV translation. You can specify other translations in the widget settings or with the "ver" option in the shortcode. For example, to use the KJV in a page or post, write:

`[bible-votd ver="9"]`

== Installation ==

1. Upload the `bible-votd.php` file to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the Bible VOTD widget to your sidebar.  Or,
4. Put `[bible-votd]` in the page or post content where you want the verse to appear.

== Frequently Asked Questions ==

= What are the version numbers for supported translations when including the verse in a page or post? =

* 8 = American Standard Version
* 45 = Amplified Bible
* 16 = Darby Translation
* 47 = English Standard Version
* 9 = King James Version
* 49 = New American Standard Bible
* 76 = New International Reader's Version
* 31 = New International Version
* 64 = New International Version - UK
* 72 = Today's New International Version
* 15 = Young's Literal Translation

= Does changing the version in the widget affect the default version used in pages or posts? =

Yes. The initial default is the New International Version (#31); however, when you change the version in the widget sidebar, the selected translation now becomes the default used for the shortcode in pages and posts.

== Changelog ==

= 2.2 =

* First public release on the WordPress Plugins directory.
