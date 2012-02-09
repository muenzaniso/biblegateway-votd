<?php
if ( !function_exists( 'dz_biblegateway_votd_uninstall' ) ) {
	/**
	 * uninstall function.
	 *
	 * Removes all options.
	 *
	 * @see dz_biblegateway_votd_admin::update_check()
	 * @see dz_biblegateway_votd::option_name
	 * @see dz_biblegateway_votd::transient_name
	 * @return void
	 */
	function dz_biblegateway_votd_uninstall() {
		foreach( array( 'dz_biblevotd', 'biblegateway_votd', 'dz_biblegateway_votd' ) as $option ) {
			delete_option( $option );
		}

		delete_transient( 'dz_biblegateway_votd_cache' );
	}

	if ( defined( 'WP_UNINSTALL_PLUGIN' ) )
		dz_biblegateway_votd_uninstall();
}