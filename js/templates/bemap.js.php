/**
 * This file is included in a PHP file to be compiled
 * It should never be enqueued directly
 */

jQuery( function( $ ) {
	'use strict';

	var _getLatLng = function( address, _callback ) {
		var geocoder = new google.maps.Geocoder();
		// Get latlng from address
		geocoder.geocode( { 'address' : address }, function( results, status ) {
	        if( status == google.maps.GeocoderStatus.OK ) {
	        	_callback( results[0].geometry.location );
	        } else {
	        	_callback( { lat : 43.07996, lng : -89.381189 } ); // Default coordinates
	        }
	    } );
	}

	var _create = function( options ) {
		// first call will set the center
		_getLatLng( "<?php echo $settings->center_address; ?>", function( latlng ) {
			// Set the center
			if( latlng !== false ) {
				options.center = latlng;
				// Create the map
				var map = new google.maps.Map( document.getElementById( 'be-map-<?php echo $id; ?>' ), options );
				// Set the resize function
				google.maps.event.addDomListener( window, 'resize', function() {
				    map.setCenter( latlng );
				});

				var markers = <?php echo json_encode( $settings->markers ); ?>;

				var marker = {};

				for( var i = 0; i < markers.length; i++ ) {

					marker = markers[i];

					_getLatLng( markers[i].address, function( markerlatlng ) {
						if( markerlatlng === false ) {
							return false;
						} else {
							// Add the new marker
							var markerArgs = {
								position: markerlatlng,
								map: map,
								title: marker.title
							};
							// Conditionally set an icon
							if( typeof marker.marker_src !== 'undefined' ) {
								markerArgs.icon = marker.marker_src;
							}
							var m = new google.maps.Marker( markerArgs );
							// Infowindow function
							google.maps.event.addListener( m, 'click', function() {
							    var infowindow = new google.maps.InfoWindow({
							        content : marker.content
							    });
							    infowindow.open( map, m );
							});
						}
					});
				}
			}
		});
	};

	var _init = function() {

		var options = {
		    zoom: <?php echo intval( $settings->zoom ); ?>,
		    minZoom: <?php echo intval( $settings->minZoom ); ?>,
		    maxZoom: <?php echo intval( $settings->maxZoom ); ?>,
		    zoomControl: <?php echo intval( $settings->zoomControl ); ?>,
		    mapTypeId: google.maps.MapTypeId.ROADMAP,
		    scrollwheel: <?php echo intval( $settings->scrollwheel ); ?>,
		    panControl: <?php echo intval( $settings->panControl ); ?>,
		    mapTypeControl: <?php echo intval( $settings->mapTypeControl ); ?>,
		    scaleControl: <?php echo intval( $settings->scaleControl ); ?>,
		    streetViewControl: <?php echo intval( $settings->streetViewControl ); ?>,
		    overviewMapControl: <?php echo intval( $settings->overviewMapControl ); ?>,
		    rotateControl: <?php echo intval( $settings->rotateControl ); ?>
		};

		if( typeof google !== 'undefined' ) {
			_create( options );
		}
	}
	_init();
});