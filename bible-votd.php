<?php
/*
Plugin Name: BibleGateway VOTD
Plugin URI: http://zaikos.com/biblegateway-votd/
Description: Insert <a href="http://www.biblegateway.com/usage/">BibleGateway.com</a>'s verse-of-the-day in pages or posts. Use the Widgets page to add the verse to your sidebar or add <strong>[bible-votd]</strong> in pages or posts where you want to insert the verse.
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
		static private $instances = array();

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
		 * __construct function.
		 *
		 * @since 3.0
		 * @access private
		 * @return void
		 */
		private function __construct() {
//			add_action( '', array( &$this, '' ) );
//			add_filter( '', array( &$this, '' ) );
			add_shortcode( 'biblevotd', array( &$this, 'bible_votd_shortcode' ) );
		}

		/**
		 * get function.
		 *
		 * Retrieves from the WordPress options table for this plugin the specific option value, or its default value.
		 *
		 * @access private
		 * @uses get_option()
		 * @param string $var
		 * @param bool $default (default: false)
		 * @return mixed The value of the specified option or false if nothing was found.
		 */
		private function get( $var, $default = false ) {
			$options = (array) get_option( self::option_name, array() );
			if ( isset( $options[$var] ) && !$default )
				return $options[$var];

			switch( $var ) {
				case 'default-version':
					return 'NIV';
			}


			return false;
		}

		/**
		 * set function.
		 *
		 * Updates the specific option for this plugin in the WordPress options table.
		 *
		 * This function, along with self::get(), wrap options in an array so the plugin only uses one
		 * row in the database table (i.e. neat and tidy).
		 *
		 * @access private
		 * @see self::get()
		 * @uses update_option()
		 * @param mixed $option
		 * @return bool False if value was not updated and true if value was updated.
		 */
		private function set( $var, $value ) {
			$options = (array) get_option( self::option_name, array() );

			$options[$var] = $value;

			return update_option( self::option_name, $options );
		}

		/**
		 * new_instance function.
		 *
		 * Keeps track of the number of times a verse has been inserted in a page and each instances
		 * corresponding version.
		 *
		 * @access public
		 * @static
		 * @param mixed $version A valid Bible version.
		 * @return int|bool The numeric instance of the inserted version. False if the passed version is not valid.
		 */
		static public function new_instance( $version ) {
			if ( $_version = $this->is_version_available( $version ) ) {
				$this->instances[] = $_version;
				end( $this->instances );
				return key( $this->instances );
			}

			return false;
		}

		/**
		 * get_available_versions function.
		 *
		 * Returns an associative array of available BibleGateway Bible versions/translations.
		 *
		 * @since 3.0
		 * @access public
		 * @static
		 * @return array Associative array of available translations with keys of abbreviations and values of full names.
		 */
		static public function get_available_versions() {
			static $versions = array();

			if ( empty( $versions ) ) {
				$versions = array(
					'KJ21' => '21st Century King James Version',
					'ASV' => 'American Standard Version',
					'AMP' => 'Amplified Bible',
					'CEB' => 'Common English Bible',
					'CEV' => 'Contemporary English Version',
					'DARBY' => 'Darby Translation',
					'DRA' => 'Douay-Rheims 1899 American Edition',
					'ERV' => 'Easy-to-Read Version',
					'ESV' => 'English Standard Version',
					'ESVUK' => 'English Standard Version Anglicised',
					'GW' => 'GOD\'S WORD Translation',
					'GNT' => 'Good News Translation',
					'HCSB' => 'Holman Christian Standard Bible',
					'PHILLIPS' => 'J.B. Phillips New Testament',
					'KJV' => 'King James Version',
					'LEB' => 'Lexham English Bible',
					'MSG' => 'The Message',
					'NASB' => 'New American Standard Bible',
					'NCV' => 'New Century Version',
					'NIRV' => 'New International Reader\'s Version',
					'NIV' => 'New International Version',
					'NIV1984' => 'New International Version 1984',
					'NIVUK' => 'New International Version - UK',
					'NKJV' => 'New King James Version',
					'NLV' => 'New Life Version',
					'NLT' => 'New Living Translation',
					'TNIV' => 'Today\'s New International Version',
					'WE' => 'Worldwide English (New Testament)',
					'WYC' => 'Wycliffe Bible',
					'YLT' => 'Young\'s Literal Translation'
					);

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
			$available = $this->get_available_versions();

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
		 * print_basic_html_code function.
		 *
		 * Prints the basic BibleGateway.com HTML/JavaScript code.
		 *
		 * This is the code BibleGateway.com provides but it should only ever be used as a last
		 * resort as it can significantly slow page loading.
		 *
		 * @access public
		 * @param string $version (default: 'NIV')
		 * @return void
		 */
		public function print_basic_html_code( $version = 'NIV' ) {
			if ( $version = $this->is_version_available( $version ) ) {
				printf( '<script type="text/javascript" language="JavaScript" src="http://www.biblegateway.com/votd/votd.write.callback.js"></script>
<script type="text/javascript" language="JavaScript" src="http://www.biblegateway.com/votd/get?format=json&amp;version=%1$s&amp;callback=BG.votdWriteCallback"></script>
<noscript><iframe framespacing="0" frameborder="no" src="http://www.biblegateway.com/votd/get?format=html&amp;version=%1$s">View Verse of the Day</iframe></noscript>
',
					esc_attr( $version ) );
			}
		}

		/**
		 * bible_votd_helper function.
		 *
		 * Determines the method of adding the verse of the day and returns the necessary
		 * code for the calling function to use.
		 *
		 * @access private
		 * @return string The sprintf-ready code to be inserted into the page.
		 */
		private function bible_votd_helper() {


		}

		/**
		 * bible_votd_shortcode function.
		 *
		 * The function handler for WordPress's shortcode API. This does the work of inserting
		 * the verse of the day in a page or post.
		 *
		 * @access private
		 * @param mixed $atts
		 * @return string The Bible verse of the day.
		 */
		private function bible_votd_shortcode( $atts ) {
			extract( shortcode_atts( array(
				'version' => null,
				'class' => 'biblevotd'
				), $atts ) );

			// Validate user-provided values.

			if ( !$this->is_version_available( $version ) )
				$version = $this->get( 'default-version' );

			$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $class ) ) );

			// Build the code.

			$votd = $this->bible_votd_helper();
			$votd = sprintf( '<div id="biblevotd-%3$d" class="%2$s">' . $votd . "</div>\n" , $version, $class, $instance );

			// Allow other plugins and themes to filter the final.

			$votd = apply_filters( 'dz_biblegateway_versions', $votd, $version, $class, $instance );

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

			public $classname = 'widget_cmo_recent'; // CSS class for widget.
			public $defaults = array( 'title' => '', 'number' => 4, 'thumbnails' => false ); // Default widget settings.

			function __construct() {
				parent::__construct( 'cmo_recent_posts_widget', 'Sticky or Recent Posts', array( 'classname' => $this->classname ) );
				add_action( 'cmo_recent_posts_widget', array( &$this, 'cmo_recent_posts_action' ), 10, 2 );
			}

			function widget( $args, $instance ) {
				extract( $instance, EXTR_SKIP );

				// If is_single, do not include the current post that is being viewed.

				$query_args = array( 'posts_per_page' => $number, 'post_type' => 'post' );
				if ( is_single() ) {
					global $post;
					$query_args['post__not_in'] = array( $post->ID );
				}

				if  ( apply_filters_ref_array( 'cmo_sticky_or_recent_posts_wp_query', array( true, &$recent, $query_args, $thumbnails ) ) )
					return;

				extract( $args, EXTR_SKIP );

				echo $before_widget;

				if ( !empty( $title ) ) {
					$title = apply_filters( 'cmo_widget_recent_title', $title, $instance );
					echo $before_title . $title . $after_title;
				}

				$this->posts_walker( &$recent, $thumbnails );

				echo $after_widget;

				wp_reset_query();
			}

			function update( $new_instance, $old_instance ) {
				$instance['title'] = sanitize_text_field( $new_instance['title'] );

				$instance['number'] = absint( $new_instance['number'] );
				if ( 1 > $instance['number'] )
					$instance['number'] = $old_instance['number'];

				$instance['thumbnails'] = (bool) $new_instance['thumbnails'];

				return $instance;
			}

			function form( $instance ) {
				$instance = wp_parse_args( (array) $instance, $this->defaults );
				extract( $instance, EXTR_SKIP );
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" />
</p>
<p>
	<input id="<?php echo $this->get_field_id( 'thumbnails' ); ?>" name="<?php echo $this->get_field_name( 'thumbnails' ); ?>" type="checkbox"<?php checked( $thumbnails, true ); ?> />
	<label for="<?php echo $this->get_field_id( 'thumbnails' ); ?>">Show post thumbnails</label>
</p>
<?php
			}

			/**
			 * Iterates through each post from the WP_Query object.
			 *
			 * This is useful as a sidebar fallback if no widgets are
			 * active.
			 *
			 * @param WP_Query $recent Reference to object containing posts to iterate through.
			 * @param bool $thumbnails Optional. If true, include selected post thumbnails.
			 */

			function posts_walker( &$recent, $thumbnails = false ) {

				// Prepare the size of the thumbnail.

				$size = apply_filters( 'cmo_widget_recent_thumbnail_size', array( 40, 40 ), $thumbnails );
?>
    	<ul>
<?php
				while ( $recent->have_posts() ) :
					$recent->the_post();

?>
    		<li class="post-<?php the_ID(); ?>">
<?php
					if ( $thumbnails && has_post_thumbnail() ) :
?>
    			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( $size, array( 'class' => 'attachment', 'alt' => '', 'title' => '' ) ); ?></a>
<?php
					endif;
?>
					<p><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a><br />
					<span><?php echo get_the_date(); ?></span></p>
				</li>
	<?php
				endwhile;
	?>
    	</ul>
<?php
			}

			function cmo_recent_posts_action( $sidebar, $args = array() ) {
				static $instance = 0;
				$instance++;

				global $wp_registered_sidebars;
				$params = $wp_registered_sidebars[$sidebar];

				$params['before_widget'] = sprintf( $params['before_widget'], __CLASS__ . '_action-' . $instance, $this->classname );

				$args = wp_parse_args( $args, $this->defaults );

				$this->widget( $params, $args );
			}

		}

	}

	if ( !isset( $plugin_dz_biblegateway_votd ) ) {
		$plugin_dz_biblegateway_votd = new dz_biblegateway_votd();
		add_action( 'widgets_init', create_function( '', 'register_widget( "dz_biblegateway_votd_widget" );' ) );
	}
}


/**
 * Old code begins here.
 */

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