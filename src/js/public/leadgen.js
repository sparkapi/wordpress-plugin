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