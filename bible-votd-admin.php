<?php
if ( !class_exists( 'dz_biblegateway_votd_admin' ) ) {
	/**
	 * dz_biblegateway_votd_admin class.
	 *
	 * Handles the administrative options for the dz_biblegateway_votd class.
	 *
	 * @version 3.0
	 * @author Dave Zaikos
	 * @copyright Copyright (c) 2012, Dave Zaikos
	 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
	 */
	class dz_biblegateway_votd_admin {

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'settings_init' ) );
		}

		/**
		 * add_admin_menu function.
		 *
		 * Adds the Bible VOTD option page to the WordPress Options menu.
		 *
		 * @access public
		 * @return void
		 */
		public function add_admin_menu() {
			add_options_page( 'Bible VOTD Settings', 'Bible VOTD', 'manage_options', 'biblevotd-options', array( &$this, 'biblevotd_options_page' ) );
		}

		/**
		 * settings_init function.
		 *
		 * Utilizes the WordPress Settings API to set up settings.
		 *
		 * @access public
		 * @return void
		 */
		public function settings_init() {
			register_setting( 'dz_biblevotd_options', dz_biblegateway_votd::option_name, array( &$this, 'settings_validation' ) );

			add_settings_section( 'biblevotd_options_general', 'General', create_function( '', '' ), 'dz_biblevotd_options_sections' );
			add_settings_field( dz_biblegateway_votd::option_name . '[default-version]', 'Default Version', array( &$this, 'setting_field_default_version' ), 'dz_biblevotd_options_sections', 'biblevotd_options_general' );

			add_settings_section( 'biblevotd_options_advance', 'Advance', create_function( '', '' ), 'dz_biblevotd_options_sections' );
			add_settings_field( dz_biblegateway_votd::option_name . '[extra-versions]', 'Additional Versions', array( &$this, 'setting_field_extra_versions' ), 'dz_biblevotd_options_sections', 'biblevotd_options_advance', array( 'label_for' => 'extra_versions' ) );

			//!TODO: Add setting for caching. Also need cron and plugin (de-)activation hooks.
		}

		/**
		 * biblevotd_options_page function.
		 *
		 * The settings page.
		 *
		 * @access public
		 * @return void
		 */
		public function biblevotd_options_page() {
			settings_errors();
?>
<div class="wrap">
<?php screen_icon(); ?>
	<h2>Bible Verse of the Day Settings</h2>

	<p>The BibleGateway.com plugin is independently written and maintained by <a href="http://zaikos.com/">Dave Zaikos</a> and in no way affiliated with BibleGateway.com.</p>
	<p>If you use this plugin, please consider <a href="http://zaikos.com/donate/">making a donation</a> to support its continued development. Donations from users such as yourself help ensure this software&#8217;s future.</p>

	<form action="options.php" method="post">
<?php
			settings_fields( 'dz_biblevotd_options' );
			do_settings_sections( 'dz_biblevotd_options_sections' );
?>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
		</p>
	</form>
</div>
<?php
		}

		/**
		 * settings_validation function.
		 *
		 * Handles validation for the plugin settings.
		 *
		 * @access public
		 * @see update_option()
		 * @param mixed $input
		 * @return void
		 */
		public function settings_validation( $input ) {

			// Get existing and default settings.

			$options = get_option( dz_biblegateway_votd::option_name );
			$options = wp_parse_args( $options, array(
				'default-version' => 'NIV',
				'extra-versions' => array()
				) );

			// Validate default version.

			if ( $valid = dz_biblegateway_votd::is_version_available( $input['default-version'] ) )
				$options['default-version'] = $valid;

			// Validate extra versions.

			if ( is_array( $input['extra-versions'] ) ) {
				$versions = array();
				foreach ( $input['extra-versions'] as $abbr => $desc ) {
					$versions[] = "$abbr,$desc\n";
				}
			} else {
				$versions = explode( "\n", $input['extra-versions'] );
			}

			$available_versions = array_diff( dz_biblegateway_votd::get_available_versions(), $options['extra-versions'] ); // Separate extra versions from the hard-coded array.

			$valid = array();
			foreach ( $versions as $version ) {
				$version = strip_tags( $version );

				if ( false === strpos( $version, ',' ) )
					continue;

				list( $abbr, $desc ) = explode( ',', $version, 2 );
				$abbr = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $abbr ) );
				$desc = ucwords( trim( preg_replace( array( '/[^- A-Za-z0-9]/', '/\s{2,}/' ), array( '', ' ' ), $desc ) ) );

				if ( !empty( $abbr ) && !empty( $desc ) && !array_key_exists( $abbr, $valid ) && !array_key_exists( $abbr, $available_versions ) )
					$valid[$abbr] = $desc;
			}
			$options['extra-versions'] = $valid;

			// Return sanitized options.

			return $options;
		}

		/**
		 * setting_field_default_version function.
		 *
		 * Creates a drop down menu with available versions. Allows users to select a default version.
		 *
		 * @access public
		 * @return void
		 */
		public function setting_field_default_version() {
			$versions = dz_biblegateway_votd::get_available_versions();

			$options = get_option( dz_biblegateway_votd::option_name );
			$default = ( isset( $options['default-version'] ) ) ? $options['default-version'] : 'NIV';
?>
<select name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[default-version]' ); ?>">
<?php
			foreach ( $versions as $abbr => $desc ) {
				$selected = selected( $abbr, $default, false );
				printf ( "\t<option value='%1\$s'%2\$s>%3\$s</option>\n", esc_attr( $abbr ), $selected, esc_attr( $desc ) );
			}
?>
</select>
<?php
		}

		/**
		 * setting_field_extra_versions function.
		 *
		 * Creates a text box that allows users to add additional Bible versions.
		 *
		 * @access public
		 * @return void
		 */
		public function setting_field_extra_versions() {
			$options = get_option( dz_biblegateway_votd::option_name );

			$versions = '';
			if ( !empty( $options['extra-versions'] ) && is_array( $options['extra-versions'] ) ) {
				foreach ( $options['extra-versions'] as $abbr => $desc ) {
					$versions .= "$abbr,$desc\n";
				}
			}
?>
<textarea name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[extra-versions]' ); ?>" rows="10" cols="50" id="extra_versions" class="large-text code"><?php echo esc_attr( rtrim( $versions, "\n" ) ); ?></textarea>
<span class="description">You can manually add extra versions available from BibleGateway.com. Enter one version per line in the format: <code>ABBREVIATION,Full Name</code>.</span>
<?php
		}

	}

	if ( !isset( $theme_dz_biblegateway_votd_admin ) )
		$theme_dz_biblegateway_votd_admin = new dz_biblegateway_votd_admin();
}