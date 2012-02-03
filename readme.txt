=== Plugin Name ===
Contributors: Dave Zaikos
Donate link: http://zaikos.com/donate/
Tags: bible, biblegateway, verse of the day, votd, niv, kjv, cev, esv
Requires at least: 2.9
Tested up to: 3.3.1
Stable tag: 2.3

Adds BibleGateway.com's verse of the day as a sidebar widget, or on page or post.

== Description ==

Adds [BibleGateway.com](http://www.biblegateway.com/)'s verse of the day as a sidebar widget. This plugin will also allow you to include the verse of the day in a page or post using a shortcode: `[bible-votd]`. Different Bible translations can be selected from the widget's page or as options in the shortcode.

The plugin defaults to using the NIV translation. You can specify other translations in the widget settings or with the "ver" option in the shortcode. For example, to use the KJV in a page or post, write:

`[bible-votd ver="9"]`

== Installation ==

1. Upload the `bible-votd.php` file to the `/wp-content/plugins/` directory.
2. Activate the plugin in the 'Plugins' page in WordPress.
3. Add the BibleGateway VOTD widget to your sidebar. Or,
4. Type `[bible-votd]` in the page or post content where you want the verse to appear.

== Frequently Asked Questions ==

= Why does using the plugin cause page load times to increase? =

The plugin takes the JavaScript provided by BibleGateway.com for inserting the verse of the day and includes that when you use the widget or shortcode. If BibleGateway.com servers are running slow your visitor's web browser will wait for BibleGateway.com to respond with the verse (or timeout) before it finishes loading the page.

This issue is common when inserting content from another site (e.g. BibleGateway.com, Twitter.com, etc.). You can work around the issue by placing the widget or shortcode as close the end of your page as possible.

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

= 3.0 =

* Complete rewrite.
* Added option to select default Bible version/translation.
* Added option to add versions/translations from BibleGateway.com that are not bundled with the plugin (all English versions available at the time of this release are bundled; however, you can easily add more versions from the Options page).
* Added internal documentation for clarity.
* Added filter for themes or other plugins to modify available translations.
* Added filter for themes or other plugins to modify the final output for the verse.
* Added fallback option to display basic BibleGateway.com HTML/JavaScript code to insert the verse (basically what BibleGateway.com has on their web site). This is slower so it is not used by default.

= 2.3 =

* Code optimizations.
* Added compatibility for WordPress versions as early as 2.5 and ensured PHP 4 and 5 compatibility.

= 2.2 =

* First public release on the WordPress Plugins directory.
