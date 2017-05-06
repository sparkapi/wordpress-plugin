(function($){

	var openPortalLoginInModal = function(){
		$( '.flexmls-carts-buttons' ).on( 'click', 'a', function( ev ){
			var action = $( this ).data( 'portalaction' );
			if( 'login' != action ){
				ev.preventDefault();
				return false;
			}
		} );
	};

	$(document).ready( function(){
		openPortalLoginInModal();
	} );

})(jQuery);