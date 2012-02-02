<?php
if ( !class_exists( 'dz_biblegateway_votd_admin' ) ) {
	class dz_biblegateway_votd_admin {

		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'settings_init' ) );
		}

		public function add_admin_menu() {
			add_options_page( 'Bible VOTD Settings', 'Bible VOTD', 'manage_options', 'biblevotd-options', array( &$this, 'biblevotd_options_page' ) );
		}

		public function settings_init() {
			register_setting( 'dz_biblevotd_options', dz_biblegateway_votd::option_name, array( &$this, 'settings_validation' ) );

			add_settings_section( 'biblevotd_options_general', 'General', create_function( '', '' ), 'dz_biblevotd_options_sections' );
			add_settings_field( dz_biblegateway_votd::option_name . '[default-version]', 'Default Version', array( &$this, 'setting_field_default_version' ), 'dz_biblevotd_options_sections', 'biblevotd_options_general' );

			add_settings_section( 'biblevotd_options_advance', 'Advance', create_function( '', '' ), 'dz_biblevotd_options_sections' );
			add_settings_field( dz_biblegateway_votd::option_name . '[extra-versions]', 'Additional Versions', array( &$this, 'setting_field_extra_versions' ), 'dz_biblevotd_options_sections', 'biblevotd_options_advance', array( 'label_for' => 'extra_versions' ) );




			// Advanced options.

			register_setting( 'biblevotd_options', '_theme_use_excerpt', array( &$this, 'setting_validation__theme_use_excerpt' ) );
			add_settings_section( 'biblevotd_options_reading', __( 'Reading' ), create_function( '', '' ), 'biblevotd_options_sections' );
			add_settings_field( '_theme_use_excerpt', 'For each article on a page, show', array( &$this, 'setting_field__theme_use_excerpt' ), 'biblevotd_options_sections', 'biblevotd_options_reading' );

/*
			register_setting( 'cmo_options', 'cmo_hosting', array( &$this, 'settings_validation' ) );
			add_settings_section( 'cmo_options_hosting', 'Hosting', create_function( '', '' ), 'cmo_options_sections' );
			add_settings_field( 'cmo_hosting[contacts]', 'Contact E-mails', array( &$this, 'contacts_setting_field' ), 'cmo_options_sections', 'cmo_options_hosting', array( 'label_for' => 'contact_emails' ) );
			add_settings_field( 'cmo_hosting[paypal]', 'PayPal Invoice URI', array( &$this, 'paypal_setting_field' ), 'cmo_options_sections', 'cmo_options_hosting', array( 'label_for' => 'paypal_invoice_uri' ) );
			add_settings_field( 'cmo_hosting[expiration]', 'Expiration Date', array( &$this, 'expiration_setting_field' ), 'cmo_options_sections', 'cmo_options_hosting', array( 'label_for' => 'expiration_date' ) );
*/

		}

		function biblevotd_options_page() {
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

		function settings_validation( $input ) {

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
				foreach( $input['extra-versions'] as $abbr => $desc ) {
					$versions[] = "$abbr,$desc\n";
				}
			} else {
				$versions = explode( "\n", $input['extra-versions'] );
			}

			$valid = array();
			foreach( $versions as $version ) {
				$version = strip_tags( $version );

				if ( false === strpos( $version, ',' ) )
					continue;

				list( $abbr, $desc ) = explode( ',', $version, 2 );
				$abbr = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $abbr ) );
				$desc = ucwords( trim( preg_replace( '/[^- .,;:A-Za-z0-9\(\)\[\]]/', '', $desc ) ) );

				if ( !empty( $abbr ) && !empty( $desc ) && !array_key_exists( $abbr, $valid ) && !dz_biblegateway_votd::is_version_available( $abbr ) )
					$valid[$abbr] = $desc;
			}
			$options['extra-versions'] = $valid;

			// Return sanitized options.

			return $options;
		}

		function setting_field_default_version() {
			$versions = dz_biblegateway_votd::get_available_versions();

			$options = get_option( dz_biblegateway_votd::option_name );
			$default = ( isset( $options['default-version'] ) ) ? $options['default-version'] : 'NIV';
?>
<select name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[default-version]' ); ?>">
<?php
			foreach( $versions as $abbr => $desc ) {
				$selected = selected( $abbr, $default, false );
				printf ( "\t<option value='%1\$s'%2\$s>%3\$s</option>\n", esc_attr( $abbr ), $selected, esc_attr( $desc ) );
			}
?>
</select>
<?php
		}

		function setting_field_extra_versions() {
			$options = get_option( dz_biblegateway_votd::option_name ); var_dump( $options );

			$versions = '';
			if ( !empty( $options['extra-versions'] ) && is_array( $options['extra-versions'] ) ) {
				foreach( $options['extra-versions'] as $abbr => $desc ) {
					$versions .= "$abbr,$desc\n";
				}
			}
?>
<textarea name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[extra-versions]' ); ?>" rows="10" cols="50" id="extra_versions" class="large-text code"><?php echo esc_attr( rtrim( $versions, "\n" ) ); ?></textarea><span class="description">You can manually add extra versions available from BibleGateway.com here. Enter one version per line in the format: <code>ABBREVIATION,Full Name</code>.</span>
<?php
		}






		function setting_validation__theme_use_excerpt( $input ) {
			return ( '1' == $input ) ? 1 : 0;
		}

		function setting_field__theme_use_excerpt() {
?>
<p><label>
	<input name="_theme_use_excerpt" type="radio" value="0"<?php checked( get_option( '_theme_use_excerpt', 0 ), 0 ); ?> />
	Full text</label>
</p>
<p><label>
	<input name="_theme_use_excerpt" type="radio" value="1"<?php checked( get_option( '_theme_use_excerpt', 0 ), 1 ); ?> />
	Summary</label>
</p>
<?php
		}

	}

	if ( !isset( $theme_dz_biblegateway_votd_admin ) )
		$theme_dz_biblegateway_votd_admin = new dz_biblegateway_votd_admin();
}