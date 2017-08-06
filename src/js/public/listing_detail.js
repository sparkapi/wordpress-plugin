(function($){

	var initGoogleMap = function(){
		var data = flexmls_data.gmaps;
		var position = new google.maps.LatLng( data.lat, data.lng );
		var map = new google.maps.Map( document.getElementById( 'flexmls-listing-map' ), {
			center: position,
			disableDoubleClickZoom: true,
			mapTypeControl: false,
			scrollwheel: false,
			zoom: 16
		} );
		var marker = new google.maps.Marker( {
			icon: flexmls.pluginurl + '/dist/assets/gmaps_flexmls_pin.png',
			map: map,
			position: position
		} );
		google.maps.event.addDomListener(window, 'resize', function() {
			map.setCenter( position );
		});
	};

	var isGoogleMapsLoaded = function(){
		if( $( '#flexmls-listing-map' ).length ){
			setTimeout( function(){
				if( 'object' === typeof google && 'object' === typeof google.maps ){
					initGoogleMap();
				} else {
					isGoogleMapsLoaded();
				}
			}, 500 );
		}
	};

	$(document).ready( function(){
		if( $( 'body' ).hasClass( 'flexmls-detail' ) ){
			isGoogleMapsLoaded();
		}
	} );

})(jQuery);