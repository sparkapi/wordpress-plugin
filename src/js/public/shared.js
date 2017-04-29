(function($){

	var magnificPhotos = function(){
		var items = [], currentListingId, previousListingId;
		$( '.flexmls-magnific-media' ).on( 'click', function( ev ){
			ev.preventDefault();
			currentListingId = $( this ).data( 'listingid' );
			var data = {
				action: 'get_listing_media',
				listingid: currentListingId,
				mediatype: $( this ).data( 'mediatype' )
			};
			if( currentListingId != previousListingId || !items.length ){
				previousListingId = currentListingId;
				if( $( '#flexmls-loading-spinner' ).length ){
					$( '#flexmls-loading-spinner' ).addClass( 'show' );
				}
				$.post( flexmls.ajaxurl, data, function( response ){
					if( 1 == response.success ){
						items = [];
						$( response.items ).each( function( i, v ){
							items.push({
								src: v.Uri1600,
								title: v.Name.length ? v.Name : v.Caption
							});
						} );
					}
					if( $( '#flexmls-loading-spinner' ).length ){
						$( '#flexmls-loading-spinner' ).removeClass( 'show' );
					}
					$.magnificPopup.open( {
						gallery:{
							enabled: true
						},
						items: items,
						type: 'image'
					}, 0 );
				}, 'json' );
			} else {
				$.magnificPopup.open( {
					gallery:{
						enabled: true
					},
					items: items,
					type: 'image'
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