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
		 * cron_name
		 *
		 * The name of the cron action hook.
		 */
		const cron_name = 'dz_biblegateway_fetch';

		/**
		 * bg_api_url
		 *
		 * The BibleGateway.com API URI. Sprintf-ready--%1$s is the version abbreviation.
		 */
		const bg_api_uri = 'http://www.biblegateway.com/votd/get/?format=json&version=%1$s';

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_init', array( &$this, 'update_check' ) );
			add_filter( 'plugin_action_links_' . str_replace( '-admin', '', plugin_basename( __FILE__ ) ), array( &$this, 'add_plugin_page_settings_link' ) );
			add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'settings_init' ) );

			add_action( self::cron_name, array( &$this, 'cache_verses' ) );

			// (De-)activation hooks.

			register_activation_hook( __FILE__, array( &$this, 'schedule_fetch' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'unschedule_fetch' ) );
		}

		/**
		 * update_check function.
		 *
		 * Updates the options from previous versions so everything works.
		 *
		 * @access public
		 * @return void
		 */
		public function update_check() { return; //!TODO: Debug this. It doesn't work properly.
			$options = get_option( dz_biblegateway_votd::option_name );
			if ( !$options || empty( $options['version'] ) || version_compare( dz_biblegateway_votd::version, $options['version'], '>' ) ) {

				// The latest defaults, which are also defined in {@link self::settings_validation()}.

				$new = array(
					'version' => dz_biblegateway_votd::version,
					'default-version' => 'NIV',
					'embed-method' => 'cache',
					'cache-versions' => array(),
					'extra-versions' => array()
					);

				// Version 1.

				if ( get_option( 'dz_biblevotd' ) ) {
					$options = maybe_unserialize( get_option( 'dz_biblevotd', array() ) );
					$options = array_merge( (array) $options, array( 'ver' => 31 ) );

					add_option( 'biblegateway_votd', $options, '', 'no' );
					delete_option( 'dz_biblevotd' );
				}

				// Version 2.

				if ( $options = get_option( 'biblegateway_votd' ) ) {
					$versions_diff = array(
						31 => 'NIV',
						49 => 'NASB',
						45 => 'AMP',
						9 => 'KJV',
						47 => 'ESV',
						8 => 'ASV',
						15 => 'YLT',
						16 => 'DARBY',
						76 => 'NIRV',
						64 => 'NIVUK',
						72 => 'TNIV'
						);

					if ( !empty( $options['ver'] ) && array_key_exists( $options['ver'], $versions_diff ) )
						$new['default-version'] = $versions_diff[$options['ver']];

					update_option( dz_biblegateway_votd::option_name, $new );
					delete_option( 'biblegateway_votd' );
				}
			}
		}

		/**
		 * add_plugin_page_settings_link function.
		 *
		 * Adds a Settings link for this plugin on the plugins page.
		 *
		 * @access public
		 * @param array $actions
		 * @return array Array of actions for this plugin on the plugins page.
		 */
		public function add_plugin_page_settings_link( $actions ) {
			$settings_action = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=biblevotd-options' ), __( 'Settings' ) );
			array_unshift( $actions, $settings_action );
			return $actions;
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
			add_settings_field( dz_biblegateway_votd::option_name . '[default-version]', 'Default Version', array( &$this, 'setting_field_default_version' ), 'dz_biblevotd_options_sections', 'biblevotd_options_general', array( 'label_for' => 'default-version' ) );
			add_settings_field( dz_biblegateway_votd::option_name . '[embed-method]', 'Embed Method', array( &$this, 'setting_field_embed_method' ), 'dz_biblevotd_options_sections', 'biblevotd_options_general', array( 'label_for' => 'embed-method' ) );
			add_settings_field( dz_biblegateway_votd::option_name . '[cache-versions]', 'Cache Versions', array( &$this, 'setting_field_cache_versions' ), 'dz_biblevotd_options_sections', 'biblevotd_options_general', array( 'label_for' => 'cache-versions' ) );

			add_settings_section( 'biblevotd_options_advance', 'Advance', create_function( '', '' ), 'dz_biblevotd_options_sections' );
			add_settings_field( dz_biblegateway_votd::option_name . '[extra-versions]', 'Additional Versions', array( &$this, 'setting_field_extra_versions' ), 'dz_biblevotd_options_sections', 'biblevotd_options_advance', array( 'label_for' => 'extra-versions' ) );
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

	<h3>Usage</h3>

	<p>Use the <a href="<?php echo admin_url( 'widgets.php' ); ?>">Widgets</a> page to add the verse to one or more of your sidebars. You can include the verse in a page or post by using the shortcode <code>[biblevotd]</code>. When using the shortcode you can override the default version (set below) by providing the version abbreviation (for example, <code>[biblevotd version="KJV"]</code> to use the King James Version).</p>

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

			// Get existing and default settings, also see {@link self::update_check()}.

			$options = (array) get_option( dz_biblegateway_votd::option_name, array() );
			$options = wp_parse_args( $options, array(
				'version' => dz_biblegateway_votd::version,
				'default-version' => 'NIV',
				'embed-method' => 'cache',
				'cache-versions' => array(),
				'extra-versions' => array()
				) );

			// Validate default version.

			if ( $valid = dz_biblegateway_votd::is_version_available( $input['default-version'] ) )
				$options['default-version'] = $valid;

			// Validate the embed method.

			if ( in_array( $input['embed-method'], array( 'cache', 'jquery', 'basic' ) ) )
				$options['embed-method'] = $input['embed-method'];

			// Validate cache versions.

			$options['cache-versions'] = array_intersect( (array) $input['cache-versions'], array_keys( dz_biblegateway_votd::get_available_versions() ) );

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
				$desc = ucwords( trim( preg_replace( array( '/[^- A-Za-z0-9\(\)]/', '/\s{2,}/' ), array( '', ' ' ), $desc ) ) );

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
<select name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[default-version]' ); ?>" id="default-version">
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
		 * setting_field_embed_method function.
		 *
		 * Provides a drop down menu of options that control how the plugin will insert the
		 * verse into the page.
		 *
		 * @access public
		 * @see dz_biblegateway_votd::bible_votd_code_helper()
		 * @return void
		 */
		public function setting_field_embed_method() {
			$methods = array(
				'cache' => 'Cache',
				'jquery' => 'jQuery',
				'basic' => 'Basic'
				);

			$options = get_option( dz_biblegateway_votd::option_name );
			$default = ( isset( $options['embed-method'] ) ) ? $options['embed-method'] : 'cache';
?>
<select name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[embed-method]' ); ?>" id="embed-method">
<?php
			foreach ( $methods as $abbr => $desc ) {
				$selected = selected( $abbr, $default, false );
				printf ( "\t<option value='%1\$s'%2\$s>%3\$s</option>\n", esc_attr( $abbr ), $selected, esc_attr( $desc ) );
			}
?>
</select>
<?php
		}

		/**
		 * setting_field_cache_versions function.
		 *
		 * Provides a multiple selection box of versions to cache.
		 *
		 * @access public
		 * @return void
		 */
		public function setting_field_cache_versions() {
			$versions = dz_biblegateway_votd::get_available_versions();

			$options = get_option( dz_biblegateway_votd::option_name );
			$selections = ( isset( $options['cache-versions'] ) ) ? $options['cache-versions'] : array();
?>
<select name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[cache-versions][]' ); ?>" id="cache-versions" multiple size="8">
<?php
			foreach ( $versions as $abbr => $desc ) {
				$selected = selected( in_array( $abbr, $selections ), true, false );
				printf ( "\t<option value='%1\$s'%2\$s>%3\$s</option>\n", esc_attr( $abbr ), $selected, esc_attr( $desc ) );
			}
?>
</select>
<span class="description">Hold the Command key (Mac) or the Control key (others) to select multiple versions. Versions selected here will be cached once daily by WordPress to prevent page loading delays when display the verse (Embed Method above must be set to <code>Cache</code>).
<?php
			if ( $next = wp_next_scheduled( self::cron_name ) ) :
?>
<br />
<span>
	<?php
				printf( 'Next caching is on: <code>%s</code>.', date_i18n( _x( 'Y-m-d G:i:s', 'timezone date format' ), $next ) );
?>
</span>
<?php
			endif;
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
<textarea name="<?php echo esc_attr( dz_biblegateway_votd::option_name . '[extra-versions]' ); ?>" rows="10" cols="50" id="extra-versions" class="large-text code"><?php echo esc_attr( rtrim( $versions, "\n" ) ); ?></textarea>
<span class="description">You can manually add extra versions available from BibleGateway.com. Enter one version per line in the format: <code>ABBREVIATION,Full Name</code>.</span>
<?php
		}

		/**
		 * remote_get_json_helper function.
		 *
		 * Processes the JSON request. Checks to make sure the verse is for the proper date,
		 * then extracts the necessary verse information from request response and builds the
		 * verse for printing.
		 *
		 * @access private
		 * @param array $json
		 * @return bool|array A boolean false if nothing could be parsed. An associative array containing the filtered verse and raw JSON request.
		 */
		private function remote_get_json_helper( $json ) {

/*

{"votd":{"text":"&ldquo;[Thanksgiving and Prayer]  We ought always to thank God for you, brothers and sisters, and rightly so, because your faith is growing more and more, and the love all of you have for one another is increasing.&rdquo;",
"display_ref":"2 Thessalonians 1:3",
"reference":"2 Thessalonians 1:3",
"permalink":"http:\/\/www.biblegateway.com\/passage\/?version=NIV&amp;search=2 Thessalonians 1:3",
"copyright":"Copyright &#169;  1973, 1978, 1984, 2011 by Biblica",
"copyrightlink":"http:\/\/www.biblegateway.com\/versions\/index.php?action=getVersionInfo&amp;vid=31&amp;lang=2",
"audiolink":"http:\/\/www.biblegateway.com\/audio\/mclean\/niv\/2Thess.1.3",
"day":"09",
"month":"02",
"year":"2012",
"version":"New International Version",
"version_id":"NIV"}}
*/
/*
html(e.text+
' &#8212; <a href="'+
e.permalink+
'">'+
e.reference+
"</a>."+
("undefined"!=typeof e.audiolink?' <a href="'+
e.audiolink+
'" title="Listen to chapter"><img width="13" height="12" src="http://www.biblegateway.com/resources/audio/images/sound.gif" alt="Listen to chapter" /></a>':"")+
' <a href="'+
e.copyrightlink+
'">'+
e.copyright+
'</a>. Powered by <a href="http://www.biblegateway.com/">BibleGateway.com</a>.');});});});
*/

			$json = json_decode( $json );

			// BibleGateway.com returns the request nested in another array. Move it up.

			if ( 1 == count( $json ) && is_array( current( $json ) )
				$json = current( $json );

			// Is this a new day?

			if ( date( 'Ymd' ) != $json['year'] . $json['month'] . $json['day'] )
				return false;

			// Build the verse.





			return false;
		}

		/**
		 * get_next_fetch_time function.
		 *
		 * Returns a Unix timestamp for the next fetch.
		 *
		 * This function first checks the cache. If it is empty, it returns a time 10-minutes from now.
		 * Otherwise it returns a time for the next day (6:01 AM UTC or 1:01 AM EST).
		 *
		 * @access private
		 * @uses get_transient()
		 * @param bool $force If true, forces the next day time even if the cache is empty.
		 * @return int
		 */
		private function get_next_fetch_time( $force = false ) {
			if ( !$force && !get_transient( dz_biblegateway_votd::transient_name ) )
				return time() + 600;

			return mktime( 6, 0, 1, date( 'n' ), date( 'j' ) + 1 );
		}

		/**
		 * cache_verses function.
		 *
		 * The main handler for fetching the verses and storing them using the WordPress Transient API.
		 *
		 * @access public
		 * @uses wp_remote_get()
		 * @uses wp_remote_retrieve_response_code()
		 * @uses wp_remote_retrieve_body()
		 * @uses self::remote_get_json_helper()
		 * @uses self::get_next_flush_time()
		 * @return void
		 */
		public function cache_verses() {

			// Get the versions to cache.

			$options = get_option( dz_biblegateway_votd::option_name );
			$versions = ( isset( $options['cache-versions'] ) ) ? $options['cache-versions'] : array();

			// Clear the existing cache.

			delete_transient( dz_biblegateway_votd::transient_name );

			// Fetch each version.

			$cache = array();
			foreach ( $versions as $version ) {
				$resp = wp_remote_get( sprintf( bg_api_uri, $version ) );

				if ( 200 != wp_remote_retrieve_response_code( $resp ) )
					continue;

				$body = wp_remote_retrieve_body( $resp );

				$verse = $this->remote_get_json_helper( $body );

				if ( false !== $verse )
					$cache[$version] = $verse;
			}

			// If versions were fetch, store them as a transient.

			if ( !empty( $cache ) )
				set_transient( dz_biblegateway_votd::transient_name, $cache, ( $this->get_next_fetch_time( true ) - 60 ) );

			// Schedule next fetch.

			$this->schedule_fetch();
		}

		/**
		 * schedule_fetch function.
		 *
		 * Schedules a cron job to fetch the verses once daily. If the cron job is already scheduled then
		 * this does nothing.
		 *
		 * @access public
		 * @uses wp_next_scheduled()
		 * @uses wp_schedule_single_event()
		 * @uses self::get_next_fetch_time()
		 * @return void
		 */
		public function schedule_fetch() {
			$options = get_option( dz_biblegateway_votd::option_name );

			if ( !empty( $options['cache-versions'] ) && !wp_next_scheduled( self::cron_name ) )
				wp_schedule_single_event( $this->get_next_fetch_time(), self::cron_name );
		}

		/**
		 * unschedule_fetch function.
		 *
		 * Unschedules the cron job to fetch the verses.
		 *
		 * @access public
		 * @uses wp_clear_scheduled_hook()
		 * @return void
		 */
		public function unschedule_fetch() {
			wp_clear_scheduled_hook( self::cron_name );
		}

	}

	if ( !isset( $theme_dz_biblegateway_votd_admin ) )
		$theme_dz_biblegateway_votd_admin = new dz_biblegateway_votd_admin();
}