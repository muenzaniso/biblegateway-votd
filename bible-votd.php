<?php
/*
Plugin Name: BibleGateway VOTD
Plugin URI: http://www.zaikos.com/coding/wp-plugins/biblegateway-votd/
Version: 2.3
Author: <a href="http://www.zaikos.com/">Dave Zaikos</a>
Description: Insert <a href="http://www.biblegateway.com/usage/">BibleGateway.com</a>'s verse-of-the-day in pages or posts. Use the Widgets page to add the verse to your sidebar or add <strong>[bible-votd]</strong> in pages or posts where you want to insert the verse.
*/

/*  Copyright 2009-2010  Dave Zaikos  (email : http://www.zaikos.com/contact/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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

if ( class_exists( 'BibleGatewayVOTD_Plugin' ) || !isset( $bgvotd_plugin ) )
	$bgvotd_plugin = new BibleGatewayVOTD_Plugin();