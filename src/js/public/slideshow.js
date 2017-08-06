(function($){

	var backgroundLoadMoreSlides = function(){
		if( $( '.flexmls_slideshow' ).length ){
			$( '.flexmls_slideshow .flexmls-slideshow > div' ).each(function(){
				if( true === !!$( this ).data( 'ajax' ) ){
					var params = $( this ).data( 'ajax' );
					var slider = $( this );
					var data = {
						action: 'flexmls_get_background_slides',
						params: window[ '' + params + '' ]
					};
					$.post( flexmls.ajaxurl, data, function( response ){
						if( true === !!response ){
							$( slider ).slick( 'unslick' );
							$.each(response, function( idx, result ){
								$( '.flexmls_slideshow .flexmls-slideshow > div' ).append( result );
							});
							slideshow();
						}

					}, 'json' );
				}
			});
		}
	};

	var slideshow = function(){
		if( $( '.flexmls_slideshow' ).length ){
			$( '.flexmls_slideshow .flexmls-slideshow > div' ).each(function(){
				var autoplay = $( this ).data( 'autoplay' );
				var cols = $( this ).data( 'cols' );
				var rows = $( this ).data( 'rows' );
				$( this ).slick({
					adaptiveHeight: true,
					autoplay: 0 == autoplay ? false : true,
					autoplaySpeed: 1000 * autoplay,
					nextArrow: '<button class="slick-next">Next <i class="fbsicon fbsicon-angle-right"></i></button>',
					prevArrow: '<button class="slick-prev"><i class="fbsicon fbsicon-angle-left"></i> Prev</button>',
					rows: rows,
					slidesPerRow: cols
				});
			});
		}
	};

	$(document).ready(function(){
		backgroundLoadMoreSlides();
		slideshow();
	});

})(jQuery);