=== Plugin Name ===
Contributors: Dave Zaikos
Donate link: http://zaikos.com/donate/
Tags: bible, biblegateway, verse of the day, votd, niv, kjv, esv
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 2.3

Adds BibleGateway.com's verse of the day as a sidebar widget, or on page or post.

== Description ==

Adds [BibleGateway.com](http://www.biblegateway.com/)'s verse of the day as a sidebar widget or inside a page or post using a the shortcode `[bible-votd]`. Different Bible translations can be selected from the widget's page or as options in the shortcode.

The plugin defaults to using the NIV translation. You can specify other translations in the widget settings or with the "version" option in the shortcode. For example, to use the KJV in a page or post, write:

`[bible-votd ver="KJV"]`

== Installation ==

1. Upload the `biblegateway-votd` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin in the 'Plugins' page in WordPress.
3. Add the Bible VOTD widget to your sidebar. Or,
4. Type `[bible-votd]` in the page or post content where you want the verse to appear.

== Frequently Asked Questions ==

= What versions are for supported when including the verse? =

* ASV = American Standard Version
* AMP = Amplified Bible
* CEB = Common English Bible
* DARBY = Darby Translation
* ESV = English Standard Version
* ESVUK = English Standard Version Anglicised
* GW = GOD'S WORD Translation
* HCSB = Holman Christian Standard Bible
* PHILLIPS = J.B. Phillips New Testament
* KJV = King James Version
* LEB = Lexham English Bible
* NASB = New American Standard Bible
* NIRV = New International Reader's Version
* NIV = New International Version
* NIVUK = New International Version - UK
* NLV = New Life Version
* TNIV = Today's New International Version
* WE = Worldwide English (New Testament)
* WYC = Wycliffe Bible
* YLT = Young's Literal Translation

= What if I want a version not listed? =

BibleGateway.com has a list of available versions for their verse of the day. You can view that page to find the version you want. Additional versions can be added from the plugin's setting page, then used by the shortcode or widget. If BibleGateway makes it available, you can add it and use it!

= Why does using the plugin cause page load times to increase? =

This issue is common when inserting content from another site (e.g. BibleGateway.com, Twitter.com, etc.). The delay is caused when BibleGateway.com is slow to provide the verse; as a result, your page halts until BibleGateway.com either responds or times out. By default the plugin works around this issue, so you should not experience any delays. If you are, make sure you are using the `jQuery` or `Cache` methods for inserting the verse. These methods can be selected from the Bible VOTD settings page and allow your web site page to load without being slowed down by using enhanced JavaScript loading or advanced caching techniques.

== Changelog ==

= 3.0 =

* Complete rewrite. If you have been using this plugin as a widget, you will have to re-add the widget to your sidebar(s) after updating.
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
