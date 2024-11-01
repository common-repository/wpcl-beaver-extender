jQuery( function( $ ) {
	'use strict';
	$('a.content-block-fledit').on( 'click', ( event ) => {
		window.open(event.currentTarget.href);
	} );
});