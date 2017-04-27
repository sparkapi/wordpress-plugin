(function($){

	var doLocationSearch = function( query, resultDiv ){
		$.ajax({
			beforeSend: function(){
				$( resultDiv ).html( '<span class="dashicons dashicons-image-filter flexmls-querying"></span>' );
			},
			crossDomain: true,
			data: query,
			dataType: 'jsonp',
			error: function( x, err, p ){
				console.log( err, p );
			},
			success: function( response ){
				// Find renderSearchFields in location.js to see formatting/parsing
				console.log( response );
			},
			url: 'https://www.flexmls.com/cgi-bin/mainmenu.cgi'
		});
	};

	var locationSelector = function(){
		var defaultResultsText = 'Begin typing above to select a location';
		var keyupTimer = null;

		$( 'body' ).on( 'click', '.flexmls-location-selector', function( ev ){
			var divID = $( this ).data( 'target' ),
				limit = $( this ).data( 'limit' ) || 0,
				searchInput = $( 'div#' + divID ).find( 'input.flexmls-searchbox' ),
				resultsDiv = $( 'div#' + divID ).find( 'section' );

			$( searchInput ).val( '' );
			$( resultsDiv ).html( defaultResultsText );

			tb_show( 'Flexmls&reg;: Select Location', '#TB_inline?height=300&width=400&inlineId=' + divID );

			var qs = {
				command_line_mode: 'true',
				cmd: 'srv+api/search/getLocations.json',
				i: 'City,StateOrProvince,SteetAddress,PostalCode,CountyOrParish,SubdivisionName,MLSAreaMajor,ListingId,SchoolDistrict',
				l: 8,
				ma: 'x\'' + flexmls.ma_tech_id + '\'',
				p: '',
				std: 'Y',
				tech_id: 'x\'' + flexmls.tech_id + '\''
			};

			$( searchInput ).focus().keyup( function(){
				var searchText = $.trim( $( this ).val() );
				if( 2 < searchText.length ){
					qs.q = searchText;
					doLocationSearch( qs, resultsDiv );
				} else {
					$( resultsDiv ).html( defaultResultsText );
				}
			} );
		} );
	};

	$(document).ready(function(){
		locationSelector();
	});

})(jQuery);