(function($){

	$( 'body' ).append( '<div id="flexmls-tinymce-shortcode-generator" style="display: none;"></div>' );

	tinymce.create( 'tinymce.plugins.FlexmlsButtons', {
		init: function( editor, url ){
			editor.addButton( 'flexmlsidx_shortcodes', {
				tooltip: 'Add a Flexmls widget',
				title: 'Add Flexmls Widget',
				image: flexmls.pluginurl + '/dist/assets/tinymce_flexmls_pin.png',
				onclick: function(){
					$.post( ajaxurl, {action: 'tinymce_popup'}, function( response ){
						$( '#flexmls-tinymce-shortcode-generator' ).html( response );
						tb_show( 'Flexmls&reg; Shortcode Generator', '#TB_inline?height=550&width=500&inlineId=flexmls-tinymce-shortcode-generator' );
					}, 'html' );
					//editor.selection.setContent('[myshortcode]');
				}
			} );
			   /**
			   * Adds HTML tag to selected content
			   */
			   /*
			   ed.addButton( 'button_green', {
					title : 'Add span',
					image : '../wp-includes/images/smilies/icon_mrgreen.gif',
					cmd: 'button_green_cmd'
			   });
			   ed.addCommand( 'button_green_cmd', function() {
					var selected_text = ed.selection.getContent();
					var return_text = '';
					return_text = '<h1>' + selected_text + '</h1>';
					ed.execCommand('mceInsertContent', 0, return_text);
			   });
			   */
		  },
		  createControl : function(n, cm) {
			   return null;
		  },
	});
	tinymce.PluginManager.add( 'flexmlsidx', tinymce.plugins.FlexmlsButtons );

})(jQuery);