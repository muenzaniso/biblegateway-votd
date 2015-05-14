<?php die( 'Please do not load this file directly.' ); ?>
<script type="text/javascript">
/* <![CDATA[ */
jQuery( document ).ready( function( $ ) {
	var dz_biblevotd_versions = <?php echo json_encode( self::$instances ); ?>;
	$.each( dz_biblevotd_versions, function( id, abbr ) {
		$.getJSON( 'http://www.biblegateway.com/votd/get?callback=?', { 'format' : 'json', 'version' : abbr }, function( json ) {
			if ( 'undefined' != typeof json.error )
				return true;

			var votd = json.votd;

			$( 'div#biblegateway-votd-' + id ).html(
				votd.text + ' &#8212; <a href="' + votd.permalink +'">' + votd.reference + '</a>.' +
				( 'undefined' != typeof votd.audiolink && '' != votd.audiolink ? ' <a href="' + votd.audiolink + '" title="Listen to chapter"><img width="13" height="12" src="http://www.biblegateway.com/resources/audio/images/sound.gif" alt="Listen to chapter" /></a>' : '' ) +
				' <a href="' + votd.copyrightlink + '">' + votd.copyright.replace( /\.+$/, "" ) + '</a>.' +
				' Powered by <a href="http://www.biblegateway.com/">BibleGateway.com</a>.'
			);
		} );
	} );
} );
/* ]]> */
</script>
