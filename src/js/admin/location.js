(function($){

	var resultsInputType, nameToDisplayInput, fieldToSearchInput, valueToSearchInput;

	var delay = (function(){
		var timer = 0;
			return function( callback, ms ){
				clearTimeout( timer );
				timer = setTimeout(callback, ms);
			};
	})();

	var doLocationSearch = function( query, resultDiv, limit ){
		$.ajax({
			beforeSend: function(){
				$( resultDiv ).html( '<span class="dashicons dashicons-image-filter flexmls-querying"></span>' );
			},
			crossDomain: true,
			data: query,
			dataType: 'jsonp',
			error: function( x, err, p ){
				$( resultDiv ).html( 'There was an error retrieving locations. Please try again later. If you continue to see this message, please contact FBS Support.' );
				console.log( err, p );
			},
			success: function( response ){
				// Find renderSearchFields in location.js to see formatting/parsing
				var html = '<div class="flexmls-location-result">';
				if( response.hasOwnProperty( 'top_result' ) ){
					html += '<h2>Top Result</h2>';
					html += '<ul><li><label><input class="flexmls-single-location" type="' + resultsInputType + '" data-field="' + response.top_result.name + '" data-value="' + response.top_result.value + '" data-name="' + response.top_result.display_val + '"> ' + response.top_result.display_val + ' <small>(' + response.top_result.field_display_val + ')</small></label></li></ul>';
				}
				if( response.hasOwnProperty( 'results' ) ){
					var groups = {};
					response.results.forEach( function( item ){
						var list = groups[ item.name ];
						if( list ){
							list.push( item );
						} else {
							groups[ item.name ] = [ item ];
						}
					} );
					for( var k in groups ){
						html += '<h2>' + groups[ k ][ 0 ].field_display_val + '</h2>';
						html += '<ul>';
						for( var j = 0; j < groups[ k ].length; j++ ){
							html += '<li><label><input class="flexmls-single-location" type="' + resultsInputType + '" data-field="' + groups[ k ][ j ].name + '" data-value="' + groups[ k ][ j ].value + '" data-name="' + groups[ k ][ j ].display_val + '"> ' + groups[ k ][ j ].display_val + '</label></li>';
						}
						html += '</ul>';
					}
				}
				if( !limit ){
					html += '<h2>&nbsp;</h2><ul><li><button type="button" class="button-secondary flexmls-multiple-locations">Add Locations</button></li></ul>';
				}
				html += '</div>';
				$( resultDiv ).html( html );
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
				resultDiv = $( 'div#' + divID ).find( 'section' );

			nameToDisplayInput = $( this ).data( 'name-to-display' );
			nameToSearchInput = $( this ).data( 'name-to-search' );
			valueToSearchInput = $( this ).data( 'value-to-search' );
			resultsInputType = 'checkbox';

			$( searchInput ).val( '' );
			$( resultDiv ).html( defaultResultsText );

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

			selectLocation( limit );

			$( searchInput ).focus().keyup( function(){
				delay( function(){
					var searchText = $.trim( $( searchInput ).val() );
					if( 2 < searchText.length ){
						qs.q = searchText;
						doLocationSearch( qs, resultDiv, limit );
					} else {
						$( resultDiv ).html( defaultResultsText );
					}
				}, 500 );
			} );
		} );
	};

	var selectLocation = function( limit ){
		$( 'body' ).on( 'click', 'button.flexmls-multiple-locations', function( ev ){
			if( 0 === limit ){
				var c = $( this ).closest( '.flexmls-location-result' );
				tb_remove();
			}
		} );
		$( 'body' ).on( 'click', 'input.flexmls-single-location', function( ev ){
			if( 1 === limit ){
				$( 'input[name="' + nameToDisplayInput + '"]' ).val( $( this ).data( 'name' ) );
				$( 'input[name="' + nameToSearchInput + '"]' ).val( $( this ).data( 'field' ) );
				$( 'input[name="' + valueToSearchInput + '"]' ).val( $( this ).data( 'value' ) );
				tb_remove();
			}
		} );
	};

	$(document).ready(function(){
		locationSelector();
	});

})(jQuery);