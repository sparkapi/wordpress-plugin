(function($){

	var populateMarketStatOptions = function(){
		$( 'body' ).on( 'change', 'select.flexmls-widget-market-stat-selector', function( ev ){
			var availableOptions = $( this ).data( 'options' );
			var selected = $( this ).val();
			var select = $( this ).closest( '.widget-content' ).find( '.flexmls-widget-market-stat-options' );
			var options = '';
			if( availableOptions.hasOwnProperty( selected ) ){
				$.each( availableOptions[selected], function( key, val ){
					options += '<option value="' + key + '">' + val + '</option>';
				} );
			}
			$( select ).html( options );
		} );
	};

	$(document).ready(function(){
		populateMarketStatOptions();
	});

})(jQuery);