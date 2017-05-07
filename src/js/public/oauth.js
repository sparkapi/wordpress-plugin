(function($){

	var detailPageViews = 0, now = Math.round( new Date().getTime() / 60000 ), summaryPageViews = 0, timeOnPage = 0, timeOnSite = 0;

	if( null === sessionStorage.getItem( 'flexmlsDetailPageViews' ) ){
		sessionStorage.setItem( 'flexmlsDetailPageViews', 0 );
	} else {
		detailPageViews = parseInt( sessionStorage.getItem( 'flexmlsDetailPageViews' ), 10 );
	}
	if( null === sessionStorage.getItem( 'flexmlsSummaryPageViews' ) ){
		sessionStorage.setItem( 'flexmlsSummaryPageViews', 0 );
	} else {
		summaryPageViews = parseInt( sessionStorage.getItem( 'flexmlsSummaryPageViews' ), 10 );
	}
	if( null === sessionStorage.getItem( 'flexmlsBeganOnSite' ) ){
		sessionStorage.setItem( 'flexmlsBeganOnSite', Math.round( new Date().getTime() / 60000 ) );
	} else {
		timeOnSite = now - parseInt( sessionStorage.getItem( 'flexmlsBeganOnSite' ), 10 );
	}

	var maybeShowPortalPopup = function(){
		if( $( '#flexmls-portal-popup' ).length ){

			var detailPageViewTrigger = parseInt( $( '#flexmls-portal-popup' ).data( 'detail' ), 10 );
			var summaryPageViewTrigger = parseInt( $( '#flexmls-portal-popup' ).data( 'summary' ), 10 );
			var timeOnPageTrigger = parseInt( $( '#flexmls-portal-popup' ).data( 'page' ), 10 );
			var timeOnSiteTrigger = parseInt( $( '#flexmls-portal-popup' ).data( 'site' ), 10 );
			var triggerPopup = false;
			var modal = $( '#flexmls-portal-popup' ).data( 'modal' );

			if( $( 'body' ).hasClass( 'flexmls-detail' ) && !isNaN( detailPageViewTrigger ) ){
				detailPageViews += 1;
				if( detailPageViews > detailPageViewTrigger ){
					triggerPopup = true;
					detailPageViews = 0;
				}
				sessionStorage.setItem( 'flexmlsDetailPageViews', detailPageViews );
			}
			if( $( 'body' ).hasClass( 'flexmls-summary' ) && !isNaN( summaryPageViewTrigger ) ){
				summaryPageViews += 1;
				if( summaryPageViews > summaryPageViewTrigger ){
					triggerPopup = true;
					summaryPageViews = 0;
				}
				sessionStorage.setItem( 'flexmlsSummaryPageViews', summaryPageViews );
			}
			if( !isNaN( timeOnSiteTrigger ) ){
				if( timeOnSite > timeOnSiteTrigger ){
					triggerPopup = true;
					timeOnSite = 0;
				}
				sessionStorage.setItem( 'flexmlsDetailPageViews', 0 );
				sessionStorage.setItem( 'flexmlsSummaryPageViews', 0 );
				sessionStorage.setItem( 'flexmlsBeganOnSite', Math.round( new Date().getTime() / 60000 ) );
			}

			if( !isNaN( timeOnPageTrigger ) ){
				setTimeout( function(){
					sessionStorage.setItem( 'flexmlsDetailPageViews', 0 );
					sessionStorage.setItem( 'flexmlsSummaryPageViews', 0 );
					sessionStorage.setItem( 'flexmlsBeganOnSite', Math.round( new Date().getTime() / 60000 ) );
					$.magnificPopup.open({
						modal: modal,
						items: {
							src: '#flexmls-portal-popup'
						},
						type: 'inline'
					}, 0);
				}, timeOnPageTrigger * 60000 );
			}

			if( true === triggerPopup ){
				$.magnificPopup.open({
					modal: modal,
					items: {
						src: '#flexmls-portal-popup'
					},
					type: 'inline'
				}, 0);
			}
		}
	};

	var toggleListingCartStatus = function(){
		$( '.flexmls-carts-buttons' ).on( 'click', 'a', function( ev ){
			var action = $( this ).data( 'portalaction' );
			if( 'toggle' == action ){
				ev.preventDefault();
				var link = $( this );
				var ul = $( this ).closest( 'ul' );
				var listing_id = $( this ).data( 'listingid' );
				var listing_cart = $( this ).data( 'listingcart' );
				var status = $( this ).data( 'status' );
				var data = {
					action: 'toggle_cart_status',
					carts: flexmls_carts,
					id: listing_id,
					cart: listing_cart,
					status: status
				};
				$( this ).blur();
				$.post( flexmls.ajaxurl, data, function( response ){
					if( 1 == response.success ){
						if( $( '.portal-cart-count' ).length ){
							$( response.update ).each( function( k, o ){
								$( '.portal-cart-count[data-cartid="' + o.cart + '"]' ).text( o.count );
							} );
						}
					}
				}, 'json' );
				if( 1 == status ){
					$( link ).data( 'status', 0 ).attr( 'data-status', 0 );
				} else {
					$( link ).data( 'status', 1 ).attr( 'data-status', 1 );
					$( flexmls_carts ).each( function( k, v ){
						if( v != listing_cart ){
							$( 'a[data-listingcart="' + v + '"][data-listingid="' + listing_id + '"]' ).data( 'status', 0 ).attr( 'data-status', 0 );
						}
					});
				}
			}
		} );
	};

	$(document).ready( function(){
		maybeShowPortalPopup();
		toggleListingCartStatus();
	} );

})(jQuery);