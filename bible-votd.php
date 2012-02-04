<?php
/*
Plugin Name: BibleGateway VOTD
Plugin URI: http://zaikos.com/biblegateway-votd/
Description: Insert <a href="http://www.biblegateway.com/usage/">BibleGateway.com</a>'s verse of the day in pages or posts. Use the Widgets page to add the verse to your sidebar or add <strong>[bible-votd]</strong> in pages or posts where you want to insert the verse.
Version: 3.0
Author: Dave Zaikos
Author URI: http://zaikos.com/
License: GPL2
*/

/*  Copyright 2012  Dave Zaikos  (email : http://zaikos.com/contact/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'dz_biblegateway_votd' ) ) {
	/**
	 * dz_biblegateway_votd class.
	 *
	 * Insert BibleGateway.com's verse-of-the-day in pages, posts, or as a widget.
	 *
	 * @version 3.0
	 * @author Dave Zaikos
	 * @copyright Copyright (c) 2012, Dave Zaikos
	 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
	 */
	class dz_biblegateway_votd {

		/**
		 * instances
		 *
		 * Stores an array of all instances of the verse of the day used on a page load.
		 * The array contains the version abbreviations used as values.
		 *
		 * (default value: array())
		 *
		 * @var array
		 * @access private
		 * @static
		 */
		private static $instances = array();

		/**
		 * option_name
		 *
		 * The option name for the WordPress options table.
		 *
		 * @see get_option()
		 * @see update_option()
		 */
		const option_name = 'dz_biblegateway_votd';

		/**
		 * transient_name
		 *
		 * The name for the transient entry in the WordPress database. Uses the Transient API
		 * to cache remotely fetched verses.
		 *
		 * @see get_transient()
		 * @see set_transient()
		 */
		const transient_name = 'dz_biblegateway_votd_cache';

		/**
		 * shortcode_name
		 *
		 * The name of the shortcode.
		 */
		const shortcode_name = 'biblevotd';

		/**
		 * __construct function.
		 *
		 * @since 3.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			add_shortcode( self::shortcode_name, array( &$this, 'bible_votd_shortcode' ) );
		}

		/**
		 * get_available_versions function.
		 *
		 * Returns an associative array of available BibleGateway Bible versions/translations.
		 *
		 * @since 3.0
		 * @access public
		 * @static
		 * @uses get_option()
		 * @return array Associative array of available translations with keys of abbreviations and values of full names.
		 */
		static public function get_available_versions() {
			static $versions = array();

			if ( empty( $versions ) ) {
				$versions = array(
					'ASV' => 'American Standard Version',
					'AMP' => 'Amplified Bible',
					'CEB' => 'Common English Bible',
					'DARBY' => 'Darby Translation',
					'ESV' => 'English Standard Version',
					'ESVUK' => 'English Standard Version Anglicised',
					'GW' => 'GOD&#8217;S WORD Translation',
					'HCSB' => 'Holman Christian Standard Bible',
					'PHILLIPS' => 'J.B. Phillips New Testament',
					'KJV' => 'King James Version',
					'LEB' => 'Lexham English Bible',
					'NASB' => 'New American Standard Bible',
					'NIRV' => 'New International Reader&#8217;s Version',
					'NIV' => 'New International Version',
					'NIVUK' => 'New International Version - UK',
					'NLV' => 'New Life Version',
					'TNIV' => 'Today&#8217;s New International Version',
					'WE' => 'Worldwide English (New Testament)',
					'WYC' => 'Wycliffe Bible',
					'YLT' => 'Young&#8217;s Literal Translation'
					);

				// Add user-defined translations.

				$options = get_option( self::option_name );
				if ( !empty( $options['extra-versions'] ) )
					$versions = array_merge( $versions, $options['extra-versions'] );

				// Filter the list so final changes can be forced.

				$versions = (array) apply_filters( 'dz_biblegateway_versions', $versions );
			}

			return $versions;
		}

		/**
		 * is_version_available function.
		 *
		 * Checks a Bible version against the list of available versions.
		 *
		 * @access public
		 * @static
		 * @uses self::get_available_versions()
		 * @param string $version Version to check.
		 * @return string|bool The abbreviation as a string if the version is available, otherwise false.
		 */
		static public function is_version_available( $version ) {
			$available = self::get_available_versions();

			// Check if it is an abbreviation.

			$_version = strtoupper( $version );
			if ( array_key_exists( $_version, $available ) )
				return $_version;

			// Check if it is a full name. If would have to be an exact match though.

			if ( ( $_version = array_search( $version, $available ) ) )
				return $_version;

			return false;
		}

		/**
		 * bible_votd_footer_script function.
		 *
		 * Prints the necessary JavaScript in the footer to load the verse of the day.
		 *
		 * @access public
		 * @return void
		 */
		public function bible_votd_footer_script() {
			$version = array( 'NIV', 'NIV', 'ASkkkV' );
?>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function(b){var a=<?php echo json_encode( $version ); ?>;b.each(a,function(d,c){b.getJSON("http://www.biblegateway.com/votd/get?callback=?",{format:"json",version:c},function(f){if("undefined"!=typeof f.error){return true;}var e=f.votd;b("div#biblegateway-votd-"+d).html(e.text+' &#8212; <a href="'+e.permalink+'">'+e.reference+"</a>."+("undefined"!=typeof e.audiolink?' <a href="'+e.audiolink+'" title="Listen to chapter"><img width="13" height="12" src="http://www.biblegateway.com/resources/audio/images/sound.gif" alt="Listen to chapter" /></a>':"")+' <a href="'+e.copyrightlink+'">'+e.copyright+'</a>. Powered by <a href="http://www.biblegateway.com/">BibleGateway.com</a>.');});});});
/* ]]> */
</script>
<?php
		}

		/**
		 * get_cached_html_code function.
		 *
		 * Returns the cached verse of the day.
		 *
		 * @access private
		 * @return string The HTML code with the verse.
		 */
		private function get_cached_html_code() {
			return $this->get_basic_html_code();
		}

		/**
		 * get_jquery_html_code function.
		 *
		 * Returns jQuery JavaScript code for embedding the verse of the day on when the DOM is completely loaded.
		 *
		 * @access private
		 * @return string The HTML code to insert the verse.
		 */
		private function get_jquery_html_code() {
			return '<div id="biblegateway-votd-%3$d">Loading <a href="http://www.biblegateway.com/">BibleGateway.com</a>&#8217;s verse of the day&#8230;</div>';
		}

		/**
		 * get_basic_html_code function.
		 *
		 * Returns the basic BibleGateway.com HTML/JavaScript code in a sprintf-ready format.
		 * The sprintf call should use %1$s for the version.
		 *
		 * This is the code BibleGateway.com provides but it should only ever be used as a last
		 * resort as it can significantly slow page loading.
		 *
		 * @access private
		 * @return string The HTML code to insert the verse.
		 */
		private function get_basic_html_code() {
			return '<script type="text/javascript" language="JavaScript" src="http://www.biblegateway.com/votd/votd.write.callback.js"></script>
<script type="text/javascript" language="JavaScript" src="http://www.biblegateway.com/votd/get?format=json&amp;version=%1$s&amp;callback=BG.votdWriteCallback"></script>
<noscript><iframe framespacing="0" frameborder="no" src="http://www.biblegateway.com/votd/get?format=html&amp;version=%1$s">View Verse of the Day</iframe></noscript>';
		}

		/**
		 * bible_votd_code_helper function.
		 *
		 * Determines the method of adding the verse of the day and returns the necessary
		 * code for the calling function to use.
		 *
		 * @access private
		 * @return string The sprintf-ready code to be inserted into the page.
		 */
		private function bible_votd_code_helper() {
			$options = get_option( self::option_name );
			$method = ( isset( $options['embed-method'] ) ) ? $options['embed-method'] : 'jquery';

			switch( $method ) {
				case 'cache':
					if ( get_transient( self::transient_name ) )
						return $this->get_cached_html_code();

				case 'jquery':
					wp_enqueue_script( 'jquery' );
					add_action( 'wp_print_footer_scripts', array( &$this, 'bible_votd_footer_script' ) );
					return $this->get_jquery_html_code();

				case 'basic':
				default:
					return $this->get_basic_html_code();
			}
		}

		/**
		 * bible_votd_shortcode function.
		 *
		 * The function handler for WordPress's shortcode API. This does the work of inserting
		 * the verse of the day in a page or post.
		 *
		 * @access public
		 * @param mixed $atts
		 * @return string The Bible verse of the day.
		 */
		public function bible_votd_shortcode( $atts ) {
			extract( shortcode_atts( array(
				'version' => null,
				'class' => 'biblevotd'
				), $atts ) );

			// Validate user-provided values.

			if ( !( $version = $this->is_version_available( $version ) ) ) {
				$defaults = get_option( self::option_name );
				$version = ( isset( $defaults['default-version'] ) ) ? $defaults['default-version'] : 'NIV';
			}

			$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $class ) ) );

			// Get and update instance.

			$this->instance[] = $version;
			end( $this->instance );
			$instance = key( $this->instance );

			// Build the code.

			$votd = "\n<!-- BibleGateway.com Verse of the Day plugin by Dave Zaikos (http://zaikos.com/biblegateway-votd/). -->\n"; /* Luke 6:31. Please do not remove the credit. Thank you! */
			$votd .= "<div id='dz-biblevotd-%3\$d' class='%2\$s'>\n";
			$votd .= "\t" . $this->bible_votd_code_helper() . "\n";
			$votd .= "</div>\n";

			$votd = sprintf( $votd, $version, $class, $instance );

			// Allow other plugins and themes to filter the final verse.

			$votd = apply_filters( 'pre_dz_biblegateway_verse', $votd, $version, $class, $instance );

			return $votd;
		}

	}

	if ( !class_exists( 'dz_biblegateway_votd_widget' ) ) {
		/**
		 * dz_biblegateway_votd_widget class.
		 *
		 * Adds a widget for the BibleGateway verse of the day.
		 *
		 * @extends WP_Widget
		 */
		class dz_biblegateway_votd_widget extends WP_Widget {

			public function __construct() {
				parent::__construct( 'dz_biblegateway_votd', 'Bible VOTD', array( 'description' => "BibleGateway.com's verse of the day." ) );
			}

			public function widget( $args, $instance ) {
				extract( $args, EXTR_SKIP );
				extract( $instance, EXTR_SKIP );

				echo $before_widget;

				if ( !empty( $title ) ) {
					echo $before_title . esc_html( $title ) . $after_title;
				}

				echo do_shortcode( '[' . dz_biblegateway_votd::shortcode_name . ' version=' . $version . ']' );

				echo $after_widget;
			}

			public function update( $new_instance, $old_instance ) {

				// Sanitize the title.

				$instance['title'] = wp_strip_all_tags( $new_instance['title'], true );

				// Sanitize the version.

				if ( !( $instance['version'] = dz_biblegateway_votd::is_version_available( $new_instance['version'] ) ) ) {
					if ( !( $instance['version'] = dz_biblegateway_votd::is_version_available( $old_instance['version'] ) ) ) {
						$options = get_option( dz_biblegateway_votd::option_name );
						$instance['version'] = ( isset( $options['default-version'] ) ) ? $options['default-version'] : 'NIV';
					}
				}

				// Return.

				return $instance;
			}

			public function form( $instance ) {

				// Get default options.

				$options = get_option( dz_biblegateway_votd::option_name );
				extract( $instance, EXTR_SKIP );

				// Validate title.

				if  ( empty( $title ) )
					$title = 'Verse of the Day';

				// Validate selected version.

				if ( empty( $version ) )
					$version = ( isset( $options['default-version'] ) ) ? $options['default-version'] : 'NIV';

				// Build the form.

				$versions = dz_biblegateway_votd::get_available_versions();
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'version' ); ?>"><?php _e( 'Version:' ); ?></label>
	<select id="<?php echo $this->get_field_id( 'version' ); ?>" name="<?php echo $this->get_field_name( 'version' ); ?>">
<?php
				foreach ( $versions as $abbr => $desc ) {
					$selected = selected( $abbr, $version, false );
					printf ( "\t<option value='%1\$s'%2\$s>%3\$s</option>\n", esc_attr( $abbr ), $selected, esc_attr( $desc ) );
				}
?>
	</select>
</p>
<?php
			}

		}

	}

	if ( !isset( $plugin_dz_biblegateway_votd ) ) {
		$plugin_dz_biblegateway_votd = new dz_biblegateway_votd();
		add_action( 'widgets_init', create_function( '', 'register_widget( "dz_biblegateway_votd_widget" );' ) );
	}

	if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) )
		include plugin_dir_path( __FILE__ ) . 'bible-votd-admin.php';
}


