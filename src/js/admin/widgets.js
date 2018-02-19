(function($){

	var dependentSelect = function(){
		$( 'select.widget-toggle-dependent' ).on( 'change', function(){
			var v = $( this ).val();
			var target = $( this ).data( 'child' );
			var triggeron = $( this ).data( 'triggeron' );
			if( -1 === $.inArray( v, triggeron ) ){
				$( target ).hide();
			} else {
				$( target ).show();
			}
		} );
	};

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
		dependentSelect();
		populateMarketStatOptions();
	});
  $(document).on('widget-added', function(event, widget){
    dependentSelect();
    populateMarketStatOptions();
  });
  $(document).on('widget-updated', function(event, widget){
    dependentSelect();
    populateMarketStatOptions();
  });

})(jQuery);