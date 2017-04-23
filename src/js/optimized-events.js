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