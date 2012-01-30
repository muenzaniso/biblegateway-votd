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
			register_setting( 'dz_biblevotd_options', dz_biblegateway_votd::option_name, array( &$this, 'setting_validation_options' ) );

			// General options.
			
			add_settings_section( 'biblevotd_options_general', 'General', create_function( '', '' ), 'dz_biblevotd_options_sections' );
			add_settings_field( dz_biblegateway_votd::option_name . '[default-version]', 'Default Version', array( &$this, 'setting_field_default_version' ), 'dz_biblevotd_options_sections', 'biblevotd_options_general' );

			// Reading options.

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

	<p>The BibleGateway.com plugin is in independently written and maintained by <a href="http://zaikos.com/">Dave Zaikos</a> and is in no way affiliated with BibleGateway.com.</p>
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

		function setting_validation_default_version( $input ) {
			if ( dz_biblegateway_votd::is_version_available( $input ) )
				return $input;

			return 'NIV';
		}

		function setting_field_default_version() {
			$versions = dz_biblegateway_votd::get_available_versions();
			$default = get_option( dz_biblegateway_votd::option_name );
			$default = ( isset( $default['default-version'] ) ) ? $default['default-version'] : 'NIV';
?>
<select name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[default-version]' ); ?>">
<?php
			foreach( $versions as $abbr => $desc ) {
				$selected = selected( $version, $default );
				printf ( "\t<option value='%1\$s'%2\$s>%3\$s</option>\n", esc_attr( $abbr ), $selected, esc_attr( $desc ) );
			}
?>
</select>
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