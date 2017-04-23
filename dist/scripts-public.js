window.optimizedResize = (function(){
	var callbacks = [], running = false;
	function resize(){
		if( !running ){
			running = true;
			if( window.requestAnimationFrame ){
				window.requestAnimationFrame( runCallbacks );
			} else {
				setTimeout( runCallbacks, 66 );
			}
		}
	}
	function runCallbacks(){
		callbacks.forEach( function( callback ){
			callback();
		} );
		running = false;
	}
	function addCallback( callback ){
		if( callback ){
			callbacks.push( callback );
		}
	}
	return {
		add: function( callback ){
			if( !callbacks.length ){
				window.addEventListener( 'resize', resize );
			}
			addCallback( callback );
		}
	};
}());
window.optimizedScroll = (function(){
	var callbacks = [], running = false;
	function scroll(){
		if( !running ){
			running = true;
			if( window.requestAnimationFrame ){
				window.requestAnimationFrame( runCallbacks );
			} else {
				setTimeout( runCallbacks, 66 );
			}
		}
	}
	function runCallbacks(){
		callbacks.forEach( function( callback ){
			callback();
		} );
		running = false;
	}
	function addCallback( callback ){
		if( callback ){
			callbacks.push( callback );
		}
	}
	return {
		add: function( callback ){
			if( !callbacks.length ){
				window.addEventListener( 'scroll', scroll );
			}
			addCallback( callback );
		}
	};
}());
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
(function($){

	var testJS = function(){
		$( 'body' ).removeClass( 'flexmls-no-js' );
	};

	$(document).ready(function(){
		testJS();
	});


})(jQuery);