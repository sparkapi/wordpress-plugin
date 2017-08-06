(function($){

	var askQuestion = function(){
		var listingid, listingaddress1, listingaddress2;
		$( '.ask-question > a, .flexmls-button-ask-question' ).on( 'click', function( ev ){
			ev.preventDefault();
			$( '.flexmls-form-notice' ).remove();
			listingid = $( this ).data( 'listingid' );
			listingaddress1 = $( this ).data( 'listingaddress1' );
			listingaddress2 = $( this ).data( 'listingaddress2' );
			$.magnificPopup.open({
				callbacks: {
					beforeOpen: function(){
						$( '#flexmls-contact-popup' ).find( 'h3' ).html( 'Ask a Question<small>' + listingaddress1 + '</small>' );
						$( '#flexmls-contact-popup button[name="send"]' ).on( 'click', function(){
							$( '.flexmls-form-notice' ).remove();
							var btn = $( this );
							$( btn ).html( 'Sending' ).attr( 'disabled', true );
							var data = {
								action: 'flexmls_listing_ask_question',
								address: listingaddress1 + ', ' + listingaddress2,
								listing: listingid,
								name: $( 'input#flexmls-contact-popup-name' ).val(),
								email: $( 'input#flexmls-contact-popup-email' ).val(),
								message: $( 'textarea#flexmls-contact-popup-comments' ).val()
							};
							$.post( flexmls.ajaxurl, data, function( response ){
								var noticeClass = 'warning';
								if( 1 === response.success ){
									noticeClass = 'success';
									$( 'textarea#flexmls-contact-popup-comments' ).val( '' );
								}
								$( '.flexmls-contact-popup-form' ).prepend( '<div class="flexmls-form-notice flexmls-form-notice-' + noticeClass + '">' + response.message + '</div>' );
								$( btn ).html( 'Send' ).removeAttr( 'disabled' );
							}, 'json' );
						} );
					},
					close: function(){
						$( '#flexmls-contact-popup button[name="send"]' ).unbind( 'click' );
					}
				},
				items: {
					src: '#flexmls-contact-popup'
				},
				type: 'inline'
			}, 0);
		} );
	};

	var scheduleShowing = function(){
		var listingid, listingaddress1, listingaddress2;
		$( '.flexmls-button-schedule-showing' ).on( 'click', function(){
			$( '.flexmls-form-notice' ).remove();
			listingid = $( this ).data( 'listingid' );
			listingaddress1 = $( this ).data( 'listingaddress1' );
			listingaddress2 = $( this ).data( 'listingaddress2' );
			$.magnificPopup.open({
				callbacks: {
					beforeOpen: function(){
						$( '#flexmls-contact-popup' ).find( 'h3' ).html( 'Schedule a Showing<small>' + listingaddress1 + '</small>' );
						$( '#flexmls-contact-popup button[name="send"]' ).on( 'click', function(){
							$( '.flexmls-form-notice' ).remove();
							var btn = $( this );
							$( btn ).html( 'Sending' ).attr( 'disabled', true );
							var data = {
								action: 'flexmls_listing_schedule_showing',
								address: listingaddress1 + ', ' + listingaddress2,
								listing: listingid,
								name: $( 'input#flexmls-contact-popup-name' ).val(),
								email: $( 'input#flexmls-contact-popup-email' ).val(),
								message: $( 'textarea#flexmls-contact-popup-comments' ).val()
							};
							$.post( flexmls.ajaxurl, data, function( response ){
								var noticeClass = 'warning';
								if( 1 === response.success ){
									noticeClass = 'success';
									$( 'textarea#flexmls-contact-popup-comments' ).val( '' );
								}
								$( '.flexmls-contact-popup-form' ).prepend( '<div class="flexmls-form-notice flexmls-form-notice-' + noticeClass + '">' + response.message + '</div>' );
								$( btn ).html( 'Send' ).removeAttr( 'disabled' );
							}, 'json' );
						} );
					},
					close: function(){
						$( '#flexmls-contact-popup button[name="send"]' ).unbind( 'click' );
					}
				},
				items: {
					src: '#flexmls-contact-popup'
				},
				type: 'inline'
			}, 0);
		} );
	};

	$(document).ready(function(){
		askQuestion();
		scheduleShowing();
	});

})(jQuery);