/* !Legacy code begins here. */

// Adds compatibility for < WP 2.8.

if ( !function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return attribute_escape( $text );
	}
}

// Adds compatibility for < WP 2.9.

if ( !function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $string ) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags( $string );
	    $string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
		return trim( $string );
	}
}

// Create class if it doesn't exist.

if ( !class_exists( 'BibleGatewayVOTD_Plugin' ) ) {
	class BibleGatewayVOTD_Plugin {

		// JavaScript from BibleGateway.com site for embedding the verse of the day.

		var $js_code = '<SCRIPT language=JavaScript src="http://www.biblegateway.com/usage/votd/votd2html.php?version=%s&amp;jscript=1" type=text/javascript></SCRIPT><!-- alternative for no javascript --><NOSCRIPT><a href="http://www.biblegateway.com/usage/votd/votd2html.php?version=%1$s&amp;jscript=0">View Verse of the Day</a></NOSCRIPT>';

		// Class constructor. Followed by PHP 4 compatible constructor.

		function __construct() {
			register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );

			add_shortcode( 'bible-votd', array( $this, 'BibleVOTD' ) );

			wp_register_sidebar_widget( 'biblegateway-votd', 'BibleGateway VOTD', array( $this, 'BibleVOTD_widget' ) );
			register_widget_control( array( 'BibleGateway VOTD', 'widgets' ), array( $this, 'BibleVOTD_widget_control' ) );
		}

		function BibleGatewayVOTD_Plugin() {
			$this->__construct();
		}

		// Activation hook. Handles update from plugin versions earlier than 2.3.

		function plugin_activation() {
			if ( !get_option( 'dz_biblevotd' ) )
				return;

			$options = maybe_unserialize( get_option( 'dz_biblevotd' ) );
			$options = array_merge( $this->get_option_defaults(), (array)$options );

			add_option( 'biblegateway_votd', $options, '', 'yes' );

			if ( get_option( 'biblegateway_votd' ) === $options )
				delete_option( 'dz_biblevotd' );
		}

		// Default options for the plugin.

		function get_option_defaults() {
			$options = array(
				'plugin_version' => '2.3',
				'biblevotd-title' => 'Verse of the Day',
				'ver' => 31
				);

			return $options;
		}

		function BibleVOTD( $atts ) {

			// Load default settings from the widget.

			if ( !( $options = get_option( 'biblegateway_votd' ) ) )
				$options = $this->get_option_defaults();

			// Get the Bible version and CSS class if specified.

			extract( shortcode_atts( array(
				'ver' => $options['ver'],
				'class' => 'biblevotd'
				), $atts ) );

			// Prepare BibleGateway.com's HTML code.

			$votd = sprintf( $this->js_code, esc_attr( $ver ) );

			// Add CSS class.

			$votd = sprintf( '<div class="%s">%s</div>', esc_attr( $class ), $votd );

			// Return output.

			return $votd;
		}

		function BibleVOTD_widget( $args ) {
			extract( $args );

			// Load default settings from the widget.

			if ( !( $options = get_option( 'biblegateway_votd' ) ) )
				$options = $this->get_option_defaults();

			// The widget.

			$votd = '<!-- BibleGateway Verse-of-the-Day widget. http://www.zaikos.com/coding/wp-plugins/biblegateway-votd/ -->';
			$votd .= $before_widget;
			$votd .= $before_title . wptexturize( esc_attr( $options['biblevotd-title'] ) ) . $after_title;
			$votd .= sprintf( $this->js_code, esc_attr( $options['ver'] ) );
			$votd .= $after_widget;

			// Print to the screen.

			echo $votd;
		}

		function BibleVOTD_widget_control() {

			// Load default settings for the widget.

			if ( !( $options = get_option( 'biblegateway_votd' ) ) )
				$options = $this->get_option_defaults();

			// Setup array of supported English translations. See http://www.biblegateway.com/usage/votd/custom_votd.php

			$translations = array(
				31 => "New International Version",
				49 => "New American Standard Bible",
				45 => "Amplified Bible",
				9 => "King James Version",
				47 => "English Standard Version",
				8 => "American Standard Version<",
				15 => "Young's Literal Translation",
				16 => "Darby Translation",
				76 => "New International Reader's Version",
				64 => "New International Version - UK",
				72 => "Today's New International Version"
				);

			// Sanitize and save form submission.

			if ( isset( $_POST['biblegateway-votd-title'], $_POST['biblegateway-votd-version'] ) ) {
				if ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() )
					$title = stripslashes( $_POST['biblegateway-votd-title'] );
				else
					$title = $_POST['biblegateway-votd-title'];

				$title = sanitize_text_field( $title, true );
				$version = absint( $_POST['biblegateway-votd-version'] );

				if ( !empty( $title ) )
					$options['biblevotd-title'] = $title;

				if ( array_key_exists( $version, $translations ) )
					$options['ver'] = $version;

				//  Save options.

				if ( get_option( 'biblegateway_votd' ) )
					update_option( 'biblegateway_votd', $options );
				else
					add_option( 'biblegateway_votd', $options, '', 'yes' );
			}

			// The form.
?>
<p>
	<label for="biblevotd-title"><?php _e( 'Title' ); ?>:
		<input class="widefat" name="biblegateway-votd-title" type="text" maxlength="50" value="<?php echo esc_attr( $options['biblevotd-title'] ); ?>">
	</label>
</p>
<p>
	<label for="ver">Version:
		<select class="widefat" name="biblegateway-votd-version">
<?php
			foreach ( $translations as $num => $version ) {
				( $options['ver'] === $num ) ? $selected = " selected='selected'" : $selected = "";
				echo "\t\t\t\t\t\t\t<option value='$num'$selected>$version</option>\n";
			}
?>		</select>
	</label>
</p>
<?php
		}

	} // End of BibleGatewayVOTD_Plugin() class.
} // End of BibleGatewayVOTD_Plugin() if.

// Initialize class.

//if ( class_exists( 'BibleGatewayVOTD_Plugin' ) || !isset( $bgvotd_plugin ) )
//	$bgvotd_plugin = new BibleGatewayVOTD_Plugin();