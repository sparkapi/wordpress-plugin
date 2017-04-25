window.optimizedResize = (function(){
	var callbacks = [], running = false;
	function resize(){
		if( !running ){
			running = true;
			if( window.requestAnimationFrame ){
				window.requestAnimationFrame( runCallbacks );
			} else {
				setTimeout( runCallbacks, 66 );
			}
		}
	}
	function runCallbacks(){
		callbacks.forEach( function( callback ){
			callback();
		} );
		running = false;
	}
	function addCallback( callback ){
		if( callback ){
			callbacks.push( callback );
		}
	}
	return {
		add: function( callback ){
			if( !callbacks.length ){
				window.addEventListener( 'resize', resize );
			}
			addCallback( callback );
		}
	};
}());
window.optimizedScroll = (function(){
	var callbacks = [], running = false;
	function scroll(){
		if( !running ){
			running = true;
			if( window.requestAnimationFrame ){
				window.requestAnimationFrame( runCallbacks );
			} else {
				setTimeout( runCallbacks, 66 );
			}
		}
	}
	function runCallbacks(){
		callbacks.forEach( function( callback ){
			callback();
		} );
		running = false;
	}
	function addCallback( callback ){
		if( callback ){
			callbacks.push( callback );
		}
	}
	return {
		add: function( callback ){
			if( !callbacks.length ){
				window.addEventListener( 'scroll', scroll );
			}
			addCallback( callback );
		}
	};
}());
(function($){

	var doLeadgen = function(){
		$( 'button[data-flexmls-button="leadgen"]' ).on( 'click', function(){
			var btn = $( this );
			var btnHtml = $( btn ).html();
			$( btn ).html( 'Sending' ).attr( 'disabled', true );
			var form = $( btn ).data( 'form' );
			var data = {
				action: 'flexmls_leadgen',
				name: $( form ).find( 'input[name="name"]' ).val(),
				email: $( form ).find( 'input[name="email"]' ).val(),
				street: $( form ).find( 'input[name="street"]' ).val() || '',
				city: $( form ).find( 'input[name="city"]' ).val() || '',
				state: $( form ).find( 'input[name="state"]' ).val() || '',
				zip: $( form ).find( 'input[name="zip"]' ).val() || '',
				phone: $( form ).find( 'input[name="phone"]' ).val() || '',
				message: $( form ).find( 'textarea[name="message"]' ).val(),
				color: $( form ).find( 'input[name="color"]' ).val(),
				source: $( form ).find( 'input[name="source"]' ).val(),
			};
			$.post( flexmls.ajaxurl, data, function( response ){
				console.log( response );
				$( btn ).html( btnHtml ).removeAttr( 'disabled' );
			}, 'json' );
		});
	};

	$(document).ready(function(){
		doLeadgen();
	});

})(jQuery);
(function($){

	var bounds, infoWindow, map, marker, markers;

	var addGoogleMapMarker = function( data ){
		var position = new google.maps.LatLng( data.lat, data.lng );
		var marker = new google.maps.Marker( {
			content: '<div class="flexmls-infowindow">\
					<a class="flexmls-infowindow-image" href="' + data.url + '" style="background-image:url(' + data.image + ');"></a>\
					<div class="flexmls-infowindow-details">\
						<div class="flexmls-infowindow-status">' + data.status + '</div>\
						<div class="flexmls-infowindow-price">' + data.price + '</div>\
						<div class="flexmls-infowindow-quickfacts">' + data.quickfacts + '</div>\
					</div>\
					<div class="flexmls-infowindow-viewlisting"><a href="' + data.url + '">See details</a></div>\
				</div>',
			icon: flexmls.pluginurl + '/dist/assets/gmaps_flexmls_pin.png',
			map: map,
			position: position
		} );
		bounds.extend( position );
		map.fitBounds( bounds );

		google.maps.event.addListener( marker, 'click', function(){
			infoWindow.close();
			infoWindow.setContent( this.content );
			infoWindow.open( map, marker );
		} );
	};

	var initGoogleMap = function(){
		markers = flexmls_data.gmaps;
		map = new google.maps.Map( document.getElementById( 'flexmls-listing-map' ), {
			center: {lat: 0, lng: 0},
			disableDoubleClickZoom: true,
			mapTypeControl: false,
			scrollwheel: false,
			zoom: 4
		} );
		bounds = new google.maps.LatLngBounds();
		infoWindow = new google.maps.InfoWindow();
		for( var i = 0; i < markers.length; i++ ){
			addGoogleMapMarker( markers[ i ] );
		}
		google.maps.event.addDomListener(window, 'resize', function() {
			map.fitBounds( bounds );
		});
	};

	var isGoogleMapsLoaded = function(){
		if( $( '#flexmls-listing-map' ).length ){
			setTimeout( function(){
				if( 'object' === typeof google && 'object' === typeof google.maps ){
					initGoogleMap();
				} else {
					console.log( 'Google maps is not loaded' );
					isGoogleMapsLoaded();
				}
			}, 500 );
		}
	};

	var changeFilterParams = function(){
		$( 'select[name="listings_per_page"]' ).change( function(){
			var baseurl = $( this ).data( 'baseurl' );
			window.location = baseurl + '?listings_per_page=' + $( this ).val();
		} );
		$( 'select[name="listings_order_by"]' ).change( function(){
			var baseurl = $( this ).data( 'baseurl' );
			window.location = baseurl + '?listings_order_by=' + $( this ).val();
		} );
	};

	$(document).ready( function( $ ){
		changeFilterParams();
		isGoogleMapsLoaded();
	} );

})(jQuery);
(function($){

	var testJS = function(){
		$( 'body' ).removeClass( 'flexmls-no-js' );
	};

	$(document).ready(function(){
		testJS();
	});


})(jQuery);