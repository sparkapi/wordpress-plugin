(function($){

	$( 'body' ).append( '<div id="flexmls-tinymce-shortcode-generator" style="display: none;"></div>' );

	tinymce.create( 'tinymce.plugins.FlexmlsButtons', {
		init: function( editor, url ){
			editor.addButton( 'flexmlsidx_shortcodes', {
				tooltip: 'Add a Flexmls widget',
				title: 'Add Flexmls Widget',
				image: flexmls.pluginurl + '/dist/assets/tinymce_flexmls_pin.png',
				onclick: function(){
					var selectedShortcode = tinyMCE.activeEditor.selection.getContent({format : 'text'});
					var gotoShortcode;
					var mainScreen;
					if( true === !!selectedShortcode ){
						var clean_parse = selectedShortcode.replace(/^\[/, "");
						clean_parse = clean_parse.replace(/\]$/, "");
						clean_parse = clean_parse.split(" ");
						gotoShortcode = clean_parse[ 0 ].replace(/flexmls_/, "");
					}

					$.post( ajaxurl, {action: 'tinymce_popup'}, function( response ){
						mainScreen = response;
						$( '#flexmls-tinymce-shortcode-generator' ).html( response );
						tb_show( 'Flexmls&reg; Shortcode Generator', '#TB_inline?height=550&width=500&inlineId=flexmls-tinymce-shortcode-generator' );
						$( '#TB_ajaxContent' ).css({
							'height' : '100%',
							'overflow-y' : 'scroll',
							'position' : 'relative',
							'width' : 'auto'
						});
						if( true === !!gotoShortcode ){
							if( $( '.flexmls-shortcode-selector[data-shortcode="' + gotoShortcode + '"]' ).length ){
								$( '.flexmls-shortcode-selector[data-shortcode="' + gotoShortcode + '"]' ).trigger( 'click' );
							}
						}
					}, 'html' );

					var shortcode;

					$( 'body' ).on( 'click', '.flexmls-shortcode-selector', function( ev ){
						ev.preventDefault();
						var c = $( this ).data( 'class' );
						shortcode = $( this ).data( 'shortcode' );
						$.post( ajaxurl, {action: 'tinymce_popup_shortcode', class: c, shortcode: shortcode}, function( res ){
							//console.log( res );
							$( '#TB_ajaxContent' ).html( res );
						}, 'html' );
					} );

					$( 'body' ).on( 'click', '.flexmls-shortcode-back', function( ev ){
						ev.preventDefault();
						$( '#TB_ajaxContent' ).html( mainScreen );
					} );

					$( 'body' ).on( 'click', '.flexmls-insert-shortcode', function(){
						editor.selection.setContent('[flexmls_' + shortcode + ' once testing is complete]');
						tb_remove();
					} );

				}
			} );
		  },
		  createControl : function(n, cm) {
			   return null;
		  },
	});
	tinymce.PluginManager.add( 'flexmlsidx', tinymce.plugins.FlexmlsButtons );

})(jQuery);