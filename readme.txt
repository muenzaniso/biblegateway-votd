=== Plugin Name ===
Contributors: Dave Zaikos
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UUKC8GMRJUJTS
Tags: bible, gateway, verse of the day, votd, biblegateway, niv, kjv, esv
Requires at least: 3.3
Tested up to: 4.2.2
Stable tag: 3.4

Adds BibleGateway.com's verse of the day as a sidebar widget, or on a page or post.

== Description ==

Adds [BibleGateway.com](http://www.biblegateway.com/)'s verse of the day as a sidebar widget, or inside a page or post using the shortcode `[biblevotd]`. Different Bible translations can be selected from the widget's page or as options in the shortcode.

The plugin defaults to using the NIV translation. You can specify other translations in the widget settings or with the "version" option in the shortcode. For example, to use the KJV in a page or post, write:

`[biblevotd version="KJV"]`

Version 3.0 of this plugin is a complete rewrite. It leverages several new methods for retrieving the verse to prevent any delays when visitors load your site. The plugin will attempt to retrieve and cache the verse behind the scenes. If it is available it will directly serve the cache. If it is not, it will fallback to using the jQuery JavaScript library. This includes the JavaScript code necessary to insert the verse but not execute any code until the entire page on your site has finished loading (allowing your site load uninterrupted, regardless of any delays from BibleGateway.com).

You can insert multiple verses of the day (in different translations) across your web site.

== Installation ==

1. Upload the `biblegateway-votd` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin in the 'Plugins' page in WordPress.
3. Optionally set your defaults from the settings page.
4. Add the Bible VOTD widget to your sidebar. Or,
5. Type `[biblevotd]` in the page or post content where you want the verse to appear.

== Frequently Asked Questions ==

= What translations are for supported when including the verse? =

* 1599 Geneva Bible (GNV)
* 21st Century King James Version (KJ21)
* American Standard Version (ASV)
* Amplified Bible (AMP)
* Authorized (King James) Version (AKJV)
* BRG Bible (BRG)
* Common English Bible (CEB)
* Complete Jewish Bible (CJB)
* Contemporary English Version (CEV)
* Darby Translation (DARBY)
* Disciples' Literal New Testament (DLNT)
* Douay-Rheims 1899 American Edition (DRA)
* Easy-to-Read Version (ERV)
* English Standard Version (ESV)
* English Standard Version Anglicised (ESVUK)
* Expanded Bible (EXB)
* GOD'S WORD Translation (GW)
* Good News Translation (GNT)
* Holman Christian Standard Bible (HCSB)
* International Children's Bible (ICB)
* International Standard Version (ISV)
* J.B. Phillips New Testament (PHILLIPS)
* Jubilee Bible 2000 (JUB)
* King James Version (KJV)
* Lexham English Bible (LEB)
* Living Bible (TLB)
* Modern English Version (MEV)
* Mounce Reverse-Interlinear New Testament (MOUNCE)
* Names of God Bible (NOG)
* New American Bible (Revised Edition) (NABRE)
* New American Standard Bible (NASB)
* New Century Version (NCV)
* New English Translation (NET Bible)
* New International Reader's Version (NIRV)
* New International Version (NIV)
* New International Version - UK (NIVUK)
* New King James Version (NKJV)
* New Life Version (NLV)
* New Living Translation (NLT)
* New Revised Standard Version (NRSV)
* New Revised Standard Version Catholic Edition (NRSVCE)
* New Revised Standard Version, Anglicised (NRSVA)
* New Revised Standard Version, Anglicised Catholic Edition (NRSVACE)
* Orthodox Jewish Bible (OJB)
* Revised Standard Version (RSV)
* Revised Standard Version Catholic Edition (RSVCE)
* The Message (MSG)
* The Voice (VOICE)
* World English Bible (WEB)
* Worldwide English (New Testament) (WE)
* Wycliffe Bible (WYC)
* Young's Literal Translation (YLT)

= What if I want a translation not listed above? =

BibleGateway.com [has a list](http://www.biblegateway.com/usage/votd/custom_votd.php) of available translations for their verse of the day. You can view that page to find the translation you want. Additional translations can be added from the plugin's settings page, then used by the shortcode or widget. If BibleGateway.com makes it available, you can add it and use it!

= I want to format the way the verse appears on my site. How can I do that? =

The plugin will wrap the verse in a CSS class named `biblevotd`. You can use that to add CSS instructions to your theme's style.css file to provide specific formatting. If you want to use a class name other than "biblevotd" you can simply pass `class="classname"` as an option when using the shortcode (for example, `[biblevotd class="dailyverse"]`). Widgets are automatically provided with additional CSS class names by WordPress and can be uniquely formatted using those.

= I need more than just CSS to change the way the verse appears. What can I do? =

Directly before printing the verse code the plugin passes the information through the `pre_dz_biblegateway_verse` filter. You can add code to your theme's functions.php file to hook into this filter and update the content accordingly. Be aware that sometimes the content will be the verse (if it was cached), or JavaScript (if using jQuery).

= Why does using the plugin cause page load times to increase? =

As of version 3.0 this should no longer be an issue. This issue is common when inserting content from another site (e.g. BibleGateway.com, Twitter.com, etc.). The delay is caused when BibleGateway.com is slow to provide the verse; as a result, your page halts until BibleGateway.com either responds or times out. Since version 3.0 the plugin will by default work around this issue, so you should not experience any delays. If you are, make sure you are using the jQuery or Cache methods for inserting the verse. These methods can be selected from the Bible VOTD settings page and allow your web site page to load without being slowed down by using enhanced JavaScript loading or caching techniques.

= Which embed method should I use: Cache, jQuery, or Basic? =

You should use Cache; it is the default setting. The plugin is intelligent in that it will continue to fallback to the next method, in order. Selecting Cache will give your site the best opportunity to provide the verse of the day without delays in page loading.

The only time you should use jQuery is if your web server is not able to fetch remote URLs. How do you know if it can? Simple: If you are able to update plugins, themes, and the WordPress core from within WordPress without issue, you can fetch remote URLs. If you cannot, you should really look into fixing that. :) But also, you should select jQuery as the embed method because there's no sense in your web server trying to cache something all the time when it is simply not able to.

The Basic method should only be used if Cache and jQuery are not working. It uses the identical code provided by BibleGateway.com to insert the verse. This has limitations: It can slow page loading if BibleGateway.com is running slow. The Basic method is only provided in case BibleGateway.com significantly alters their delivery scheme and that results in the other two methods breaking. Switching to Basic should allow you to continue to use the plugin until it is updated.

== Screenshots ==

1. The settings page where you can configure the default translation as well as which translations to cache.
2. The widgets page. You can add as many widgets with different translations as your theme will allow.
3. A sample of the verse of the day being used in a shortcode on a page with the Twenty Eleven theme.

== Changelog ==

= 3.4 =

* Worked around possible T_PAAMAYIM_NEKUDOTAYIM PHP fatal error.

= 3.3 =

* Fixed an error that caused the audio/speaker link to appear even if playback was not available for the chosen translation. (Previously only fixed this when using the Cache method, now also fixed when using jQuery.)

= 3.2 =

* Fixed an error that caused the audio/speaker link to appear even if playback was not available for the chosen translation.
* Updated translations list in FAQs.

= 3.1 =

* Updated available English translations.
* Fixed some PHP strict errors.
* Minor tweaks to some reference URLs.

= 3.0 =

* Complete rewrite.
* Added option to select default Bible version/translation.
* Added option to add versions/translations from BibleGateway.com that are not bundled with the plugin (all English translations available at the time of this release are bundled; however, you can easily add more translations from the options page).
* Added internal documentation for clarity.
* Added filter for themes or other plugins to modify available translations.
* Added filter for themes or other plugins to modify the final output for the verse.
* Added fallback option to display basic BibleGateway.com HTML/JavaScript code to insert the verse (basically what BibleGateway.com has on their web site). This is slower so it is not used by default.

= 2.3 =

* Code optimizations.
* Added compatibility for WordPress versions as early as 2.5 and ensured PHP 4 and 5 compatibility.

= 2.2 =

* First public release on the WordPress Plugins directory.

== Upgrade Notice ==

A complete rewrite of the plugin. Please see the Changelog for full details.

Note the usage for the shortcode has changed. In previous versions the shortcode was `[bible-votd]` and now it is `[biblevotd]`. Additionally, specifying a translation used to be done with the `ver` option and is now done with the `version` option. For example, to print the King James Version using the shortcode you previous typed `[bible-votd ver="9"]`. Now you type `[biblevotd version="KJV"]`.

If you have been using a widget, you will have to re-add the widget to your sidebar(s) after updating. This is necessary due to a significant change in the underlying code. However, the positive side of this is now you can add multiple widgets and have different translations for the day's verse.
