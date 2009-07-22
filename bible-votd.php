<?php 
/*  
Plugin Name: BibleGateway VOTD
Plugin URI: http://www.zaikos.com/blog/wp-plugins/biblegateway-votd/
Version: 2.2
Author: <a href="http://www.zaikos.com/blog/">Dave Zaikos</a>
Description: Insert <a href="http://www.biblegateway.com/usage/">BibleGateway.com</a>'s verse-of-the-day in pages or posts. Add <strong>[bible-votd]</strong> in pages or posts where you want to insert the verse.
*/

// Create class if it doesn't exist.

if (!class_exists("dzBibleGatewayVOTDPlugin")) {
	class dzBibleGatewayVOTDPlugin {

		function BibleVOTD($atts) {

			// Load default settings from widget.

			if ( get_option('dz_biblevotd') ) {
				$options = unserialize(get_option('dz_biblevotd'));
			} else {
				$options['ver'] = 31;
			}

			// Get the Bible version and CSS class if specified.

			extract(shortcode_atts(array(
				'ver' => $options['ver'],
				'class' => 'biblevotd',
			), $atts));

			// Prepare BibleGateway.com's HTML code.

			$votd = "<SCRIPT language=JavaScript src=\"http://www.biblegateway.com/usage/votd/votd2html.php?version=" . $ver . "&amp;jscript=1\" type=text/javascript></SCRIPT><!-- alternative for no javascript --><NOSCRIPT><a href=\"http://www.biblegateway.com/usage/votd/votd2html.php?version=" . $ver . "&amp;jscript=0\">View Verse of the Day</a></NOSCRIPT>";

			// Add CSS class.

			$votd = "<div class=\"" . $class . "\">" . $votd . "</div>";

			// Return output.

			return $votd;
		}

		function BibleVOTD_widget_init() {
			if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
				return;

			function BibleVOTD_widget($args) {
				extract($args);

				if ( get_option('dz_biblevotd') ) {
					$options = unserialize(get_option('dz_biblevotd'));
				} else {
					$options['biblevotd-title'] = 'Verse of the Day';
					$options['ver'] = 31;
				}

				// Prepare the widget.

				$votd = $before_widget;
				$votd .= $before_title . wptexturize($options['biblevotd-title']) . $after_title;

				// Prepare BibleGateway.com's HTML code.

				$votd .= "<SCRIPT language=JavaScript src=\"http://www.biblegateway.com/usage/votd/votd2html.php?version=" . $options['ver'] . "&amp;jscript=1\" type=text/javascript></SCRIPT><!-- alternative for no javascript --><NOSCRIPT><a href=\"http://www.biblegateway.com/usage/votd/votd2html.php?version=" . $options['ver'] . "&amp;jscript=0\">View Verse of the Day</a></NOSCRIPT>";

				// Wrap-up the widget.

				$votd .= $after_widget;

				// Print to the screen.

				echo $votd;
			}

			function BibleVOTD_widget_control() {
				if ( get_option('dz_biblevotd') ) {
					$options = unserialize(get_option('dz_biblevotd'));
				} else {
					$options['biblevotd-title'] = 'Verse of the Day';
					$options['ver'] = 31;
				}

				if ( isset($_POST['dzbiblevotd-widget-submit']) ) {

					// Clean up form submission.

					if( get_magic_quotes_gpc() )
						$options['biblevotd-title'] = esc_attr(strip_tags(stripslashes($_POST['biblevotd-title'])));
					else
						$options['biblevotd-title'] = esc_attr(strip_tags($_POST['biblevotd-title']));

					$options['ver'] = (int)$_POST['ver'];

					//  Save options.

					if ( get_option('dz_biblevotd') )
						update_option('dz_biblevotd', serialize($options));
					else
						add_option('dz_biblevotd', serialize($options), '', 'yes');
				}

					// The settings form.
?>
<input type="hidden" name="dzbiblevotd-widget-submit" value="1" />
<p>
	<label for="biblevotd-title">Title:
		<input class="widefat" name="biblevotd-title" type="text" maxlength="50" value="<?php echo htmlentities($options['biblevotd-title'], ENT_QUOTES); ?>">
	</label>
</p>
<p>
	<label for="ver">Version:
		<select class="widefat" name="ver">
			<option value="31"<?php echo ($options['ver'] === 31) ? ' selected' : ''; ?>>New International Version</option>
			<option value="49"<?php echo ($options['ver'] === 49) ? ' selected' : ''; ?>>New American Standard Bible</option>
			<option value="45"<?php echo ($options['ver'] === 45) ? ' selected' : ''; ?>>Amplified Bible</option>
			<option value="9"<?php echo ($options['ver'] === 9) ? ' selected' : ''; ?>>King James Version</option>
			<option value="47"<?php echo ($options['ver'] === 47) ? ' selected' : ''; ?>>English Standard Version</option>
			<option value="8"<?php echo ($options['ver'] === 8) ? ' selected' : ''; ?>>American Standard Version</option>
			<option value="15"<?php echo ($options['ver'] === 15) ? ' selected' : ''; ?>>Young's Literal Translation</option>
			<option value="16"<?php echo ($options['ver'] === 16) ? ' selected' : ''; ?>>Darby Translation</option>
			<option value="76"<?php echo ($options['ver'] === 76) ? ' selected' : ''; ?>>New International Reader's Version</option>
			<option value="64"<?php echo ($options['ver'] === 64) ? ' selected' : ''; ?>>New International Version - UK</option>
			<option value="72"<?php echo ($options['ver'] === 72) ? ' selected' : ''; ?>>Today's New International Version</option>
		</select>
	</label>
</p>
<?php
			}

			register_sidebar_widget('Bible VOTD', 'BibleVOTD_widget');
			register_widget_control(array('Bible VOTD','widgets'), 'BibleVOTD_widget_control');
		}

	}
}

// Initialize class.

if ( class_exists("dzBibleGatewayVOTDPlugin") ) {
	$dzvotd_plugin = new dzBibleGatewayVOTDPlugin();
}

// Add shortcode and initialize widget.

if ( isset($dzvotd_plugin) ) {
	add_shortcode('bible-votd', array(&$dzvotd_plugin, 'BibleVOTD'));
	add_action('plugins_loaded', array(&$dzvotd_plugin, 'BibleVOTD_widget_init'));
}