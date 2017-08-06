(function($){

	var magnificPhotos = function(){
		var items = [], previousListingId, previousMediaType = null, magnificType = 'image';

		var iframe_customization = {
			patterns: {
				youtube: {
					src: '//www.youtube.com/embed/%id%?autoplay=1&rel=0&modestbranding=1'
				}
			}
		};
		$( '.flexmls-magnific-media' ).on( 'click', function( ev ){
			ev.preventDefault();
			var currentListingId = $( this ).data( 'listingid' );
			var currentMediaType = $( this ).data( 'mediatype' );
			var data = {
				action: 'get_listing_media',
				listingid: currentListingId,
				mediatype: currentMediaType
			};
			if( currentListingId != previousListingId || currentMediaType != previousMediaType || !items.length ){
				previousListingId = currentListingId;
				previousMediaType = currentMediaType;
				if( $( '#flexmls-loading-spinner' ).length ){
					$( '#flexmls-loading-spinner' ).addClass( 'show' );
				}
				$.post( flexmls.ajaxurl, data, function( response ){
					if( 1 == response.success ){
						items = [];
						if( 'photos' == currentMediaType ){
							magnificType = 'image';
							$( response.items ).each( function( i, v ){
								items.push({
									src: v.Uri1600,
									title: v.Name.length ? v.Name : v.Caption
								});
							} );
						} else {
							magnificType = 'iframe';
							console.log( response );
							$( response.items ).each( function( i, v ){
								items.push({
									src: v.Uri,
									title: ''
								});
							} );
						}
					}
					if( $( '#flexmls-loading-spinner' ).length ){
						$( '#flexmls-loading-spinner' ).removeClass( 'show' );
					}
					$.magnificPopup.open( {
						gallery:{
							enabled: true
						},
						iframe: iframe_customization,
						items: items,
						type: magnificType
					}, 0 );
				}, 'json' );
			} else {
				$.magnificPopup.open( {
					gallery:{
						enabled: true
					},
					iframe: iframe_customization,
					items: items,
					type: magnificType
				}, 0 );
			}
		} );
	};

	var testJS = function(){
		$( 'body' ).removeClass( 'flexmls-no-js' );
	};

	$(document).ready(function(){
		testJS();
		magnificPhotos();
	});


})(jQuery);