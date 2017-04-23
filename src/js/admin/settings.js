(function($){

	var cacheClearProgressCount = 0;

	var ajaxClearCache = function(){
		$( 'a#clear-spark-api-cache' ).on( 'click', function( ev ){
			ev.preventDefault();
			cacheClearProgressCount = 0;
			var btn = $( this );
			$( this ).blur();
			var btnText = $( btn ).text();
			$( btn ).addClass( 'is-clearing-cache' );
			var cacheClearInterval = setInterval( function(){
				ajaxClearingCache( btn );
			}, 300 );
			$.post( ajaxurl, {action: 'clear_spark_api_cache'}, function( response ){
				if( 1 === response.success ){
					clearInterval( cacheClearInterval );
					$( btn ).text( 'Cache has been cleared' ).removeClass( 'is-clearing-cache' ).addClass( 'cache-is-cleared' );
					setTimeout( function(){
						$( btn ).text(btnText).removeClass( 'cache-is-cleared' );
					}, 3000 );
				}
			}, 'json' );
		} );
	};

	var ajaxClearingCache = function( btn ){
		var ellipses = '.';
		if( 1 === cacheClearProgressCount ){
			ellipses = '..';
		} else if( 2 === cacheClearProgressCount ){
			ellipses = '...';
		}
		$( btn ).text( 'Clearing cache' + ellipses );
		cacheClearProgressCount++;
		if( 3 === cacheClearProgressCount ){
			cacheClearProgressCount = 0;
		}
	};

	var searchableSearchDefault = function(){
		if( $( '.flexmls-searchdefault-select2' ).length ){
			$( '.flexmls-searchdefault-select2' ).select2( {
				placeholder: 'Select a link',
			} );
		}
	};

	var sortableSearchFields = function(){
		if( $( '#searchresults-fields' ).length ){
			$( '#searchresults-fields' ).on( 'click', '.flexmls-searchresults-delete-row', function(){
				var el = $( this ).closest( 'li' );
				$( el ).fadeOut( 'fast', function(){
					$( el ).remove();
				});
			} );
			$( '#searchresults-fields' ).sortable({
				axis: 'y'
			});
			$( '#searchresults-fields' ).disableSelection();
		}
		if( $( '.flexmls-searchresults-select2' ).length ){
			$( '.flexmls-searchresults-select2' )
				.select2({
					placeholder: 'Add a new field',
				} )
				.on( 'select2:select', function( ev ){
					var label = $( this ).val();
					$( '#searchresults-fields' ).append( '<li>\
							<div class="flexmls-sortable-row">\
								<label>' + label + '</label>\
								<input type="text" class="regular-text" name="flexmls_settings[general][search_results_fields][' + label + ']" value="' + label + '">\
								<button type="button" class="flexmls-searchresults-delete-row"><span class="dashicons dashicons-no-alt"></span></button>\
							</div>\
						</li>' );
					$( '.flexmls-searchresults-select2' ).val( [] ).trigger( 'change' );
					$( '#searchresults-fields' ).sortable( 'refresh' );
				} );
		}
	};

	$(document).ready(function(){
		ajaxClearCache();
		searchableSearchDefault();
		sortableSearchFields();
	});

})(jQuery);