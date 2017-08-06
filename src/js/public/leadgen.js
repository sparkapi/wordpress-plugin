(function($){

	var doLeadgen = function(){
		$( 'button[data-flexmls-button="leadgen"]' ).on( 'click', function(){
			$( '.flexmls-form-notice' ).remove();
			var btn = $( this );
			var btnHtml = $( btn ).html();
			$( btn ).html( 'Sending' ).attr( 'disabled', true );
			var form = $( btn ).data( 'form' );
			var data = {
				action: 'flexmls_leadgen',
				name: $( form ).find( 'input[name="name"]' ).val(),
				email: $( form ).find( 'input[name="email"]' ).val(),
				message: $( form ).find( 'textarea[name="message"]' ).val(),
				color: $( form ).find( 'input[name="color"]' ).val(),
				source: $( form ).find( 'input[name="source"]' ).val(),
				success: $( form ).find( 'input[name="success"]' ).val()
			};
			if( $( form ).find( 'input[name="street"]' ).length ){
				data.street = $( form ).find( 'input[name="street"]' ).val();
				data.city = $( form ).find( 'input[name="city"]' ).val();
				data.state = $( form ).find( 'input[name="state"]' ).val();
				data.zip = $( form ).find( 'input[name="zip"]' ).val();
			}
			if( $( form ).find( 'input[name="phone"]' ).length ){
				data.phone = $( form ).find( 'input[name="phone"]' ).val();
			}

			$.post( flexmls.ajaxurl, data, function( response ){
				var noticeClass = 'warning';
				if( 1 === response.success ){
					noticeClass = 'success';
				}
				$( form ).prepend( '<div class="flexmls-form-notice flexmls-form-notice-' + noticeClass + '">' + response.message + '</div>' );
				$( btn ).html( btnHtml ).removeAttr( 'disabled' );
			}, 'json' );
		});
	};

	$(document).ready(function(){
		doLeadgen();
	});

})(jQuery);