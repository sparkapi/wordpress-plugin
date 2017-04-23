(function($){

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
	} );

})(jQuery);